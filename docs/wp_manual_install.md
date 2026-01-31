# WordPress Setup on Windows with CLI-first

This document describes how to set up **WordPress** on Windows with a focus on:

- Lightweight installation
- Minimal GUI usage
- Suitable for WordPress custom feature

---

## 1. Install WordPress

### 1.1. Download wordpress.zip

- Redirect `path` to public folder which contains WordPress source code written in PHP
```bash
cd public
```

- Download `lastest version` from `wordpress.org`
```bash
curl -o wordpress.zip https://wordpress.org/latest.zip
```

### 1.2. Relocate to source code folder without overriding custom plugins

Why this step exists:

- WordPress ships with its own wp-content

- Your repo already has wp-content/mu-plugins

- Blindly moving files will overwrite your custom code

So we copy only core files, not wp-content.

- Unzip downloaded WordPress folder
```bash
tar -xf wordpress.zip
```

- Merge mu-plugins from GitHub
```bash
Copy-Item -Recurse -Force wp-content\mu-plugins wordpress\wp-content\
```

- Cleanup after mẻ:
```bash
Move-Item wordpress\* .
Remove-Item wordpress.zip
Remove-Item wordpress -Recurse -Force
```

- Test location after merging
```bash
Test-Path wp-content\mu-plugins
Test-Path wp-content\themes
Test-Path wp-content\plugins
```

## 2. Config WordPress

### 2.1. Config WordPress database using MySQL

- Copy `wp-config.php` from `wp-config-sample.php` template
```bash
copy wp-config-sample.php wp-config.php
```

- Config MySQL account in `wp-config.php`
```bash
define('DB_NAME', 'blog');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'admin');
define('DB_HOST', '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
```
- Config Debug mode
```bash
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```

### 2.2. Setup WordPress with UI

- Verfity WordPress version and go to setup UI:
```bash
php -S localhost:8000 -t public
```

