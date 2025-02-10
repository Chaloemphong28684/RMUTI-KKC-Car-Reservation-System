<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    // ถ้าไม่มีอีเมลในเซสชันให้กลับไปหน้ากรอกอีเมล
    header('Location: forgot_password.php');
    exit();
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_SESSION['email'];
    $new_password = $_POST['new_password'];  // รับรหัสผ่านใหม่
    $confirm_password = $_POST['confirm_password'];  // รับการยืนยันรหัสผ่านใหม่

    // ตรวจสอบว่า รหัสผ่านใหม่และการยืนยันรหัสผ่านตรงกันหรือไม่
    if ($new_password === $confirm_password) {
        // อัปเดตรหัสผ่านในฐานข้อมูล พร้อมรีเซ็ตจำนวนการพยายามเข้าสู่ระบบเป็น 0
        $stmt_update = $conn->prepare("UPDATE login SET password = ?, failed_attempts = 0 WHERE email = ?");
        $stmt_update->bind_param("ss", $new_password, $email);

        if ($stmt_update->execute()) {
            $success = true;
            unset($_SESSION['email']);  // ลบอีเมลในเซสชันหลังจากเปลี่ยนรหัสผ่าน
            header("refresh:2;url=login.php");  // ไปยังหน้าเข้าสู่ระบบหลังจาก 2 วินาที
        } else {
            echo "<script>alert('ไม่สามารถเปลี่ยนรหัสผ่านได้');</script>";
        }
    } else {
        echo "<script>alert('รหัสผ่านไม่ตรงกัน');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, #74b9ff, #81ecec);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .password-container {
            position: relative;
            margin-bottom: 20px;
        }

        .password-container input {
            font-family: monospace;
            letter-spacing: 0.1em;
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #dfe6e9;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }

        .password-container input:focus {
            border-color: #0984e3;
        }

        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #636e72;
            cursor: pointer;
        }

        button {
            background: #0984e3;
            color: #fff;
            border: none;
            padding: 12px 0;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background: #74b9ff;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
        }

        .popup.show {
            display: block;
        }

        .popup h3 {
            font-size: 20px;
            font-weight: 500;
            color: #2d3436;
            margin-bottom: 20px;
        }

        .popup button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .popup button:hover {
            background-color: #45a049;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }
    </style>

    </style>
</head>

<body>
    <div class="container">
        <h2>กรอกรหัสผ่านใหม่</h2>
        <form action="reset_password.php" method="POST">
            <div class="password-container">
                <input type="password" id="new_password" name="new_password" placeholder="กรอกรหัสผ่านใหม่" required>
                <span class="eye-icon" onclick="togglePasswordVisibility('new_password')">👁️</span>
            </div>
            <div class="password-container">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่านใหม่" required>
                <span class="eye-icon" onclick="togglePasswordVisibility('confirm_password')">👁️</span>
            </div>
            <button type="submit">ตั้งรหัสผ่านใหม่</button>
        </form>
    </div>

    <div class="popup" id="popup">
        <h3>รหัสผ่านของคุณถูกเปลี่ยนเรียบร้อยแล้ว</h3>
        <button onclick="redirectToLogin()">ตกลง</button>
    </div>

    <script>
        function togglePasswordVisibility(id) {
            var passwordField = document.getElementById(id);
            if (passwordField.type === "password") {
                passwordField.type = "text"; // แสดงรหัสผ่าน
            } else {
                passwordField.type = "password"; // ซ่อนรหัสผ่าน
            }
        }

        function redirectToLogin() {
            window.location.href = "login.php";
        }

        <?php if ($success): ?>
            // แสดงป็อปอัพหลังจากเปลี่ยนรหัสผ่านสำเร็จ
            document.getElementById('popup').classList.add('show');
        <?php endif; ?>
    </script>
</body>

</html>