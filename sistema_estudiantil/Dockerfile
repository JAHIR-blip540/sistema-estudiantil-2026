FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/sistema-estudiantil.conf \
    && a2enconf sistema-estudiantil

WORKDIR /var/www/html
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

CMD ["bash", "-lc", "set -e; rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf /etc/apache2/mods-enabled/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.conf; ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load; ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf; PORT=${PORT:-8080}; sed -ri \"s/^Listen .*/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -ri \"s#<VirtualHost \\*:([0-9]+)>#<VirtualHost *:${PORT}>#\" /etc/apache2/sites-available/000-default.conf; apache2ctl -t; exec apache2-foreground"]
