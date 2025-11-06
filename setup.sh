#!/bin/bash

#==============================================================================
# Comprehensive Setup Script for CodeIgniter 4 Project on Ubuntu
#==============================================================================
# DESCRIPTION:
# This script automates the installation and configuration of a complete
# LEMP/LAMP-like stack required to run the specified CodeIgniter 4 application.
# It handles the web server, database, PHP, and project-specific setup.
#
# HOW TO USE:
# 1. Save this script as setup.sh on your fresh Ubuntu server.
# 2. Make it executable:  chmod +x setup.sh
# 3. Run it with sudo:     sudo ./setup.sh
#==============================================================================

# --- Script Configuration ---
# Exit immediately if a command exits with a non-zero status.
set -e

# --- !! IMPORTANT: SET YOUR PROJECT AND DATABASE DETAILS HERE !! ---
GIT_REPO_URL="https://github.com/nehemiaobati/genaiwebapplication.git"
PROJECT_DIR_NAME="wasmercodegniter"
PROJECT_PATH="/var/www/${PROJECT_DIR_NAME}"

DB_NAME="server_codeigniter"
DB_USER="ci4_user"

# DB_PASSWORD will be generated after openssl is installed.

# --- 1. System Update ---
echo "--- [1/10] Updating and upgrading system packages... ---"
apt-get update
apt-get upgrade -y

# Install essential utilities: openssl for password generation, unzip for composer, and git for cloning
echo "Installing openssl, unzip, and git..."
apt-get install -y openssl unzip git sudo nano perl # Ensure perl is installed for minimal uncommenting

# Generate a secure, random password for the database user AFTER openssl is installed
DB_PASSWORD=$(openssl rand -base64 16)
# Generate a secure, random encryption key
ENCRYPTION_KEY=$(openssl rand -base64 32) # A longer key for encryption

# --- 2. Install Apache2 Web Server ---
echo "--- [2/10] Installing Apache2 Web Server... ---"
apt-get install -y apache2

# --- 3. Install PHP 8.2 and Required Extensions ---
echo "--- [3/10] Installing PHP 8.2 and required extensions... ---"

# Add the ondrej/php PPA for the latest PHP versions
apt-get install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt-get update

# Install PHP and all extensions required by the project
apt-get install -y php8.2 php8.2-mysql php8.2-intl php8.2-mbstring php8.2-bcmath php8.2-curl php8.2-xml php8.2-zip php8.2-gd

# --- 4. Install and Configure MySQL ---
echo "--- [4/10] Installing and configuring MySQL... ---"

# Install MySQL Server
apt-get install -y mysql-server

# Explicitly start MySQL service (as apt might fail to start it in some environments)
echo "Attempting to start MySQL service..."
sudo service mysql start
sudo service mysql status

# Wait for MySQL service to be fully up and accepting connections
echo "Waiting for MySQL service to become ready..."
MAX_TRIES=15 # Maximum number of attempts
TRIES=0
while ! sudo mysqladmin ping -u root --silent; do
  TRIES=$((TRIES+1))
  if [ $TRIES -ge $MAX_TRIES ]; then
    echo "ERROR: MySQL server did not respond after multiple attempts. Exiting."
    exit 1
  fi
  echo "MySQL not ready yet, waiting 2 seconds... (Attempt ${TRIES}/${MAX_TRIES})"
  sleep 2
done
echo "MySQL service is ready and accepting connections."

# Create the database and user in a non-interactive way
sudo mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
sudo mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
sudo mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
sudo mysql -u root -e "FLUSH PRIVILEGES;"
echo "[SUCCESS] Database '${DB_NAME}' and user '${DB_USER}' created."

# --- 5. Install Composer (PHP Dependency Manager) ---
echo "--- [5/10] Installing Composer... ---"
EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "${EXPECTED_CHECKSUM}" != "${ACTUAL_CHECKSUM}" ]; then
  >&2 echo 'ERROR: Invalid Composer installer checksum'
  rm composer-setup.php
  exit 1
fi

php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# --- 6. Install Node.js and NPM ---
echo "--- [6/10] Installing Node.js and NPM... ---"

# Use NodeSource repository for a recent version (e.g., Node.js 20.x)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
apt-get install -y nodejs

# --- 7. Clone the Project from Git ---
echo "--- [7/10] Cloning project from Git repository... ---"
if [ -d "${PROJECT_PATH}" ]; then
  echo "[WARNING] Project directory already exists. Skipping clone."
else
  git clone "${GIT_REPO_URL}" "${PROJECT_PATH}"
fi

# --- 8. Configure Project ---
echo "--- [8/10] Setting up CodeIgniter project... ---"
cd "${PROJECT_PATH}"

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Create and configure the .env file
cp env .env

# Define default database connection parameters for local setup
DB_HOSTNAME="127.0.0.1"
DB_DRIVER="MySQLi"
DB_PORT="3306"

# Define the complete lines for the .env file for critical settings
# These will overwrite or add the relevant sections
ENV_CI_ENVIRONMENT="CI_ENVIRONMENT = production"
ENV_APP_BASEURL="app.baseURL = 'http://localhost:80'"
ENV_DB_HOSTNAME="database.default.hostname = ${DB_HOSTNAME}"
ENV_DB_DATABASE="database.default.database = ${DB_NAME}"
ENV_DB_USERNAME="database.default.username = ${DB_USER}"
ENV_DB_PASSWORD="database.default.password = ${DB_PASSWORD}"
ENV_DB_DBDRIVER="database.default.DBDriver = ${DB_DRIVER}"
ENV_DB_PORT="database.default.port = ${DB_PORT}"
ENV_ENCRYPTION_KEY="encryption.key = ${ENCRYPTION_KEY}"

# Define lines for other configurations to be uncommented or explicitly set (empty for user input)
ENV_PAYSTACK_KEY="PAYSTACK_SECRET_KEY=\"\""
ENV_GEMINI_KEY="GEMINI_API_KEY=\"\""
ENV_RECAPTCHA_SITE="recaptcha_siteKey=''"
ENV_RECAPTCHA_SECRET="recaptcha_secretKey=''"
ENV_EMAIL_FROMEMAIL="email_fromEmail = ''"
ENV_EMAIL_FROMNAME="email_fromName = 'AFRIKENKID'"
ENV_EMAIL_SMTPHOST="email_SMTPHost = 'smtp.gmail.com'"
ENV_EMAIL_SMTPUSER="email_SMTPUser = ''"
ENV_EMAIL_SMTPPASS="email_SMTPPass = ''"
ENV_EMAIL_SMTPPORT="email_SMTPPort = 587"
ENV_EMAIL_SMTPCRYPTO="email_SMTPCrypto = 'tls'"


# Create a temporary file to build the new .env content
TEMP_ENV_FILE=$(mktemp)

# Read the original .env content, filter out lines we will explicitly set,
# and write the remaining content to the temporary file.
# Using grep -vE for Extended Regex to handle the OR condition in the pattern.
grep -vE "^(#+\s*)?(CI_ENVIRONMENT|app.baseURL|database\.default\.(hostname|database|username|password|DBDriver|port)|encryption\.key|PAYSTACK_SECRET_KEY|GEMINI_API_KEY|recaptcha_siteKey|recaptcha_secretKey|email_fromEmail|email_fromName|email_SMTPHost|email_SMTPUser|email_SMTPPass|email_SMTPPort|email_SMTPCrypto)\s*=" "${PROJECT_PATH}/.env" > "${TEMP_ENV_FILE}"

# Append the corrected/uncommented/set critical lines to the temporary file.
# Adding comments and blank lines for better readability, mirroring the sample .env structure.
echo "" >> "${TEMP_ENV_FILE}" # Ensure a newline before starting new sections

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# ENVIRONMENT (configured by setup script)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_CI_ENVIRONMENT}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# APP (configured by setup script)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_APP_BASEURL}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# DATABASE (configured by setup script)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_DB_HOSTNAME}" >> "${TEMP_ENV_FILE}"
echo "${ENV_DB_DATABASE}" >> "${TEMP_ENV_FILE}"
echo "${ENV_DB_USERNAME}" >> "${TEMP_ENV_FILE}"
echo "${ENV_DB_PASSWORD}" >> "${TEMP_ENV_FILE}"
echo "${ENV_DB_DBDRIVER}" >> "${TEMP_ENV_FILE}"
echo "${ENV_DB_PORT}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# ENCRYPTION (configured by setup script)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_ENCRYPTION_KEY}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# PAYMENT GATEWAY CONFIGURATION (User Input Required)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_PAYSTACK_KEY}" >> "${TEMP_ENV_FILE}"
echo "${ENV_GEMINI_KEY}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# RECAPTCHA CONFIGURATION (User Input Required)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_RECAPTCHA_SITE}" >> "${TEMP_ENV_FILE}"
echo "${ENV_RECAPTCHA_SECRET}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"

echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "# EMAIL CONFIGURATION (User Input Required for User/Pass)" >> "${TEMP_ENV_FILE}"
echo "#--------------------------------------------------------------------" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_FROMEMAIL}" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_FROMNAME}" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_SMTPHOST}" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_SMTPUSER}" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_SMTPPASS}" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_SMTPPORT}" >> "${TEMP_ENV_FILE}"
echo "${ENV_EMAIL_SMTPCRYPTO}" >> "${TEMP_ENV_FILE}"
echo "" >> "${TEMP_ENV_FILE}"


# Move the temporary file to replace the original .env
mv "${TEMP_ENV_FILE}" "${PROJECT_PATH}/.env"

# Run database migrations
php spark migrate

# Set correct file permissions for the web server
chown -R www-data:www-data "${PROJECT_PATH}"
chmod -R 775 "${PROJECT_PATH}"
chmod -R 775 "${PROJECT_PATH}/writable"

# --- 9. Configure Apache Virtual Host ---
echo "--- [9/10] Configuring Apache Virtual Host... ---"

# Create a new virtual host file
cat <<EOF > /etc/apache2/sites-available/${PROJECT_DIR_NAME}.conf
<VirtualHost *:80>
ServerAdmin webmaster@localhost
DocumentRoot ${PROJECT_PATH}/public

<Directory ${PROJECT_PATH}/public>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

ErrorLog \${APACHE_LOG_DIR}/error.log
CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF
# Enable the new site, enable rewrite module, and disable the default site
a2ensite "${PROJECT_DIR_NAME}.conf"
a2enmod rewrite
a2dissite 000-default.conf

# Restart Apache to apply changes
sudo service apache2 start
sudo service apache2 status

# --- 10. Final Summary ---
echo "--- [10/10] Setup Complete! ---"
echo ""
echo "============================================================"
echo "          🚀 DEPLOYMENT SUCCESSFUL 🚀"
echo "============================================================"
echo ""
echo "Your application has been deployed to: ${PROJECT_PATH}"
echo ""
echo "DATABASE DETAILS (save these securely!):"
echo "  Database Name: ${DB_NAME}"
echo "  Database User: ${DB_USER}"
echo "  Database Password: ${DB_PASSWORD}"
echo ""
echo "VERSIONS INSTALLED:"
php -v | head -n 1
mysql --version
composer --version
node -v
npm -v
echo ""
echo "NEXT STEPS:"
echo "1. Point your domain's DNS 'A' record to this server's IP address."
echo "2. SSH back into the server and edit the .env file with your API keys:"
echo "   sudo nano ${PROJECT_PATH}/.env"
echo "3. (Optional) For HTTPS, install Certbot: sudo apt install certbot python3-certbot-apache && sudo certbot --apache"
echo "============================================================"