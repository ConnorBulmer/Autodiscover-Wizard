# Autodiscover Wizard (Beta)

A **one-click PHP script** to automate the generation and configuration of
**autodiscover.xml** and **autoconfig.xml** files on shared hosting (e.g. cPanel, DirectAdmin).
This script helps Outlook, Thunderbird, Apple Mail, and other email clients
auto-detect IMAP/SMTP settings without manual input.

---

## Table of Contents

1. [Features](#features)
2. [How It Works](#how-it-works)
3. [Requirements](#requirements)
4. [Installation](#installation)
5. [Usage](#usage)
6. [DNS Configuration](#dns-configuration)
7. [Security & Deletion](#security--deletion)
8. [License](#license)

---

## Features

- **Automatic Domain Detection**: The script picks up your domain from the server environment.
- **Generates XML Files**: Creates both `autodiscover.xml` (for Outlook) and `autoconfig.xml` (for Thunderbird and similar).
- **Automated .htaccess Updates**: Inserts rewrite rules to ensure requests go to the right files.
- **Idempotent**: Won’t duplicate entries in `.htaccess` if the rules are already present.
- **Zero Root Access Required**: Perfect for shared reseller hosting environments.

---

## How It Works

1. A user (or email client) attempts to find mail settings for `user@yourdomain.com`.
2. By default, Outlook/Thunderbird/Mail clients will check:
   - `https://autodiscover.yourdomain.com/autodiscover/autodiscover.xml`
   - `https://autoconfig.yourdomain.com/autoconfig`
   - DNS SRV/CNAME records (if set)
3. When these requests hit your server, the `.htaccess` rules redirect them to the generated XML files:
   - `autodiscover.xml`
   - `autoconfig.xml`
4. The client automatically configures incoming/outgoing mail using the script-defined settings.

---

## Requirements

1. **PHP 5.6+** (older versions might also work, but 5.6+ is recommended)
2. **Apache or LiteSpeed** server with `.htaccess` rewrite capabilities
3. **Write permissions** in your domain’s document root (`public_html/` or equivalent)

---

## Installation

1. **Download** or **clone** this repository.
2. **Upload** the file `setup_autoconfig.php` (or similarly named script) to your domain’s
   document root (`public_html/` on most shared hosts).
3. (Optional) **Back up** your existing `.htaccess` file if you have a heavily customised version.

---

## Usage

1. **Navigate** in your browser to:  
   `https://yourdomain.com/setup_autoconfig.php`
2. The script will:
   - Detect your domain name.
   - Generate `autodiscover.xml` and `autoconfig.xml`.
   - Create or modify `.htaccess` to add the necessary rewrite rules for auto-discovery.
3. **Check** the script’s output for success messages.
4. **Test** by opening your mail client and adding a new email account. It should auto-configure using:
   - IMAP (Port 993, SSL)
   - SMTP (Port 587, STARTTLS)

---

## DNS Configuration

For maximum compatibility, **add the following DNS records** for each domain you want to auto-configure:

autodiscover  CNAME  mail.yourdomain.com.
autoconfig    CNAME  mail.yourdomain.com.

_autodiscover._tcp  SRV  10 10 443  mail.yourdomain.com.
_imaps._tcp         SRV  0 1 993  mail.yourdomain.com.
_submission._tcp    SRV  0 1 587  mail.yourdomain.com.
_carddavs._tcp      SRV  0 1 443  mail.yourdomain.com.
_caldavs._tcp       SRV  0 1 443  mail.yourdomain.com.

> **Note:** Replace `yourdomain.com` with the actual domain.

---

## Security & Deletion

- **Delete** the `setup_autoconfig.php` script once everything is working. It’s only needed for initial setup.
- The generated `autodiscover.xml` and `autoconfig.xml` files do not contain any password details (only server settings), but it’s best practice to serve them over HTTPS.
  
---

## License

This project is licensed under the [MIT License](LICENSE).  
Feel free to fork, modify, and distribute it for your own use.

---

**Enjoy hassle-free mailbox auto-configuration on shared hosting!**
