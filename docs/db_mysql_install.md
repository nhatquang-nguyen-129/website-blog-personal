

### 3. Install MySQL
3.1 Download MySQL Server

Download MySQL Community Server 8.0 (x64)
→ Choose MySQL Installer for Windows (.msi)

⚠️ Do NOT choose:

Innovation

8.4

HeatWave

3.2 Run MySQL Installer

When the installer starts:

Installation type:

👉 Server only

Why:

No need for MySQL Workbench (GUI)

CLI-first workflow

Lightweight, fewer conflicts

3.3 Configure MySQL Server

Port: 3306

Authentication: Use Strong Password Encryption

Set a password for user root

4. MySQL Login & Configuration
4.1 Login to MySQL from PowerShell
mysql -u root -p


Enter the password you set during installation.

4.2 Change authentication method (if needed)
ALTER USER 'root'@'localhost'
IDENTIFIED WITH mysql_native_password
BY 'root1234!';


Apply changes:

FLUSH PRIVILEGES;

4.3 Verify login again

Exit MySQL:

EXIT;


Login again:

mysql -u root -p

5. Create WordPress Database
CREATE DATABASE wp_local
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


Verify:

SHOW DATABASES;

6. Important Notes

WordPress only needs one initial DB setup

Default theme (e.g. twentytwentyfive) is stored in wp_options

Changing themes later is done via WP Admin, not manual DB edits

If you see admin redirects or missing theme errors → drop DB and re-init

7. Recommended Workflow

PHP + MySQL: manual, stable installation

WordPress core: clean, untouched

Custom logic: features/ + mu-plugins/bootstrap.php

When things break strangely: reset DB > debug


If you want, next I can:

- Add **WordPress core install & `wp-config.php` doc**
- Add **1-command DB reset doc**
- Write **repo structure guidelines (WP + features)**
- Turn this into a **team onboarding README**
