<?php
session_start();
include 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'driver') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['email'];

// เตรียมคำสั่ง SQL เพื่อดึงข้อมูลจากตาราง driver_profile
$stmt = $conn->prepare("SELECT first_name, last_name, phone_number, employee_id, license_number, profile_picture FROM driver_profile WHERE email = ?");
if (!$stmt) {
    die("การเตรียมคำสั่ง SQL ดึงข้อมูลล้มเหลว: " . $conn->error);
}

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
    <title>ข้อมูลส่วนตัวพนักงานขับรถ</title>
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

        .profile-info {
            background-color: white;
            margin-left: 350px;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 1000px;
        }

        .profile-info label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .profile-info p {
            margin: 10px 0;
        }

        .button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .button:hover {
            background-color: #003366;
        }

        .s1 {
            margin: 80px 0 20px 300px;
            color: #333;
            font-size: 18px;
        }

        .profile-picture {
            width: 150px;
            /* กำหนดความกว้าง */
            height: 170px;
            /* กำหนดความสูง */
            object-fit: cover;
            /* ทำให้ภาพไม่ยืดหรือบีบเกินไป */
            border-radius: 10px;
            box-shadow: 0 10px 12px rgba(0, 0, 0, 0.1);
        }
    </style>

    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="Logo">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="driver_dashboard.php">หน้าหลัก</a>
        <a href="driver_profile.php">ข้อมูลส่วนตัว</a>
        <a href="driver_confirmation.php">อนุมัติการขับรถ</a>
        <a href="driver_dashboard2.php">สถานะ/รายการต้องขับ</a>
        <a href="change_password3.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div>
        <h2 class="s1">ข้อมูลส่วนตัว</h2>
        <div class="profile-info">
            <?php if (isset($row)): ?>
                <label>อีเมล:</label>
                <p><?php echo htmlspecialchars($email); ?></p>

                <label>ชื่อ:</label>
                <p><?php echo htmlspecialchars($row['first_name']); ?></p>

                <label>นามสกุล:</label>
                <p><?php echo htmlspecialchars($row['last_name']); ?></p>

                <label>หมายเลขโทรศัพท์:</label>
                <p><?php echo htmlspecialchars($row['phone_number']); ?></p>

                <label>รหัสพนักงาน:</label>
                <p><?php echo htmlspecialchars($row['employee_id']); ?></p>

                <label>เลขใบอนุญาตขับรถ:</label>
                <p><?php echo htmlspecialchars($row['license_number']); ?></p>

                <label>ภาพโปรไฟล์:</label>
                <p>
                    <?php if ($row['profile_picture']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        ไม่มีภาพโปรไฟล์
                    <?php endif; ?>
                </p>


                <a href="edit_driver_profile.php" class="button">แก้ไขข้อมูลส่วนตัว</a>
            <?php else: ?>
                <p>ไม่พบข้อมูลพนักงานขับรถ</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>