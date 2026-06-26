from django.core.exceptions import ValidationError
from django.core.validators import FileExtensionValidator

DOCUMENT_EXT = ['pdf', 'jpg', 'jpeg', 'png']
IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp']
FAVICON_EXT = ['ico', 'png', 'svg']

MAX_DOCUMENT_MB = 5
MAX_IMAGE_MB = 2
MAX_FAVICON_KB = 500


validate_document_ext = FileExtensionValidator(allowed_extensions=DOCUMENT_EXT)
validate_image_ext = FileExtensionValidator(allowed_extensions=IMAGE_EXT)
validate_favicon_ext = FileExtensionValidator(allowed_extensions=FAVICON_EXT)


def validate_document_size(f):
    limit = MAX_DOCUMENT_MB * 1024 * 1024
    if f.size > limit:
        raise ValidationError(f'Ukuran file maksimum {MAX_DOCUMENT_MB} MB.')


def validate_image_size(f):
    limit = MAX_IMAGE_MB * 1024 * 1024
    if f.size > limit:
        raise ValidationError(f'Ukuran gambar maksimum {MAX_IMAGE_MB} MB.')


def validate_favicon_size(f):
    limit = MAX_FAVICON_KB * 1024
    if f.size > limit:
        raise ValidationError(f'Ukuran favicon maksimum {MAX_FAVICON_KB} KB.')
