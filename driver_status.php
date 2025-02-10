<?php
include 'db_connect.php'; // เชื่อมต่อฐานข้อมูล

$driver_email = "driver@example.com"; // เปลี่ยนเป็นค่าที่ได้จาก session

$sql = "SELECT * FROM approvals 
WHERE driver_email = ? 
AND admin_approval = 'Approved' 
AND driver_approval = 'Pending'
ORDER BY booking_id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $driver_email); // s = string
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "Booking ID: " . $row["booking_id"] . " - รถ: " . $row["vehicle_license_plate"] . "<br>";
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html>

<head>
    <title>สถานะการอนุมัติการขับรถ - พนักงานขับรถ</title>
</head>

<body>

    <table>
        <thead>
            <tr>
                <th>หมายเลขการมอบหมาย</th>
                <th>วันที่มอบหมาย</th>
                <th>ผู้ขอการมอบหมาย</th>
                <th>เลขทะเบียนรถ</th>
                <th>ผู้ขับรถ</th>
                <th>สถานะการอนุมัติ</th>
                <th>อนุมัติ/ไม่อนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // ดึงข้อมูลการมอบหมายงานขับรถทั้งหมดจากฐานข้อมูล
            $sql = "SELECT da.assignment_id, da.assignment_date, ur.first_name, ur.last_name, da.license_plate, ur.driver_name, da.approval_status
                    FROM driver_assignments da
                    LEFT JOIN user_requests ur ON da.requester_email = ur.email
                    WHERE da.driver_email = ?"; // กรองการมอบหมายที่มีพนักงานขับรถตามอีเมล

            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die('เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: ' . $conn->error);
            }

            $stmt->bind_param("s", $_SESSION['email']); // ตรวจสอบอีเมลพนักงานขับรถ
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['assignment_id'] . "</td>";
                    echo "<td>" . $row['assignment_date'] . "</td>";
                    echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                    echo "<td>" . $row['license_plate'] . "</td>";
                    echo "<td>" . $row['driver_name'] . "</td>";
                    echo "<td>" . (isset($row['approval_status']) ? $row['approval_status'] : "รอดำเนินการ") . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action='driver_status.php'>";
                    echo "<input type='hidden' name='request_id' value='" . $row['assignment_id'] . "'>";
                    echo "<button type='submit' name='status' value='อนุมัติ'>อนุมัติ</button>";
                    echo "<button type='submit' name='status' value='ไม่อนุมัติ'>ไม่อนุมัติ</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>ไม่พบข้อมูลการมอบหมาย</td></tr>";
            }

            // ปิดการเชื่อมต่อ
            $stmt->close();
            ?>
        </tbody>
    </table>

</body>

</html>