<?php
include '../config/connect.php';
require_once '../vendor/jwt/JWT.php';
require_once '../vendor/jwt/Key.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$secret_key = "YOUR_SECRET_KEY";

// CORS Headers (if needed)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
                $father_name = mysqli_real_escape_string($conn, trim($_POST['father_name']));
                $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
                $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
                $gender = mysqli_real_escape_string($conn, trim($_POST['gender']));
                $age = intval($_POST['age']);
                $class = mysqli_real_escape_string($conn, trim($_POST['class']));
                $bio = mysqli_real_escape_string($conn, trim($_POST['bio']));
                $address = mysqli_real_escape_string($conn, trim($_POST['address']));
                $enrollment_date = mysqli_real_escape_string($conn, trim($_POST['enrollment_date']));

                // Handle file upload
                $profile_picture = null;
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                    $target_dir = "../uploads/"; // Directory to save the image
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
                    }
                    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check if the file is an actual image
                    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
                    if ($check !== false) {
                        // Save the file
                        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                            $profile_picture = basename($_FILES["profile_picture"]["name"]); // Store the filename
                        } else {
                            echo json_encode(["status" => "error", "message" => "Failed to upload profile picture."]);
                            exit();
                        }
                    } else {
                        echo json_encode(["status" => "error", "message" => "File is not an image."]);
                        exit();
                    }
                }

                // Insert student details into the database
                $sql = "INSERT INTO students (name, father_name, email, phone, gender, age, class, bio, address, enrollment_date, profile_picture) 
                        VALUES ('$name', '$father_name', '$email', '$phone', '$gender', '$age', '$class', '$bio', '$address', '$enrollment_date', '$profile_picture')";
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
