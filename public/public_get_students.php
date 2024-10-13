<?php
include '../config/connect.php';

// This is a public API, so no authentication is required.

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Get filter parameters
    $age_filter = isset($_GET['age']) ? intval($_GET['age']) : null; // Filter by age (integer)
    $class_filter = isset($_GET['class']) ? mysqli_real_escape_string($conn, trim($_GET['class'])) : null; // Filter by class
    $sort_order = isset($_GET['sort']) && in_array($_GET['sort'], ['asc', 'desc']) ? $_GET['sort'] : 'asc'; // Sort order

    // Base SQL query
    $sql = "SELECT * FROM students WHERE 1=1"; // Start with a base query

    // Apply filters based on provided parameters
    if ($age_filter !== null) {
        $sql .= " AND age = $age_filter";
    }
    if ($class_filter) {
        $sql .= " AND class = '$class_filter'";
    }

    // Apply sorting
    $sql .= " ORDER BY name $sort_order";

    $result = $conn->query($sql);
    $students = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }

    echo json_encode(["status" => "success", "students" => $students]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
