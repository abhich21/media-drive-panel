<?php
/**
 * MDM Status Card Component
 * For maintenance status row (Cleaning, Cleaned, Pod Line Up, Standby, Back to Hotel)
 * 
 * Usage:
 * renderStatusCard([
 *     'icon' => 'cleaning',
 *     'label' => 'Cleaning Cars',
 *     'value' => 4
 * ]);
 */

function renderStatusCard($config)
{
    $icon = $config['icon'] ?? 'default';
    $label = $config['label'] ?? 'Status';
    $value = $config['value'] ?? 0;

    // Icon SVG paths
    $icons = [
        'cleaning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>',
        'cleaned' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'pod_lineup' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16M4 18h16"/>',
        'standby' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'hotel' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m0 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'default' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];

    $iconPath = $icons[$icon] ?? $icons['default'];
    ?>
    <div class="flex flex-col items-center justify-center p-4">
        <!-- Icon Circle -->
        <div class="w-20 h-20 rounded-full bg-[#E8E6E1] flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-[#909090]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <?= $iconPath ?>
            </svg>
        </div>

        <!-- Label -->
        <span class="text-sm font-semibold text-black/80 text-center mb-2 tracking-wide"><?= h($label) ?></span>

        <!-- Value -->
        <span class="text-[2.5rem] font-bold text-black leading-none"><?= h($value) ?></span>
    </div>
    <?php
}

/**
 * Render full maintenance status row
 * @param array $stats [cleaning, cleaned, pod_lineup, standby, hotel]
 */
function renderMaintenanceStatusRow($stats)
{
    // Legacy support wrapper if called directly
    ?>
    <div class="bg-white rounded-[24px] relative pt-16 pb-12 px-8 mt-12 shadow-sm">
        <!-- Floating Pill -->
        <div class="absolute -top-5 left-8 bg-[#E8E6E1] px-8 py-3 rounded-full border-[6px] border-[#E6E7E2] z-10">
            <span class="font-extrabold text-black text-base tracking-wide uppercase">Maintenance Status</span>
        </div>

        <!-- Status Grid -->
        <div class="grid grid-cols-4 gap-4">
            <?php
            renderStatusCard(['icon' => 'cleaning', 'label' => 'Under Cleaning', 'value' => $stats['cleaning'] ?? 0]);
            renderStatusCard(['icon' => 'cleaned', 'label' => 'Cleaned Cars', 'value' => $stats['cleaned'] ?? 0]);
            renderStatusCard(['icon' => 'pod_lineup', 'label' => 'POD Line Up', 'value' => $stats['pod_lineup'] ?? 0]);
            renderStatusCard(['icon' => 'standby', 'label' => 'On Drive', 'value' => 5]);
            ?>
        </div>
    </div>
    <?php
}
