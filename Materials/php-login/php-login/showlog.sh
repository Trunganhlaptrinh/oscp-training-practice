#!/usr/bin/env bash
# showlog.sh - Theo dõi câu SQL thật sự chạy trong DB của lab php-login (macOS)
# Cách dùng:
#   ./showlog.sh          -> theo dõi realtime query trên bảng members
#   ./showlog.sh on       -> bật general_log
#   ./showlog.sh off      -> tắt general_log
#   ./showlog.sh clear    -> xóa log cũ
#   ./showlog.sh all      -> xem 20 query gần nhất (mọi bảng)

cd "$(dirname "$0")" || exit 1
DB="docker compose exec -T db mysql -uroot -proot"

case "$1" in
  on)
    $DB -e "SET GLOBAL log_output='TABLE'; SET GLOBAL general_log='ON';" 2>/dev/null
    echo "[*] general_log = ON"
    ;;
  off)
    $DB -e "SET GLOBAL general_log='OFF';" 2>/dev/null
    echo "[*] general_log = OFF"
    ;;
  clear)
    $DB -e "TRUNCATE mysql.general_log;" 2>/dev/null
    echo "[*] Đã xóa log."
    ;;
  all)
    $DB -e "SELECT event_time, argument FROM mysql.general_log WHERE command_type='Query' ORDER BY event_time DESC LIMIT 20;" 2>/dev/null
    ;;
  *)
    # Mặc định: bật log rồi theo dõi realtime các query chạm bảng members
    $DB -e "SET GLOBAL log_output='TABLE'; SET GLOBAL general_log='ON';" 2>/dev/null
    echo "[*] Đang theo dõi query trên bảng 'members'. Ctrl+C để dừng."
    while true; do
      clear
      echo "=== SQL chạm bảng members (realtime) ==="
      $DB -N -e "SELECT event_time, argument FROM mysql.general_log WHERE command_type='Query' AND argument LIKE '%members%';" 2>/dev/null
      sleep 1
    done
    ;;
esac
