<?php
include '../config/connect.php';  // Include your database connection
require_once '../vendor/jwt/JWT.php';
require_once '../vendor/jwt/Key.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secret_key = "YOUR_SECRET_KEY";

// CORS Headers (if needed)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$headers = apache_request_headers();
if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        // Check if the user is an admin or user
        if ($decoded->data->role == 'admin' || $decoded->data->role == 'user') {
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);

                // Fetch the student data
                $sql = "SELECT * FROM students WHERE id=$id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $student = $result->fetch_assoc();

                    // Return student data along with profile picture URL
                    $student['profile_picture'] = $student['profile_picture'] ? 'http://localhost/uploads/' . $student['profile_picture'] : null;
                    echo json_encode(["status" => "success", "student" => $student]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Student not found"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Student ID is required"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Access denied"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Access denied. Invalid token"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No token provided"]);
}
