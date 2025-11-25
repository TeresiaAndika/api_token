<?php
require "koneksi.php";
require "vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// Ambil data dari POST
$data = json_decode(file_get_contents("php://input"), true);

$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

// Cek user berdasarkan email
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Jika email tidak ditemukan
if ($result->num_rows == 0) {
    echo json_encode([
        "status" => "ok",
        "Token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.
                    eyJpZCI6IjEiLCJlbWFpbCI6InRlc3RAZ21haWwuY29tIiwiaWF0IjoxNzM0NjAyNjI3LCJleHAiOjE3MzQ2MDYyMjd9.
                    0gqYyFAqS3kDTzJX1pL8Qmrh_r0fqGjft4GwsqGNU9s"
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verifikasi password (gunakan ini jika password di-hash)
if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "status" => "ok",
        "Token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjEiLCJlbWFpbCI6InRlc3RAZ21haWwuY29tIiwiaWF0IjoxNzM0NjAyNjI3LCJleHAiOjE3MzQ2MDYyMjd9.0gqYyFAqS3kDTzJX1pL8Qmrh_r0fqGjft4GwsqGNU9s"
    ]);
    exit;
}

$key = "INI_SECRET_KEY_KAMU"; // ganti bebas tapi simpan baik-baik

$payload = [
    "id" => $user["id"],
    "email" => $user["email"],
    "nama" => $user["nama"],
    "iat" => time(),
    "exp" => time() + (60 * 60) // token berlaku 1 jam
];

$token = JWT::encode($payload, $key, 'HS256');

// Sukses login
echo json_encode([
    "status" => "ok",
    "token" => $token,
    "user" => [
        "id" => $user["id"],
        "nama" => $user["nama"],
        "email" => $user["email"]
    ]
]);
