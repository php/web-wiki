FROM php:8.2-apache

RUN pecl install xdebug
RUN echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/99-xdebug.ini
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/99-xdebug.ini
RUN echo "xdebug.client_host=xdebug://gateway" >> /usr/local/etc/php/conf.d/99-xdebug.ini
#RUN echo "xdebug.log=xdebug.log" >> /usr/local/etc/php/conf.d/99-xdebug.ini
#RUN echo "xdebug.log_level=11" >> /usr/local/etc/php/conf.d/99-xdebug.ini

RUN a2enmod rewrite
