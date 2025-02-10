<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

include 'db.php';

if (isset($_POST['cancel'])) {
    unset($_SESSION['temp_user']);
    header('Location: add_user.php');
    exit();
}

$email = $_SESSION['email'];

// Fetch admin's name
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$display_name = ($row) ? $row['first_name'] . ' ' . $row['last_name'] : 'ผู้ดูแลระบบ';

if (isset($_POST['confirm']) && !isset($_POST['edit_id'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $rank = $_POST['rank'];
    $position = 'faculty';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "อีเมลไม่ถูกต้อง";
    } else {
        // Check for duplicate email
        $check_stmt = $conn->prepare("SELECT * FROM login WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "อีเมลนี้มีอยู่ในระบบแล้ว กรุณาใช้อีเมลอื่น";
        } else {
            // Insert login information
            $stmt_login = $conn->prepare("INSERT INTO login (email, password) VALUES (?, ?)");
            $stmt_login->bind_param("ss", $email, $password);

            if ($stmt_login->execute()) {
                // Insert user profile
                $stmt_profile = $conn->prepare("INSERT INTO user_profile (email, first_name, last_name, phone_number, rank, position) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_profile->bind_param("ssssss", $email, $first_name, $last_name, $phone_number, $rank, $position);

                if ($stmt_profile->execute()) {
                    echo "เพิ่มข้อมูลผู้ใช้สำเร็จ";
                } else {
                    echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูลโปรไฟล์: " . $conn->error;
                }
            } else {
                echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูลล็อกอิน: " . $conn->error;
            }
        }
    }

    unset($_SESSION['temp_user']);
}

if (isset($_GET['delete'])) {
    $email_to_delete = $_GET['delete'];

    // Confirm delete action
    if (isset($_GET['confirm_delete'])) {
        $stmt_delete_login = $conn->prepare("DELETE FROM login WHERE email = ?");
        $stmt_delete_login->bind_param("s", $email_to_delete);

        if ($stmt_delete_login->execute()) {
            // Delete from user_profile
            $stmt_delete_profile = $conn->prepare("DELETE FROM user_profile WHERE email = ?");
            $stmt_delete_profile->bind_param("s", $email_to_delete);
            $stmt_delete_profile->execute();
            echo "ลบข้อมูลผู้ใช้สำเร็จ";
        } else {
            echo "เกิดข้อผิดพลาดในการลบข้อมูล: " . $conn->error;
        }
    } else {
        echo "<script>if(confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลผู้ใช้นี้?')){window.location='add_user.php?delete={$email_to_delete}&confirm_delete=true';}else{window.location='add_user.php';}</script>";
    }
}

if (isset($_POST['edit_id'])) {
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $rank = $_POST['rank'];
    $position = 'faculty';

    // Update user profile
    $stmt_update = $conn->prepare("UPDATE user_profile SET first_name = ?, last_name = ?, phone_number = ?, rank = ?, position = ? WHERE email = ?");
    $stmt_update->bind_param("ssssss", $first_name, $last_name, $phone_number, $rank, $position, $email);

    if ($stmt_update->execute()) {
        echo "แก้ไขข้อมูลผู้ใช้สำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $email_to_delete = $_GET['delete'];

    // ลบข้อมูลจากตาราง driver_profile ก่อน
    $stmt_delete_profile = $conn->prepare("DELETE FROM user_profile WHERE email = ?");
    $stmt_delete_profile->bind_param("s", $email_to_delete);

    if ($stmt_delete_profile->execute()) {
        // ลบข้อมูลจากตาราง login
        $stmt_delete_login = $conn->prepare("DELETE FROM login WHERE email = ?");
        $stmt_delete_login->bind_param("s", $email_to_delete);

        if ($stmt_delete_login->execute()) {
            echo "<script>alert('ลบข้อมูลสำเร็จ');</script>";
        } else {
            echo "เกิดข้อผิดพลาดในการลบข้อมูลล็อกอิน: " . $conn->error;
        }
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูลโปรไฟล์: " . $conn->error;
    }
}

?>

<!-- HTML code for the user interface continues... -->




<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลผู้ใช้</title>
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

        /* พื้นที่หลัก (Main Content) */

        /* เนื้อหาหลักที่อยู่ฝั่งขวา */
        .container {
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        h2 {
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        /* ฟอร์มการเพิ่มข้อมูล */
        form {
            width: 70%;
            /* ลดขนาดจาก 80% เป็น 70% */
            max-width: 400px;
            /* ลดขนาดจาก 500px เป็น 400px */
            margin: 0 auto;
            background-color: white;
            padding: 15px;
            /* ลด padding */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            /* ลดความโค้ง */
        }

        label {
            display: block;
            margin-top: 8px;
            /* ลดระยะห่าง */
            font-size: 14px;
            /* ลดขนาดฟอนต์ */
            color: #333;
        }

        input,
        select,
        button {
            width: 100%;
            height: 28px;
            /* ลดความสูง */
            padding: 4px;
            /* ลด padding */
            margin-top: 6px;
            /* ลดระยะห่าง */
            font-size: 0.9em;
            /* ลดขนาดฟอนต์ */
            border: 1px solid #ccc;
            /* เปลี่ยนสีขอบให้จางลง */
            border-radius: 3px;
            /* ลดความโค้ง */
            box-sizing: border-box;
        }

        button {
            background-color: #004080;
            color: white;
            border: none;
            cursor: pointer;
            height: 32px;
            /* ลดขนาดปุ่ม */
        }

        /* ปรับขนาดตามหน้าจอ */
        @media screen and (max-width: 768px) {
            form {
                width: 80%;
                max-width: 350px;
            }

            input,
            select,
            button {
                font-size: 0.85em;
                height: 26px;
            }
        }

        @media screen and (max-width: 480px) {
            form {
                width: 90%;
                max-width: 320px;
            }

            input,
            select,
            button {
                font-size: 0.8em;
                height: 24px;
            }
        }

        button:hover {
            background-color: rgb(0, 255, 34);
        }

        /* เพิ่มลักษณะสำหรับตาราง */
        table {
            margin-left: 5%;
            width: 90%;
            /* ลดขนาดจาก 95% เป็น 90% */
            border-collapse: collapse;
            margin-top: 15px;
            /* ลด margin-top */
            text-align: center;
            font-size: 0.9em;
            /* ลดขนาดฟอนต์ */
            border-radius: 8px;
            /* เพิ่มมุมโค้ง */
            overflow: hidden;
            /* ป้องกันขอบมนหาย */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* เพิ่มเงาให้ดูมีมิติ */
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 6px;
            /* ลด padding */
            text-align: center;
        }

        table th {
            background-color: #004080;
            color: white;
            font-weight: bold;
            padding: 10px;
            /* ปรับ padding ของหัวตารางให้เด่นขึ้น */
        }

        table tr:hover {
            background-color: #f9f9f9;
            /* ใช้สีอ่อนลงเพื่อความสวยงาม */
        }

        /* ปรับขนาดตารางสำหรับหน้าจอเล็ก */
        @media screen and (max-width: 768px) {
            table {
                width: 100%;
                font-size: 0.85em;
            }

            table th,
            table td {
                padding: 5px;
            }
        }

        @media screen and (max-width: 480px) {
            table {
                font-size: 0.8em;
            }

            table th,
            table td {
                padding: 4px;
            }
        }

        .a11 {
            margin-top: 5%;
            margin-left: 5%;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="โลโก้">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="admin_dashboard.php">หน้าหลัก</a>
        <a href="admin_profile.php">ข้อมูลส่วนตัว</a>
        <a href="admin_requests.php">จัดการคำขอจองรถ</a>
        <a href="add_user.php">เพิ่มข้อมูลผู้ใช้</a>
        <a href="add_vehicle.php">เพิ่มข้อมูลรถ</a>
        <a href="add_driver.php">เพิ่มข้อมูลพนักงานขับรถ</a>
        <a href="admin_status.php">รออนุมัติสถานะการจอง</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="container">
        <h2 class="a11">เพิ่มข้อมูลผู้ใช้</h2>
        <!-- ส่วนของการเพิ่มข้อมูลผู้ใช้ -->
        <form method="post">
            <label for="email">อีเมล:</label>
            <input type="email" name="email" id="email" required><br>

            <label for="first_name">ชื่อ:</label>
            <input type="text" name="first_name" id="first_name" required>

            <label for="last_name">นามสกุล:</label>
            <input type="text" name="last_name" id="last_name" required><br>

            <label for="phone_number">เบอร์โทรศัพท์:</label>
            <input type="tel" name="phone_number" id="phone_number" required>

            <label for="rank">ตำแหน่ง:</label>
            <input type="text" name="rank" id="rank" required><br>

            <label for="password">รหัสผ่าน:</label>
            <input type="password" name="password" id="password" required><br>

            <button type="submit" name="confirm">ยืนยัน</button>
        </form>

        <!-- แสดงตารางข้อมูลผู้ใช้ -->
        <table class="tb2">
            <thead>
                <tr>
                    <th>อีเมล</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>ตำแหน่ง</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>แก้ไข/ลบ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ดึงข้อมูลผู้ใช้จากฐานข้อมูลและแสดงในตาราง
                $stmt = $conn->prepare("SELECT * FROM user_profile");
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['email']}</td>
                            <td>{$row['first_name']}</td>
                            <td>{$row['last_name']}</td>
                            <td>{$row['rank']}</td>  <!-- แสดงตำแหน่ง -->
                            <td>{$row['phone_number']}</td>
                            <td>
                                <a href='edit_user.php?email={$row['email']}'>แก้ไข</a> | 
                                <a href='add_user.php?delete={$row['email']}'>ลบ</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>