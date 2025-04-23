<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body>
<div class="admin-container">
    <h1>Административная панель</h1>

    <div class="breadcrumb">
        <a href="?">Главная</a>
        <?php if($currentDir): ?>
        <?php foreach($breadcrumbs as $crumb): ?>
        / <a href="?dir=<?php echo urlencode($crumb['path']) ?>"><?php echo $crumb['name'] ?></a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="upload-section">
        <h2>Загрузка файлов</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" name="file" id="fileInput" required>
            <label for="dirSelect">Выберите директорию:</label>
            <select name="targetDir" id="dirSelect" required>
                <?php foreach($allowedDirs as $dir): ?>
                <option value="<?php echo $dir ?>"><?php echo $dir ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Загрузить</button>
        </form>
        <div id="uploadStatus" class="status-message"></div>
    </div>

    <div class="file-list">
        <h2>Список файлов</h2>
        <?php if(!empty($files)): ?>
        <div class="file-items">
            <?php foreach($files as $file): ?>
            <div class="file-item">
                <?php if($file['is_dir']): ?>
                <a href="?dir=<?php echo urlencode($file['path']) ?>" class="directory">
                    📁 <?php echo $file['name'] ?>
                </a>
                <?php else: ?>
                <span class="file-name">📄 <?php echo $file['name'] ?></span>
                <span class="file-path"><?php echo $file['path'] ?></span>
                <div class="file-actions">
                    <button class="delete-btn" data-path="<?php echo $file['fullPath'] ?>">Удалить</button>
                    <button class="view-btn" data-path="<?php echo $file['fullPath'] ?>">Просмотр</button>
                    <a href="../../index.php"
                       class="download-btn">Скачать</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="empty">Файлы не найдены!</p>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="fileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <pre id="fileContent"></pre>
            <img id="fileImage" alt="Предпросмотр изображения">
        </div>
    </div>
</div>

<script src="/public/js/admin.js"></script>
</body>
</html>
