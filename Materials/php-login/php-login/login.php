<?php
// Thông tin kết nối MySQL - sửa lại cho phù hợp
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = 'php_login';

$error   = '';
$success = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Kiểm tra dữ liệu rỗng
    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        // 2. Kết nối CSDL
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($conn->connect_error) {
            die('Kết nối CSDL thất bại: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');

        // 3. Truy vấn tìm user (dùng prepared statement để chống SQL Injection)
        $stmt = $conn->prepare('SELECT password FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);  
        $stmt->execute();
        $stmt->bind_result($hash);

        // 4. So khớp mật khẩu
        if ($stmt->fetch() && password_verify($password, $hash)) {
            $success = 'Đăng nhập thành công! Xin chào ' . htmlspecialchars($username) . '.';
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f2f2f2; }
        .box { width: 320px; margin: 80px auto; padding: 24px; background: #fff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h2 { text-align: center; margin-top: 0; }
        label { display: block; margin-top: 12px; }
        input[type=text], input[type=password] { width: 100%; padding: 8px; box-sizing: border-box; margin-top: 4px; }
        button { width: 100%; margin-top: 16px; padding: 10px; background: #2d7be5; color: #fff; border: 0; border-radius: 4px; cursor: pointer; }
        .error { color: #c0392b; margin-top: 12px; }
        .success { color: #27ae60; margin-top: 12px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Đăng nhập</h2>
    <form method="post" action="">
        <label>Tên đăng nhập
            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">
        </label>
        <label>Mật khẩu
            <input type="password" name="password">
        </label>
        <button type="submit">Đăng nhập</button>
    </form>
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
</div>
</body>
</html>
