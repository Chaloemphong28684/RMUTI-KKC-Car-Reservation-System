<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// ดึงข้อมูลผู้ดูแลระบบจากฐานข้อมูล
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    // ถ้าไม่มีข้อมูลให้แสดงฟอร์มเพิ่มข้อมูล
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $employee_id = $_POST['employee_id'];
        $phone_number = $_POST['phone_number'];

        // เพิ่มข้อมูลในฐานข้อมูล
        $stmt = $conn->prepare("INSERT INTO admin_profile (email, first_name, last_name, employee_id, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $first_name, $last_name, $employee_id, $phone_number);

        if ($stmt->execute()) {
            // Redirect to profile page after successful insertion
            header('Location: admin_profile.php');
            exit();
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลส่วตัว</title>
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
        .main-content {
            margin-left: 260px;
            padding: 15px;
            width: 1200px;
        }


        .profile-info {
            background-color: white;
            margin-left: 70px;
            margin-right: 70px;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .button:hover {
            background-color: #003366;
        }

        .warning {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- แถบด้านบน -->
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <!-- แถบด้านซ้าย -->
    <div class="sidebar">
        <h2>สวัสดี, <?php echo isset($row) ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) : 'ผู้ดูแลระบบ'; ?></h2>
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

    <!-- พื้นที่หลัก -->
    <div class="main-content">
        <h2 style="margin: 70px 0 20px 50px; font-size: 18px;">ข้อมูลส่วนตัว</h2>
        <div class="profile-info">
            <?php if (isset($row)): ?>
                <label>อีเมล:</label>
                <p><?php echo htmlspecialchars($row['email']); ?></p>

                <label>ชื่อ:</label>
                <p><?php echo htmlspecialchars($row['first_name']); ?></p>

                <label>นามสกุล:</label>
                <p><?php echo htmlspecialchars($row['last_name']); ?></p>

                <label>รหัสพนักงาน:</label>
                <p><?php echo htmlspecialchars($row['employee_id']); ?></p>

                <label>หมายเลขโทรศัพท์:</label>
                <p><?php echo isset($row['phone_number']) ? htmlspecialchars($row['phone_number']) : 'ไม่มีข้อมูล'; ?></p>

                <a href="update_profile_admin.php" class="button">แก้ไขข้อมูลส่วนตัว</a>
            <?php else: ?>
                <h3>กรุณากรอกข้อมูลส่วนตัวของคุณ:</h3>
                <form method="POST">
                    <label for="first_name">ชื่อ:</label>
                    <input type="text" name="first_name" required>

                    <label for="last_name">นามสกุล:</label>
                    <input type="text" name="last_name" required>

                    <label for="employee_id">รหัสพนักงาน:</label>
                    <input type="text" name="employee_id" required>

                    <label for="phone_number">หมายเลขโทรศัพท์:</label>
                    <input type="text" name="phone_number">

                    <button type="submit" class="button">บันทึกข้อมูล</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>