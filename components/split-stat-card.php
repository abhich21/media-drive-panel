<?php
/**
 * MDM Split Stat Card Component
 * Wide card with metrics on left + car image on right
 * Matches the "Total Active/Inactive Cars" design from mockup
 * 
 * Usage:
 * renderSplitStatCard([
 *     'title' => 'Total Active/ Inactive Cars',
 *     'active' => 23,
 *     'total' => 50,
 *     'imageUrl' => '/assets/images/car-top-view.png'
 * ]);
 */

function renderSplitStatCard($config)
{
    $title = $config['title'] ?? 'Stat';
    $imageUrl = $config['imageUrl'] ?? '';
    // Support both old 'value' styled input or split inputs
    $active = $config['active'] ?? '0';
    $total = $config['total'] ?? '0';

    // If generic value passed (legacy support)
    if (isset($config['value']) && !isset($config['active'])) {
        $parts = explode('/', $config['value']);
        $active = $parts[0] ?? $config['value'];
        $total = $parts[1] ?? '';
    }
    ?>
    <div class="bg-white rounded-[24px] relative pt-12 pb-6 px-8 mt-6 shadow-sm min-h-[200px] overflow-visible">
        <!-- Floating Pill Layout -->
        <div class="absolute -top-5 left-8 bg-[#E8E6E1] px-6 py-2 rounded-full border-[6px] border-[#E6E7E2] z-20">
            <span class="font-extrabold text-black text-sm tracking-wide uppercase"><?= h($title) ?></span>
        </div>

        <div class="flex h-full relative z-10 items-end pb-2">
            <!-- Left Content -->
            <div class="relative z-10">
                <div class="text-[5.5rem] font-bold text-black leading-[0.8] tracking-tighter">
                    <?= h($active) ?><span class="text-4xl text-black/40 font-semibold ml-1">/<?= h($total) ?></span>
                </div>
            </div>

            <!-- Right Car Image -->
            <?php if ($imageUrl): ?>
                <div class="absolute -right-12 top-1/2 -translate-y-1/2 w-[320px] pointer-events-none z-0">
                    <!-- Motion Lines (CSS) -->
                    <div class="absolute right-[20px] top-[40%] w-[180px] h-[16px] bg-[#E8E6E1] rounded-full opacity-60"></div>
                    <div class="absolute right-[60px] top-[55%] w-[120px] h-[16px] bg-[#E8E6E1] rounded-full opacity-60"></div>

                    <img src="<?= h($imageUrl) ?>" alt="Car"
                        class="relative w-full object-contain transform scale-125 translate-x-10">
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
