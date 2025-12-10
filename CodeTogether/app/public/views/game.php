<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Code Challenge</title>

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/core/main.css">
    <link rel="stylesheet" href="/public/css/page/game.css">
</head>

<body>
<canvas id="matrix-canvas"></canvas>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="page-game">
<div class="container-fluid container-lg py-5">



    <!-- DAILY CHALLENGE BOX -->
    <div class="challenge-box mb-4">
        <div class="row justify-content-center">
            <div class="col-12 p-2">
                <h1 id="problemTitle" class="text-2xl font-bold text-teal-400 mb-2">Loading Problem...</h1>
                <p id="problemDescription" class="text-lg">Please wait while we fetch today's challenge...</p>
                <p id="problemExample" class="mt-2 text-sm text-secondary"></p>
            </div>
        </div>
    </div>

    <!-- CODE EDITOR SECTION -->
    <div class="row justify-content-center mb-4">
        <div class="col-12 col-md-10">
            <div class="mb-2 text-end">
                <select id="languageSelect" class="form-select w-auto d-inline-block">
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="cpp">C++</option>
                    <option value="csharp">C#</option>
                    <option value="java">Java</option>
                </select>
            </div>

            <div id="editor" style="height: 500px; border-radius: 8px; overflow: hidden;"></div>

            <div class="text-center mt-3">
                <button id="submitBtn" class="btn btn-success btn-lg px-5" onclick="submitCode()">
                    <i class="fa-solid fa-paper-plane"></i> Submit Solution
                </button>
            </div>

            <div id="submissionResult" class="mt-3 text-center"></div>
        </div>
    </div>

</div>
</main>

<!-- AI Review Widget -->
<?php include __DIR__ . '/../includes/aiReviewWidget.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<!-- Monaco Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.33.0/min/vs/loader.min.js"></script>
<script src="/public/js/core/theme.js"></script>
<script src="/public/js/core/rain.js"></script>
<script src="/public/js/core/status.js"></script>
<script src="/public/js/container/aiReview.js"></script>
<script src="/public/js/page/game.js"></script>
<link rel="stylesheet" href="/public/css/container/aiReview.css">

</body>
</html>
