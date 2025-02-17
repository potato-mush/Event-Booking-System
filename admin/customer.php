<div class="customers-container">
    <h1>Customer Management</h1>
    
    <div class="customer-actions">
        <input type="text" placeholder="Search customers..." class="search-input">
        <select class="filter-select">
            <option value="all">All Users</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <table class="customers-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name & Email</th>
                <th>Username</th>
                <th>Address</th>
                <th>Phone Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="customers-tbody">
            <!-- Customer rows will be populated here -->
        </tbody>
    </table>
    <div class="no-matches" style="display: none; text-align: center; color: #999;">No matches found</div>
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

    function fetchCustomers() {
        const search = $('.search-input').val();
        const filter = $('.filter-select').val();
        
        $.ajax({
            url: 'includes/fetch_customers.php',
            method: 'GET',
            data: { search: search, filter: filter, page: currentPage },
            success: function(response) {
                $('#customers-tbody').html(response);
                if ($('#customers-tbody').children().length === 0) {
                    $('.no-matches').show();
                } else {
                    $('.no-matches').hide();
                }
                updatePagination();
            }
        });
    }

    function updatePagination() {
        $.ajax({
            url: 'includes/fetch_total_customer_pages.php',
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

    $('.search-input').on('input', fetchCustomers);
    $('.filter-select').on('change', fetchCustomers);

    $('.prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            fetchCustomers();
        }
    });

    $('.next-page').on('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            fetchCustomers();
        }
    });

    fetchCustomers();
});
</script>
