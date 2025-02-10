<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: admin_dashboard.php');
    exit();
}

$email = $_SESSION['email'];

// ดึงข้อมูลชื่อจากฐานข้อมูล
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// ตั้งค่าชื่อที่จะแสดง
if ($row) {
    $display_name = $row['first_name'] . ' ' . $row['last_name']; // แสดงชื่อและนามสกุล
} else {
    $display_name = 'ผู้ดูแลระบบ'; // ถ้าไม่มีข้อมูลในฐานข้อมูล ใช้ชื่อเริ่มต้น
}

$events = [];
$query = "SELECT license_plate, start_datetime, end_datetime FROM calendar_bookings";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => 'ทะเบียน: ' . $row['license_plate'],
            'start' => $row['start_datetime'],
            'end' => $row['end_datetime']
        ];
    }
} else {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ปฏิทินการจองรถ</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <style>
        /* สไตล์ CSS ของแถบเมนู */
        /* ตั้งค่าพื้นฐาน */
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
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        .main-content h2 {
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        /* ปรับขนาดปฏิทินให้พอดีกับหน้า */
        /* ปรับขนาดปฏิทินให้เล็กลง */
        #calendar {
            max-width: 55%;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 8px;
            background-color: #fff;
            font-size: 0.9em;
        }

        /* ปรับขนาดปฏิทินตามหน้าจอ */
        @media screen and (max-width: 1024px) {
            #calendar {
                max-width: 70%;
                font-size: 0.75em;
            }
        }

        @media screen and (max-width: 768px) {
            #calendar {
                max-width: 80%;
                font-size: 0.6em;
            }
        }

        @media screen and (max-width: 480px) {
            #calendar {
                max-width: 80%;
                font-size: 0.75em;
            }
        }
        .aa1{
            margin-top: 70px;
            margin-left: 50px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- แถบด้านบน -->
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="Logo">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <!-- แถบด้านซ้าย -->
    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="#">หน้าหลัก</a>
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
    <div class="main-content">
        <h2 class="aa1">ปฏิทินการจองรถของมหาวิทยาลัย</h2>
        <div id='calendar'></div>
    </div>

    <!-- เริ่มต้นปฏิทิน -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var events = <?php echo json_encode($events); ?>;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'th',
                events: events,
                selectable: true,
                select: function(info) {
                    Swal.fire({
                        title: 'เพิ่มการจอง',
                        html: `
                            <input type="date" id="start_date" class="swal2-input" placeholder="วันที่เริ่มต้น">
                            <input type="time" id="start_time" class="swal2-input" placeholder="เวลาเริ่มต้น">
                            <input type="date" id="end_date" class="swal2-input" placeholder="วันที่สิ้นสุด">
                            <input type="time" id="end_time" class="swal2-input" placeholder="เวลาสิ้นสุด">
                            <input type="text" id="license_plate" class="swal2-input" placeholder="ทะเบียนรถ">
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'บันทึก',
                        preConfirm: () => {
                            let startDate = document.getElementById('start_date').value;
                            let startTime = document.getElementById('start_time').value;
                            let endDate = document.getElementById('end_date').value;
                            let endTime = document.getElementById('end_time').value;
                            let licensePlate = document.getElementById('license_plate').value;

                            if (!startDate || !startTime || !endDate || !endTime || !licensePlate) {
                                Swal.showValidationMessage('กรุณากรอกข้อมูลทั้งหมด');
                                return false;
                            }

                            let startDateTime = startDate + ' ' + startTime;
                            let endDateTime = endDate + ' ' + endTime;

                            return {
                                start: startDateTime,
                                end: endDateTime,
                                license_plate: licensePlate
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('add_booking.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(result.value)
                            }).then(response => response.json()).then(data => {
                                if (data.success) {
                                    calendar.addEvent({
                                        title: 'ทะเบียน: ' + result.value.license_plate,
                                        start: result.value.start,
                                        end: result.value.end,
                                        allDay: false
                                    });
                                    Swal.fire('บันทึกสำเร็จ!', '', 'success');
                                } else {
                                    Swal.fire('เกิดข้อผิดพลาด', data.message, 'error');
                                }
                            });
                        }
                    });
                }
            });
            calendar.render();
        });
    </script>
</body>

</html>