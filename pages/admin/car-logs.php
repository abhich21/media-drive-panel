<?php
/**
 * MDM Admin - Car Logs
 * View all car activity logs with filtering and pagination
 */

$pageTitle = 'Car Logs';
$currentPage = 'car-logs';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireAdmin();

include __DIR__ . '/../../components/layout.php';
?>

<!-- Filters Bar -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Event Filter Dropdown -->
            <select id="eventFilter" class="px-4 py-2 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none text-sm">
                <option value="all">All Events</option>
            </select>
            
            <span id="totalCount" class="text-sm text-mdm-text/60">Loading...</span>
        </div>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="mdm-card p-8 text-center">
    <div class="animate-spin w-8 h-8 border-4 border-mdm-sidebar border-t-transparent rounded-full mx-auto mb-4"></div>
    <p class="text-mdm-text/60">Loading logs...</p>
</div>

<!-- Empty State -->
<div id="emptyState" class="mdm-card p-8 text-center hidden">
    <div class="text-4xl mb-4">📋</div>
    <h3 class="font-semibold text-lg mb-2">No logs found</h3>
    <p class="text-mdm-text/60">No car logs match your current filters</p>
</div>

<!-- Logs Table -->
<div id="logsTableContainer" class="mdm-card hidden" style="overflow-x: auto;">
    <table class="w-full" id="logsTable">
        <thead>
            <tr class="border-b border-mdm-tag/30">
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Date/Time</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Car</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Type</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Journalist</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">KM</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Fuel</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Notes</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-mdm-text/60">Promoter</th>
            </tr>
        </thead>
        <tbody id="logsTableBody">
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

<script>
const basePath = '<?= BASE_PATH ?>';
let currentPage = 1;
let currentEventId = localStorage.getItem('carlogs_eventId') || 'all';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadEvents();
    loadLogs();
    
    document.getElementById('eventFilter').addEventListener('change', function() {
        currentEventId = this.value;
        localStorage.setItem('carlogs_eventId', currentEventId);
        currentPage = 1;
        loadLogs();
    });
});

// Load events for dropdown
async function loadEvents() {
    try {
        const response = await fetch(`${basePath}/api/car_logs.php?action=get_events`);
        const data = await response.json();
        
        if (data.success) {
            const dropdown = document.getElementById('eventFilter');
            data.data.forEach(event => {
                const selected = event.id == currentEventId ? 'selected' : '';
                dropdown.innerHTML += `<option value="${event.id}" ${selected}>${escapeHtml(event.name)}</option>`;
            });
        }
    } catch (error) {
        console.error('Failed to load events:', error);
    }
}

// Load logs with current filters
async function loadLogs() {
    showLoading();
    
    try {
        let url = `${basePath}/api/car_logs.php?action=list&page=${currentPage}&per_page=20`;
        if (currentEventId !== 'all') url += `&event_id=${currentEventId}`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderLogs(data.data.logs);
            renderPagination(data.data.pagination);
        } else {
            showEmpty();
        }
    } catch (error) {
        console.error('Failed to load logs:', error);
        showEmpty();
    }
}

// Render logs table
function renderLogs(logs) {
    hideLoading();
    
    if (!logs || logs.length === 0) {
        showEmpty();
        return;
    }
    
    const tbody = document.getElementById('logsTableBody');
    
    const typeColors = {
        'exit': ['bg-blue-100', 'text-blue-700'],
        'return': ['bg-green-100', 'text-green-700'],
        'damage': ['bg-red-100', 'text-red-700'],
        'status_change': ['bg-yellow-100', 'text-yellow-700'],
        'note': ['bg-gray-100', 'text-gray-700']
    };
    
    tbody.innerHTML = logs.map(log => {
        const [bgColor, textColor] = typeColors[log.log_type] || ['bg-gray-100', 'text-gray-700'];
        const typeLabel = log.log_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const dateTime = formatDateTime(log.created_at);
        
        return `
            <tr class="hover:bg-mdm-bg/50 transition-colors border-b border-mdm-tag/20">
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium">${dateTime.date}</div>
                    <div class="text-mdm-text/50 text-xs">${dateTime.time}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium text-mdm-text">${escapeHtml(log.car_name || '-')}</div>
                    <div class="text-xs text-mdm-text/50">${escapeHtml(log.car_code || '')}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium ${bgColor} ${textColor}">${typeLabel}</span>
                </td>
                <td class="px-4 py-3 text-sm">
                    ${log.journalist_name ? `
                        <div class="font-medium">${escapeHtml(log.journalist_name)}</div>
                        <div class="text-xs text-mdm-text/50">${escapeHtml(log.journalist_outlet || '')}</div>
                    ` : '<span class="text-mdm-text/40">-</span>'}
                </td>
                <td class="px-4 py-3 text-sm text-mdm-text/70">${log.km_reading ? log.km_reading + ' km' : '-'}</td>
                <td class="px-4 py-3 text-sm text-mdm-text/70">${log.fuel_level ? log.fuel_level + '%' : '-'}</td>
                <td class="px-4 py-3 text-sm text-mdm-text/70 max-w-xs truncate" title="${escapeHtml(log.notes || '')}">${escapeHtml(log.notes || '-')}</td>
                <td class="px-4 py-3 text-sm text-mdm-text/70">${escapeHtml(log.promoter_name || '-')}</td>
            </tr>
        `;
    }).join('');
    
    document.getElementById('logsTableContainer').classList.remove('hidden');
    document.getElementById('emptyState').classList.add('hidden');
}

// Render pagination
function renderPagination(pagination) {
    document.getElementById('totalCount').textContent = `Total: ${pagination.total} logs`;
    
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

// Change page
function changePage(delta) {
    currentPage += delta;
    loadLogs();
}

// Show/hide states
function showLoading() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('logsTableContainer').classList.add('hidden');
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('pagination').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('loadingState').classList.add('hidden');
}

function showEmpty() {
    hideLoading();
    document.getElementById('logsTableContainer').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
    document.getElementById('pagination').classList.add('hidden');
    document.getElementById('totalCount').textContent = 'Total: 0 logs';
}

// Format datetime
function formatDateTime(dateStr) {
    if (!dateStr) return { date: '-', time: '' };
    const d = new Date(dateStr);
    return {
        date: d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }),
        time: d.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' })
    };
}

// Utility
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
