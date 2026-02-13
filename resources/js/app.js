import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

let chartJsLoader;

async function getChartJs() {
    if (!chartJsLoader) {
        chartJsLoader = import('chart.js/auto').then((module) => module.default);
    }

    return chartJsLoader;
}

async function initializeDashboardChart() {
    const canvas = document.getElementById('execution-rate-chart');
    if (!canvas) {
        return;
    }

    const labels = JSON.parse(canvas.dataset.labels ?? '[]');
    const values = JSON.parse(canvas.dataset.values ?? '[]');

    if (!Array.isArray(labels) || !Array.isArray(values) || labels.length === 0) {
        return;
    }

    const Chart = await getChartJs();

    // eslint-disable-next-line no-new
    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: "Taux d'execution (%)",
                    data: values,
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.15)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#0f172a',
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        boxWidth: 14,
                        color: '#334155',
                        font: {
                            weight: '600',
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `${context.dataset.label}: ${Number(context.parsed.y).toFixed(2)}%`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: true,
                        maxRotation: 0,
                        color: '#64748b',
                        font: {
                            size: 11,
                        },
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 20,
                        color: '#64748b',
                        callback(value) {
                            return `${value}%`;
                        },
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.25)',
                    },
                },
            },
        },
    });
}

async function initializePerformanceCharts() {
    const trendCanvas = document.getElementById('performance-trend-chart');
    const statusCanvas = document.getElementById('performance-status-chart');
    const unitCanvas = document.getElementById('performance-unit-chart');

    if (!trendCanvas || !statusCanvas || !unitCanvas) {
        return;
    }

    const Chart = await getChartJs();

    const trendLabels = JSON.parse(trendCanvas.dataset.labels ?? '[]');
    const trendValues = JSON.parse(trendCanvas.dataset.values ?? '[]');
    if (trendLabels.length > 0) {
        // eslint-disable-next-line no-new
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: "Taux d'execution (%)",
                        data: trendValues,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.15)',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#0f172a',
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                    },
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            callback(value) {
                                return `${value}%`;
                            },
                        },
                    },
                },
            },
        });
    }

    const statusLabels = JSON.parse(statusCanvas.dataset.labels ?? '[]');
    const statusValues = JSON.parse(statusCanvas.dataset.values ?? '[]');
    if (statusLabels.length > 0) {
        // eslint-disable-next-line no-new
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [
                    {
                        data: statusValues,
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
                        borderColor: '#f8fafc',
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }

    const unitLabels = JSON.parse(unitCanvas.dataset.labels ?? '[]');
    const unitValues = JSON.parse(unitCanvas.dataset.values ?? '[]');
    if (unitLabels.length > 0) {
        // eslint-disable-next-line no-new
        new Chart(unitCanvas, {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [
                    {
                        label: "Taux d'execution (%)",
                        data: unitValues,
                        borderRadius: 8,
                        backgroundColor: 'rgba(15, 118, 110, 0.85)',
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        min: 0,
                        max: 100,
                        ticks: {
                            callback(value) {
                                return `${value}%`;
                            },
                        },
                    },
                },
            },
        });
    }
}

initializeDashboardChart();
initializePerformanceCharts();
