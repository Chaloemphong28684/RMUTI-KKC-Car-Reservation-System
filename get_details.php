<?php
session_start();
include 'database.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    $stmt = $conn->prepare("SELECT * FROM booking_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // ส่งข้อมูลเป็น JSON
    } else {
        echo json_encode([]);
    }

    $stmt->close();
}

$conn->close();
