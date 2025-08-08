FROM php:8.3-cli

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

RUN composer install

CMD ["php", "index.php"]