<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

include 'db.php';

// ตรวจสอบว่ามีการส่งค่า email มาหรือไม่
if (isset($_GET['email'])) {
    $email = $_GET['email'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM user_profile WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "ไม่พบข้อมูลผู้ใช้";
        exit();
    }
}
$admin_email = $_SESSION['email']; // Get the admin's email from the session
$stmt = $conn->prepare("SELECT * FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc(); // Store the fetched data in the $row variable


// เมื่อมีการส่งฟอร์ม
if (isset($_POST['update'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $rank = $_POST['rank'];
    $password = $_POST['password'];

    // อัปเดตข้อมูลในตาราง user_profile
    $stmt_update_profile = $conn->prepare("UPDATE user_profile SET first_name = ?, last_name = ?, phone_number = ?, rank = ? WHERE email = ?");
    $stmt_update_profile->bind_param("sssss", $first_name, $last_name, $phone_number, $rank, $email);
    if ($stmt_update_profile->execute()) {
        // อัปเดตข้อมูลในตาราง login
        if (!empty($password)) {
            $stmt_update_login = $conn->prepare("UPDATE login SET password = ? WHERE email = ?");
            $stmt_update_login->bind_param("ss", $password, $email);
            $stmt_update_login->execute();
        }
        echo "แก้ไขข้อมูลผู้ใช้สำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลผู้ใช้</title>
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

        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 20px auto;
        }

        input[type="text"],
        input[type="file"],
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4cae4c;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        button[type="button"] {
            background-color: #007bff;
            color: white;
            margin-top: 10px;
        }

        button[type="button"]:hover {
            background-color: #0056b3;
        }

        .aa1 {
            margin-top: 70px;
            margin-left: 50px;
            margin-bottom: 20px;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="ตราสัญลักษณ์">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h2>
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

    <div class="main-content">
        <h2 class="aa1">แก้ไขข้อมูลผู้ใช้</h2>
        <form method="post">
            <label for="first_name">ชื่อ:</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

            <label for="last_name">นามสกุล:</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

            <label for="phone_number">เบอร์โทรศัพท์:</label>
            <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>

            <label for="rank">ตำแหน่ง:</label>
            <input type="text" name="rank" id="rank" value="<?php echo htmlspecialchars($user['rank']); ?>" required>

            <label for="password">รหัสผ่าน (หากต้องการเปลี่ยน):</label>
            <input type="text" name="password" id="password">

            <button type="submit" name="update">ยืนยันการแก้ไข</button>
            <button type="button" onclick="window.location.href='add_user.php'" style="background-color: #007bff; color: white;">ย้อนกลับ</button>
        </form>
    </div>
</body>

</html>