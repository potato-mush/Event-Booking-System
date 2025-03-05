document.addEventListener('DOMContentLoaded', function() {
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const customersCtx = document.getElementById('customersChart').getContext('2d');
    const bookingsStatusCtx = document.getElementById('bookingsStatusChart').getContext('2d');
    const monthlyBookingsCtx = document.getElementById('monthlyBookingsChart').getContext('2d');

    function formatMonthLabel(month) {
        const date = new Date(month + '-01');
        return date.toLocaleString('default', { month: 'long', year: 'numeric' });
    }

    fetch('includes/fetch_transactions.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching data:', data.error);
                return;
            }
            const labels = data.map(item => formatMonthLabel(item.month));
            const revenueData = data.map(item => item.total);

            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: revenueData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Revenue (₱)'
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    fetch('includes/fetch_customers_chart.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching data:', data.error);
                return;
            }
            const labels = data.map(item => formatMonthLabel(item.month));
            const customersData = data.map(item => item.total);

            new Chart(customersCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly New Customers',
                        data: customersData,
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Customers'
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    fetch('includes/fetch_bookings_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching data:', data.error);
                return;
            }
            const labels = data.map(item => item.status);
            const statusData = data.map(item => item.total);

            new Chart(bookingsStatusCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Booking Status',
                        data: statusData,
                        backgroundColor: [
                            'rgba(255, 206, 86, 0.2)', // Pending - Yellow
                            'rgba(75, 192, 192, 0.2)', // Confirmed - Green
                            'rgba(255, 99, 132, 0.2)'  // Cancelled - Red
                        ],
                        borderColor: [
                            'rgba(255, 206, 86, 1)', // Pending - Yellow
                            'rgba(75, 192, 192, 1)', // Confirmed - Green
                            'rgba(255, 99, 132, 1)'  // Cancelled - Red
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    fetch('includes/fetch_monthly_bookings.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching data:', data.error);
                return;
            }
            const labels = data.map(item => formatMonthLabel(item.month));
            const monthlyBookingsData = data.map(item => item.total);

            new Chart(monthlyBookingsCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Monthly Bookings',
                        data: monthlyBookingsData,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Bookings'
                            }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));
});
