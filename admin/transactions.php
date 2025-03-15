<?php
require_once 'includes/db_connection.php';

$dateFrom = isset($_POST['dateFrom']) ? $_POST['dateFrom'] : '';
$dateTo = isset($_POST['dateTo']) ? $_POST['dateTo'] : '';
$search = isset($_POST['search']) ? $_POST['search'] : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql = "SELECT t.id, b.event_name, b.guest_no, b.event_date, b.event_time_start, b.event_time_end, u.first_name, u.last_name, u.email, t.transaction_date, t.transaction_number, t.total_amount, t.status 
        FROM transactions t
        JOIN booking b ON t.booking_id = b.id
        JOIN users u ON t.user_id = u.id";

$conditions = [];
$params = [];

if ($dateFrom && $dateTo) {
    $conditions[] = "t.transaction_date BETWEEN :dateFrom AND :dateTo";
    $params[':dateFrom'] = $dateFrom;
    $params[':dateTo'] = $dateTo;
}

if ($search) {
    $conditions[] = "(b.event_name LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search OR t.transaction_number LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= " LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countSql = "SELECT COUNT(*) FROM transactions t
             JOIN booking b ON t.booking_id = b.id
             JOIN users u ON t.user_id = u.id";

if ($conditions) {
    $countSql .= ' WHERE ' . implode(' AND ', $conditions);
}

$countStmt = $conn->prepare($countSql);

foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}

$countStmt->execute();
$totalTransactions = $countStmt->fetchColumn();
$totalPages = ceil($totalTransactions / $limit);
?>

<div class="reports-container">
    <h1>Transactions</h1>

    <div class="report-filters">
        <form method="POST" id="filter-form">
            <input type="date" name="dateFrom" class="date-from" value="<?php echo $dateFrom; ?>">
            <input type="date" name="dateTo" class="date-to" value="<?php echo $dateTo; ?>">
            <button type="submit" class="generate-report-btn">Generate Report</button>
            <input type="text" name="search" class="search-bar" placeholder="Search..." value="<?php echo $search; ?>">
        </form>
    </div>

    <div class="report-content">

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
                                <?php if ($transaction['status'] == 'PARTIALLY PAID'): ?>
                                    <button class="confirm-btn" data-id="<?php echo $transaction['id']; ?>"><i class="fas fa-check-circle"></i></button>
                                <?php else: ?>
                                    <button class="pending-btn" data-id="<?php echo $transaction['id']; ?>"><i class="fas fa-minus-circle"></i></button>
                                <?php endif; ?>
                            </td>
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
</div>

<div id="print-preview" class="print-preview">
    <div class="print-preview-content">
        <span class="close" onclick="document.getElementById('print-preview').style.display='none'">&times;</span>
        <div id="print-content">
            <div id="receipt-logo">
                <img src="assets/images/logo.png" alt="Logo" style="width: 150px; display: block; margin: 0 auto;">
            </div>
            <h3>Event Payment Receipt</h3>
            <div id="receipt-header">
                <p style="text-align: left; float: left;"><strong>Date:</strong> <span id="receipt-date"></span></p>
                <p style="text-align: right; float: right;"><strong>Transaction Number:</strong> <span id="receipt-transaction-number"></span></p>
            </div>
            <div style="clear: both;"></div>
            <div id="receipt-details">
                <h3>Guest Attendees Information</h3>
                <div class="receipt-row">
                    <span class="receipt-label">Guest/Attendee Name:</span>
                    <span class="receipt-value" id="receipt-guest-name"></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Email:</span>
                    <span class="receipt-value" id="receipt-guest-email"></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Number of Guests:</span>
                    <span class="receipt-value" id="receipt-guest-count"></span>
                </div>
                <!-- Add more guest information fields as needed -->
                <h3>Event Information</h3>
                <div class="receipt-row">
                    <span class="receipt-label">Event Name:</span>
                    <span class="receipt-value" id="receipt-event-name"></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Event Date:</span>
                    <span class="receipt-value" id="receipt-event-date"></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Event Time:</span>
                    <span class="receipt-value" id="receipt-event-time"></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Transaction Date:</span>
                    <span class="receipt-value" id="receipt-transaction-date"></span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Total Amount:</span>
                    <span class="receipt-value">₱<span id="receipt-total-amount"></span></span>
                </div>
            </div>
        </div>
        <button>Print</button>
    </div>
</div>

<style>
    @media print {
        .print-preview-content {
            max-width: 800px;
            margin: 0 auto;
        }
        .print-preview-content .close,
        .print-preview-content button {
            display: none !important;
        }
    }
</style>

<script>
    let currentPage = 1;

    function fetchTransactions(page = 1) {
        const dateFrom = document.querySelector('.date-from').value;
        const dateTo = document.querySelector('.date-to').value;
        const search = document.querySelector('.search-bar').value;

        fetch('includes/fetch_user_transactions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                dateFrom,
                dateTo,
                search,
                page
            })
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('.transactions-table tbody');
            tbody.innerHTML = '';
            
            data.transactions.forEach(transaction => {
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
                        ${transaction.status === 'PARTIALLY PAID' ? 
                            `<button class="confirm-btn" data-id="${transaction.id}"><i class="fas fa-check-circle"></i></button>` : 
                            `<button class="pending-btn" data-id="${transaction.id}"><i class="fas fa-minus-circle"></i></button>`}
                    </td>
                `;
                tbody.appendChild(row);
            });

            // Update pagination
            currentPage = data.currentPage;
            const pagination = document.querySelector('.pagination');
            pagination.innerHTML = `
                <button class="pagination-btn" data-page="${currentPage - 1}" ${currentPage <= 1 ? 'disabled' : ''}>Previous</button>
                <span>Page ${currentPage} of ${data.totalPages}</span>
                <button class="pagination-btn" data-page="${currentPage + 1}" ${currentPage >= data.totalPages ? 'disabled' : ''}>Next</button>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching transactions.');
        });
    }

    // Form submission handler
    document.querySelector('#filter-form').addEventListener('submit', function(event) {
        event.preventDefault();
        currentPage = 1;
        fetchTransactions(currentPage);
    });

    // Search input handler
    let searchTimeout;
    document.querySelector('.search-bar').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            fetchTransactions(currentPage);
        }, 500);
    });

    // Pagination click handler
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('pagination-btn') && !event.target.disabled) {
            const page = parseInt(event.target.getAttribute('data-page'));
            fetchTransactions(page);
        }
    });

    // Initial load
    fetchTransactions(1);

    function number_format(number, decimals) {
        return parseFloat(number).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    document.addEventListener('click', function(event) {
        if (event.target.closest('.confirm-btn')) {
            const transactionId = event.target.closest('.confirm-btn').getAttribute('data-id');
            if (confirm('Are you sure you want to mark this transaction as PAID?')) {
                updateTransactionStatus(transactionId, 'PAID');
            }
        } else if (event.target.closest('.cancel-btn')) {
            const transactionId = event.target.closest('.cancel-btn').getAttribute('data-id');
            if (confirm('Are you sure you want to mark this transaction as PARTIALLY PAID?')) {
                updateTransactionStatus(transactionId, 'PARTIALLY PAID');
            }
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
                body: JSON.stringify({
                    transactionId,
                    status
                })
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

    function formatTime(time) {
        const [hours, minutes] = time.split(':');
        const period = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;
        return `${formattedHours}:${minutes} ${period}`;
    }

    function showPrintPreview(transactionId) {
        fetch('includes/fetch_transaction_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    transactionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error fetching transaction details:', data.error);
                    alert('An error occurred while fetching transaction details. Please try again later.');
                    return;
                }
                document.getElementById('receipt-date').innerText = new Date().toLocaleDateString();
                document.getElementById('receipt-transaction-number').innerText = data.transaction_number;
                document.getElementById('receipt-guest-name').innerText = `${data.first_name} ${data.last_name}`;
                document.getElementById('receipt-guest-email').innerText = data.email;
                document.getElementById('receipt-guest-count').innerText = data.guest_no;
                document.getElementById('receipt-event-name').innerText = data.event_name;
                document.getElementById('receipt-event-date').innerText = data.event_date;
                document.getElementById('receipt-event-time').innerText = `${formatTime(data.event_time_start)} - ${formatTime(data.event_time_end)}`;
                document.getElementById('receipt-transaction-date').innerText = data.transaction_date;
                document.getElementById('receipt-total-amount').innerText = number_format(data.total_amount, 2);
                document.getElementById('print-preview').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching transaction details:', error);
                alert('An error occurred while fetching transaction details. Please try again later.');
            });
    }

    function printReceipt() {
        const printContent = document.getElementById('print-content').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = `<div class="print-preview-content">${printContent}</div>`;
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }

    document.querySelector('.print-preview button').addEventListener('click', printReceipt);
</script>