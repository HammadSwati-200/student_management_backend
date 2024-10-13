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

        // Check if the user is an admin
        if ($decoded->data->role == 'admin') {

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // Sanitize inputs
                $id = intval($_POST['id']);
                $username = mysqli_real_escape_string($conn, trim($_POST['username']));
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $role = mysqli_real_escape_string($conn, trim($_POST['role']));

                // Validate email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
                    exit;
                }

                // Check if username is unique for other users
                $checkQuery = "SELECT * FROM users WHERE username='$username' AND id != $id";
                $result = $conn->query($checkQuery);

                if ($result->num_rows > 0) {
                    echo json_encode(["status" => "error", "message" => "Username already exists"]);
                } else {
                    // Update user details
                    $sql = "UPDATE users SET username='$username', email='$email', role='$role' WHERE id=$id";
                    if ($conn->query($sql) === TRUE) {
                        echo json_encode(["status" => "success", "message" => "User updated successfully"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
                    }
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
