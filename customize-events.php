<?php

// Check if the user is logged in
if (!isset($_SESSION['user_username'])) {
    header('Location: login.php');
    exit();
}

require 'include/db_connection.php';

function getOptions($category) {
    global $conn;
    $stmt = $conn->prepare("SELECT option_name, price FROM event_options WHERE category = ?");
    $stmt->execute([$category]);
    $options = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $options[] = ['name' => $row['option_name'], 'price' => $row['price']];
    }
    return $options;
}

$seatingOptions = getOptions('seating-arrangement');
$menuOptions = getOptions('menu-type');
$serviceOptions = getOptions('additional-services');
$entertainmentOptions = getOptions('preferred-entertainment');
$eventTypeOptions = getOptions('event-type');
$decorationOptions = getOptions('decoration');
?>

<form class="customize-event-form" action="index.php?page=receipt" method="POST" onsubmit="return validateForm()">
    <!-- Event Name, Date, and Time grouped together -->
    <div class="form-group">
        <h3 for="event-name">Event Name/Title</h3>
        <input type="text" id="event-name" name="event-name" required>

        <h3 for="event-date">Event Date</h3>
        <input type="date" id="event-date" name="event-date" required>

        <h3>Event Time</h3>
        <div class="time-group">
            <label for="event-time-start">Start</label>
            <input type="time" id="event-time-start" name="event-time-start" required>

            <label for="event-time-end">End</label>
            <input type="time" id="event-time-end" name="event-time-end" required>
        </div>
    </div>

    <!-- Event Theme and Number of Guests grouped together -->
    <div class="form-group">
        <h3 for="event-theme">Event Theme</h3>
        <input type="text" id="event-theme" name="event-theme" required>

        <h3 for="number-of-guests">Number of Guests</h3>
        <input type="number" id="number-of-guests" name="number-of-guests" min="1" max="100" required>
    </div>

    <!-- Seating Arrangement -->
    <div class="form-group">
        <h3>Seating Arrangement</h3>
        <div class="radio-group">
            <?php foreach ($seatingOptions as $option): ?>
                <div>
                    <input type="radio" id="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" name="seating-arrangement" value="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" required>
                    <label for="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>"><?= $option['name'] ?></label>
                </div>
            <?php endforeach; ?>
            <div>
                <input type="radio" id="custom-seating-arrangement" name="seating-arrangement" value="custom">
                <label for="custom-seating-arrangement">Custom</label>
                <input type="text" id="custom-seating-arrangement-input" name="custom-seating-arrangement" placeholder="Enter custom seating arrangement">
            </div>
        </div>
    </div>

    <!-- Menu Type -->
    <div class="form-group">
        <h3>Menu Type</h3>
        <div class="radio-group">
            <?php foreach ($menuOptions as $option): ?>
                <div>
                    <input type="radio" id="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" name="menu-type" value="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" required>
                    <label for="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>"><?= $option['name'] ?></label>
                </div>
            <?php endforeach; ?>
            <div>
                <input type="radio" id="custom-menu-type" name="menu-type" value="custom">
                <label for="custom-menu-type">Custom</label>
                <input type="text" id="custom-menu-type-input" name="custom-menu-type" placeholder="Enter custom menu type">
            </div>
        </div>
    </div>

    <!-- Additional Services -->
    <div class="form-group">
        <h3>Additional Services</h3>
        <div class="radio-group">
            <?php foreach ($serviceOptions as $option): ?>
                <div>
                    <input type="radio" id="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" name="additional-services" value="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" required>
                    <label for="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>"><?= $option['name'] ?></label>
                </div>
            <?php endforeach; ?>
            <div>
                <input type="radio" id="custom-additional-services" name="additional-services" value="custom">
                <label for="custom-additional-services">Custom</label>
                <input type="text" id="custom-additional-services-input" name="custom-additional-services" placeholder="Enter custom additional service">
            </div>
        </div>
    </div>

    <!-- Preferred Entertainment -->
    <div class="form-group">
        <h3>Preferred Entertainment</h3>
        <div class="radio-group">
            <?php foreach ($entertainmentOptions as $option): ?>
                <div>
                    <input type="radio" id="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" name="preferred-entertainment" value="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" required>
                    <label for="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>"><?= $option['name'] ?></label>
                </div>
            <?php endforeach; ?>
            <div>
                <input type="radio" id="custom-preferred-entertainment" name="preferred-entertainment" value="custom">
                <label for="custom-preferred-entertainment">Custom</label>
                <input type="text" id="custom-preferred-entertainment-input" name="custom-preferred-entertainment" placeholder="Enter custom preferred entertainment">
            </div>
        </div>
    </div>

    <!-- Event Type -->
    <div class="form-group">
        <h3>Event Type</h3>
        <div class="radio-group">
            <?php foreach ($eventTypeOptions as $option): ?>
                <div>
                    <input type="radio" id="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" name="event-type" value="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" required>
                    <label for="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>"><?= $option['name'] ?></label>
                </div>
            <?php endforeach; ?>
            <div>
                <input type="radio" id="custom-event-type" name="event-type" value="custom">
                <label for="custom-event-type">Custom</label>
                <input type="text" id="custom-event-type-input" name="custom-event-type" placeholder="Enter custom event type">
            </div>
        </div>
    </div>

    <!-- Decoration -->
    <div class="form-group">
        <h3>Decoration</h3>
        <div class="radio-group">
            <?php foreach ($decorationOptions as $option): ?>
                <div>
                    <input type="radio" id="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" name="decoration" value="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>" required>
                    <label for="<?= strtolower(str_replace(' ', '-', $option['name'])) ?>"><?= $option['name'] ?></label>
                </div>
            <?php endforeach; ?>
            <div>
                <input type="radio" id="custom-decoration" name="decoration" value="custom">
                <label for="custom-decoration">Custom</label>
                <input type="text" id="custom-decoration-input" name="custom-decoration" placeholder="Enter custom decoration">
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit">Confirm</button>
</form>

<script>
function validateForm() {
    const requiredFields = document.querySelectorAll('input[required]');
    for (let field of requiredFields) {
        if (!field.value) {
            alert('Please complete all required fields.');
            return false;
        }
    }

    const startTime = document.getElementById('event-time-start').value;
    const endTime = document.getElementById('event-time-end').value;

    if (startTime && endTime) {
        const start = new Date(`1970-01-01T${startTime}:00`);
        const end = new Date(`1970-01-01T${endTime}:00`);
        const diff = (end - start) / (1000 * 60 * 60); // Difference in hours

        if (diff > 3) {
            alert('The event duration cannot exceed 3 hours.');
            return false;
        }
    }

    return true;
}
</script>