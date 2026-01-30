## Chuyển Terminal về Public Folder
```bash
cd public`

## Download wordpress.zip
```bash
curl -o wordpress.zip https://wordpress.org/latest.zip`

## Unzip WordPress
tar -xf wordpress.zip

# Đưa WordPress ra web root
Move-Item wordpress\* .
Remove-Item wordpress -Recurse
Remove-Item wordpress.zip


## Tạo config mẫu:
```bash
copy wp-config-sample.php wp-config.php`

Sửa wp-config.php trong VS Code

# Khai báo dữ liệu database
define('DB_NAME', 'blog');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'admin');
define('DB_HOST', '127.0.0.1');

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
# Chèn thêm Debug ở cuối file
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

Kiểm tra version và cài đặt wordpress trên local:
php -S localhost:8000 -t public




Tạo Dummy Theme: Khi git pull mã nguồn, chỉ có phần wp-content là phần cho phép tùy chỉnh để fit với các custom features, nhưng localhost sẽ báo lỗi cài đặt lần đầu khi trong mã nguồn có các custom features mà không có theme từ trước, do đó sẽ cần tạo folder wp-content/themes/_dummy để có thể vào được UI cài đặt
style.css: WordPress chỉ check file này nên đây là bắt buộc, chỉ cần có Theme Name là WordPress sẽ nhận mà không cần CSS thật
/*
Theme Name: Dummy
Theme URI: https://internal
Author: Internal
Description: Non UI Bootstrap theme to satisfy WordPress requirements
Version: 0.1.0
Text Domain: _dummy
*/

index.php: WordPress sẽ fallback render khi không tìm thấy các templates khác nên file này là bắt buộc
<?php
// Silence is golden.

functions.php: WordPress không bắt buộc phải có file này nhưng nên có để tránh edge-case và dễ Debug nếu cần
<?php
/**
 * Core Dummy Theme
 *
 * Infrastructure-only theme.
 * Do not put UI or business logic here.
 */

