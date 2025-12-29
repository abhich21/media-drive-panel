<?php
/**
 * MDM Admin - Analytics
 * Admin analytics dashboard with event filter and export
 */

$pageTitle = 'Analytics';
$currentPage = 'analytics';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireAdmin();

include __DIR__ . '/../../components/layout.php';
?>

<!-- Filter Bar -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <select id="eventFilter" class="px-4 py-2 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none text-sm">
                <option value="all">All Events</option>
            </select>
            <select id="yearFilter" class="px-4 py-2 rounded-xl border border-mdm-tag focus:border-mdm-sidebar focus:outline-none text-sm">
            </select>
        </div>
        <button id="downloadBtn" onclick="downloadEventData()" class="mdm-header-btn hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Event Data
        </button>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="mdm-card p-8 text-center">
    <div class="animate-spin w-8 h-8 border-4 border-mdm-sidebar border-t-transparent rounded-full mx-auto mb-4"></div>
    <p class="text-mdm-text/60">Loading analytics...</p>
</div>

<!-- Stats Cards -->
<div id="statsContainer" class="hidden">
    <div id="statsGrid" class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div id="eventsCard" class="mdm-card text-center">
            <div id="statEvents" class="text-3xl font-bold text-mdm-text">-</div>
            <div class="text-sm text-mdm-text/60">Total Events</div>
        </div>
        <div class="mdm-card text-center">
            <div id="statCars" class="text-3xl font-bold text-mdm-text">-</div>
            <div class="text-sm text-mdm-text/60">Total Cars</div>
        </div>
        <div class="mdm-card text-center">
            <div id="statPromoters" class="text-3xl font-bold text-mdm-text">-</div>
            <div class="text-sm text-mdm-text/60">Promoters</div>
        </div>
        <div class="mdm-card text-center">
            <div id="statDrives" class="text-3xl font-bold text-mdm-text">-</div>
            <div class="text-sm text-mdm-text/60">Total Drives</div>
        </div>
        <div class="mdm-card text-center">
            <div id="statKm" class="text-3xl font-bold text-mdm-text">-</div>
            <div class="text-sm text-mdm-text/60">Total KM</div>
        </div>
    </div>

    <!-- Events per Month Chart (only visible when All selected) -->
    <div id="chartContainer" class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">Events by Month</h3>
        <canvas id="eventsChart" height="200"></canvas>
    </div>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>

<script>
const basePath = '<?= BASE_PATH ?>';
let currentEventId = localStorage.getItem('analytics_eventId') || 'all';
let currentYear = localStorage.getItem('analytics_year') || new Date().getFullYear();

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    populateYearDropdown();
    loadEvents();
    loadAnalytics();
    
    // Restore download button state
    document.getElementById('downloadBtn').classList.toggle('hidden', currentEventId === 'all');
    
    document.getElementById('eventFilter').addEventListener('change', function() {
        currentEventId = this.value;
        localStorage.setItem('analytics_eventId', currentEventId);
        loadAnalytics();
        
        // Show/hide download button
        document.getElementById('downloadBtn').classList.toggle('hidden', currentEventId === 'all');
    });
    
    document.getElementById('yearFilter').addEventListener('change', function() {
        currentYear = this.value;
        localStorage.setItem('analytics_year', currentYear);
        loadAnalytics();
    });
});

// Populate year dropdown (last 5 years + next year)
function populateYearDropdown() {
    const yearFilter = document.getElementById('yearFilter');
    const currentYearNum = new Date().getFullYear();
    const savedYear = parseInt(currentYear);
    
    for (let year = currentYearNum + 1; year >= currentYearNum - 4; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === savedYear) option.selected = true;
        yearFilter.appendChild(option);
    }
}

// Load events for dropdown
async function loadEvents() {
    try {
        const response = await fetch(`${basePath}/api/analytics.php?action=get_events`);
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

// Load analytics data
async function loadAnalytics() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('statsContainer').classList.add('hidden');
    
    try {
        // Load stats
        let statsUrl = `${basePath}/api/analytics.php?action=stats&year=${currentYear}`;
        if (currentEventId !== 'all') statsUrl += `&event_id=${currentEventId}`;
        
        const statsResponse = await fetch(statsUrl);
        const statsData = await statsResponse.json();
        
        if (statsData.success) {
            document.getElementById('statEvents').textContent = statsData.data.totalEvents;
            document.getElementById('statCars').textContent = statsData.data.totalCars;
            document.getElementById('statPromoters').textContent = statsData.data.totalPromoters;
            document.getElementById('statDrives').textContent = formatNumber(statsData.data.totalDrives);
            document.getElementById('statKm').textContent = formatNumber(Math.round(statsData.data.totalKm));
            
            // Hide events card when specific event selected
            const eventsCard = document.getElementById('eventsCard');
            const statsGrid = document.getElementById('statsGrid');
            if (currentEventId === 'all') {
                eventsCard.classList.remove('hidden');
                statsGrid.classList.remove('lg:grid-cols-4');
                statsGrid.classList.add('lg:grid-cols-5');
            } else {
                eventsCard.classList.add('hidden');
                statsGrid.classList.remove('lg:grid-cols-5');
                statsGrid.classList.add('lg:grid-cols-4');
            }
        }
        
        // Show/hide chart based on filter
        const chartContainer = document.getElementById('chartContainer');
        if (currentEventId === 'all') {
            chartContainer.classList.remove('hidden');
            renderEventsChart();
        } else {
            chartContainer.classList.add('hidden');
        }
        
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('statsContainer').classList.remove('hidden');
        
    } catch (error) {
        console.error('Failed to load analytics:', error);
        document.getElementById('loadingState').innerHTML = '<p class="text-red-500">Failed to load analytics</p>';
    }
}

// Render events by month chart
let eventsChart = null;
async function renderEventsChart() {
    const ctx = document.getElementById('eventsChart');
    
    // Destroy existing chart if any
    if (eventsChart) {
        eventsChart.destroy();
    }
    
    try {
        const response = await fetch(`${basePath}/api/analytics.php?action=events_by_month&year=${currentYear}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            eventsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: result.data.map(d => d.month),
                    datasets: [{
                        label: 'Events',
                        data: result.data.map(d => d.count),
                        backgroundColor: '#C7C1B4',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }
    } catch (error) {
        console.error('Failed to load chart data:', error);
    }
}

// Download event data
async function downloadEventData() {
    if (currentEventId === 'all') {
        alert('Please select a specific event to download data');
        return;
    }
    
    const btn = document.getElementById('downloadBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Downloading...';
    
    try {
        const response = await fetch(`${basePath}/api/analytics.php?action=export&event_id=${currentEventId}`);
        const data = await response.json();
        
        if (data.success) {
            // Create and trigger download
            const blob = new Blob([data.data.content], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data.data.filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } else {
            alert(data.message || 'Failed to export data');
        }
    } catch (error) {
        alert('An error occurred while exporting');
    }
    
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> Download Event Data';
}

// Utilities
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>