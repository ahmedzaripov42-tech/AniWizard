#!/bin/bash

DB_DIR="/home/runner/workspace/db_data"
SOCKET="/tmp/mysql.sock"
BOT_DIR="/home/runner/workspace/bot"

echo "=== MariaDB ishga tushirilmoqda ==="

# MariaDB data directoriyasini boshlash (agar yo'q bo'lsa)
if [ ! -d "$DB_DIR" ]; then
  echo "MariaDB data directoriyasi yaratilmoqda..."
  # --auth-root-socket-user=$(whoami) orqali runner unix foydalanuvchi root sifatida kirishi mumkin
  mysql_install_db \
    --user=$(whoami) \
    --datadir="$DB_DIR" \
    --auth-root-socket-user=$(whoami) \
    --skip-test-db > /tmp/mariadb_init.log 2>&1
  echo "MariaDB tayyor."
fi

# MariaDB ni ishga tushirish (agar ishlamayotgan bo'lsa)
if ! mysqladmin --socket="$SOCKET" ping --silent 2>/dev/null; then
  mysqld_safe --datadir="$DB_DIR" --socket="$SOCKET" --port=3306 --skip-name-resolve > /dev/null 2>&1 &
  MYSQL_PID=$!
  echo "MariaDB ishga tushmoqda (PID: $MYSQL_PID)..."

  for i in $(seq 1 30); do
    if mysqladmin --socket="$SOCKET" ping --silent 2>/dev/null; then
      echo "MariaDB tayyor!"
      break
    fi
    echo "MariaDB kutilmoqda... ($i/30)"
    sleep 1
  done
else
  echo "MariaDB allaqachon ishlamoqda."
fi

# Ma'lumotlar bazasini yaratish va sxemani import qilish
DB_INITIALIZED_FLAG="$DB_DIR/.initialized"
if [ ! -f "$DB_INITIALIZED_FLAG" ]; then
  echo "Ma'lumotlar bazasi sozlanmoqda..."
  
  # unix_socket plugin: runner unix foydalanuvchi sifatida (user flagsiz) ulaning
  MYSQL_CMD="mariadb --socket=$SOCKET"

  # Wizard bot foydalanuvchi va bazasini yaratish (localhost va 127.0.0.1 uchun)
  $MYSQL_CMD -e "
    CREATE DATABASE IF NOT EXISTS wizard_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    CREATE USER IF NOT EXISTS 'wizardbot'@'127.0.0.1' IDENTIFIED BY 'wizardbot123';
    CREATE USER IF NOT EXISTS 'wizardbot'@'localhost' IDENTIFIED BY 'wizardbot123';
    GRANT ALL PRIVILEGES ON wizard_bot.* TO 'wizardbot'@'127.0.0.1';
    GRANT ALL PRIVILEGES ON wizard_bot.* TO 'wizardbot'@'localhost';
    FLUSH PRIVILEGES;
  " 2>&1 && echo "Foydalanuvchi va baza yaratildi!"

  echo "Sxema import qilinmoqda..."
  $MYSQL_CMD wizard_bot < "$BOT_DIR/schema.sql" 2>&1 && echo "Sxema import qilindi!" || \
  mariadb --socket="$SOCKET" -u wizardbot -pwizardbot123 wizard_bot < "$BOT_DIR/schema.sql" 2>&1 && echo "Sxema import qilindi (wizardbot)!" || echo "Sxema import xatosi"

  touch "$DB_INITIALIZED_FLAG"
  echo "Ma'lumotlar bazasi tayyor!"
fi

# Zarur papkalarni yaratish
mkdir -p "$BOT_DIR/step"
mkdir -p "$BOT_DIR/tizim"

echo "=== PHP Bot Serveri port 5000 da ishga tushirilmoqda ==="
cd "$BOT_DIR"
php -S 0.0.0.0:5000 -t "$BOT_DIR" "$BOT_DIR/router.php" &
PHP_PID=$!
echo "PHP server ishga tushdi (PID: $PHP_PID) port 5000 da"

# Webhook ro'yxatdan o'tkazish
sleep 2
BOT_TOKEN="${BOT_TOKEN:-8832961212:AAEEDiqTSB6FgYECvvXDhaGlrxYtalezFL0}"
WEBHOOK_URL="https://${REPLIT_DEV_DOMAIN}/bot/bot.php"
echo "Webhook ro'yxatdan o'tkazilmoqda: $WEBHOOK_URL"
curl -s "https://api.telegram.org/bot${BOT_TOKEN}/setWebhook?url=${WEBHOOK_URL}" | cat
echo ""
echo "=== Bot ishlamoqda! ==="

wait $PHP_PID
