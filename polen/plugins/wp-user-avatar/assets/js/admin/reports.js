(function ($) {

    function format_amount(amount) {
        return currencySymbol.replace(/[0-9.,]+/, amount);
    }

    $(window).on('load', function () {

        if (document.getElementById('ppress-report-revenue')) {

            $('#ppress-mode-filter select').on('change', function () {
                $('#ppress-date-filters').toggle(this.value === 'custom');
            }).change();

            const revenueConfig = {
                type: 'line',
                data: {
                    labels: revenueStat.label,
                    datasets: revenueStat.dataset
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            // https://www.chartjs.org/docs/latest/samples/scale-options/ticks.html
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, ticks) {
                                    return format_amount(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';

                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += format_amount(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                },
            };

            const taxesConfig = {
                type: 'line',
                data: {
                    labels: taxesStat.label,
                    datasets: taxesStat.dataset
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            // https://www.chartjs.org/docs/latest/samples/scale-options/ticks.html
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, ticks) {
                                    return format_amount(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';

                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += format_amount(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                },
            };

            const ordersConfig = {
                type: 'line',
                data: {
                    labels: orderStat.label,
                    datasets: orderStat.dataset
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                        }
                    }
                },
            };

            const refundConfig = {
                type: 'line',
                data: {
                    labels: refundStat.label,
                    datasets: refundStat.dataset
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                },
            };

            const topPlanConfig = {
                type: 'line',
                data: {
                    labels: topPlansStat.label,
                    datasets: topPlansStat.dataset
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            stacked: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            // grid line settings
                            grid: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, ticks) {
                                    return format_amount(value);
                                }
                            }
                        },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';

                                    if (label) label += ': ';

                                    if (context.parsed.y !== null) {

                                        if (context.dataset.borderColor === 'rgb(255, 99, 132)') {
                                            label += format_amount(context.parsed.y);
                                        } else {
                                            label += context.parsed.y;
                                        }
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                },
            };

            const paymentMethodsConfig = {
                type: 'line',
                data: {
                    labels: paymentMethodsStat.label,
                    datasets: paymentMethodsStat.dataset
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    scales: {
                        y: {
                            type: 'linear',
                            stacked: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            // grid line settings
                            grid: {
                                drawOnChartArea: false, // only want the grid lines for one axis to show up
                            },
                            ticks: {
                                // Include a dollar sign in the ticks
                                callback: function (value, index, ticks) {
                                    return format_amount(value);
                                }
                            }
                        },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';

                                    if (label) label += ': ';

                                    if (context.parsed.y !== null) {

                                        if (context.dataset.borderColor === 'rgb(255, 99, 132)') {
                                            label += format_amount(context.parsed.y);
                                        } else {
                                            label += context.parsed.y;
                                        }
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                },
            };

            new Chart(document.getElementById('ppress-report-revenue'), revenueConfig);
            new Chart(document.getElementById('ppress-report-orders'), ordersConfig);
            if ($('#ppress-report-tax').length > 0) {
                new Chart(document.getElementById('ppress-report-tax'), taxesConfig);
            }
            new Chart(document.getElementById('ppress-report-refunds'), refundConfig);
            new Chart(document.getElementById('ppress-report-top-plans'), topPlanConfig);
            new Chart(document.getElementById('ppress-report-payment-methods'), paymentMethodsConfig);
        }
    });

})(jQuery);