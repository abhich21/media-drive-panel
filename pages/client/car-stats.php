<?php
/**
 * MDM Client - Individual Car Stats
 * Detailed car information with search and list
 */

$pageTitle = 'Individual Car Stats';
$currentPage = 'car-stats';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['client', 'superadmin']);

// TODO: Fetch from database - currently showing first car as selected
$selectedCar = [
    'carCode' => 'B6',
    'qrCode' => BASE_PATH . '/img/qr-placeholder.png', // You'll need to generate actual QR codes
    'make' => 'Tata Motors',
    'model' => 'Punch',
    'user' => 'Firm or Influencer Name',
    'status' => 'On Drive',
    'promoter' => 'Name',
    'vinNumber' => '1234567890987654',
    'vinData' => [200, 300, 400, 500, 600, 700, 800, 900, 1000] // Sample data matching mockup progression
];

// All cars list
$cars = [
    ['id' => 1, 'code' => 'B6', 'name' => 'Tata Punch', 'make' => 'Tata Motors', 'status' => 'On Drive', 'km' => 125.5],
    ['id' => 2, 'code' => 'A3', 'name' => 'Tata Nexon', 'make' => 'Tata Motors', 'status' => 'Cleaned', 'km' => 98.2],
    ['id' => 3, 'code' => 'C9', 'name' => 'Tata Harrier', 'make' => 'Tata Motors', 'status' => 'Standby', 'km' => 156.8],
    ['id' => 4, 'code' => 'D2', 'name' => 'Tata Safari', 'make' => 'Tata Motors', 'status' => 'Cleaning', 'km' => 78.3],
    ['id' => 5, 'code' => 'E7', 'name' => 'Tata Tiago', 'make' => 'Tata Motors', 'status' => 'Breakdown', 'km' => 210.5],
    ['id' => 6, 'code' => 'F1', 'name' => 'Tata Altroz', 'make' => 'Tata Motors', 'status' => 'Breakdown', 'km' => 45.0],
];

include __DIR__ . '/../../components/layout.php';
?>

<style>
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 32px;
    }

    .detail-card {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        min-height: 140px;
    }

    .detail-card-label {
        background: #E8E8E8;
        border-radius: 12px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        color: #000000;
        margin-bottom: 12px;
        text-align: center;
    }

    .detail-card-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .detail-card-value {
        font-size: 22px;
        font-weight: 700;
        color: #000000;
    }

    .detail-card-value.large {
        font-size: 42px;
        letter-spacing: -1px;
    }

    .qr-code {
        width: 100px;
        height: 100px;
        background: #F5F5F5;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #999;
    }

    .status-badge {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #5D4B3A;
        color: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        border: 4px solid #3D2B1A;
    }

    .vin-chart-container {
        width: 100%;
        height: 100px;
        margin-top: 8px;
    }

    .vin-number-text {
        font-size: 11px;
        color: #666;
        margin-bottom: 8px;
    }

    /* Search and Filter Section */
    .search-filter-section {
        background: #FFFFFF;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .search-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .search-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #E0E0E0;
        border-radius: 12px;
        font-size: 14px;
        outline: none;
    }

    .search-input:focus {
        border-color: #000000;
    }

    .filter-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 16px;
        background: #F5F5F5;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn:hover {
        background: #E0E0E0;
    }

    .filter-btn.active {
        background: #000000;
        color: #FFFFFF;
    }

    /* Car List */
    .car-list {
        background: #FFFFFF;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .car-list-item {
        display: grid;
        grid-template-columns: 80px 1fr 1fr 120px 100px;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #F0F0F0;
        cursor: pointer;
        transition: background 0.2s;
    }

    .car-list-item:hover {
        background: #F8F8F8;
    }

    .car-list-item:last-child {
        border-bottom: none;
    }

    .car-code {
        font-size: 18px;
        font-weight: 700;
        color: #000000;
    }

    .car-name {
        font-size: 14px;
        font-weight: 600;
        color: #000000;
    }

    .car-make {
        font-size: 13px;
        color: #666666;
    }

    .car-status {
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        color: #000000;
    }

    /* Only these statuses get badge backgrounds */
    .car-status.on-drive {
        padding: 8px 16px;
        border-radius: 8px;
        background: #5D4B3A;
        color: #FFFFFF;
    }

    .car-status.cleaned {
        padding: 8px 16px;
        border-radius: 8px;
        background: #4CAF50;
        color: #FFFFFF;
    }

    .car-status.standby {
        padding: 8px 16px;
        border-radius: 8px;
        background: #FFB74D;
        color: #000000;
    }

    .car-km {
        font-size: 14px;
        font-weight: 600;
        color: #000000;
        text-align: right;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .detail-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }

        .car-list-item {
            grid-template-columns: 60px 1fr 80px;
            gap: 12px;
        }

        .car-make,
        .car-km {
            display: none;
        }
    }
</style>

<!-- Car Details Grid -->
<div class="detail-grid">
    <!-- Car Code / QR -->
    <div class="detail-card">
        <div class="detail-card-label">Car Code / QR</div>
        <div class="detail-card-content">
            <div class="detail-card-value large"><?= h($selectedCar['carCode']) ?></div>
            <div class="qr-code" style="margin-top: 12px;">
                QR Code
            </div>
        </div>
    </div>

    <!-- Car Make -->
    <div class="detail-card">
        <div class="detail-card-label">Car Make</div>
        <div class="detail-card-content">
            <div class="detail-card-value"><?= h($selectedCar['make']) ?></div>
        </div>
    </div>

    <!-- Car Model -->
    <div class="detail-card">
        <div class="detail-card-label">Car Model</div>
        <div class="detail-card-content">
            <div class="detail-card-value"><?= h($selectedCar['model']) ?></div>
        </div>
    </div>

    <!-- User -->
    <div class="detail-card">
        <div class="detail-card-label">User</div>
        <div class="detail-card-content">
            <div class="detail-card-value" style="font-size: 18px;"><?= h($selectedCar['user']) ?></div>
        </div>
    </div>

    <!-- Status -->
    <div class="detail-card">
        <div class="detail-card-label">Status</div>
        <div class="detail-card-content">
            <div class="status-badge"><?= h($selectedCar['status']) ?></div>
        </div>
    </div>

    <!-- Promoter -->
    <div class="detail-card">
        <div class="detail-card-label">Promoter</div>
        <div class="detail-card-content">
            <div class="detail-card-value"><?= h($selectedCar['promoter']) ?></div>
        </div>
    </div>

    <!-- VIN Number with Chart (spans 2 columns) -->
    <div class="detail-card" style="grid-column: span 2;">
        <div class="detail-card-label">VIN Number</div>
        <div class="detail-card-content">
            <div class="vin-number-text"><?= h($selectedCar['vinNumber']) ?></div>
            <div class="vin-chart-container">
                <canvas id="vinChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="search-filter-section">
    <div class="search-bar">
        <input type="text" class="search-input" placeholder="Search by car code, model, or make..." id="carSearch">
    </div>
    <div class="filter-buttons">
        <button class="filter-btn active" onclick="filterStatus('all')">All</button>
        <button class="filter-btn" onclick="filterStatus('on-drive')">On Drive</button>
        <button class="filter-btn" onclick="filterStatus('cleaned')">Cleaned</button>
        <button class="filter-btn" onclick="filterStatus('standby')">Standby</button>
        <button class="filter-btn" onclick="filterStatus('cleaning')">Cleaning</button>
        <button class="filter-btn" onclick="filterStatus('breakdown')">Breakdown</button>
    </div>
</div>

<!-- Car List -->
<div class="car-list">
    <?php foreach ($cars as $car): ?>
        <div class="car-list-item" onclick="selectCar(<?= $car['id'] ?>)">
            <div class="car-code"><?= h($car['code']) ?></div>
            <div>
                <div class="car-name"><?= h($car['name']) ?></div>
            </div>
            <div class="car-make"><?= h($car['make']) ?></div>
            <div class="car-status <?= strtolower(str_replace(' ', '-', $car['status'])) ?>">
                <?= h($car['status']) ?>
            </div>
            <div class="car-km"><?= number_format($car['km'], 1) ?> km</div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Initialize VIN Chart with enhanced styling
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('vinChart');
        if (ctx) {
            // Create gradient with original beige color
            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 100);
            gradient.addColorStop(0, 'rgba(193, 176, 158, 0.85)');   // Beige top
            gradient.addColorStop(0.5, 'rgba(193, 176, 158, 0.5)');  // Beige middle
            gradient.addColorStop(1, 'rgba(193, 176, 158, 0.15)');   // Beige bottom

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['8am', '10am', '12pm', '2pm', '4pm', '6pm'],
                    datasets: [{
                        label: 'Kilometers',
                        data: <?= json_encode($selectedCar['vinData']) ?>,
                        backgroundColor: gradient,
                        borderColor: '#A0896F',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4, // Smooth curves
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#A0896F',
                        pointHoverBorderColor: '#FFFFFF',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(93, 75, 58, 0.95)',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                size: 12,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 14,
                                weight: '700'
                            },
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    return context.parsed.y + ' km';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            border: {
                                display: true,
                                color: '#D1C7BE',
                                width: 1
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                color: '#5D4B3A',
                                padding: 8
                            }
                        },
                        y: {
                            display: true,
                            position: 'left',
                            min: 0,
                            max: 1000,
                            border: {
                                display: true,
                                color: '#D1C7BE',
                                width: 1
                            },
                            grid: {
                                color: '#E8E4DF',
                                lineWidth: 1,
                                drawTicks: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                color: '#5D4B3A',
                                stepSize: 300,
                                padding: 8,
                                callback: function (value) {
                                    return value;
                                }
                            },
                            title: {
                                display: true,
                                text: 'Kms',
                                font: {
                                    size: 12,
                                    weight: '700'
                                },
                                color: '#5D4B3A',
                                padding: 4
                            }
                        }
                    }
                }
            });
        }
    });

    // Search functionality
    document.getElementById('carSearch').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.car-list-item');

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'grid';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Filter by status
    let currentFilter = 'all';
    function filterStatus(status) {
        currentFilter = status;

        // Update button states
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Filter list items
        const items = document.querySelectorAll('.car-list-item');
        items.forEach(item => {
            const statusEl = item.querySelector('.car-status');
            if (status === 'all' || statusEl.classList.contains(status)) {
                item.style.display = 'grid';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Select car
    function selectCar(id) {
        // TODO: Reload page with selected car or use AJAX
        console.log('Selected car:', id);
        // For now, just reload the page - you can add ?id= parameter
        // window.location.href = '?id=' + id;
    }
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>