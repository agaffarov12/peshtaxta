FROM trafex/php-nginx:3.0.0

ARG UID=1000
ARG GID=1000
ARG BUILD_DEV
ARG DEV_DEPENDENCIES="php81-pecl-xdebug"

ENV DOCKER_USER="nobody"

COPY --from=composer /usr/bin/composer /usr/bin/composer

USER root

RUN apk --no-cache add shadow icu-data-full && \
    usermod -u $UID nobody && \
    groupmod -g $GID nobody && \
    chown -R nobody.nobody /run /var/lib/nginx /var/log/nginx && \
    chown -R nobody.nobody /var/www

RUN if [ "$BUILD_DEV" ]; \
    then \
      >&2 echo "Building development environment"; \
      >&2 echo "Installing dev dependencies: ($DEV_DEPENDENCIES)"; \
      apk --no-cache add $DEV_DEPENDENCIES; \
      sed -ie 's~pm.process_idle_timeout = 10s;~pm.process_idle_timeout = 1s;~' /etc/php81/php-fpm.d/www.conf; \
      #docker-php-ext-enable xdebug; \
    else \
      >&2 echo "Building production environment"; \
    fi

RUN rm -rf /var/www/* &&  \
    sed -ie 's~/var/www/html~/var/www/public~' /etc/nginx/conf.d/default.conf && \
    sed -ie 's~/index.php?q=\$uri&\$args;~/index.php\$is_args\$args;~' /etc/nginx/conf.d/default.conf && \
    sed -ie 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' /etc/php81/php.ini && \
    sed -ie 's/post_max_size = 8M/post_max_size = 256M/' /etc/php81/php.ini && \
    sed -ie 's/;date.timezone =/date.timezone = Asia\/Tashkent/' /etc/php81/php.ini && \
    sed -i '/sendfile off;/a client_max_body_size 256M;' /etc/nginx/conf.d/default.conf

RUN apk --no-cache add  \
    php81-tokenizer \
    php81-xmlwriter \
    php81-simplexml \
    php81-pdo  \
    php81-zip \
    php81-fileinfo \
    php81-pdo_pgsql && \
    rm /etc/php81/conf.d/custom.ini

#cron

ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.29/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=cd48d45c4b10f3f0bfdd3a57d054cd05ac96812b

RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

COPY --chown=nobody ./docker/crontabs/nobody /etc/crontabs/

RUN echo $' \n[program: supercronic] \n\
    command=supercronic /etc/crontabs/nobody \n\
    stdout_logfile=/dev/stdout \n\
    stderr_logfile=/dev/stderr' >> /etc/supervisor/conf.d/supervisord.conf

#end-cron

USER nobody
WORKDIR /var/www

COPY --chown=nobody . .

#RUN if test -n "$BUILD_DEV"; \
#    then \
#      composer install --no-interaction --no-scripts; \
#    else \
#      composer install --no-interaction --no-scripts --no-dev --prefer-dist; \
#    fi

#CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
