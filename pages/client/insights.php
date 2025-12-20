<?php
/**
 * MDM Client - Insights
 * Strong points, weak points, and major concerns
 */

$pageTitle = 'Insights';
$currentPage = 'insights';
$clientLogo = 'Client Logo';

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

// requireAuth(['client', 'superadmin']);

// TODO: Fetch from database (aggregated feedback)
$insights = [
    'strongPoints' => [
        'Excellent vehicle presentation and cleanliness',
        'Professional and knowledgeable promoters',
        'Smooth handover process with minimal wait time',
        'Well-organized scheduling with no overlaps',
        'High engagement from journalists during test drives',
    ],
    'weakPoints' => [
        'Some vehicles returned with low fuel',
        'Minor delays in morning schedules',
        'Limited availability of premium variants',
    ],
    'majorConcerns' => [
        'One vehicle reported minor scratch on Day 3',
        'Feedback form completion rate below 80%',
    ],
];

include __DIR__ . '/../../components/layout.php';
?>

<style>
    .chart-card {
        background: #FFFFFF;
        border-radius: 20px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 700;
        color: #000000;
    }

    .chart-subtitle {
        font-size: 13px;
        color: #666666;
        margin-top: 4px;
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
        }
    }
</style>

<!-- Histogram Chart Section -->
<div class="chart-card">
    <div class="chart-container">
        <canvas id="usageHistogram"></canvas>
    </div>
</div>

<!-- Insights Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Strong Points -->
    <div class="mdm-card">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-mdm-text">Strong Points</h3>
        </div>
        <ul class="space-y-3">
            <?php foreach ($insights['strongPoints'] as $point): ?>
                <li class="flex items-start gap-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mt-2 flex-shrink-0"></span>
                    <span class="text-sm text-mdm-text/80"><?= h($point) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Weak Points -->
    <div class="mdm-card">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-mdm-text">Weak Points</h3>
        </div>
        <ul class="space-y-3">
            <?php foreach ($insights['weakPoints'] as $point): ?>
                <li class="flex items-start gap-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mt-2 flex-shrink-0"></span>
                    <span class="text-sm text-mdm-text/80"><?= h($point) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Major Concerns -->
    <div class="mdm-card">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-mdm-text">Major Concerns</h3>
        </div>
        <ul class="space-y-3">
            <?php foreach ($insights['majorConcerns'] as $point): ?>
                <li class="flex items-start gap-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mt-2 flex-shrink-0"></span>
                    <span class="text-sm text-mdm-text/80"><?= h($point) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Report Download Section -->
<div class="mdm-card mt-6">
    <h3 class="text-lg font-semibold text-mdm-text mb-6">Download Reports</h3>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <button onclick="downloadReport('daily-pdf')"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zm-3 9v5h2v-5h2l-3-3-3 3h2z" />
                </svg>
            </div>
            <div class="text-left">
                <div class="font-medium text-mdm-text text-sm">Daily Summary</div>
                <div class="text-xs text-mdm-text/60">PDF</div>
            </div>
        </button>

        <button onclick="downloadReport('event-pdf')"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zm-3 9v5h2v-5h2l-3-3-3 3h2z" />
                </svg>
            </div>
            <div class="text-left">
                <div class="font-medium text-mdm-text text-sm">Event Summary</div>
                <div class="text-xs text-mdm-text/60">PDF</div>
            </div>
        </button>

        <button onclick="downloadReport('raw-excel')"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8 17v-5l2 2.5L12 12v5h-1.5v-2.5L9 16l-1.5-1.5V17H6z" />
                </svg>
            </div>
            <div class="text-left">
                <div class="font-medium text-mdm-text text-sm">Raw Logs</div>
                <div class="text-xs text-mdm-text/60">Excel</div>
            </div>
        </button>

        <button onclick="downloadReport('car-report')"
            class="flex items-center gap-3 p-4 bg-mdm-bg rounded-xl hover:bg-mdm-tag transition-colors">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8 13h8v2H8v-2zm0 4h5v2H8v-2z" />
                </svg>
            </div>
            <div class="text-left">
                <div class="font-medium text-mdm-text text-sm">Car Wise</div>
                <div class="text-xs text-mdm-text/60">Report</div>
            </div>
        </button>
    </div>
</div>

<script>
    function downloadReport(type) {
        // TODO: Implement actual download
        console.log('Downloading report:', type);
        alert('Report download will be available soon!');
    }

    // Initialize Histogram Chart
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('usageHistogram');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Handling', 'Comfort', 'Performance', 'NVH', 'Features', 'Appearence', 'Overall'],
                    datasets: [{
                        label: 'Rating',
                        data: [65, 45, 78, 58, 85, 68, 92],
                        backgroundColor: [
                            '#C4B8A8', // Handling - light tan
                            '#B8A898', // Comfort - lighter brown
                            '#C9B99A', // Performance - beige
                            '#9D8B7A', // NVH - medium brown
                            '#7D6B5A', // Features - dark brown
                            '#8D7B6A', // Appearence - brown
                            '#5D4B3A'  // Overall - darkest brown
                        ],
                        borderRadius: 4,
                        borderSkipped: false,
                        barThickness: 50,
                        maxBarThickness: 60
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#5D4B3A',
                            titleColor: '#FFFFFF',
                            bodyColor: '#FFFFFF',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: '#666666',
                                font: {
                                    size: 12,
                                    weight: 500
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            border: {
                                display: false
                            },
                            grid: {
                                color: '#E8E8E8',
                                drawBorder: false
                            },
                            ticks: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>