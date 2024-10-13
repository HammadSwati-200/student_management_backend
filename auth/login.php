<?php
include '../config/connect.php';
require_once '../vendor/jwt/JWT.php';
require_once '../vendor/jwt/Key.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secret_key = "YOUR_SECRET_KEY";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Generate JWT token with role-based information
            $payload = [
                "iss" => "http://localhost",
                "aud" => "http://localhost",
                "iat" => time(),
                "exp" => time() + (60 * 60), // 1-hour expiration
                "data" => [
                    "id" => $user['id'],
                    "username" => $user['username'],
                    "role" => $user['role']  // Include user role in the token
                ]
            ];

            $jwt = JWT::encode($payload, $secret_key, 'HS256');

            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "token" => $jwt,
                "role" => $user['role']  // Return the role to the frontend
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }

    $conn->close();
}
