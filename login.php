<?php
require_once 'config.php';

// إذا كان المستخدم مسجلاً، إعادة التوجيه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        // التحقق من كلمة المرور
        $passwordValid = false;
        
        // إذا كانت كلمة المرور مشفرة
        if (password_verify($password, $user['password'])) {
            $passwordValid = true;
        }
        // إذا كانت كلمة المرور غير مشفرة (للمرة الأولى)
        else if ($user['password'] === $password) {
            $passwordValid = true;
            // تشفير كلمة المرور
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $user['id']]);
        }
        
        if ($passwordValid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['shop_id'] = $user['shop_id'];
            
            // جلب معلومات المحل إذا كان بائعاً
            if ($user['shop_id']) {
                $shopStmt = $conn->prepare("SELECT name FROM shops WHERE id = ?");
                $shopStmt->execute([$user['shop_id']]);
                $shop = $shopStmt->fetch();
                $_SESSION['shop_name'] = $shop ? $shop['name'] : '';
            }
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>تسجيل الدخول - محل سمير ترانقل</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(233, 69, 96, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
            pointer-events: none;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: clamp(25px, 5vw, 45px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            position: relative;
            backdrop-filter: blur(10px);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: clamp(25px, 5vw, 35px);
        }

        .logo-icon {
            width: clamp(60px, 15vw, 75px);
            height: clamp(60px, 15vw, 75px);
            background: linear-gradient(135deg, #e94560 0%, #533483 100%);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            box-shadow: 0 8px 25px rgba(233, 69, 96, 0.3);
            padding: 12px;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        h1 {
            font-size: clamp(22px, 5vw, 26px);
            color: #1a1a2e;
            font-weight: 900;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .subtitle {
            color: #666;
            font-size: clamp(13px, 3vw, 15px);
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: clamp(14px, 3vw, 15px);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: clamp(15px, 3.5vw, 16px);
            font-family: 'Tajawal', sans-serif;
            transition: all 0.3s ease;
            background: #f8f9fa;
            -webkit-appearance: none;
            appearance: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #e94560;
            background: white;
            box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #e94560 0%, #533483 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: clamp(16px, 4vw, 18px);
            font-weight: 700;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(233, 69, 96, 0.3);
            -webkit-tap-highlight-color: transparent;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(233, 69, 96, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);
        }

        .error {
            background: #ffe0e0;
            color: #c00;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-right: 4px solid #c00;
            font-weight: 500;
            font-size: clamp(13px, 3vw, 14px);
            animation: shake 0.5s ease;
            line-height: 1.5;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .info-box {
            background: #e8f4fd;
            padding: 14px;
            border-radius: 10px;
            margin-top: 20px;
            border-right: 4px solid #2196F3;
            font-size: clamp(12px, 3vw, 13px);
            color: #1976D2;
            line-height: 1.6;
        }

        .info-box strong {
            display: block;
            margin-bottom: 6px;
            font-size: clamp(13px, 3vw, 14px);
        }

        .info-box > div {
            margin-bottom: 8px;
        }

        .info-box > div:last-child {
            margin-bottom: 0;
        }

        /* تحسينات للشاشات الصغيرة جداً */
        @media (max-width: 380px) {
            body {
                padding: 10px;
            }

            .login-container {
                padding: 20px 15px;
                border-radius: 16px;
            }

            .logo {
                margin-bottom: 20px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            input[type="text"],
            input[type="password"] {
                padding: 12px 15px;
            }

            .btn-login {
                padding: 14px;
            }

            .info-box {
                padding: 12px;
                margin-top: 16px;
            }
        }

        /* تحسينات للشاشات الكبيرة */
        @media (min-width: 768px) {
            body {
                padding: 30px;
            }

            .login-container {
                padding: 45px 40px;
            }

            .logo {
                margin-bottom: 35px;
            }

            .form-group {
                margin-bottom: 24px;
            }

            input[type="text"],
            input[type="password"] {
                padding: 15px 20px;
            }

            .btn-login {
                padding: 17px;
            }

            .info-box {
                margin-top: 25px;
                padding: 16px;
            }
        }

        /* إصلاح مشاكل الزووم في iOS */
        @supports (-webkit-touch-callout: none) {
            input[type="text"],
            input[type="password"] {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="logo">
                <div class="logo-icon">
                    <img src="./LogoB.png" alt="Logo Samir">
                </div>
                <h1>محل سمير ترانقل</h1>
                <p class="subtitle">نظام إدارة ذكي ومتطور</p>
            </div>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-login">تسجيل الدخول</button>
            </form>

            <div class="info-box">
                <strong>للتجربة:</strong>
                <div>
                    <strong>المدير:</strong> admin / admin123
                </div>
                <div>
                    <strong>بائع المحل الأول:</strong> seller1 / admin123
                </div>
                <div>
                    <strong>بائع المحل الثاني:</strong> seller2 / admin123
                </div>
            </div>
        </div>
    </div>
</body>
</html>