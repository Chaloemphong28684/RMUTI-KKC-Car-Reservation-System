<!-- file_details.php -->
<?php
$fileId = $_GET['id'];  // รับ ID ของไฟล์จาก URL (เช่น file_details.php?id=1)

$conn = new mysqli("localhost", "username", "password", "database_name");
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$sql = "SELECT * FROM files WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $file = $result->fetch_assoc();
    echo "<h1>รายละเอียดของไฟล์: " . $file['file_name'] . "</h1>";
    echo "<p>URL ของไฟล์: <a href='" . $file['file_url'] . "'>" . $file['file_url'] . "</a></p>";
    echo "<p>ชื่อไฟล์: " . $file['file_name'] . "</p>";
    // แสดงข้อมูลอื่น ๆ ที่ต้องการ
} else {
    echo "ไม่พบข้อมูลไฟล์";
}

$stmt->close();
$conn->close();
?>