<?php
// package-detail.php
include 'include/db_connection.php'; // Include the database connection

// Check if the package ID is provided in the URL
if (!isset($_GET['id'])) {
    die("Package ID not provided.");
}

$packageId = $_GET['id'];

// Fetch the package details from the database
$stmt = $conn->prepare("SELECT * FROM catering_packages WHERE id = :id");
$stmt->bindParam(':id', $packageId);
$stmt->execute();
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    die("Package not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $package['title']; ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/catering-packages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Main container */
        .page-container {
            position: relative;
            width: 100%;
            height: 100%;
            background-image: url("assets/images/bg2.jpg");
            display: flex;
            justify-content: center;
            /* Centers horizontally */
            align-items: center;
            /* Centers vertically */
        }

        /* Back button at the top-left */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-button a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }

        /* Title positioned at the top-center */
        .package-heading {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            border: 2px solid #F59A23;
            background-color: #333;
            color: white;
            padding: 10px;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
        }

        /* Content wrapper to align elements horizontally */
        .content-container {
            display: flex;
            justify-content: center;
            /* Ensures both are centered */
            align-items: center;
            width: 90%;
            /* Adjust width as needed */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Image container (LEFT SIDE) */
        .image-container {
            position: absolute;
            left: 0;
            width: 50%;
            /* Adjust width as needed */
            display: flex;
        }

        .image-container img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        /* Octagon container (RIGHT SIDE) */
        .octagon-container {
            position: absolute;
            right: 0;
            width: 50%;
            height: auto;
            background: #f0f0f0;
            clip-path: polygon(10% 0%, 90% 0%, 100% 15%, 100% 85%, 90% 100%, 10% 100%, 0% 85%, 0% 15%);
            display: flex;
            padding: 40px;
        }

        /* Package description inside octagon */
        .package-description {
            font-size: 1.3rem;
            text-align: left;
            max-width: 80%;
        }

        /* Book now button positioned at the bottom-right */
        .book-now-button {
            position: absolute;
            bottom: 20px;
            right: 20px;
        }

        .book-now-button .btn {
            padding: 10px 20px;
            background-color: #000;
            color: #F59A23;
            border: 2px solid #F59A23;
            text-decoration: none;
            border-radius: 15px;
            font-size: 24px;
            display: inline-block;
        }

        .book-now-button .btn:hover {
            transform: scale(1.15);
        }
    </style>
</head>

<body>
    <div class="page-container">
        <!-- Back button -->
        <div class="back-button">
            <a href="index.php?page=catering-packages"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <!-- Title at the top-center -->
        <div class="package-heading"><?php echo $package['title']; ?></div>

        <!-- Content container with image on the left and octagon on the right -->
        <div class="content-container">
            <!-- Image container (left side) -->
            <div class="image-container">
                <img src="<?php echo $package['image_url']; ?>" alt="<?php echo $package['title']; ?>">
            </div>

            <!-- Octagon container (right side) -->
            <div class="octagon-container">
                <div class="package-description">
                    <p><?php echo nl2br($package['description']); ?></p>
                    <p>This Package Starts at <strong><?php echo number_format($package['price'], 2); ?></strong></p>
                </div>
            </div>
        </div>

        <!-- Book now button at the bottom-right -->
        <div class="book-now-button">
            <a href="#" class="btn">Book Now!</a>
        </div>
    </div>

</body>

</html>