# MySQL Setup on Windows with CLI-first

This document describes how to set up **MySQL** on Windows with a focus on:

- Lightweight installation
- Minimal GUI usage
- Suitable for WordPress custom feature

---

### 1. Install MySQL

### 1.1 Download MySQL Server

- Download `MySQL Community Server 8.0 (x64)` then choose `MySQL Installer for Windows (.msi)` in:
`https://dev.mysql.com/downloads/mysql/8.0.html`

- Run MySQL Installer and install `Server` only because no need for MySQL Workbench (GUI)

### 1.2. MySQL configuration

- Change Windows PowerShell path to MySQL bin folder:
```bash
cd "C:\MySQL\MySQL Server 8.0\bin"
```

- Initialize folder data
```bash
.\mysqld --initialize-insecure --datadir="C:\MySQL\MySQL Server 8.0\data"
```

- Install MySQL Server 8.0:
```bash
.\mysqld --install MySQL80
```

- Initialize MySQL80Service:
```bash
net start MySQL80
```

- Login to MySQL from Windows PowerShell
```bash
mysql -u root -p
```

- Create MySQL account with user `admin` and password `admin`:
```bash
CREATE USER 'admin'@'localhost'
IDENTIFIED WITH mysql_native_password
BY 'admin';
```

- Set local developer permission:
```bash
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

- Login to MySQL database with registered account:
```bash
cd "C:\MySQL\MySQL Server 8.0\bin"
.\mysql -u admin -p
```

- Create MySQL database for personal blog website:
```bash
# Create MySQL database
CREATE DATABASE blog
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
# Check existing database
SHOW DATABASES;
# Exit MySQL
exit
```