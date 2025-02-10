<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['email'];

// ดึงข้อมูลชื่อผู้ดูแลระบบ
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$display_name = ($row) ? $row['first_name'] . ' ' . $row['last_name'] : 'ผู้ดูแลระบบ';

// เพิ่มพนักงานขับรถใหม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $employee_id = $_POST['employee_id'];
    $license_number = $_POST['license_number'];
    $profile_picture = $_FILES['profile_picture']['name'];
    $type = 'driver';

    // ตรวจสอบอีเมลซ้ำ
    $check_stmt = $conn->prepare("SELECT * FROM login WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('อีเมลนี้มีอยู่ในระบบแล้ว กรุณาใช้อีเมลอื่น');</script>";
    } else {
        // บันทึกข้อมูลลงในตาราง login
        $stmt = $conn->prepare("INSERT INTO login (email, password, type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $type);

        if ($stmt->execute()) {
            // อัปโหลดรูปภาพ
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], "uploads/" . $profile_picture);

            // เพิ่มข้อมูลพนักงานขับรถในตาราง driver_profile
            $driver_stmt = $conn->prepare("INSERT INTO driver_profile (email, first_name, last_name, phone_number, employee_id, license_number, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $driver_stmt->bind_param("sssssss", $email, $first_name, $last_name, $phone_number, $employee_id, $license_number, $profile_picture);

            if ($driver_stmt->execute()) {
                echo "<script>alert('เพิ่มข้อมูลพนักงานขับรถสำเร็จ');</script>";
            } else {
                echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูลโปรไฟล์: " . $conn->error;
            }
        } else {
            echo "เกิดข้อผิดพลาดในการเพิ่มข้อมูล: " . $conn->error;
        }
    }
}

// ลบพนักงานขับรถ
if (isset($_GET['delete'])) {
    $email_to_delete = $_GET['delete'];

    // ลบข้อมูลจากตาราง driver_profile ก่อน
    $stmt_delete_profile = $conn->prepare("DELETE FROM driver_profile WHERE email = ?");
    $stmt_delete_profile->bind_param("s", $email_to_delete);

    if ($stmt_delete_profile->execute()) {
        // ลบข้อมูลจากตาราง login
        $stmt_delete_login = $conn->prepare("DELETE FROM login WHERE email = ?");
        $stmt_delete_login->bind_param("s", $email_to_delete);

        if ($stmt_delete_login->execute()) {
            echo "<script>alert('ลบข้อมูลพนักงานขับรถสำเร็จ');</script>";
        } else {
            echo "เกิดข้อผิดพลาดในการลบข้อมูลล็อกอิน: " . $conn->error;
        }
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูลโปรไฟล์: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เพิ่มข้อมูลพนักงานขับรถ</title>
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


        /* เพิ่ม margin ให้เนื้อหา */

        /* เนื้อหาหลักที่อยู่ฝั่งขวา */

        /* พื้นที่หลัก */
        .main-content {
            margin-left: 600px;
            margin-bottom: 10px;
            padding: 15px;
            width: 500px;
            height: auto;
            font-size: small;
        }

        /* สไตล์ฟอร์ม */
        .main-content form {
            max-width: 600px;
            margin: 0 auto;
        }

        .main-content input[type="text"],
        .main-content input[type="password"],
        .main-content input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .main-content button {
            padding: 10px 20px;
            background-color: #004080;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .main-content button:hover {
            background-color: #003366;
        }

        /* สไตล์ตาราง */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #004080;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* ปรับรูปภาพในตาราง */
        table img {
            max-width: 120px;
            height: auto;
            border-radius: 4px;
        }


        /* ฟอร์มการเพิ่มข้อมูล */
        form {
            width: 80%;
            max-width: 550px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-size: 10px;
            color: #333;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 7px;
            margin-top: 8px;
            font-size: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #004080;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: rgb(0, 255, 34);
        }

        .tb2 {
            margin-left: 300px;
            padding: 5px;
            width: 1200px;
            font-size: small;
        }

        .g1 {
            margin: 70px 0 10px 310px;
            margin-top: 5%;
            font-size: 19px;
        }
    </style>
</head>

<body>

    <!-- แถบด้านบน -->
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="โลโก้มหาวิทยาลัย">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <!-- แถบด้านซ้าย -->
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
        <a href="login.php">ออกจากระบบ</a>
    </div>

    <!-- พื้นที่เนื้อหาหลัก -->
    <div>
        <h2 class="g1">เพิ่มข้อมูลพนักงานขับรถ</h2>
        <form class="main-content" action="add_driver.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="email" placeholder="อีเมล (ต้องลงท้ายด้วย @rmuti.ac.th)" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <input type="text" name="first_name" placeholder="ชื่อ" required>
            <input type="text" name="last_name" placeholder="นามสกุล" required>
            <input type="text" name="phone_number" placeholder="เบอร์โทร" required>
            <input type="text" name="employee_id" placeholder="รหัสพนักงาน" required>
            <input type="text" name="license_number" placeholder="เลขใบอนุญาตขับรถ" required>
            <input type="file" name="profile_picture" accept="image/*" required>
            <button type="submit" name="confirm">ยืนยันข้อมูล</button>
            <button type="reset" name="cancel">ยกเลิก</button>
        </form>

        <!-- แสดงข้อมูลพนักงานขับรถ -->
        <table class="tb2">
            <tr>
                <th>อีเมล</th>
                <th>ชื่อ</th>
                <th>นามสกุล</th>
                <th>เบอร์โทร</th>
                <th>รหัสพนักงาน</th>
                <th>เลขใบอนุญาตขับรถ</th>
                <th>รูปภาพโปรไฟล์</th>
                <th>แก้ไข</th>
                <th>ลบ</th>
            </tr>
            <?php
            $result = $conn->query("SELECT * FROM driver_profile");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['license_number']) . "</td>";
                echo "<td><img src='uploads/" . htmlspecialchars($row['profile_picture']) . "'></td>";
                echo "<td><a href='edit_driver.php?email=" . urlencode($row['email']) . "'>แก้ไข</a></td>";
                echo "<td><a href='add_driver.php?delete=" . urlencode($row['email']) . "' onclick='return confirm(\"คุณต้องการลบพนักงานขับรถนี้หรือไม่?\")'>ลบ</a></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</body>

</html>