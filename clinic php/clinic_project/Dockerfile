# Use an official PHP image with Apache
FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli zip

# Enable Apache mod_rewrite for clean URLs (optional, but good practice)
RUN a2enmod rewrite

# Set the working directory to Apache's web root
WORKDIR /var/www/html

# Copy application source code to the web root
# The actual project files will be mounted via docker-compose for development,
# but this ensures the image can be built independently if needed.
# We assume the Dockerfile is in the parent directory of 'clinic_project'
# or that the build context is set correctly.
# For this setup, we'll assume the Dockerfile is placed *inside* the clinic_project directory
# and we copy the contents of the current directory.
COPY . /var/www/html/

# Ensure Apache has permissions to write to necessary directories if any (e.g., uploads, cache)
# For now, we assume standard permissions are sufficient. If specific writable directories are needed:
# RUN chown -R www-data:www-data /var/www/html/writable_directory
# RUN chmod -R 775 /var/www/html/writable_directory

# Expose port 80 for the web server
EXPOSE 80

# The default Apache CMD will be used from the base image

