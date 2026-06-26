import os
import mimetypes
from pathlib import Path
from dotenv import load_dotenv

mimetypes.add_type("text/css", ".css", True)
mimetypes.add_type("text/javascript", ".js", True)

load_dotenv()

BASE_DIR = Path(__file__).resolve().parent.parent


# ── Core security ─────────────────────────────────────────────────────────────

SECRET_KEY = os.environ.get('SECRET_KEY')
if not SECRET_KEY:
    raise RuntimeError(
        'SECRET_KEY tidak ditemukan di environment. '
        'Set di file .env atau variabel lingkungan sebelum menjalankan aplikasi.'
    )

DEBUG = os.environ.get('DEBUG', 'False').lower() in ('true', '1', 'yes')

ALLOWED_HOSTS = [h.strip() for h in os.environ.get('ALLOWED_HOSTS', 'localhost,127.0.0.1').split(',') if h.strip()]

CSRF_TRUSTED_ORIGINS = [
    o.strip() for o in os.environ.get('CSRF_TRUSTED_ORIGINS', '').split(',') if o.strip()
]


# ── Apps & middleware ─────────────────────────────────────────────────────────

INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'django.contrib.humanize',
    'core',
    'pendaftaran',
    'raport',
    'beasiswa',
    'umum',
    'panel',
    'api',
]

# WhiteNoise hanya aktif di production (DEBUG=False) — di dev biarkan runserver
# serve static langsung dari STATICFILES_DIRS tanpa manifest.
_whitenoise_middleware = []
if not DEBUG:
    try:
        import whitenoise  # noqa: F401
        _whitenoise_middleware = ['whitenoise.middleware.WhiteNoiseMiddleware']
    except ImportError:
        pass

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    *_whitenoise_middleware,
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'pmb_terpadu.urls'

TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
                'core.context_processors.site_settings',
            ],
        },
    },
]

WSGI_APPLICATION = 'pmb_terpadu.wsgi.application'


# ── Database ──────────────────────────────────────────────────────────────────

_use_sqlite = os.environ.get('USE_SQLITE', 'False').lower() in ('true', '1', 'yes')

if _use_sqlite:
    # Mode development lokal tanpa SQL Server — pakai SQLite
    DATABASES = {
        'default': {
            'ENGINE': 'django.db.backends.sqlite3',
            'NAME': BASE_DIR / 'db.sqlite3',
        }
    }
else:
    DATABASES = {
        'default': {
            'ENGINE': 'mssql',
            'NAME': os.environ.get('DB_NAME'),
            'USER': os.environ.get('DB_USER'),
            'PASSWORD': os.environ.get('DB_PASSWORD'),
            'HOST': os.environ.get('DB_HOST'),
            'PORT': os.environ.get('DB_PORT', '1433'),
            'OPTIONS': {
                'driver': 'ODBC Driver 17 for SQL Server',
                'extra_params': 'TrustServerCertificate=yes',
            },
        }
    }


# ── Password validators ───────────────────────────────────────────────────────

AUTH_PASSWORD_VALIDATORS = [
    {'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator'},
    {'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator',
     'OPTIONS': {'min_length': 8}},
    {'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator'},
    {'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator'},
]


# ── i18n / static / media ─────────────────────────────────────────────────────

LANGUAGE_CODE = 'id'
TIME_ZONE = 'Asia/Jakarta'
USE_I18N = True
USE_TZ = True

DATE_INPUT_FORMATS = [
    '%Y-%m-%d',
    '%d-%m-%Y', '%d/%m/%Y',
    '%d-%m-%y', '%d/%m/%y',
]

STATIC_URL = '/static/'
STATICFILES_DIRS = [BASE_DIR / 'static']
STATIC_ROOT = BASE_DIR / 'staticfiles'

# WhiteNoise ManifestStorage hanya aktif di prod — butuh staticfiles.json yang
# dihasilkan collectstatic. Di dev (DEBUG=True) pakai default agar runserver
# tidak error "Missing staticfiles manifest entry" saat manifest belum ada.
# Subclass Tolerant... mengabaikan reference sourcemap/url yang hilang di
# bundle JS/CSS vendored (datatables, dsb) supaya collectstatic tidak fatal.
if not DEBUG:
    STORAGES = {
        'default': {'BACKEND': 'django.core.files.storage.FileSystemStorage'},
        'staticfiles': {
            'BACKEND': 'core.storages.TolerantCompressedManifestStaticFilesStorage',
        },
    }

MEDIA_URL = '/media/'
MEDIA_ROOT = BASE_DIR / 'media'

DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'


# ── Auth redirects ────────────────────────────────────────────────────────────

LOGIN_URL = '/login/'
LOGIN_REDIRECT_URL = '/dashboard/'
LOGOUT_REDIRECT_URL = '/'


# ── Upload limits ─────────────────────────────────────────────────────────────
# Field FileField/ImageField akan disimpan ke disk lewat temp, tapi Django
# memfilter memory upload dulu. 10 MB cukup untuk KTP/foto/PDF berkas.

FILE_UPLOAD_MAX_MEMORY_SIZE = 10 * 1024 * 1024   # 10 MB
DATA_UPLOAD_MAX_MEMORY_SIZE = 10 * 1024 * 1024   # 10 MB
DATA_UPLOAD_MAX_NUMBER_FIELDS = 2000


# ── Session & CSRF cookies ────────────────────────────────────────────────────

SESSION_COOKIE_AGE = 60 * 60 * 8               # 8 jam (re-login setiap hari kerja)
SESSION_EXPIRE_AT_BROWSER_CLOSE = False
SESSION_SAVE_EVERY_REQUEST = True              # perpanjang session tiap request


# ── Email ─────────────────────────────────────────────────────────────────────
# Dev: console backend (email dicetak di terminal). Prod: SMTP.

if DEBUG:
    EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'
else:
    EMAIL_BACKEND = os.environ.get('EMAIL_BACKEND', 'django.core.mail.backends.smtp.EmailBackend')
    EMAIL_HOST = os.environ.get('EMAIL_HOST', '')
    EMAIL_PORT = int(os.environ.get('EMAIL_PORT', '587'))
    EMAIL_HOST_USER = os.environ.get('EMAIL_HOST_USER', '')
    EMAIL_HOST_PASSWORD = os.environ.get('EMAIL_HOST_PASSWORD', '')
    EMAIL_USE_TLS = os.environ.get('EMAIL_USE_TLS', 'True').lower() in ('true', '1', 'yes')
    DEFAULT_FROM_EMAIL = os.environ.get('DEFAULT_FROM_EMAIL', 'noreply@example.com')


# ── Integrasi Sistem Keuangan (ODS push tagihan) ──────────────────────────────
# Endpoint inbound keuangan: POST {KEUANGAN_API_BASE}/api/integrations/ods/tagihan/
# Auth: header X-API-Key = KEUANGAN_ODS_TOKEN (sama dgn ODS_INBOUND_TOKEN di keuangan).
KEUANGAN_API_BASE = os.environ.get('KEUANGAN_API_BASE', '')
KEUANGAN_ODS_TOKEN = os.environ.get('KEUANGAN_ODS_TOKEN', '')
KEUANGAN_TIMEOUT = int(os.environ.get('KEUANGAN_TIMEOUT', '10'))


# ── Production security hardening ─────────────────────────────────────────────
# Hanya aktif jika DEBUG=False. Jangan aktif di dev karena bisa bikin browser
# enforce HTTPS ke localhost dan bikin cookie tidak jalan di HTTP.

if not DEBUG:
    # Pasang di belakang reverse proxy (Nginx/Traefik) yang terminate SSL
    SECURE_PROXY_SSL_HEADER = ('HTTP_X_FORWARDED_PROTO', 'https')

    SECURE_SSL_REDIRECT = os.environ.get('SECURE_SSL_REDIRECT', 'True').lower() in ('true', '1', 'yes')
    SESSION_COOKIE_SECURE = True
    CSRF_COOKIE_SECURE = True
    SECURE_CONTENT_TYPE_NOSNIFF = True
    SECURE_REFERRER_POLICY = 'same-origin'
    X_FRAME_OPTIONS = 'DENY'

    # HSTS — setelah Anda yakin semua subdomain pakai HTTPS
    SECURE_HSTS_SECONDS = int(os.environ.get('SECURE_HSTS_SECONDS', '0'))
    SECURE_HSTS_INCLUDE_SUBDOMAINS = False
    SECURE_HSTS_PRELOAD = False


# ── Logging ───────────────────────────────────────────────────────────────────

LOG_DIR = BASE_DIR / 'logs'
LOG_DIR.mkdir(exist_ok=True)

LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'formatters': {
        'standard': {'format': '[{asctime}] {levelname} {name}: {message}', 'style': '{'},
    },
    'handlers': {
        'console': {
            'class': 'logging.StreamHandler',
            'formatter': 'standard',
        },
        'file_app': {
            'class': 'logging.handlers.RotatingFileHandler',
            'filename': LOG_DIR / 'app.log',
            'maxBytes': 5 * 1024 * 1024,   # 5 MB
            'backupCount': 5,
            'formatter': 'standard',
        },
        'file_error': {
            'class': 'logging.handlers.RotatingFileHandler',
            'filename': LOG_DIR / 'error.log',
            'maxBytes': 5 * 1024 * 1024,
            'backupCount': 5,
            'level': 'ERROR',
            'formatter': 'standard',
        },
    },
    'root': {
        'handlers': ['console', 'file_app', 'file_error'],
        'level': 'INFO' if not DEBUG else 'DEBUG',
    },
    'loggers': {
        'django.security': {'handlers': ['file_error'], 'level': 'WARNING', 'propagate': False},
        'django.request':  {'handlers': ['file_error'], 'level': 'ERROR',   'propagate': False},
    },
}
