FROM php:8.1-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libxml2-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    vim \
    unzip \
    git \
    curl \
    nodejs npm


###### configure php ext ##########
RUN docker-php-ext-configure soap --enable-soap \
    && docker-php-ext-install soap \
    && docker-php-ext-install pdo_mysql
# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
# RUN docker-php-ext-install pdo_mysql

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#install dependancies
CMD composer install && cd public/assets/page/app && npm install

#cache folder access right
CMD chmod -R 777 cache/

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
