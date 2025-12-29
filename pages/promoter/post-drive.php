<?php
/**
 * MDM Promoter - Post Drive Operations
 * Capture post-drive vehicle data: mileage, condition photos, notes
 * 
 * WORKFLOW: This form is for cars with status = "returned"
 * After submission, status changes to "under_cleaning"
 */

$pageTitle = 'Post Drive Operations';
$currentPage = 'post-drive';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Get cars with status = "returned" (ready for post-drive ops)
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
    .post-drive-page {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .page-header {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 24px;
    }

    .top-row {
        display: flex;
        gap: 24px;
        margin-bottom: 32px;
        align-items: flex-end;
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

    .helper-text {
        font-size: 0.75rem;
        color: #888;
        font-weight: 400;
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
    }

    .form-card {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 24px;
    }

    .upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafafa;
    }

    .upload-box:hover {
        border-color: #9ca3af;
        background: #f5f5f5;
    }

    .upload-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        color: #6b7280;
    }

    .upload-text {
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .upload-subtext {
        font-size: 0.875rem;
        color: #888;
    }

    .preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        font-size: 14px;
    }

    .readonly-field {
        background: #f3f4f6;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }

    .readonly-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .readonly-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    .action-row {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin-top: 32px;
    }

    .btn {
        padding: 12px 32px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }

    .btn-primary {
        background: #1a1a1a;
        color: white;
    }

    .btn-primary:hover {
        background: #333;
    }

    .btn-secondary {
        background: white;
        color: #1a1a1a;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
    }
</style>

<div class="post-drive-page">
    <a href="dashboard.php"
        class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-medium mb-6 transition-colors">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>

    <?php if (empty($returnedCars)): ?>
        <div class="form-card" style="text-align: center; padding: 60px 20px; color: #6b7280;">
            <svg style="width: 64px; height: 64px; margin: 0 auto 16px; opacity: 0.5;" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 style="font-size: 1.25rem; font-weight: 600; color: #1a1a1a; margin-bottom: 8px;">No Cars Ready for
                Post-Drive Ops</h3>
            <p>Cars must be in "Returned" status to fill post-drive details.</p>
        </div>
    <?php else: ?>

        <!-- Top Row: Car Code + Info Badges -->
        <div class="top-row">
            <div class="form-group" style="width: 180px;">
                <label class="form-label">
                    Car Code <span class="helper-text">(Returned Cars)</span>
                </label>
                <select id="carSelect" class="form-select" required>
                    <?php foreach ($returnedCars as $car): ?>
                        <option value="<?= $car['id'] ?>"
                            data-influencer-name="<?= h($car['influencer_name'] ?? $car['journalist_name'] ?? '') ?>"
                            data-pr-firm-name="<?= h($car['pr_firm_name'] ?? '') ?>"
                            data-pr-firm-id="<?= $car['pr_firm_id'] ?? '' ?>" <?= ($mostRecentCar && $mostRecentCar['id'] == $car['id']) ? 'selected' : '' ?>>
                            <?= h($car['car_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Auto-filled info badges -->
            <div class="readonly-field" style="padding: 8px 16px; display: inline-block;">
                <div class="readonly-label">Media Outlet</div>
                <div class="readonly-value" id="prFirmDisplay" style="font-size: 1rem;">
                    <?= h($mostRecentCar['pr_firm_name'] ?? 'N/A') ?>
                </div>
            </div>

            <div class="readonly-field" style="padding: 8px 16px; display: inline-block;">
                <div class="readonly-label">Influencer</div>
                <div class="readonly-value" id="influencerDisplay" style="font-size: 1rem;">
                    <?= h($mostRecentCar['influencer_name'] ?? $mostRecentCar['journalist_name'] ?? 'N/A') ?>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <form id="postDriveForm" class="form-card" enctype="multipart/form-data">
            <input type="hidden" name="car_id" id="carIdInput" value="<?= $mostRecentCar['id'] ?? '' ?>">
            <input type="hidden" name="pr_firm_id" id="prFirmIdInput" value="<?= $mostRecentCar['pr_firm_id'] ?? '' ?>">

            <h2 class="card-title">Fill out Post Drive Operations</h2>

            <!-- Distance Reading -->
            <div class="form-group mb-6">
                <label class="form-label">Distance Reading (KM) *</label>
                <input type="number" name="km_reading" step="0.1" min="0" class="form-input"
                    placeholder="Enter odometer reading" required>
            </div>

            <!-- Photo Upload -->
            <div class="form-group mb-6">
                <label class="form-label">Car Condition Photos</label>
                <div class="upload-box" onclick="document.getElementById('photoInput').click()">
                    <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div class="upload-text">Click to Upload Photos</div>
                    <div class="upload-subtext">Front, Rear, Sides & Odometer</div>
                </div>
                <input type="file" id="photoInput" name="photos[]" accept="image/jpeg,image/png,image/jpg" multiple
                    style="display: none;" onchange="handlePhotoUpload(event)">
                <div id="photoPreview" class="preview-grid"></div>
            </div>

            <!-- Notes -->
            <div class="form-group mb-6">
                <label class="form-label">Notes / Comments</label>
                <textarea name="notes" class="form-input" rows="3" maxlength="500"
                    placeholder="Any observations or notes..." style="resize: vertical;"></textarea>
            </div>

            <!-- Arrival Time (Readonly) -->
            <div class="readonly-field mb-6">
                <div class="readonly-label">Arrival Time</div>
                <div class="readonly-value" id="arrivalTime"><?= date('h:i:s A') ?></div>
            </div>

            <!-- Actions -->
            <div class="action-row">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save
                </button>
            </div>
        </form>
    </div>

    <script>
        let selectedFiles = [];

        // When car selection changes, update the display badges and hidden inputs
        document.getElementById('carSelect')?.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];

            // Update hidden inputs
            document.getElementById('carIdInput').value = this.value;
            document.getElementById('prFirmIdInput').value = selected.dataset.prFirmId || '';

            // Update display badges
            document.getElementById('prFirmDisplay').textContent = selected.dataset.prFirmName || 'N/A';
            document.getElementById('influencerDisplay').textContent = selected.dataset.influencerName || 'N/A';
        });

        // Handle photo upload preview
        function handlePhotoUpload(event) {
            const files = Array.from(event.target.files);
            const preview = document.getElementById('photoPreview');

            files.forEach(file => {
                if (selectedFiles.length >= 6) {
                    showToast('Maximum 6 photos allowed', 'error');
                    return;
                }

                selectedFiles.push(file);

                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="preview-remove" onclick="removePhoto(${selectedFiles.length - 1})">×</button>
            `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        function removePhoto(index) {
            selectedFiles.splice(index, 1);
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = '';
            selectedFiles.forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="preview-remove" onclick="removePhoto(${i})">×</button>
            `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        // Form submission
        document.getElementById('postDriveForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const carId = document.getElementById('carIdInput').value;

            if (!carId) {
                showToast('Please select a car', 'error');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'post_drive_ops');

            // Add selected photos
            selectedFiles.forEach((file, i) => {
                formData.append(`photos[${i}]`, file);
            });

            const btn = this.querySelector('.btn-primary');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const res = await fetch('<?= BASE_PATH ?>/api/cars.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();

                if (json.success) {
                    showToast('Post drive operations saved successfully', 'success');
                    setTimeout(() => window.location.href = 'dashboard.php', 1500);
                } else {
                    showToast(json.message || 'Error saving', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Save';
                }
            } catch (e) {
                console.error(e);
                showToast('Failed to save. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = 'Save';
            }
        });
    </script>

<?php endif; ?>

<?php include __DIR__ . '/../../components/status-legend.php'; ?>
<?php include __DIR__ . '/../../components/layout-footer.php'; ?>