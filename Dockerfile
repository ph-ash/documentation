FROM composer:1 as composer
COPY . /var/www/html
WORKDIR /var/www/html
ENV APP_ENV=prod

RUN composer install --ignore-platform-reqs \
    && composer dump-autoload --optimize

# next stage #

FROM docker/compose:1.24.1
COPY --from=composer /var/www/html /var/www/html
WORKDIR /var/www/html
ENV APP_ENV=prod

RUN apk add --no-cache php7-cli \
       php7-ctype \
       php7-curl \
       php7-dom \
       php7-iconv \
       php7-json \
       php7-mbstring \
       php7-openssl \
       php7-session \
       php7-tokenizer

ENTRYPOINT ["docker/entrypoint.sh"]
