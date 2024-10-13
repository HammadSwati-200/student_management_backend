<?php
include '../config/connect.php';
require_once '../vendor/jwt/JWT.php';
require_once '../vendor/jwt/Key.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secret_key = "YOUR_SECRET_KEY";

$headers = apache_request_headers();
if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        // Check if the user's role is 'admin'
        if ($decoded->data->role == 'admin') {

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $username = mysqli_real_escape_string($conn, trim($_POST['username']));
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $password = mysqli_real_escape_string($conn, trim($_POST['password']));
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = mysqli_real_escape_string($conn, trim($_POST['role']));  // Can be 'admin' or 'user'

                // Insert the user into the database
                $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', '$role')";
                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["status" => "success", "message" => "User added successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
                }
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Access denied. Admins only."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Access denied. Invalid token."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No token provided"]);
}
