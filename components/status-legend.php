<?php
/**
 * MDM Status Legend Footer
 * Shows color-coded status indicators - no background, centered
 */
?>
<div class="status-legend-footer">
    <div class="legend-item">
        <span class="legend-dot ready"></span>
        <span class="legend-label">Ready</span>
    </div>
    <div class="legend-item">
        <span class="legend-dot pending"></span>
        <span class="legend-label">Pending</span>
    </div>
    <div class="legend-item">
        <span class="legend-dot on-drive"></span>
        <span class="legend-label">On Drive</span>
    </div>
</div>

<style>
    .status-legend-footer {
        position: fixed;
        bottom: 12px;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        z-index: 100;
        pointer-events: none;
        transition: left 0.3s ease;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .legend-dot.ready {
        background: #4CAF50;
    }

    .legend-dot.pending {
        background: #FF9800;
    }

    .legend-dot.on-drive {
        background: #F44336;
    }

    .legend-label {
        font-size: 0.75rem;
        color: #666;
        font-weight: 500;
    }

    @media (min-width: 768px) {
        .status-legend-footer {
            left: 240px;
        }
    }
</style>