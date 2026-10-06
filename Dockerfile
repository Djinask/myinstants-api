FROM php:8.3-cli

RUN docker-php-ext-install curl

WORKDIR /app
COPY . .

ENV PORT=8080
EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "router.php"]
