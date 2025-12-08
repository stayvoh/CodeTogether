<?php include __DIR__ . '/../includes/sessionCheck.php'; ?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Code Challenge</title>

    <!-- Monaco Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.33.0/min/vs/loader.min.js"></script>

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

    <!-- STREAK DISPLAY -->
    <div class="streak-box mb-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 text-center">
                <div class="streak-display">
                    <i class="fa-solid fa-fire text-orange-500"></i>
                    <span class="streak-number" id="currentStreak">0</span>
                    <span class="streak-label">day streak!</span>
                    <span class="streak-best ms-3">Best: <span id="longestStreak">0</span></span>
                </div>
            </div>
        </div>
    </div>

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
            <div id="streakMilestone" class="mt-3 text-center d-none"></div>
        </div>
    </div>

</div>
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script src="/public/js/core/theme.js"></script>
<script src="/public/js/core/rain.js"></script>
<script src="/public/js/core/status.js"></script>

<script>
require.config({
    paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.33.0/min/vs' }
});

let editor;
let currentProblem = {};
let hasSubmittedToday = false;

require(["vs/editor/editor.main"], function () {
    editor = monaco.editor.create(document.getElementById("editor"), {
        value: "// Write your solution here",
        language: "javascript",
        theme: "vs-dark",
        automaticLayout: true,
        lineNumbers: "on",
        minimap: { enabled: true },
        fontSize: 16
    });

    document.getElementById("languageSelect").addEventListener("change", function () {
        monaco.editor.setModelLanguage(editor.getModel(), this.value);
    });

    fetchProblem();
    fetchUserStreak();
});

function fetchProblem() {
    fetch('generateProblem.php?language=javascript&difficulty=beginner')
        .then(r => r.json())
        .then(problem => {
            currentProblem = problem;
            document.getElementById('problemTitle').textContent = problem.title;
            document.getElementById('problemDescription').textContent = problem.description;
            document.getElementById('problemExample').textContent =
                `Example Input: ${problem.exampleInput} | Example Output: ${problem.exampleOutput}`;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('problemTitle').textContent = "Error loading problem";
        });
}

function fetchUserStreak() {
    fetch('submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code: '',
            language: '',
            problem: { title: '', description: '' },
            checkStreakOnly: true
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.streak) {
            updateStreakDisplay(data.streak);
        }
    })
    .catch(err => {
        console.error('Error fetching streak:', err);
    });
}

function updateStreakDisplay(streakData) {
    document.getElementById('currentStreak').textContent = streakData.current;
    document.getElementById('longestStreak').textContent = streakData.longest;
    
    if (streakData.milestone) {
        showStreakMilestone(streakData.current);
    }
}

function showStreakMilestone(streak) {
    const milestoneDiv = document.getElementById('streakMilestone');
    milestoneDiv.innerHTML = `
        <div class="alert alert-warning">
            <i class="fa-solid fa-trophy"></i> 
            <strong>Milestone!</strong> ${streak} day streak! Keep it up!
        </div>
    `;
    milestoneDiv.classList.remove('d-none');
    
    setTimeout(() => {
        milestoneDiv.classList.add('d-none');
    }, 5000);
}

function submitCode() {
    if (hasSubmittedToday) {
        document.getElementById('submissionResult').innerHTML = 
            '<span class="text-warning">You have already submitted today. Come back tomorrow for a new challenge!</span>';
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';

    fetch('submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code: editor.getValue(),
            language: document.getElementById('languageSelect').value,
            problem: currentProblem
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            document.getElementById('submissionResult').innerHTML = `<span class="text-danger">${data.error}</span>`;
            
            if (data.alreadySubmitted) {
                hasSubmittedToday = true;
                updateSubmitButton();
            }
        } else {
            hasSubmittedToday = true;
            updateSubmitButton();
            
            // Update streak display
            if (data.streak) {
                updateStreakDisplay(data.streak);
            }
            
            let html = `
                <div class="alert alert-success">
                    <strong>Score:</strong> ${data.score}<br>
                    <strong>Correct:</strong> ${data.correct ? '✅ Yes' : '❌ No'}<br>
                    <strong>Feedback:</strong> ${data.feedback}
                </div>
                <div class="mt-2">
                    <strong>Top Users:</strong><br>
            `;
            data.leaderboard.forEach(entry => {
                html += `${entry.username}: ${entry.points}<br>`;
            });
            html += '</div>';
            document.getElementById('submissionResult').innerHTML = html;
        }
    })
    .catch(err => {
        console.error('Submission error:', err);
        document.getElementById('submissionResult').innerHTML = 
            '<span class="text-danger">Error submitting solution. Please try again.</span>';
    })
    .finally(() => {
        if (!hasSubmittedToday) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Solution';
        }
    });
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Submitted Today';
    submitBtn.classList.remove('btn-success');
    submitBtn.classList.add('btn-secondary');
}
</script>

</body>
</html>
