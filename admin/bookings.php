
<div class="bookings-container">
    <h1>Bookings Management</h1>
    
    <div class="booking-filters">
        <input type="text" placeholder="Search bookings..." class="search-input">
        <select class="filter-select">
            <option value="all">All Status</option>
            <option value="PENDING">Pending</option>
            <option value="CONFIRMED">Confirmed</option>
            <option value="CANCELLED">Cancelled</option>
        </select>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Event Name</th>
                <th>Event Date</th>
                <th>Event Type</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="bookings-tbody">
            <?php
            require_once 'includes/db_connection.php';
            // Fetch bookings and user details from the database
            $query = "SELECT booking.id, users.first_name, users.last_name, booking.event_date, booking.event_type, booking.event_name, booking.event_time_start, booking.event_time_end, booking.status 
                      FROM booking 
                      JOIN users ON booking.user_id = users.id
                      LIMIT 5";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $row) {
                $customerName = $row['first_name'] . ' ' . $row['last_name'];
                $startTime = new DateTime($row['event_time_start']);
                $endTime = new DateTime($row['event_time_end']);
                $duration = $startTime->diff($endTime)->format('%h hour(s)');
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$customerName}</td>
                        <td>{$row['event_date']}</td>
                        <td>{$row['event_type']}</td>
                        <td>{$row['event_name']}</td>
                        <td>{$duration}</td>
                        <td><span class='status-badge status-{$row['status']}'>{$row['status']}</span></td>
                        <td>";
                if ($row['status'] == 'PENDING') {
                    echo "<button class='confirm-btn' data-id='{$row['id']}'><i class='fas fa-check-circle'></i></button>
                          <button class='cancel-btn' data-id='{$row['id']}'><i class='fas fa-times-circle'></i></button>";
                } else {
                    echo "<button class='pending-btn' data-id='{$row['id']}'><i class='fas fa-minus-circle'></i></button>";
                }
                echo "</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="pagination">
        <button class="prev-page">Previous</button>
        <span class="page-info">Page <span id="current-page">1</span> of <span id="total-pages">1</span></span>
        <button class="next-page">Next</button>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentPage = 1;
    let totalPages = 1;

    function fetchBookings() {
        const search = $('.search-input').val();
        const filter = $('.filter-select').val();
        
        $.ajax({
            url: 'includes/fetch_bookings.php',
            method: 'GET',
            data: { search: search, filter: filter, page: currentPage },
            success: function(response) {
                $('#bookings-tbody').html(response);
                updatePagination();
            }
        });
    }

    function updatePagination() {
        $.ajax({
            url: 'includes/fetch_total_pages.php',
            method: 'GET',
            data: { search: $('.search-input').val(), filter: $('.filter-select').val() },
            success: function(response) {
                totalPages = parseInt(response);
                $('#current-page').text(currentPage);
                $('#total-pages').text(totalPages);
                $('.prev-page').prop('disabled', currentPage <= 1);
                $('.next-page').prop('disabled', currentPage >= totalPages);
            }
        });
    }

    $('.search-input').on('input', fetchBookings);
    $('.filter-select').on('change', fetchBookings);

    $(document).on('click', '.confirm-btn', function() {
        const bookingId = $(this).data('id');
        $.ajax({
            url: 'includes/update_booking_status.php',
            method: 'POST',
            data: { id: bookingId, status: 'CONFIRMED' },
            success: function() {
                fetchBookings();
            }
        });
    });

    $(document).on('click', '.cancel-btn', function() {
        const bookingId = $(this).data('id');
        $.ajax({
            url: 'includes/update_booking_status.php',
            method: 'POST',
            data: { id: bookingId, status: 'CANCELLED' },
            success: function() {
                fetchBookings();
            }
        });
    });

    $(document).on('click', '.pending-btn', function() {
        const bookingId = $(this).data('id');
        $.ajax({
            url: 'includes/update_booking_status.php',
            method: 'POST',
            data: { id: bookingId, status: 'PENDING' },
            success: function() {
                fetchBookings();
            }
        });
    });

    $('.prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            fetchBookings();
        }
    });

    $('.next-page').on('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            fetchBookings();
        }
    });

    fetchBookings();
});
</script>