<?php
$imagePath = 'vcc.pdf';

// ตรวจสอบว่าไฟล์ PDF มีอยู่จริง
if (file_exists($imagePath)) {
    // แสดงตัวอย่าง PDF ใน iframe เต็มหน้าจอ
    echo '<html><body style="margin: 0; padding: 0;">';
    echo '<iframe src="' . $imagePath . '" width="100%" height="100%" style="border: none;"></iframe>';
    echo '</body></html>';
} else {
    echo "ไม่พบไฟล์ PDF";
}
