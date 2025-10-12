function showCreateForm() {
    document.getElementById('createForm').style.display = 'block';
    document.getElementById('filterForm').style.display = 'none';
    // Add event listeners for date changes
    document.getElementById('date_needed').addEventListener('change', updateItemAvailability);
    document.getElementById('date_of_return').addEventListener('change', updateItemAvailability);
    // Initialize borrower dropdown
    initializeBorrowerDropdown();
    // Initialize item dropdown
    initializeItemDropdown();
}

function hideCreateForm() {
    document.getElementById('createForm').style.display = 'none';
}

function showFilterForm() {
    document.getElementById('filterForm').style.display = 'block';
    document.getElementById('createForm').style.display = 'none';
}

function hideFilterForm() {
    document.getElementById('filterForm').style.display = 'none';
    // Clear filters
    window.location.href = 'transactions.php';
}

function removeItem(button) {
    button.parentElement.remove();
}

function showReturnForm(transactionId) {
    document.getElementById('returnTransactionId').value = transactionId;
    document.getElementById('returnItemsList').innerHTML = '';

    // Find the transaction row with the matching transactionId
    const rows = document.querySelectorAll('tbody tr');
    let itemsData = null;
    rows.forEach(row => {
        const idCell = row.querySelector('td:first-child');
        if (idCell && idCell.textContent == transactionId) {
            itemsData = row.querySelector('td[data-items]').getAttribute('data-items');
        }
    });

    if (!itemsData) {
        document.getElementById('returnItemsList').innerHTML = '<p>No items found for this transaction.</p>';
        return;
    }

    // Decode HTML entities
    function decodeHtmlEntities(str) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = str;
        return textarea.value;
    }
    itemsData = decodeHtmlEntities(itemsData);

    let items = [];
    try {
        items = JSON.parse(itemsData);
    } catch (e) {
        document.getElementById('returnItemsList').innerHTML = '<p>Error parsing items data.</p>';
        return;
    }

    if (items.length === 0) {
        document.getElementById('returnItemsList').innerHTML = '<p>No items found for this transaction.</p>';
        return;
    }

    let html = '';
    items.forEach(item => {
        const maxReturnable = item.quantity_issued - item.quantity_returned;
        html += `
            <div style="margin-bottom: 10px;">
                <label>
                    <strong>${item.item_name}</strong> (Issued: ${item.quantity_issued}, Returned: ${item.quantity_returned})<br>
                    Quantity to return:
                    <input type="number" name="quantities_returned[${item.borrowed_item_id}]" min="0" max="${maxReturnable}" value="${maxReturnable}" required>
                </label>
            </div>
        `;
    });
    document.getElementById('returnItemsList').innerHTML = html;
    document.getElementById('returnModal').style.display = 'block';
}

function hideReturnForm() {
    document.getElementById('returnModal').style.display = 'none';
}

function initializeBorrowerDropdown() {
    const searchInput = document.getElementById('borrower_search');
    const optionsDiv = document.getElementById('borrower_options');
    const hiddenInput = document.getElementById('borrower_id');
    const dropdown = document.getElementById('borrower_dropdown');

    // Show dropdown on input focus
    searchInput.addEventListener('focus', function() {
        optionsDiv.style.display = 'block';
    });

    // Filter options on input
    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const options = optionsDiv.querySelectorAll('.dropdown-option');
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        optionsDiv.style.display = 'block';
    });

    // Select option
    optionsDiv.addEventListener('click', function(e) {
        if (e.target.classList.contains('dropdown-option')) {
            const value = e.target.getAttribute('data-value');
            const text = e.target.textContent;
            hiddenInput.value = value;
            searchInput.value = text;
            optionsDiv.style.display = 'none';
        }
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            optionsDiv.style.display = 'none';
        }
    });
}

function initializeItemDropdown() {
    const searchInput = document.getElementById('item_search');
    const optionsDiv = document.getElementById('item_options');
    const dropdown = document.getElementById('item_dropdown');

    if (!searchInput || !optionsDiv) return;

    // Show dropdown on input focus
    searchInput.addEventListener('focus', function() {
        optionsDiv.style.display = 'block';
    });

    // Filter options on input
    searchInput.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const options = optionsDiv.querySelectorAll('.dropdown-option');
        let hasVisible = false;
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = 'block';
                hasVisible = true;
            } else {
                option.style.display = 'none';
            }
        });
        optionsDiv.style.display = hasVisible || filter === '' ? 'block' : 'none';
    });

    // Select option
    optionsDiv.addEventListener('click', function(e) {
        if (e.target.classList.contains('dropdown-option')) {
            const itemId = e.target.getAttribute('data-value');
            const itemName = e.target.getAttribute('data-name');
            const available = parseInt(e.target.getAttribute('data-available')) || 0;
            addItemToList(itemId, itemName, available);
            searchInput.value = '';
            optionsDiv.style.display = 'none';
        }
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            optionsDiv.style.display = 'none';
        }
    });
}

function addItemToList(itemId, itemName, available) {
    if (!itemId || !itemName || available <= 0) {
        return;
    }

    const container = document.getElementById('selectedItems');

    // Check if item already added
    const existingInputs = container.querySelectorAll('input[name^="items"]');
    for (let input of existingInputs) {
        if (input.name === `items[${itemId}]`) {
            alert('This item is already added.');
            return;
        }
    }

    const itemDiv = document.createElement('div');
    itemDiv.innerHTML = `
        <div style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <strong>${itemName}</strong> (Available: <span id="avail-${itemId}">${available}</span>)<br>
            <label>Quantity to borrow:
                <input type="number" name="items[${itemId}]" min="1" max="${available}" value="1" style="margin-left: 10px;">
            </label>
            <button type="button" onclick="removeItem(this)" style="margin-left: 10px; background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px;">Remove</button>
        </div>
    `;

    container.appendChild(itemDiv);

    // Add event listener to update available display
    const qtyInput = itemDiv.querySelector(`input[name="items[${itemId}]"]`);
    const availSpan = itemDiv.querySelector(`#avail-${itemId}`);
    qtyInput.addEventListener('input', function() {
        const qty = parseInt(this.value) || 0;
        if (qty > available) {
            this.value = available;
        }
        availSpan.textContent = available - qty;
    });
}

function updateItemAvailability() {
    const dateNeeded = document.getElementById('date_needed').value;
    const dateOfReturn = document.getElementById('date_of_return').value;

    if (!dateNeeded || !dateOfReturn) {
        return; // Don't update if dates are not set
    }

    fetch(`../api/get_item_availability.php?date_needed=${dateNeeded}&date_of_return=${dateOfReturn}`)
        .then(response => response.json())
        .then(data => {
            const optionsDiv = document.getElementById('item_options');
            if (!optionsDiv) return;
            const options = optionsDiv.querySelectorAll('.dropdown-option');
            options.forEach(option => {
                const itemId = option.getAttribute('data-value');
                const itemName = option.getAttribute('data-name');
                const available = data[itemId] || 0;
                option.setAttribute('data-available', available);
                option.textContent = `${itemName} (${available} available)`;
            });
        })
        .catch(error => console.error('Error updating availability:', error));
}
