<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_vehicle_booking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = $_POST['request_id'];
    $vehicle_id = $_POST['vehicle_id'];
    $driver_id = $_POST['driver_id'];

    // Update booking with selected vehicle and driver
    $stmt = $conn->prepare("UPDATE booking_requests SET vehicle_id = ?, driver_id = ?, status = 'Approved' WHERE id = ?");
    $stmt->bind_param("iii", $vehicle_id, $driver_id, $request_id);

    if ($stmt->execute()) {
        echo "<script>alert('การจองได้รับการยืนยันแล้ว'); window.location.href='admin_requests.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . $stmt->error . "'); window.history.back();</script>";
    }
}

$conn->close();
