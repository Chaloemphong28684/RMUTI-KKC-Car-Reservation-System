<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$license_plate = $data['license_plate'];
$start_datetime = $data['start'];  // วันที่และเวลาเริ่มต้น
$end_datetime = $data['end'];  // วันที่และเวลาสิ้นสุด

// คำสั่ง SQL เพื่อเพิ่มข้อมูลการจองในตาราง calendar_bookings
$stmt = $conn->prepare("INSERT INTO calendar_bookings (license_plate, start_datetime, end_datetime) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $license_plate, $start_datetime, $end_datetime);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
}

$stmt->close();
$conn->close();
