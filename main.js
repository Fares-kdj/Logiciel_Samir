// ========== المتغيرات العامة ==========
let allComponents = [];
let allShops = [];
let cart = [];
let packageComponents = [];

// ========== Helper Functions ==========
async function apiCall(action, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: { 'Content-Type': 'application/json' }
    };
    
    if (data && method === 'POST') {
        options.body = JSON.stringify(data);
    }
    
    const response = await fetch(`./api.php?action=${action}`, options);
    return await response.json();
}

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} active`;
    alertDiv.textContent = message;
    
    if (window.innerWidth <= 768) {
        alertDiv.style.cssText = 'position: fixed; top: 70px; left: 10px; right: 10px; z-index: 9999; width: auto; max-width: none;';
    } else {
        alertDiv.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; min-width: 300px; max-width: 500px;';
    }
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function logout() {
    if (confirm('هل تريد تسجيل الخروج؟')) {
        window.location.href = 'logout.php';
    }
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobile-overlay');
    
    sidebar.classList.toggle('mobile-active');
    overlay.classList.toggle('active');
}

// ========== Navigation ==========
document.addEventListener('DOMContentLoaded', function() {
    // Close mobile menu when clicking on menu item
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
            
            // Navigation
            document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            const section = this.dataset.section;
            document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
            document.getElementById(section).classList.add('active');
        });
    });
});

// ========== تحميل قائمة المحلات ==========
async function loadShopsForFilters() {
    const result = await apiCall('get_shops');
    if (result.success) {
        allShops = result.data;
        
        const filterSelects = [
            'dashboard-shop-filter',
            'filter-shop',
            'sale-shop-select',
            'filter-invoice-shop',
            'reports-shop-filter',
            'component-shop'
        ];
        
        filterSelects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const currentValue = select.value;
                const isRequired = select.hasAttribute('required');
                
                if (!isRequired) {
                    select.innerHTML = '<option value="">كل المحلات</option>';
                } else {
                    select.innerHTML = '<option value="">اختر المحل</option>';
                }
                
                result.data.forEach(shop => {
                    select.innerHTML += `<option value="${shop.id}" ${shop.id == currentValue ? 'selected' : ''}>${shop.name}</option>`;
                });
            }
        });
    }
}

// ========== Dashboard ==========
async function loadDashboard() {
    const shopFilter = document.getElementById('dashboard-shop-filter');
    const shopId = shopFilter ? shopFilter.value : '';
    
    const dailyReport = await apiCall(`get_daily_report${shopId ? '&shop_id=' + shopId : ''}`);
    if (dailyReport.success && dailyReport.data) {
        document.getElementById('daily-sales').textContent = parseFloat(dailyReport.data.total_sales || 0).toFixed(2) + ' دج';
        document.getElementById('daily-profit').textContent = parseFloat(dailyReport.data.total_profit || 0).toFixed(2) + ' دج';
        document.getElementById('daily-cost').textContent = parseFloat(dailyReport.data.total_cost || 0).toFixed(2) + ' دج';
    }

    const components = await apiCall(`get_components${shopId ? '&shop_id=' + shopId : ''}`);
    if (components.success) {
        document.getElementById('total-components').textContent = components.data.length;
    }

    const lowStock = await apiCall(`get_low_stock${shopId ? '&shop_id=' + shopId : ''}`);
    if (lowStock.success) {
        document.getElementById('low-stock').textContent = lowStock.data.length;
        
        const tbody = document.querySelector('#low-stock-table tbody');
        tbody.innerHTML = '';
        
        if (lowStock.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #999;">جميع المنتجات متوفرة ✅</td></tr>';
        } else {
            lowStock.data.forEach(item => {
                const unitText = item.unit_type === 'meter' ? 'م' : 'قطعة';
                const currentQty = parseFloat(item.quantity) || 0;
                
                let statusClass, statusText;
                if (currentQty === 0) {
                    statusClass = 'danger';
                    statusText = 'نفدت الكمية';
                } else {
                    statusClass = 'warning';
                    statusText = 'منخفض';
                }
                
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${item.name}</strong></td>
                        <td>${item.shop_name || ''}</td>
                        <td>${item.category}</td>
                        <td>${currentQty} ${unitText}</td>
                        <td>${item.min_quantity} ${unitText}</td>
                        <td><span class="badge badge-${statusClass}">${statusText}</span></td>
                    </tr>
                `;
            });
        }
    }
}

// ========== Components ==========
async function loadComponents(search = '', category = '') {
    const shopFilter = document.getElementById('filter-shop');
    const shopId = shopFilter ? shopFilter.value : '';
    
    const result = await apiCall(`get_components${search ? '&search=' + encodeURIComponent(search) : ''}${category ? '&category=' + encodeURIComponent(category) : ''}${shopId ? '&shop_id=' + shopId : ''}`);
    
    if (result.success) {
        allComponents = result.data;
        
        const categories = [...new Set(result.data.map(c => c.category))];
        
        const filterCategory = document.getElementById('filter-components-category');
        if (filterCategory) {
            const currentValue = filterCategory.value;
            filterCategory.innerHTML = '<option value="">كل الفئات</option>';
            categories.forEach(cat => {
                filterCategory.innerHTML += `<option value="${cat}" ${cat === currentValue ? 'selected' : ''}>${cat}</option>`;
            });
        }
        
        const filterSaleCategory = document.getElementById('filter-sale-category');
        if (filterSaleCategory) {
            const currentValue = filterSaleCategory.value;
            filterSaleCategory.innerHTML = '<option value="">كل الفئات</option>';
            categories.forEach(cat => {
                filterSaleCategory.innerHTML += `<option value="${cat}" ${cat === currentValue ? 'selected' : ''}>${cat}</option>`;
            });
        }
        
        // ملء فلتر الفئات في نافذة التجميعة
        const packageCategoryFilter = document.getElementById('package-category-filter');
        if (packageCategoryFilter) {
            const currentValue = packageCategoryFilter.value;
            packageCategoryFilter.innerHTML = '<option value="">كل الفئات</option>';
            categories.forEach(cat => {
                packageCategoryFilter.innerHTML += `<option value="${cat}" ${cat === currentValue ? 'selected' : ''}>${cat}</option>`;
            });
        }
        
        let componentsToDisplay = result.data;
        if (category) {
            componentsToDisplay = result.data.filter(c => c.category === category);
        }
        
        const tbody = document.querySelector('#components-table tbody');
        tbody.innerHTML = '';
        
        const isAdmin = document.getElementById('dashboard') !== null;
        
        componentsToDisplay.forEach(comp => {
            const currentQty = parseFloat(comp.quantity) || 0;
            const minQty = parseFloat(comp.min_quantity) || 0;
            const purchasePrice = parseFloat(comp.purchase_price) || 0;
            const sellingPrice = parseFloat(comp.selling_price) || 0;
            const profit = sellingPrice - purchasePrice;
            
            let statusBadge;
            if (currentQty === 0) {
                statusBadge = '<span class="badge badge-danger">نفدت</span>';
            } else if (currentQty <= minQty) {
                statusBadge = '<span class="badge badge-warning">منخفض</span>';
            } else {
                statusBadge = '<span class="badge badge-success">متوفر</span>';
            }
            
            const unitText = comp.unit_type === 'meter' ? 'متر' : 'قطعة';
            const shopColumn = isAdmin ? `<td>${comp.shop_name || ''}</td>` : '';
            
            tbody.innerHTML += `
                <tr>
                    <td>${comp.id}</td>
                    ${shopColumn}
                    <td><strong>${comp.name}</strong></td>
                    <td>${comp.category}</td>
                    <td>${purchasePrice.toFixed(2)} دج</td>
                    <td>${sellingPrice.toFixed(2)} دج</td>
                    <td><span class="badge badge-profit">${profit.toFixed(2)} دج</span></td>
                    <td>${currentQty}</td>
                    <td>${unitText}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-warning" onclick="editComponent(${comp.id})" style="padding: 8px 15px; font-size: 13px;">✏️</button>
                        <button class="btn btn-danger" onclick="deleteComponent(${comp.id})" style="padding: 8px 15px; font-size: 13px;">🗑️</button>
                    </td>
                </tr>
            `;
        });

        displaySaleComponents(componentsToDisplay);
    }
}

function displaySaleComponents(components) {
    const saleList = document.getElementById('components-list');
    if (!saleList) return;
    
    saleList.innerHTML = '';
    
    const saleShopSelect = document.getElementById('sale-shop-select');
    let filteredComponents = components;
    
    const isAdmin = document.getElementById('dashboard') !== null;
    if (isAdmin && saleShopSelect) {
        const selectedShopId = saleShopSelect.value;
        if (selectedShopId) {
            filteredComponents = components.filter(c => c.shop_id == selectedShopId);
        }
    }
    
    filteredComponents.forEach(comp => {
        const unitText = comp.unit_type === 'meter' ? 'م' : 'قطعة';
        
        saleList.innerHTML += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #e9ecef;">
                <div style="flex: 1;">
                    <strong>${comp.name}</strong>
                    <small style="display: block; color: #666;">${comp.category} - ${comp.selling_price} دج/${unitText}</small>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="number" min="0.01" max="${comp.quantity}" value="1" step="${comp.unit_type === 'meter' ? '0.01' : '1'}" 
                           id="comp-qty-${comp.id}" style="width: 70px; padding: 8px; border: 2px solid #e9ecef; border-radius: 8px;">
                    <button class="btn btn-primary" onclick="addToCart('component', ${comp.id})" style="padding: 8px 15px;">➕</button>
                </div>
            </div>
        `;
    });
}

function openComponentModal(id = null) {
    document.getElementById('component-modal').classList.add('active');
    
    if (id) {
        const comp = allComponents.find(c => c.id == id);
        if (comp) {
            document.getElementById('component-id').value = comp.id;
            document.getElementById('component-name').value = comp.name;
            document.getElementById('component-category').value = comp.category;
            document.getElementById('component-unit-type').value = comp.unit_type || 'piece';
            document.getElementById('component-purchase-price').value = comp.purchase_price;
            document.getElementById('component-selling-price').value = comp.selling_price;
            document.getElementById('component-quantity').value = comp.quantity;
            document.getElementById('component-min').value = comp.min_quantity;
            document.getElementById('component-description').value = comp.description || '';
            
            const shopSelect = document.getElementById('component-shop');
            if (shopSelect) {
                shopSelect.value = comp.shop_id;
            }
        }
    } else {
        document.getElementById('component-form').reset();
        document.getElementById('component-id').value = '';
        document.getElementById('component-unit-type').value = 'piece';
    }
}

async function saveComponent(e) {
    e.preventDefault();
    
    const id = document.getElementById('component-id').value;
    const data = {
        id: id || undefined,
        name: document.getElementById('component-name').value,
        category: document.getElementById('component-category').value,
        unit_type: document.getElementById('component-unit-type').value,
        purchase_price: document.getElementById('component-purchase-price').value,
        selling_price: document.getElementById('component-selling-price').value,
        quantity: document.getElementById('component-quantity').value,
        min_quantity: document.getElementById('component-min').value,
        description: document.getElementById('component-description').value
    };
    
    const shopSelect = document.getElementById('component-shop');
    if (shopSelect) {
        const shopId = shopSelect.value;
        if (!shopId) {
            showAlert('الرجاء تحديد المحل', 'warning');
            return;
        }
        data.shop_id = shopId;
    }
    
    const action = id ? 'update_component' : 'add_component';
    const result = await apiCall(action, 'POST', data);
    
    if (result.success) {
        showAlert(result.message, 'success');
        closeModal('component-modal');
        
        // تحديث قائمة المكونات فوراً
        await loadComponents();
        
        // تحديث Dashboard إذا كان موجوداً
        if (document.getElementById('dashboard')) {
            await loadDashboard();
        }
    } else {
        showAlert(result.message, 'danger');
    }
}

function editComponent(id) {
    openComponentModal(id);
}

async function deleteComponent(id) {
    if (!confirm('هل أنت متأكد من حذف هذا المكون؟')) return;
    
    const result = await apiCall(`delete_component&id=${id}`, 'POST');
    
    if (result.success) {
        showAlert(result.message, 'success');
        
        // تحديث قائمة المكونات فوراً
        await loadComponents();
        
        // تحديث Dashboard إذا كان موجوداً
        if (document.getElementById('dashboard')) {
            await loadDashboard();
        }
    } else {
        showAlert(result.message, 'danger');
    }
}

// ========== Cart & Sale ==========
function addToCart(type, id) {
    const comp = allComponents.find(c => c.id == id);
    if (!comp) {
        showAlert('المنتج غير موجود', 'danger');
        return;
    }

    const qtyInput = document.getElementById(`comp-qty-${id}`);
    const quantity = parseFloat(qtyInput.value) || 1;
    
    if (quantity > comp.quantity) {
        showAlert(`الكمية المتاحة فقط ${comp.quantity}`, 'warning');
        return;
    }
    
    const existing = cart.find(c => c.type === 'component' && c.id == id);
    
    if (existing) {
        existing.quantity += quantity;
    } else {
        cart.push({
            type: 'component',
            id: id,
            name: comp.name,
            quantity: quantity,
            unit_type: comp.unit_type,
            purchase_price: parseFloat(comp.purchase_price),
            selling_price: parseFloat(comp.selling_price),
            shop_id: comp.shop_id
        });
    }
    
    updateCartDisplay();
    showAlert('تمت الإضافة إلى السلة', 'success');
}

function updateCartDisplay() {
    const container = document.getElementById('cart-items');
    if (!container) return;
    
    container.innerHTML = '';
    
    let totalRevenue = 0;
    
    cart.forEach((item, index) => {
        const itemRevenue = item.selling_price * item.quantity;
        totalRevenue += itemRevenue;
        
        const unitText = item.unit_type === 'meter' ? 'م' : 'قطعة';
        
        container.innerHTML += `
            <div class="cart-item">
                <div style="flex: 1;">
                    <strong>${item.name}</strong>
                    <small style="display: block;">${item.quantity} ${unitText} × ${item.selling_price.toFixed(2)} دج</small>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <strong style="color: var(--accent);">${itemRevenue.toFixed(2)} دج</strong>
                    <button class="btn btn-warning" onclick="editPrice(${index})" style="padding: 6px 10px; font-size: 12px;">💵</button>
                    <button class="btn btn-danger" onclick="removeFromCart(${index})" style="padding: 6px 10px; font-size: 12px;">🗑️</button>
                </div>
            </div>
        `;
    });
    
    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = totalRevenue.toFixed(2) + ' دج';
    }
}

function editPrice(index) {
    const item = cart[index];
    
    document.getElementById('edit-price-index').value = index;
    document.getElementById('edit-price-item-name').textContent = item.name;
    document.getElementById('edit-price-current').textContent = item.selling_price.toFixed(2) + ' دج';
    document.getElementById('edit-price-new').value = item.selling_price;
    
    document.getElementById('price-edit-modal').classList.add('active');
}

function saveNewPrice() {
    const index = document.getElementById('edit-price-index').value;
    const newPrice = parseFloat(document.getElementById('edit-price-new').value);
    
    if (newPrice <= 0) {
        showAlert('السعر يجب أن يكون أكبر من صفر', 'warning');
        return;
    }
    
    cart[index].selling_price = newPrice;
    updateCartDisplay();
    closeModal('price-edit-modal');
    showAlert('تم تحديث السعر', 'success');
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}

async function completeSale() {
    if (cart.length === 0) {
        showAlert('السلة فارغة', 'warning');
        return;
    }
    
    const shops = [...new Set(cart.map(item => item.shop_id))];
    if (shops.length > 1) {
        showAlert('لا يمكن إتمام البيع من عدة محلات في نفس الوقت', 'warning');
        return;
    }
    
    const items = cart.map(item => ({
        type: item.type,
        id: item.id,
        name: item.name,
        quantity: item.quantity,
        unit_type: item.unit_type,
        selling_price: item.selling_price,
        components: item.components || []
    }));
    
    const data = { items };
    
    if (document.getElementById('dashboard')) {
        data.shop_id = shops[0];
    }
    
    const result = await apiCall('create_sale', 'POST', data);
    
    if (result.success) {
        // مسح السلة
        cart = [];
        updateCartDisplay();
        
        // تحديث المكونات
        await loadComponents();
        
        // تحديث Dashboard إذا كان موجوداً
        if (document.getElementById('dashboard')) {
            await loadDashboard();
        }
        
        // تحديث التقارير إذا كانت موجودة
        if (document.getElementById('reports')) {
            await loadReports();
        }
        
        // تحديث قائمة الوصولات
        await loadInvoices();
        
        // عرض رسالة النجاح مع خيار عرض الوصل
        const confirmView = confirm(`تم إتمام البيع بنجاح! ✅\n\nرقم الوصل: ${result.data.invoice_number}\nالمبلغ الكلي: ${result.data.total_amount} دج\nالربح: ${result.data.total_profit} دج\n\nهل تريد عرض تفاصيل الوصل؟`);
        
        if (confirmView) {
            // الانتقال إلى صفحة الوصولات
            const invoicesSection = document.getElementById('invoices');
            if (invoicesSection) {
                document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
                document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
                
                invoicesSection.classList.add('active');
                const invoicesMenuItem = document.querySelector('.menu-item[data-section="invoices"]');
                if (invoicesMenuItem) {
                    invoicesMenuItem.classList.add('active');
                }
                
                // عرض تفاصيل الوصل
                setTimeout(() => {
                    viewInvoice(result.data.invoice_id);
                }, 300);
            }
        } else {
            showAlert(`تم إتمام البيع! رقم الوصل: ${result.data.invoice_number} | الربح: ${result.data.total_profit} دج`, 'success');
        }
    } else {
        showAlert(result.message, 'danger');
    }
}

// ========== Package ==========
function openPackageModal() {
    document.getElementById('package-modal').classList.add('active');
    document.getElementById('package-name').value = '';
    document.getElementById('package-selling-price').value = '';
    packageComponents = [];
    packageSelections = {}; // إعادة تعيين الاختيارات
    
    // إعادة تعيين الفلاتر
    const pkgSearchInput = document.getElementById('package-search');
    const pkgCategoryFilter = document.getElementById('package-category-filter');
    if (pkgSearchInput) pkgSearchInput.value = '';
    if (pkgCategoryFilter) pkgCategoryFilter.value = '';
    
    displayPackageComponents();
    updatePackageCalculation();
}

function displayPackageComponents(searchTerm = '', categoryFilter = '') {
    const list = document.getElementById('package-components-list');
    if (!list) return;
    
    list.innerHTML = '';
    
    let availableComponents = allComponents;
    
    // فلترة حسب المحل المختار
    if (document.getElementById('dashboard')) {
        const saleShopSelect = document.getElementById('sale-shop-select');
        if (saleShopSelect && saleShopSelect.value) {
            availableComponents = allComponents.filter(c => c.shop_id == saleShopSelect.value);
        }
    }
    
    // فلترة حسب البحث
    if (searchTerm) {
        availableComponents = availableComponents.filter(c => 
            c.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            c.category.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }
    
    // فلترة حسب الفئة
    if (categoryFilter) {
        availableComponents = availableComponents.filter(c => c.category === categoryFilter);
    }
    
    if (availableComponents.length === 0) {
        list.innerHTML = '<div style="text-align: center; padding: 20px; color: #999;">لا توجد مكونات متاحة</div>';
        return;
    }

        // عرض المكونات مع الاختيارات المحفوظة
    availableComponents.forEach(comp => {
        const unitText = comp.unit_type === 'meter' ? 'م' : 'قطعة';
        const saved = packageSelections[comp.id];
        const isChecked = saved && saved.checked ? 'checked' : '';
        const savedQty = saved && saved.quantity ? saved.quantity : 1;
        
        list.innerHTML += `
            <div class="package-comp-item" data-comp-id="${comp.id}" style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #e9ecef;">
                <label style="flex: 1; display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="pkg-comp-${comp.id}" onchange="updatePackageCalculation()" style="margin-left: 10px;" ${isChecked}>
                    <div>
                        <strong>${comp.name}</strong>
                        <small style="display: block; color: #666;">متاح: ${comp.quantity} ${unitText} - ${comp.category}</small>
                    </div>
                </label>
                <input type="number" min="0.01" value="${savedQty}" step="${comp.unit_type === 'meter' ? '0.01' : '1'}" 
                       id="pkg-qty-${comp.id}" onchange="updatePackageCalculation()" 
                       style="width: 70px; padding: 8px; border: 2px solid #e9ecef; border-radius: 8px;">
            </div>
        `;
    });
}

function filterPackageComponents() {
        // حفظ الاختيارات الحالية أولاً قبل أي شيء
    saveCurrentSelections();
    
    const searchTerm = document.getElementById('package-search').value;
    const category = document.getElementById('package-category-filter').value;
    displayPackageComponents(searchTerm, category);
}

function saveCurrentSelections() {
    // حفظ جميع الاختيارات والكميات الحالية
    allComponents.forEach(comp => {
        const checkbox = document.getElementById(`pkg-comp-${comp.id}`);
        const qtyInput = document.getElementById(`pkg-qty-${comp.id}`);
        
        if (checkbox) {
            if (checkbox.checked) {
                packageSelections[comp.id] = {
                    checked: true,
                    quantity: qtyInput ? qtyInput.value : 1
                };
            } else if (!checkbox.checked && packageSelections[comp.id]) {
                // إذا كان غير محدد والمتغير يحتوي عليه، نحذفه
                delete packageSelections[comp.id];
            }
        }
    });
}

function updatePackageCalculation() {
    // أولاً: حفظ أي تغييرات من DOM إلى packageSelections
    allComponents.forEach(comp => {
        const checkbox = document.getElementById(`pkg-comp-${comp.id}`);
        const qtyInput = document.getElementById(`pkg-qty-${comp.id}`);
        
        if (checkbox) {
            if (checkbox.checked) {
                const qty = parseFloat(qtyInput.value) || 1;
                packageSelections[comp.id] = {
                    checked: true,
                    quantity: qty
                };
            } else if (!checkbox.checked && packageSelections[comp.id]) {
                delete packageSelections[comp.id];
            }
        }
    });
    
    // ثانياً: حساب التكلفة وبناء packageComponents من packageSelections
    let totalCost = 0;
    packageComponents = [];
    
    // استخدام packageSelections بدلاً من DOM
    Object.keys(packageSelections).forEach(compId => {
        const selection = packageSelections[compId];
        if (selection && selection.checked) {
            // البحث عن المكون في allComponents
            const comp = allComponents.find(c => c.id == compId);
            if (comp) {
                const qty = parseFloat(selection.quantity) || 1;
                const cost = parseFloat(comp.purchase_price) * qty;
                totalCost += cost;
                
                packageComponents.push({
                    id: comp.id,
                    name: comp.name,
                    quantity: qty,
                    purchase_price: parseFloat(comp.purchase_price),
                    shop_id: comp.shop_id,
                    unit_type: comp.unit_type,
                    category: comp.category
                });
            }
        }
    });
    
    // تحديث عرض المكونات المختارة
    updateSelectedComponentsDisplay();
    
    const totalCostElement = document.getElementById('package-total-cost');
    if (totalCostElement) {
        totalCostElement.textContent = totalCost.toFixed(2) + ' دج';
    }
    
    const sellingPriceInput = document.getElementById('package-selling-price');
    const errorMsg = document.getElementById('package-price-error');
    
    if (sellingPriceInput && errorMsg) {
        const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
        
        if (sellingPrice > 0 && sellingPrice < totalCost) {
            errorMsg.style.display = 'block';
            sellingPriceInput.style.borderColor = 'var(--danger)';
        } else {
            errorMsg.style.display = 'none';
            sellingPriceInput.style.borderColor = 'var(--border)';
        }
    }
}

function updateSelectedComponentsDisplay() {
    const displayDiv = document.getElementById('selected-components-display');
    const listDiv = document.getElementById('selected-components-list');
    const countSpan = document.getElementById('selected-count');
    
    if (!displayDiv || !listDiv || !countSpan) return;
    
    if (packageComponents.length === 0) {
        displayDiv.style.display = 'none';
        return;
    }
    
    displayDiv.style.display = 'block';
    countSpan.textContent = packageComponents.length;
    
    listDiv.innerHTML = '';
    packageComponents.forEach(comp => {
        const unitText = comp.unit_type === 'meter' ? 'م' : 'قطعة';
        const badge = document.createElement('div');
        badge.style.cssText = `
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: white;
            padding: 8px 12px;
            border-radius: 8px;
            border: 2px solid var(--accent);
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        `;
        badge.innerHTML = `
            <span style="color: var(--accent);">✓</span>
            <span>${comp.name}</span>
            <span style="background: var(--accent); color: white; padding: 2px 8px; border-radius: 5px; font-size: 12px;">
                ${comp.quantity} ${unitText}
            </span>
            <span style="color: #999; font-size: 11px;">${comp.category}</span>
        `;
        listDiv.appendChild(badge);
    });
}

function addPackageToCart() {
    const packageName = document.getElementById('package-name').value.trim();
    
    if (!packageName) {
        showAlert('الرجاء إدخال اسم التجميعة', 'warning');
        return;
    }
    
    if (packageComponents.length === 0) {
        showAlert('الرجاء اختيار مكون واحد على الأقل', 'warning');
        return;
    }
    
    const sellingPrice = parseFloat(document.getElementById('package-selling-price').value);
    if (!sellingPrice || sellingPrice <= 0) {
        showAlert('الرجاء إدخال سعر البيع', 'warning');
        return;
    }
    
    let totalCost = 0;
    const shops = [...new Set(packageComponents.map(c => c.shop_id))];
    
    if (shops.length > 1) {
        showAlert('لا يمكن إنشاء تجميعة من مكونات من محلات مختلفة', 'warning');
        return;
    }
    
    packageComponents.forEach(comp => {
        totalCost += comp.purchase_price * comp.quantity;
    });
    
    if (sellingPrice < totalCost) {
        showAlert('⚠️ سعر البيع لا يمكن أن يقل عن التكلفة (' + totalCost.toFixed(2) + ' دج)', 'danger');
        return;
    }
    
    cart.push({
        type: 'package',
        id: Date.now(),
        name: packageName,
        quantity: 1,
        unit_type: 'piece',
        purchase_price: totalCost,
        selling_price: sellingPrice,
        components: packageComponents,
        shop_id: shops[0]
    });
    
    updateCartDisplay();
    closeModal('package-modal');
    showAlert('تمت إضافة التجميعة إلى السلة', 'success');
}

// ========== Invoices ==========
async function loadInvoices(queryString = '') {
    const result = await apiCall('get_invoices' + queryString);
    
    if (result.success) {
        const tbody = document.querySelector('#invoices-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        const isAdmin = document.getElementById('dashboard') !== null;
        
        if (result.data.length === 0) {
            const colspan = isAdmin ? '7' : '6';
            tbody.innerHTML = `<tr><td colspan="${colspan}" style="text-align: center; padding: 30px;">لا توجد وصولات</td></tr>`;
            return;
        }
        
        result.data.forEach(inv => {
            const date = new Date(inv.created_at);
            const shopColumn = isAdmin ? `<td>${inv.shop_name || ''}</td>` : '';
            
            tbody.innerHTML += `
                <tr>
                    <td><strong>${inv.invoice_number}</strong></td>
                    ${shopColumn}
                    <td>${parseFloat(inv.total_amount).toFixed(2)} دج</td>
                    <td style="color: var(--cost-color);">${parseFloat(inv.total_cost).toFixed(2)} دج</td>
                    <td><span class="badge badge-profit">${parseFloat(inv.total_profit).toFixed(2)} دج</span></td>
                    <td>${date.toLocaleString('ar-DZ')}</td>
                    <td>
                        <button class="btn btn-primary" onclick="viewInvoice(${inv.id})" style="padding: 8px 15px; font-size: 13px;">👁️</button>
                        <button class="btn btn-danger" onclick="deleteInvoice(${inv.id})" style="padding: 8px 15px; font-size: 13px;">🗑️</button>
                    </td>
                </tr>
            `;
        });
    }
}

async function filterInvoices() {
    const search = document.getElementById('filter-invoice-number').value;
    const date = document.getElementById('filter-date').value;
    const shopFilter = document.getElementById('filter-invoice-shop');
    const shopId = shopFilter ? shopFilter.value : '';
    
    let params = [];
    if (search) params.push(`search=${encodeURIComponent(search)}`);
    if (date) params.push(`date=${date}`);
    if (shopId) params.push(`shop_id=${shopId}`);
    
    const queryString = params.length ? '&' + params.join('&') : '';
    await loadInvoices(queryString);
}

function resetInvoiceFilters() {
    document.getElementById('filter-invoice-number').value = '';
    document.getElementById('filter-date').value = '';
    const shopFilter = document.getElementById('filter-invoice-shop');
    if (shopFilter) shopFilter.value = '';
    loadInvoices();
}

async function viewInvoice(id) {
    const result = await apiCall(`get_invoice_details&id=${id}`);
    
    if (result.success) {
        const inv = result.data;
        const date = new Date(inv.created_at);
        
        let itemsHtml = '';
        inv.items.forEach(item => {
            const unitText = item.unit_type === 'meter' ? 'م' : 'قطعة';
            const itemProfit = parseFloat(item.profit) || 0;
            
            itemsHtml += `
                <tr>
                    <td><strong>${item.item_name}</strong></td>
                    <td>${item.item_type === 'component' ? 'مكون' : 'تجميعة'}</td>
                    <td>${item.quantity} ${unitText}</td>
                    <td>${parseFloat(item.purchase_price).toFixed(2)} دج</td>
                    <td>${parseFloat(item.selling_price).toFixed(2)} دج</td>
                    <td><span class="badge badge-profit">${itemProfit.toFixed(2)} دج</span></td>
                    <td><strong>${parseFloat(item.total_price).toFixed(2)} دج</strong></td>
                </tr>
            `;
        });
        
        const html = `
            <div style="margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 12px;">
                <h3 style="margin-bottom: 10px; color: var(--primary);">رقم الوصل: ${inv.invoice_number}</h3>
                <p style="color: #666;">التاريخ: ${date.toLocaleString('ar-DZ')}</p>
                ${inv.shop_name ? `<p style="color: #666;">المحل: ${inv.shop_name}</p>` : ''}
            </div>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #1a5490; color: white;">
                        <th style="padding: 12px; text-align: right;">المنتج</th>
                        <th style="padding: 12px; text-align: right;">النوع</th>
                        <th style="padding: 12px; text-align: right;">الكمية</th>
                        <th style="padding: 12px; text-align: right;">سعر الشراء</th>
                        <th style="padding: 12px; text-align: right;">سعر البيع</th>
                        <th style="padding: 12px; text-align: right;">الربح</th>
                        <th style="padding: 12px; text-align: right;">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 20px; background: linear-gradient(135deg, #1a5490 0%, #2d6ca8 100%); color: white; border-radius: 12px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>التكلفة الإجمالية:</span>
                    <strong>${parseFloat(inv.total_cost).toFixed(2)} دج</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>المبلغ المدفوع:</span>
                    <strong>${parseFloat(inv.total_amount).toFixed(2)} دج</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px solid rgba(255,255,255,0.3);">
                    <span style="font-size: 20px; font-weight: bold;">صافي الربح:</span>
                    <span style="font-size: 24px; font-weight: 900;">💚 ${parseFloat(inv.total_profit).toFixed(2)} دج</span>
                </div>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <button class="btn btn-success" onclick="printInvoice(${id})" style="padding: 12px 30px; font-size: 16px;">🖨️ طباعة الوصل</button>
            </div>
        `;
        
        document.getElementById('invoice-details').innerHTML = html;
        document.getElementById('invoice-modal').classList.add('active');
    }
}

async function deleteInvoice(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الوصل؟ لا يمكن التراجع!')) return;
    
    const result = await apiCall(`delete_invoice&id=${id}`, 'POST');
    
    if (result.success) {
        showAlert(result.message, 'success');
        
        // إغلاق نافذة التفاصيل إذا كانت مفتوحة
        closeModal('invoice-modal');
        
        // تحديث قائمة الوصولات فوراً
        await loadInvoices();
        
        // تحديث Dashboard إذا كان موجوداً
        if (document.getElementById('dashboard')) {
            await loadDashboard();
        }
        
        // تحديث التقارير إذا كانت موجودة
        if (document.getElementById('reports')) {
            await loadReports();
        }
        
        // تحديث المكونات (لأن الكميات قد تكون تغيرت)
        await loadComponents();
    } else {
        showAlert(result.message, 'danger');
    }
}

function printInvoice(invoiceId) {
    const printWindow = window.open('', '_blank', 'width=300,height=600');
    const invoiceDetails = document.getElementById('invoice-details');
    
    const invoiceNumber = invoiceDetails.querySelector('h3').textContent.split(': ')[1];
    const invoiceDate = invoiceDetails.querySelectorAll('p')[0].textContent.split(': ')[1];
    
    const items = [];
    const rows = invoiceDetails.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cols = row.querySelectorAll('td');
        if (cols.length > 0) {
            items.push({
                name: cols[0].textContent,
                quantity: cols[2].textContent,
                price: cols[4].textContent,
                total: cols[6].textContent
            });
        }
    });
    
    const totalDiv = invoiceDetails.querySelector('div[style*="linear-gradient"]');
    const totalAmount = totalDiv ? totalDiv.querySelectorAll('strong')[1].textContent : '0 دج';
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=80mm">
            <title>وصل شراء</title>
            <style>
                @page { size: 80mm auto; margin: 0; }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: 'Courier New', monospace; width: 80mm; padding: 5mm; font-size: 11pt; line-height: 1.4; }
                .header { text-align: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px dashed #000; }
                .shop-name { font-size: 16pt; font-weight: bold; margin-bottom: 3px; }
                .receipt-title { font-size: 13pt; font-weight: bold; margin: 8px 0; text-align: center; }
                .info-row { display: flex; justify-content: space-between; margin: 3px 0; font-size: 9pt; }
                .item { margin: 8px 0; padding: 5px 0; border-bottom: 1px dotted #999; }
                .item-name { font-weight: bold; margin-bottom: 3px; }
                .item-details { display: flex; justify-content: space-between; font-size: 10pt; margin: 2px 0; }
                .total-section { margin-top: 10px; padding: 8px 0; border-top: 2px solid #000; border-bottom: 2px solid #000; }
                .total-row { display: flex; justify-content: space-between; font-size: 14pt; font-weight: bold; padding: 5px 0; }
                .footer { text-align: center; margin-top: 10px; padding-top: 8px; border-top: 2px dashed #000; font-size: 10pt; font-weight: bold; }
                @media print { .no-print { display: none !important; } }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="shop-name">محل سمير ترانقل</div>
                <div>قضبان الستائر وملحقاتها</div>
            </div>
            
            <div class="receipt-title">وصل شراء</div>
            
            <div style="margin: 8px 0; padding-bottom: 8px; border-bottom: 1px dashed #000;">
                <div class="info-row"><span>رقم الوصل:</span><strong>${invoiceNumber}</strong></div>
                <div class="info-row"><span>التاريخ:</span><span>${invoiceDate}</span></div>
            </div>
            
            <div>
                ${items.map(item => `
                    <div class="item">
                        <div class="item-name">${item.name}</div>
                        <div class="item-details"><span>${item.quantity}</span><span>${item.price}</span></div>
                        <div class="item-details"><span>المجموع:</span><strong>${item.total}</strong></div>
                    </div>
                `).join('')}
            </div>
            
            <div class="total-section">
                <div class="total-row"><span>المجموع الكلي:</span><span>${totalAmount}</span></div>
            </div>
            
            <div class="footer">
                محل سمير ترانقل<br>يشكركم على وفائكم 🌟
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 15px;">
                <button onclick="window.print()" style="padding: 8px 15px; margin: 0 5px;">🖨️ طباعة</button>
                <button onclick="window.close()" style="padding: 8px 15px; margin: 0 5px;">✖ إغلاق</button>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    setTimeout(() => printWindow.focus(), 250);
}

// ========== Reports ==========
async function loadReports() {
    const shopFilter = document.getElementById('reports-shop-filter');
    const shopId = shopFilter ? shopFilter.value : '';
    
    const daily = await apiCall(`get_daily_report${shopId ? '&shop_id=' + shopId : ''}`);
    if (daily.success && daily.data) {
        const reportDailySales = document.getElementById('report-daily-sales');
        const reportDailyProfit = document.getElementById('report-daily-profit');
        if (reportDailySales) reportDailySales.textContent = parseFloat(daily.data.total_sales || 0).toFixed(2) + ' دج';
        if (reportDailyProfit) reportDailyProfit.textContent = parseFloat(daily.data.total_profit || 0).toFixed(2) + ' دج';
    }

    const monthly = await apiCall(`get_monthly_report${shopId ? '&shop_id=' + shopId : ''}`);
    if (monthly.success && monthly.data) {
        const reportMonthlySales = document.getElementById('report-monthly-sales');
        const reportMonthlyProfit = document.getElementById('report-monthly-profit');
        if (reportMonthlySales) reportMonthlySales.textContent = parseFloat(monthly.data.total_sales || 0).toFixed(2) + ' دج';
        if (reportMonthlyProfit) reportMonthlyProfit.textContent = parseFloat(monthly.data.total_profit || 0).toFixed(2) + ' دج';
    }

    const topSelling = await apiCall(`get_top_selling${shopId ? '&shop_id=' + shopId : ''}`);
    if (topSelling.success) {
        const tbody = document.querySelector('#top-selling-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (topSelling.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px;">لا توجد مبيعات بعد</td></tr>';
        } else {
            topSelling.data.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td><strong>${item.item_name}</strong></td>
                        <td>${item.item_type === 'component' ? 'مكون' : 'تجميعة'}</td>
                        <td>${parseFloat(item.total_sold).toFixed(2)}</td>
                        <td>${parseFloat(item.total_revenue).toFixed(2)} دج</td>
                        <td><span class="badge badge-profit">${parseFloat(item.total_profit).toFixed(2)} دج</span></td>
                    </tr>
                `;
            });
        }
    }
}

// ========== Search & Filters ==========
let searchTimeout;
document.addEventListener('DOMContentLoaded', function() {
    const searchComponents = document.getElementById('search-components');
    if (searchComponents) {
        searchComponents.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const category = document.getElementById('filter-components-category').value;
                loadComponents(this.value, category);
            }, 500);
        });
    }

    const filterCategory = document.getElementById('filter-components-category');
    if (filterCategory) {
        filterCategory.addEventListener('change', function() {
            const search = document.getElementById('search-components').value;
            loadComponents(search, this.value);
        });
    }

    const filterSaleCategory = document.getElementById('filter-sale-category');
    if (filterSaleCategory) {
        filterSaleCategory.addEventListener('change', function() {
            const category = this.value;
            const filtered = category ? allComponents.filter(c => c.category === category) : allComponents;
            displaySaleComponents(filtered);
        });
    }

    const searchSaleComponents = document.getElementById('search-sale-components');
    if (searchSaleComponents) {
        searchSaleComponents.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            const category = document.getElementById('filter-sale-category').value;
            
            let filtered = allComponents.filter(c => 
                c.name.toLowerCase().includes(search) || 
                c.category.toLowerCase().includes(search)
            );
            
            if (category) {
                filtered = filtered.filter(c => c.category === category);
            }
            
            displaySaleComponents(filtered);
        });
    }
    
    const saleShopSelect = document.getElementById('sale-shop-select');
    if (saleShopSelect) {
        saleShopSelect.addEventListener('change', function() {
            displaySaleComponents(allComponents);
            if (cart.length > 0) {
                if (confirm('تغيير المحل سيؤدي إلى مسح السلة. هل تريد المتابعة؟')) {
                    cart = [];
                    updateCartDisplay();
                } else {
                    this.value = cart[0].shop_id;
                }
            }
        });
    }
    
    const pkgPriceInput = document.getElementById('package-selling-price');
    if (pkgPriceInput) {
        pkgPriceInput.addEventListener('input', updatePackageCalculation);
    }
});

// ========== Initialize ==========
window.addEventListener('load', async function() {
        // تحديد الصفحة الافتراضية
    const defaultSection = userRole === 'admin' ? 'dashboard' : 'sale';
    
    // إخفاء جميع الأقسام
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    
    // إظهار الصفحة الافتراضية
    const defaultSectionElement = document.getElementById(defaultSection);
    if (defaultSectionElement) {
        defaultSectionElement.classList.add('active');
    }
    
    // تفعيل العنصر المناسب في القائمة
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === defaultSection) {
            item.classList.add('active');
        }
    });
    
    // تحميل البيانات
    if (document.getElementById('dashboard')) {
        await loadShopsForFilters();
        loadDashboard();
        loadReports();
    }
    
    loadComponents();
    loadInvoices();
});