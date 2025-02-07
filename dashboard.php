<div class="dashboard">
    <h1>Let's Make Your Dream Event Happen!</h1>

    <!-- Image Containers -->
    <div class="image-container">
        <div class="slideshow" id="slideshow1">
            <img src="assets/images/image1.jpg" alt="Image 1">
            <img src="assets/images/image2.jpg" alt="Image 2">
            <img src="assets/images/image3.jpg" alt="Image 3">
        </div>
    </div>

    <div class="image-container">
        <div class="slideshow" id="slideshow2">
            <img src="assets/images/image4.jpg" alt="Image 4">
            <img src="assets/images/image5.jpg" alt="Image 5">
            <img src="assets/images/image6.jpg" alt="Image 6">
        </div>
    </div>

    <div class="image-container">
        <div class="slideshow" id="slideshow3">
            <img src="assets/images/image7.jpg" alt="Image 7">
            <img src="assets/images/image8.jpg" alt="Image 8">
            <img src="assets/images/image9.jpg" alt="Image 9">
        </div>
    </div>

    <div class="image-container">
        <div class="slideshow" id="slideshow4">
            <img src="assets/images/image10.jpg" alt="Image 10">
            <img src="assets/images/image11.jpg" alt="Image 11">
            <img src="assets/images/image12.jpg" alt="Image 12">
        </div>
    </div>
</div>

<script>
    function startSlideshow(containerId, directions) {
        const container = document.getElementById(containerId);
        const images = container.querySelectorAll("img");
        let currentIndex = 0;

        // Display the first image immediately without transition
        images[currentIndex].style.opacity = 1;
        images[currentIndex].style.transform = 'translateX(0)'; // Keep the first image in view

        // Set up the first random delay for the transition start
        const initialDelay = Math.floor(Math.random() * 2000) + 1000; // Random delay between 1s and 3s

        setTimeout(() => {
            // Start changing images after the initial delay
            setInterval(() => {
                // Get a random direction for the next transition
                const randomDirection = directions[Math.floor(Math.random() * directions.length)];

                // Apply the transition for the current image (slide it out in the opposite direction)
                if (randomDirection === 'show-left') {
                    images[currentIndex].style.transform = 'translateX(100%)'; // Slide out to the right
                } else if (randomDirection === 'show-top') {
                    images[currentIndex].style.transform = 'translateY(100%)'; // Slide out downward
                } else if (randomDirection === 'show-right') {
                    images[currentIndex].style.transform = 'translateX(-100%)'; // Slide out to the left
                }

                // Move to the next image
                currentIndex = (currentIndex + 1) % images.length;

                // Set the new image to opacity 1 and prepare for the transition
                images[currentIndex].style.opacity = 1;
                images[currentIndex].style.transition = 'transform .5s ease, opacity .5s ease';

                // Apply the transition for the new image (slide it in from the chosen direction)
                if (randomDirection === 'show-left') {
                    images[currentIndex].style.transform = 'translateX(-100%)'; // Slide in from left
                } else if (randomDirection === 'show-top') {
                    images[currentIndex].style.transform = 'translateY(-100%)'; // Slide in from top
                } else if (randomDirection === 'show-right') {
                    images[currentIndex].style.transform = 'translateX(100%)'; // Slide in from right
                }

                // After a short delay, reset the transform to the final position
                setTimeout(() => {
                    images[currentIndex].style.transform = 'translateX(0)';
                    images[currentIndex].style.transform = 'translateY(0)';
                }, 100); // Delay before resetting transform to final position

            }, Math.floor(Math.random() * 2000) + 3000); // Random delay for each image change (1s - 3s)
        }, initialDelay); // Initial random delay before starting the slideshow
    }

    // Usage
    const directions = ["show-left", "show-top", "show-right"];
    startSlideshow("slideshow1", directions); // For each container
    startSlideshow("slideshow2", directions);
    startSlideshow("slideshow3", directions);
    startSlideshow("slideshow4", directions);
</script>