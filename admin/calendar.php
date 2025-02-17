<div class="calendar-container">
    <h1>Event Calendar</h1>
    <div class="legend">
        <span class="legend-item"><span class="legend-color pending"></span> Pending</span>
        <span class="legend-item"><span class="legend-color confirmed"></span> Confirmed</span>
        <span class="legend-item"><span class="legend-color cancelled"></span> Cancelled</span>
    </div>
    <div id="calendar"></div>

    <!-- View/Edit Event Modal -->
    <div id="viewEventModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="viewEventContent">
                <h3>Event Details</h3>
                <form id="editEventForm">
                    <input type="hidden" id="editEventId" name="eventId">
                    <div class="form-group">
                        <label for="editEventTitle">Event Title</label>
                        <input type="text" id="editEventTitle" name="eventTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="editEventStartTime">Start Time</label>
                        <input type="time" id="editEventStartTime" name="eventStartTime" required>
                    </div>
                    <div class="form-group">
                        <label for="editEventEndTime">End Time</label>
                        <input type="time" id="editEventEndTime" name="eventEndTime" required>
                    </div>
                    <div class="form-group">
                        <label for="editEventType">Event Type</label>
                        <select id="editEventType" name="eventType" required>
                            <option value="wedding">Wedding</option>
                            <option value="birthday">Birthday</option>
                            <option value="corporate">Corporate Event</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editEventTheme">Event Theme</label>
                        <input type="text" id="editEventTheme" name="eventTheme">
                    </div>
                    <div class="form-group">
                        <label for="editMenuType">Menu Type</label>
                        <input type="text" id="editMenuType" name="menuType">
                    </div>
                    <div class="form-group">
                        <label for="editGuestNo">Number of Guests</label>
                        <input type="number" id="editGuestNo" name="guestNo">
                    </div>
                    <div class="form-group">
                        <label for="editSeatingArrangement">Seating Arrangement</label>
                        <input type="text" id="editSeatingArrangement" name="seatingArrangement">
                    </div>
                    <div class="form-group">
                        <label for="editEntertainment">Preferred Entertainment</label>
                        <input type="text" id="editEntertainment" name="entertainment">
                    </div>
                    <div class="form-group">
                        <label for="editDecoration">Decoration Preferences</label>
                        <input type="text" id="editDecoration" name="decoration">
                    </div>
                    <div class="form-group">
                        <label for="editAdditionalServices">Additional Services</label>
                        <input type="text" id="editAdditionalServices" name="additionalServices">
                    </div>
                    <button type="submit" class="save-event-btn">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="addEventModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Add Event</h3>
            <form id="eventForm">
                <div class="form-group">
                    <label for="eventTitle">Event Title</label>
                    <input type="text" id="eventTitle" name="eventTitle" required>
                </div>
                <div class="form-group">
                    <label for="eventStartTime">Start Time</label>
                    <input type="time" id="eventStartTime" name="eventStartTime" required>
                </div>
                <div class="form-group">
                    <label for="eventEndTime">End Time</label>
                    <input type="time" id="eventEndTime" name="eventEndTime" required>
                </div>
                <div class="form-group">
                    <label for="eventType">Event Type</label>
                    <select id="eventType" name="eventType" required>
                        <option value="wedding">Wedding</option>
                        <option value="birthday">Birthday</option>
                        <option value="corporate">Corporate Event</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="eventTheme">Event Theme</label>
                    <input type="text" id="eventTheme" name="eventTheme">
                </div>
                <div class="form-group">
                    <label for="menuType">Menu Type</label>
                    <input type="text" id="menuType" name="menuType">
                </div>
                <div class="form-group">
                    <label for="guestNo">Number of Guests</label>
                    <input type="number" id="guestNo" name="guestNo">
                </div>
                <div class="form-group">
                    <label for="seatingArrangement">Seating Arrangement</label>
                    <input type="text" id="seatingArrangement" name="seatingArrangement">
                </div>
                <div class="form-group">
                    <label for="entertainment">Preferred Entertainment</label>
                    <input type="text" id="entertainment" name="entertainment">
                </div>
                <div class="form-group">
                    <label for="decoration">Decoration Preferences</label>
                    <input type="text" id="decoration" name="decoration">
                </div>
                <div class="form-group">
                    <label for="additionalServices">Additional Services</label>
                    <input type="text" id="additionalServices" name="additionalServices">
                </div>
                <button type="submit" class="save-event-btn">Save Event</button>
            </form>
        </div>
    </div>
</div>

<!-- Updated FullCalendar Dependencies -->
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
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        eventDisplay: 'block',
        displayEventTime: true,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault(); // Prevent the browser from following the link in the event
            hidePopover();
            showEventDetails(info.event);
        },
        dateClick: function(info) {
            if (new Date(info.dateStr) >= new Date()) {
                showAddEventForm(info.dateStr);
            } else {
                alert('Cannot add events to past dates.');
            }
        },
        events: 'includes/load_bookings.php',  // Update path to match your file structure
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
        const editForm = document.getElementById('editEventForm');

        // Populate the form with event details
        document.getElementById('editEventId').value = event.id;
        document.getElementById('editEventTitle').value = event.title;
        document.getElementById('editEventStartTime').value = convertTo24HourFormat(event.start);
        document.getElementById('editEventEndTime').value = convertTo24HourFormat(event.end);
        document.getElementById('editEventType').value = event.extendedProps.event_type;
        document.getElementById('editEventTheme').value = event.extendedProps.event_theme || '';
        document.getElementById('editMenuType').value = event.extendedProps.menu_type || '';
        document.getElementById('editGuestNo').value = event.extendedProps.guest_no || '';
        document.getElementById('editSeatingArrangement').value = event.extendedProps.seating_arrangement || '';
        document.getElementById('editEntertainment').value = event.extendedProps.entertainment || '';
        document.getElementById('editDecoration').value = event.extendedProps.decoration || '';
        document.getElementById('editAdditionalServices').value = event.extendedProps.additional_services || '';

        modal.style.display = 'block';
    }

    function showAddEventForm(dateStr) {
        const modal = document.getElementById('addEventModal');
        const addForm = document.getElementById('eventForm');

        if (addForm) addForm.style.display = 'block';
        modal.style.display = 'block';

        // Set the event date in a hidden input
        const eventDateInput = document.createElement('input');
        eventDateInput.type = 'hidden';
        eventDateInput.name = 'eventDate';
        eventDateInput.value = dateStr;
        addForm.appendChild(eventDateInput);
    }

    function checkEventAvailability(dateStr, startTime, endTime) {
        return fetch('includes/check_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ eventDate: dateStr, eventStartTime: startTime, eventEndTime: endTime })
        })
        .then(response => response.json());
    }

    function hidePopover() {
        const popover = document.querySelector('.fc-popover');
        if (popover) {
            popover.style.display = 'none';
        }
    }

    function convertTo24HourFormat(date) {
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // Modal handling
    const viewModal = document.getElementById('viewEventModal');
    const addModal = document.getElementById('addEventModal');
    const closeBtns = document.getElementsByClassName('close-modal');
    
    for (let btn of closeBtns) {
        btn.onclick = function() {
            viewModal.style.display = 'none';
            addModal.style.display = 'none';
        }
    }

    window.onclick = function(event) {
        if (event.target == viewModal) {
            viewModal.style.display = 'none';
        }
        if (event.target == addModal) {
            addModal.style.display = 'none';
        }
    }

    // Form handling
    document.getElementById('eventForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(e.target);

        checkEventAvailability(formData.get('eventDate'), formData.get('eventStartTime'), formData.get('eventEndTime'))
        .then(data => {
            if (data.status === 'available') {
                fetch('includes/save_bookings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Reload the page to show the newly added event
                        location.reload();
                    } else {
                        alert('Failed to save event: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to save event');
                });
            } else {
                alert('Time slot is already booked.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to check event availability');
        });
    }

    document.getElementById('editEventForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(e.target);

        fetch('includes/edit_bookings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Reload the page to show the updated event
                location.reload();
            } else {
                alert('Failed to update event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update event');
        });
    }
});
</script>
