<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_vehicle_booking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
$request_id = $_POST['request_id'];
$vehicle_id = $_POST['vehicle_id'];
$driver_id = $_POST['driver_id'];

// Update booking request with vehicle and driver
$stmt = $conn->prepare("UPDATE booking_requests SET vehicle_id = ?, driver_id = ?, status = 'Approved' WHERE id = ?");
$stmt->bind_param("iii", $vehicle_id, $driver_id, $request_id);

if ($stmt->execute()) {
    header("Location: admin_requests.php?success=1");
    exit();
} else {
    die("Error updating booking: " . $stmt->error);
}
