#!/bin/bash

#==============================================================================
# Comprehensive Setup Script for AI Studio (CodeIgniter 4) on Ubuntu
#==============================================================================
# DESCRIPTION:
# Automates the stack installation for the AI Studio application.
# Includes requirements for: Apache, MySQL, PHP 8.2, FFMpeg, Pandoc + LaTeX.
#
# HOW TO USE:
# 1. Save as setup.sh:   nano setup.sh
# 2. Make executable:    chmod +x setup.sh
# 3. Run with sudo:      sudo ./setup.sh
#==============================================================================

set -e

# --- Configuration ---
readonly GIT_REPO_URL="https://github.com/nehemiaobati/genaiwebapplication.git"
readonly PROJECT_DIR_NAME="genaiwebapplication"
readonly PROJECT_PATH="/var/www/${PROJECT_DIR_NAME}"

readonly DB_NAME="server_codeigniter"
readonly DB_USER="ci4_user"

# Global Vars
DB_PASSWORD=""
ENCRYPTION_KEY=""
STEP_COUNT=10

log_step() {
    echo ""
    echo "--- [${1}/${STEP_COUNT}] ${2} ---"
}

# --- Functions ---

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
    #apt-get texlive-xetex texlive-fonts-recommended lmodern
}

generate_secure_credentials() {
    echo "Generating secure credentials..."
    DB_PASSWORD=$(openssl rand -base64 16)
    ENCRYPTION_KEY=$(openssl rand -base64 32)
}

install_apache() {
    log_step 2 "Installing Apache2"
    apt-get install -y apache2
}

install_php() {
    log_step 3 "Installing PHP 8.2 and Extensions"
    # Added specific extensions used in your provided code (intl, gd, curl, mbstring)
    apt-get install -y php8.2 php8.2-mysql php8.2-intl php8.2-mbstring \
                       php8.2-bcmath php8.2-curl php8.2-xml php8.2-zip php8.2-gd \
                       php8.2-imagick
}

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

install_composer() {
    log_step 5 "Installing Composer"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
}

install_nodejs() {
    log_step 6 "Installing Node.js (for frontend assets)"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
}

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

configure_project() {
    log_step 8 "Configuring Application"
    cd "${PROJECT_PATH}"

    echo "Installing PHP Dependencies..."
    # Ensure composer installs dependencies required by your code (dompdf, php-ffmpeg, nlp-tools)
    composer install --no-dev --optimize-autoloader

    echo "Creating .env file..."
    create_env_file

    echo "Setting up Directory Permissions..."
    # Create specific directories required by your Controllers/Services
    mkdir -p "${PROJECT_PATH}/writable/uploads/gemini_temp"
    mkdir -p "${PROJECT_PATH}/writable/uploads/ttsaudio_secure"
    mkdir -p "${PROJECT_PATH}/writable/uploads/pandoc_temp"
    mkdir -p "${PROJECT_PATH}/writable/uploads/dompdf_temp"
    mkdir -p "${PROJECT_PATH}/writable/nlp" # For TrainingService.php models
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
    # Run migrations as www-data to ensure created files have right permissions, 
    # OR run as root and fix permissions after. Running as root is easier in setup script.
    php spark migrate
    php spark migrate -all
    
    # If TrainingService needs a seed or initial training, strictly speaking it should be done here,
    # but we will leave that for manual execution or a seeder.
    
    php spark cache:clear
}

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

#--------------------------------------------------------------------
# OTHER CONFIGS
#--------------------------------------------------------------------
PAYSTACK_SECRET_KEY=""
recaptcha_siteKey=""
recaptcha_secretKey=""
EOF
}

configure_apache() {
    log_step 9 "Configuring Apache vHost"
    local vhost_file="/etc/apache2/sites-available/${PROJECT_DIR_NAME}.conf"

    cat <<EOF > "${vhost_file}"
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot ${PROJECT_PATH}/public
    ServerName localhost

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
    service apache2 restart
}

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
    echo "1. Edit the .env file and add your GEMINI_API_KEY:"
    echo "   nano ${PROJECT_PATH}/.env"
    echo "2. If your app uses custom namespaces (e.g. App\Modules), ensure"
    echo "   composer.json is configured correctly and run 'composer dump-autoload'."
    echo "============================================================"
}

# --- Execution ---

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