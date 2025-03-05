<?php
include 'includes/db_connection.php'; // Include your database connection file

// Fetch total bookings
$totalBookingsQuery = "SELECT COUNT(*) as totalBookings FROM booking";
$totalBookingsResult = $conn->query($totalBookingsQuery);
$totalBookings = $totalBookingsResult->fetch(PDO::FETCH_ASSOC)['totalBookings'];

// Fetch total customers
$totalCustomersQuery = "SELECT COUNT(*) as totalCustomers FROM users";
$totalCustomersResult = $conn->query($totalCustomersQuery);
$totalCustomers = $totalCustomersResult->fetch(PDO::FETCH_ASSOC)['totalCustomers'];

// Fetch pending bookings
$pendingBookingsQuery = "SELECT COUNT(*) as pendingBookings FROM booking WHERE status = 'pending'";
$pendingBookingsResult = $conn->query($pendingBookingsQuery);
$pendingBookings = $pendingBookingsResult->fetch(PDO::FETCH_ASSOC)['pendingBookings'];

// Fetch total paid amount
$totalPaidQuery = "SELECT SUM(total_amount) as totalPaid FROM transactions WHERE status = 'PAID'";
$totalPaidResult = $conn->query($totalPaidQuery);
$totalPaid = $totalPaidResult->fetch(PDO::FETCH_ASSOC)['totalPaid'];
?>

<div class="dashboard-container">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-calendar-check"></i>
            <h3>Total Bookings</h3>
            <p class="number"><?php echo $totalBookings; ?></p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3>Total Customers</h3>
            <p class="number"><?php echo $totalCustomers; ?></p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <h3>Pending Bookings</h3>
            <p class="number"><?php echo $pendingBookings; ?></p>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Total Paid Amount</h3>
            <p class="number">₱<?php echo number_format($totalPaid, 2); ?></p>
        </div>
    </div>
    
    <div class="chart-container">
        <canvas id="revenueChart"></canvas>
    </div>
    <div class="chart-row">
        <div class="chart-container">
            <canvas id="monthlyBookingsChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="bookingsStatusChart"></canvas>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="customersChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/dashboard-charts.js"></script>
