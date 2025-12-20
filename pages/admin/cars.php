<?php
/**
 * MDM Admin - Manage Cars
 * Admin car management page with Add/Edit functionality
 */

$pageTitle = 'Manage Cars';
$currentPage = 'cars';
$clientLogo = 'Admin Panel';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['superadmin']);

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $message = 'Car added successfully!';
    } elseif ($_POST['action'] === 'edit') {
        $message = 'Car updated successfully!';
    } elseif ($_POST['action'] === 'delete') {
        $message = 'Car deleted successfully!';
    }
}

// TODO: Fetch from database
$cars = [
    ['id' => 1, 'name' => 'Tata Nexon EV', 'model' => 'Nexon EV Max', 'registration' => 'MH01AB1234', 'color' => 'Teal Blue', 'status' => 'on_drive', 'event' => 'Media Drive 2024'],
    ['id' => 2, 'name' => 'Tata Harrier', 'model' => 'Harrier Dark', 'registration' => 'MH01CD5678', 'color' => 'Atlas Black', 'status' => 'cleaned', 'event' => 'Media Drive 2024'],
    ['id' => 3, 'name' => 'Tata Safari', 'model' => 'Safari Adventure', 'registration' => 'MH01EF9012', 'color' => 'Tropical Mist', 'status' => 'standby', 'event' => 'Media Drive 2024'],
    ['id' => 4, 'name' => 'Tata Punch', 'model' => 'Punch Creative', 'registration' => 'MH01GH3456', 'color' => 'Meteor Bronze', 'status' => 'cleaning', 'event' => 'Media Drive 2024'],
    ['id' => 5, 'name' => 'Tata Tiago', 'model' => 'Tiago XZ+', 'registration' => 'MH01IJ7890', 'color' => 'Opal White', 'status' => 'returned', 'event' => 'Media Drive 2024'],
    ['id' => 6, 'name' => 'Tata Altroz', 'model' => 'Altroz DCA', 'registration' => 'MH01KL2345', 'color' => 'Opera Blue', 'status' => 'hotel', 'event' => 'Media Drive 2024'],
];

$events = [
    ['id' => 1, 'name' => 'Media Drive 2024'],
    ['id' => 2, 'name' => 'Tata Safari Launch'],
];

$statuses = ['standby', 'cleaning', 'cleaned', 'on_drive', 'returned', 'hotel', 'pod_lineup'];

include __DIR__ . '/../../components/layout.php';
?>

<?php if ($message): ?>
    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-xl flex items-center justify-between">
        <span><?= h($message) ?></span>
        <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">&times;</button>
    </div>
<?php endif; ?>

<!-- Actions Bar -->
<div class="mdm-card mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-sm text-mdm-text/60">Total: <strong><?= count($cars) ?> cars</strong></span>
        </div>
        <button type="button" class="mdm-header-btn" id="addCarBtn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Car
        </button>
    </div>
</div>

<!-- Cars Table -->
<div class="mdm-card overflow-hidden">
    <table class="w-full">
        <thead class="bg-mdm-tag/50">
            <tr>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Car Name</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Registration</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Color</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Event</th>
                <th class="text-left px-4 py-3 text-sm font-semibold text-mdm-text">Status</th>
                <th class="text-right px-4 py-3 text-sm font-semibold text-mdm-text">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-mdm-tag/30">
            <?php foreach ($cars as $car):
                $badge = getStatusBadge($car['status']);
                ?>
                <tr class="hover:bg-mdm-bg/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium text-mdm-text"><?= h($car['name']) ?></div>
                        <div class="text-xs text-mdm-text/50"><?= h($car['model']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-mdm-text/70"><?= h($car['registration']) ?></td>
                    <td class="px-4 py-3 text-mdm-text/70"><?= h($car['color']) ?></td>
                    <td class="px-4 py-3 text-mdm-text/70"><?= h($car['event']) ?></td>
                    <td class="px-4 py-3">
                        <span
                            class="inline-block px-3 py-1.5 rounded-full text-xs font-medium <?= $badge[0] ?> <?= $badge[1] ?>"><?= $badge[2] ?></span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" class="text-mdm-text/60 hover:text-mdm-text mr-2 edit-car-btn"
                            data-car='<?= htmlspecialchars(json_encode($car), ENT_QUOTES) ?>' title="Edit">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button type="button" class="text-red-400 hover:text-red-600 delete-car-btn"
                            data-id="<?= $car['id'] ?>" data-name="<?= h($car['name']) ?>" title="Delete">
                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>

<!-- Add/Edit Car Modal - OUTSIDE main content wrapper -->
<div id="addCarModal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div
        style="background:white; border-radius:20px; width:100%; max-width:500px; margin:20px; max-height:90vh; overflow-y:auto;">
        <div style="padding:24px; border-bottom:1px solid #ddd;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h3 id="modalTitle" style="font-size:20px; font-weight:600; margin:0;">Add New Car</h3>
                <button type="button" id="closeModalBtn"
                    style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
            </div>
        </div>
        <form id="carForm" method="POST" style="padding:24px;">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="carId" value="">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:14px; font-weight:500; margin-bottom:8px;">Car Name *</label>
                    <input type="text" name="name" id="carName" required
                        style="width:100%; padding:12px 16px; border:1px solid #ddd; border-radius:12px; font-size:14px;"
                        placeholder="e.g. Tata Nexon">
                </div>
                <div>
                    <label style="display:block; font-size:14px; font-weight:500; margin-bottom:8px;">Model</label>
                    <input type="text" name="model" id="carModel"
                        style="width:100%; padding:12px 16px; border:1px solid #ddd; border-radius:12px; font-size:14px;"
                        placeholder="e.g. EV Max">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:14px; font-weight:500; margin-bottom:8px;">Registration
                        *</label>
                    <input type="text" name="registration" id="carRegistration" required
                        style="width:100%; padding:12px 16px; border:1px solid #ddd; border-radius:12px; font-size:14px;"
                        placeholder="e.g. MH01AB1234">
                </div>
                <div>
                    <label style="display:block; font-size:14px; font-weight:500; margin-bottom:8px;">Color</label>
                    <input type="text" name="color" id="carColor"
                        style="width:100%; padding:12px 16px; border:1px solid #ddd; border-radius:12px; font-size:14px;"
                        placeholder="e.g. Teal Blue">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:14px; font-weight:500; margin-bottom:8px;">Event *</label>
                    <select name="event_id" id="carEvent" required
                        style="width:100%; padding:12px 16px; border:1px solid #ddd; border-radius:12px; font-size:14px;">
                        <option value="">Select Event</option>
                        <?php foreach ($events as $event): ?>
                            <option value="<?= $event['id'] ?>"><?= h($event['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:14px; font-weight:500; margin-bottom:8px;">Status</label>
                    <select name="status" id="carStatus"
                        style="width:100%; padding:12px 16px; border:1px solid #ddd; border-radius:12px; font-size:14px;">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $status ?>"><?= ucfirst(str_replace('_', ' ', $status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:24px; padding-top:24px; border-top:1px solid #ddd;">
                <button type="button" id="cancelBtn"
                    style="flex:1; padding:12px; border:1px solid #ddd; border-radius:12px; background:white; cursor:pointer; font-size:14px;">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:1; padding:12px; border:none; border-radius:12px; background:#080808; color:white; cursor:pointer; font-size:14px;">
                    Save Car
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal"
    style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div
        style="background:white; border-radius:20px; width:100%; max-width:400px; margin:20px; padding:24px; text-align:center;">
        <div
            style="width:64px; height:64px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
            <svg style="width:32px; height:32px; color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h3 style="font-size:20px; font-weight:600; margin-bottom:8px;">Delete Car</h3>
        <p style="color:#666; margin-bottom:24px;">Are you sure you want to delete <strong id="deleteCarName"></strong>?
        </p>
        <form method="POST" style="display:flex; gap:12px;">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteCarId" value="">
            <button type="button" id="cancelDeleteBtn"
                style="flex:1; padding:12px; border:1px solid #ddd; border-radius:12px; background:white; cursor:pointer;">
                Cancel
            </button>
            <button type="submit"
                style="flex:1; padding:12px; border:none; border-radius:12px; background:#dc2626; color:white; cursor:pointer;">
                Delete
            </button>
        </form>
    </div>
</div>

<script>
    // Modal functions
    function openModal(id) {
        var modal = document.getElementById(id);
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Add Car button
    document.getElementById('addCarBtn').addEventListener('click', function () {
        document.getElementById('modalTitle').textContent = 'Add New Car';
        document.getElementById('formAction').value = 'add';
        document.getElementById('carForm').reset();
        openModal('addCarModal');
    });

    // Close modal buttons
    document.getElementById('closeModalBtn').addEventListener('click', function () {
        closeModal('addCarModal');
    });
    document.getElementById('cancelBtn').addEventListener('click', function () {
        closeModal('addCarModal');
    });
    document.getElementById('cancelDeleteBtn').addEventListener('click', function () {
        closeModal('deleteModal');
    });

    // Edit buttons
    document.querySelectorAll('.edit-car-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var car = JSON.parse(this.getAttribute('data-car'));
            document.getElementById('modalTitle').textContent = 'Edit Car';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('carId').value = car.id;
            document.getElementById('carName').value = car.name;
            document.getElementById('carModel').value = car.model || '';
            document.getElementById('carRegistration').value = car.registration;
            document.getElementById('carColor').value = car.color || '';
            document.getElementById('carStatus').value = car.status;
            openModal('addCarModal');
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-car-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('deleteCarId').value = this.getAttribute('data-id');
            document.getElementById('deleteCarName').textContent = this.getAttribute('data-name');
            openModal('deleteModal');
        });
    });

    // Close on backdrop click
    document.getElementById('addCarModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal('addCarModal');
    });
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal('deleteModal');
    });
</script>