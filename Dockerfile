FROM php:8.2-apache

# 1. System deps
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    less \
    rsync \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. PHP extensions required by WordPress (mysqli for DB, gd for images, zip for plugin installs)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" mysqli pdo_mysql gd zip

# 3. Apache config
RUN a2enmod rewrite

# 4. Set web root
WORKDIR /var/www/html

# 5. Copy public folder (contains wp-content/mu-plugins)
COPY public/ /var/www/html/

# 6. Download WordPress core + merge wp-content correctly
RUN curl -o /tmp/wordpress.zip https://wordpress.org/latest.zip \
 && unzip /tmp/wordpress.zip -d /tmp \
 \
 # Copy WordPress core (everything)
 && rsync -av /tmp/wordpress/ /var/www/html/ \
 \
 # Re-inject mu-plugins from repo (delta layer)
 && if [ -d "/var/www/html/wp-content/mu-plugins" ]; then \
        echo "mu-plugins already present"; \
    fi \
 \
 && rm -rf /tmp/wordpress /tmp/wordpress.zip

# 7. wp-config.php (reads DB_* from environment at runtime — see docker-compose.yml)
COPY docker/wp-config.docker.php /var/www/html/wp-config.php

# 8. Permissions
# wp-content/uploads isn't part of the WordPress core zip — it only gets
# created the first time something is uploaded. Pre-create it here so it's
# www-data-owned in the image; otherwise the wp_uploads named volume's first
# mount picks up root ownership instead, and every upload 403s.
RUN mkdir -p wp-content/uploads \
 && chown -R www-data:www-data /var/www/html

EXPOSE 80