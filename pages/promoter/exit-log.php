<?php
/**
 * MDM Promoter - Exit Log
 * Log car exit details (journalist, km, fuel, photos)
 */

$pageTitle = 'Log Car Exit';
$currentPage = 'cars';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth('promoter');

$carId = $_GET['car_id'] ?? 1;

// TODO: Fetch from database
$car = [
    'id' => $carId,
    'name' => 'BMW X5 Sport',
    'registration' => 'MH-01-AB-1234',
    'lastKm' => 45678.5,
    'lastFuel' => 85,
];

include __DIR__ . '/../../components/layout.php';
?>

<div class="max-w-2xl mx-auto">
    <!-- Car Info -->
    <div class="mdm-card mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-mdm-tag rounded-xl flex items-center justify-center">
                <svg class="w-8 h-8 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-mdm-text"><?= h($car['name']) ?></h2>
                <p class="text-mdm-text/60"><?= h($car['registration']) ?></p>
            </div>
        </div>
    </div>

    <!-- Exit Form -->
    <form action="<?= BASE_PATH ?>/api/cars.php" method="POST" enctype="multipart/form-data" class="mdm-card"
        id="exitForm">
        <input type="hidden" name="action" value="log_exit">
        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">

        <h3 class="text-lg font-semibold text-mdm-text mb-6">Exit Details</h3>

        <!-- Journalist Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Journalist/Influencer Name *</label>
                <input type="text" name="journalist_name" required
                    class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none"
                    placeholder="Enter name">
            </div>
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Media Outlet</label>
                <input type="text" name="journalist_outlet"
                    class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none"
                    placeholder="e.g. Auto Weekly">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-mdm-text mb-2">Phone Number</label>
            <input type="tel" name="journalist_phone"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none"
                placeholder="+91 XXXXX XXXXX">
        </div>

        <!-- KM & Fuel -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">KM Reading *</label>
                <input type="number" name="km_reading" step="0.1" required value="<?= $car['lastKm'] ?>"
                    class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none"
                    placeholder="Enter KM">
            </div>
            <div>
                <label class="block text-sm font-medium text-mdm-text mb-2">Fuel Level *</label>
                <select name="fuel_level" required
                    class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none">
                    <option value="">Select fuel level</option>
                    <option value="100" <?= $car['lastFuel'] >= 90 ? 'selected' : '' ?>>Full (100%)</option>
                    <option value="75" <?= $car['lastFuel'] >= 70 && $car['lastFuel'] < 90 ? 'selected' : '' ?>>3/4 (75%)
                    </option>
                    <option value="50" <?= $car['lastFuel'] >= 40 && $car['lastFuel'] < 70 ? 'selected' : '' ?>>Half (50%)
                    </option>
                    <option value="25" <?= $car['lastFuel'] >= 10 && $car['lastFuel'] < 40 ? 'selected' : '' ?>>1/4 (25%)
                    </option>
                    <option value="10" <?= $car['lastFuel'] < 10 ? 'selected' : '' ?>>Low (10%)</option>
                </select>
            </div>
        </div>

        <!-- Exit Time -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-mdm-text mb-2">Exit Time *</label>
            <input type="datetime-local" name="exit_time" required value="<?= date('Y-m-d\TH:i') ?>"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none">
        </div>

        <!-- Photos -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-mdm-text mb-2">Car Condition Photos</label>
            <div class="border-2 border-dashed border-mdm-tag rounded-xl p-6 text-center">
                <input type="file" name="photos[]" multiple accept="image/*" class="hidden" id="photoInput">
                <label for="photoInput" class="cursor-pointer">
                    <div class="w-12 h-12 mx-auto rounded-full bg-mdm-tag flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-mdm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-mdm-text/70">Click to upload photos</span>
                    <p class="text-xs text-mdm-text/50 mt-1">Front, rear, sides, and odometer</p>
                </label>
            </div>
            <div id="photoPreview" class="grid grid-cols-4 gap-2 mt-3"></div>
        </div>

        <!-- Notes -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-mdm-text mb-2">Notes</label>
            <textarea name="notes" rows="2"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none resize-none"
                placeholder="Any observations or notes..."></textarea>
        </div>

        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit"
                class="flex-1 py-3 bg-mdm-sidebar text-white font-medium rounded-xl hover:bg-black transition-colors">
                Log Exit
            </button>
            <a href="<?= BASE_PATH ?>/pages/promoter/dashboard.php"
                class="px-6 py-3 border border-mdm-tag text-mdm-text font-medium rounded-xl hover:bg-mdm-bg transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Photo preview
    document.getElementById('photoInput').addEventListener('change', function (e) {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';

        Array.from(e.target.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-20 object-cover rounded-lg';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });

    // Form submission
    document.getElementById('exitForm').addEventListener('submit', function (e) {
        e.preventDefault();
        // TODO: Submit via API
        alert('Exit logged successfully!');
        window.location = '<?= BASE_PATH ?>/pages/promoter/dashboard.php';
    });
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>