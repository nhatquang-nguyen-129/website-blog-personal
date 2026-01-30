FROM php:8.2-apache

# --------------------------------------------------
# 1. System deps
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    less \
    && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# 2. Apache config
# --------------------------------------------------
RUN a2enmod rewrite

# --------------------------------------------------
# 3. Set web root
# --------------------------------------------------
WORKDIR /var/www/html

# --------------------------------------------------
# 4. Copy public folder (contains wp-content/mu-plugins)
# --------------------------------------------------
COPY public/ /var/www/html/

# --------------------------------------------------
# 5. Download WordPress core into /tmp
#    (DO NOT overwrite wp-content)
# --------------------------------------------------
RUN curl -o /tmp/wordpress.zip https://wordpress.org/latest.zip \
 && unzip /tmp/wordpress.zip -d /tmp \
 && rsync -av \
      --exclude=wp-content \
      /tmp/wordpress/ /var/www/html/ \
 && rm -rf /tmp/wordpress /tmp/wordpress.zip

# --------------------------------------------------
# 6. Permissions
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80