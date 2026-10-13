<html>
<head>
    <meta charset="UTF-8">
    <title>Trang đăng nhập</title>
</head>
<body>
<?php
if (isset($_POST['login'])) { //GET 
    $username = $_POST['username'];
    $password = $_POST['password'];


    if ($username == "" || $password == "") {
        echo "Bạn chưa nhập tên đăng nhập hoặc mật khẩu!<br>";
    } else {
        $host = getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost';
        $pass = getenv('DB_PASS') ? getenv('DB_PASS') : '';
        $conn = mysqli_connect($host, 'root', $pass, 'php_login') or die("Không thể kết nối CSDL");
        mysqli_set_charset($conn, 'utf8');

        // CHÚ Ý: nối chuỗi trực tiếp -> có lỗi SQL Injection
        $sql = "SELECT * FROM members WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $sql); 

        if ($result && mysqli_num_rows($result) > 0) {
            echo "Đăng nhập thành công!<br>";
           // echo "Welcome " . $username . "<br>";
        } else {
            echo "Tài khoản " . $username . " không tồn tại!<br>";
        }
        mysqli_close($conn);
    }
}
?>
<form method="POST" action="login_basic.php">
    <table>
        <tr><td colspan="2"><h3>Đăng nhập</h3></td></tr>
        <tr><td>Tên đăng nhập:</td><td><input type="text" name="username"></td></tr>
        <tr><td>Mật khẩu:</td><td><input type="password" name="password"></td></tr>
        <tr><td></td><td><input type="submit" name="login" value="Đăng nhập"></td></tr>
    </table>
</form>
</body>
</html>
