FROM ubuntu:18.04

LABEL maintainer="Jess Stopa"

ARG DEBIAN_FRONTEND=noninteractive

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y curl zip unzip git supervisor sqlite3 nginx gnupg

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu bionic main" > /etc/apt/sources.list.d/ppa_ondrej_php.list \
    && echo "deb http://ppa.launchpad.net/nginx/development/ubuntu bionic main" > /etc/apt/sources.list.d/ppa_nginx_mainline.list \
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys E5267A6C \
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys C300EE8C \
    && apt-get update && apt-get install -y php7.3-fpm php7.3-cli \
        php7.3-pgsql php7.3-sqlite3 php7.3-gd \
        php7.3-curl php7.3-memcached \
        php7.3-imap php7.3-mysql php7.3-mbstring \
        php7.3-xml php7.3-zip php7.3-bcmath php7.3-soap \
        php7.3-intl php7.3-readline \
        php7.3-msgpack php7.3-igbinary

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && mkdir /run/php \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && echo "daemon off;" >> /etc/nginx/nginx.conf

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

COPY custom.ini /etc/php/7.3/fpm/conf.d/99-custom.ini
COPY php-fpm.conf /etc/php/7.3/fpm/php-fpm.conf
COPY default /etc/nginx/sites-available/default
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

ENTRYPOINT ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
