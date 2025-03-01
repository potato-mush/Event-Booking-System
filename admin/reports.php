<?php
require_once 'includes/db_connection.php';

$dateFrom = isset($_POST['dateFrom']) ? $_POST['dateFrom'] : '';
$dateTo = isset($_POST['dateTo']) ? $_POST['dateTo'] : '';

$sql = "SELECT t.id, b.event_name, u.first_name, u.last_name, t.transaction_date, t.transaction_number, t.total_amount, t.status 
        FROM transactions t
        JOIN booking b ON t.booking_id = b.id
        JOIN users u ON t.user_id = u.id";

if ($dateFrom && $dateTo) {
    $sql .= " WHERE t.transaction_date BETWEEN :dateFrom AND :dateTo";
}

$stmt = $conn->prepare($sql);

if ($dateFrom && $dateTo) {
    $stmt->bindParam(':dateFrom', $dateFrom);
    $stmt->bindParam(':dateTo', $dateTo);
}

$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="reports-container">
    <h1>Reports & Analytics</h1>
    
    <div class="report-filters">
        <input type="date" class="date-from">
        <input type="date" class="date-to">
        <button class="generate-report-btn">Generate Report</button>
    </div>

    <div class="report-content">
        <div class="chart-container">
            <!-- Charts will be rendered here -->
        </div>

        <div class="transaction-table-container">
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Transaction No.</th>
                        <th>Event Name</th>
                        <th>Customer Name</th>
                        <th>Transaction Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr data-id="<?php echo $transaction['id']; ?>">
                            <td><?php echo $transaction['transaction_number']; ?></td>
                            <td><?php echo $transaction['event_name']; ?></td>
                            <td><?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?></td>
                            <td><?php echo $transaction['transaction_date']; ?></td>
                            <td>₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                            <td><span class="status-badge status-<?php echo strtolower($transaction['status']); ?>"><?php echo $transaction['status']; ?></span></td>
                            <td>
                                <?php if ($transaction['status'] == 'UNPAID'): ?>
                                    <button class="confirm-btn" data-id="<?php echo $transaction['id']; ?>"><i class="fas fa-check-circle"></i></button>
                                <?php else: ?>
                                    <button class="cancel-btn" data-id="<?php echo $transaction['id']; ?>"><i class="fas fa-times-circle"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="print-preview" class="print-preview">
    <div class="print-preview-content">
        <span class="close" onclick="document.getElementById('print-preview').style.display='none'">&times;</span>
        <h2>Event Receipt</h2>
        <div id="receipt-details"></div>
        <button onclick="window.print()">Print</button>
    </div>
</div>

<script>
document.querySelector('.generate-report-btn').addEventListener('click', function() {
    const dateFrom = document.querySelector('.date-from').value;
    const dateTo = document.querySelector('.date-to').value;

    // Fetch and display the report based on the selected filters
    fetchTransactions(dateFrom, dateTo);
});

function fetchTransactions(dateFrom, dateTo) {
    fetch('includes/fetch_transactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ dateFrom, dateTo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error fetching transactions:', data.error);
            alert('An error occurred while fetching transactions. Please try again later.');
            return;
        }
        const tbody = document.querySelector('.transactions-table tbody');
        tbody.innerHTML = '';
        data.forEach(transaction => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', transaction.id);
            row.innerHTML = `
                <td>${transaction.transaction_number}</td>
                <td>${transaction.event_name}</td>
                <td>${transaction.first_name} ${transaction.last_name}</td>
                <td>${transaction.transaction_date}</td>
                <td>₱${number_format(transaction.total_amount, 2)}</td>
                <td><span class="status-badge status-${transaction.status.toLowerCase()}">${transaction.status}</span></td>
                <td>
                    ${transaction.status === 'UNPAID' ? 
                        `<button class="confirm-btn" data-id="${transaction.id}"><i class="fas fa-check-circle"></i></button>` : 
                        `<button class="cancel-btn" data-id="${transaction.id}"><i class="fas fa-times-circle"></i></button>`}
                </td>
            `;
            tbody.appendChild(row);
        });
    })
    .catch(error => {
        console.error('Error fetching transactions:', error);
        alert('An error occurred while fetching transactions. Please try again later.');
    });
}

function number_format(number, decimals) {
    return parseFloat(number).toFixed(decimals);
}

document.addEventListener('click', function(event) {
    if (event.target.classList.contains('confirm-btn')) {
        const transactionId = event.target.getAttribute('data-id');
        updateTransactionStatus(transactionId, 'PAID');
    } else if (event.target.classList.contains('cancel-btn')) {
        const transactionId = event.target.getAttribute('data-id');
        updateTransactionStatus(transactionId, 'UNPAID');
    } else if (event.target.closest('tr') && event.target.closest('tr').getAttribute('data-id')) {
        const transactionId = event.target.closest('tr').getAttribute('data-id');
        showPrintPreview(transactionId);
    }
});

function updateTransactionStatus(transactionId, status) {
    fetch('includes/update_transaction_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ transactionId, status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transaction status updated successfully.');
            location.reload();
        } else {
            alert('Failed to update transaction status.');
        }
    })
    .catch(error => {
        console.error('Error updating transaction status:', error);
        alert('An error occurred while updating transaction status. Please try again later.');
    });
}

function showPrintPreview(transactionId) {
    fetch('includes/fetch_transaction_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ transactionId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error fetching transaction details:', data.error);
            alert('An error occurred while fetching transaction details. Please try again later.');
            return;
        }
        const receiptDetails = document.getElementById('receipt-details');
        receiptDetails.innerHTML = `
            <p><strong>Transaction Number:</strong> ${data.transaction_number}</p>
            <p><strong>Event Name:</strong> ${data.event_name}</p>
            <p><strong>Customer Name:</strong> ${data.first_name} ${data.last_name}</p>
            <p><strong>Transaction Date:</strong> ${data.transaction_date}</p>
            <p><strong>Total Amount:</strong> ₱${number_format(data.total_amount, 2)}</p>
            <p><strong>Status:</strong> ${data.status}</p>
        `;
        document.getElementById('print-preview').style.display = 'block';
    })
    .catch(error => {
        console.error('Error fetching transaction details:', error);
        alert('An error occurred while fetching transaction details. Please try again later.');
    });
}
</script>

<style>
.print-preview {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
    animation: fadeIn 0.5s ease-in-out;
}

.print-preview-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
    text-align: center;
    border-radius: 10px;
    animation: slideIn 0.5s ease-in-out;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
