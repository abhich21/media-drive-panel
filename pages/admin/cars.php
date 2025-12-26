<?php
/**
 * MDM Admin - Manage Cars
 * Admin car management page with dynamic loading, filtering, and CRUD
 */

$pageTitle = 'Manage Cars';
$currentPage = 'cars';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireAdmin();

include __DIR__ . '/../../components/layout.php';
?>

<!-- Actions Bar -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Event Filter Dropdown -->
            <select id="eventFilter" class="px-4 py-2 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none text-sm">
                <option value="all">All Events</option>
            </select>
            
            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-2 flex-wrap">
                <button onclick="filterByStatus('all')" class="status-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors active" data-status="all">All</button>
                <button onclick="filterByStatus('standby')" class="status-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-status="standby">Standby</button>
                <button onclick="filterByStatus('on_drive')" class="status-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-status="on_drive">On Drive</button>
                <button onclick="filterByStatus('returned')" class="status-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors" data-status="returned">Returned</button>
                <button onclick="filterByStatus('deleted')" class="status-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors text-red-500" data-status="deleted">Deleted</button>
            </div>
            
            <span id="totalCount" class="text-sm text-mdm-text/60">Loading...</span>
        </div>
        <button type="button" onclick="openAddModal()" class="mdm-header-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Car
        </button>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="mdm-card p-8 text-center">
    <div class="animate-spin w-8 h-8 border-4 border-mdm-sidebar border-t-transparent rounded-full mx-auto mb-4"></div>
    <p class="text-mdm-text/60">Loading cars...</p>
</div>

<!-- Empty State -->
<div id="emptyState" class="mdm-card p-8 text-center hidden">
    <div class="text-4xl mb-4">🚗</div>
    <h3 class="font-semibold text-lg mb-2">No cars found</h3>
    <p class="text-mdm-text/60 mb-4">No cars match your current filters</p>
    <button onclick="openAddModal()" class="px-4 py-2 bg-mdm-sidebar text-white rounded-xl hover:bg-black transition-colors">Add First Car</button>
</div>

<!-- Cars Table -->
<div id="carsTable" class="mdm-card overflow-hidden hidden">
    <table class="w-full">
        <thead class="bg-mdm-tag/50">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Car Name</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Car Code</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Engine Number</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Color</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Event</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Status</th>
                <th class="text-right px-4 py-3 text-sm font-semibold text-mdm-text">Actions</th>
            </tr>
        </thead>
        <tbody id="carsTableBody" class="divide-y divide-mdm-tag/30">
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div id="pagination" class="mt-6 flex items-center justify-between hidden">
    <span id="paginationInfo" class="text-sm text-mdm-text/60"></span>
    <div class="flex gap-2">
        <button id="prevBtn" onclick="changePage(-1)" class="px-4 py-2 rounded-xl border border-mdm-tag hover:bg-mdm-bg transition-colors disabled:opacity-50" disabled>Previous</button>
        <button id="nextBtn" onclick="changePage(1)" class="px-4 py-2 rounded-xl border border-mdm-tag hover:bg-mdm-bg transition-colors disabled:opacity-50" disabled>Next</button>
    </div>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>

<!-- Add/Edit Car Modal -->
<div id="carModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div class="p-6 border-b border-mdm-tag/30">
            <div class="flex justify-between items-center">
                <h3 id="modalTitle" class="text-xl font-semibold">Add New Car</h3>
                <button onclick="closeModal()" class="text-2xl text-mdm-text/60 hover:text-mdm-text">&times;</button>
            </div>
        </div>
        <form id="carForm" class="p-6 space-y-4">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="carId" value="">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Car Name *</label>
                    <input type="text" name="name" id="carName" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none"
                           placeholder="e.g. Tata Nexon">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Model</label>
                    <input type="text" name="model" id="carModel"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none"
                           placeholder="e.g. EV Max">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Car Code</label>
                    <input type="text" name="car_code" id="carCode"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none"
                           placeholder="e.g. CAR001">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Engine Number</label>
                    <input type="text" name="engine_number" id="carEngineNumber"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none"
                           placeholder="e.g. EN123456">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Registration</label>
                    <input type="text" name="registration_number" id="carRegistration"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none"
                           placeholder="e.g. MH01AB1234">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Color</label>
                    <input type="text" name="color" id="carColor"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none"
                           placeholder="e.g. Teal Blue">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Event *</label>
                    <select name="event_id" id="carEventId" required
                            class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                        <option value="">Select Event</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Initial KM</label>
                    <input type="number" name="initial_km" id="carInitialKm" step="0.1" value="0"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Initial Fuel %</label>
                    <input type="number" name="initial_fuel" id="carInitialFuel" min="0" max="100" value="100"
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t border-mdm-tag/30">
                <button type="button" onclick="closeModal()" 
                        class="flex-1 py-3 border border-mdm-tag rounded-xl text-mdm-text hover:bg-mdm-bg transition-colors">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                        class="flex-1 py-3 bg-mdm-sidebar text-white rounded-xl hover:bg-black transition-colors">
                    Save Car
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 400px; padding: 24px; text-align: center;">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Delete Car</h3>
        <p class="text-mdm-text/60 mb-6">Are you sure you want to delete <strong id="deleteCarName"></strong>?</p>
        <div style="display: flex; gap: 12px; margin-top: 16px;">
            <button onclick="closeDeleteModal()" 
                    style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 12px; background: white; cursor: pointer;">
                Cancel
            </button>
            <button onclick="confirmDelete()" id="confirmDeleteBtn"
                    style="flex: 1; padding: 12px; border: none; border-radius: 12px; background: #dc2626; color: white; cursor: pointer;">
                Delete
            </button>
        </div>
    </div>
</div>

<style>
.status-btn {
    background: var(--mdm-bg, #f5f5f5);
    color: var(--mdm-text, #080808);
    opacity: 0.7;
}
.status-btn:hover { opacity: 1; }
.status-btn.active {
    background: var(--mdm-sidebar, #080808);
    color: white;
    opacity: 1;
}
</style>

<script>
const basePath = '<?= BASE_PATH ?>';
let currentPage = 1;
let currentEventId = 'all';
let currentStatus = 'all';
let deleteCarId = null;
let eventsData = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
    loadCars();
    
    document.getElementById('eventFilter').addEventListener('change', function() {
        currentEventId = this.value;
        currentPage = 1;
        loadCars();
    });
    
    document.getElementById('carForm').addEventListener('submit', handleFormSubmit);
});

// Load events for dropdown
async function loadEvents() {
    try {
        const response = await fetch(`${basePath}/api/cars.php?action=get_events`);
        const data = await response.json();
        
        if (data.success) {
            eventsData = data.data;
            const filterDropdown = document.getElementById('eventFilter');
            const modalDropdown = document.getElementById('carEventId');
            
            eventsData.forEach(event => {
                filterDropdown.innerHTML += `<option value="${event.id}">${escapeHtml(event.name)}</option>`;
                modalDropdown.innerHTML += `<option value="${event.id}">${escapeHtml(event.name)}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load events:', error);
    }
}

// Load cars with current filters
async function loadCars() {
    showLoading();
    
    try {
        let url = `${basePath}/api/cars.php?action=list&page=${currentPage}`;
        if (currentEventId !== 'all') url += `&event_id=${currentEventId}`;
        if (currentStatus !== 'all') url += `&status=${currentStatus}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderCars(data.data.cars);
            renderPagination(data.data.pagination);
        } else {
            showEmpty();
        }
    } catch (error) {
        console.error('Failed to load cars:', error);
        showEmpty();
    }
}

// Render cars table
function renderCars(cars) {
    hideLoading();
    
    if (!cars || cars.length === 0) {
        showEmpty();
        return;
    }
    
    const tbody = document.getElementById('carsTableBody');
    const statusColors = {
        'standby': ['bg-gray-100', 'text-gray-700'],
        'cleaning': ['bg-yellow-100', 'text-yellow-700'],
        'cleaned': ['bg-blue-100', 'text-blue-700'],
        'pod_lineup': ['bg-purple-100', 'text-purple-700'],
        'on_drive': ['bg-green-100', 'text-green-700'],
        'returned': ['bg-orange-100', 'text-orange-700'],
        'hotel': ['bg-pink-100', 'text-pink-700'],
        'out_of_service': ['bg-red-100', 'text-red-700'],
        'under_inspection': ['bg-indigo-100', 'text-indigo-700']
    };
    
    tbody.innerHTML = cars.map(car => {
        const isDeleted = car.is_active == 0;
        const [bgColor, textColor] = isDeleted ? ['bg-red-100', 'text-red-700'] : (statusColors[car.status] || ['bg-gray-100', 'text-gray-700']);
        const statusLabel = isDeleted ? 'Deleted' : car.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        // Different action buttons for deleted vs active cars
        const actionButtons = isDeleted ? `
            <button onclick="restoreCar(${car.id}, '${escapeHtml(car.name)}')" class="text-green-500 hover:text-green-700" title="Restore">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        ` : `
            <button onclick="openEditModal(${car.id})" class="text-mdm-text/60 hover:text-mdm-text mr-2" title="Edit">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button onclick="openDeleteModal(${car.id}, '${escapeHtml(car.name)}')" class="text-red-400 hover:text-red-600" title="Delete">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        `;
        
        return `
            <tr class="hover:bg-mdm-bg/50 transition-colors">
                <td class="px-4 py-3">
                    <div class="font-medium text-mdm-text">${escapeHtml(car.name)}</div>
                    <div class="text-xs text-mdm-text/50">${escapeHtml(car.model || '')}</div>
                </td>
                <td class="px-4 py-3 text-mdm-text/70">${escapeHtml(car.car_code || '-')}</td>
                <td class="px-4 py-3 text-mdm-text/70">${escapeHtml(car.engine_number || '-')}</td>
                <td class="px-4 py-3 text-mdm-text/70">${escapeHtml(car.color || '')}</td>
                <td class="px-4 py-3 text-mdm-text/70">${escapeHtml(car.event_name || 'N/A')}</td>
                <td class="px-4 py-3">
                    <span class="inline-block px-3 py-1.5 rounded-full text-xs font-medium ${bgColor} ${textColor}">${statusLabel}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    ${actionButtons}
                </td>
            </tr>
        `;
    }).join('');
    
    document.getElementById('carsTable').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

// Render pagination
function renderPagination(pagination) {
    document.getElementById('totalCount').textContent = `Total: ${pagination.total} cars`;
    
    if (pagination.total_pages <= 1) {
        document.getElementById('pagination').classList.add('hidden');
        return;
    }
    
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
    
    document.getElementById('paginationInfo').textContent = `Showing ${start}-${end} of ${pagination.total}`;
    document.getElementById('prevBtn').disabled = !pagination.has_prev;
    document.getElementById('nextBtn').disabled = !pagination.has_next;
    document.getElementById('pagination').classList.remove('hidden');
}

// Filter by status
function filterByStatus(status) {
    currentStatus = status;
    currentPage = 1;
    
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === status);
    });
    
    loadCars();
}

// Change page
function changePage(delta) {
    currentPage += delta;
    loadCars();
}

// Show/hide states
function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('carsTable').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('pagination').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
}

function showEmpty() {
    hideLoading();
    document.getElementById('carsTable').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('pagination').classList.add('hidden');
    document.getElementById('totalCount').textContent = 'Total: 0 cars';
}

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Car';
    document.getElementById('formAction').value = 'create';
    document.getElementById('carForm').reset();
    document.getElementById('carId').value = '';
    document.getElementById('carModal').style.display = 'flex';
}

async function openEditModal(carId) {
    try {
        const response = await fetch(`${basePath}/api/cars.php?action=get&id=${carId}`);
        const data = await response.json();
        
        if (data.success) {
            const car = data.data;
            document.getElementById('modalTitle').textContent = 'Edit Car';
            document.getElementById('formAction').value = 'update';
            document.getElementById('carId').value = car.id;
            document.getElementById('carName').value = car.name || '';
            document.getElementById('carModel').value = car.model || '';
            document.getElementById('carCode').value = car.car_code || '';
            document.getElementById('carEngineNumber').value = car.engine_number || '';
            document.getElementById('carRegistration').value = car.registration_number || '';
            document.getElementById('carColor').value = car.color || '';
            document.getElementById('carEventId').value = car.event_id || '';
            document.getElementById('carInitialKm').value = car.initial_km || 0;
            document.getElementById('carInitialFuel').value = car.initial_fuel || 100;
            document.getElementById('carModal').style.display = 'flex';
        } else {
            alert('Failed to load car details');
        }
    } catch (error) {
        alert('An error occurred');
    }
}

function closeModal() {
    document.getElementById('carModal').style.display = 'none';
    document.getElementById('submitBtn').textContent = 'Save Car';
    document.getElementById('submitBtn').disabled = false;
}

// Form submit
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${basePath}/api/cars.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal();
            loadCars();
        } else {
            alert(data.message || 'Failed to save car');
            submitBtn.textContent = 'Save Car';
            submitBtn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred');
        submitBtn.textContent = 'Save Car';
        submitBtn.disabled = false;
    }
}

// Delete functions
function openDeleteModal(carId, carName) {
    deleteCarId = carId;
    document.getElementById('deleteCarName').textContent = carName;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    deleteCarId = null;
    document.getElementById('deleteModal').style.display = 'none';
    document.getElementById('confirmDeleteBtn').textContent = 'Delete';
    document.getElementById('confirmDeleteBtn').disabled = false;
}

async function confirmDelete() {
    if (!deleteCarId) return;
    
    const btn = document.getElementById('confirmDeleteBtn');
    btn.textContent = 'Deleting...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', deleteCarId);
    
    try {
        const response = await fetch(`${basePath}/api/cars.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeDeleteModal();
            loadCars();
        } else {
            alert(data.message || 'Failed to delete car');
            btn.textContent = 'Delete';
            btn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred');
        btn.textContent = 'Delete';
        btn.disabled = false;
    }
}

// Restore deleted car
async function restoreCar(carId, carName) {
    if (!confirm(`Restore "${carName}"?`)) return;
    
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('id', carId);
    
    try {
        const response = await fetch(`${basePath}/api/cars.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadCars();
        } else {
            alert(data.message || 'Failed to restore car');
        }
    } catch (error) {
        alert('An error occurred');
    }
}

// Utility
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modals on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

// Close on backdrop click
document.getElementById('carModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>