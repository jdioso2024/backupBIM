# ─────────────────────────────────────────────────────────────────────────────
# PMB Terpadu — production image (MicroK8s + NFS PVC)
# Code TIDAK di-bake ke image. Kode di-mount dari NFS PVC ke /app saat runtime
# (pola sama dengan myakademik). Rebuild image hanya bila requirements.txt
# atau sistem dep berubah.
# ─────────────────────────────────────────────────────────────────────────────
FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

# ── Sistem: ODBC Driver 17 (pyodbc / MSSQL) + deps Pillow ────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        curl gnupg2 ca-certificates apt-transport-https \
        build-essential unixodbc unixodbc-dev \
        libjpeg-dev zlib1g-dev \
    && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
        | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
        > /etc/apt/sources.list.d/microsoft-prod.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql17 \
    && apt-get purge -y build-essential \
    && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Code di-mount dari NFS PVC ke /app saat runtime.
# pip install & collectstatic dijalankan dari command pod (k8s-deployment.yaml).
EXPOSE 8000
