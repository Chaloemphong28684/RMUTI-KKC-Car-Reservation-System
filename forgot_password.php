<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // ตรวจสอบว่าอีเมลมีในระบบหรือไม่
    $stmt = $conn->prepare("SELECT * FROM login WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // เก็บอีเมลในเซสชัน เพื่อใช้ในหน้า reset_password.php
        $_SESSION['email'] = $email;
        header('Location: reset_password.php');
        exit();
    } else {
        echo "<script>alert('อีเมลนี้ไม่อยู่ในระบบ');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kanit', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg,rgb(140, 199, 255), #9face6);
            color: #333;
        }

        .container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            padding: 30px;
            text-align: center;
        }

        .container h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #4a4a4a;
        }

        .container form input[type="text"] {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.3s;
        }

        .container form input[type="text"]:focus {
            border-color:rgb(116, 176, 235);
        }

        .container form button {
            width: 100%;
            padding: 15px;
            margin-top: 10px;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg,rgb(80, 70, 255),rgb(107, 134, 255));
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .container form button:hover {
            background: linear-gradient(135deg,rgb(55, 55, 55),rgb(68, 68, 68));
        }

        .container form button:active {
            transform: scale(0.98);
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            .container h2 {
                font-size: 1.5rem;
            }

            .container form input[type="text"],
            .container form button {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>ลืมรหัสผ่าน</h2>
        <form action="forgot_password.php" method="POST">
            <input type="text" name="email" placeholder="กรุณากรอกอีเมลของคุณ" required>
            <button type="submit">ยืนยัน</button>
        </form>
    </div>
</body>

</html>