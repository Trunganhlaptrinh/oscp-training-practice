<?php
/*
 * LAB 4 - CODE INJECTION qua eval() (CWE-95, OWASP A03, ATT&CK T1059)
 * ------------------------------------------------------------------
 * Kịch bản: "máy tính" nhận biểu thức rồi eval() để tính.
 * LỖI: eval() thực thi mọi mã PHP người dùng gửi -> RCE trực tiếp.
 *
 * KHAI THÁC (PoC):
 *   expr = system('id')
 *   expr = phpinfo()
 *   expr = file_get_contents('/etc/passwd')
 *   expr = 1; system('whoami');
 *
 * Lưu ý: cùng họ nguy hiểm với eval(): assert(), create_function(),
 *        preg_replace('/./e', ...), call_user_func($_GET[..]) ...
 *
 * CÁCH VÁ (nhánh 'safe'):
 *   - KHÔNG dùng eval cho dữ liệu người dùng. Parse biểu thức bằng bộ phân tích an toàn
 *     (chỉ cho số và + - * / ( )), hoặc dùng thư viện math an toàn.
 */

$mode = $_GET['mode'] ?? 'vuln';
$expr = $_REQUEST['expr'] ?? '';
$out  = '';

if ($expr !== '') {
    if ($mode === 'safe') {
        // --- Bản vá: chỉ cho ký tự toán học, tự tính bằng bộ parse giới hạn ---
        if (!preg_match('/^[0-9+\-*\/().\s]+$/', $expr)) {
            $out = 'Chỉ cho phép số và + - * / ( ).';
        } else {
            // dùng eval nhưng CHỈ sau khi allow-list tuyệt đối ký tự (an toàn cho demo);
            // production nên dùng parser riêng (shunting-yard) thay vì eval.
            try { $out = @eval("return ($expr);"); }
            catch (\Throwable $e) { $out = 'Biểu thức lỗi.'; }
        }
    } else {
        // === LỖI CỐ Ý: eval thẳng chuỗi người dùng ===
        $out = eval("return ($expr);");
    }
}
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>eval calc</title>
<style>body{font-family:Arial;max-width:760px;margin:30px auto}pre{background:#111;color:#0f0;padding:12px}</style></head><body>
<h2>Lab 4 — Máy tính (Code Injection qua eval)</h2>
<p>Chế độ: <b><?= htmlspecialchars($mode) ?></b> — <a href="?mode=vuln">vuln</a> | <a href="?mode=safe">safe</a></p>
<form method="post" action="?mode=<?= htmlspecialchars($mode) ?>">
  Biểu thức: <input name="expr" size="40" value="<?= htmlspecialchars($expr) ?>" placeholder="1+2*3  |  system('id')">
  <button>Tính</button>
</form>
<?php if ($expr !== ''): ?><h3>Kết quả:</h3><pre><?= htmlspecialchars(var_export($out, true)) ?></pre><?php endif; ?>
<p><a href="index.php">&larr; Menu</a></p>
</body></html>
