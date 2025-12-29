<?php
/**
 * MDM Promoter - Feedback Form
 * Capture structured qualitative and quantitative feedback after test drive
 * 
 * WORKFLOW (per spec):
 * - Only show cars with status = "Returned"
 * - When car is selected, auto-populate PR Firm and Influencer from the exit log
 * - Influencer should NOT have to wait - fast form
 */

$pageTitle = 'Feedback Form';
$currentPage = 'feedback';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Get cars that are in "returned" status (ready for feedback)
$returnedCars = dbQuery("
    SELECT c.id, c.car_code, c.name,
           cl.influencer_id, cl.pr_firm_id, cl.journalist_name,
           i.name as influencer_name,
           pf.name as pr_firm_name,
           cl.exit_time, cl.return_time
    FROM cars c
    LEFT JOIN car_logs cl ON cl.car_id = c.id AND cl.log_type = 'exit'
    LEFT JOIN influencers i ON i.id = cl.influencer_id
    LEFT JOIN pr_firms pf ON pf.id = cl.pr_firm_id
    WHERE c.status = 'returned'
    ORDER BY cl.return_time DESC, c.car_code ASC
");

// Most recent returned car (auto-select)
$mostRecentCar = !empty($returnedCars) ? $returnedCars[0] : null;

include __DIR__ . '/../../components/layout.php';
?>

<style>
    .feedback-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .back-nav {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 24px;
        text-decoration: none;
        transition: color 0.2s;
    }

    .back-nav:hover {
        color: #1a1a1a;
    }

    .context-row {
        display: flex;
        gap: 24px;
        margin-bottom: 32px;
        align-items: flex-end;
        flex-wrap: wrap;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .form-select,
    .form-input {
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        background: white;
        color: #1a1a1a;
    }

    .form-select:focus,
    .form-input:focus {
        outline: none;
        border-color: #6b7280;
        box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
    }

    .form-select:disabled,
    .form-input:disabled {
        background: #f3f4f6;
        color: #6b7280;
    }

    .feedback-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #f3f4f6;
        padding: 12px 20px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    .info-badge .label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
    }

    .info-badge .value {
        font-weight: 700;
        color: #1a1a1a;
    }

    .instruction-text {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 20px;
    }

    .ratings-container {
        display: grid;
        grid-template-columns: 1fr 1px 1fr;
        gap: 32px;
        margin-bottom: 24px;
    }

    .divider {
        background: #e5e7eb;
        width: 1px;
    }

    .rating-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .rating-label {
        font-size: 0.95rem;
        color: #1a1a1a;
        font-weight: 500;
    }

    .stars {
        display: flex;
        gap: 4px;
    }

    .star {
        cursor: pointer;
        font-size: 24px;
        color: #d1d5db;
        transition: color 0.15s, transform 0.1s;
        user-select: none;
    }

    .star.filled {
        color: #f59e0b;
    }

    .star:hover {
        color: #fbbf24;
        transform: scale(1.1);
    }

    .comments-section {
        margin-top: 16px;
    }

    .comments-label {
        font-size: 0.95rem;
        color: #1a1a1a;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .comments-textarea {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        resize: none;
        font-family: inherit;
    }

    .comments-textarea:focus {
        outline: none;
        border-color: #6b7280;
        box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.1);
    }

    .action-row {
        display: flex;
        justify-content: flex-end;
        margin-top: 24px;
    }

    .btn-done {
        background: #16a34a;
        color: white;
        padding: 12px 40px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-done:hover {
        background: #15803d;
    }

    .btn-done:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-state svg {
        width: 64px;
        height: 64px;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .ratings-container {
            grid-template-columns: 1fr;
        }

        .divider {
            display: none;
        }
    }
</style>

<div class="feedback-page">
    <a href="dashboard.php" class="back-nav">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>

    <?php if (empty($returnedCars)): ?>
        <div class="feedback-card empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3>No Cars Ready for Feedback</h3>
            <p>Cars must be in "Returned" status to collect feedback.</p>
        </div>
    <?php else: ?>

        <!-- Context Row: Car Selection -->
        <div class="context-row">
            <div class="form-group" style="width: 180px;">
                <label class="form-label">Car Code</label>
                <select id="carSelect" class="form-select" required>
                    <?php foreach ($returnedCars as $car): ?>
                        <option value="<?= $car['id'] ?>" data-influencer-id="<?= $car['influencer_id'] ?? '' ?>"
                            data-influencer-name="<?= h($car['influencer_name'] ?? $car['journalist_name'] ?? '') ?>"
                            data-pr-firm-id="<?= $car['pr_firm_id'] ?? '' ?>"
                            data-pr-firm-name="<?= h($car['pr_firm_name'] ?? '') ?>" <?= ($mostRecentCar && $mostRecentCar['id'] == $car['id']) ? 'selected' : '' ?>>
                            <?= h($car['car_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Auto-filled PR Firm (locked) -->
            <div class="info-badge">
                <div>
                    <div class="label">Media Outlet</div>
                    <div class="value" id="prFirmDisplay"><?= h($mostRecentCar['pr_firm_name'] ?? 'N/A') ?></div>
                </div>
            </div>

            <!-- Auto-filled Influencer Name (locked) -->
            <div class="info-badge">
                <div>
                    <div class="label">Influencer</div>
                    <div class="value" id="influencerDisplay">
                        <?= h($mostRecentCar['influencer_name'] ?? $mostRecentCar['journalist_name'] ?? 'N/A') ?></div>
                </div>
            </div>
        </div>

        <!-- Feedback Form Card -->
        <form id="feedbackForm" class="feedback-card">
            <!-- Hidden fields for submission -->
            <input type="hidden" name="car_id" id="carIdInput" value="<?= $mostRecentCar['id'] ?? '' ?>">
            <input type="hidden" name="pr_firm_id" id="prFirmIdInput" value="<?= $mostRecentCar['pr_firm_id'] ?? '' ?>">
            <input type="hidden" name="influencer_id" id="influencerIdInput"
                value="<?= $mostRecentCar['influencer_id'] ?? '' ?>">

            <!-- Instruction -->
            <div class="instruction-text">Please rate the drive out of 5</div>

            <!-- Ratings Grid -->
            <div class="ratings-container">
                <!-- Left Column -->
                <div class="left-column">
                    <div class="rating-item">
                        <span class="rating-label">Handling</span>
                        <div class="stars" data-category="handling">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-item">
                        <span class="rating-label">Comfort</span>
                        <div class="stars" data-category="comfort">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-item">
                        <span class="rating-label">Performance</span>
                        <div class="stars" data-category="performance">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-item">
                        <span class="rating-label">NVH</span>
                        <div class="stars" data-category="nvh">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                </div>

                <!-- Vertical Divider -->
                <div class="divider"></div>

                <!-- Right Column -->
                <div class="right-column">
                    <div class="rating-item">
                        <span class="rating-label">Features</span>
                        <div class="stars" data-category="features">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-item">
                        <span class="rating-label">Appearance</span>
                        <div class="stars" data-category="appearance">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>
                    <div class="rating-item">
                        <span class="rating-label">Overall</span>
                        <div class="stars" data-category="overall">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                    </div>

                    <!-- Additional Comments -->
                    <div class="comments-section">
                        <div class="comments-label">Additional Comments:</div>
                        <textarea name="comments" class="comments-textarea" rows="4"
                            placeholder="Anything more to add..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs for ratings -->
            <input type="hidden" name="handling" id="rating_handling">
            <input type="hidden" name="comfort" id="rating_comfort">
            <input type="hidden" name="performance" id="rating_performance">
            <input type="hidden" name="nvh" id="rating_nvh">
            <input type="hidden" name="features" id="rating_features">
            <input type="hidden" name="appearance" id="rating_appearance">
            <input type="hidden" name="overall" id="rating_overall">

            <!-- Action -->
            <div class="action-row">
                <button type="submit" class="btn-done">Done</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // When car selection changes, update the PR Firm and Influencer displays
    document.getElementById('carSelect')?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];

        // Update hidden inputs
        document.getElementById('carIdInput').value = this.value;
        document.getElementById('prFirmIdInput').value = selected.dataset.prFirmId || '';
        document.getElementById('influencerIdInput').value = selected.dataset.influencerId || '';

        // Update display badges
        document.getElementById('prFirmDisplay').textContent = selected.dataset.prFirmName || 'N/A';
        document.getElementById('influencerDisplay').textContent = selected.dataset.influencerName || 'N/A';
    });

    // Star rating interaction
    const ratings = {};

    document.querySelectorAll('.stars').forEach(starsContainer => {
        const category = starsContainer.dataset.category;
        const stars = starsContainer.querySelectorAll('.star');

        stars.forEach((star, index) => {
            // Hover effect
            star.addEventListener('mouseenter', () => {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('filled');
                    } else {
                        s.classList.remove('filled');
                    }
                });
            });

            // Click to lock rating
            star.addEventListener('click', () => {
                const value = parseInt(star.dataset.value);
                ratings[category] = value;
                document.getElementById(`rating_${category}`).value = value;

                // Lock the visual state
                stars.forEach((s, i) => {
                    if (i < value) {
                        s.classList.add('filled');
                    } else {
                        s.classList.remove('filled');
                    }
                });
            });
        });

        // Reset on mouse leave if not clicked
        starsContainer.addEventListener('mouseleave', () => {
            const currentRating = ratings[category] || 0;
            stars.forEach((s, i) => {
                if (i < currentRating) {
                    s.classList.add('filled');
                } else {
                    s.classList.remove('filled');
                }
            });
        });
    });

    // Form submission
    document.getElementById('feedbackForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const carId = document.getElementById('carIdInput').value;
        const prFirmId = document.getElementById('prFirmIdInput').value;
        const influencerId = document.getElementById('influencerIdInput').value;

        if (!carId) {
            showToast('Please select a car', 'error');
            return;
        }

        // Validate all ratings
        const requiredCategories = ['handling', 'comfort', 'performance', 'nvh', 'features', 'appearance', 'overall'];
        for (const cat of requiredCategories) {
            if (!ratings[cat]) {
                showToast(`Please rate ${cat.charAt(0).toUpperCase() + cat.slice(1)}`, 'error');
                return;
            }
        }

        const formData = new FormData(this);
        formData.append('action', 'submit_feedback');

        const btn = this.querySelector('.btn-done');
        btn.disabled = true;
        btn.textContent = 'Submitting...';

        try {
            const res = await fetch('<?= BASE_PATH ?>/api/feedback.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();

            if (json.success) {
                showToast('Feedback submitted successfully!', 'success');
                setTimeout(() => window.location.href = 'dashboard.php', 1500);
            } else {
                showToast(json.message || 'Error submitting feedback', 'error');
                btn.disabled = false;
                btn.textContent = 'Done';
            }
        } catch (e) {
            console.error(e);
            showToast('Failed to submit. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Done';
        }
    });
</script>

<?php include __DIR__ . '/../../components/status-legend.php'; ?>
<?php include __DIR__ . '/../../components/layout-footer.php'; ?>