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
                $name = mysqli_real_escape_string($conn, trim($_POST['name']));
                $age = intval($_POST['age']);
                $class = mysqli_real_escape_string($conn, trim($_POST['class']));
                $contact_info = mysqli_real_escape_string($conn, trim($_POST['contact_info']));

                // Insert student details into the database
                $sql = "INSERT INTO students (name, age, class, contact_info) VALUES ('$name', '$age', '$class', '$contact_info')";
                if ($conn->query($sql) === TRUE) {
                    echo json_encode(["status" => "success", "message" => "Student added successfully"]);
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
