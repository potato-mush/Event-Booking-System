<div class="bookings-container">
    <h1>Bookings Management</h1>
    
    <div class="booking-filters">
        <input type="text" placeholder="Search bookings..." class="search-input">
        <select class="filter-select">
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Event Date</th>
                <th>Event Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Booking rows will be populated here -->
        </tbody>
    </table>
</div>
