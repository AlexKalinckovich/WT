<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Food Restaurant</title>

    <!-- Link to external CSS files -->
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/buttons.css">
    <link rel="stylesheet" href="/css/food-list.css">
</head>
<body>
<header>
    <h1>Fast Food Restaurant</h1>
</header>

<section id="food-list">
    <h2>Our Menu</h2>
    <div class="menu-grid">
        <?php if (!empty($menuItems)): ?>
            <?php foreach ($menuItems as $index => $item): ?>
                <div class="menu-item" data-id="<?= $index + 1 ?>">
                    <img src="/images/<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="description"><?= htmlspecialchars($item['description']) ?></p>
                    <button class="order-btn">Order</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No menu items available.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Link to external JavaScript files -->
<script src="/js/main.js"></script>
<script src="/js/food-list.js"></script>
</body>
</html>