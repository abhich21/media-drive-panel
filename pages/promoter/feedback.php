<?php
/**
 * MDM Promoter - Feedback Form
 * Submit feedback after drive completion
 */

$pageTitle = 'Submit Feedback';
$currentPage = 'feedback';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth('promoter');

// TODO: Fetch recent completed drives
$recentDrives = [
    ['id' => 1, 'car' => 'BMW X5', 'journalist' => 'John Smith', 'time' => '2 hours ago'],
    ['id' => 2, 'car' => 'Mercedes GLC', 'journalist' => 'Sarah Johnson', 'time' => '4 hours ago'],
];

include __DIR__ . '/../../components/layout.php';
?>

<div class="max-w-2xl mx-auto">
    <!-- Select Drive -->
    <div class="mdm-card mb-6">
        <h3 class="text-lg font-semibold text-mdm-text mb-4">Select Drive</h3>
        <div class="space-y-3">
            <?php foreach ($recentDrives as $drive): ?>
                <label class="block cursor-pointer">
                    <input type="radio" name="drive_id" value="<?= $drive['id'] ?>" class="hidden peer">
                    <div
                        class="p-4 rounded-xl border-2 border-mdm-tag hover:border-mdm-accent peer-checked:border-mdm-sidebar peer-checked:bg-mdm-bg/50 transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-mdm-text"><?= h($drive['car']) ?></span>
                                <span class="text-mdm-text/60"> • <?= h($drive['journalist']) ?></span>
                            </div>
                            <span class="text-sm text-mdm-text/50"><?= h($drive['time']) ?></span>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Feedback Form -->
    <form action="<?= BASE_PATH ?>/api/feedback.php" method="POST" class="mdm-card" id="feedbackForm">
        <input type="hidden" name="action" value="submit_feedback">

        <h3 class="text-lg font-semibold text-mdm-text mb-6">Feedback Details</h3>

        <!-- Rating -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-mdm-text mb-3">Overall Experience *</label>
            <div class="flex gap-2" id="ratingStars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" data-rating="<?= $i ?>"
                        class="rating-star w-12 h-12 rounded-xl border-2 border-mdm-tag hover:border-yellow-400 transition-all flex items-center justify-center text-2xl">
                        ⭐
                    </button>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" required>
        </div>

        <!-- Experience Notes -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-mdm-text mb-2">Experience Summary</label>
            <textarea name="experience" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none resize-none"
                placeholder="How was the overall drive experience?"></textarea>
        </div>

        <!-- Strong Points -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-mdm-text mb-2">Strong Points</label>
            <textarea name="strong_points" rows="2"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none resize-none"
                placeholder="What went well?"></textarea>
        </div>

        <!-- Weak Points -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-mdm-text mb-2">Areas for Improvement</label>
            <textarea name="weak_points" rows="2"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none resize-none"
                placeholder="Any issues or suggestions?"></textarea>
        </div>

        <!-- Major Concerns -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-mdm-text mb-2">Major Concerns (if any)</label>
            <textarea name="concerns" rows="2"
                class="w-full px-4 py-3 rounded-xl border border-mdm-tag bg-white focus:border-mdm-sidebar focus:outline-none resize-none"
                placeholder="Any critical issues to report?"></textarea>
        </div>

        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit"
                class="flex-1 py-3 bg-mdm-sidebar text-white font-medium rounded-xl hover:bg-black transition-colors">
                Submit Feedback
            </button>
            <a href="<?= BASE_PATH ?>/pages/promoter/dashboard.php"
                class="px-6 py-3 border border-mdm-tag text-mdm-text font-medium rounded-xl hover:bg-mdm-bg transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Rating stars
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const rating = this.dataset.rating;
            ratingInput.value = rating;

            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('bg-yellow-100', 'border-yellow-400');
                    s.classList.remove('border-mdm-tag');
                } else {
                    s.classList.remove('bg-yellow-100', 'border-yellow-400');
                    s.classList.add('border-mdm-tag');
                }
            });
        });
    });

    // Form submission
    document.getElementById('feedbackForm').addEventListener('submit', function (e) {
        e.preventDefault();

        if (!ratingInput.value) {
            alert('Please select a rating');
            return;
        }

        // TODO: Submit via API
        alert('Feedback submitted successfully!');
        window.location = '<?= BASE_PATH ?>/pages/promoter/dashboard.php';
    });
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>