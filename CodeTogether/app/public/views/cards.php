<?php
include __DIR__ . '/../includes/sessionCheck.php';

// Figure out which set we're playing
$rawSet = $set ?? ($_GET['set'] ?? 'default');

// Grab just the last part: user/test_deck_1 → test_deck_1
$rawName = basename($rawSet);

// Turn underscores/dashes into spaces and capitalize
if ($rawName === '' || $rawName === 'default') {
    $displayName = 'System';
} else {
    $displayName = ucwords(str_replace(['_', '-'], ' ', $rawName));
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminology Matching</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Font - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="/public/css/core/main.css">
    <link rel="stylesheet" href="/public/css/page/FlashCards.css">
</head>

<body class="app-body">

    <canvas id="matrix-canvas"></canvas>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="page-cards">
        <!-- Main Application Content (Z-index 10) -->
        <div id="app" class="app-container">
            <!-- Header -->
            <h1 id="main-title" class="main-title matrix-text">
                <?= htmlspecialchars($displayName) ?> Terminology: Match
            </h1>
            <p class="matrix-subtitle-text">
                Connect the Term to its correct Definition. Failure is not an option.
            </p>

            <!-- Score and Controls -->
            <div class="control-bar">
                <div class="score-display matrix-text">
                    Score: <span id="score">0</span> / <span id="total-matches">0</span>
                </div>
                <button id="restart-button" class="restart-button matrix-text" onclick="startGame()">
                    <span class="font-bold">RELOAD PROGRAM</span>
                </button>
            </div>

            <?php include __DIR__ . '/../includes/progressBar.php'; ?>

            <!-- Game Area -->
            <div id="game-area" class="game-area">
                <!-- Terms Column (Draggable Cards) -->
                <div class="column">
                    <h2 class="column-header matrix-text">Terms</h2>
                    <div id="terms-container" class="card-list">
                        <!-- Term cards will be inserted here -->
                    </div>
                </div>

                <!-- Definitions Column (Drop Targets) -->
                <div class="column">
                    <h2 class="column-header matrix-text">Definitions</h2>
                    <div id="definitions-container" class="card-list">
                        <!-- Definition targets will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Game Over Modal -->
            <div id="game-over-modal" class="modal-overlay hidden">
                <div class="modal-content matrix-text">
                    <h3 class="text-3xl font-bold mb-4">MATCHING COMPLETE</h3>
                    <p class="text-xl mb-6">You have successfully matched all terms.</p>
                    <p class="text-2xl mb-8 font-bold">
                        Final Score: <span id="final-score">0</span>
                    </p>
                    <button class="restart-button matrix-text"
                            onclick="document.getElementById('game-over-modal').classList.add('hidden'); startGame();">
                        INITIATE NEW SEQUENCE
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="/public/js/core/theme.js"></script>
    <script src="/public/js/core/rain.js"></script>
    <script src="/public/js/page/FlashCards.js"></script>
    <script src="/public/js/core/status.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
