<div class="calendar-container"  style="padding: 40px;">
    <h1>My Bookings</h1>
    <div id="calendar"></div>

    <!-- View Event Modal -->
    <div id="viewEventModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="viewEventContent">
                <h3 style="color: #333; margin-bottom: 20px; text-align: center;">Event Details</h3>
                <div class="event-details" style="margin-bottom: 30px;">
                    <p><strong>Event Title:</strong> <span id="eventTitle"></span></p>
                    <p><strong>Date:</strong> <span id="eventDate"></span></p>
                    <p><strong>Time:</strong> <span id="eventTime"></span></p>
                    <p><strong>Event Type:</strong> <span id="eventType"></span></p>
                    <p><strong>Status:</strong> <span id="eventStatus"></span></p>
                    <p><strong>Theme:</strong> <span id="eventTheme"></span></p>
                    <p><strong>Menu Type:</strong> <span id="menuType"></span></p>
                    <p><strong>Number of Guests:</strong> <span id="guestNo"></span></p>
                    <p><strong>Seating Arrangement:</strong> <span id="seatingArrangement"></span></p>
                    <p><strong>Entertainment:</strong> <span id="entertainment"></span></p>
                    <p><strong>Decoration:</strong> <span id="decoration"></span></p>
                    <p><strong>Additional Services:</strong> <span id="additionalServices"></span></p>
                    <p><strong>Payment Status:</strong> <span id="paymentStatus"></span></p>
                    <p><strong>Amount Paid:</strong> <span id="amountPaid"></span></p>
                    <p><strong>Reference Number:</strong> <span id="referenceNumber"></span></p>
                </div>
                <div style="text-align: center; padding: 20px 0;">
                    <button id="cancelEventBtn" class="cancel-event-btn" style="
                        background-color: #e74c3c;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        transition: background-color 0.3s ease;
                    ">Cancel Booking</button>
                    <button id="printEventBtn" class="print-event-btn" style="
                        background-color: #3498db;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        margin-left: 10px;
                        transition: background-color 0.3s ease;
                        display: none;
                    ">Print Booking</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.close-modal {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #666;
    z-index: 1;
}

.modal-content {
    position: relative;
}
</style>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: false,
        selectable: false,
        dayMaxEvents: true,
        eventDisplay: 'block',
        displayEventTime: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showEventDetails(info.event);
        },
        events: function(info, successCallback, failureCallback) {
            fetch('include/load_user_bookings.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Loaded events:', data);
                    if (data.error) {
                        console.error('Error loading events:', data.error);
                        failureCallback(data.error);
                    } else {
                        successCallback(data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        eventDidMount: function(info) {
            if (info.event.extendedProps.status === 'PENDING') {
                info.el.style.backgroundColor = '#f39c12';
                info.el.style.borderColor = '#f39c12';
            } else if (info.event.extendedProps.status === 'CONFIRMED') {
                info.el.style.backgroundColor = '#2ecc71';
                info.el.style.borderColor = '#2ecc71';
            } else if (info.event.extendedProps.status === 'CANCELLED') {
                info.el.style.backgroundColor = '#e74c3c';
                info.el.style.borderColor = '#e74c3c';
            }
        }
    });
    calendar.render();

    function showEventDetails(event) {
        const modal = document.getElementById('viewEventModal');
        
        // Populate event details
        document.getElementById('eventTitle').textContent = event.title;
        document.getElementById('eventDate').textContent = event.start.toLocaleDateString();
        document.getElementById('eventTime').textContent = `${event.start.toLocaleTimeString()} - ${event.end.toLocaleTimeString()}`;
        document.getElementById('eventType').textContent = event.extendedProps.event_type;
        document.getElementById('eventStatus').textContent = event.extendedProps.status;
        document.getElementById('eventTheme').textContent = event.extendedProps.event_theme || 'N/A';
        document.getElementById('menuType').textContent = event.extendedProps.menu_type || 'N/A';
        document.getElementById('guestNo').textContent = event.extendedProps.guest_no || 'N/A';
        document.getElementById('seatingArrangement').textContent = event.extendedProps.seating_arrangement || 'N/A';
        document.getElementById('entertainment').textContent = event.extendedProps.entertainment || 'N/A';
        document.getElementById('decoration').textContent = event.extendedProps.decoration || 'N/A';
        document.getElementById('additionalServices').textContent = event.extendedProps.additional_services || 'N/A';

        // Add payment information
        const transaction = event.extendedProps.transaction;
        if (transaction) {
            const total = parseFloat(transaction.amount) || 0;
            const status = transaction.status || 'UNPAID';
            const halfAmount = total / 2;
            
            let paymentText, amountPaidText;
            
            switch(status) {
                case 'PARTIALLY PAID':
                    paymentText = `Partially Paid`;
                    amountPaidText = `₱${halfAmount.toFixed(2)}`;
                    break;
                case 'PAID':
                    paymentText = 'FULLY PAID';
                    amountPaidText = `₱${total.toFixed(2)}`;
                    break;
                case 'CANCELLED':
                    paymentText = 'CANCELLED';
                    amountPaidText = '₱0.00';
                    break;
                default:
                    paymentText = 'UNPAID';
                    amountPaidText = '₱0.00';
            }
            
            document.getElementById('paymentStatus').textContent = paymentText;
            document.getElementById('amountPaid').textContent = amountPaidText;
            document.getElementById('referenceNumber').textContent = transaction.reference || 'N/A';
        } else {
            document.getElementById('paymentStatus').textContent = 'No payment information';
            document.getElementById('amountPaid').textContent = 'N/A';
            document.getElementById('referenceNumber').textContent = 'N/A';
        }

        // Show/hide buttons based on event status
        const cancelBtn = document.getElementById('cancelEventBtn');
        const printBtn = document.getElementById('printEventBtn');
        
        if (event.extendedProps.status === 'PENDING') {
            cancelBtn.style.display = 'inline-block';
            printBtn.style.display = 'none';
            cancelBtn.onclick = () => cancelEvent(event.id);
        } else if (event.extendedProps.status === 'CONFIRMED') {
            cancelBtn.style.display = 'none';
            printBtn.style.display = 'inline-block';
            printBtn.onclick = () => printBooking(event);
        } else {
            cancelBtn.style.display = 'none';
            printBtn.style.display = 'none';
        }

        modal.style.display = 'block';
    }

    function printBooking(event) {
        const transaction = event.extendedProps.transaction;
        const total = parseFloat(transaction.amount) || 0;
        const halfAmount = total / 2;

        // Create print content with styles included
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Event Receipt</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    .print-preview-content {
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                    }
                    .receipt-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10px;
                        padding: 5px 0;
                        border-bottom: 1px dotted #ddd;
                    }
                    h3 {
                        margin: 20px 0 10px;
                        color: #333;
                    }
                    @media print {
                        .no-print {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="print-preview-content">
                    <div id="receipt-logo">
                        <img src="assets/images/logo.png" alt="Logo" style="width: 150px; display: block; margin: 0 auto;">
                    </div>
                    <h3 style="text-align: center;">Event Payment Receipt</h3>
                    <div style="margin-bottom: 20px;">
                        <p style="text-align: left; float: left;"><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                        <p style="text-align: right;"><strong>Transaction Number:</strong> ${transaction.number}</p>
                    </div>
                    <div style="clear: both;"></div>
                    <div id="receipt-details">
                        <h3>Guest Information</h3>
                        <div class="receipt-row">
                            <span class="receipt-label">Guest Name:</span>
                            <span class="receipt-value">${event.title}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Number of Guests:</span>
                            <span class="receipt-value">${event.extendedProps.guest_no}</span>
                        </div>
                        <h3>Event Information</h3>
                        <div class="receipt-row">
                            <span class="receipt-label">Event Type:</span>
                            <span class="receipt-value">${event.extendedProps.event_type}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Event Date:</span>
                            <span class="receipt-value">${event.start.toLocaleDateString()}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Event Time:</span>
                            <span class="receipt-value">${event.start.toLocaleTimeString()} - ${event.end.toLocaleTimeString()}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Payment Status:</span>
                            <span class="receipt-value">${transaction.status}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Amount Paid:</span>
                            <span class="receipt-value">₱${transaction.status === 'PARTIALLY PAID' ? halfAmount.toFixed(2) : total.toFixed(2)}</span>
                        </div>
                        <div class="receipt-row">
                            <span class="receipt-label">Total Amount:</span>
                            <span class="receipt-value">₱${total.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
                <div class="no-print" style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Receipt</button>
                </div>
            </body>
            </html>
        `;

        // Open in new tab and write content
        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
    }

    function cancelEvent(eventId) {
        if (confirm('Are you sure you want to cancel this booking?')) {
            fetch('include/cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ eventId: eventId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Failed to cancel booking: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to cancel booking');
            });
        }
    }

    // Modal close handling
    const modal = document.getElementById('viewEventModal');
    const closeBtn = document.getElementsByClassName('close-modal')[0];
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});
</script>
