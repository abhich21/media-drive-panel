<?php
/**
 * MDM Client - Promoter Attendance
 * View promoter attendance records (read-only for clients)
 */

$pageTitle = 'Promoter Attendance';
$currentPage = 'attendance';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['client', 'superadmin']);

// TODO: Fetch from database
$attendance = [
    ['id' => 1, 'name' => 'John Smith', 'date' => '2024-01-15', 'checkIn' => '08:30', 'checkOut' => '18:00', 'status' => 'present'],
    ['id' => 2, 'name' => 'Sarah Johnson', 'date' => '2024-01-15', 'checkIn' => '09:15', 'checkOut' => '17:30', 'status' => 'late'],
    ['id' => 3, 'name' => 'Mike Wilson', 'date' => '2024-01-15', 'checkIn' => '08:00', 'checkOut' => '18:30', 'status' => 'present'],
    ['id' => 4, 'name' => 'Emily Brown', 'date' => '2024-01-15', 'checkIn' => null, 'checkOut' => null, 'status' => 'absent'],
    ['id' => 5, 'name' => 'David Lee', 'date' => '2024-01-15', 'checkIn' => '08:45', 'checkOut' => '13:00', 'status' => 'half_day'],
];

$summary = [
    'total' => count($attendance),
    'present' => count(array_filter($attendance, fn($a) => $a['status'] === 'present')),
    'late' => count(array_filter($attendance, fn($a) => $a['status'] === 'late')),
    'absent' => count(array_filter($attendance, fn($a) => $a['status'] === 'absent')),
];

include __DIR__ . '/../../components/layout.php';
?>

<!-- Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-text"><?= $summary['total'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Total Promoters</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-success"><?= $summary['present'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Present</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-warning"><?= $summary['late'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Late</div>
    </div>
    <div class="mdm-card text-center">
        <div class="text-3xl font-bold text-mdm-alert"><?= $summary['absent'] ?></div>
        <div class="text-sm text-mdm-text/60 mt-1">Absent</div>
    </div>
</div>

<!-- Date Filter -->
<div class="mdm-card mb-6">
    <div class="flex items-center gap-4">
        <span class="text-sm font-medium text-mdm-text">Date:</span>
        <input type="date" value="2024-01-15" class="mdm-tag border-0 cursor-pointer"
            onchange="filterByDate(this.value)">
    </div>
</div>

<!-- Attendance Table -->
<div class="mdm-card overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-mdm-tag">
                <th class="text-left py-4 px-4 font-semibold text-mdm-text">Promoter</th>
                <th class="text-left py-4 px-4 font-semibold text-mdm-text">Check In</th>
                <th class="text-left py-4 px-4 font-semibold text-mdm-text">Check Out</th>
                <th class="text-left py-4 px-4 font-semibold text-mdm-text">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendance as $record): ?>
                <?php
                $statusClasses = [
                    'present' => 'bg-green-100 text-green-800',
                    'late' => 'bg-yellow-100 text-yellow-800',
                    'absent' => 'bg-red-100 text-red-800',
                    'half_day' => 'bg-orange-100 text-orange-800',
                ];
                $statusClass = $statusClasses[$record['status']] ?? 'bg-gray-100 text-gray-800';
                $statusLabel = ucfirst(str_replace('_', ' ', $record['status']));
                ?>
                <tr class="border-b border-mdm-tag/50 hover:bg-mdm-bg/50">
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-mdm-tag flex items-center justify-center font-medium">
                                <?= strtoupper(substr($record['name'], 0, 1)) ?>
                            </div>
                            <span class="font-medium text-mdm-text"><?= h($record['name']) ?></span>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-mdm-text">
                        <?= $record['checkIn'] ? formatTime($record['checkIn']) : '-' ?>
                    </td>
                    <td class="py-4 px-4 text-mdm-text">
                        <?= $record['checkOut'] ? formatTime($record['checkOut']) : '-' ?>
                    </td>
                    <td class="py-4 px-4">
                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function filterByDate(date) {
        const url = new URL(window.location);
        url.searchParams.set('date', date);
        window.location = url;
    }
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>