<?php
// db.php

$servername = "localhost"; // เปลี่ยนเป็นโฮสต์ของฐานข้อมูลของคุณ
$username = "root"; // ชื่อผู้ใช้ของฐานข้อมูล
$password = ""; // รหัสผ่านของฐานข้อมูล
$dbname = "university_vehicle_booking"; // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
