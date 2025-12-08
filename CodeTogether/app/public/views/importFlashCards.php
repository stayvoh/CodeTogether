<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Flashcard Set</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />

    <!-- Core styles -->
    <link rel="stylesheet" href="../public/css/core/main.css">
    <link rel="stylesheet" href="../public/css/page/CreateFlashcards.css?v=1.0">
    <link rel="stylesheet" href="../public/css/page/FlashCards.css?v=1.1">
</head>

<body class="app-body">

    <canvas id="matrix-canvas"></canvas>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="page-cards">

        <div id="app" class="app-container">

            <h1 class="main-title matrix-text">
                IMPORT FLASHCARDS
            </h1>

            <p class="matrix-subtitle-text">
                Upload a <strong>.csv</strong> or <strong>.txt</strong> file and we’ll automatically create your deck.
            </p>

            <form method="POST"
                  action="index.php?action=cards&do=import"
                  enctype="multipart/form-data"
                  class="import-set-form">

                <div class="mb-4">
                    <label class="form-label matrix-text" for="import-set-name">Imported Set Name</label>
                    <input type="text"
                           class="form-control matrix-text"
                           id="import-set-name"
                           name="set_name"
                           placeholder="Enter name for imported set"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label matrix-text" for="flashcard-file">Flashcard File (.csv or .txt)</label>
                    <input type="file"
                           class="form-control matrix-text"
                           id="flashcard-file"
                           name="flashcard_file"
                           accept=".csv,.txt"
                           required>
                </div>

                <p class="matrix-text small">
                    <strong>CSV format:</strong> <span>Front,Back</span><br>
                    <strong>TXT format:</strong> <span>Question | Answer</span>  - or tab separated.
                </p>

                <div class="mt-3">
                    <button type="submit" class="btn btn-outline-success matrix-text">
                        <i class="fa-solid fa-file-import"></i> Import Flashcards
                    </button>
                    <a href="?action=cards" class="btn btn-outline-success matrix-text">Cancel</a>
                </div>

            </form>

        </div>

    </main>

    <!-- Scripts -->
    <script src="/public/js/core/theme.js"></script>
    <script src="/public/js/core/rain.js"></script>
    <script src="/public/js/core/status.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
