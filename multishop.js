// ========== دوال إدارة المحلات ==========

async function loadShops() {
    const result = await apiCall('get_shops');
    
    if (result.success) {
        const tbody = document.querySelector('#shops-table tbody');
        tbody.innerHTML = '';
        
        result.data.forEach(shop => {
            const statusBadge = shop.is_active ? 
                '<span class="badge badge-success">نشط</span>' : 
                '<span class="badge badge-danger">معطل</span>';
            
            tbody.innerHTML += `
                <tr>
                    <td>${shop.id}</td>
                    <td><strong>${shop.name}</strong></td>
                    <td>${shop.location || '-'}</td>
                    <td>${shop.phone || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-warning" onclick="editShop(${shop.id})" style="padding: 8px 15px;">✏️</button>
                        <button class="btn btn-danger" onclick="deleteShop(${shop.id})" style="padding: 8px 15px;">🗑️</button>
                    </td>
                </tr>
            `;
        });
    }
}

function openShopModal(id = null) {
    document.getElementById('shop-modal').classList.add('active');
    
    if (id) {
        const row = event.target.closest('tr');
        document.getElementById('shop-id').value = id;
        document.getElementById('shop-name').value = row.cells[1].textContent;
        document.getElementById('shop-location').value = row.cells[2].textContent === '-' ? '' : row.cells[2].textContent;
        document.getElementById('shop-phone').value = row.cells[3].textContent === '-' ? '' : row.cells[3].textContent;
        document.getElementById('shop-active').checked = row.cells[4].textContent === 'نشط';
    } else {
        document.getElementById('shop-form').reset();
        document.getElementById('shop-id').value = '';
    }
}

async function saveShop(e) {
    e.preventDefault();
    
    const id = document.getElementById('shop-id').value;
    const data = {
        id: id || undefined,
        name: document.getElementById('shop-name').value,
        location: document.getElementById('shop-location').value,
        phone: document.getElementById('shop-phone').value,
        is_active: document.getElementById('shop-active').checked ? 1 : 0
    };
    
    const action = id ? 'update_shop' : 'add_shop';
    const result = await apiCall(action, 'POST', data);
    
    if (result.success) {
        showAlert(result.message, 'success');
        closeModal('shop-modal');
        loadShops();
        loadShopsForFilters();
    } else {
        showAlert(result.message, 'danger');
    }
}

function editShop(id) {
    openShopModal(id);
}

async function deleteShop(id) {
    if (!confirm('هل أنت متأكد من حذف هذا المحل؟ سيتم حذف جميع البيانات المرتبطة به!')) return;
    
    const result = await apiCall(`delete_shop&id=${id}`, 'POST');
    
    if (result.success) {
        showAlert(result.message, 'success');
        loadShops();
        loadShopsForFilters();
    } else {
        showAlert(result.message, 'danger');
    }
}

// ========== دوال إدارة المستخدمين ==========

async function loadUsers() {
    const result = await apiCall('get_users');
    
    if (result.success) {
        const tbody = document.querySelector('#users-table tbody');
        tbody.innerHTML = '';
        
        result.data.forEach(user => {
            const roleBadge = user.role === 'admin' ? 
                '<span class="badge badge-success">مدير</span>' : 
                '<span class="badge badge-primary">بائع</span>';
            
            const statusBadge = user.is_active ? 
                '<span class="badge badge-success">نشط</span>' : 
                '<span class="badge badge-danger">معطل</span>';
            
            tbody.innerHTML += `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td><strong>${user.full_name}</strong></td>
                    <td>${roleBadge}</td>
                    <td>${user.shop_name || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-warning" onclick="editUser(${user.id})" style="padding: 8px 15px;">✏️</button>
                        <button class="btn btn-danger" onclick="deleteUser(${user.id})" style="padding: 8px 15px;">🗑️</button>
                    </td>
                </tr>
            `;
        });
    }
}

function toggleShopField() {
    const role = document.getElementById('user-role').value;
    const shopGroup = document.getElementById('user-shop-group');
    
    if (role === 'admin') {
        shopGroup.style.display = 'none';
        document.getElementById('user-shop').removeAttribute('required');
    } else {
        shopGroup.style.display = 'block';
        document.getElementById('user-shop').setAttribute('required', 'required');
    }
}

function openUserModal(id = null) {
    document.getElementById('user-modal').classList.add('active');
    
    // تحميل قائمة المحلات
    const shopSelect = document.getElementById('user-shop');
    shopSelect.innerHTML = '<option value="">اختر المحل</option>';
    allShops.forEach(shop => {
        shopSelect.innerHTML += `<option value="${shop.id}">${shop.name}</option>`;
    });
    
    if (id) {
        // تعديل مستخدم موجود
        document.getElementById('user-id').value = id;
        document.getElementById('user-password').removeAttribute('required');
        document.querySelector('#user-password').parentElement.querySelector('small').style.display = 'block';
    } else {
        // مستخدم جديد
        document.getElementById('user-form').reset();
        document.getElementById('user-id').value = '';
        document.getElementById('user-password').setAttribute('required', 'required');
        document.querySelector('#user-password').parentElement.querySelector('small').style.display = 'none';
    }
    
    toggleShopField();
}

async function saveUser(e) {
    e.preventDefault();
    
    const id = document.getElementById('user-id').value;
    const data = {
        id: id || undefined,
        username: document.getElementById('user-username').value,
        password: document.getElementById('user-password').value,
        full_name: document.getElementById('user-fullname').value,
        role: document.getElementById('user-role').value,
        shop_id: document.getElementById('user-role').value === 'seller' ? document.getElementById('user-shop').value : null,
        is_active: document.getElementById('user-active').checked ? 1 : 0
    };
    
    const action = id ? 'update_user' : 'add_user';
    const result = await apiCall(action, 'POST', data);
    
    if (result.success) {
        showAlert(result.message, 'success');
        closeModal('user-modal');
        loadUsers();
    } else {
        showAlert(result.message, 'danger');
    }
}

function editUser(id) {
    openUserModal(id);
    
    // ملء البيانات من الصف
    const users = document.querySelectorAll('#users-table tbody tr');
    users.forEach(row => {
        if (row.cells[0].textContent == id) {
            document.getElementById('user-username').value = row.cells[1].textContent;
            document.getElementById('user-fullname').value = row.cells[2].textContent;
            document.getElementById('user-role').value = row.cells[3].textContent.includes('مدير') ? 'admin' : 'seller';
            
            // البحث عن المحل
            if (row.cells[4].textContent !== '-') {
                const shopName = row.cells[4].textContent;
                const shop = allShops.find(s => s.name === shopName);
                if (shop) {
                    document.getElementById('user-shop').value = shop.id;
                }
            }
            
            document.getElementById('user-active').checked = row.cells[5].textContent.includes('نشط');
            toggleShopField();
        }
    });
}

async function deleteUser(id) {
    if (!confirm('هل أنت متأكد من حذف هذا المستخدم؟')) return;
    
    const result = await apiCall(`delete_user&id=${id}`, 'POST');
    
    if (result.success) {
        showAlert(result.message, 'success');
        loadUsers();
    } else {
        showAlert(result.message, 'danger');
    }
}

// ========== تحميل الصفحات عند التحميل ==========
window.addEventListener('load', function() {
    if (userRole === 'admin') {
        if (document.getElementById('shops')) {
            loadShops();
        }
        if (document.getElementById('users')) {
            loadUsers();
        }
    }
});