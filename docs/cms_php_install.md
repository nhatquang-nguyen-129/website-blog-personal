# WordPress Setup on Windows with CLI-first

This document describes how to set up **PHP** on Windows with a focus on:

- Lightweight installation
- Minimal GUI usage
- Suitable for WordPress custom feature

---

## 1. Install PHP

### 1.1 Download PHP for Windows

- Download **Thread Safe / x64** PHP from:
`https://www.php.net/downloads.php?usage=web&os=windows&osvariant=windows-downloads&version=default`

- Extract it to:
`C:\php`

### 1.2 Config php.ini

- Find `php.ini-development` inside `C:\php` location

- Copy and rename it to `php.ini` then open it with Notepad or VSCode

- Enable extension directory by removing the semicolon at the begining of the line
```bash
extension_dir = "ext"
extension=curl
extension=mbstring
extension=mysqli
extension=openssl
extension=gd
extension=zip
extension=fileinfo
```

- If available, also enable:
```bash
extension=pdo_mysql
```

- Set timezone to avoid warnings:
```bash
date.timezone = Asia/Ho_Chi_Minh
```

- Set memory and upload settings:
```bash
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
```

### 2. Add PHP to Windows PATH

### 2.1 Open Environment Variables

- Press `Windows + R` then enter `sysdm.cpl` to open `System Properties`

- Go to `Advanced` tab then click `Environment Variables`

- Under `System Variables` select `Path` to `Edit` to `New` then add `C:\php`

### 2.2 Verify PHP installation

- Open Windows PowerShell then enter `php -v` and must return 
```bash
PHP 8.x.x (cli) ...
```