FROM php:7.1.8-apache

# Install packages
RUN apt update
RUN apt install -y git libmemcached-dev libz-dev mlocate mysql-client netcat nmap tree vim libxml2-dev libcurl4-gnutls-dev

# Install memcache
RUN yes '' | pecl install memcache

# Install PHP extensions
RUN docker-php-ext-install mysql
RUN docker-php-ext-install pcntl

# Install Memcached INI
RUN echo "extension=/usr/local/lib/php/extensions/no-debug-non-zts-20121212/memcache.so" >>/usr/local/etc/php/conf.d/memcache.ini

# Set up aliases
RUN echo "alias ll='ls -al --color'" >>/root/.bashrc

# Enable Apache 2 rewrite mod
RUN a2enmod rewrite

# Update some Linux file system index thing
RUN updatedb
