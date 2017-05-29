
# See https://github.com/bylexus/docker-apache-php53

# Source container
FROM bylexus/apache-php53

# Install dependencies
RUN yes | \
    apt-get update && \
    apt-get install -y \
        curl \
        php5-curl \
        php5-intl \
        git

# Add user david (to avoid user permission problems)
RUN useradd -m -u 1000 -U david

# Install composer in /usr/local/bin/composer
RUN yes | \
    curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/local/bin --filename=composer

# Replace the run command to set APACHE_RUN_USER & APACHE_RUN_GROUP
COPY docker-cmd /usr/local/bin/run
RUN chmod +x /usr/local/bin/run

# Set the WORKDIR to /var/www so all following commands run in /var/www
RUN mkdir -p /var/www
WORKDIR /var/www