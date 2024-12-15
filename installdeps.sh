#!/bin/bash

if ! command -v apt &>/dev/null; then
    echo "Error: This system is not compatible with 'apt'. Exiting."
    exit 1
fi

ask_yes_no() {
    local question="$1"
    local default="Y"
    local user_input

    read -p "$question (Y/n): " user_input
    user_input=${user_input:-$default}

    if [[ "$user_input" =~ ^[Yy]$ ]]; then
        return 0 # Yes
    else
        return 1 # No
    fi
}

echo "Updating the system..."
echo "This script avoid install any database options except php-mysqli"
sudo apt update && sudo apt upgrade -y

# Install Basic
if ask_yes_no "Do you want to install the basic deps (apache,php-fpm,...)"; then
    echo "Installing basic deps..."
    sudo apt install -y apache2 php-fpm php-mysqli php-curl php-mbstring

fi

# Install Composer
if ask_yes_no "Do you want to install Composer? (need phpmailer)"; then
    echo "Installing Composer..."
    sudo apt install -y composer

    # Install phpseclib
    #if ask_yes_no "Do you want to install phpseclib? (not necesary yet)"; then
    #    echo "Installing phpseclib with Composer..."
    #    composer require phpseclib/phpseclib
    #fi

    # Install PHPMailer
    if ask_yes_no "Do you want to install PHPMailer? (not necesary yet)"; then
        echo "Installing PHPMailer with Composer..."
        composer require phpmailer/phpmailer
    fi
fi

# Install Ansible
if ask_yes_no "Do you want to install Ansible? (not necesary yet)"; then
    echo "Installing Ansible..."
    sudo apt install -y ansible

    # Install Proxmox module
    if ask_yes_no "Do you want to install the Proxmox module for Ansible? (will install pip)"; then
        echo "Installing Proxmox module..."
        if ! command -v pip &>/dev/null; then
            echo "Pip is not installed. Installing pip..."
            sudo apt install -y python3-pip
        fi
        pip install proxmoxer
    fi

    # Install VMware module
    if ask_yes_no "Do you want to install the VMware module for Ansible? (will install pip)"; then
        echo "Installing VMware module..."
        if ! command -v pip &>/dev/null; then
            echo "Pip is not installed. Installing pip..."
            sudo apt install -y python3-pip
        fi
        pip install pyvmomi
    fi
fi

echo "Script completed."
