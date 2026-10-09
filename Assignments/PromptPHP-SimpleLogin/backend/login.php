<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (empty($_SESSION["user_id"]) || empty($_SESSION["username"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Bạn chưa đăng nhập"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $_SESSION["user_id"],
            "username" => $_SESSION["username"]
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Phương thức không được hỗ trợ"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once "config/database.php";

$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

if (trim($username) === "") {
    echo json_encode([
        "success" => false,
        "message" => "Username không được để trống"
    ]);
    exit;
}

if (trim($password) === "") {
    echo json_encode([
        "success" => false,
        "message" => "Password không được để trống"
    ]);
    exit;
}

$sql = "SELECT id, username FROM users WHERE username = ? AND password = ?";

$statement = $connection->prepare($sql);

if (!$statement) {
    echo json_encode([
        "success" => false,
        "message" => "Lỗi database"
    ]);
    exit;
}

$statement->bind_param("ss", $username, $password);
$statement->execute();

$result = $statement->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Username hoặc password không đúng"
    ]);
    exit;
}

$user = $result->fetch_assoc();

session_regenerate_id(true);
$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];

echo json_encode([
    "success" => true,
    "message" => "Đăng nhập thành công",
    "data" => [
        "id" => $user["id"],
        "username" => $user["username"]
    ]
], JSON_UNESCAPED_UNICODE);

$statement->close();
$connection->close();
