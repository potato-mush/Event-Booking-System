<?php

// Check if the user is logged in
if (!isset($_SESSION['user_username'])) {
    header('Location: login.php');
    exit();
}

require 'include/db_connection.php';

function getOptions($category)
{
    global $conn;
    $stmt = $conn->prepare("SELECT option_name, price FROM event_options WHERE category = ?");
    $stmt->execute([$category]);
    $options = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $options[] = ['name' => $row['option_name'], 'price' => $row['price']];
    }
    return $options;
}

function getFullCourseMenu()
{
    global $conn;
    $stmt = $conn->prepare("SELECT title, main_course, salad, appetizer, dessert, drinks, price FROM menu");
    $stmt->execute();
    $menu = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $menu[] = $row;
    }
    return $menu;
}

$seatingOptions = getOptions('seating-arrangement');
$menuOptions = getOptions('menu-type');
$serviceOptions = getOptions('additional-services');
$entertainmentOptions = getOptions('preferred-entertainment');
$eventTypeOptions = getOptions('event-type');
$decorationOptions = getOptions('decoration');
$fullCourseMenu = getFullCourseMenu();

?>

<form class="customize-event-form" method="POST">  <!-- Remove action attribute -->
    <!-- Event Name, Date, and Time grouped together -->
    <div class="form-group">
        <h3 for="event-name">Event Name/Title</h3>
        <input type="text" id="event-name" name="event-name" required>

        <h3 for="event-date">Event Date</h3>
        <input type="date" id="event-date" name="event-date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>

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

    <!-- Full Course Meal Menu -->
    <div class="full-course-menu" style="flex: 1 1 100%;">
        <h3>Full Course Meal Menu</h3>
        <div class="menu-list" style="width: 100%;">
            <?php foreach ($fullCourseMenu as $index => $menu): ?>
                <div class="menu-item">
                    <input type="radio" id="menu-<?= $index ?>" name="full-course-menu" value="<?= $menu['price'] ?>" data-title="<?= $menu['title'] ?>" required>
                    <label for="menu-<?= $index ?>">
                        <strong><?= $menu['title'] ?></strong> <br>
                        <strong>Main Course:</strong> <?= $menu['main_course'] ?><br>
                        <strong>Salad:</strong> <?= $menu['salad'] ?><br>
                        <strong>Appetizer:</strong> <?= $menu['appetizer'] ?><br>
                        <strong>Dessert:</strong> <?= $menu['dessert'] ?><br>
                        <strong>Drinks:</strong> <?= $menu['drinks'] ?><br>
                        <strong>Price per Cover:</strong> ₱<?= $menu['price'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add this hidden input before the submit button -->
    <input type="hidden" name="menu-title" id="menu-title">
    <!-- Submit Button -->
    <button type="submit" id="submitForm">Confirm</button>
</form>

<!-- Error Modal -->
<?php if (isset($_SESSION['show_modal']) && $_SESSION['show_modal']): ?>
<div class="modal" id="errorModal" style="display: block; z-index: 1001;">
    <div class="modal-content">
        <h2>Booking Error</h2>
        <p><?php echo $_SESSION['modal_message']; ?></p>
        <div class="button-group">
            <button type="button" onclick="document.getElementById('errorModal').style.display='none'">Close</button>
        </div>
    </div>
</div>
<?php 
    unset($_SESSION['show_modal']);
    unset($_SESSION['modal_message']);
endif; 
?>

<!-- Payment Modal -->
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <h2>Down Payment Required</h2>
        <p>Total Amount: ₱<span id="totalAmount">0.00</span></p>
        <p>Required Down Payment (50%): ₱<span id="downPayment">0.00</span></p>
        <p>Please scan the QR code below to pay the down payment</p>
        <img src="assets/images/qrCode.jpg" alt="Payment QR Code" style="width: 200px; height: 200px;">
        <div class="form-group" style="width: 100%; box-sizing: border-box;">
            <label for="reference-number">Reference Number:</label>
            <input type="text" id="reference-number" pattern=".{13,13}" maxlength="13"
                style="width: 100%; box-sizing: border-box;"
                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                title="Reference number must be exactly 13 numbers long">
        </div>
        <div class="button-group">
            <button type="submit" id="confirmPayment">Confirm Payment</button>
            <button type="button" onclick="closePaymentModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
    // Add this to your existing JavaScript
    document.querySelectorAll('input[name="full-course-menu"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('menu-title').value = this.getAttribute('data-title');
        });
    });

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

            if (diff < 1) {
                alert('The event duration must be at least 1 hour.');
                return false;
            }

            if (diff > 3) {
                alert('The event duration cannot exceed 3 hours.');
                return false;
            }
        }

        return true;
    }

    // Update this function to calculate total price
    function calculateTotal() {
        let total = 0;
        // Get prices from all selected radio options
        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            // Get the option name without any custom prefix
            const optionName = radio.id.replace('custom-', '');
            
            // Get the price from the data attribute or calculate menu price
            if (radio.name === 'full-course-menu') {
                const guestCount = parseInt(document.getElementById('number-of-guests').value) || 0;
                total += parseFloat(radio.value) * guestCount;
            } else {
                // For other options, get their prices from PHP-generated data
                const prices = <?php echo json_encode([
                    'seating-arrangement' => array_column($seatingOptions, 'price', 'name'),
                    'menu-type' => array_column($menuOptions, 'price', 'name'),
                    'additional-services' => array_column($serviceOptions, 'price', 'name'),
                    'preferred-entertainment' => array_column($entertainmentOptions, 'price', 'name'),
                    'event-type' => array_column($eventTypeOptions, 'price', 'name'),
                    'decoration' => array_column($decorationOptions, 'price', 'name'),
                ]); ?>;
                
                // Get the normalized option name
                const category = radio.name;
                const selectedOption = document.querySelector(`label[for="${radio.id}"]`).textContent;
                
                // Add the price if it exists in our prices object
                if (prices[category] && prices[category][selectedOption]) {
                    total += parseFloat(prices[category][selectedOption]);
                }
            }
        });
        return total;
    }

    // Add this function to create error modal dynamically
    function showErrorModal(message) {
        // Remove existing error modal if any
        const existingModal = document.getElementById('errorModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create new error modal
        const modalHtml = `
            <div class="modal" id="errorModal" style="display: block; z-index: 1001;">
                <div class="modal-content">
                    <h2>Booking Error</h2>
                    <p>${message}</p>
                    <div class="button-group">
                        <button type="button" onclick="document.getElementById('errorModal').style.display='none'">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    // Modify the form submission
    document.querySelector('.customize-event-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) return;

        // First check for date and time conflicts
        const formData = new FormData(this);
        try {
            const checkResponse = await fetch('include/check_availability.php', {
                method: 'POST',
                body: formData
            });
            
            if (!checkResponse.ok) {
                throw new Error('Network response was not ok');
            }

            const checkResult = await checkResponse.json();
            if (checkResult.status === 'error') {
                showErrorModal(checkResult.message);
                return; // Stop here if there's an error
            }

            // Only proceed to payment modal if there are no errors
            const totalAmount = calculateTotal();
            const downPayment = totalAmount * 0.5;
            
            document.getElementById('totalAmount').textContent = totalAmount.toLocaleString('en-PH', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('downPayment').textContent = downPayment.toLocaleString('en-PH', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.getElementById('payment-modal').style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            showErrorModal('An error occurred while checking availability. Please try again.');
        }
    });

    // Replace confirmPayment event listener
    document.getElementById('confirmPayment').addEventListener('click', async function() {
        const refNumber = document.getElementById('reference-number').value;
        if (refNumber.length !== 13) {
            alert('Please enter a valid 13-character reference number.');
            return;
        }

        // Disable the confirm button to prevent double submission
        this.disabled = true;

        const form = document.querySelector('.customize-event-form');
        const formData = new FormData(form);
        formData.append('reference-number', refNumber);

        try {
            const response = await fetch('include/confirm_booking.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.status === 'success') {
                // Redirect on success
                window.location.href = 'index.php?page=receipt';
            } else {
                showErrorModal(result.message || 'An error occurred during booking.');
                // Re-enable the button on error
                this.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorModal('An error occurred. Please try again.');
            // Re-enable the button on error
            this.disabled = false;
        }
    });

    // Add this to handle reference number input
    document.getElementById('reference-number').addEventListener('input', function(e) {
        // Remove any non-numeric characters
        this.value = this.value.replace(/[^\d]/g, '');

        // Limit to 13 characters
        if (this.value.length > 13) {
            this.value = this.value.slice(0, 13);
        }
    });

    // Define the closePaymentModal function
    function closePaymentModal() {
        document.getElementById('payment-modal').style.display = 'none';
    }
</script>