FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .

ENV PORT=8080
EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "router.php"]
