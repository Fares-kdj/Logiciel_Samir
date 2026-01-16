<?php
require_once 'config.php';
check_login();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

// ========== المكونات ==========
if ($action == 'get_components') {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $shop_id = $_GET['shop_id'] ?? get_shop_filter();
    
    $query = "SELECT * FROM components WHERE 1=1";
    $params = [];
    
    // فلترة حسب المحل
    if ($shop_id !== null) {
        $query .= " AND shop_id = ?";
        $params[] = $shop_id;
    }
    
    if ($search) {
        $query .= " AND (name LIKE ? OR category LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY category, name";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    $components = $stmt->fetchAll();
    json_response(true, 'تم جلب المكونات', $components);
}

if ($action == 'add_component') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // تحديد shop_id
    $shop_id = $data['shop_id'] ?? $_SESSION['shop_id'];
    
    // التحقق من الصلاحية
    if (!can_access_shop($shop_id)) {
        json_response(false, 'غير مصرح لك بإضافة مكونات لهذا المحل');
    }
    
    $stmt = $conn->prepare("INSERT INTO components (shop_id, name, category, purchase_price, selling_price, quantity, unit_type, min_quantity, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $shop_id,
        $data['name'],
        $data['category'],
        $data['purchase_price'],
        $data['selling_price'],
        $data['quantity'],
        $data['unit_type'] ?? 'piece',
        $data['min_quantity'] ?? 5,
        $data['description'] ?? ''
    ]);
    
    json_response(true, 'تمت إضافة المكون بنجاح', ['id' => $conn->lastInsertId()]);
}

if ($action == 'update_component') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من الصلاحية
    $stmt = $conn->prepare("SELECT shop_id FROM components WHERE id = ?");
    $stmt->execute([$data['id']]);
    $component = $stmt->fetch();
    
    if (!$component || !can_access_shop($component['shop_id'])) {
        json_response(false, 'غير مصرح لك بتعديل هذا المكون');
    }
    
    $stmt = $conn->prepare("UPDATE components SET name=?, category=?, purchase_price=?, selling_price=?, quantity=?, unit_type=?, min_quantity=?, description=? WHERE id=?");
    $stmt->execute([
        $data['name'],
        $data['category'],
        $data['purchase_price'],
        $data['selling_price'],
        $data['quantity'],
        $data['unit_type'],
        $data['min_quantity'],
        $data['description'],
        $data['id']
    ]);
    
    json_response(true, 'تم تحديث المكون بنجاح');
}

if ($action == 'delete_component') {
    $id = $_GET['id'] ?? 0;
    
    // التحقق من الصلاحية
    $stmt = $conn->prepare("SELECT shop_id FROM components WHERE id = ?");
    $stmt->execute([$id]);
    $component = $stmt->fetch();
    
    if (!$component || !can_access_shop($component['shop_id'])) {
        json_response(false, 'غير مصرح لك بحذف هذا المكون');
    }
    
    $stmt = $conn->prepare("DELETE FROM components WHERE id = ?");
    $stmt->execute([$id]);
    
    json_response(true, 'تم حذف المكون بنجاح');
}

// ========== البيع ==========
if ($action == 'create_sale') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // تحديد المحل
    $shop_id = $_SESSION['shop_id']; // البائع يبيع من محله فقط
    if ($_SESSION['role'] === 'admin' && isset($data['shop_id'])) {
        $shop_id = $data['shop_id']; // المدير يمكنه اختيار المحل
    }
    
    if (!$shop_id) {
        json_response(false, 'يجب تحديد المحل');
    }
    
    $conn->beginTransaction();
    
    try {
        // إنشاء رقم الوصل
        $date = date('Ymd');
        $stmt = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE DATE(created_at) = CURDATE() AND shop_id = $shop_id");
        $count = $stmt->fetch()['count'] + 1;
        $invoice_number = "INV-$shop_id-$date-" . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // حساب المجاميع
        $total_amount = 0;
        $total_cost = 0;
        
        // إضافة الوصل
        $stmt = $conn->prepare("INSERT INTO invoices (shop_id, user_id, invoice_number, total_amount, total_cost, total_profit) VALUES (?, ?, ?, 0, 0, 0)");
        $stmt->execute([$shop_id, $_SESSION['user_id'], $invoice_number]);
        $invoice_id = $conn->lastInsertId();
        
        // معالجة كل عنصر
        foreach ($data['items'] as $item) {
            $quantity = $item['quantity'];
            $selling_price = $item['selling_price'];
            
            if ($item['type'] === 'component') {
                // مكون عادي
                $stmt = $conn->prepare("SELECT * FROM components WHERE id = ? AND shop_id = ?");
                $stmt->execute([$item['id'], $shop_id]);
                $component = $stmt->fetch();
                
                if (!$component || $component['quantity'] < $quantity) {
                    throw new Exception('الكمية غير متوفرة: ' . ($component['name'] ?? 'المنتج'));
                }
                
                $purchase_price = $component['purchase_price'];
                $item_cost = $purchase_price * $quantity;
                $item_total = $selling_price * $quantity;
                $item_profit = $item_total - $item_cost;
                
                // إضافة العنصر للوصل
                $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_type, item_name, quantity, unit_type, purchase_price, selling_price, total_cost, total_price, profit) VALUES (?, 'component', ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $invoice_id,
                    $component['name'],
                    $quantity,
                    $component['unit_type'],
                    $purchase_price,
                    $selling_price,
                    $item_cost,
                    $item_total,
                    $item_profit
                ]);
                
                // خصم المخزون
                $stmt = $conn->prepare("UPDATE components SET quantity = quantity - ? WHERE id = ?");
                $stmt->execute([$quantity, $item['id']]);
                
                $total_cost += $item_cost;
                $total_amount += $item_total;
                
            } else if ($item['type'] === 'package') {
                // تجميعة فورية
                $package_components = $item['components'];
                $package_cost = 0;
                $package_details = [];
                
                // حساب تكلفة التجميعة
                foreach ($package_components as $pkg_comp) {
                    $stmt = $conn->prepare("SELECT * FROM components WHERE id = ? AND shop_id = ?");
                    $stmt->execute([$pkg_comp['id'], $shop_id]);
                    $component = $stmt->fetch();
                    
                    if (!$component || $component['quantity'] < $pkg_comp['quantity']) {
                        throw new Exception('الكمية غير متوفرة: ' . ($component['name'] ?? 'المنتج'));
                    }
                    
                    $comp_cost = $component['purchase_price'] * $pkg_comp['quantity'];
                    $package_cost += $comp_cost;
                    
                    $package_details[] = [
                        'id' => $component['id'],
                        'name' => $component['name'],
                        'quantity' => $pkg_comp['quantity'],
                        'unit_type' => $component['unit_type'],
                        'purchase_price' => $component['purchase_price'],
                        'cost' => $comp_cost
                    ];
                    
                    // خصم المخزون
                    $stmt = $conn->prepare("UPDATE components SET quantity = quantity - ? WHERE id = ?");
                    $stmt->execute([$pkg_comp['quantity'], $component['id']]);
                }
                
                $package_total = $selling_price;
                $package_profit = $package_total - $package_cost;
                
                // إضافة التجميعة للوصل
                $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_type, item_name, quantity, unit_type, purchase_price, selling_price, total_cost, total_price, profit, package_details) VALUES (?, 'package', ?, 1, 'piece', ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $invoice_id,
                    $item['name'],
                    $package_cost,
                    $selling_price,
                    $package_cost,
                    $package_total,
                    $package_profit,
                    json_encode($package_details, JSON_UNESCAPED_UNICODE)
                ]);
                
                $total_cost += $package_cost;
                $total_amount += $package_total;
            }
        }
        
        // تحديث المجاميع
        $total_profit = $total_amount - $total_cost;
        $stmt = $conn->prepare("UPDATE invoices SET total_amount = ?, total_cost = ?, total_profit = ? WHERE id = ?");
        $stmt->execute([$total_amount, $total_cost, $total_profit, $invoice_id]);
        
        // سجل العملية
        $stmt = $conn->prepare("INSERT INTO sales_log (invoice_id, action, details) VALUES (?, 'sale_completed', ?)");
        $stmt->execute([$invoice_id, json_encode($data, JSON_UNESCAPED_UNICODE)]);
        
        $conn->commit();
        
        json_response(true, 'تم إتمام البيع بنجاح', [
            'invoice_id' => $invoice_id,
            'invoice_number' => $invoice_number,
            'total_amount' => $total_amount,
            'total_profit' => $total_profit
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        json_response(false, 'خطأ: ' . $e->getMessage());
    }
}


// ========== الوصولات ==========
if ($action == 'get_invoices') {
    $limit = $_GET['limit'] ?? 50;
    $search = $_GET['search'] ?? '';
    $date = $_GET['date'] ?? '';
    $shop_id = $_GET['shop_id'] ?? get_shop_filter();
    
    $query = "SELECT i.*, s.name as shop_name, u.full_name as seller_name 
              FROM invoices i 
              LEFT JOIN shops s ON i.shop_id = s.id
              LEFT JOIN users u ON i.user_id = u.id
              WHERE 1=1";
    $params = [];
    
    // فلترة حسب المحل
    if ($shop_id !== null) {
        $query .= " AND i.shop_id = ?";
        $params[] = $shop_id;
    }
    
    if ($search) {
        $query .= " AND i.invoice_number LIKE ?";
        $params[] = "%$search%";
    }
    
    if ($date) {
        $query .= " AND DATE(i.created_at) = ?";
        $params[] = $date;
    }
    
    $query .= " ORDER BY i.created_at DESC LIMIT ?";
    $params[] = (int)$limit;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();
    
    json_response(true, 'تم جلب الوصولات', $invoices);
}

if ($action == 'get_invoice_details') {
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("SELECT i.*, s.name as shop_name, u.full_name as seller_name 
                            FROM invoices i
                            LEFT JOIN shops s ON i.shop_id = s.id
                            LEFT JOIN users u ON i.user_id = u.id
                            WHERE i.id = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch();
    
    if (!$invoice || !can_access_shop($invoice['shop_id'])) {
        json_response(false, 'غير مصرح لك بعرض هذا الوصل');
    }
    
    $stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    $invoice['items'] = $items;
    json_response(true, 'تم جلب تفاصيل الوصل', $invoice);
}

if ($action == 'delete_invoice') {
    $id = $_GET['id'] ?? 0;
    
    // التحقق من الصلاحية
    $stmt = $conn->prepare("SELECT shop_id FROM invoices WHERE id = ?");
    $stmt->execute([$id]);
    $invoice = $stmt->fetch();
    
    if (!$invoice || !can_access_shop($invoice['shop_id'])) {
        json_response(false, 'غير مصرح لك بحذف هذا الوصل');
    }
    
    $conn->beginTransaction();
    
    try {
        $stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        
        $conn->commit();
        json_response(true, 'تم حذف الوصل بنجاح');
        
    } catch (Exception $e) {
        $conn->rollBack();
        json_response(false, 'حدث خطأ: ' . $e->getMessage());
    }
}

// ========== التقارير ==========
if ($action == 'get_daily_report') {
    $shop_id = $_GET['shop_id'] ?? get_shop_filter();
    
    $query = "SELECT 
                COUNT(*) as total_invoices,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(SUM(total_cost), 0) as total_cost,
                COALESCE(SUM(total_profit), 0) as total_profit
              FROM invoices 
              WHERE DATE(created_at) = CURDATE()";
    
    if ($shop_id !== null) {
        $query .= " AND shop_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$shop_id]);
    } else {
        $stmt = $conn->query($query);
    }
    
    $report = $stmt->fetch();
    json_response(true, 'تقرير اليوم', $report);
}

if ($action == 'get_monthly_report') {
    $shop_id = $_GET['shop_id'] ?? get_shop_filter();
    
    $query = "SELECT 
                COUNT(*) as total_invoices,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(SUM(total_cost), 0) as total_cost,
                COALESCE(SUM(total_profit), 0) as total_profit
              FROM invoices 
              WHERE MONTH(created_at) = MONTH(CURDATE()) 
              AND YEAR(created_at) = YEAR(CURDATE())";
    
    if ($shop_id !== null) {
        $query .= " AND shop_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$shop_id]);
    } else {
        $stmt = $conn->query($query);
    }
    
    $report = $stmt->fetch();
    json_response(true, 'تقرير الشهر', $report);
}

if ($action == 'get_shops_summary') {
    check_admin(); // فقط للمدير
    
    $stmt = $conn->query("
        SELECT 
            s.id,
            s.name,
            COALESCE(SUM(CASE WHEN DATE(i.created_at) = CURDATE() THEN i.total_amount ELSE 0 END), 0) as daily_sales,
            COALESCE(SUM(CASE WHEN DATE(i.created_at) = CURDATE() THEN i.total_profit ELSE 0 END), 0) as daily_profit,
            COALESCE(SUM(CASE WHEN MONTH(i.created_at) = MONTH(CURDATE()) AND YEAR(i.created_at) = YEAR(CURDATE()) THEN i.total_amount ELSE 0 END), 0) as monthly_sales,
            COALESCE(SUM(CASE WHEN MONTH(i.created_at) = MONTH(CURDATE()) AND YEAR(i.created_at) = YEAR(CURDATE()) THEN i.total_profit ELSE 0 END), 0) as monthly_profit
        FROM shops s
        LEFT JOIN invoices i ON s.id = i.shop_id
        WHERE s.is_active = 1
        GROUP BY s.id, s.name
    ");
    
    $summary = $stmt->fetchAll();
    json_response(true, 'ملخص المحلات', $summary);
}

if ($action == 'get_top_selling') {
    $shop_id = $_GET['shop_id'] ?? get_shop_filter();
    
    $query = "SELECT 
                ii.item_name,
                ii.item_type,
                SUM(ii.quantity) as total_sold,
                SUM(ii.total_price) as total_revenue,
                SUM(ii.profit) as total_profit
              FROM invoice_items ii
              JOIN invoices i ON ii.invoice_id = i.id";
    
    if ($shop_id !== null) {
        $query .= " WHERE i.shop_id = ?";
    }
    
    $query .= " GROUP BY ii.item_name, ii.item_type
                ORDER BY total_sold DESC 
                LIMIT 10";
    
    if ($shop_id !== null) {
        $stmt = $conn->prepare($query);
        $stmt->execute([$shop_id]);
    } else {
        $stmt = $conn->query($query);
    }
    
    $items = $stmt->fetchAll();
    json_response(true, 'الأكثر مبيعاً', $items);
}

if ($action == 'get_low_stock') {
    $shop_id = $_GET['shop_id'] ?? get_shop_filter();
    
    $query = "SELECT c.*, s.name as shop_name 
              FROM components c
              LEFT JOIN shops s ON c.shop_id = s.id
              WHERE c.quantity <= c.min_quantity";
    
    if ($shop_id !== null) {
        $query .= " AND c.shop_id = ?";
    }
    
    $query .= " ORDER BY c.quantity ASC";
    
    if ($shop_id !== null) {
        $stmt = $conn->prepare($query);
        $stmt->execute([$shop_id]);
    } else {
        $stmt = $conn->query($query);
    }
    
    $items = $stmt->fetchAll();
    json_response(true, 'تم جلب المنتجات المنخفضة', $items);
}

// ========== إدارة المحلات (للمدير فقط) ==========
if ($action == 'get_shops') {
    check_admin();
    
    $stmt = $conn->query("SELECT * FROM shops ORDER BY name");
    $shops = $stmt->fetchAll();
    
    json_response(true, 'تم جلب المحلات', $shops);
}

if ($action == 'add_shop') {
    check_admin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $conn->prepare("INSERT INTO shops (name, location, phone, is_active) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['location'] ?? '',
        $data['phone'] ?? '',
        $data['is_active'] ?? true
    ]);
    
    json_response(true, 'تمت إضافة المحل بنجاح', ['id' => $conn->lastInsertId()]);
}

if ($action == 'update_shop') {
    check_admin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $conn->prepare("UPDATE shops SET name=?, location=?, phone=?, is_active=? WHERE id=?");
    $stmt->execute([
        $data['name'],
        $data['location'],
        $data['phone'],
        $data['is_active'],
        $data['id']
    ]);
    
    json_response(true, 'تم تحديث المحل بنجاح');
}

if ($action == 'delete_shop') {
    check_admin();
    
    $id = $_GET['id'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM shops WHERE id = ?");
    $stmt->execute([$id]);
    
    json_response(true, 'تم حذف المحل بنجاح');
}

// ========== إدارة المستخدمين (للمدير فقط) ==========
if ($action == 'get_users') {
    check_admin();
    
    $stmt = $conn->query("
        SELECT u.*, s.name as shop_name 
        FROM users u
        LEFT JOIN shops s ON u.shop_id = s.id
        ORDER BY u.role, u.full_name
    ");
    $users = $stmt->fetchAll();
    
    json_response(true, 'تم جلب المستخدمين', $users);
}

if ($action == 'add_user') {
    check_admin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // التحقق من عدم تكرار اسم المستخدم
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetch()) {
        json_response(false, 'اسم المستخدم موجود مسبقاً');
    }
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role, shop_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['username'],
        $hashedPassword,
        $data['full_name'],
        $data['role'],
        $data['shop_id'] ?? null,
        $data['is_active'] ?? true
    ]);
    
    json_response(true, 'تمت إضافة المستخدم بنجاح', ['id' => $conn->lastInsertId()]);
}

if ($action == 'update_user') {
    check_admin();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['password']) && !empty($data['password'])) {
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, full_name=?, role=?, shop_id=?, is_active=? WHERE id=?");
        $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['full_name'],
            $data['role'],
            $data['shop_id'] ?? null,
            $data['is_active'],
            $data['id']
        ]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, full_name=?, role=?, shop_id=?, is_active=? WHERE id=?");
        $stmt->execute([
            $data['username'],
            $data['full_name'],
            $data['role'],
            $data['shop_id'] ?? null,
            $data['is_active'],
            $data['id']
        ]);
    }
    
    json_response(true, 'تم تحديث المستخدم بنجاح');
}

if ($action == 'delete_user') {
    check_admin();
    
    $id = $_GET['id'] ?? 0;
    
    // منع حذف الحساب الحالي
    if ($id == $_SESSION['user_id']) {
        json_response(false, 'لا يمكنك حذف حسابك الخاص');
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    json_response(true, 'تم حذف المستخدم بنجاح');
}

json_response(false, 'عملية غير معروفة');
?>
