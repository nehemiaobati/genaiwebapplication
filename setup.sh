#!/bin/bash

#==============================================================================
# Comprehensive Setup Script for AI Studio (CodeIgniter 4) on Ubuntu
#==============================================================================
# DESCRIPTION:
# Automates the stack installation for the AI Studio application.
# Includes requirements for: Apache, MySQL, PHP, FFMpeg, Pandoc + LaTeX.
#
# HOW TO USE:
# 1. Save as setup.sh:   nano setup.sh
# 2. Make executable:    chmod +x setup.sh
# 3. Run with sudo:      sudo ./setup.sh
#==============================================================================

set -e

#==============================================================================
# Configuration
#==============================================================================
readonly GIT_REPO_URL="https://github.com/nehemiaobati/genaiwebapplication.git"
readonly PROJECT_DIR_NAME="genaiwebapplication"
readonly PROJECT_PATH="/var/www/${PROJECT_DIR_NAME}"

readonly DB_NAME="server_codeigniter"
readonly DB_USER="ci4_user"
readonly DOMAIN="afrikenkid.com"

# Global Vars
DB_PASSWORD=""
ENCRYPTION_KEY=""
STEP_COUNT=10

log_step() {
    echo ""
    echo "--- [${1}/${STEP_COUNT}] ${2} ---"
}

#==============================================================================
# Functions
#==============================================================================

update_and_install_essentials() {
    log_step 1 "Updating system and installing dependencies"
    
    # Add PHP repository
    apt-get update
    apt-get install -y software-properties-common
    add-apt-repository ppa:ondrej/php -y
    apt-get update

    # 1. Basic Utils
    apt-get install -y openssl unzip git sudo nano curl
    
    # 2. Multimedia & Document Processing (Crucial for your App)
    # ffmpeg: Required by FfmpegService.php
    # pandoc: Required by PandocService.php
    # texlive-xetex: Required by PandocService (--pdf-engine=xelatex)
    
    echo "Installing Multimedia and PDF engines (this may take a few minutes)..."
    apt-get install -y ffmpeg pandoc 
    
    # Optional: Heavy dependencies for PDF generation (XeLaTeX)
    # To enable: uncomment the line below
    # apt-get install -y texlive-xetex texlive-fonts-recommended lmodern
}

#==============================================================================
generate_secure_credentials() {
    echo "Generating secure credentials..."
    DB_PASSWORD=$(openssl rand -base64 16)
    ENCRYPTION_KEY=$(openssl rand -base64 32)
}

#==============================================================================
install_apache() {
    log_step 2 "Installing Apache2"
    apt-get install -y apache2
}

#==============================================================================
install_php() {
    log_step 3 "Installing PHP and Extensions"
    
    # 1. Get the version first
    apt-get install -y php
    # We do this before installing anything to ensure we target the right version
    PHP_V=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    echo "Detected PHP Version: $PHP_V"

    # 2. Install everything version-specific
    # This ensures mysqli, gd, and zip are matched exactly to your running PHP
    apt-get update
    apt-get install -y \
        "php${PHP_V}-mysql" \
        "php${PHP_V}-intl" \
        "php${PHP_V}-curl" \
        "php${PHP_V}-xml" \
        "php${PHP_V}-bcmath" \
        "php${PHP_V}-mbstring" \
        "php${PHP_V}-gd" \
        "php${PHP_V}-zip" \
        "php${PHP_V}-imagick" \
        "php${PHP_V}-sqlite3"
}

#==============================================================================
install_and_configure_mysql() {
    log_step 4 "Installing MySQL"
    apt-get install -y mysql-server
    service mysql start

    echo "Waiting for MySQL..."
    sleep 5

    echo "Configuring Database..."
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
    mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
    mysql -u root -e "FLUSH PRIVILEGES;"
}

#==============================================================================
install_composer() {
    log_step 5 "Installing Composer"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
}

#==============================================================================
install_nodejs() {
    log_step 6 "Installing Node.js (for frontend assets)"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
}

#==============================================================================
clone_project() {
    log_step 7 "Cloning Repository"
    if [ -d "${PROJECT_PATH}" ]; then
        echo "Directory exists. Pulling latest changes..."
        cd "${PROJECT_PATH}"
        git pull origin main || git pull origin master
    else
        git clone "${GIT_REPO_URL}" "${PROJECT_PATH}"
    fi
}

#==============================================================================
configure_project() {
    log_step 8 "Configuring Application"
    cd "${PROJECT_PATH}"

    echo "Installing PHP Dependencies..."
    # Ensure composer installs dependencies required by your code (dompdf, php-ffmpeg, nlp-tools)
    composer install --no-dev --optimize-autoloader

    if [ ! -f "${PROJECT_PATH}/.env" ]; then
        echo "Creating .env file..."
        create_env_file
    else
        echo ".env file already exists. Skipping creation to preserve your keys."
    fi

    echo "Setting up Directory Permissions..."
    # Create specific directories required by your Controllers/Services
    mkdir -p "${PROJECT_PATH}/writable/uploads/gemini_temp"
    mkdir -p "${PROJECT_PATH}/writable/uploads/ttsaudio_secure"
    mkdir -p "${PROJECT_PATH}/writable/uploads/pandoc_temp"
    mkdir -p "${PROJECT_PATH}/writable/uploads/dompdf_temp"
    mkdir -p "${PROJECT_PATH}/writable/uploads/generated" # For MediaGenerationService.php
    mkdir -p "${PROJECT_PATH}/writable/nlp" # For TrainingService.php models
    mkdir -p "${PROJECT_PATH}/writable/backups" # For db:backup command
    mkdir -p "${PROJECT_PATH}/writable/session"
    mkdir -p "${PROJECT_PATH}/writable/cache"
    mkdir -p "${PROJECT_PATH}/writable/logs"

    # Set Ownership to web server user
    chown -R www-data:www-data "${PROJECT_PATH}"
    
    # Set Permissions
    find "${PROJECT_PATH}" -type f -exec chmod 644 {} \;
    find "${PROJECT_PATH}" -type d -exec chmod 755 {} \;
    chmod -R 775 "${PROJECT_PATH}/writable"
    chmod -R 775 "${PROJECT_PATH}/public"
    
    echo "Running Migrations..."
    #==============================================================================
    # NOTE: We run migrations as root here for simplicity. 
    # File permissions are fixed in previous steps, but double-check ownership 
    # if you run into permission issues later.
    #==============================================================================
    
    # Run all migrations including modules
    php spark migrate --all
    
    echo "Optimizing for Production..."
    php spark optimize
    
    #==============================================================================
    # Optional Seeding
    #==============================================================================
    # If you wish to seed the database immediately, uncomment the line below:
    # php spark db:seed AdminUserSeeder
    
    php spark cache:clear

    echo "Optimizing Session Table (Critical Fix)..."
    mysql -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "ALTER TABLE ci_sessions MODIFY data MEDIUMBLOB;"
}

#==============================================================================
create_env_file() {
    # Updated based on the keys found in your provided PHP code
    cat <<EOF > "${PROJECT_PATH}/.env"
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost'
# If you have a domain, change localhost to your domain

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
session.driver = 'CodeIgniter\Session\Handlers\DatabaseHandler'
session.savePath = 'ci_sessions'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = 127.0.0.1
database.default.database = ${DB_NAME}
database.default.username = ${DB_USER}
database.default.password = ${DB_PASSWORD}
database.default.DBDriver = MySQLi
database.default.port = 3306

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = ${ENCRYPTION_KEY}

#--------------------------------------------------------------------
# EMAIL Configuration
#--------------------------------------------------------------------
email.fromEmail = '_@gmail.com'
email.fromName = ''
email.SMTPHost = 'smtp.gmail.com'
email.SMTPUser = '_@gmail.com'
email.SMTPPass = ''
email.SMTPPort = 587
email.SMTPCrypto = 'tls'
email.mailType = 'html'

#--------------------------------------------------------------------
# AI & API KEYS (Required for GeminiService.php)
#--------------------------------------------------------------------
GEMINI_API_KEY="" 
# ^^^ ENTER YOUR GOOGLE GEMINI API KEY ABOVE ^^^

OPENROUTER_API_KEY=""
# ^^^ ENTER YOUR OPENROUTER API KEY ABOVE ^^^

OLLAMA_BASE_URL = "http://localhost:11434"

#--------------------------------------------------------------------
# OTHER CONFIGS
#--------------------------------------------------------------------
PAYSTACK_SECRET_KEY=""
recaptcha_siteKey=""
recaptcha_secretKey=""
EOF
}

#==============================================================================
configure_apache() {
    log_step 9 "Configuring Apache vHost"
    local vhost_file="/etc/apache2/sites-available/${PROJECT_DIR_NAME}.conf"
    local ssl_vhost_file="/etc/apache2/sites-available/${PROJECT_DIR_NAME}-ssl.conf"
    
    # Domain handling: If DOMAIN is empty, use PROJECT_DIR_NAME
    local domain_name="${DOMAIN:-${PROJECT_DIR_NAME}}"
    local log_prefix="${domain_name%%.*}" # Extract brand name for logs (e.g. 'afrikenkid' from 'afrikenkid.com')

    # Port 80 Configuration
    cat <<EOF > "${vhost_file}"
<VirtualHost *:80>
    ServerAdmin webmaster@${domain_name}
    DocumentRoot ${PROJECT_PATH}/public
    ServerName http://${domain_name}

    <Directory ${PROJECT_PATH}/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${log_prefix}_error.log
    CustomLog \${APACHE_LOG_DIR}/${log_prefix}_access.log combined
</VirtualHost>
EOF

    # Port 443 (SSL) Configuration
    cat <<EOF > "${ssl_vhost_file}"
<VirtualHost *:443>
    ServerAdmin webmaster@${domain_name}
    DocumentRoot "${PROJECT_PATH}/public"
    ServerName https://${domain_name}

    #DirectoryIndex index.php index.html
    <Directory "${PROJECT_PATH}/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${log_prefix}_ssl_error.log
    CustomLog \${APACHE_LOG_DIR}/${log_prefix}_ssl_access.log common

    SSLEngine on
    SSLCertificateFile "${PROJECT_PATH}/ssl/certificate.crt"
    SSLCertificateKeyFile "${PROJECT_PATH}/ssl/private.key"
    SSLCertificateChainFile "${PROJECT_PATH}/ssl/ca_bundle.crt"
</VirtualHost>
EOF

    # Enable Modules and Sites
    a2enmod rewrite
    a2enmod ssl
    a2dissite 000-default.conf
    a2ensite "${PROJECT_DIR_NAME}.conf"
    a2ensite "${PROJECT_DIR_NAME}-ssl.conf"
    
    service apache2 restart
}

#==============================================================================
final_summary() {
    log_step 10 "Installation Complete"
    echo "============================================================"
    echo "SUCCESS! The AI Studio is installed."
    echo "============================================================"
    echo "Path: ${PROJECT_PATH}"
    echo "DB User: ${DB_USER}"
    echo "DB Pass: ${DB_PASSWORD}"
    echo ""
    echo "!!! IMPORTANT NEXT STEPS !!!"
    echo "1. Edit the .env file and add AI API KEYs (GEMINI_API_KEY, OPENROUTER_API_KEY):"
    echo "   nano ${PROJECT_PATH}/.env"
    echo "2. Optional: Install Ollama (for local AI):"
    echo "   curl -fsSL https://ollama.com/install.sh | sh"
    echo "3. Optional: Seed the admin account:"
    echo "   cd ${PROJECT_PATH} && php spark db:seed AdminUserSeeder"
    echo "4. If your app uses custom namespaces (e.g. App\Modules), ensure"
    echo "   composer.json is configured correctly and run 'composer dump-autoload'."
    echo "============================================================"
}

#==============================================================================
# Execution
#==============================================================================

if [[ "${EUID}" -ne 0 ]]; then
    echo "ERROR: Run as root (sudo ./setup.sh)"
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