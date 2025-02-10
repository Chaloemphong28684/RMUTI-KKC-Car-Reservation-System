<?php
session_start();
include 'db.php'; // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบว่า user ได้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['email'])) {
    header('Location: login.php'); // หากไม่ได้ล็อกอิน ให้ไปที่หน้าหลัก
    exit();
}

// ดึงข้อมูลจากฐานข้อมูลเกี่ยวกับคนขับ
$email = $_SESSION['email'];
$sql = "SELECT first_name, last_name FROM driver_profile WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// ตั้งค่าชื่อที่จะแสดง
$display_name = $row['first_name'] . ' ' . $row['last_name'];

// การเปลี่ยนรหัสผ่าน
$message = ""; // ตัวแปรเก็บข้อความแจ้งเตือน
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_password'], $_POST['confirm_new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        // ตรวจสอบว่ารหัสผ่านใหม่และยืนยันรหัสผ่านใหม่ตรงกัน
        if ($new_password === $confirm_new_password) {
            // การเข้ารหัสรหัสผ่านใหม่
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // อัปเดตรหัสผ่านในตาราง login
            $update_stmt = $conn->prepare("UPDATE login SET password = ? WHERE email = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("ss", $hashed_password, $email);
                if ($update_stmt->execute()) {
                    if ($update_stmt->affected_rows > 0) {
                        $message = "รหัสผ่านถูกเปลี่ยนเรียบร้อยแล้ว";
                    } else {
                        $message = "ไม่มีการเปลี่ยนแปลงข้อมูล";
                    }
                } else {
                    $message = "เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน: " . $conn->error;
                }
                $update_stmt->close();
            } else {
                $message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error;
            }
        } else {
            $message = "รหัสผ่านใหม่กับการยืนยันรหัสผ่านไม่ตรงกัน";
        }
    } else {
        $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เปลี่ยนรหัสผ่าน</title>
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

        .password-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-left: 650px;
            width: 320px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 100px;
        }

        .password-container label {
            display: block;
            font-size: 1em;
            color: #004080;
            margin-top: 10px;
            margin-bottom: 5px;
            text-align: left;
            width: 100%;
        }

        .password-container input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .password-container button {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .password-container button:hover {
            background-color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        .modal-content p {
            margin-bottom: 20px;
        }

        .close-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมคลอีสาน วิทยาเขตขอนแก่น</h1>
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

    <h1>เปลี่ยนรหัสผ่าน</h1>
    <div class="password-container">
        <form method="POST" action="">
            <label for="new_password">รหัสผ่านใหม่:</label>
            <input type="password" id="new_password" name="new_password" required><br>

            <label for="confirm_new_password">ยืนยันรหัสผ่านใหม่:</label>
            <input type="password" id="confirm_new_password" name="confirm_new_password" required><br>

            <button type="submit">เปลี่ยนรหัสผ่าน</button>
        </form>
    </div>

    <?php if ($message): ?>
        <div id="myModal" class="modal">
            <div class="modal-content">
                <p><?php echo $message; ?></p>
                <button class="close-btn" onclick="closeModal()">ตกลง</button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modal = document.getElementById("myModal");
            if (modal) {
                modal.style.display = "flex";
            }
        });

        function closeModal() {
            var modal = document.getElementById("myModal");
            if (modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>