from django.db import migrations, models


SEED_JENIS = [
    ('BKAD',   'Beasiswa Khusus Anak Daerah',      10),
    ('BU-UMS', 'Beasiswa Unggulan UMS',            20),
    ('BTUMD',  'Beasiswa Tidak Mampu',             30),
    ('KIP',    'KIP Kuliah',                       40),
    ('OSC',    'One Stop Community',               50),
    ('BP-ORS', 'Beasiswa Prestasi Olahraga/Seni',  60),
    ('BP-MTQ', 'Beasiswa Prestasi MTQ',            70),
    ('LAIN',   'Lainnya',                          99),
]


def seed_jenis(apps, schema_editor):
    JenisBeasiswa = apps.get_model('beasiswa', 'JenisBeasiswa')
    for kode, nama, urut in SEED_JENIS:
        JenisBeasiswa.objects.get_or_create(
            kode=kode,
            defaults={'nama': nama, 'urutan': urut, 'aktif': True},
        )


def unseed_jenis(apps, schema_editor):
    JenisBeasiswa = apps.get_model('beasiswa', 'JenisBeasiswa')
    JenisBeasiswa.objects.filter(kode__in=[k for k, _, _ in SEED_JENIS]).delete()


class Migration(migrations.Migration):

    dependencies = [
        ('beasiswa', '0002_alter_beasiswadaftar_file_formulir_and_more'),
    ]

    operations = [
        migrations.CreateModel(
            name='JenisBeasiswa',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('kode', models.CharField(help_text='Kode unik (mis: KIP, BTUMD). Akan disimpan pada record pendaftaran.', max_length=20, unique=True)),
                ('nama', models.CharField(max_length=150)),
                ('deskripsi', models.TextField(blank=True)),
                ('aktif', models.BooleanField(default=True, help_text='Jika dimatikan, jenis ini tidak muncul pada form pendaftaran cama.')),
                ('urutan', models.IntegerField(default=0, help_text='Semakin kecil semakin atas.')),
            ],
            options={
                'verbose_name': 'Jenis Beasiswa',
                'verbose_name_plural': 'Jenis Beasiswa',
                'db_table': 'beasiswa_jenis',
                'ordering': ['urutan', 'nama'],
            },
        ),
        migrations.AlterField(
            model_name='beasiswadaftar',
            name='jenis_beasiswa',
            field=models.CharField(help_text='Kode JenisBeasiswa yang dipilih cama.', max_length=20),
        ),
        migrations.RunPython(seed_jenis, unseed_jenis),
    ]
