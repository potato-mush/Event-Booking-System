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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal-content {
    max-width: 600px;
    margin: 5% auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.event-details {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
}

.event-details p {
    margin: 10px 0;
    line-height: 1.6;
}

.cancel-event-btn:hover {
    background-color: #c0392b !important;
}

.close-modal {
    float: right;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #666;
}

.close-modal:hover {
    color: #333;
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

        // Show/hide cancel button based on event status
        const cancelBtn = document.getElementById('cancelEventBtn');
        if (event.extendedProps.status === 'PENDING') {
            cancelBtn.style.display = 'block';
            cancelBtn.onclick = () => cancelEvent(event.id);
        } else {
            cancelBtn.style.display = 'none';
        }

        modal.style.display = 'block';
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
