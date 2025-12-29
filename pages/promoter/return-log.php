<?php
/**
 * MDM Promoter - Return Log
 * Log car return with damage reporting and photos
 * Styled to match Post Drive Details form
 */

$pageTitle = 'Log Return';
$currentPage = 'dashboard';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

$carId = $_GET['car_id'] ?? null;

if (!$carId) {
    header('Location: ' . BASE_PATH . '/pages/promoter/dashboard.php');
    exit;
}

// Fetch car info and exit log
$car = dbQueryOne("SELECT c.*, e.name as event_name FROM cars c JOIN events e ON e.id = c.event_id WHERE c.id = ?", [$carId]);

if (!$car || $car['status'] !== 'on_drive') {
    header('Location: ' . BASE_PATH . '/pages/promoter/dashboard.php');
    exit;
}

// Get exit log for this drive
$exitLog = dbQueryOne("
    SELECT cl.*, i.name as influencer_name, pf.name as pr_firm_name
    FROM car_logs cl
    LEFT JOIN influencers i ON i.id = cl.influencer_id
    LEFT JOIN pr_firms pf ON pf.id = cl.pr_firm_id
    WHERE cl.car_id = ? AND cl.log_type = 'exit'
    ORDER BY cl.created_at DESC LIMIT 1
", [$carId]);

include __DIR__ . '/../../components/layout.php';
?>

<style>
    .return-log-page {
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

    .upload-box.damage-mode {
        border-color: #fca5a5;
        background: #fef2f2;
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

    .damage-toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #fef2f2;
        border-radius: 12px;
        cursor: pointer;
        border: 1px solid #fecaca;
    }

    .damage-toggle input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: #dc2626;
    }

    .damage-toggle-text {
        font-weight: 600;
        color: #b91c1c;
    }

    .damage-toggle-subtext {
        font-size: 0.875rem;
        color: #dc2626;
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

    .car-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: white;
        padding: 12px 20px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .car-info-badge .icon-wrap {
        width: 40px;
        height: 40px;
        background: #fee2e2;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .car-info-badge .icon-wrap svg {
        width: 20px;
        height: 20px;
        color: #dc2626;
    }

    .car-info-badge .details .car-code {
        font-weight: 700;
        color: #1a1a1a;
    }

    .car-info-badge .details .sub-info {
        font-size: 0.875rem;
        color: #6b7280;
    }
</style>

<div class="return-log-page">
    <a href="dashboard.php"
        class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-medium mb-6 transition-colors">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Dashboard
    </a>

    <!-- Top Row: Car Info Badge -->
    <div class="top-row">
        <div class="car-info-badge">
            <div class="icon-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <div class="details">
                <div class="car-code"><?= h($car['car_code']) ?> - <?= h($car['name']) ?></div>
                <div class="sub-info">
                    <?= h($exitLog['pr_firm_name'] ?? 'Unknown PR Firm') ?> •
                    With: <?= h($exitLog['journalist_name'] ?? $exitLog['influencer_name'] ?? 'Unknown') ?>
                    <?php if ($exitLog['exit_time']): ?>
                        • Exit: <?= date('h:i A', strtotime($exitLog['exit_time'])) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <form id="returnForm" class="form-card" enctype="multipart/form-data">
        <input type="hidden" name="action" value="log_return">
        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">

        <h2 class="card-title">Fill out Return Details</h2>

        <!-- KM Reading & Fuel Level -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;" class="mb-6">
            <div class="form-group">
                <label class="form-label">KM Reading *</label>
                <input type="number" name="km_reading" step="0.1" min="0" class="form-input"
                    placeholder="Current odometer" required>
            </div>
            <div class="form-group">
                <label class="form-label">Fuel Level *</label>
                <select name="fuel_level" class="form-select" required>
                    <option value="">Select</option>
                    <option value="100">Full (100%)</option>
                    <option value="75">3/4 (75%)</option>
                    <option value="50">Half (50%)</option>
                    <option value="25">1/4 (25%)</option>
                    <option value="10">Low (10%)</option>
                </select>
            </div>
        </div>

        <!-- Damage Toggle -->
        <div class="form-group mb-6">
            <label class="damage-toggle">
                <input type="checkbox" name="has_damage" value="1" id="damageToggle">
                <div>
                    <div class="damage-toggle-text">Report Damage</div>
                    <div class="damage-toggle-subtext">Check if the car has any damage</div>
                </div>
            </label>
        </div>

        <!-- Damage Details (hidden by default) -->
        <div id="damageSection" class="hidden mb-6">
            <div class="form-group mb-4">
                <label class="form-label">Damage Description *</label>
                <textarea name="damage_description" class="form-input" rows="3"
                    placeholder="Describe the damage in detail..." style="resize: vertical;"></textarea>
            </div>

            <!-- Damage Photo Upload -->
            <div class="form-group">
                <label class="form-label">Damage Photos (Required)</label>
                <div class="upload-box damage-mode" onclick="document.getElementById('photoInput').click()">
                    <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div class="upload-text">Click to Upload Damage Photos</div>
                    <div class="upload-subtext">Clear photos of all damaged areas</div>
                </div>
                <input type="file" id="photoInput" name="photos[]" accept="image/jpeg,image/png,image/jpg" multiple
                    style="display: none;" onchange="handlePhotoUpload(event)">
                <div id="photoPreview" class="preview-grid"></div>
            </div>
        </div>

        <!-- Notes -->
        <div class="form-group mb-6">
            <label class="form-label">Notes / Comments</label>
            <textarea name="notes" class="form-input" rows="3" maxlength="500"
                placeholder="Any observations or notes..." style="resize: vertical;"></textarea>
        </div>

        <!-- Return Time (Readonly) -->
        <div class="readonly-field mb-6">
            <div class="readonly-label">Return Time</div>
            <div class="readonly-value" id="returnTime"><?= date('h:i:s A') ?></div>
        </div>

        <!-- Actions -->
        <div class="action-row">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">
                Cancel
            </button>
            <button type="submit" class="btn btn-primary">
                Complete Return
            </button>
        </div>
    </form>
</div>

<script>
    let selectedFiles = [];

    // Update return time every second
    setInterval(() => {
        document.getElementById('returnTime').textContent = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    }, 1000);

    // Toggle damage section
    document.getElementById('damageToggle').addEventListener('change', function () {
        const section = document.getElementById('damageSection');
        section.classList.toggle('hidden', !this.checked);
        if (this.checked) {
            document.querySelector('[name="damage_description"]').setAttribute('required', 'true');
        } else {
            document.querySelector('[name="damage_description"]').removeAttribute('required');
        }
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
    document.getElementById('returnForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Add selected photos
        selectedFiles.forEach((file, i) => {
            formData.append(`photos[${i}]`, file);
        });

        const btn = this.querySelector('.btn-primary');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            const response = await fetch('<?= BASE_PATH ?>/api/cars.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location = '<?= BASE_PATH ?>/pages/promoter/dashboard.php', 1500);
            } else {
                showToast(data.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Complete Return';
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Failed to log return. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Complete Return';
        }
    });
</script>

<?php include __DIR__ . '/../../components/status-legend.php'; ?>
<?php include __DIR__ . '/../../components/layout-footer.php'; ?>