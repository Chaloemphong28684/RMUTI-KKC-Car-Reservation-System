<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// Fetch admin data
$admin_email = $_SESSION['email']; // Get the admin's email from the session
$stmt = $conn->prepare("SELECT * FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc(); // Store the fetched data in the $row variable

if (!$row) {
    // Handle case where the admin data is not found
    echo "ข้อมูลผู้ดูแลระบบไม่พบ";
    exit();
}

// Fetch driver data
$email = $_GET['email'];
$stmt = $conn->prepare("SELECT * FROM driver_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// Update driver information
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $employee_id = $_POST['employee_id'];
    $license_number = $_POST['license_number'];

    // Check for uploaded profile picture
    if ($_FILES['profile_picture']['name']) {
        $profile_picture = $_FILES['profile_picture']['name'];
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], "uploads/" . $profile_picture);
    } else {
        $profile_picture = $driver['profile_picture']; // Keep the old picture if not updated
    }

    // Update driver profile
    $stmt = $conn->prepare("UPDATE driver_profile SET first_name=?, last_name=?, phone_number=?, employee_id=?, license_number=?, profile_picture=? WHERE email=?");
    $stmt->bind_param("sssssss", $first_name, $last_name, $phone_number, $employee_id, $license_number, $profile_picture, $email);

    if ($stmt->execute()) {
        echo "<script>alert('แก้ไขข้อมูลพนักงานขับรถสำเร็จ'); window.location.href='add_driver.php';</script>";
    } else {
        echo "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลพนักงานขับรถ</title>
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

        .g1 {
            margin: 70px 0 10px 50px;
            margin-top: 5%;
            font-size: 19px;
        }
    </style>
</head>

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

<body>
    <div class="main-content">
        <h2 class="g1">แก้ไขข้อมูลพนักงานขับรถ</h2>
    </div>

    <form action="edit_driver.php?email=<?php echo urlencode($email); ?>" method="POST" enctype="multipart/form-data">
        <label for="first_name">ชื่อ:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($driver['first_name']); ?>" required>

        <label for="last_name">นามสกุล:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($driver['last_name']); ?>" required>

        <label for="phone_number">เบอร์โทร:</label>
        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($driver['phone_number']); ?>" required>

        <label for="employee_id">รหัสพนักงาน:</label>
        <input type="text" name="employee_id" value="<?php echo htmlspecialchars($driver['employee_id']); ?>" required>

        <label for="license_number">หมายเลขใบอนุญาตขับขี่:</label>
        <input type="text" name="license_number" value="<?php echo htmlspecialchars($driver['license_number']); ?>" required>

        <label for="profile_picture">อัปโหลดรูปโปรไฟล์:</label>
        <input type="file" name="profile_picture" accept="image/*">

        <button type="submit" name="update">ยืนยันการแก้ไข</button>
        <button type="button" onclick="window.location.href='add_driver.php'" style="background-color: #007bff; color: white;">ย้อนกลับ</button>
    </form>


</body>

</html>