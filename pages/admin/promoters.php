<?php
/**
 * MDM Admin - Manage Promoters
 * Admin promoter management page with dynamic loading, filtering, and CRUD
 */

$pageTitle = 'Manage Promoters';
$currentPage = 'promoters';
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
            
            <!-- Filter Tabs -->
            <div class="flex items-center gap-2">
                <button onclick="filterByType('all')" class="filter-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors active" data-filter="all">All</button>
                <button onclick="filterByType('deleted')" class="filter-btn px-3 py-2 rounded-lg text-sm font-medium transition-colors text-red-500" data-filter="deleted">Deleted</button>
            </div>
            
            <span id="totalCount" class="text-sm text-mdm-text/60">Loading...</span>
        </div>
        <button type="button" onclick="openAddModal()" class="mdm-header-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Promoter
        </button>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="mdm-card p-8 text-center">
    <div class="animate-spin w-8 h-8 border-4 border-mdm-sidebar border-t-transparent rounded-full mx-auto mb-4"></div>
    <p class="text-mdm-text/60">Loading promoters...</p>
</div>

<!-- Empty State -->
<div id="emptyState" class="mdm-card p-8 text-center hidden">
    <div class="text-4xl mb-4">👥</div>
    <h3 class="font-semibold text-lg mb-2">No promoters found</h3>
    <p class="text-mdm-text/60 mb-4">No promoters match your current filters</p>
    <button onclick="openAddModal()" class="px-4 py-2 bg-mdm-sidebar text-white rounded-xl hover:bg-black transition-colors">Add First Promoter</button>
</div>

<!-- Promoters Grid -->
<div id="promotersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 hidden">
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

<!-- Add/Edit Promoter Modal -->
<div id="promoterModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 550px; max-height: 90vh; overflow-y: auto;">
        <div style="padding: 24px; border-bottom: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="font-size: 20px; font-weight: 600; margin: 0;">Add New Promoter</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
        </div>
        <form id="promoterForm" style="padding: 24px;">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="promoterId" value="">
            <input type="hidden" name="event_promoter_id" id="eventPromoterId" value="">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Name *</label>
                    <input type="text" name="name" id="promoterName" required
                           style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; box-sizing: border-box;"
                           placeholder="e.g. John Smith">
                </div>
                <div>
                    <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Phone</label>
                    <input type="text" name="phone" id="promoterPhone"
                           style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; box-sizing: border-box;"
                           placeholder="e.g. +91 98765 43210">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Email *</label>
                <input type="email" name="email" id="promoterEmail" required
                       style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; box-sizing: border-box;"
                       placeholder="e.g. john@example.com">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Password <span id="passwordRequired">*</span></label>
                <div style="position: relative;">
                    <input type="password" name="password" id="promoterPassword"
                           style="width: 100%; padding: 12px 16px; padding-right: 50px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; box-sizing: border-box;"
                           placeholder="Enter password">
                    <button type="button" onclick="togglePasswordVisibility()" id="togglePasswordBtn"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px;">
                        <svg id="eyeIcon" style="width: 20px; height: 20px; color: #666;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Event *</label>
                <select name="event_id" id="promoterEventId" required
                        style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; box-sizing: border-box;"
                        onchange="loadCarsForEvent(this.value)">
                    <option value="">Select Event</option>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px;">Assign Cars</label>
                <div id="carsCheckboxContainer" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; border-radius: 12px; padding: 12px;">
                    <p style="color: #999; font-size: 13px; margin: 0;">Select an event first to see available cars</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #ddd;">
                <button type="button" onclick="closeModal()"
                        style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 12px; background: white; cursor: pointer; font-size: 14px;">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                        style="flex: 1; padding: 12px; border: none; border-radius: 12px; background: #080808; color: white; cursor: pointer; font-size: 14px;">
                    Save Promoter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 400px; padding: 24px; text-align: center;">
        <div style="width: 64px; height: 64px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <svg style="width: 32px; height: 32px; color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">Delete Promoter</h3>
        <p style="color: #666; margin-bottom: 24px;">Are you sure you want to delete <strong id="deletePromoterName"></strong>?</p>
        <div style="display: flex; gap: 12px;">
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

<!-- View Promoter Modal -->
<div id="viewModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px;">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div style="padding: 24px; border-bottom: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 20px; font-weight: 600; margin: 0;">Promoter Details</h3>
                <button onclick="closeViewModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
        </div>
        <div style="padding: 24px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <div id="viewAvatar" style="width: 80px; height: 80px; border-radius: 50%; background: #f5f5f5; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 32px; font-weight: 700;"></div>
                <h4 id="viewName" style="font-size: 20px; font-weight: 600; margin: 0 0 4px;"></h4>
                <p id="viewEmail" style="color: #666; margin: 0;"></p>
            </div>
            
            <div style="border: 1px solid #eee; border-radius: 12px; overflow: hidden;">
                <div style="display: flex; border-bottom: 1px solid #eee;">
                    <div style="flex: 1; padding: 12px 16px; background: #f9f9f9; font-weight: 500;">Phone</div>
                    <div id="viewPhone" style="flex: 2; padding: 12px 16px;"></div>
                </div>
                <div style="display: flex; border-bottom: 1px solid #eee;">
                    <div style="flex: 1; padding: 12px 16px; background: #f9f9f9; font-weight: 500;">Password</div>
                    <div id="viewPassword" style="flex: 2; padding: 12px 16px; font-family: monospace;"></div>
                </div>
                <div style="display: flex; border-bottom: 1px solid #eee;">
                    <div style="flex: 1; padding: 12px 16px; background: #f9f9f9; font-weight: 500;">Event</div>
                    <div id="viewEvent" style="flex: 2; padding: 12px 16px;"></div>
                </div>
                <div style="display: flex;">
                    <div style="flex: 1; padding: 12px 16px; background: #f9f9f9; font-weight: 500;">Assigned Cars</div>
                    <div id="viewCars" style="flex: 2; padding: 12px 16px;"></div>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button onclick="closeViewModal()"
                        style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 12px; background: #080808; color: white; cursor: pointer; font-size: 14px;">
                    Close
                </button>
                <!-- <button onclick="closeViewModal(); openEditModal(currentViewPromoterId);"
                        style="flex: 1; padding: 12px; border: none; border-radius: 12px; background: #080808; color: white; cursor: pointer; font-size: 14px;">
                    Edit Promoter
                </button> -->
            </div>
        </div>
    </div>
</div>

<style>
.filter-btn {
    background: var(--mdm-bg, #f5f5f5);
    color: var(--mdm-text, #080808);
    opacity: 0.7;
}
.filter-btn:hover { opacity: 1; }
.filter-btn.active {
    background: var(--mdm-sidebar, #080808);
    color: white;
    opacity: 1;
}
</style>

<script>
const basePath = '<?= BASE_PATH ?>';
let currentPage = 1;
let currentEventId = localStorage.getItem('promoters_eventId') || 'all';
let currentFilter = localStorage.getItem('promoters_filter') || 'all';
let deletePromoterId = null;
let currentViewPromoterId = null;
let eventsData = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
    loadPromoters();
    
    // Restore filter tab
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === currentFilter);
    });
    
    document.getElementById('eventFilter').addEventListener('change', function() {
        currentEventId = this.value;
        localStorage.setItem('promoters_eventId', currentEventId);
        currentPage = 1;
        loadPromoters();
    });
    
    document.getElementById('promoterForm').addEventListener('submit', handleFormSubmit);
});

// Load events for dropdown
async function loadEvents() {
    try {
        const response = await fetch(`${basePath}/api/promoters.php?action=get_events`);
        const data = await response.json();
        
        if (data.success) {
            eventsData = data.data;
            const filterDropdown = document.getElementById('eventFilter');
            const modalDropdown = document.getElementById('promoterEventId');
            
            eventsData.forEach(event => {
                const selected = event.id == currentEventId ? 'selected' : '';
                filterDropdown.innerHTML += `<option value="${event.id}" ${selected}>${escapeHtml(event.name)}</option>`;
                modalDropdown.innerHTML += `<option value="${event.id}">${escapeHtml(event.name)}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load events:', error);
    }
}

// Load cars for selected event
async function loadCarsForEvent(eventId) {
    const container = document.getElementById('carsCheckboxContainer');
    
    if (!eventId) {
        container.innerHTML = '<p style="color: #999; font-size: 13px; margin: 0;">Select an event first to see available cars</p>';
        return;
    }
    
    container.innerHTML = '<p style="color: #999; font-size: 13px; margin: 0;">Loading cars...</p>';
    
    try {
        const response = await fetch(`${basePath}/api/cars.php?action=list&event_id=${eventId}&per_page=100`);
        const data = await response.json();
        
        if (data.success && data.data.cars && data.data.cars.length > 0) {
            container.innerHTML = data.data.cars.map(car => `
                <label style="display: flex; align-items: center; padding: 8px 0; cursor: pointer; border-bottom: 1px solid #f0f0f0;">
                    <input type="checkbox" name="assigned_cars[]" value="${car.id}" 
                           style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                    <span style="font-size: 14px;">${escapeHtml(car.name)} ${car.car_code ? '(' + escapeHtml(car.car_code) + ')' : ''}</span>
                </label>
            `).join('');
        } else {
            container.innerHTML = '<p style="color: #999; font-size: 13px; margin: 0;">No cars available for this event</p>';
        }
    } catch (error) {
        container.innerHTML = '<p style="color: #dc2626; font-size: 13px; margin: 0;">Failed to load cars</p>';
    }
}

// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('promoterPassword');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
        `;
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}

// Load promoters with current filters
async function loadPromoters() {
    showLoading();
    
    try {
        let url = `${basePath}/api/promoters.php?action=list&page=${currentPage}&filter=${currentFilter}`;
        if (currentEventId !== 'all') url += `&event_id=${currentEventId}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderPromoters(data.data.promoters);
            renderPagination(data.data.pagination);
        } else {
            showEmpty();
        }
    } catch (error) {
        console.error('Failed to load promoters:', error);
        showEmpty();
    }
}

// Render promoters grid
function renderPromoters(promoters) {
    hideLoading();
    
    if (!promoters || promoters.length === 0) {
        showEmpty();
        return;
    }
    
    const grid = document.getElementById('promotersGrid');
    const isDeleted = currentFilter === 'deleted';
    
    grid.innerHTML = promoters.map(promoter => {
        const initial = promoter.name ? promoter.name.charAt(0).toUpperCase() : '?';
        
        const actionButtons = isDeleted ? `
            <button onclick="restorePromoter(${promoter.id}, '${escapeHtml(promoter.name)}')" 
                    style="padding: 8px; border-radius: 8px; background: #dcfce7; border: none; cursor: pointer;" title="Restore">
                <svg style="width: 16px; height: 16px; color: #22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        ` : `
            <button onclick="openViewModal(${promoter.id})" 
                    style="padding: 8px; border-radius: 8px; background: #e0f2fe; border: none; cursor: pointer; margin-right: 8px;" title="View">
                <svg style="width: 16px; height: 16px; color: #0284c7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
            <button onclick="openEditModal(${promoter.id})" 
                    style="padding: 8px; border-radius: 8px; background: #f3f4f6; border: none; cursor: pointer; margin-right: 8px;" title="Edit">
                <svg style="width: 16px; height: 16px; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button onclick="openDeleteModal(${promoter.id}, '${escapeHtml(promoter.name)}')" 
                    style="padding: 8px; border-radius: 8px; background: #fee2e2; border: none; cursor: pointer;" title="Delete">
                <svg style="width: 16px; height: 16px; color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        `;
        
        return `
            <div class="mdm-card mdm-card-hover">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--mdm-tag, #f5f5f5); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 18px; font-weight: 700; color: var(--mdm-text, #080808);">${initial}</span>
                    </div>
                </div>
                <h3 style="font-weight: 600; margin-bottom: 4px;">${escapeHtml(promoter.name)}</h3>
                <p style="font-size: 14px; color: #666; margin-bottom: 4px;">${escapeHtml(promoter.email || '')}</p>
                <p style="font-size: 14px; color: #666; margin-bottom: 12px;">${escapeHtml(promoter.phone || '')}</p>
                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(0,0,0,0.1);">
                    <span style="font-size: 14px; color: #666;">${promoter.assigned_cars_count || 0} cars assigned</span>
                    <div>${actionButtons}</div>
                </div>
            </div>
        `;
    }).join('');
    
    document.getElementById('promotersGrid').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

// Render pagination
function renderPagination(pagination) {
    document.getElementById('totalCount').textContent = `Total: ${pagination.total} promoters`;
    
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

// Filter by type
function filterByType(filter) {
    currentFilter = filter;
    currentPage = 1;
    
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });
    
    loadPromoters();
}

// Change page
function changePage(delta) {
    currentPage += delta;
    loadPromoters();
}

// Show/hide states
function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('promotersGrid').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('pagination').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
}

function showEmpty() {
    hideLoading();
    document.getElementById('promotersGrid').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('pagination').classList.add('hidden');
    document.getElementById('totalCount').textContent = 'Total: 0 promoters';
}

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Promoter';
    document.getElementById('formAction').value = 'create';
    document.getElementById('promoterForm').reset();
    document.getElementById('promoterId').value = '';
    document.getElementById('eventPromoterId').value = '';
    document.getElementById('promoterPassword').required = true;
    document.getElementById('promoterPassword').type = 'password';
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('promoterEventId').value = '';
    document.getElementById('carsCheckboxContainer').innerHTML = '<p style="color: #999; font-size: 13px; margin: 0;">Select an event first to see available cars</p>';
    document.getElementById('promoterModal').style.display = 'flex';
}

async function openEditModal(promoterId) {
    try {
        const response = await fetch(`${basePath}/api/promoters.php?action=get&id=${promoterId}`);
        const data = await response.json();
        
        if (data.success) {
            const promoter = data.data;
            document.getElementById('modalTitle').textContent = 'Edit Promoter';
            document.getElementById('formAction').value = 'update';
            document.getElementById('promoterId').value = promoter.id;
            document.getElementById('promoterName').value = promoter.name || '';
            document.getElementById('promoterEmail').value = promoter.email || '';
            document.getElementById('promoterPhone').value = promoter.phone || '';
            document.getElementById('promoterPassword').value = promoter.password || '';
            document.getElementById('promoterPassword').required = false;
            document.getElementById('promoterPassword').type = 'password';
            document.getElementById('passwordRequired').style.display = 'none';
            
            // Set event and load cars
            if (promoter.event_id) {
                document.getElementById('promoterEventId').value = promoter.event_id;
                document.getElementById('eventPromoterId').value = promoter.event_promoter_id || '';
                await loadCarsForEvent(promoter.event_id);
                
                // Check assigned cars
                if (promoter.assigned_cars) {
                    const carIds = promoter.assigned_cars.split(',');
                    carIds.forEach(carId => {
                        const checkbox = document.querySelector(`input[name="assigned_cars[]"][value="${carId.trim()}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            } else {
                document.getElementById('promoterEventId').value = '';
                document.getElementById('eventPromoterId').value = '';
                document.getElementById('carsCheckboxContainer').innerHTML = '<p style="color: #999; font-size: 13px; margin: 0;">Select an event first to see available cars</p>';
            }
            
            document.getElementById('promoterModal').style.display = 'flex';
        } else {
            alert('Failed to load promoter details');
        }
    } catch (error) {
        alert('An error occurred');
    }
}

function closeModal() {
    document.getElementById('promoterModal').style.display = 'none';
    document.getElementById('submitBtn').textContent = 'Save Promoter';
    document.getElementById('submitBtn').disabled = false;
}

// View modal functions
async function openViewModal(promoterId) {
    currentViewPromoterId = promoterId;
    
    try {
        const response = await fetch(`${basePath}/api/promoters.php?action=get&id=${promoterId}`);
        const data = await response.json();
        
        if (data.success) {
            const promoter = data.data;
            const initial = promoter.name ? promoter.name.charAt(0).toUpperCase() : '?';
            
            document.getElementById('viewAvatar').textContent = initial;
            document.getElementById('viewName').textContent = promoter.name || '';
            document.getElementById('viewEmail').textContent = promoter.email || '';
            document.getElementById('viewPhone').textContent = promoter.phone || '-';
            document.getElementById('viewPassword').textContent = promoter.password || '-';
            
            // Get event name
            if (promoter.event_id) {
                const eventName = eventsData.find(e => e.id == promoter.event_id)?.name || `Event #${promoter.event_id}`;
                document.getElementById('viewEvent').textContent = eventName;
            } else {
                document.getElementById('viewEvent').textContent = 'Not assigned';
            }
            
            // Get car names
            if (promoter.assigned_cars) {
                const carIds = promoter.assigned_cars.split(',');
                try {
                    const carsResponse = await fetch(`${basePath}/api/cars.php?action=list&event_id=${promoter.event_id}&per_page=100`);
                    const carsData = await carsResponse.json();
                    if (carsData.success && carsData.data.cars) {
                        const carNames = carsData.data.cars
                            .filter(car => carIds.includes(String(car.id)))
                            .map(car => car.name + (car.car_code ? ` (${car.car_code})` : ''))
                            .join(', ');
                        document.getElementById('viewCars').textContent = carNames || 'None';
                    } else {
                        document.getElementById('viewCars').textContent = `${carIds.length} car(s)`;
                    }
                } catch {
                    document.getElementById('viewCars').textContent = `${carIds.length} car(s)`;
                }
            } else {
                document.getElementById('viewCars').textContent = 'None';
            }
            
            document.getElementById('viewModal').style.display = 'flex';
        } else {
            alert('Failed to load promoter details');
        }
    } catch (error) {
        alert('An error occurred');
    }
}

function closeViewModal() {
    currentViewPromoterId = null;
    document.getElementById('viewModal').style.display = 'none';
}

// Form submit
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${basePath}/api/promoters.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeModal();
            loadPromoters();
        } else {
            alert(data.message || 'Failed to save promoter');
            submitBtn.textContent = 'Save Promoter';
            submitBtn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred');
        submitBtn.textContent = 'Save Promoter';
        submitBtn.disabled = false;
    }
}

// Delete functions
function openDeleteModal(promoterId, promoterName) {
    deletePromoterId = promoterId;
    document.getElementById('deletePromoterName').textContent = promoterName;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    deletePromoterId = null;
    document.getElementById('deleteModal').style.display = 'none';
    document.getElementById('confirmDeleteBtn').textContent = 'Delete';
    document.getElementById('confirmDeleteBtn').disabled = false;
}

async function confirmDelete() {
    if (!deletePromoterId) return;
    
    const btn = document.getElementById('confirmDeleteBtn');
    btn.textContent = 'Deleting...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', deletePromoterId);
    
    try {
        const response = await fetch(`${basePath}/api/promoters.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeDeleteModal();
            loadPromoters();
        } else {
            alert(data.message || 'Failed to delete promoter');
            btn.textContent = 'Delete';
            btn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred');
        btn.textContent = 'Delete';
        btn.disabled = false;
    }
}

// Restore function
async function restorePromoter(promoterId, promoterName) {
    if (!confirm(`Restore "${promoterName}"?`)) return;
    
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('id', promoterId);
    
    try {
        const response = await fetch(`${basePath}/api/promoters.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadPromoters();
        } else {
            alert(data.message || 'Failed to restore promoter');
        }
    } catch (error) {
        alert('An error occurred');
    }
}

// Filter by type
function filterByType(filter) {
    currentFilter = filter;
    localStorage.setItem('promoters_filter', filter);
    currentPage = 1;
    
    // Update active tab
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });
    
    loadPromoters();
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
        closeViewModal();
    }
});

// Close on backdrop click
document.getElementById('promoterModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewModal();
});
</script>