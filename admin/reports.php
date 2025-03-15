<?php
if (!isset($_SESSION['admin_username'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db_connection.php';

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.address, u.phone_number 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.transaction_date DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM transactions t JOIN users u ON t.user_id = u.id";
$countStmt = $conn->prepare($countSql);
$countStmt->execute();
$totalTransactions = $countStmt->fetchColumn();
$totalPages = ceil($totalTransactions / $limit);
?>

<div class="reports-container">
    <h1>Reports</h1>
    
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
</script>
