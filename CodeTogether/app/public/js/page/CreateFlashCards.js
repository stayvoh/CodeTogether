// /public/js/page/CreateFlashcards.js

const cardsContainer = document.getElementById('cards-container');
const addCardBtn = document.getElementById('add-card-btn');

let cardCount = 0;

// Function to create a card row
function createCardRow() {
    cardCount++;

    const cardRow = document.createElement('div');
    cardRow.className = 'set-box card-row';

    cardRow.innerHTML = `
        <h3 class="matrix-text">Card ${cardCount} <span class="remove-card">&times;</span></h3>
        <p>
            <input type="text" name="terms[]" class="form-control matrix-text mb-2" placeholder="Term ${cardCount}" required>
            <input type="text" name="definitions[]" class="form-control matrix-text" placeholder="Definition ${cardCount}" required>
        </p>
    `;

    // Remove card on X click
    const removeBtn = cardRow.querySelector('.remove-card');
    removeBtn.addEventListener('click', () => {
        cardsContainer.removeChild(cardRow);
        updateCardNumbers();
    });

    cardsContainer.appendChild(cardRow);
}

// Update card headings after deletion
function updateCardNumbers() {
    const cardRows = cardsContainer.querySelectorAll('.card-row');
    cardRows.forEach((row, index) => {
        row.querySelector('h3').childNodes[0].nodeValue = `Card ${index + 1} `;
    });
    cardCount = cardRows.length;
}

// Add initial card
createCardRow();

// Add new card on button click
addCardBtn.addEventListener('click', createCardRow);
