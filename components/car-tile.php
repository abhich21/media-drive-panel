<?php
/**
 * MDM Car Tile Component
 * Individual car stats card with thumbnail
 * 
 * Usage:
 * renderCarTile([
 *     'id' => 1,
 *     'name' => 'BMW X5',
 *     'imageUrl' => '/assets/images/cars/bmw.jpg',
 *     'kmDriven' => 125.5,
 *     'fuelSpent' => 18,
 *     'drivesDone' => 5,
 *     'status' => 'on_drive'
 * ]);
 */

function renderCarTile($config)
{
    $id = $config['id'] ?? 0;
    $name = $config['name'] ?? 'Car';
    $imageUrl = $config['imageUrl'] ?? '';
    $kmDriven = $config['kmDriven'] ?? 0;
    $fuelSpent = $config['fuelSpent'] ?? 0;
    $drivesDone = $config['drivesDone'] ?? 0;
    $status = $config['status'] ?? 'standby';

    // Get status badge
    $badges = [
        'standby' => ['bg-mdm-tag', 'Standby'],
        'cleaning' => ['bg-yellow-100 text-yellow-800', 'Cleaning'],
        'cleaned' => ['bg-green-100 text-green-800', 'Cleaned'],
        'on_drive' => ['bg-blue-100 text-blue-800', 'On Drive'],
        'returned' => ['bg-purple-100 text-purple-800', 'Returned'],
        'hotel' => ['bg-gray-200 text-gray-700', 'Hotel'],
        'pod_lineup' => ['bg-orange-100 text-orange-800', 'Pod Line Up'],
    ];
    $badge = $badges[$status] ?? $badges['standby'];
    ?>
    <div class="mdm-card mdm-card-hover group cursor-pointer" onclick="viewCar(<?= $id ?>)">
        <!-- Thumbnail -->
        <div class="relative h-36 -mx-6 -mt-6 mb-4 bg-mdm-tag/30 rounded-t-card overflow-hidden">
            <?php if ($imageUrl): ?>
                <img src="<?= h($imageUrl) ?>" alt="<?= h($name) ?>"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-mdm-tag" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M8 17h.01M16 17h.01M3 11l1.5-5A2 2 0 016.4 4.5h11.2a2 2 0 011.9 1.5L21 11M3 11v6a1 1 0 001 1h1m16-7v6a1 1 0 01-1 1h-1M3 11h18" />
                    </svg>
                </div>
            <?php endif; ?>

            <!-- Status Badge -->
            <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-medium <?= $badge[0] ?>">
                <?= $badge[1] ?>
            </span>
        </div>

        <!-- Car Name -->
        <h4 class="font-semibold text-lg text-mdm-text mb-3"><?= h($name) ?></h4>

        <!-- Stats Grid -->
        <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-mdm-bg/50 rounded-lg py-2 px-1">
                <div class="text-lg font-bold text-mdm-text"><?= number_format($kmDriven, 1) ?></div>
                <div class="text-xs text-mdm-text/60">KM</div>
            </div>
            <div class="bg-mdm-bg/50 rounded-lg py-2 px-1">
                <div class="text-lg font-bold text-mdm-text"><?= $fuelSpent ?>%</div>
                <div class="text-xs text-mdm-text/60">Fuel</div>
            </div>
            <div class="bg-mdm-bg/50 rounded-lg py-2 px-1">
                <div class="text-lg font-bold text-mdm-text"><?= $drivesDone ?></div>
                <div class="text-xs text-mdm-text/60">Drives</div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render car grid
 * @param array $cars Array of car data
 * @param int $columns Grid columns (default 4)
 */
function renderCarGrid($cars, $columns = 4)
{
    $gridClass = "grid-cols-" . $columns;
    ?>
    <div class="grid <?= $gridClass ?> gap-card-gap">
        <?php foreach ($cars as $car): ?>
            <?php renderCarTile($car); ?>
        <?php endforeach; ?>
    </div>
    <?php
}
