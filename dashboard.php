
<div class="container">
    <div class="centered-text">
        <h1>Escape to Paradise</h1>
        <p>Your Ultimate Resort & Event Destination</p>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="index.php?page=catering-packages" class="cta-button">Book Now!</a>
        <?php else: ?>
            <a href="login.php" class="cta-button">Book Now!</a>
        <?php endif; ?>
    </div>

    <div class="images-container">
        <div class="floating-image" data-speed="0.3">
            <img src="assets/images/image1.jpg" alt="Freedom Image 1">
        </div>
        <div class="floating-image" data-speed="0.5">
            <img src="assets/images/image2.jpg" alt="Freedom Image 2">
        </div>
        <div class="floating-image" data-speed="0.8">
            <img src="assets/images/image3.jpg" alt="Freedom Image 3">
        </div>
        <div class="floating-image" data-speed="0.5">
            <img src="assets/images/image4.jpg" alt="Freedom Image 4">
        </div>
        <div class="floating-image" data-speed="0.3">
            <img src="assets/images/image5.jpg" alt="Freedom Image 5">
        </div>
        <div class="floating-image" data-speed="0.5">
            <img src="assets/images/image6.jpg" alt="Freedom Image 6">
        </div>
        <div class="floating-image" data-speed="0.8">
            <img src="assets/images/image7.jpg" alt="Freedom Image 7">
        </div>
        <div class="floating-image" data-speed="0.5">
            <img src="assets/images/image8.jpg" alt="Freedom Image 8">
        </div>
    </div>
</div>