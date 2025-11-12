#!/bin/bash

#==============================================================================
# Comprehensive Setup Script for CodeIgniter 4 Project on Ubuntu (Refactored)
#==============================================================================
# DESCRIPTION:
# This script automates the installation and configuration of a complete
# LEMP/LAMP-like stack required to run a CodeIgniter 4 application.
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
readonly GIT_REPO_URL="https://github.com/nehemiaobati/genaiwebapplication.git"
readonly PROJECT_DIR_NAME="genaiwebapplication"
readonly PROJECT_PATH="/var/www/${PROJECT_DIR_NAME}"

readonly DB_NAME="server_codeigniter"
readonly DB_USER="ci4_user"

# --- Global Variables ---
DB_PASSWORD=""
ENCRYPTION_KEY=""
STEP_COUNT=10

# --- Helper Functions ---
log_step() {
    local step_number=$1
    local message=$2
    echo ""
    echo "--- [${step_number}/${STEP_COUNT}] ${message} ---"
}

# --- Installation and Configuration Functions ---

update_and_install_essentials() {
    log_step 1 "Updating system and installing essential utilities"
    apt-get update
    apt-get upgrade -y
    apt-get install -y openssl unzip git sudo nano perl pandoc ffmpeg 
}

generate_secure_credentials() {
    echo "Generating secure database password and encryption key..."
    DB_PASSWORD=$(openssl rand -base64 16)
    ENCRYPTION_KEY=$(openssl rand -base64 32) # A longer key for encryption
}

install_apache() {
    log_step 2 "Installing Apache2 Web Server"
    apt-get install -y apache2
}

install_php() {
    log_step 3 "Installing PHP 8.2 and required extensions"
    apt-get install -y software-properties-common
    add-apt-repository ppa:ondrej/php -y
    apt-get update
    apt-get install -y php8.2 php8.2-mysql php8.2-intl php8.2-mbstring \
                       php8.2-bcmath php8.2-curl php8.2-xml php8.2-zip php8.2-gd
}

install_and_configure_mysql() {
    log_step 4 "Installing and configuring MySQL"
    apt-get install -y mysql-server

    echo "Starting MySQL service..."
    service mysql start
    service mysql status

    echo "Waiting for MySQL service to become ready..."
    local max_tries=15
    local tries=0
    while ! mysqladmin ping -u root --silent; do
        tries=$((tries + 1))
        if [ "${tries}" -ge "${max_tries}" ]; then
            echo "ERROR: MySQL server did not respond after multiple attempts. Exiting."
            exit 1
        fi
        echo "MySQL not ready yet, waiting 2 seconds... (Attempt ${tries}/${max_tries})"
        sleep 2
    done
    echo "MySQL service is ready."

    echo "Creating database and user..."
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
    mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
    mysql -u root -e "FLUSH PRIVILEGES;"
    echo "[SUCCESS] Database '${DB_NAME}' and user '${DB_USER}' created."
}

install_composer() {
    log_step 5 "Installing Composer"
    local expected_checksum
    expected_checksum="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    local actual_checksum
    actual_checksum="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "${expected_checksum}" != "${actual_checksum}" ]; then
        >&2 echo 'ERROR: Invalid Composer installer checksum'
        rm composer-setup.php
        exit 1
    fi

    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
}

install_nodejs() {
    log_step 6 "Installing Node.js and NPM"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
}

clone_project() {
    log_step 7 "Cloning project from Git repository"
    if [ -d "${PROJECT_PATH}" ]; then
        echo "[WARNING] Project directory already exists. Skipping clone."
    else
        git clone "${GIT_REPO_URL}" "${PROJECT_PATH}"
    fi
}

configure_project() {
    log_step 8 "Setting up CodeIgniter project"
    cd "${PROJECT_PATH}"

    echo "Installing PHP dependencies..."
    composer install --no-dev --optimize-autoloader

    echo "Creating and configuring .env file..."
    create_env_file

    echo "Running database migrations..."
    php spark migrate
    php spark cache:clear 
    php spark optimize

    echo "Setting file permissions..."
    chown -R www-data:www-data "${PROJECT_PATH}"
    chmod -R 775 "${PROJECT_PATH}/writable"
}

create_env_file() {
    cat <<EOF > "${PROJECT_PATH}/.env"
#--------------------------------------------------------------------
# ENVIRONMENT (configured by setup script)
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP (configured by setup script)
#--------------------------------------------------------------------
app.baseURL = 'http://localhost:80'

#--------------------------------------------------------------------
# DATABASE (configured by setup script)
#--------------------------------------------------------------------
database.default.hostname = 127.0.0.1
database.default.database = ${DB_NAME}
database.default.username = ${DB_USER}
database.default.password = ${DB_PASSWORD}
database.default.DBDriver = MySQLi
database.default.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION (configured by setup script)
#--------------------------------------------------------------------
encryption.key = ${ENCRYPTION_KEY}

#--------------------------------------------------------------------
# PAYMENT GATEWAY CONFIGURATION (User Input Required)
#--------------------------------------------------------------------
PAYSTACK_SECRET_KEY=""
GEMINI_API_KEY=""

#--------------------------------------------------------------------
# RECAPTCHA CONFIGURATION (User Input Required)
#--------------------------------------------------------------------
recaptcha_siteKey=''
recaptcha_secretKey=''

#--------------------------------------------------------------------
# EMAIL CONFIGURATION (User Input Required for User/Pass)
#--------------------------------------------------------------------
email_fromEmail = ''
email_fromName = 'AFRIKENKID'
email_SMTPHost = 'smtp.gmail.com'
email_SMTPUser = ''
email_SMTPPass = ''
email_SMTPPort = 587
email_SMTPCrypto = 'tls'
EOF
}

configure_apache() {
    log_step 9 "Configuring Apache Virtual Host"
    local vhost_file="/etc/apache2/sites-available/${PROJECT_DIR_NAME}.conf"

    cat <<EOF > "${vhost_file}"
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

    a2ensite "${PROJECT_DIR_NAME}.conf"
    a2enmod rewrite
    a2dissite 000-default.conf

    echo "Restarting Apache to apply changes..."
    service apache2 restart
}

final_summary() {
    log_step 10 "Setup Complete!"
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
    echo "   nano ${PROJECT_PATH}/.env"
    echo "3. (Optional) For HTTPS, install Certbot: apt install certbot python3-certbot-apache && certbot --apache"
    echo "============================================================"
}


# --- Main Execution ---
main() {
    # Goal 3: Enforce root execution and clarify sudo usage
    if [[ "${EUID}" -ne 0 ]]; then
        echo "ERROR: This script must be run with sudo or as the root user."
        exit 1
    fi

    update_and_install_essentials
    generate_secure_credentials
    install_apache
    install_php
    install_and_configure_mysql
    install_composer
    install_nodejs
    clone_project
    configure_project
    configure_apache
    final_summary
}

main "$@"
