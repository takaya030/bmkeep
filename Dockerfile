# composer 用
FROM composer:2 as build
WORKDIR /app
COPY composer.json composer.lock /app
RUN composer install --no-dev --no-scripts

# Laravel の実行環境用のコンテナ
FROM php:8.1-apache
#RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 8080
COPY --from=build /app /var/www/
COPY . /var/www
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .env.prd /var/www/.env
RUN chmod 777 -R /var/www/storage/ && \
    echo "Listen 8080" >> /etc/apache2/ports.conf && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

WORKDIR /var/www
RUN php artisan optimize:clear && \
	php artisan optimize && \
	php artisan view:cache
