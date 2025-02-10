<?php
session_start();
include 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'faculty') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['email'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['first_name'], $_POST['last_name'], $_POST['phone_number'], $_POST['rank'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone_number = $_POST['phone_number'];
        $rank = $_POST['rank'];

        // อัปเดตข้อมูลส่วนตัว
        $stmt = $conn->prepare("UPDATE user_profile SET first_name = ?, last_name = ?, phone_number = ?, rank = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("sssss", $first_name, $last_name, $phone_number, $rank, $email);
            if ($stmt->execute()) {
                $message = "ข้อมูลส่วนตัวถูกแก้ไขเรียบร้อยแล้ว";
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

$stmt = $conn->prepare("SELECT first_name, last_name, phone_number, rank, email FROM user_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// ตั้งค่าชื่อที่จะแสดง
if ($row) {
    $display_name = $row['first_name'] . ' ' . $row['last_name'];
} else {
    $display_name = 'ผู้ขอจองรถมหาวิทยาลัย';
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดอาจารย์/เจ้าหน้าที่</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 241, 241);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

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
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        .form-container {
            width: 50%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="date"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container button {
            width: 100%;
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .form-container button:hover {
            background-color: #003366;
        }

        .message {
            text-align: center;
            font-size: 16px;
            color: #333;
            margin-top: 20px;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
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

        .a1 {
            margin: 80px 0 20px 300px;
            color: #333;
            font-size: 18px
        }
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
        <a href="faculty_dashboard.php">หน้าหลัก</a>
        <a href="faculty_profile.php">ข้อมูลส่วนตัว</a>
        <a href="request_vehicle.php">คำขอจองรถ</a>
        <a href="user_booking_status.php">สถานะการจองรถ</a>
        <a href="change_password2.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div>
        <h2 class="a1">ข้อมูลส่วนตัว</h2>
        <div class="profile-info">
            <?php if (isset($row)): ?>
                <label>อีเมล:</label>
                <p><?php echo htmlspecialchars($row['email']); ?></p>

                <label>ชื่อ:</label>
                <p><?php echo htmlspecialchars($row['first_name']); ?></p>

                <label>นามสกุล:</label>
                <p><?php echo htmlspecialchars($row['last_name']); ?></p>

                <label>หมายเลขโทรศัพท์:</label>
                <p><?php echo isset($row['phone_number']) ? htmlspecialchars($row['phone_number']) : 'ไม่มีข้อมูล'; ?></p>

                <label>ตำแหน่ง:</label>
                <p><?php echo htmlspecialchars($row['rank']); ?></p>

                <a href="update_profile_faculty.php" class="button">แก้ไขโปรไฟล์</a>
            <?php else: ?>
                <p>ไม่มีข้อมูลโปรไฟล์</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>