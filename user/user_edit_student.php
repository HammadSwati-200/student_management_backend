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
        // Decode the JWT token to verify the user
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        $user_id = $decoded->data->id;  // Get the logged-in user's ID

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize inputs
            $username = mysqli_real_escape_string($conn, trim($_POST['username']));
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $profile_status = mysqli_real_escape_string($conn, trim($_POST['profile_status']));  // 'on' or 'off'

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(["status" => "error", "message" => "Invalid email format"]);
                exit;
            }

            // Check if username is unique (but ignore the current user's username)
            $checkQuery = "SELECT * FROM users WHERE username='$username' AND id != $user_id";
            $result = $conn->query($checkQuery);

            if ($result->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "Username already exists"]);
            } else {
                // Update the logged-in user's profile
                $sql = "UPDATE users SET username='$username', email='$email', profile_status='$profile_status' WHERE id=$user_id";
                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
                }
            }
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Access denied. Invalid token."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No token provided"]);
}
