<?php
session_start();
include 'db.php';

// ตรวจสอบล็อกอิน
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// ตรวจสอบว่ามี booking_id ใน URL หรือไม่
if (isset($_GET['booking_id']) && !empty($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    // ดึงข้อมูล booking_status (ถ้ามี)
    $stmt_status = $conn->prepare("SELECT booking_status FROM booking_status WHERE booking_id = ?");
    $stmt_status->bind_param("i", $booking_id);
    $stmt_status->execute();
    $result_status = $stmt_status->get_result();

    if ($result_status->num_rows > 0) {
        $row_status = $result_status->fetch_assoc();
        $current_status = $row_status['booking_status'];
    } else {
        $current_status = null; // ยังไม่มีข้อมูล booking_status
    }

    $stmt_status->close();

    // เตรียมคำสั่ง SQL สำหรับอัปเดต booking_requests
    $stmt_request = $conn->prepare("UPDATE booking_requests SET status = ? WHERE booking_id = ?");
    if ($stmt_request === false) {
        die('MySQL prepare error (requests): ' . $conn->error);
    }

    // เตรียมคำสั่ง SQL สำหรับอัปเดตหรือเพิ่ม booking_status
    if ($current_status !== null) {
        $stmt_booking = $conn->prepare("UPDATE booking_status SET booking_status = ? WHERE booking_id = ?");
        if ($stmt_booking === false) {
            die('MySQL prepare error (status update): ' . $conn->error);
        }
    } else {
        $stmt_booking = $conn->prepare("INSERT INTO booking_status (booking_id, booking_status) VALUES (?, ?)");
        if ($stmt_booking === false) {
            die('MySQL prepare error (status insert): ' . $conn->error);
        }
    }

    // ผูกพารามิเตอร์และกำหนดสถานะ
    if (isset($_GET['status'])) { // ตรวจสอบว่ามีการส่ง status มาใน URL หรือไม่
        $status = $_GET['status'];
    } else {
        $status = 'Approved'; // ถ้าไม่ระบุ status ให้ default เป็น 'Approved'
    }

    $stmt_request->bind_param("si", $status, $booking_id);
    if ($current_status !== null) {
        $stmt_booking->bind_param("si", $status, $booking_id);
    } else {
        $stmt_booking->bind_param("is", $booking_id, $status);
    }

    // เรียกใช้งานคำสั่ง SQL
    $execute_result_request = $stmt_request->execute();
    $execute_result_booking = $stmt_booking->execute();

    if ($execute_result_request && $execute_result_booking) {
        // Redirect กลับไปยัง admin_status.php
        header("Location: admin_status.php");
        exit();
    } else {
        echo "Error executing query: " . $stmt_request->error . " / " . $stmt_booking->error;
    }

    // ปิดการเชื่อมต่อ
    $stmt_request->close();
    $stmt_booking->close();
    $conn->close();
} else {
    echo "No booking ID provided or booking ID is empty.";
}
