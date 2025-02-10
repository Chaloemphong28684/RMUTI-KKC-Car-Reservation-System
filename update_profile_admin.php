<?php
session_start();
include 'db.php';

// ตรวจสอบว่าเป็นผู้ดูแลระบบหรือไม่
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// ดึงข้อมูลโปรไฟล์ผู้ดูแลระบบ
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "ไม่พบข้อมูลผู้ดูแลระบบ";
    exit();
}

// อัปเดตข้อมูลเมื่อผู้ใช้ส่งแบบฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone_number'];
    $employee_id = $_POST['employee_id'];

    // ตรวจสอบข้อมูลและอัปเดตในฐานข้อมูล
    if (!empty($first_name) && !empty($last_name) && !empty($phone) && !empty($employee_id)) {
        $stmt = $conn->prepare("UPDATE admin_profile SET first_name = ?, last_name = ?, phone_number = ?, employee_id = ? WHERE email = ?");
        $stmt->bind_param("sssss", $first_name, $last_name, $phone, $employee_id, $email);
    }
    if ($stmt->execute()) {
        $message = "ข้อมูลส่วนตัวถูกแก้ไขเรียบร้อยแล้ว";
        echo '<script>
            setTimeout(function() {
                window.location.href = "admin_profile.php";
            }, 2000); // 2000ms = 2 seconds
          </script>';
    } else {
        $message = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว</title>
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


        /* เพิ่ม margin ให้เนื้อหา */

        /* เนื้อหาหลักที่อยู่ฝั่งขวา */

        /* พื้นที่หลัก */
        .main-content {
            margin: 70px;
            margin-left: 250px;
            margin-bottom: 10px;
            padding: 15px;
            width: 500px;
            height: auto;
            font-size: small;
        }

        .main-content h2 {
            font-size: 1.5em;
        }

        .form-container {
            background-color: white;
            margin-left: 650px;
            margin-right: 50px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
        }

        .form-container input[type="text"],
        .form-container input[type="tel"] {
            width: 327px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
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

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .d1 {
            margin: 70px 0 10px 300px;
            margin-top: 5%;
            font-size: 19px;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="ตราสัญลักษณ์">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
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

    <div >
        <h2 class="d1">แก้ไขข้อมูลส่วนตัว</h2>

        <!-- แสดงข้อความถ้ามีการอัปเดตข้อมูล -->
        <?php if (!empty($message)): ?>
            <div class="error-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <label for="first_name">ชื่อ:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>

                <label for="last_name">นามสกุล:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>

                <label for="employee_id">รหัสพนักงาน:</label>
                <input type="text" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($row['employee_id']); ?>" required>

                <label for="phone_number">หมายเลขโทรศัพท์:</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>

                <button type="submit">ยืนยัน</button>
                <a href="admin_profile.php">
                    <button type="button" style="background-color: #f44336; color: white;">ยกเลิก</button>
                </a>
            </form>
        </div>
    </div>

</body>

</html>