FROM php:7.3.6-fpm-alpine3.9

RUN apk add --no-cache shadow mysql-client openssl \
    && docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

ENV DOCKERIZE_VERSION v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-alpine-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# Set uid of www-data user inside the container to 1001. That way, it will be exactly the same user
# as my linux user outside the container that also has uid 1001 because both, docker host and
# docker machine, share the same kernel.
# https://medium.com/@mccode/understanding-how-uid-and-gid-work-in-docker-containers-c37a01d01cf
# https://askubuntu.com/questions/427107/why-can-i-create-users-with-the-same-uid
RUN usermod -u 1001 www-data

WORKDIR /var/www/html

USER www-data

EXPOSE 9000

ENTRYPOINT ["./entrypoint.sh"]
