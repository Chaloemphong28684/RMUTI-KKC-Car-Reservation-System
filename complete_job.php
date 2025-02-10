<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['type'] != 'driver') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];

    $sql = "UPDATE booking_requests SET job_completed = 'สิ้นสุดแล้ว' WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        header("Location: driver_dashboard2.php?success=completed");
    } else {
        header("Location: driver_dashboard2.php?error=failed");
    }
    exit();
}
