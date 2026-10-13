<?php
// vuln/index.php - Menu các bài lab lỗ hổng dẫn tới RCE (chỉ dùng trong lab cục bộ)
$labs = [
    '1_command_injection.php' => ['OS Command Injection', 'Nối dữ liệu người dùng vào lệnh hệ thống (system/exec/shell_exec).'],
    '2_file_upload.php'       => ['Unrestricted File Upload', 'Cho upload file .php -> webshell -> RCE.'],
    '3_lfi.php'              => ['Local File Inclusion -> RCE', 'include() biến người dùng; log poisoning / php filter / data wrapper.'],
    '4_eval_calc.php'       => ['Code Injection (eval)', 'Đưa chuỗi người dùng thẳng vào eval().'],
    '5_unserialize.php'     => ['PHP Object Injection', 'unserialize() dữ liệu người dùng + magic method __destruct.'],
    '6_sqli_outfile.php'    => ['SQLi -> RCE (INTO OUTFILE)', 'Từ SQL Injection ghi webshell ra webroot.'],
];
?>
<!DOCTYPE html>
<html lang="vi"><head><meta charset="UTF-8"><title>Lab RCE</title>
<style>
body{font-family:Arial;max-width:760px;margin:40px auto;background:#f7f7f9;color:#222}
h1{font-size:22px}a{color:#2d7be5;text-decoration:none;font-weight:bold}
.card{background:#fff;border:1px solid #e3e3e8;border-radius:8px;padding:14px 18px;margin:10px 0}
.warn{background:#fff4e5;border:1px solid #ffcf8f;padding:10px 14px;border-radius:6px}
small{color:#666}
</style></head><body>
<h1>Lab lỗ hổng dẫn tới RCE</h1>
<p class="warn"><b>CẢNH BÁO:</b> Các trang dưới đây <u>cố ý</u> chứa lỗ hổng. Chỉ chạy trong lab cục bộ (Docker), không bao giờ deploy ra Internet.</p>
<?php foreach ($labs as $file => $info): ?>
  <div class="card">
    <a href="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($info[0]) ?></a>
    <div><small><?= htmlspecialchars($info[1]) ?></small></div>
  </div>
<?php endforeach; ?>
<p><small>Quay lại <a href="/login.php">login an toàn</a> | <a href="/login_basic.php">login dính SQLi</a></small></p>
</body></html>
