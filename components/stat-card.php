<?php
/**
 * MDM Stat Card Component
 * Reusable stat card with title, value, and optional unit
 * 
 * Usage: 
 * renderStatCard([
 *     'title' => 'Total Distance Covered',
 *     'value' => '679.5',
 *     'unit' => 'km',
 *     'subtext' => 'Since event start'
 * ]);
 */

function renderStatCard($config)
{
    $title = $config['title'] ?? 'Stat';
    $value = $config['value'] ?? '0';
    $unit = $config['unit'] ?? '';
    $subtext = $config['subtext'] ?? '';
    $subtitleTitle = $config['subtitleTitle'] ?? '';
    $subtitleValue = $config['subtitleValue'] ?? '';
    ?>
    <div class="bg-white rounded-[24px] relative pt-12 pb-8 px-8 mt-6 shadow-sm hover:shadow-md transition-shadow">
        <!-- Floating Pill Layout -->
        <div class="absolute -top-5 left-8 bg-[#E8E6E1] px-6 py-2 rounded-full border-[6px] border-[#E6E7E2] z-10">
            <span class="font-extrabold text-black text-sm tracking-wide uppercase"><?= h($title) ?></span>
        </div>

        <!-- Main Value -->
        <div class="flex items-end gap-3 mt-2">
            <span class="text-[5.5rem] font-bold text-black leading-[0.9] tracking-tighter"><?= h($value) ?></span>
            <div class="flex flex-col mb-4">
                <span class="text-3xl font-bold text-black"><?= h($unit) ?></span>
            </div>
            <?php if ($subtext): ?>
                <span class="mb-5 ml-2 text-sm font-bold text-[#4CAF50] whitespace-nowrap"><?= h($subtext) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($subtitleTitle && $subtitleValue): ?>
            <!-- Subtitle Section -->
            <div class="mt-8">
                <div class="inline-block bg-[#F2F2F0] px-4 py-2 rounded-lg">
                    <span class="text-xs font-bold text-black uppercase tracking-wide"><?= h($subtitleTitle) ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
