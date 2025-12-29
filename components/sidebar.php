<?php
/**
 * MDM Sidebar Component
 * Dark vertical navigation - Collapsible on both Desktop & Mobile
 */

$currentPage = $currentPage ?? 'dashboard';
$userRole = $_SESSION['user_role'] ?? 'client';

// Navigation items based on role
$clientNav = [
    ['id' => 'dashboard', 'label' => 'Overall Car Stats', 'icon' => 'car', 'url' => '/pages/client/dashboard.php'],
    ['id' => 'car-stats', 'label' => 'Individual Car Stats', 'icon' => 'chart', 'url' => '/pages/client/car-stats.php'],
    ['id' => 'attendance', 'label' => 'GPS Tracking', 'icon' => 'location', 'url' => '/pages/client/attendance.php'],
    ['id' => 'events', 'label' => 'Event Details', 'icon' => 'calendar', 'url' => '/pages/client/events.php'],
    ['id' => 'insights', 'label' => 'Insights', 'icon' => 'eye', 'url' => '/pages/client/insights.php'],
];

$adminNav = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'url' => '/pages/admin/dashboard.php'],
    ['id' => 'cars', 'label' => 'Manage Cars', 'icon' => 'car', 'url' => '/pages/admin/cars.php'],
    ['id' => 'promoters', 'label' => 'Manage Promoters', 'icon' => 'users', 'url' => '/pages/admin/promoters.php'],
    ['id' => 'events', 'label' => 'Events', 'icon' => 'calendar', 'url' => '/pages/admin/events.php'],
    ['id' => 'car-logs', 'label' => 'Car Logs', 'icon' => 'eye', 'url' => '/pages/admin/car-logs.php'],
    ['id' => 'analytics', 'label' => 'Analytics', 'icon' => 'chart', 'url' => '/pages/admin/analytics.php'],
    ['id' => 'settings', 'label' => 'Settings', 'icon' => 'settings', 'url' => '/pages/admin/settings.php'],
];

$promoterNav = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'url' => '/pages/promoter/dashboard.php'],
    ['id' => 'cars', 'label' => 'Car Status Update', 'icon' => 'car', 'url' => '/pages/promoter/car-status.php'],
    ['id' => 'feedback', 'label' => 'Feedback', 'icon' => 'check', 'url' => '/pages/promoter/feedback.php'],
    ['id' => 'post-drive', 'label' => 'Post Drive Details', 'icon' => 'arrow-left', 'url' => '/pages/promoter/post-drive.php'],
    ['id' => 'drive-logs', 'label' => 'Drive Logs', 'icon' => 'list', 'url' => '/pages/promoter/drive-logs.php'],
];

$cleaningNav = [
    ['id' => 'dashboard', 'label' => 'Cleaning Dashboard', 'icon' => 'home', 'url' => '/pages/cleaning/dashboard.php'],
];

$navItems = match ($userRole) {
    'superadmin' => $adminNav,
    'client' => $clientNav,
    'promoter' => $promoterNav,
    'cleaning_staff' => $cleaningNav,
    default => $clientNav,
};

function getSidebarIconSvg($icon)
{
    $paths = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'car' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'location' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'eye' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'arrow-left' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>',
        'list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
    ];
    return $paths[$icon] ?? $paths['home'];
}
?>

<style>
    /* Sidebar Styles */
    .mdm-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 240px;
        height: 100vh;
        background-color: #000000 !important;
        display: flex;
        flex-direction: column;
        z-index: 100;
        border-top-right-radius: 40px;
        transition: width 0.3s ease, transform 0.3s ease;
    }

    /* Collapsed state on desktop */
    .mdm-sidebar.collapsed {
        width: 72px;
        border-top-right-radius: 24px;
    }

    .mdm-sidebar.collapsed .mdm-sidebar-logo span,
    .mdm-sidebar.collapsed .mdm-nav-link span {
        display: none;
    }

    .mdm-sidebar.collapsed .mdm-sidebar-logo {
        justify-content: center;
        padding: 24px 16px;
    }

    .mdm-sidebar.collapsed .mdm-nav-link {
        justify-content: center;
        padding: 12px;
    }

    .mdm-sidebar.collapsed .mdm-collapse-btn {
        right: 50%;
        transform: translateX(50%) rotate(180deg);
    }

    .mdm-sidebar-logo {
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #FFFFFF;
    }

    .mdm-sidebar-logo span {
        font-weight: 700;
        font-size: 16px;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .mdm-sidebar-nav {
        flex: 1;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        overflow-y: auto;
    }

    .mdm-nav-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        color: #FFFFFF;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 14px;
    }

    .mdm-nav-link:hover {
        background-color: rgba(255, 255, 255, 0.08);
    }

    .mdm-nav-link.active {
        color: #000000;
        background-color: #D9D9D6;
        font-weight: 600;
    }

    .mdm-nav-link svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .mdm-nav-link span {
        white-space: nowrap;
    }

    /* Collapse button (desktop) */
    .mdm-collapse-btn {
        position: absolute;
        bottom: 24px;
        right: 16px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        transition: all 0.3s;
    }

    .mdm-collapse-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .mdm-collapse-btn svg {
        width: 16px;
        height: 16px;
        transition: transform 0.3s;
    }

    /* Close button for mobile */
    .mdm-sidebar-close {
        display: none;
        position: absolute;
        top: 20px;
        right: 20px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
    }

    .mdm-sidebar-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Overlay for mobile */
    .mdm-sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99;
    }

    .mdm-sidebar-overlay.active {
        display: block;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .mdm-sidebar {
            transform: translateX(-100%);
            width: 280px;
            border-radius: 0;
        }

        .mdm-sidebar.open {
            transform: translateX(0);
        }

        .mdm-sidebar.collapsed {
            width: 280px;
        }

        .mdm-sidebar-close {
            display: flex;
        }

        .mdm-collapse-btn {
            display: none;
        }

        .mdm-sidebar.collapsed .mdm-sidebar-logo span,
        .mdm-sidebar.collapsed .mdm-nav-link span {
            display: inline;
        }

        .mdm-sidebar.collapsed .mdm-sidebar-logo,
        .mdm-sidebar.collapsed .mdm-nav-link {
            justify-content: flex-start;
            padding: 12px 16px;
        }
    }
</style>

<!-- Sidebar Overlay -->
<div class="mdm-sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="mdm-sidebar" id="mdmSidebar">
    <!-- Close Button (Mobile) -->
    <button class="mdm-sidebar-close" onclick="closeSidebar()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12" />
        </svg>
    </button>

    <!-- Collapse Button (Desktop) -->
    <button class="mdm-collapse-btn" onclick="toggleSidebarCollapse()" title="Collapse sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    <!-- Logo Area -->
    <div class="mdm-sidebar-logo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M2 17L12 22L22 17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M2 12L12 17L22 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span><?= h($clientLogo ?? 'Client Logo') ?></span>
    </div>

    <!-- Navigation -->
    <nav class="mdm-sidebar-nav">
        <?php foreach ($navItems as $item): ?>
            <a href="<?= BASE_PATH . $item['url'] ?>"
                class="mdm-nav-link <?= $currentPage === $item['id'] ? 'active' : '' ?>">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?= getSidebarIconSvg($item['icon']) ?>
                </svg>
                <span><?= h($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
    // Mobile sidebar functions
    function openSidebar() {
        document.getElementById('mdmSidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        document.getElementById('mdmSidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Desktop collapse function
    function toggleSidebarCollapse() {
        const sidebar = document.getElementById('mdmSidebar');
        const main = document.querySelector('.mdm-main');
        const header = document.querySelector('.mdm-header');
        const legend = document.querySelector('.status-legend-footer');

        sidebar.classList.toggle('collapsed');

        // Adjust main content margin
        if (sidebar.classList.contains('collapsed')) {
            if (main) main.style.marginLeft = '72px';
            if (header) header.style.left = '72px';
            if (legend) legend.style.left = '72px';
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            if (main) main.style.marginLeft = '240px';
            if (header) header.style.left = '240px';
            if (legend) legend.style.left = '240px';
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    }

    // Restore sidebar state on page load
    document.addEventListener('DOMContentLoaded', function () {
        handleViewportChange();
    });

    // Handle viewport changes (resize)
    function handleViewportChange() {
        const sidebar = document.getElementById('mdmSidebar');
        const main = document.querySelector('.mdm-main');
        const header = document.querySelector('.mdm-header');
        const legend = document.querySelector('.status-legend-footer');

        if (window.innerWidth <= 768) {
            // Mobile view - reset all inline styles
            if (main) main.style.marginLeft = '';
            if (header) header.style.left = '';
            if (legend) legend.style.left = '';
            sidebar.classList.remove('collapsed');
        } else {
            // Desktop view - restore collapsed state if saved
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                if (main) main.style.marginLeft = '72px';
                if (header) header.style.left = '72px';
                if (legend) legend.style.left = '72px';
            } else {
                sidebar.classList.remove('collapsed');
                if (main) main.style.marginLeft = '240px';
                if (header) header.style.left = '240px';
                if (legend) legend.style.left = '240px';
            }
        }
    }

    // Listen for window resize
    let resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleViewportChange, 100);
    });
</script>