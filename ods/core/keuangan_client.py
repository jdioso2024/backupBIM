"""Client integrasi ke Sistem Keuangan (ODS push tagihan).

Mengirim tagihan pendaftar (CAMA) ke endpoint inbound keuangan:
    POST {KEUANGAN_API_BASE}/api/integrations/ods/tagihan/
    Header: X-API-Key: {KEUANGAN_ODS_TOKEN}

Memakai urllib stdlib (tanpa dependency tambahan). Semua kegagalan ditangkap
dan dicatat ke log — pendaftaran TIDAK boleh gagal hanya karena keuangan
tidak terjangkau (idempotensi di sisi keuangan memungkinkan retry/sinkron ulang).
"""
import json
import logging
import urllib.error
import urllib.request
from datetime import date, timedelta
from decimal import Decimal

logger = logging.getLogger(__name__)


def _config():
    """Konfigurasi integrasi dari DB (singleton). Bisa diubah lewat menu."""
    from core.models import IntegrasiConfig
    return IntegrasiConfig.load()


def _enabled() -> bool:
    cfg = _config()
    return bool(cfg.keuangan_aktif and cfg.keuangan_api_base and cfg.keuangan_ods_token)


def _post(payload: dict):
    cfg = _config()
    url = cfg.keuangan_api_base.rstrip('/') + '/api/integrations/ods/tagihan/'
    data = json.dumps(payload).encode('utf-8')
    req = urllib.request.Request(
        url, data=data, method='POST',
        headers={
            'Content-Type': 'application/json',
            'X-API-Key': cfg.keuangan_ods_token,
        },
    )
    with urllib.request.urlopen(req, timeout=cfg.keuangan_timeout) as resp:
        return json.loads(resp.read().decode('utf-8'))


def kirim_tagihan(payload: dict):
    """Kirim payload tagihan ke keuangan.

    Return dict respons keuangan bila sukses; bila gagal/terlewat, dict
    ``{'_error': True, 'detail': '...'}`` agar pemanggil bisa menampilkan alasan.
    """
    if not _enabled():
        logger.warning('Integrasi keuangan belum dikonfigurasi (KEUANGAN_API_BASE/TOKEN); lewati.')
        return {'_error': True, 'detail': 'Integrasi keuangan belum dikonfigurasi.'}
    try:
        hasil = _post(payload)
        logger.info('Push tagihan keuangan OK: %s', hasil.get('detail', hasil))
        return hasil
    except urllib.error.HTTPError as exc:
        body = exc.read().decode('utf-8', 'ignore')
        logger.error('Push tagihan keuangan HTTP %s: %s', exc.code, body)
        return {'_error': True, 'detail': f'HTTP {exc.code}: {body[:300]}'}
    except Exception as exc:  # noqa: BLE001 — jangan ganggu alur pendaftaran
        logger.error('Push tagihan keuangan gagal: %s', exc)
        return {'_error': True, 'detail': str(exc)}


def kirim_tagihan_pendaftaran(pendaftar):
    """Buat tagihan **Biaya Pendaftaran** di keuangan untuk seorang CAMA.

    Nominal diambil dari periode biaya aktif (BiayaKuliahPeriode.get_aktif()).
    Idempotent: ref tagihan = "<no_daftar>-PENDAFTARAN", jadi aman dipanggil ulang.
    """
    from core.models import BiayaKuliahPeriode

    periode = BiayaKuliahPeriode.get_aktif()
    if not periode:
        logger.warning('Tidak ada periode biaya aktif; lewati tagihan pendaftaran %s',
                       getattr(pendaftar, 'no_daftar', '?'))
        return None

    prodi = getattr(pendaftar, 'prodi1', None)
    tahun = str(periode.tahun_pmb).strip()[:4] or str(date.today().year)
    payload = {
        'mahasiswa': {
            'ref': pendaftar.no_daftar,             # no_daftar (boleh ada huruf) — kunci idempotensi
            'nama': pendaftar.nama,
            'program_studi': prodi.nama if prodi else '',
            'fakultas': getattr(getattr(prodi, 'fakultas', None), 'nama', '') if prodi else '',
            'angkatan': int(tahun),
        },
        'tagihan': [{
            'kategori': 'pendaftaran',
            'nominal': str(periode.biaya_pendaftaran),
            'jatuh_tempo': (date.today() + timedelta(days=14)).isoformat(),
            'ref': f'{pendaftar.no_daftar}-PENDAFTARAN',
        }],
    }
    return kirim_tagihan(payload)


def kirim_tagihan_kelulusan(pendaftar):
    """Buat tagihan **DPP Cicilan 1** + **SPP Awal** saat pendaftar lulus.

    Nominal diambil dari rincian biaya prodi×kelas (BiayaKuliahProdi) sesuai
    prodi_lulus & kelas pilihan cama pada periode aktif. VA dari respons keuangan
    disimpan ke Registrasi untuk ditampilkan. Idempotent (ref per kategori).
    Return dict respons (berisi ``nomor_va``) atau None.
    """
    from core.models import BiayaKuliahPeriode, BiayaKuliahProdi
    from pendaftaran.models import Registrasi

    reg = Registrasi.objects.filter(pendaftar=pendaftar).first()
    prodi = reg.prodi_lulus if reg else None
    kelas = (reg.kelas if reg else '') or ''
    periode = BiayaKuliahPeriode.get_aktif()
    if not (reg and prodi and kelas and periode):
        kurang = []
        if not (reg and prodi):
            kurang.append('prodi lulus')
        if not kelas:
            kurang.append('kelas')
        if not periode:
            kurang.append('periode biaya aktif')
        msg = 'Belum lengkap: ' + ', '.join(kurang)
        logger.warning('Lulus %s: %s — lewati DPP/SPP',
                       getattr(pendaftar, 'no_daftar', '?'), msg)
        return {'_error': True, 'detail': msg}

    biaya = BiayaKuliahProdi.objects.filter(
        periode=periode, prodi=prodi, jenis_kelas=kelas,
    ).first()
    if not biaya:
        msg = f'Rincian biaya prodi {prodi} kelas {kelas} belum diisi'
        logger.warning('Lulus %s: %s', pendaftar.no_daftar, msg)
        return {'_error': True, 'detail': msg}

    tahun = str(periode.tahun_pmb).strip()[:4] or str(date.today().year)
    jt = (date.today() + timedelta(days=30)).isoformat()
    nd = pendaftar.no_daftar
    items = []

    def _add(kategori, nominal, suffix):
        if nominal and Decimal(nominal) > 0:
            items.append({'kategori': kategori, 'nominal': str(nominal),
                          'semester': 1, 'jatuh_tempo': jt, 'ref': f'{nd}-{suffix}'})

    if getattr(biaya, 'is_boarding', False):
        # International Boarding: 3 komponen penuh (tanpa cicilan).
        _add('pengembangan', biaya.pengembangan, 'PNG')
        _add('biaya_hidup', biaya.biaya_hidup, 'BHD')
        _add('dpp_spp', biaya.dpp_spp_total, 'DPPSPP')
    else:
        # Reguler/Karyawan/Internasional: DPP Cicilan 1 (penuh) + SPP Cicilan 1 (SPP/2).
        _add('dpp_cicilan_1', biaya.dpp_cicilan_1, 'DPP1')
        if biaya.spp_per_semester and biaya.spp_per_semester > 0:
            spp_cicilan_1 = (Decimal(biaya.spp_per_semester) / 2).quantize(Decimal('0.01'))
            _add('spp_cicilan_1', spp_cicilan_1, 'SPP1')

    if not items:
        msg = 'Semua komponen biaya bernilai 0 pada rincian biaya prodi/kelas ini'
        logger.warning('Lulus %s: %s', nd, msg)
        return {'_error': True, 'detail': msg}

    payload = {
        'mahasiswa': {
            'ref': pendaftar.no_daftar,
            'nim': (reg.nim or ''),
            'nama': pendaftar.nama,
            'program_studi': prodi.nama,
            'fakultas': getattr(getattr(prodi, 'fakultas', None), 'nama', ''),
            'angkatan': int(tahun),
        },
        'tagihan': items,
    }
    hasil = kirim_tagihan(payload)
    if hasil and hasil.get('nomor_va') and reg.nomor_va != hasil['nomor_va']:
        reg.nomor_va = hasil['nomor_va']
        reg.save(update_fields=['nomor_va'])
    return hasil
