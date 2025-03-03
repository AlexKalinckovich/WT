<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Food Restaurant</title>

    <!-- Link to external CSS files -->
    <link rel="stylesheet" href="/views/css/main.css">
    <link rel="stylesheet" href="/views/css/buttons.css">
    <link rel="stylesheet" href="/views/css/food-list.css">
    <link rel ="stylesheet" href="/views/css/navigation.css">
    <link rel="stylesheet" href="/views/css/cityForm.css">
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
</header>

<section id="citySection">
    <div class="modal-content">
        <span id="closeFormBtn" class="close">&times;</span>
        <h2>Введите города через запятую</h2>
        <form id="cityForm" method="GET" action="/process-cities">
            <label for="cities">Города:</label>
            <textarea id="cities" name="cities" rows="5" cols="50"></textarea>
            <br>
            <button type="submit">Отправить</button>
        </form>
    </div>
</section>

<section id="food-list">
    <h2>Our Menu</h2>
    <div class="menu-grid">
        {{ if !empty($menuItems) }}
        {{ foreach $menuItems as $index => $item }}
        <div class="menu-item" data-id="{{ $index + 1 }}">
            <img src="Public/images/{{ $item['image_path'] }}" alt="{{ $item['name'] }}">
            <h3>{{ $item['name'] }}</h3>
            <p class="description">{{ $item['description'] }}</p>
            <button class="order-btn">Order</button>
        </div>
        {{ endforeach }}
        {{ else }}
        <p>No menu items available.</p>
        {{ endif }}
    </div>
</section>

<!-- Link to external JavaScript files -->
<script src="/views/js/main.js"></script>
<script src="/views/js/food-list.js"></script>
<script src="/views/js/headerLinks.js"></script>
<script src="/views/js/cityForm.js"></script>
</body>
</html>