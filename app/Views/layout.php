<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Knowledge Tree') ?></title>
    <meta name="description" content="Visual knowledge management system - organize your thoughts in tree structures">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="/public/css/main.css">
    <link rel="stylesheet" href="/public/css/tree.css">
    <link rel="stylesheet" href="/public/css/animations.css">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/public/assets/favicon.svg">
</head>
<body>
    <?= $content ?>
    <!-- Scripts -->
    <script src="/public/js/utils.js"></script>
    <script src="/public/js/api.js"></script>
    <script src="/public/js/app.js"></script>
</body>
</html>
