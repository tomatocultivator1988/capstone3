<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Examination System' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?= $head ?? '' ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php if (isset($showHeader) && $showHeader): ?>
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= $headerTitle ?? 'Dashboard' ?></h1>
                <p class="text-gray-600"><?= $headerSubtitle ?? '' ?></p>
            </div>
            <?php if (isset($showLogout) && $showLogout): ?>
            <button id="logoutBtn" class="px-4 py-2 border border-red-400 text-red-600 rounded-md hover:bg-red-50 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"></path>
                </svg>
                Logout
            </button>
            <?php endif; ?>
        </div>
    </header>
    <?php endif; ?>

    <main class="<?= isset($showHeader) && $showHeader ? 'max-w-7xl mx-auto px-4 py-8' : '' ?>">
        <?= $content ?>
    </main>

    <?= $scripts ?? '' ?>
</body>
</html>