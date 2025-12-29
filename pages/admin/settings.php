<?php
/**
 * MDM Admin - Settings
 * Admin settings page
 */

$pageTitle = 'Settings';
$currentPage = 'settings';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireAdmin();

include __DIR__ . '/../../components/layout.php';
?>

<!-- Settings Sections -->
<div class="space-y-6">
    <!-- Profile Settings -->
    <div class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">Profile Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Name</label>
                <input type="text" value="Admin"
                    class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Email</label>
                <input type="email" value="admin@cloudplay.com"
                    class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none">
            </div>
        </div>
        <button class="mdm-header-btn mt-4" onclick="alert('Save coming soon')">Save Changes</button>
    </div>

    <!-- System Settings -->
    <div class="mdm-card">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">System Settings</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-mdm-tag/30">
                <div>
                    <p class="font-medium text-mdm-text">Email Notifications</p>
                    <p class="text-sm text-mdm-text/60">Receive email alerts for important events</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" checked class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-mdm-tag rounded-full peer peer-checked:bg-mdm-sidebar peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                    </div>
                </label>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-mdm-tag/30">
                <div>
                    <p class="font-medium text-mdm-text">Auto-refresh Dashboard</p>
                    <p class="text-sm text-mdm-text/60">Automatically update stats every 30 seconds</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-mdm-tag rounded-full peer peer-checked:bg-mdm-sidebar peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all">
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="mdm-card border-2 border-red-200">
        <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-mdm-text">Reset All Data</p>
                <p class="text-sm text-mdm-text/60">This will permanently delete all event data</p>
            </div>
            <button class="px-4 py-2 bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-colors"
                onclick="alert('This action is disabled in demo')">
                Reset Data
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>