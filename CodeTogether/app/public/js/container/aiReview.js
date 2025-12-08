// AI Review Widget Controller
class AIReviewWidget {
    constructor() {
        this.widget = document.getElementById('aiReviewWidget');
        this.isActive = false;
        this.timeout = null;
    }

    show() {
        if (this.widget) {
            clearTimeout(this.timeout);
            this.widget.classList.add('active');
            this.isActive = true;
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
    }

    hide() {
        if (this.widget) {
            this.widget.classList.remove('active');
            this.isActive = false;
            document.body.style.overflow = ''; // Restore scrolling
            
            // Clear any existing timeout
            clearTimeout(this.timeout);
        }
    }

    // Auto-hide after specified time (in milliseconds)
    autoHide(delay = 30000) {
        this.timeout = setTimeout(() => {
            this.hide();
        }, delay);
    }

    // Check if widget is currently active
    isShowing() {
        return this.isActive;
    }
}

// Results Display Controller
class ResultsDisplay {
    constructor() {
        this.resultContainer = document.getElementById('submissionResult');
    }

    showEnhancedResults(data) {
        if (!this.resultContainer) return;

        const scoreClass = this.getScoreClass(data.score);
        const scoreIcon = this.getScoreIcon(data.score);
        const celebration = data.score >= 80 ? 'high-score' : '';
        
        let html = `
            <div class="result-card ${scoreClass} ${celebration}">
                <div class="result-header">
                    <h3>
                        <i class="${scoreIcon}"></i>
                        Submission Results
                    </h3>
                </div>
                
                <div class="score-display">
                    ${data.score}/100
                    ${this.getScoreBadge(data.score)}
                </div>
                
                <div class="result-status ${data.correct ? 'correct' : 'incorrect'}">
                    <i class="fa-solid fa-circle-${data.correct ? 'check' : 'xmark'}"></i>
                    <span>${data.correct ? 'Correct Solution' : 'Incorrect Solution'}</span>
                </div>
                
                <div class="result-feedback">
                    <i class="fa-solid fa-comment-dots"></i>
                    <p>${data.feedback}</p>
                </div>
                
                ${this.createLeaderboardHTML(data.leaderboard)}
            </div>
        `;
        
        this.resultContainer.innerHTML = html;
        
        // Add celebration effects for high scores
        if (data.score >= 80) {
            this.triggerCelebration();
        }
    }

    showWarningResult(message = 'You have already submitted today. Come back tomorrow for a new challenge!') {
        if (!this.resultContainer) return;

        let html = `
            <div class="result-card warning-result">
                <div class="result-header">
                    <h3>
                        <i class="fa-solid fa-clock"></i>
                        Daily Limit Reached
                    </h3>
                </div>
                
                <div class="result-feedback">
                    <i class="fa-solid fa-info-circle"></i>
                    <p>${message}</p>
                </div>
            </div>
        `;
        
        this.resultContainer.innerHTML = html;
    }

    showErrorResult(message = 'Error submitting solution. Please try again.') {
        if (!this.resultContainer) return;

        let html = `
            <div class="result-card error-result">
                <div class="result-header">
                    <h3>
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        Submission Error
                    </h3>
                </div>
                
                <div class="result-feedback">
                    <i class="fa-solid fa-bug"></i>
                    <p>${message}</p>
                </div>
            </div>
        `;
        
        this.resultContainer.innerHTML = html;
    }

    getScoreClass(score) {
        if (score >= 80) return 'high-score';
        if (score >= 60) return 'good-score';
        if (score >= 40) return 'average-score';
        return 'low-score';
    }

    getScoreIcon(score) {
        if (score >= 80) return 'fa-solid fa-trophy';
        if (score >= 60) return 'fa-solid fa-medal';
        if (score >= 40) return 'fa-solid fa-star';
        return 'fa-solid fa-code';
    }

    getScoreBadge(score) {
        if (score >= 80) return '<span class="score-badge">Excellent!</span>';
        if (score >= 60) return '<span class="score-badge">Good Job!</span>';
        if (score >= 40) return '<span class="score-badge">Keep Trying!</span>';
        return '<span class="score-badge">Needs Work</span>';
    }

    createLeaderboardHTML(leaderboard) {
        if (!leaderboard || leaderboard.length === 0) {
            return '';
        }

        let html = `
            <div class="leaderboard-section">
                <div class="leaderboard-header">
                    <i class="fa-solid fa-users"></i>
                    Top Performers
                </div>
        `;

        leaderboard.forEach((entry, index) => {
            const rankClass = this.getRankClass(index + 1);
            html += `
                <div class="leaderboard-entry">
                    <div class="leaderboard-rank ${rankClass}">${index + 1}</div>
                    <div class="leaderboard-info">
                        <span class="leaderboard-username">${entry.username}</span>
                        <span class="leaderboard-points">${entry.points} pts</span>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    getRankClass(rank) {
        switch(rank) {
            case 1: return 'gold';
            case 2: return 'silver';
            case 3: return 'bronze';
            default: return '';
        }
    }

    triggerCelebration() {
        const resultCard = document.querySelector('.result-card');
        if (!resultCard) return;

        // Create particle effects
        for (let i = 0; i < 20; i++) {
            setTimeout(() => {
                this.createParticle(resultCard);
            }, i * 100);
        }

        // Add celebration sound effect (if available)
        this.playCelebrationSound();
    }

    createParticle(container) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Random starting position at bottom of container
        const startX = Math.random() * container.offsetWidth;
        particle.style.left = startX + 'px';
        particle.style.bottom = '0px';
        
        // Random horizontal movement
        const xOffset = (Math.random() - 0.5) * 100;
        particle.style.setProperty('--x-offset', xOffset + 'px');
        
        // Random color from theme
        const colors = ['#06a342', '#00ff88', '#00ff41'];
        particle.style.background = colors[Math.floor(Math.random() * colors.length)];
        
        container.appendChild(particle);
        
        // Remove particle after animation
        setTimeout(() => {
            particle.remove();
        }, 3000);
    }

    playCelebrationSound() {
        // Optional: Add sound effect for celebration
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE');
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Ignore audio play errors
            });
        } catch (e) {
            // Ignore audio errors
        }
    }

    clear() {
        if (this.resultContainer) {
            this.resultContainer.innerHTML = '';
        }
    }
}

// Initialize controllers when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.aiReviewWidget = new AIReviewWidget();
    window.resultsDisplay = new ResultsDisplay();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        AIReviewWidget,
        ResultsDisplay
    };
}