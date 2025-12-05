let editor;
let currentProblem = {};

if (typeof require !== "undefined") {
    require.config({
        paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.33.0/min/vs' }
    });

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
    });
}

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

function submitCode() {
    fetch('submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code: editor.getValue(),
            language: document.getElementById('languageSelect').value,
            problem: JSON.stringify(currentProblem)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            document.getElementById('submissionResult').innerHTML = `<span class="text-danger">${data.error}</span>`;
        } else {
            let html = `
                <strong>Score:</strong> ${data.score}<br>
                <strong>Correct:</strong> ${data.correct}<br>
                <strong>Feedback:</strong> ${data.feedback}<br>
                <strong>Top Users:</strong><br>
            `;
            for (const [user, score] of Object.entries(data.leaderboard)) {
                html += `${user}: ${score}<br>`;
            }
            document.getElementById('submissionResult').innerHTML = html;
        }
    });
}
