<?php
if (!isset($_SESSION['admin_username'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db_connection.php';

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';

$whereClause = '';
$params = array();

switch ($filter) {
    case 'today':
        $whereClause = "WHERE DATE(t.transaction_date) = CURDATE()";
        break;
    case 'week':
        $whereClause = "WHERE YEARWEEK(t.transaction_date) = YEARWEEK(CURDATE())";
        break;
    case 'month':
        $whereClause = "WHERE MONTH(t.transaction_date) = MONTH(CURDATE()) AND YEAR(t.transaction_date) = YEAR(CURDATE())";
        break;
    case 'year':
        $whereClause = "WHERE YEAR(t.transaction_date) = YEAR(CURDATE())";
        break;
}

$sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.address, u.phone_number 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        $whereClause
        ORDER BY t.transaction_date DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all data for printing (without LIMIT)
$printSql = "SELECT t.*, u.first_name, u.last_name, u.email, u.address, u.phone_number 
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            $whereClause
            ORDER BY t.transaction_date DESC";
$printStmt = $conn->prepare($printSql);
$printStmt->execute();
$allTransactions = $printStmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM transactions t JOIN users u ON t.user_id = u.id $whereClause";
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$totalTransactions = $countStmt->fetchColumn();
$totalPages = ceil($totalTransactions / $limit);
?>

<div class="reports-container">
    <h1>Reports</h1>
    
    <form method="POST" class="filter-form">
        <select name="filter" onchange="this.form.submit()">
            <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Time</option>
            <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="week" <?php echo $filter === 'week' ? 'selected' : ''; ?>>This Week</option>
            <option value="month" <?php echo $filter === 'month' ? 'selected' : ''; ?>>This Month</option>
            <option value="year" <?php echo $filter === 'year' ? 'selected' : ''; ?>>This Year</option>
        </select>
        <input type="hidden" name="page" value="1">
        <button type="button" onclick="printReport()" class="print-btn">Print Report</button>
    </form>

    <div class="transaction-table-container">
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Phone Number</th>
                    <th>Transaction No.</th>
                    <th>Reference No.</th>
                    <th>Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td>
                            <?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?>
                            <br>
                            <small><?php echo $transaction['email']; ?></small>
                        </td>
                        <td><?php echo $transaction['address']; ?></td>
                        <td><?php echo $transaction['phone_number']; ?></td>
                        <td><?php echo $transaction['transaction_number']; ?></td>
                        <td><?php echo $transaction['reference_number']; ?></td>
                        <td>₱<?php 
                            $balance = 0;
                            if ($transaction['status'] == 'PARTIALLY PAID') {
                                $balance = $transaction['total_amount'] / 2;
                            }
                            echo number_format($balance, 2); 
                        ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Hidden table for printing -->
    <div class="print-only-table">
        <h2>Transaction Report - <?php echo ucfirst($filter); ?></h2>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Phone Number</th>
                    <th>Transaction No.</th>
                    <th>Reference No.</th>
                    <th>Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allTransactions as $transaction): ?>
                    <tr>
                        <td>
                            <?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?>
                            <br>
                            <small><?php echo $transaction['email']; ?></small>
                        </td>
                        <td><?php echo $transaction['address']; ?></td>
                        <td><?php echo $transaction['phone_number']; ?></td>
                        <td><?php echo $transaction['transaction_number']; ?></td>
                        <td><?php echo $transaction['reference_number']; ?></td>
                        <td>₱<?php 
                            $balance = 0;
                            if ($transaction['status'] == 'PARTIALLY PAID') {
                                $balance = $transaction['total_amount'] / 2;
                            }
                            echo number_format($balance, 2); 
                        ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <button class="pagination-btn" data-page="<?php echo $page - 1; ?>" <?php if ($page <= 1) echo 'disabled'; ?>>Previous</button>
        <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
        <button class="pagination-btn" data-page="<?php echo $page + 1; ?>" <?php if ($page >= $totalPages) echo 'disabled'; ?>>Next</button>
    </div>
</div>

<script>
    document.querySelectorAll('.pagination-btn').forEach(button => {
        button.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            if (page > 0 && page <= <?php echo $totalPages; ?>) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.appendChild(createHiddenInput('page', page));
                form.appendChild(createHiddenInput('filter', '<?php echo $filter; ?>'));
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    function createHiddenInput(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }

    function printReport() {
        window.print();
    }
</script>

<style>
.filter-form {
    margin-bottom: 20px;
}

.filter-form select {
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.print-btn {
    margin-left: 10px;
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.print-btn:hover {
    background-color: #45a049;
}

.print-only-table {
    display: none;
}

@media print {
    body * {
        visibility: hidden;
    }
    .reports-container,
    .reports-container * {
        visibility: visible;
    }
    .reports-container {
        position: absolute;
        left: 0;
        top: 0;
    }
    .filter-form,
    .pagination {
        display: none;
    }
    .transactions-table {
        width: 100%;
        border-collapse: collapse;
    }
    .transactions-table th,
    .transactions-table td {
        border: 1px solid #000;
        padding: 8px;
    }
    .transaction-table-container {
        display: none;
    }
    .print-only-table {
        display: block;
    }
    .print-only-table h2 {
        text-align: center;
        margin-bottom: 20px;
    }
}
</style>
