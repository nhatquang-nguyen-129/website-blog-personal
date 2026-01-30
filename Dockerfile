FROM php:8.2-apache

# --------------------------------------------------
# 1. System deps
# --------------------------------------------------
RUN apt-get update && apt-get install -y \
    curl \
    unzip \
    less \
    rsync \
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
# 5. Download WordPress core + merge wp-content correctly
# --------------------------------------------------
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

# --------------------------------------------------
# 6. Permissions
# --------------------------------------------------
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
