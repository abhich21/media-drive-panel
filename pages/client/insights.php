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
        flex-wrap: wrap;
        gap: 16px;
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

    .chart-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .data-selector {
        padding: 8px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        color: #1a1a1a;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
    }

    .data-selector:hover {
        border-color: #9ca3af;
        background: #f9fafb;
    }

    .data-selector:focus {
        outline: none;
        border-color: #5D4B3A;
        box-shadow: 0 0 0 3px rgba(93, 75, 58, 0.1);
    }

    .chart-type-buttons {
        display: flex;
        gap: 6px;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 8px;
    }

    .chart-type-btn {
        padding: 6px 12px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-type-btn:hover {
        color: #1a1a1a;
        background: rgba(255, 255, 255, 0.5);
    }

    .chart-type-btn.active {
        background: white;
        color: #5D4B3A;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
    }

    @media (max-width: 768px) {
        .chart-container {
            height: 300px;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .chart-controls {
            width: 100%;
        }

        .data-selector {
            flex: 1;
        }
    }
</style>

<!-- Interactive Chart Section -->
<div class="chart-card">
    <div class="chart-header">
        <div>
            <div class="chart-title">Feedback Analytics</div>
            <div class="chart-subtitle" id="chartSubtitle">Average ratings across all categories</div>
        </div>
        <div class="chart-controls">
            <select id="dataSelector" class="data-selector">
                <option value="ratings">Rating Categories</option>
                <option value="cars">By Car</option>
                <option value="influencers">By Influencer</option>
                <option value="prfirms">By Media Outlet</option>
                <option value="timeline">Timeline (Daily)</option>
            </select>
            <div class="chart-type-buttons">
                <button class="chart-type-btn active" data-type="bar" title="Bar Chart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="4" y="11" width="4" height="9" />
                        <rect x="10" y="6" width="4" height="14" />
                        <rect x="16" y="3" width="4" height="17" />
                    </svg>
                </button>
                <button class="chart-type-btn" data-type="pie" title="Pie Chart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                    </svg>
                </button>
                <button class="chart-type-btn" data-type="line" title="Line Chart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 17 9 11 13 15 21 7" />
                    </svg>
                </button>
                <button class="chart-type-btn" data-type="doughnut" title="Doughnut Chart">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
                        <circle cx="12" cy="12" r="4" fill="white" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="analyticsChart"></canvas>
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

    // Chart configuration and state
    let currentChart = null;
    let currentChartType = 'bar';
    let currentDataSource = 'ratings';

    // Color palettes
    const colorPalettes = {
        brown: [
            '#C4B8A8', '#B8A898', '#C9B99A', '#9D8B7A',
            '#7D6B5A', '#8D7B6A', '#5D4B3A', '#A89878'
        ],
        vibrant: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
            '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
        ]
    };

    // Data configurations for different sources
    const dataConfigs = {
        ratings: {
            labels: ['Handling', 'Comfort', 'Performance', 'NVH', 'Features', 'Appearance', 'Overall'],
            data: [65, 45, 78, 58, 85, 68, 92],
            subtitle: 'Average ratings across all categories',
            colors: colorPalettes.brown
        },
        cars: {
            labels: ['Car A1', 'Car A2', 'Car B1', 'Car B2', 'Car C1'],
            data: [75, 82, 68, 91, 73],
            subtitle: 'Average ratings by car',
            colors: colorPalettes.brown
        },
        influencers: {
            labels: ['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Williams', 'Tom Brown'],
            data: [88, 76, 92, 81, 85],
            subtitle: 'Average ratings by influencer',
            colors: colorPalettes.brown
        },
        prfirms: {
            labels: ['Auto Today', 'Car Magazine', 'Drive Weekly', 'Motor Trends', 'Speed News'],
            data: [82, 78, 85, 79, 88],
            subtitle: 'Average ratings by media outlet',
            colors: colorPalettes.brown
        },
        timeline: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            data: [72, 78, 85, 81, 88, 76, 82],
            subtitle: 'Daily average ratings over time',
            colors: colorPalettes.brown
        }
    };

    // Initialize chart
    function initChart() {
        const ctx = document.getElementById('analyticsChart');
        if (!ctx) return;

        const config = dataConfigs[currentDataSource];

        // Destroy existing chart
        if (currentChart) {
            currentChart.destroy();
        }

        // Create new chart
        currentChart = new Chart(ctx, {
            type: currentChartType,
            data: {
                labels: config.labels,
                datasets: [{
                    label: 'Rating',
                    data: config.data,
                    backgroundColor: currentChartType === 'line' ? 'rgba(93, 75, 58, 0.1)' : config.colors,
                    borderColor: currentChartType === 'line' ? '#5D4B3A' : config.colors,
                    borderWidth: currentChartType === 'line' ? 3 : 1,
                    borderRadius: currentChartType === 'bar' ? 6 : 0,
                    borderSkipped: false,
                    barThickness: currentChartType === 'bar' ? 40 : undefined,
                    maxBarThickness: currentChartType === 'bar' ? 50 : undefined,
                    fill: currentChartType === 'line',
                    tension: currentChartType === 'line' ? 0.4 : 0,
                    pointRadius: currentChartType === 'line' ? 5 : 0,
                    pointBackgroundColor: currentChartType === 'line' ? '#5D4B3A' : undefined,
                    pointBorderColor: currentChartType === 'line' ? '#fff' : undefined,
                    pointBorderWidth: currentChartType === 'line' ? 2 : 0,
                    pointHoverRadius: currentChartType === 'line' ? 7 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: currentChartType === 'pie' || currentChartType === 'doughnut',
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12,
                                weight: 500
                            },
                            color: '#1a1a1a',
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#5D4B3A',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + '/100';
                                } else if (context.parsed !== null) {
                                    label += context.parsed + '/100';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: currentChartType === 'bar' || currentChartType === 'line' ? {
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
                                size: 11,
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
                            color: '#666666',
                            font: {
                                size: 11
                            },
                            callback: function (value) {
                                return value;
                            }
                        }
                    }
                } : {}
            }
        });

        // Update subtitle
        document.getElementById('chartSubtitle').textContent = config.subtitle;
    }

    // Handle chart type button clicks
    document.querySelectorAll('.chart-type-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            // Update active state
            document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Update chart type and re-render
            currentChartType = this.dataset.type;
            initChart();
        });
    });

    // Handle data source selection
    document.getElementById('dataSelector')?.addEventListener('change', function () {
        currentDataSource = this.value;
        initChart();
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
        initChart();

        // TODO: Fetch real data from API
        // fetchFeedbackData();
    });

    // Function to fetch real feedback data (to be implemented)
    async function fetchFeedbackData() {
        try {
            const response = await fetch('<?= BASE_PATH ?>/api/feedback.php?action=list');
            const data = await response.json();

            if (data.success && data.data) {
                // Process and update chart data
                processRealData(data.data);
            }
        } catch (error) {
            console.error('Error fetching feedback data:', error);
        }
    }

    // Process real data from API
    function processRealData(feedbackData) {
        // Calculate averages for each rating category
        const categories = ['handling', 'comfort', 'performance', 'nvh', 'features', 'appearance', 'overall'];
        const averages = {};

        categories.forEach(cat => {
            const ratings = feedbackData
                .map(f => f.ratings?.[cat])
                .filter(r => r !== undefined && r !== null);

            averages[cat] = ratings.length > 0
                ? ratings.reduce((sum, r) => sum + r, 0) / ratings.length * 20 // Convert to 100 scale
                : 0;
        });

        // Update dataConfigs with real data
        dataConfigs.ratings.data = categories.map(cat => Math.round(averages[cat]));

        // Re-render chart
        if (currentDataSource === 'ratings') {
            initChart();
        }
    }
</script>

<?php include __DIR__ . '/../../components/layout-footer.php'; ?>