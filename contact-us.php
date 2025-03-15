<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'include/db_connection.php';

function getUserEmail($userId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['email'] : false;
}

function sendEmail($message, $userId)
{
    $senderEmail = getUserEmail($userId);
    if (!$senderEmail) {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'kevinsresort.restaurant@gmail.com'; // Replace with your email
        $mail->Password = 'hjbz umns dzyb eqes'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($senderEmail, $userId); // Set the sender as the user
        $mail->addAddress('kevinsresort.restaurant@gmail.com'); // Restaurant email as recipient

        $mail->isHTML(true);
        $mail->Subject = 'Contact Form Message from ' . $userId;

        // Format the message body
        $messageBody = "
            <p><strong>Message from:</strong> {$userId} ({$senderEmail})</p>
            <hr>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        ";

        $mail->Body = $messageBody;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'];
    $userId = $_SESSION['user_username']; // Using username since that's what we have in session

    if (sendEmail($message, $userId)) {
        echo "<script>alert('Message sent successfully!');</script>";
    } else {
        echo "<script>alert('Failed to send message. Please try again.');</script>";
    }
}
?>
<div class="contact-wrapper">
    <div class="contact-container">
        <h1 class="contact-heading">Contact Us</h1>
        <div class="content-wrapper">
            <div class="message-section">
                <h2>Have a question? Contact us!</h2>
                <form method="POST" class="textarea-container">
                    <textarea name="message" placeholder="Your message here..." rows="9" required></textarea>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
            <div class="contact-icons">
                <!-- Font Awesome icons for phone, landline, and email -->
                <div>
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>(123) 456-7890</span> <!-- Sample phone number -->
                </div>
                <div>
                    <i class="fa-solid fa-phone"></i>
                    <span>(987) 654-3210</span> <!-- Sample landline number -->
                </div>
                <div>
                    <i class="fas fa-envelope" title="Email"></i>
                    <span>kevinsresort.restaurant@gmail.com</span> <!-- Sample email -->
                </div>
                <button class="rate-btn" onclick="openRateModal()">Rate Us</button>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div id="rateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Rate Your Experience</h2>
        <div class="rating">
            <i class="fas fa-star" data-rating="1"></i>
            <i class="fas fa-star" data-rating="2"></i>
            <i class="fas fa-star" data-rating="3"></i>
            <i class="fas fa-star" data-rating="4"></i>
            <i class="fas fa-star" data-rating="5"></i>
        </div>
        <form id="ratingForm">
            <textarea name="feedback" placeholder="Tell us about your experience..." required></textarea>
            <button type="submit">Submit Rating</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('rateModal');
    const stars = document.querySelectorAll('.rating .fa-star');
    let selectedRating = 0;

    function openRateModal() {
        modal.style.display = 'block';
    }

    document.querySelector('.close').onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.dataset.rating;
            highlightStars(rating);
        });

        star.addEventListener('click', function() {
            selectedRating = this.dataset.rating;
            highlightStars(selectedRating);
        });
    });

    function highlightStars(rating) {
        stars.forEach(star => {
            star.style.color = star.dataset.rating <= rating ? '#ffd700' : '#ccc';
        });
    }

    document.getElementById('ratingForm').onsubmit = function(e) {
        e.preventDefault();
        if (selectedRating === 0) {
            alert('Please select a rating');
            return;
        }

        const feedback = document.querySelector('#ratingForm textarea').value;
        const formData = new FormData();
        formData.append('rating', selectedRating);
        formData.append('feedback', feedback);
        formData.append('action', 'submit_rating');

        fetch('include/submit_rating.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for your rating!');
                    modal.style.display = 'none';
                    document.querySelector('#ratingForm textarea').value = '';
                    selectedRating = 0;
                    highlightStars(0);
                } else {
                    alert(data.error || 'Failed to submit rating. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit rating. Please try again.');
            });
    };
</script>