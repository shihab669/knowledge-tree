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
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/tree.css">
    <link rel="stylesheet" href="/css/animations.css">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
</head>
<body>
    <?= $content ?>
    <!-- Scripts -->
    <script src="/js/utils.js"></script>
    <script src="/js/api.js"></script>
    <script src="/js/app.js"></script>
</body>
</html>
