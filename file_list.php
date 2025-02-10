<!-- file_list.php -->
<?php
$conn = new mysqli("localhost", "username", "password", "database_name");
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$sql = "SELECT file_name, file_url FROM files";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p><a href='" . $row['file_url'] . "'>" . $row['file_name'] . "</a></p>";
    }
} else {
    echo "ยังไม่มีไฟล์ในฐานข้อมูล";
}

$conn->close();
?>