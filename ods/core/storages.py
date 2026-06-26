"""Custom static file storage.

WhiteNoise `CompressedManifestStaticFilesStorage` memindai JS/CSS saat
post-process untuk rewrite `url()` dan `//# sourceMappingURL=...` ke versi
hashed. Banyak bundle vendored (datatables, bootstrap, pdfmake) referensi ke
file `.map`/aset yang tidak ikut terpaket — collectstatic jadi fatal error.

Subclass ini log warning lalu lanjut, supaya deploy tidak gagal karena aset
third-party yang incomplete. Cache busting + gzip manifest tetap bekerja.
"""
import sys

from whitenoise.storage import CompressedManifestStaticFilesStorage


class TolerantCompressedManifestStaticFilesStorage(CompressedManifestStaticFilesStorage):
    def post_process(self, *args, **kwargs):
        for name, hashed_name, processed in super().post_process(*args, **kwargs):
            if isinstance(processed, Exception):
                sys.stderr.write(
                    f'[staticfiles] WARN: skip post-process {name}: {processed}\n'
                )
                processed = True
            yield name, hashed_name, processed
