# Custom Paymenter image.
# Build from the repo root: docker build -f paymenter/Dockerfile -t <tag> paymenter
# Keeps the container runtime clean — no runtime composer hacks.

# Pin the exact upstream revision currently running in the cluster.
FROM ghcr.io/paymenter/paymenter@sha256:27471e077f4413f5f7c4e63656a8e7894892fc8c6885e5481f91e420bd37b63b

USER root

# 1. Jalali/Shamsi support for Filament.
#    v3.0.0 matches the Filament 5.7.x shipped in this image (v3.1.0 needs >=5.10).
#    The package auto-registers its service provider via Laravel discovery.
RUN cd /app \
 && composer require ariaieboy/filament-jalali:3.0.0 --no-interaction --no-progress \
 && composer clear-cache

# 2. Remove all bundled gateways / server extensions and all languages except English.
#    Persian is added below from this build context. The entrypoint only copies defaults
#    into an empty directory, and PAYMENTER_SKIP_DEFAULT=true stops renewal on restart.
RUN rm -rf /app/extensions/* /app/extensions_default/* \
 && mkdir -p /app/extensions /app/extensions_default \
 && find /app/lang -mindepth 1 -maxdepth 1 ! -name en -exec rm -rf {} +

# 3. Register Persian: RTL layout and a label for the locale switcher.
RUN sed -i "s/^        'ar',$/        'ar',\\n        'fa',/" /app/config/app.php \
 && sed -i "/^        'en' => 'English',$/a\\        'fa' => 'فارسی'," /app/config/app.php

# 4. Our files.
COPY lang/fa /app/lang/fa
COPY FilamentJalaliProvider.php /app/app/Providers/FilamentJalaliProvider.php
COPY bootstrap-providers.php /app/bootstrap/providers.php

# Bundled gateways/servers were stripped above. Drop our gateway into the
# default-extension source; the entrypoint copies it into /app/extensions on
# first boot when that directory is empty.
COPY extensions/Gateways/Zibal /app/extensions_default/Gateways/Zibal

# Upstream ENTRYPOINT/CMD (entrypoint.sh -> supervisord) are preserved.
