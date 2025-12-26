<?php
/**
 * MDM Admin - Events Management
 * Admin events management page
 */

$pageTitle = 'Events';
$currentPage = 'events';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/queries/events.php';

requireAdmin();

include __DIR__ . '/../../components/layout.php';
?>

<!-- Actions Bar -->
<div class="mdm-card mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="filterEvents('all')" 
                    class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-colors active"
                    data-status="all">
                All
            </button>
            <button onclick="filterEvents('active')" 
                    class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                    data-status="active">
                Active
            </button>
            <button onclick="filterEvents('upcoming')" 
                    class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                    data-status="upcoming">
                Upcoming
            </button>
            <button onclick="filterEvents('completed')" 
                    class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-colors"
                    data-status="completed">
                Completed
            </button>
            <button onclick="filterEvents('deleted')" 
                    class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition-colors text-red-500"
                    data-status="deleted">
                Deleted
            </button>
            
            <span id="totalCount" class="ml-4 text-sm text-mdm-text/60">Loading...</span>
        </div>
        
        <button onclick="openCreateModal()" 
                style="width: fit-content;"
                class="px-4 py-2 bg-mdm-sidebar text-white text-sm font-medium rounded-xl hover:bg-black transition-colors">
            + Create Event
        </button>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="text-center py-12">
    <div class="inline-block w-8 h-8 border-4 border-mdm-tag border-t-mdm-sidebar rounded-full animate-spin"></div>
    <p class="text-mdm-text/60 mt-4">Loading events...</p>
</div>

<!-- Empty State -->
<div id="emptyState" class="hidden text-center py-12">
    <div class="w-16 h-16 mx-auto mb-4 bg-mdm-tag rounded-2xl flex items-center justify-center">
        <svg class="w-8 h-8 text-mdm-text/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-mdm-text mb-2">No events found</h3>
    <p class="text-mdm-text/60">Create your first event to get started.</p>
</div>

<!-- Events Grid -->
<div id="eventsGrid" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
    <!-- Events will be loaded here -->
</div>

<!-- Pagination -->
<div id="pagination" class="hidden flex items-center justify-between">
    <div id="paginationInfo" class="text-sm text-mdm-text/60"></div>
    <div id="paginationButtons" class="flex items-center gap-2"></div>
</div>

<!-- Create Event Modal -->
<div id="createModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 1rem;">
    <div class="bg-white rounded-2xl w-full max-w-md mx-auto" style="max-height: 90vh; overflow-y: auto;">
        <div class="p-6 border-b border-mdm-tag">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-mdm-text">Create Event</h2>
                <button onclick="closeCreateModal()" class="text-mdm-text/60 hover:text-mdm-text">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <form id="createEventForm" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Event Name *</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Client Name</label>
                <input type="text" name="client_name"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Location</label>
                <input type="text" name="location"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-mdm-text mb-2">Start Date *</label>
                    <input type="date" name="start_date" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-mdm-text mb-2">End Date *</label>
                    <input type="date" name="end_date" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Logo URL</label>
                <input type="url" name="logo_url" placeholder="https://example.com/logo.png"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeCreateModal()" 
                        class="flex-1 py-3 border border-mdm-tag rounded-xl text-mdm-text hover:bg-mdm-bg transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 py-3 bg-mdm-sidebar text-white rounded-xl hover:bg-black transition-colors">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Event Modal -->
<div id="editModal" class="hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: none; align-items: center; justify-content: center; padding: 1rem;">
    <div class="bg-white rounded-2xl w-full max-w-md mx-auto" style="max-height: 90vh; overflow-y: auto;">
        <div class="p-6 border-b border-mdm-tag">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-mdm-text">Edit Event</h2>
                <button onclick="closeEditModal()" class="text-mdm-text/60 hover:text-mdm-text">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <form id="editEventForm" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editEventId">
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Event Name *</label>
                <input type="text" name="name" id="editEventName" required
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Client Name</label>
                <input type="text" name="client_name" id="editEventClient"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Location</label>
                <input type="text" name="location" id="editEventLocation"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-mdm-text mb-2">Start Date *</label>
                    <input type="date" name="start_date" id="editEventStartDate" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-mdm-text mb-2">End Date *</label>
                    <input type="date" name="end_date" id="editEventEndDate" required
                           class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Logo URL</label>
                <input type="url" name="logo_url" id="editEventLogo" placeholder="https://example.com/logo.png"
                       class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Status</label>
                <select name="status" id="editEventStatus"
                        class="w-full px-4 py-3 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none">
                    <option value="upcoming">Upcoming</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" 
                        class="flex-1 py-3 border border-mdm-tag rounded-xl text-mdm-text hover:bg-mdm-bg transition-colors">
                    Cancel
                </button>
                <button type="submit" id="editEventSubmitBtn"
                        class="flex-1 py-3 bg-mdm-sidebar text-white rounded-xl hover:bg-black transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.filter-btn {
    background: var(--mdm-bg, #f5f5f5);
    color: var(--mdm-text, #080808);
    opacity: 0.7;
}
.filter-btn:hover {
    opacity: 1;
}
.filter-btn.active {
    background: var(--mdm-sidebar, #080808);
    color: white;
    opacity: 1;
}
</style>

<script>
const basePath = '<?= BASE_PATH ?>';
let currentStatus = 'all';
let currentPage = 1;
const perPage = 12;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
});

// Load events with filtering and pagination
async function loadEvents() {
    showLoading();
    
    try {
        const response = await fetch(
            `${basePath}/api/events.php?action=list&status=${currentStatus}&page=${currentPage}&per_page=${perPage}`
        );
        const data = await response.json();
        
        if (data.success) {
            renderEvents(data.data.events);
            renderPagination(data.data.pagination);
            updateTotalCount(data.data.pagination.total);
        } else {
            showEmpty();
        }
    } catch (error) {
        console.error('Error loading events:', error);
        showEmpty();
    }
}

// Filter events by status
function filterEvents(status) {
    currentStatus = status;
    currentPage = 1;
    
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.status === status);
    });
    
    loadEvents();
}

// Go to page
function goToPage(page) {
    currentPage = page;
    loadEvents();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Render events grid
function renderEvents(events) {
    const grid = document.getElementById('eventsGrid');
    
    if (!events || events.length === 0) {
        showEmpty();
        return;
    }
    
    const statusStyles = {
        'active': 'background-color: #22c55e; color: white;',
        'upcoming': 'background-color: #3b82f6; color: white;',
        'completed': 'background-color: #9ca3af; color: white;'
    };
    
    grid.innerHTML = events.map(event => {
        const isDeleted = event.is_deleted == 1;
        const statusStyle = isDeleted 
            ? 'background-color: #ef4444; color: white;' 
            : (statusStyles[event.status] || 'background-color: #9ca3af; color: white;');
        const statusLabel = isDeleted ? 'Deleted' : capitalize(event.status);
        const startDate = formatDate(event.start_date);
        const endDate = formatDate(event.end_date);
        const carCount = event.car_count || 0;
        
        // Different buttons for deleted vs active events
        const actionButtons = isDeleted ? `
            <button onclick="event.stopPropagation(); restoreEvent(${event.id}, '${escapeHtml(event.name)}')" 
                    style="padding: 8px; border-radius: 8px; background: #dcfce7; border: none; cursor: pointer;" title="Restore">
                <svg style="width: 16px; height: 16px; color: #22c55e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
            <button onclick="event.stopPropagation(); permanentDeleteEvent(${event.id}, '${escapeHtml(event.name)}')" 
                    style="padding: 8px; border-radius: 8px; background: #fee2e2; border: none; cursor: pointer; margin-left: 8px; title="Delete Permanently">
                <svg style="width: 16px; height: 16px; color: #dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        ` : `
            <button onclick="event.stopPropagation(); openEditModal(${event.id})" 
                    style="padding: 8px; border-radius: 8px; background: #f3f4f6; border: none; cursor: pointer;" title="Edit">
                <svg style="width: 16px; height: 16px; color: #6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            <button onclick="event.stopPropagation(); softDeleteEvent(${event.id}, '${escapeHtml(event.name)}')" 
                    style="padding: 8px; border-radius: 8px; background: #ffedd5; border: none; cursor: pointer; margin-left: 8px;" title="Move to Trash">
                <svg style="width: 16px; height: 16px; color: #f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        `;
        
        return `
            <div class="mdm-card mdm-card-hover cursor-pointer group" 
                 onclick="window.location.href='${basePath}/pages/admin/event-detail.php?id=${event.id}'">
                <div class="flex items-start justify-between mb-3">
                    <span style="padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; ${statusStyle}">${statusLabel}</span>
                    <div class="flex gap-1">
                        ${actionButtons}
                    </div>
                </div>
                <h3 class="font-semibold text-lg text-mdm-text mb-2 group-hover:text-mdm-accent transition-colors">
                    ${escapeHtml(event.name)}
                </h3>
                <p class="text-sm text-mdm-text/60 mb-3">${escapeHtml(event.client_name || 'No client')}</p>
                <div style="display: flex; align-items: center; gap: 16px; font-size: 14px; color: rgba(0,0,0,0.6); margin-bottom: 8px;">
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        📍 ${escapeHtml(event.location || 'TBD')}
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        📅 ${startDate} - ${endDate}
                    </span>
                </div>
                <div class="text-sm text-mdm-text/60 pt-2 border-t border-mdm-tag/50">
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        🚗 ${carCount} cars
                    </span>
                </div>
            </div>
        `;
    }).join('');
    
    hideLoading();
    grid.classList.remove('hidden');
}

// Render pagination
function renderPagination(pagination) {
    const container = document.getElementById('pagination');
    const info = document.getElementById('paginationInfo');
    const buttons = document.getElementById('paginationButtons');
    
    if (pagination.total_pages <= 1) {
        container.classList.add('hidden');
        return;
    }
    
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
    
    info.textContent = `Showing ${start}-${end} of ${pagination.total} events`;
    
    let buttonsHtml = '';
    
    // Previous button
    buttonsHtml += `
        <button onclick="goToPage(${pagination.current_page - 1})" 
                class="px-3 py-2 rounded-lg border border-mdm-tag hover:bg-mdm-bg transition-colors ${!pagination.has_prev ? 'opacity-50 cursor-not-allowed' : ''}"
                ${!pagination.has_prev ? 'disabled' : ''}>
            ← Prev
        </button>
    `;
    
    // Page numbers
    const maxVisible = 5;
    let startPage = Math.max(1, pagination.current_page - Math.floor(maxVisible / 2));
    let endPage = Math.min(pagination.total_pages, startPage + maxVisible - 1);
    
    if (endPage - startPage < maxVisible - 1) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        buttonsHtml += `
            <button onclick="goToPage(${i})" 
                    class="px-3 py-2 rounded-lg transition-colors ${i === pagination.current_page 
                        ? 'bg-mdm-sidebar text-white' 
                        : 'border border-mdm-tag hover:bg-mdm-bg'}">
                ${i}
            </button>
        `;
    }
    
    // Next button
    buttonsHtml += `
        <button onclick="goToPage(${pagination.current_page + 1})" 
                class="px-3 py-2 rounded-lg border border-mdm-tag hover:bg-mdm-bg transition-colors ${!pagination.has_next ? 'opacity-50 cursor-not-allowed' : ''}"
                ${!pagination.has_next ? 'disabled' : ''}>
            Next →
        </button>
    `;
    
    buttons.innerHTML = buttonsHtml;
    container.classList.remove('hidden');
}

// Update total count display
function updateTotalCount(total) {
    document.getElementById('totalCount').textContent = `Total: ${total} events`;
}

// Show/hide states
function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('eventsGrid').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('pagination').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
}

function showEmpty() {
    hideLoading();
    document.getElementById('eventsGrid').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('pagination').classList.add('hidden');
    document.getElementById('totalCount').textContent = 'Total: 0 events';
}

// Modals
function openCreateModal() {
    const modal = document.getElementById('createModal');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
}

function closeCreateModal() {
    const modal = document.getElementById('createModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    document.getElementById('createEventForm').reset();
}

async function openEditModal(eventId) {
    try {
        const response = await fetch(`${basePath}/api/events.php?action=get&id=${eventId}`);
        const data = await response.json();
        
        if (data.success) {
            const event = data.data;
            document.getElementById('editEventId').value = event.id;
            document.getElementById('editEventName').value = event.name;
            document.getElementById('editEventClient').value = event.client_name || '';
            document.getElementById('editEventLocation').value = event.location || '';
            document.getElementById('editEventStartDate').value = event.start_date;
            document.getElementById('editEventEndDate').value = event.end_date;
            document.getElementById('editEventLogo').value = event.logo_url || '';
            document.getElementById('editEventStatus').value = event.status;
            
            const modal = document.getElementById('editModal');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        } else {
            alert('Failed to load event details');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    document.getElementById('editEventForm').reset();
    
    // Reset submit button state
    const submitBtn = document.getElementById('editEventSubmitBtn');
    submitBtn.textContent = 'Save Changes';
    submitBtn.disabled = false;
}

// Form submissions
document.getElementById('createEventForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${basePath}/api/events.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeCreateModal();
            loadEvents();
        } else {
            alert(data.message || 'Failed to create event');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
});

document.getElementById('editEventForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('editEventSubmitBtn');
    const originalText = submitBtn.textContent;
    
    // Set loading state
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(`${basePath}/api/events.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Reset button before closing
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            closeEditModal();
            loadEvents();
        } else {
            alert(data.message || 'Failed to update event');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }
});

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
    }
});

// Close modals on backdrop click
document.getElementById('createModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Utility functions
function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Soft delete event (move to trash)
async function softDeleteEvent(eventId, eventName) {
    if (!confirm(`Move "${eventName}" to trash?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'soft_delete');
    formData.append('id', eventId);
    
    try {
        const response = await fetch(`${basePath}/api/events.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadEvents();
        } else {
            alert(data.message || 'Failed to move event to trash');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

// Restore soft-deleted event
async function restoreEvent(eventId, eventName) {
    if (!confirm(`Restore "${eventName}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'restore');
    formData.append('id', eventId);
    
    try {
        const response = await fetch(`${basePath}/api/events.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadEvents();
        } else {
            alert(data.message || 'Failed to restore event');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

// Permanent delete event
async function permanentDeleteEvent(eventId, eventName) {
    if (!confirm(`PERMANENTLY delete "${eventName}"?\n\nThis action CANNOT be undone!`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'permanent_delete');
    formData.append('id', eventId);
    
    try {
        const response = await fetch(`${basePath}/api/events.php`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadEvents();
        } else {
            alert(data.message || 'Failed to delete event');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>