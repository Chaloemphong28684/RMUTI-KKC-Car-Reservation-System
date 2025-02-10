<?php
session_start();
include 'db.php';

// ... (ตรวจสอบล็อกอิน)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    $vehicle_license = $_POST['vehicle_license'];
    $driver_email = $_POST['driver_email'];
    $driver_status = $_POST['driver_status']; // ถ้ามี

    // ดึงชื่อคนขับ
    $stmt = $conn->prepare("SELECT first_name, last_name FROM driver_profile WHERE email = ?");
    $stmt->bind_param("s", $driver_email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $driver_name = $row['first_name'] . " " . $row['last_name'];
    }

    // บันทึกข้อมูลลงในตาราง booking_status
    $stmt = $conn->prepare("INSERT INTO booking_status (booking_id, vehicle_number, driver_name, driver_status, booking_status) VALUES (?, ?, ?, ?, 'รออนุมัติ')");
    $stmt->bind_param("issss", $booking_id, $vehicle_license, $driver_name, $driver_status);
    $stmt->execute();

    header("Location: admin_status.php");
    exit();
}
