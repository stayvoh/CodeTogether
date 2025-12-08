// Game Controller - Daily Code Challenge
class GameController {
    constructor() {
        this.editor = null;
        this.currentProblem = {};
        this.hasSubmittedToday = false;
        this.init();
    }

    init() {
        // Wait for DOM to be fully loaded and Bootstrap to initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeMonacoEditor();
            });
        } else {
            this.initializeMonacoEditor();
        }
    }

    initializeMonacoEditor() {
        // Configure Monaco Editor
        require.config({
            paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.33.0/min/vs' }
        });

        // Initialize Monaco Editor and game
        require(["vs/editor/editor.main"], () => {
            this.initializeEditor();
            this.setupEventListeners();
            this.fetchProblem();
            this.checkSubmissionStatus();
        });
    }

    initializeEditor() {
        this.editor = monaco.editor.create(document.getElementById("editor"), {
            value: "// Write your solution here",
            language: "javascript",
            theme: "vs-dark",
            automaticLayout: true,
            lineNumbers: "on",
            minimap: { enabled: true },
            fontSize: 16
        });
    }

    setupEventListeners() {
        const languageSelect = document.getElementById("languageSelect");
        if (languageSelect) {
            languageSelect.addEventListener("change", () => {
                monaco.editor.setModelLanguage(this.editor.getModel(), languageSelect.value);
            });
        }
    }

    fetchProblem() {
        fetch('generateProblem.php?difficulty=intermediate')
            .then(r => r.json())
            .then(problem => {
                this.currentProblem = problem;
                this.updateProblemDisplay(problem);
            })
            .catch(err => {
                console.error('Error fetching problem:', err);
                this.showProblemError();
            });
    }

    updateProblemDisplay(problem) {
        const titleElement = document.getElementById('problemTitle');
        const descriptionElement = document.getElementById('problemDescription');
        const exampleElement = document.getElementById('problemExample');

        if (titleElement) titleElement.textContent = problem.title;
        if (descriptionElement) descriptionElement.textContent = problem.description;
        if (exampleElement) {
            exampleElement.textContent = 
                `Example Input: ${problem.exampleInput} | Example Output: ${problem.exampleOutput}`;
        }
    }

    showProblemError() {
        const titleElement = document.getElementById('problemTitle');
        if (titleElement) {
            titleElement.textContent = "Error loading problem";
        }
    }

    checkSubmissionStatus() {
        fetch('submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code: '',
                language: '',
                problem: { title: '', description: '' },
                checkSubmissionStatus: true
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.submission) {
                this.hasSubmittedToday = data.submission.alreadySubmitted;
                if (this.hasSubmittedToday) {
                    this.updateSubmitButton();
                }
            }
        })
        .catch(err => {
            console.error('Error checking submission status:', err);
        });
    }

    submitCode() {
        if (this.hasSubmittedToday) {
            if (window.resultsDisplay) {
                window.resultsDisplay.showWarningResult();
            }
            return;
        }

        // Show AI review animation
        if (window.aiReviewWidget) {
            window.aiReviewWidget.show();
        }
        
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> AI Reviewing...';
        }

        fetch('submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code: this.editor.getValue(),
                language: document.getElementById('languageSelect').value,
                problem: this.currentProblem
            })
        })
        .then(r => r.json())
        .then(data => {
            // Hide AI review animation
            if (window.aiReviewWidget) {
                window.aiReviewWidget.hide();
            }
            
            if (data.error) {
                if (window.resultsDisplay) {
                    window.resultsDisplay.showErrorResult(data.error);
                }
                
                if (data.alreadySubmitted) {
                    this.hasSubmittedToday = true;
                    this.updateSubmitButton();
                }
            } else {
                this.hasSubmittedToday = true;
                this.updateSubmitButton();
                
                // Show enhanced results with celebration effects
                if (window.resultsDisplay) {
                    window.resultsDisplay.showEnhancedResults(data);
                }
            }
        })
        .catch(err => {
            console.error('Submission error:', err);
            
            // Hide AI review animation on error
            if (window.aiReviewWidget) {
                window.aiReviewWidget.hide();
            }
            
            if (window.resultsDisplay) {
                window.resultsDisplay.showErrorResult('Error submitting solution. Please try again.');
            }
        })
        .finally(() => {
            if (!this.hasSubmittedToday) {
                const submitBtn = document.getElementById('submitBtn');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Submit Solution';
                }
            }
        });
    }

    updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Submitted Today';
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-secondary');
        }
    }

    // Public methods for external access
    getEditor() {
        return this.editor;
    }

    getCurrentProblem() {
        return this.currentProblem;
    }

    hasSubmitted() {
        return this.hasSubmittedToday;
    }
}

// Initialize game when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Make game controller globally accessible
    window.gameController = new GameController();
    
    // Make submitCode function globally accessible for button onclick
    window.submitCode = () => {
        if (window.gameController) {
            window.gameController.submitCode();
        }
    };
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GameController;
}