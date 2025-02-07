<?php
// catering-packages.php
include 'include/db_connection.php'; // Include the database connection

// Fetch all packages from the database
$stmt = $conn->query("SELECT * FROM catering_packages");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="catering-packages">
    <div class="row">
        <?php
        // Display the first 3 packages in the first row
        for ($i = 0; $i < 3; $i++):
            if (isset($packages[$i])):
        ?>
                <a href="index.php?page=package-detail&id=<?php echo $packages[$i]['id']; ?>" class="package">
                    <img src="<?php echo $packages[$i]['image_url']; ?>" alt="<?php echo $packages[$i]['title']; ?>">
                    <div class="package-title"><?php echo $packages[$i]['title']; ?></div>
                </a>
        <?php
            endif;
        endfor;
        ?>
    </div>
    <div class="row">
        <?php
        // Display the next 2 packages in the second row, centered
        for ($i = 3; $i < 5; $i++):
            if (isset($packages[$i])):
        ?>
                <a href="index.php?page=package-detail&id=<?php echo $packages[$i]['id']; ?>" class="package">
                    <img src="<?php echo $packages[$i]['image_url']; ?>" alt="<?php echo $packages[$i]['title']; ?>">
                    <div class="package-title"><?php echo $packages[$i]['title']; ?></div>
                </a>
        <?php
            endif;
        endfor;
        ?>
    </div>
</div>