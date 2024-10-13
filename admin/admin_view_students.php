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

            // Fetch all students from the database
            $sql = "SELECT * FROM students";
            $result = $conn->query($sql);

            $students = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $students[] = $row;
                }
            }

            echo json_encode(["status" => "success", "students" => $students]);
        } else {
            echo json_encode(["status" => "error", "message" => "Access denied. Admins only."]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Access denied. Invalid token."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No token provided"]);
}
