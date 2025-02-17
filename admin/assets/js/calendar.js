let currentDate = new Date();
let events = {};

// Initialize calendar
function initCalendar() {
    showCalendar(currentDate);
    document.getElementById('currentMonthYear').textContent = formatMonth(currentDate);
}

// Format month display
function formatMonth(date) {
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
}

// Show calendar
function showCalendar(date) {
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    const startingDay = firstDay.getDay();
    const monthLength = lastDay.getDate();

    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';

    // Create empty cells for days before start of month
    for (let i = 0; i < startingDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-day empty';
        calendarDays.appendChild(emptyCell);
    }

    // Create cells for days of the month
    for (let day = 1; day <= monthLength; day++) {
        const cell = document.createElement('div');
        cell.className = 'calendar-day';
        
        const dateStr = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
        
        // Add date number
        const dateNumber = document.createElement('span');
        dateNumber.className = 'date-number';
        dateNumber.textContent = day;
        cell.appendChild(dateNumber);

        // Add events for this day if they exist
        if (events[dateStr]) {
            const eventDot = document.createElement('div');
            eventDot.className = 'event-dot';
            cell.appendChild(eventDot);
        }

        // Add click handler to open modal
        cell.addEventListener('click', () => openEventModal(dateStr));
        
        calendarDays.appendChild(cell);
    }
}

// Navigation functions
function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    updateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    updateCalendar();
}

function goToToday() {
    currentDate = new Date();
    updateCalendar();
}

function updateCalendar() {
    document.getElementById('currentMonthYear').textContent = formatMonth(currentDate);
    showCalendar(currentDate);
}

// Modal handling
function openEventModal(date) {
    const modal = document.getElementById('eventModal');
    document.getElementById('eventDate').value = date;
    modal.style.display = 'block';
}

// Event handlers
document.addEventListener('DOMContentLoaded', function() {
    initCalendar();

    // Close modal when clicking (x) or outside
    const modal = document.getElementById('eventModal');
    const closeBtn = document.getElementsByClassName('close-modal')[0];
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Handle form submission
    document.getElementById('eventForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        const eventData = {
            date: formData.get('eventDate'),
            title: formData.get('eventTitle'),
            time: formData.get('eventTime'),
            type: formData.get('eventType')
        };

        // Here you would typically save to database
        saveEvent(eventData);
        
        modal.style.display = 'none';
        updateCalendar();
    }
});

// Save event (mock function - replace with actual API call)
function saveEvent(eventData) {
    if (!events[eventData.date]) {
        events[eventData.date] = [];
    }
    events[eventData.date].push(eventData);
}
