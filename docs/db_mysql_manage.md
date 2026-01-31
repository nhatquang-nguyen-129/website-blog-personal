# Login vào MySQL trên Windows PowerShell
```bash
cd "C:\MySQL\MySQL Server 8.0\bin"
.\mysql -u admin -p
```

# Kiểm tra danh sách Database
```bash
SHOW DATABASES;
```

# Xóa Database cũ
```bash
DROP DATABASE put_your_former_database_name;
```

# Create new Database
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