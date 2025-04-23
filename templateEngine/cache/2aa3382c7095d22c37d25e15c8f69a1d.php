<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Food Restaurant</title>

    <!-- Link to external CSS files -->
    <link rel="stylesheet" href="/public/css/main.css">
    <link rel="stylesheet" href="/public/css/buttons.css">
    <link rel="stylesheet" href="/public/css/food-list.css">
    <link rel ="stylesheet" href="/public/css/navigation.css">
</head>
<body>
<header>
    <h1>Fast Food Restaurant</h1>
    <nav>
        <ul class="nav-links">
            <li class="nav-item"><a href="#" class="nav-link active">About</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Services</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Price</a></li>
            <li class="nav-item"><a href="#" class="nav-link">Contacts</a></li>
        </ul>
    </nav>
    <button class="order-btn" id="adminBtn"> Admin panel</a></button>
</header>

<section id="food-list">
    <h2>Our Menu</h2>
    <div class="menu-grid">
        <?php if(!empty($menuItems)): ?>
        <?php foreach($menuItems as $index => $item): ?>
        <div class="menu-item" data-id="<?php echo $index + 1 ?>">
            <img src="public/images/<?php echo $item['image_path'] ?>" alt="<?php echo $item['name'] ?>">
            <h3><?php echo $item['name'] ?></h3>
            <p class="description"><?php echo $item['description'] ?></p>
            <button class="order-btn">Order</button>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>No menu items available.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Link to external JavaScript files -->
<script src="/public/js/main.js"></script>
<script src="/public/js/food-list.js"></script>
<script src="/public/js/headerLinks.js"></script>
</body>
</html>