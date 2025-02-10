<?php
session_start();
include 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'driver') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['email'];
$message = "";

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['first_name'], $_POST['last_name'], $_POST['phone_number'], $_POST['license_number'], $_POST['employee_id'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone_number = $_POST['phone_number'];
        $license_number = $_POST['license_number'];
        $employee_id = $_POST['employee_id'];

        // ตรวจสอบและอัปโหลดรูปภาพ
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['profile_picture']['name']);
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $target_file;
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
            }
        }

        // อัปเดตข้อมูลส่วนตัว
        $stmt = $conn->prepare("UPDATE driver_profile SET first_name = ?, last_name = ?, phone_number = ?, license_number = ?, employee_id = ?, profile_picture = IFNULL(?, profile_picture) WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("sssssss", $first_name, $last_name, $phone_number, $license_number, $employee_id, $profile_picture, $email);
            if ($stmt->execute()) {
                $message = "ข้อมูลส่วนตัวถูกแก้ไขเรียบร้อยแล้ว";
                echo "<script>setTimeout(() => { window.location.href = 'driver_profile.php'; }, 2000);</script>";
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $conn->error;
            }
            $stmt->close();
        } else {
            $message = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $conn->error;
        }
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

// ดึงข้อมูลเดิมจากฐานข้อมูล
$stmt = $conn->prepare("SELECT first_name, last_name, phone_number, license_number, employee_id, profile_picture FROM driver_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// ตั้งค่าชื่อที่จะแสดง
$display_name = $row['first_name'] . ' ' . $row['last_name'];
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 241, 241);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* แถบด้านบน (Header) */
        header {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-container img {
            height: 35px;
            margin-right: 10px;
        }

        .logo-container h1 {
            margin: 0;
            font-size: 1.2em;
            white-space: nowrap;
        }

        /* แถบด้านข้าง (Sidebar) */
        .sidebar {
            background-color: #f4f4f4;
            width: 200px;
            height: calc(100vh - 50px);
            position: fixed;
            top: 50px;
            left: 0;
            padding: 15px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar h2 {
            margin-bottom: 15px;
            font-size: 1em;
            text-align: center;
        }

        .sidebar a {
            text-decoration: none;
            color: #004080;
            display: block;
            padding: 8px 5px;
            font-size: 0.9em;
        }

        .sidebar a:hover {
            background-color: #e0e0e0;
            border-radius: 5px;
        }

        .main-content {
            margin-left: 360px;
            padding: 20px;
            width: 900px;
        }

        .profile-info {
            background-color: white;
            margin-left: 70px;
            margin-right: 70px;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
        }

        input {
            width: 97%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #003366;
        }

        .message {
            color: green;
        }

        .error {
            color: red;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .a1 {
            margin: 70px 0 10px ;
            margin-top: 70px;
            font-size: 19px;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="Logo">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="driver_dashboard.php">หน้าหลัก</a>
        <a href="driver_profile.php">ข้อมูลส่วนตัว</a>
        <a href="driving_approval.php">อนุมัติการขับรถ</a>
        <a href="driver_dashboard2.php">สถานะ/รายการต้องขับ</a>
        <a href="change_password3.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="main-content">
        <h2 class="a1">แก้ไขข้อมูลส่วนตัว</h2>
        <?php if ($message): ?>
            <p class="<?php echo strpos($message, 'ข้อผิดพลาด') === false ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="profile-info">
                <label>ชื่อ:</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>

                <label>นามสกุล:</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>

                <label>หมายเลขโทรศัพท์:</label>
                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>

                <label>เลขใบอนุญาตขับรถ:</label>
                <input type="text" name="license_number" value="<?php echo htmlspecialchars($row['license_number']); ?>" required>

                <label>รหัสพนักงาน:</label>
                <input type="text" name="employee_id" value="<?php echo htmlspecialchars($row['employee_id']); ?>" required>

                <label>รูปโปรไฟล์:</label>
                <input type="file" name="profile_picture" accept="image/*">

                <div class="button-container">
                    <button type="submit" class="button">ยืนยัน</button>
                    <a href="driver_profile.php" class="button" style="text-decoration: none; text-align: center;">ยกเลิก</a>
                </div>
            </div>
        </form>
    </div>
</body>

</html>