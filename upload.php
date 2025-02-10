<!-- upload.php -->
<form action="upload.php" method="post" enctype="multipart/form-data">
    <label for="file">เลือกไฟล์:</label>
    <input type="file" name="file" id="file" required>
    <input type="submit" value="อัปโหลดไฟล์">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $targetDir = "uploads/";  // โฟลเดอร์ที่เก็บไฟล์
    $targetFile = $targetDir . basename($_FILES['file']['name']);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo "ไฟล์ถูกอัปโหลดสำเร็จ";

        // เชื่อมต่อฐานข้อมูลและบันทึก URL ของไฟล์
        $fileUrl = $targetFile;
        $conn = new mysqli("localhost", "username", "password", "database_name");
        if ($conn->connect_error) {
            die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
        }
        $sql = "INSERT INTO files (file_name, file_url) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $_FILES['file']['name'], $fileUrl);
        $stmt->execute();
        echo "ข้อมูลไฟล์ถูกบันทึกในฐานข้อมูลเรียบร้อยแล้ว";
        $stmt->close();
        $conn->close();
    } else {
        echo "การอัปโหลดไฟล์ล้มเหลว";
    }
}
?>