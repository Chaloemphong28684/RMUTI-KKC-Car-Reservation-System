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

        /* Main content */
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        .main-content h2 {
            font-size: 1.5em;
        }

        /* Form container */
        .form-container {
            background-color: white;
            margin-left: 700px;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
        }

        .form-container input[type="text"],
        .form-container input[type="phone"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .form-container button {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #003366;
        }

        .form-container .cancel-btn {
            background-color: #f44336;
            /* Red for cancel button */
        }

        .form-container .cancel-btn:hover {
            background-color: #d32f2f;
        }

        /* Message container */
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

        .s1 {
            margin-top: 80px;
            margin-left: 280px;
            margin-bottom: 20px;
            font-size: 19px;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="Logo">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมคลอีสาน วิทยาเขตขอนแก่น</h1>
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

    <div >
        <h2 class="s1">แก้ไขข้อมูลส่วนตัว</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'เรียบร้อย') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
            <?php if (strpos($message, 'เรียบร้อย') !== false): ?>
                <script>
                    setTimeout(function() {
                        window.location.href = 'faculty_profile.php';
                    }, 2000); // Redirect after 2 seconds
                </script>
            <?php endif; ?>
        <?php endif; ?>

        <div class="form-container">
            <form action="update_profile_faculty.php" method="POST">
                <label for="first_name">ชื่อ:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>

                <label for="last_name">นามสกุล:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>

                <label for="phone_number">หมายเลขโทรศัพท์:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>

                <label for="rank">ตำแหน่ง:</label>
                <input type="text" id="rank" name="rank" value="<?php echo htmlspecialchars($row['rank']); ?>" required>

                <button type="submit">ยืนยัน</button>
                <a href="faculty_profile.php">
                    <button type="button" class="cancel-btn">ยกเลิก</button>
                </a>
            </form>
        </div>
    </div>
</body>

</html>