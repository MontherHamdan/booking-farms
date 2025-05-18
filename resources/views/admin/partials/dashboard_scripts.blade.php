<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Register the ChartDataLabels plugin
        Chart.register(ChartDataLabels);

        const orderStatusCanvas = document.getElementById('orderStatusChart');
        if (orderStatusCanvas) {
            const orderStatuses = @json($orderStatuses);
            const labels = Object.keys(orderStatuses);
            const dataValues = Object.values(orderStatuses);
            // Updated colors in RGBA format with 80% opacity
            const statusColorMap = {
                "Pending": "rgba(255, 193, 7, 0.8)", 
                "preparing": "rgba(111, 66, 193, 0.8)",
                "Out for Delivery": "rgba(232, 62, 140, 0.8)", 
                "Completed": "rgba(40, 167, 69, 0.8)",
                "Received": "rgba(23, 162, 184, 0.8)", 
                "Canceled": "rgba(220, 53, 69, 0.8)"
            };

            // Define a separate hover color map with full opacity (or any effect you prefer)
            const statusHoverColorMap = {
                "Pending": "rgba(255, 193, 7, 1)", 
                "preparing": "rgba(111, 66, 193, 1)",
                "Out for Delivery": "rgba(232, 62, 140, 1)", 
                "Completed": "rgba(40, 167, 69, 1)",
                "Received": "rgba(23, 162, 184, 1)", 
                "Canceled": "rgba(220, 53, 69, 1)"
            };

            new Chart(orderStatusCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: labels.map(l => statusColorMap[l] || "rgba(153,153,153,0.8)"),
                        borderWidth: 0,
                        // Use the new hover mapping for a different hover color effect
                        hoverBackgroundColor: labels.map(l => statusHoverColorMap[l] || "rgba(153,153,153,1)"),
                        hoverBorderColor: labels.map(l => statusHoverColorMap[l] || "rgba(153,153,153,1)"),
                        hoverBorderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: function(context) {
                                const index = context.tooltip.dataPoints[0].dataIndex;
                                const dataset = context.tooltip.dataPoints[0].dataset;
                                return dataset.backgroundColor[index];
                            },
                            borderColor: '#fff',  // White border color
                            borderWidth: 1,       // Adjust the width as needed
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    const percentage = total ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                    return `${label}: ${value} (${percentage})`;
                                }
                            }
                        },
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bold', size: 14 },
                            formatter: (value, context) => {
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                                return percentage + '%';
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 1200,
                        easing: 'easeInOutBack'
                    }
                }
            });
        }

        // --- Orders With/Without Additives Bar Chart ---
        const additivesCanvas = document.getElementById('additivesChart');
        if (additivesCanvas) {
            const ctx = additivesCanvas.getContext('2d');
            // Create gradient fills using the canvas height
            const gradientBlue = ctx.createLinearGradient(0, 0, 0, additivesCanvas.height);
            gradientBlue.addColorStop(0, 'rgba(0, 123, 255, 0.8)');
            gradientBlue.addColorStop(1, 'rgba(0, 123, 255, 0.4)');

            const gradientRed = ctx.createLinearGradient(0, 0, 0, additivesCanvas.height);
            gradientRed.addColorStop(0, 'rgba(220, 53, 69, 0.8)');
            gradientRed.addColorStop(1, 'rgba(220, 53, 69, 0.4)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ["With Additives", "Without Additives"],
                    datasets: [{
                        data: [@json($ordersWithAdditives), @json($ordersWithoutAdditives)],
                        backgroundColor: [gradientBlue, gradientRed],
                        borderColor: ['#007bff', '#dc3545'],
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 50
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#333', font: { size: 12 } },
                            grid: { color: 'rgba(0,0,0,0.1)' }
                        },
                        x: {
                            ticks: { color: '#333', font: { size: 12 } },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.7)',
                            titleFont: { weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 4,
                            displayColors: false
                        },
                        datalabels: {
                            color: '#fff',
                            anchor: 'end',
                            align: 'top',
                            font: { weight: 'bold', size: 12 },
                            formatter: (value) => value
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }

        // --- Top Selling Products Chart (Vertical Bar Chart) ---
        const topSellingCanvas = document.getElementById('topSellingChart');
        if(topSellingCanvas) {
            new Chart(topSellingCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topSellingProducts->pluck('bookType.name_ar')) !!},
                    datasets: [{
                        label: 'Orders',
                        data: {!! json_encode($topSellingProducts->pluck('total_orders')) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#333' },
                            grid: { color: 'rgba(0,0,0,0.1)' }
                        },
                        x: {
                            ticks: { color: '#333', autoSkip: false },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.7)',
                            titleFont: { weight: 'bold' },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#333',
                            font: { weight: 'bold' },
                            formatter: (value) => value
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuad'
                    }
                }
            });
        }

        // --- Orders by School Chart ---
        const schoolCanvas = document.getElementById('schoolChart');
        if(schoolCanvas) {
            const schoolLabels = {!! json_encode($ordersBySchool->pluck('school_name')) !!};
            const schoolData = {!! json_encode($ordersBySchool->pluck('total_orders')) !!};

            new Chart(schoolCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: schoolLabels,
                    datasets: [{
                        label: 'Orders',
                        data: schoolData,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#333' },
                            grid: { color: 'rgba(0,0,0,0.1)' }
                        },
                        x: {
                            ticks: { color: '#333', autoSkip: false },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.7)',
                            titleFont: { weight: 'bold' },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#333',
                            font: { weight: 'bold' },
                            formatter: (value) => value
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuad'
                    }
                }
            });
        }
    });
</script>

