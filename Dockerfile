FROM php:8.2-fpm-alpine


ENV DockerHOME=/var/www
RUN mkdir -p $DockerHOME

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install mysqli pdo pdo_mysql

ADD composer.lock composer.json $DockerHOME
WORKDIR $DockerHOME
RUN composer install --no-scripts --no-dev

COPY . .
RUN cp .env.example .env



# Add user for laravel application
#RUN groupadd -g 1000 www
#RUN useradd -u 1000 -ms /bin/bash -g www www



#COPY --chown=www:www . /var/www

# Change current user to www
#USER www

RUN chmod +x $DockerHOME/entry.sh
ENTRYPOINT ["sh", "/var/www/entry.sh"]


# Expose port 9000 and start php-fpm server
#EXPOSE 9000
#CMD ["php-fpm"]
