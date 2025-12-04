<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Flashcard Set</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />

    <!-- Core styles -->
    <link rel="stylesheet" href="../public/css/core/main.css">
    <link rel="stylesheet" href="../public/css/page/CreateFlashcards.css?v=1.0">

    <!-- FlashCards page styles -->
    <link rel="stylesheet" href="../public/css/page/FlashCards.css?v=1.1">
</head>

<body class="app-body">

    <canvas id="matrix-canvas"></canvas>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="page-cards">

        <div id="app" class="app-container">

            <h1 class="main-title matrix-text">
                CREATE YOUR FLASHCARD SET
            </h1>

            <p class="matrix-subtitle-text">
                Build a personalized training program. Fill out your terms and definitions below.
            </p>

            <form method="POST" action="index.php?action=cards&do=save" class="create-set-form">
                
                <div class="mb-4">
                    <label class="form-label matrix-text" for="set-name">Set Name</label>
                    <input type="text" class="form-control matrix-text" id="set-name" name="set_name" placeholder="Enter your set name" required>
                </div>

                <div class="set-grid" id="cards-container">

                    <!-- Fixed 5 term-definition rows -->
                   

                </div>
                 <button type="button" id="add-card-btn" class="btn btn-outline-primary matrix-text mt-3">
                        + Add Card
                </button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary matrix-text">Save Set</button>
                    <a href="?action=cards" class="btn btn-secondary matrix-text">Cancel</a>
                </div>

            </form>

        </div>

    </main>

    <!-- Scripts -->
    <script src="/public/js/core/theme.js"></script>
    <script src="/public/js/core/rain.js"></script>
    <script src="/public/js/core/status.js"></script>
    <script src="/public/js/page/FlashCards.js"></script>
    <script src="/public/js/page/CreateFlashcards.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
