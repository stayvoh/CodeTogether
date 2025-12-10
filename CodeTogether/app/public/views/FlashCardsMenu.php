<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Flashcard Set</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />

    <!-- Core styles -->
    <link rel="stylesheet" href="../public/css/core/main.css">

    <!-- FlashCards page styles (must be after main.css) -->
    <link rel="stylesheet" href="../public/css/page/FlashCards.css?v=1.1">
</head>


<body class="app-body">

    <canvas id="matrix-canvas"></canvas>

    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <main class="page-cards">

        <div id="app" class="app-container">

            <h1 class="main-title matrix-text">
                SELECT FLASHCARD SET
            </h1>

            <p class="matrix-subtitle-text">
                Choose your training program. Precision is mandatory.
            </p>

            <div class="set-grid">
                 

                <!-- PREMADE SETS -->
               
                     <a href="?action=cards&do=play&set=programmingFundamentals" class="set-box">
                        <h3 class="matrix-text">Programming Fundamentals</h3>
                        <p>Core programming concepts and fundamentals.</p>
                    </a>
                  <a href="?action=cards&do=play&set=os" class="set-box">
                        <h3 class="matrix-text">Operating Systems</h3>
                        <p>Core OS concepts, processes, scheduling, memory, and more.</p>
                    </a>


              
                    <a href="?action=cards&do=play&set=hardware" class="set-box">
                        <h3 class="matrix-text">Hardware</h3>
                        <p>CPU, memory, I/O, machine architecture terminology.</p>
                    </a>
               

               
                   <a href="?action=cards&do=play&set=network" class="set-box">
                        <h3 class="matrix-text">Networking</h3>
                        <p>Protocols, layers, routing, addressing, and communication theory.</p>
                    </a>
                      <a href="?action=cards&do=play&set=database" class="set-box">
                        <h3 class="matrix-text">Database</h3>
                        <p>Data modeling, SQL, transactions, and database design principles.</p>
                    </a>
                    
                    <?php if (!empty($customSets)): ?>
                        <?php foreach ($customSets as $setName): ?>

                            <?php 
                                // Make the displayed name friendly: test_deck_1 → Test Deck 1
                                $displayName = ucwords(str_replace(['_', '-'], ' ', $setName)); 
                            ?>

                            <div class="set-box position-relative">

                                <!-- Play link -->
                                <a href="?action=cards&do=play&set=user/<?= urlencode($setName) ?>" 
                                class="text-decoration-none d-block"
                                style="color: inherit;">
                                    <h3 class="matrix-text"><?= htmlspecialchars($displayName) ?></h3>
                                    <p>Your custom flashcard set.</p>
                                </a>

                                <!-- Delete button (top-right corner) -->
                                <form method="POST" 
                                    action="index.php?action=cards&do=delete"
                                    onsubmit="return confirm('Delete this flashcard set permanently?');"
                                    style="position: absolute; top: 10px; right: 10px;">
                                    <input type="hidden" name="set" value="<?= htmlspecialchars($setName) ?>">
                                    <button type="submit" 
                                            class="btn btn-outline-danger matrix-text"
                                            style="padding:2px 6px; font-size: 0.75rem;">
                                        X
                                    </button>
                                </form>

                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>


                

                <!-- CUSTOM SET OPTIONS -->
                
                   <a href="?action=cards&do=create" class="set-box">
                        <h3 class="matrix-text">Create Your Own</h3>
                        <p>Build a personalized flashcard set for advanced training.</p>
                    </a>
               

             
                   <a href="?action=cards&do=import" class="set-box">
                        <h3 class="matrix-text">Upload Set</h3>
                        <p>Upload a CSV to instantly generate a new flashcard training set.</p>
                    </a>
             

            </div>

        </div>

    </main>

    <!-- Scripts -->
    <script src="/public/js/core/theme.js"></script>
    <script src="/public/js/core/rain.js"></script>
    <script src="/public/js/core/status.js"></script>
    <script src="/public/js/page/FlashCards.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
