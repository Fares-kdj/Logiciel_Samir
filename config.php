<?php
// ملف الاتصال بقاعدة البيانات
// config.php

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'curtain_rods_shop');

// إنشاء الاتصال
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// دالة تنظيف المدخلات
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// دالة إرجاع استجابة JSON
function json_response($success, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// بدء الجلسة
session_start();

// دالة التحقق من تسجيل الدخول
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// دالة التحقق من صلاحية المدير
function check_admin() {
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        json_response(false, 'غير مصرح لك بالوصول');
    }
}

// دالة الحصول على shop_id المناسب
function get_shop_filter() {
    if ($_SESSION['role'] === 'admin') {
        return null; // المدير يرى كل المحلات
    }
    return $_SESSION['shop_id']; // البائع يرى محله فقط
}

// دالة التحقق من الوصول للمحل
function can_access_shop($shop_id) {
    if ($_SESSION['role'] === 'admin') {
        return true; // المدير يصل لكل المحلات
    }
    return $_SESSION['shop_id'] == $shop_id; // البائع يصل لمحله فقط
}

// دالة تسجيل الخروج
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
