// --- Core Application Data ---

// --- Global State ---
let currentCards = [];
let score = 0;

// --- DOM Elements ---
const termsContainer = document.getElementById('terms-container');
const definitionsContainer = document.getElementById('definitions-container');
const scoreElement = document.getElementById('score');
const totalMatchesElement = document.getElementById('total-matches');
const gameOverModal = document.getElementById('game-over-modal');
const progressBar = document.getElementById('progress-bar'); // NEW
const progressText = document.getElementById('progress-text'); // NEW
const urlParams = new URLSearchParams(window.location.search);
const setName = urlParams.get('set') || 'default';
const mainTitle = document.getElementById('main-title');
if (mainTitle) {
    let displayName = setName;

    // If it's a user set, remove "user/" prefix
    if (setName.startsWith('user/')) {
        displayName = setName.slice(5); // remove first 5 characters
    }

    // Capitalize the first letter
    const formattedSet = displayName.charAt(0).toUpperCase() + displayName.slice(1);
    mainTitle.textContent = `${formattedSet} Terminology: Match`;
}


// --- AutoScroll during the drag operation ---
let autoScrollInterval = null;
const SCROLL_SPEED = 10;
const SCROLL_ZONE_HEIGHT = 50;

// Function to start the scrolling loop
function startAutoScrolling(scrollAmount) {
    // Only start if not already scrolling in the requested direction
    if (autoScrollInterval) {
        // Determine direction based on sign
        const currentDirection = autoScrollInterval.scrollAmount > 0 ? 'down' : 'up';
        const newDirection = scrollAmount > 0 ? 'down' : 'up';

        // If the direction hasn't changed, do nothing
        if (currentDirection === newDirection) return;

        // If the direction changed, clear the old one
        clearInterval(autoScrollInterval.id);
    }

    autoScrollInterval = {
        id: setInterval(() => {
            window.scrollBy(0, scrollAmount);
        }, 50),
        scrollAmount: scrollAmount // Store for direction check
    };
}

// Function to stop the scrolling loop
function stopAutoScrolling() {
    if (autoScrollInterval) {
        clearInterval(autoScrollInterval.id);
        autoScrollInterval = null;
    }
}

// Listen to the drag event globally when a drag operation is active
window.addEventListener('dragover', (e) => {
    // Prevent default to allow drop *and* ensure the drag event keeps firing regularly
    e.preventDefault();

    const viewportHeight = window.innerHeight;
    const mouseY = e.clientY; // Mouse position relative to the viewport

    // Check if dragging near the top edge
    if (mouseY < SCROLL_ZONE_HEIGHT) {
        // Scroll Up
        startAutoScrolling(-SCROLL_SPEED);
    }
    // Check if dragging near the bottom edge
    else if (mouseY > viewportHeight - SCROLL_ZONE_HEIGHT) {
        // Scroll Down
        startAutoScrolling(SCROLL_SPEED);
    }
    // Mouse is in the middle, stop scrolling
    else {
        stopAutoScrolling();
    }
});

// Ensure scrolling stops when drag operation ends or is cancelled
window.addEventListener('dragend', stopAutoScrolling);



// Utility to shuffle an array (Fisher-Yates)
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

// --- Drag and Drop Handlers ---

function handleDragStart(e) {
    // Set the ID of the term being dragged
    e.dataTransfer.setData('text/plain', e.target.dataset.termId);
    e.target.classList.add('opacity-50');
}

function handleDragEnd(e) {
    e.target.classList.remove('opacity-50');
}

function handleDragOver(e) {
    e.preventDefault(); // Necessary to allow dropping
}

function handleDragEnter(e) {
    e.preventDefault();
    if (e.target.classList.contains('definition-target') && !e.target.classList.contains('matched')) {
        e.target.classList.add('drag-over');
    }
}

function handleDragLeave(e) {
    if (e.target.classList.contains('definition-target')) {
        e.target.classList.remove('drag-over');
    }
}

function handleDrop(e) {
    e.preventDefault();
    const droppedElement = e.target.closest('.definition-target');
    if (!droppedElement || droppedElement.classList.contains('matched')) {
        return;
    }

    droppedElement.classList.remove('drag-over');

    const termId = parseInt(e.dataTransfer.getData('text/plain'));
    const definitionId = parseInt(droppedElement.dataset.definitionId);
    const termCard = document.querySelector(`[data-term-id='${termId}']`);

    if (termId === definitionId) {
        // Correct Match
        score++;
        scoreElement.textContent = score;

        // --- PROGRESS UPDATE ---
        const percentage = Math.round((score / currentCards.length) * 100);
        progressBar.style.width = `${percentage}%`;
        progressText.textContent = `${percentage}%`;
        // If progress is low, switch text color to neon green to ensure visibility
        progressText.style.color = percentage < 10 ? '#000000' : '#000000';
        // -------------------------

        // 1. Style the definition target
        droppedElement.classList.add('matched');
        droppedElement.innerHTML = `
                        <div class="match-container">
                            <div class="match-term">${termCard.textContent}</div>
                            <div class="match-definition">${droppedElement.dataset.definitionText}</div>
                        </div>
                        `;


        droppedElement.style.cursor = 'default';

        // 2. Remove the term card
        termCard.remove();

        // 3. Check for Game Over
        if (score === currentCards.length) {
            endGame();
        }

    } else {
        // Incorrect Match - Apply shake animation to the definition target
        droppedElement.classList.add('incorrect');
        setTimeout(() => {
            droppedElement.classList.remove('incorrect');
        }, 500);
    }
}

// --- Rendering Functions ---

function renderTerms(terms) {
    termsContainer.innerHTML = '';
    terms.forEach(card => {
        const termDiv = document.createElement('div');
        termDiv.className = 'matrix-card';
        termDiv.textContent = card.term;
        termDiv.setAttribute('draggable', 'true');
        termDiv.dataset.termId = card.id;

        termDiv.addEventListener('dragstart', handleDragStart);
        termDiv.addEventListener('dragend', handleDragEnd);

        termsContainer.appendChild(termDiv);
    });
}

function renderDefinitions(definitions) {
    definitionsContainer.innerHTML = '';
    definitions.forEach(card => {
        const definitionDiv = document.createElement('div');
        definitionDiv.className = 'definition-target';
        definitionDiv.textContent = card.definition;
        definitionDiv.dataset.definitionId = card.id;
        definitionDiv.dataset.definitionText = card.definition; // Store original text for display on match

        definitionDiv.addEventListener('dragover', handleDragOver);
        definitionDiv.addEventListener('dragenter', handleDragEnter);
        definitionDiv.addEventListener('dragleave', handleDragLeave);
        definitionDiv.addEventListener('drop', handleDrop);

        definitionsContainer.appendChild(definitionDiv);
    });
}

// --- Game Flow Functions ---

async function startGame() {
      
    // Reset state
    score = 0;
    scoreElement.textContent = 0;

    // Fetch the set from the controller
    try {
        const response = await fetch(`index.php?action=cards&do=play&set=${encodeURIComponent(setName)}`, {
            headers: { 'Accept': 'application/json' }
        });
        console.log('Fetch response status:', response.status);
        const data = await response.json();
        console.log('Data returned from server:', data);  
        currentCards = data;
        console.log('currentCards array length:', currentCards.length);
        console.log('currentCards contents:', currentCards);

        if (!Array.isArray(currentCards) || currentCards.length === 0) {
            alert('No cards found for this set.');
            return;
        }

        totalMatchesElement.textContent = currentCards.length;

        // Reset Progress Bar
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
        progressText.style.color = '#030303ff';

        // Shuffle terms for left column
        const shuffledTerms = JSON.parse(JSON.stringify(currentCards));
        shuffleArray(shuffledTerms);

        // Shuffle definitions for right column
        const definitions = JSON.parse(JSON.stringify(currentCards));
        shuffleArray(definitions);

        // Render
        renderTerms(shuffledTerms);
        renderDefinitions(definitions);

    } catch (error) {
        console.error('Failed to load flashcards:', error);
        alert('Failed to load flashcards.');
    }
}

function endGame() {
    document.getElementById('final-score').textContent = `${score}/${currentCards.length}`;
    gameOverModal.classList.remove('hidden');

    fetch('index.php?action=addPoints', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ points: score })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(`${score} points added successfully!`);
        } else {
            console.warn('Failed to update score:', data.error || data);
        }
    })
    .catch(error => console.error('Error updating score:', error));
}


// Initialize the game on load
window.onload = () => startGame();