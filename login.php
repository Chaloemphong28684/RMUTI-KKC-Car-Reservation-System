<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ตรวจสอบว่าอีเมลลงท้ายด้วย @rmuti.ac.th หรือไม่
    if (!str_ends_with($email, '@rmuti.ac.th')) {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let popup = document.createElement('div');
                popup.style.position = 'fixed';
                popup.style.top = '50%';
                popup.style.left = '50%';
                popup.style.transform = 'translate(-50%, -50%)';
                popup.style.backgroundColor = 'white';
                popup.style.border = '1px solid #ccc';
                popup.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
                popup.style.padding = '20px';
                popup.style.borderRadius = '8px';
                popup.style.textAlign = 'center';
                popup.style.width = '300px';

                let message = document.createElement('p');
                message.innerText = 'กรุณาใช้อีเมลที่ลงท้ายด้วย @rmuti.ac.th';
                message.style.fontFamily = 'TH Sarabun New, sans-serif';
                message.style.fontSize = '20px';
                message.style.marginBottom = '20px';
                popup.appendChild(message);

                let button = document.createElement('button');
                button.innerText = 'ตกลง';
                button.style.backgroundColor = '#004080';
                button.style.color = 'white';
                button.style.border = 'none';
                button.style.padding = '10px 20px';
                button.style.borderRadius = '5px';
                button.style.cursor = 'pointer';
                button.addEventListener('click', function() {
                    window.location.href = 'login.php'; // เปลี่ยนเส้นทางกลับไปยังหน้า login
                });
                popup.appendChild(button);

                document.body.appendChild(popup);
                document.body.style.backgroundColor = 'rgba(0, 0, 0, 0.4)'; // ทำพื้นหลังมืด
            });
        </script>
        ";
        exit();
    }

    // ค้นหาผู้ใช้ในฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM login WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ตรวจสอบจำนวนการพยายามเข้าสู่ระบบ
        if ($user['failed_attempts'] >= 5) {
            echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let popup = document.createElement('div');
                    popup.style.position = 'fixed';
                    popup.style.top = '50%';
                    popup.style.left = '50%';
                    popup.style.transform = 'translate(-50%, -50%)';
                    popup.style.backgroundColor = 'white';
                    popup.style.border = '1px solid #ccc';
                    popup.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
                    popup.style.padding = '20px';
                    popup.style.borderRadius = '8px';
                    popup.style.textAlign = 'center';
                    popup.style.width = '300px';

                    let message = document.createElement('p');
                    message.innerText = 'คุณได้พยายามเข้าสู่ระบบเกิน 5 ครั้ง กรุณาคลิก \"ลืมรหัสผ่าน\" เพื่อตั้งรหัสใหม่';
                    message.style.fontFamily = 'TH Sarabun New, sans-serif';
                    message.style.fontSize = '20px';
                    message.style.marginBottom = '20px';
                    popup.appendChild(message);

                    let button = document.createElement('button');
                    button.innerText = 'ตกลง';
                    button.style.backgroundColor = '#004080';
                    button.style.color = 'white';
                    button.style.border = 'none';
                    button.style.padding = '10px 20px';
                    button.style.borderRadius = '5px';
                    button.style.cursor = 'pointer';
                    button.addEventListener('click', function() {
                        window.location.href = 'forgot_password.php';
                    });
                    popup.appendChild(button);

                    document.body.appendChild(popup);
                    document.body.style.backgroundColor = 'rgba(0, 0, 0, 0.4)'; // ทำพื้นหลังมืด
                });
            </script>
            ";
            exit();
        }

        // ตรวจสอบรหัสผ่าน
        if ($password == $user['password']) {
            $_SESSION['email'] = $user['email'];
            $_SESSION['type'] = $user['type'];

            // ตรวจสอบและดึงข้อมูลโปรไฟล์
            if ($user['type'] == 'admin') {
                $_SESSION['role'] = 'admin';
                header('Location: admin_dashboard.php');
            } elseif ($user['type'] == 'faculty') {
                $_SESSION['role'] = 'faculty';
                header('Location: faculty_dashboard.php');
            } elseif ($user['type'] == 'driver') {
                $_SESSION['role'] = 'driver';
                header('Location: driver_dashboard.php');
            }
            exit();
        } else {
            // เพิ่มจำนวนการพยายามเข้าสู่ระบบ
            $failed_attempts = $user['failed_attempts'] + 1;
            $stmt_update = $conn->prepare("UPDATE login SET failed_attempts = ? WHERE email = ?");
            $stmt_update->bind_param("is", $failed_attempts, $email);
            $stmt_update->execute();

            echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let popup = document.createElement('div');
                    popup.style.position = 'fixed';
                    popup.style.top = '50%';
                    popup.style.left = '50%';
                    popup.style.transform = 'translate(-50%, -50%)';
                    popup.style.backgroundColor = 'white';
                    popup.style.border = '1px solid #ccc';
                    popup.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
                    popup.style.padding = '20px';
                    popup.style.borderRadius = '8px';
                    popup.style.textAlign = 'center';
                    popup.style.width = '300px';

                    let message = document.createElement('p');
                    message.innerText = 'รหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
                    message.style.fontFamily = 'TH Sarabun New, sans-serif';
                    message.style.fontSize = '20px';
                    message.style.marginBottom = '20px';
                    popup.appendChild(message);

                    let button = document.createElement('button');
                    button.innerText = 'ตกลง';
                    button.style.backgroundColor = '#004080';
                    button.style.color = 'white';
                    button.style.border = 'none';
                    button.style.padding = '10px 20px';
                    button.style.borderRadius = '5px';
                    button.style.cursor = 'pointer';
                    button.addEventListener('click', function() {
                        window.location.href = 'login.php'; // เปลี่ยนเส้นทางกลับไปยังหน้า login
                    });
                    popup.appendChild(button);

                    document.body.appendChild(popup);
                    document.body.style.backgroundColor = 'rgba(0, 0, 0, 0.4)'; // ทำพื้นหลังมืด
                });
            </script>
            ";
            exit();
        }
    } else {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                alert('อีเมลไม่ถูกต้อง');
                window.location.href = 'login.php';
            });
        </script>
        ";
    }
}
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจองรถมหาวิทยาลัย</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=TH+Sarabun+New:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'TH Sarabun New', sans-serif;
            background: linear-gradient(to right, #f4f4f4, #d9e4f5);
            padding: 0;
            margin: 20px 0 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            width: 100%;
        }

        .image-container {
            margin-bottom: 10px;
            text-align: center;
        }

        .image-container img {
            width: 40%;
            max-width: 250px;
            height: auto;
            border-radius: 8px;
        }

        header {
            text-align: center;
            margin-bottom: 10px;
            width: 100%;
        }

        h1 {
            color: #004080;
            margin: 0;
            font-size: 32px;
            text-align: center;
        }

        .container {
            background: linear-gradient(white, #f0f8ff);
            border: 2px solid #004080;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            color: #333;
            margin: 0 0 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .info {
            color: crimson;
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 40px);
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .container {
            padding: 20px;
            max-width: 340px;
        }


        button {
            background-color: #004080;
            color: white;
            border: none;
            margin: 10px 0;
            border-radius: 4px;
            cursor: pointer;
            width: 40%;
            padding: 10px;
            font-size: 14px;
        }

        button:hover {
            background-color: #003366;
        }

        .forgot-password {
            margin-top: 10px;
            display: inline-block;
            font-size: 16px;
        }

        @media screen and (max-width: 400px) {
            .image-container img {
                width: 50%;
            }

            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="image-container">
        <img src="uploads/RMUTI_KORAT.png" style="width: 40%; height: auto; border-radius: 8px;">
    </div>
    <div class="dd11">
        <header>
            <h1><b>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน <br>วิทยาเขตขอนแก่น</b></h1>
        </header>
    </div>

    <div class="container">
        <h2>เข้าสู่ระบบ</h2>
        <div class="info">กรุณาใช้ อีเมล ที่ลงท้ายด้วย <strong>@rmuti.ac.th</strong></div>
        <form action="login.php" method="POST">
            <input type="text" name="email" placeholder="อีเมล" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>
        <a href="forgot_password.php" class="forgot-password">ลืมรหัสผ่าน?</a>
    </div>
</body>

</html>