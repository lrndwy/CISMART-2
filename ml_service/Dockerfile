# syntax=docker/dockerfile:1.7

FROM python:3.11-slim-bookworm

ENV PYTHONUNBUFFERED=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update && apt-get install -y --no-install-recommends \
      gcc \
      g++ \
      curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Layer requirements terpisah agar rebuild kode tidak ulang pip install
COPY requirements.txt .
RUN --mount=type=cache,target=/root/.cache/pip \
    pip install -r requirements.txt

COPY app.py ml_engine.py validators.py ./
COPY sample_training_data.csv combined_training_data.xlsx ./
COPY train_model.py train_with_real_data.py ./

RUN mkdir -p /app/models /app/data /app/logs \
 && chmod -R 755 /app

EXPOSE 5000

HEALTHCHECK --interval=15s --timeout=5s --start-period=40s --retries=5 \
    CMD curl -f http://localhost:5000/health || exit 1

CMD ["gunicorn", "--bind", "0.0.0.0:5000", "--workers", "2", "--threads", "4", "--timeout", "300", "app:app"]
