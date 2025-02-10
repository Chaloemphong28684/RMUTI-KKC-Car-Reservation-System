<?php
session_start();
include 'db.php';

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    $vehicle_license = $_POST['vehicle_license'];
    $driver_email = $_POST['driver_email'];

    // อัปเดตข้อมูลในตาราง booking_status
    $update_query = $conn->prepare("UPDATE booking_status SET vehicle_number = ?, driver_name = (SELECT CONCAT(first_name, ' ', last_name) FROM driver_profile WHERE email = ?) WHERE booking_id = ?");
    $update_query->bind_param("ssi", $vehicle_license, $driver_email, $booking_id);

    if ($update_query->execute()) {
        echo "ข้อมูลการจองถูกอัปเดตสำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูลการจอง: " . $update_query->error;
    }

    $update_query->close();
}

$conn->close();
