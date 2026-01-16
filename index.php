<?php
require_once 'config.php';
check_login();

$user_role = $_SESSION['role'];
$shop_id = $_SESSION['shop_id'];
$shop_name = $_SESSION['shop_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>محل سمير ترانقل - نظام الإدارة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2d3561;
            --secondary: #764ba2;
            --accent: #667eea;
            --success: #06d6a0;
            --warning: #ffc107;
            --danger: #ef476f;
            --light: #f8f9fa;
            --dark: #343a40;
            --border: #e9ecef;
            --profit-color: #10b981;
            --cost-color: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            direction: rtl;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo-section {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 2px solid var(--border);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

.logo-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    padding: 10px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.logo-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

        .logo-text {
            font-size: 20px;
            font-weight: 900;
            margin-bottom: 5px;
        }

        .logo-subtitle {
            font-size: 12px;
            opacity: 0.9;
        }

        .menu {
            flex: 1;
            padding: 20px 0;
        }

        .menu-item {
            padding: 15px 25px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            border-right: 4px solid transparent;
        }

        .menu-item:hover {
            background: var(--light);
            border-right-color: var(--accent);
            color: var(--accent);
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1), transparent);
            border-right-color: var(--accent);
            color: var(--accent);
        }

        .menu-icon {
            font-size: 24px;
            width: 30px;
        }

        .user-section {
            padding: 20px;
            border-top: 2px solid var(--border);
            text-align: center;
        }

        .logout-btn {
            width: 100%;
            padding: 12px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Cairo', sans-serif;
        }

        .logout-btn:hover {
            background: #d63447;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 71, 111, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-right: 280px;
            padding: 30px;
        }

        .content-section {
            display: none;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            font-size: 28px;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 span {
            font-size: 36px;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Cairo', sans-serif;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #05b589;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--dark);
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: #d63447;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            padding: 25px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.profit-card {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-card.cost-card {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 900;
        }

        /* Tables */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background: linear-gradient(135deg, #1a5490 0%, #2d6ca8 100%);
            color: white;
            padding: 16px;
            text-align: right;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 3px solid #0d3d66;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        th:first-child {
            border-radius: 12px 0 0 0;
        }

        th:last-child {
            border-radius: 0 12px 0 0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        tr:hover {
            background: var(--light);
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
        }

        .badge-success {
            background: var(--success);
            color: white;
        }

        .badge-warning {
            background: var(--warning);
            color: var(--dark);
        }

        .badge-danger {
            background: var(--danger);
            color: white;
        }

        .badge-profit {
            background: var(--profit-color);
            color: white;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 25px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

        .close-modal {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert.active {
            display: flex;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }

        .alert-danger {
            background: #ffe4e6;
            color: #be123c;
            border: 2px solid #ef476f;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 2px solid #ffc107;
        }

        /* Cart */
        .cart-item {
            background: var(--light);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .cart-item:hover {
            background: #e9ecef;
            transform: translateX(-5px);
        }

        .profit-indicator {
            background: var(--profit-color);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 700;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .mobile-overlay.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 70px;
            }

            .main-content {
                margin-right: 70px;
            }

            .logo-text,
            .logo-subtitle,
            .menu-item span:not(.menu-icon) {
                display: none;
            }

            .menu-item {
                justify-content: center;
                padding: 15px;
            }

            .user-section {
                padding: 10px;
            }

            .logout-btn {
                font-size: 0;
                padding: 12px;
            }

            .logout-btn:before {
                content: "🚪";
                font-size: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }

            .sidebar {
                position: fixed;
                right: -280px;
                width: 280px;
                height: 100vh;
                transition: right 0.3s ease;
                z-index: 1000;
                box-shadow: -5px 0 15px rgba(0,0,0,0.3);
            }

            .sidebar.mobile-active {
                right: 0;
            }

            .sidebar.mobile-active .logo-text,
            .sidebar.mobile-active .logo-subtitle,
            .sidebar.mobile-active .menu-item span:not(.menu-icon) {
                display: block;
            }

            .sidebar.mobile-active .menu-item {
                justify-content: flex-start;
                padding: 15px 25px;
            }

            .sidebar.mobile-active .logout-btn {
                font-size: 16px;
            }

            .sidebar.mobile-active .logout-btn:before {
                content: "";
            }

            .main-content {
                margin-right: 0;
                padding: 70px 10px 15px 10px;
            }

            .mobile-menu-btn {
                display: block !important;
                position: fixed;
                top: 15px;
                right: 15px;
                z-index: 999;
                background: var(--primary);
                color: white;
                border: none;
                padding: 15px 18px;
                border-radius: 10px;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 4px 10px rgba(0,0,0,0.3);
                touch-action: manipulation;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 24px;
            }

            .header {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .header h1 {
                font-size: 20px;
            }

            .header > div {
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }

            .header input,
            .header select,
            .header button {
                width: 100%;
                min-width: auto !important;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -10px;
                padding: 0 10px;
            }

            table {
                min-width: 100%;
                font-size: 12px;
            }

            th, td {
                padding: 10px 6px;
                font-size: 12px;
                white-space: nowrap;
            }

            table th:first-child,
            table td:first-child {
                position: sticky;
                right: 0;
                background: white;
                z-index: 10;
            }

            table th:first-child {
                background: linear-gradient(135deg, #1a5490 0%, #2d6ca8 100%);
            }

            .btn {
                padding: 12px 16px;
                font-size: 14px;
                min-height: 44px;
                touch-action: manipulation;
            }

            table .btn {
                padding: 8px 12px;
                font-size: 12px;
                margin: 2px;
                display: inline-block;
            }

            td > .btn {
                margin-left: 5px;
            }

            .modal-content {
                width: 95%;
                max-width: 95%;
                max-height: 90vh;
                margin: 10px;
            }

            .modal-header h2 {
                font-size: 18px;
            }

            .modal-body {
                padding: 20px 15px;
            }

            .sale-grid {
                grid-template-columns: 1fr !important;
                gap: 15px;
            }

            .sale-filters {
                flex-direction: column !important;
                gap: 10px;
            }

            .sale-filters select,
            .sale-filters input {
                width: 100% !important;
                min-width: 100% !important;
            }

            #components-list > div {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
                padding: 12px !important;
            }

            #components-list > div > div:last-child {
                width: 100%;
                display: flex;
                justify-content: space-between;
                gap: 10px;
            }

            #components-list input[type="number"] {
                flex: 1;
                max-width: 80px;
            }

            .cart-item {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 12px;
            }

            .cart-item > div:first-child {
                width: 100%;
            }

            .cart-item > div:last-child {
                width: 100%;
                justify-content: space-between;
                display: flex;
            }

            #package-components-list > div {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
                padding: 10px;
            }

            #package-components-list label {
                width: 100%;
                margin-bottom: 5px;
            }

            #package-components-list input[type="number"] {
                width: 100% !important;
                max-width: 150px;
            }

            input[type="text"],
            input[type="number"],
            input[type="date"],
            select,
            textarea {
                font-size: 16px !important;
                padding: 12px !important;
                min-height: 44px;
            }

            .alert {
                margin: 10px;
                font-size: 14px;
            }

            .badge {
                font-size: 11px;
                padding: 4px 8px;
            }

            .logo-section {
                padding: 20px 15px;
            }

.logo-icon {
    width: 60px;
    height: 60px;
    margin-bottom: 10px;
}

            .logo-text {
                font-size: 18px;
            }

            .content-section {
                padding: 20px 15px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 65px 5px 10px 5px;
            }

            .content-section {
                padding: 15px;
            }

            .header h1 {
                font-size: 18px;
            }

            .header h1 span {
                font-size: 24px;
            }

            .stat-value {
                font-size: 22px;
            }

            .stat-icon {
                font-size: 32px;
            }

            table {
                font-size: 11px;
            }

            th, td {
                padding: 8px 4px;
            }

            .btn {
                padding: 10px 12px;
                font-size: 13px;
            }

            table .btn {
                padding: 6px 8px;
                font-size: 11px;
            }

            .modal-content {
                width: 98%;
            }

            .modal-header {
                padding: 15px;
            }

            .modal-body {
                padding: 15px 10px;
            }
        }

        @media (min-width: 769px) {
            .mobile-menu-btn,
            .mobile-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div id="alert-container"></div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay" onclick="toggleMobileMenu()"></div>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-section">
                <div class="logo-icon">
                    <img src="./LogoB.png" alt="Logo Samir">
                </div>
                <div class="logo-text">محل سمير ترانقل</div>
                <div class="logo-subtitle">نظام إدارة متطور</div>
                <?php if ($user_role === 'seller'): ?>
                    <div style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 8px; margin-top: 10px; font-size: 12px;">
                        📍 <?php echo htmlspecialchars($shop_name); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="menu">
                <?php if ($user_role === 'admin'): ?>
                    <!-- قائمة المدير -->
                    <div class="menu-item" data-section="dashboard">
                        <span class="menu-icon">📊</span>
                        <span>لوحة التحكم</span>
                    </div>
                    <div class="menu-item" data-section="components">
                        <span class="menu-icon">📦</span>
                        <span>إدارة المكونات</span>
                    </div>
                    <div class="menu-item" data-section="sale">
                        <span class="menu-icon">🛒</span>
                        <span>البيع السريع</span>
                    </div>
                    <div class="menu-item" data-section="invoices">
                        <span class="menu-icon">📄</span>
                        <span>الوصولات</span>
                    </div>
                    <div class="menu-item" data-section="reports">
                        <span class="menu-icon">📈</span>
                        <span>التقارير</span>
                    </div>
                    <div class="menu-item" data-section="shops">
                        <span class="menu-icon">🏪</span>
                        <span>إدارة المحلات</span>
                    </div>
                    <div class="menu-item" data-section="users">
                        <span class="menu-icon">👥</span>
                        <span>إدارة المستخدمين</span>
                    </div>
                <?php else: ?>
                    <!-- قائمة البائع -->
                    <div class="menu-item active" data-section="sale">
                        <span class="menu-icon">🛒</span>
                        <span>البيع السريع</span>
                    </div>
                    <div class="menu-item" data-section="components">
                        <span class="menu-icon">📦</span>
                        <span>المكونات</span>
                    </div>
                    <div class="menu-item" data-section="invoices">
                        <span class="menu-icon">📄</span>
                        <span>الوصولات</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="user-section">
                <button class="logout-btn" onclick="logout()">
                    🚪 تسجيل الخروج
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if ($user_role === 'admin'): ?>
            <!-- Dashboard Section (للمدير فقط) -->
            <div class="content-section active" id="dashboard">
                <div class="header">
                    <h1>
                        <span>📊</span>
                        لوحة التحكم
                    </h1>
                    <select id="dashboard-shop-filter" onchange="loadDashboard()" style="padding: 12px 20px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'Cairo';">
                        <option value="">كل المحلات</option>
                    </select>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">مبيعات اليوم</div>
                        <div class="stat-value" id="daily-sales">0 دج</div>
                    </div>
                    <div class="stat-card profit-card">
                        <div class="stat-icon">💚</div>
                        <div class="stat-label">ربح اليوم</div>
                        <div class="stat-value" id="daily-profit">0 دج</div>
                    </div>
                    <div class="stat-card cost-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-label">تكلفة اليوم</div>
                        <div class="stat-value" id="daily-cost">0 دج</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-label">عدد المكونات</div>
                        <div class="stat-value" id="total-components">0</div>
                    </div>
                    <div class="stat-card" style="background: linear-gradient(135deg, #ef476f, #d63447);">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-label">منتجات منخفضة</div>
                        <div class="stat-value" id="low-stock">0</div>
                    </div>
                </div>

                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">⚠️ المنتجات المنخفضة</h3>
                    <table id="low-stock-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>المحل</th>
                                <th>الفئة</th>
                                <th>الكمية</th>
                                <th>الحد الأدنى</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Components Section -->
            <div class="content-section <?php echo $user_role === 'seller' ? 'active' : ''; ?>" id="components">
                <div class="header">
                    <h1>
                        <span>📦</span>
                        <?php echo $user_role === 'admin' ? 'إدارة المكونات' : 'المكونات'; ?>
                    </h1>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if ($user_role === 'admin'): ?>
                        <select id="filter-shop" onchange="loadComponents()" style="padding: 12px 20px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'Cairo'; min-width: 150px;">
                            <option value="">كل المحلات</option>
                        </select>
                        <?php endif; ?>
                        <input type="text" id="search-components" placeholder="🔍 بحث في المكونات..." 
                               style="padding: 12px 20px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'Cairo'; min-width: 200px;">
                        <select id="filter-components-category" style="padding: 12px 20px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'Cairo'; min-width: 150px;">
                            <option value="">كل الفئات</option>
                        </select>
                        <button class="btn btn-primary" onclick="openComponentModal()">
                            ➕ إضافة مكون جديد
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table id="components-table">
                        <thead>
                            <tr>
                                <th>المعرف</th>
                                <?php if ($user_role === 'admin'): ?>
                                <th>المحل</th>
                                <?php endif; ?>
                                <th>الاسم</th>
                                <th>الفئة</th>
                                <th>سعر الشراء</th>
                                <th>سعر البيع</th>
                                <th>الربح</th>
                                <th>الكمية</th>
                                <th>الوحدة</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Sale Section -->
            <div class="content-section <?php echo $user_role === 'seller' ? 'active' : ''; ?>" id="sale">
                <div class="header">
                    <h1>
                        <span>🛒</span>
                        البيع السريع
                    </h1>
                    <?php if ($user_role === 'admin'): ?>
                    <select id="sale-shop-select" style="padding: 12px 20px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'Cairo'; min-width: 200px;">
                        <option value="">اختر المحل</option>
                    </select>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;" class="sale-grid">
                    <div>
                        <div class="table-container" style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                                <h3 style="color: var(--primary); margin: 0;">اختر المكونات</h3>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;" class="sale-filters">
                                    <select id="filter-sale-category" style="padding: 10px 15px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Cairo'; min-width: 120px; flex: 1;">
                                        <option value="">كل الفئات</option>
                                    </select>
                                    <input type="text" id="search-sale-components" placeholder="🔍 بحث..." 
                                           style="padding: 10px 15px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Cairo'; min-width: 150px; flex: 1;">
                                </div>
                            </div>
                            <div id="components-list"></div>
                        </div>
                    </div>

                    <!-- Cart -->
                    <div>
                        <div class="table-container">
                            <h3 style="margin-bottom: 15px; color: var(--primary);">🛒 السلة</h3>
                            <div id="cart-items"></div>
                            
                            <div style="margin-top: 20px; padding: 15px; background: var(--light); border-radius: 10px;">
                                <div style="display: flex; justify-content: space-between; padding-top: 10px;">
                                    <span style="font-weight: 900; font-size: 18px;">المجموع الكلي:</span>
                                    <span id="cart-total" style="color: var(--accent); font-size: 22px; font-weight: 900;">0 دج</span>
                                </div>
                            </div>

                            <button class="btn btn-success" onclick="completeSale()" 
                                    style="width: 100%; margin-top: 15px; padding: 15px; font-size: 18px;">
                                ✅ إتمام البيع
                            </button>
                            
                            <button class="btn btn-primary" onclick="openPackageModal()" 
                                    style="width: 100%; margin-top: 10px; padding: 12px;">
                                📦 إنشاء تجميعة فورية
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Section -->
            <div class="content-section" id="invoices">
                <div class="header">
                    <h1>
                        <span>📄</span>
                        سجل الوصولات
                    </h1>
                </div>

                <div class="table-container" style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">🔍 بحث وفلترة الوصولات</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                        <?php if ($user_role === 'admin'): ?>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666;">المحل</label>
                            <select id="filter-invoice-shop" style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-family: 'Cairo';">
                                <option value="">كل المحلات</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666;">رقم الوصل</label>
                            <input type="text" id="filter-invoice-number" placeholder="INV-20241228..." 
                                   style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-family: 'Cairo';">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #666;">اليوم</label>
                            <input type="date" id="filter-date" 
                                   style="width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: 8px; font-family: 'Cairo';">
                        </div>
                        <div style="display: flex; align-items: flex-end; gap: 10px;">
                            <button class="btn btn-primary" onclick="filterInvoices()" style="flex: 1;">
                                🔍 بحث
                            </button>
                            <button class="btn btn-warning" onclick="resetInvoiceFilters()" style="flex: 1;">
                                🔄 إعادة تعيين
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="invoices-table">
                        <thead>
                            <tr>
                                <th>رقم الوصل</th>
                                <?php if ($user_role === 'admin'): ?>
                                <th>المحل</th>
                                <?php endif; ?>
                                <th>المبلغ</th>
                                <th>التكلفة</th>
                                <th>الربح</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <?php if ($user_role === 'admin'): ?>
            <!-- Reports Section -->
            <div class="content-section" id="reports">
                <div class="header">
                    <h1>
                        <span>📈</span>
                        التقارير والإحصائيات
                    </h1>
                    <select id="reports-shop-filter" onchange="loadReports()" style="padding: 12px 20px; border: 2px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'Cairo';">
                        <option value="">كل المحلات</option>
                    </select>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-label">مبيعات اليوم</div>
                        <div class="stat-value" id="report-daily-sales">0 دج</div>
                    </div>
                    <div class="stat-card profit-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">ربح اليوم</div>
                        <div class="stat-value" id="report-daily-profit">0 دج</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-label">مبيعات الشهر</div>
                        <div class="stat-value" id="report-monthly-sales">0 دج</div>
                    </div>
                    <div class="stat-card profit-card">
                        <div class="stat-icon">💚</div>
                        <div class="stat-label">ربح الشهر</div>
                        <div class="stat-value" id="report-monthly-profit">0 دج</div>
                    </div>
                </div>

                <div class="table-container">
                    <h3 style="margin-bottom: 15px; color: var(--primary);">🏆 الأكثر مبيعاً</h3>
                    <table id="top-selling-table">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>النوع</th>
                                <th>الكمية المباعة</th>
                                <th>الإيراد</th>
                                <th>الربح</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Shops Section -->
            <div class="content-section" id="shops">
                <div class="header">
                    <h1>
                        <span>🏪</span>
                        إدارة المحلات
                    </h1>
                    <button class="btn btn-primary" onclick="openShopModal()">
                        ➕ إضافة محل جديد
                    </button>
                </div>

                <div class="table-container">
                    <table id="shops-table">
                        <thead>
                            <tr>
                                <th>المعرف</th>
                                <th>اسم المحل</th>
                                <th>الموقع</th>
                                <th>الهاتف</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Users Section -->
            <div class="content-section" id="users">
                <div class="header">
                    <h1>
                        <span>👥</span>
                        إدارة المستخدمين
                    </h1>
                    <button class="btn btn-primary" onclick="openUserModal()">
                        ➕ إضافة مستخدم جديد
                    </button>
                </div>

                <div class="table-container">
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th>المعرف</th>
                                <th>اسم المستخدم</th>
                                <th>الاسم الكامل</th>
                                <th>الدور</th>
                                <th>المحل</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Component Modal -->
    <div class="modal" id="component-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>إضافة / تعديل مكون</h2>
                <button class="close-modal" onclick="closeModal('component-modal')">✖</button>
            </div>
            <div class="modal-body">
                <form id="component-form" onsubmit="saveComponent(event)">
                    <input type="hidden" id="component-id">
                    
                    <?php if ($user_role === 'admin'): ?>
                    <div class="form-group">
                        <label>المحل *</label>
                        <select id="component-shop" required>
                            <option value="">اختر المحل</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>اسم المكون *</label>
                        <input type="text" id="component-name" required>
                    </div>

                    <div class="form-group">
                        <label>الفئة *</label>
                        <input type="text" id="component-category" required>
                    </div>

                    <div class="form-group">
                        <label>وحدة القياس *</label>
                        <select id="component-unit-type" required>
                            <option value="piece">وحدة (قطعة)</option>
                            <option value="meter">متر (طول)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>سعر الشراء (دج) * 💰</label>
                            <input type="number" step="0.01" id="component-purchase-price" required>
                            <small style="color: #666;">السعر من المورد</small>
                        </div>

                        <div class="form-group">
                            <label>سعر البيع (دج) * 💵</label>
                            <input type="number" step="0.01" id="component-selling-price" required>
                            <small style="color: #666;">السعر للزبون</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>الكمية *</label>
                            <input type="number" step="0.01" id="component-quantity" required>
                        </div>

                        <div class="form-group">
                            <label>الحد الأدنى *</label>
                            <input type="number" step="0.01" id="component-min" value="5" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>الوصف</label>
                        <textarea id="component-description" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                        💾 حفظ
                    </button>
                </form>
            </div>
        </div>
    </div>

<!-- Package Modal -->
    <div class="modal" id="package-modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>📦 إنشاء تجميعة فورية</h2>
                <button class="close-modal" onclick="closeModal('package-modal')">✖</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>اسم التجميعة *</label>
                    <input type="text" id="package-name" placeholder="مثال: تجميعة غرفة كاملة" required>
                </div>
                
                <!-- قسم عرض المكونات المختارة -->
                <div id="selected-components-display" style="display: none; margin: 15px 0; padding: 15px; background: linear-gradient(135deg, #667eea15, #764ba215); border-radius: 10px; border: 2px solid var(--accent);">
                    <h4 style="margin-bottom: 10px; color: var(--accent); display: flex; align-items: center; gap: 8px;">
                        <span>✅</span>
                        <span>المكونات المختارة (<span id="selected-count">0</span>)</span>
                    </h4>
                    <div id="selected-components-list" style="display: flex; flex-wrap: wrap; gap: 8px;"></div>
                </div>

                <h3 style="margin: 20px 0 15px; color: var(--primary);">اختر المكونات:</h3>
                
                <!-- فلاتر البحث -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <input type="text" id="package-search" placeholder="🔍 بحث في المكونات..." 
                               oninput="filterPackageComponents()"
                               style="padding: 10px 15px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Cairo';">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <select id="package-category-filter" onchange="filterPackageComponents()"
                                style="padding: 10px 15px; border: 2px solid var(--border); border-radius: 8px; font-size: 14px; font-family: 'Cairo';">
                            <option value="">كل الفئات</option>
                        </select>
                    </div>
                </div>
                
                <div id="package-components-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px;"></div>

                <div style="margin-top: 20px; padding: 20px; background: var(--light); border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                        <span style="font-weight: 600; font-size: 16px;">التكلفة الإجمالية:</span>
                        <span id="package-total-cost" style="color: var(--cost-color); font-weight: 700; font-size: 18px;">0 دج</span>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label>سعر البيع للتجميعة (دج) * 💵</label>
                        <input type="number" step="0.01" id="package-selling-price" required 
                               style="font-size: 18px; font-weight: 700; padding: 15px;"
                               placeholder="أدخل سعر البيع">
                        <small id="package-price-error" style="color: var(--danger); display: none; margin-top: 5px;">
                            ⚠️ السعر يجب أن لا يقل عن التكلفة!
                        </small>
                    </div>
                </div>

                <button class="btn btn-success" onclick="addPackageToCart()" style="width: 100%; margin-top: 15px; padding: 15px; font-size: 18px;">
                    ➕ إضافة التجميعة للسلة
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Details Modal -->
    <div class="modal" id="invoice-modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2>تفاصيل الوصل</h2>
                <button class="close-modal" onclick="closeModal('invoice-modal')">✖</button>
            </div>
            <div class="modal-body">
                <div id="invoice-details"></div>
            </div>
        </div>
    </div>
    
    <!-- Sale Success Modal -->
    <div class="modal" id="sale-success-modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #06d6a0, #10b981); color: white;">
                <h2 style="display: flex; align-items: center; gap: 10px; color: white;">
                    <span style="font-size: 40px;">✅</span>
                    <span>تم البيع بنجاح!</span>
                </h2>
            </div>
            <div class="modal-body">
                <div style="text-align: center; padding: 20px;">
                    <div style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 25px; border-radius: 15px; margin-bottom: 20px;">
                        <div style="margin-bottom: 15px;">
                            <span style="color: #666; font-size: 15px;">رقم الوصل</span>
                            <div id="success-invoice-number" style="font-size: 24px; font-weight: 900; color: var(--primary); margin-top: 5px;">
                                #12345
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                            <div style="background: white; padding: 15px; border-radius: 10px; border: 2px solid var(--border);">
                                <span style="color: #666; font-size: 13px; display: block; margin-bottom: 5px;">المبلغ الكلي</span>
                                <div id="success-total-amount" style="font-size: 20px; font-weight: 700; color: var(--accent);">
                                    0 دج
                                </div>
                            </div>
                            <div style="background: white; padding: 15px; border-radius: 10px; border: 2px solid var(--profit-color);">
                                <span style="color: #666; font-size: 13px; display: block; margin-bottom: 5px;">الربح الصافي</span>
                                <div id="success-profit" style="font-size: 20px; font-weight: 700; color: var(--profit-color);">
                                    0 دج
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button class="btn btn-success" onclick="printInvoiceFromSuccess()" style="padding: 15px; font-size: 16px;">
                            🖨️ طباعة الوصل
                        </button>
                        <button class="btn btn-primary" onclick="viewInvoiceFromSuccess()" style="padding: 15px; font-size: 16px;">
                            👁️ عرض التفاصيل
                        </button>
                    </div>

                    <button class="btn" onclick="closeModal('sale-success-modal')" style="width: 100%; margin-top: 10px; padding: 12px; background: #e9ecef; color: var(--dark);">
                        ✖ إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Price Edit Modal -->
    <div class="modal" id="price-edit-modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>💵 تعديل سعر البيع</h2>
                <button class="close-modal" onclick="closeModal('price-edit-modal')">✖</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-price-index">
                
                <div class="form-group">
                    <label>المنتج:</label>
                    <div id="edit-price-item-name" style="font-weight: 700; font-size: 16px; color: var(--primary);"></div>
                </div>

                <div class="form-group">
                    <label>سعر البيع الحالي:</label>
                    <div id="edit-price-current" style="color: #666; font-weight: 600; font-size: 18px;"></div>
                </div>

                <div class="form-group">
                    <label>سعر البيع الجديد (دج) * 💵</label>
                    <input type="number" step="0.01" id="edit-price-new" required 
                           style="font-size: 18px; font-weight: 700; padding: 15px;">
                </div>

                <button class="btn btn-success" onclick="saveNewPrice()" style="width: 100%; padding: 15px;">
                    ✅ حفظ السعر الجديد
                </button>
            </div>
        </div>
    </div>

    <?php if ($user_role === 'admin'): ?>
    <!-- Shop Modal -->
    <div class="modal" id="shop-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>إضافة / تعديل محل</h2>
                <button class="close-modal" onclick="closeModal('shop-modal')">✖</button>
            </div>
            <div class="modal-body">
                <form id="shop-form" onsubmit="saveShop(event)">
                    <input type="hidden" id="shop-id">
                    
                    <div class="form-group">
                        <label>اسم المحل *</label>
                        <input type="text" id="shop-name" required>
                    </div>

                    <div class="form-group">
                        <label>الموقع</label>
                        <input type="text" id="shop-location">
                    </div>

                    <div class="form-group">
                        <label>الهاتف</label>
                        <input type="text" id="shop-phone">
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="shop-active" checked>
                            نشط
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                        💾 حفظ
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal" id="user-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>إضافة / تعديل مستخدم</h2>
                <button class="close-modal" onclick="closeModal('user-modal')">✖</button>
            </div>
            <div class="modal-body">
                <form id="user-form" onsubmit="saveUser(event)">
                    <input type="hidden" id="user-id">
                    
                    <div class="form-group">
                        <label>اسم المستخدم *</label>
                        <input type="text" id="user-username" required>
                    </div>

                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" id="user-password">
                        <small>اتركها فارغة للحفاظ على كلمة المرور الحالية</small>
                    </div>

                    <div class="form-group">
                        <label>الاسم الكامل *</label>
                        <input type="text" id="user-fullname" required>
                    </div>

                    <div class="form-group">
                        <label>الدور *</label>
                        <select id="user-role" required onchange="toggleShopField()">
                            <option value="seller">بائع</option>
                            <option value="admin">مدير</option>
                        </select>
                    </div>

                    <div class="form-group" id="user-shop-group">
                        <label>المحل *</label>
                        <select id="user-shop"></select>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="user-active" checked>
                            نشط
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">
                        💾 حفظ
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        const userRole = '<?php echo $user_role; ?>';
        const userShopId = <?php echo $shop_id ?? 'null'; ?>;
    </script>
    <script src="./main.js"></script>
    <script src="./multishop.js"></script>

</body>
</html>