// Fetch trashed documents from backend
let deletedDocuments = [];

async function fetchTrashedDocuments() {
    try {
        const response = await fetch('/api/trash');
        const data = await response.json();
        deletedDocuments = data.documents.map(doc => ({
            id: doc.id,
            name: doc.subject || 'Untitled',
            type: (doc.fileType || '').toLowerCase(),
            size: doc.files && doc.files[0] ? (doc.files[0].size || '') : '',
            deletedDate: new Date(doc.dateDeleted),
            sender: doc.sender,
            recipient: doc.recipient,
            notes: doc.notes,
            files: doc.files || [],
        }));
        sortDocuments();
    } catch (err) {
        documentListEl.innerHTML = '<div class="empty-state">Failed to load trash.</div>';
    }
}

// DOM elements
const documentListEl = document.getElementById('document-list');
const searchEl = document.getElementById('search');
const sortByEl = document.getElementById('sort-by');
const restoreAllBtn = document.getElementById('restore-all');
const emptyTrashBtn = document.getElementById('empty-trash');
const itemCountEl = document.getElementById('item-count');

// Format date to readable string
function formatDate(date) {
    const now = new Date();
    const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) {
        return 'Today';
    } else if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else {
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
}

// Get icon for document type
function getDocumentIcon(type) {
    const icons = {
        'docx': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#2563eb"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
        'xlsx': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#16a34a"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
        'pptx': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#dc2626"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>',
        'pdf': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#dc2626"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>',
        'jpg': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#0891b2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>',
        'txt': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#6b7280"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>'
    };
    
    return icons[type] || '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#6b7280"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>';
}

// Render document list
function renderDocuments(documents) {
    if (documents.length === 0) {
        documentListEl.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🗑️</div>
                <div class="empty-title">Trash is empty</div>
                <div class="empty-description">Items you delete will appear here for 30 days before being permanently deleted.</div>
            </div>
        `;
        return;
    }

    documentListEl.innerHTML = '';
    
    documents.forEach(doc => {
        const docEl = document.createElement('div');
        docEl.className = 'document-item';
        docEl.dataset.id = doc.id;
        
        docEl.innerHTML = `
            <div class="document-icon">
                ${getDocumentIcon(doc.type)}
            </div>
            <div class="document-details">
                <div class="document-name">${doc.name}</div>
                <div class="document-meta">
                    <span>${doc.size}</span>
                    <span>Deleted ${formatDate(doc.deletedDate)}</span>
                </div>
            </div>
            <div class="document-actions">
                <button class="btn-ghost restore-btn" title="Restore">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <button class="btn-ghost delete-btn" title="Delete permanently">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        `;
        
        documentListEl.appendChild(docEl);
    });

    // Update item count
    updateItemCount();
    
    // Add event listeners to buttons
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const docId = parseInt(e.target.closest('.document-item').dataset.id);
            restoreDocument(docId);
        });
    });
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const docId = parseInt(e.target.closest('.document-item').dataset.id);
            deleteDocumentPermanently(docId);
        });
    });
}

// Update item count text
function updateItemCount() {
    const count = deletedDocuments.length;
    itemCountEl.textContent = `${count} item${count !== 1 ? 's' : ''} in trash • Will be automatically deleted after 30 days`;
}

// Filter documents based on search
function filterDocuments() {
    const searchTerm = searchEl.value.toLowerCase();
    const filteredDocs = deletedDocuments.filter(doc => 
        doc.name.toLowerCase().includes(searchTerm)
    );
    
    sortDocuments(filteredDocs);
}

// Sort documents
function sortDocuments(docs = deletedDocuments) {
    const sortValue = sortByEl.value;
    let sortedDocs = [...docs];
    
    switch (sortValue) {
        case 'date-desc':
            sortedDocs.sort((a, b) => b.deletedDate - a.deletedDate);
            break;
        case 'date-asc':
            sortedDocs.sort((a, b) => a.deletedDate - b.deletedDate);
            break;
        case 'name-asc':
            sortedDocs.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name-desc':
            sortedDocs.sort((a, b) => b.name.localeCompare(a.name));
            break;
    }
    
    renderDocuments(sortedDocs);
}

// Restore document
function restoreDocument(id) {
    if (confirm('Are you sure you want to restore this document?')) {
        fetch(`/api/trash/${id}/restore`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Laravel?.csrfToken || document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(() => fetchTrashedDocuments());
    }
}

// Delete document permanently
function deleteDocumentPermanently(id) {
    if (confirm('Are you sure you want to permanently delete this document? This action cannot be undone.')) {
        fetch(`/api/trash/${id}/force`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.Laravel?.csrfToken || document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(() => fetchTrashedDocuments());
    }
}

// Empty trash
function emptyTrash() {
    if (deletedDocuments.length === 0) {
        alert('Trash is already empty.');
        return;
    }
    
    if (confirm('Are you sure you want to permanently delete all items in the trash? This action cannot be undone.')) {
        fetch('/api/trash/empty', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.Laravel?.csrfToken || document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(() => fetchTrashedDocuments());
    }
}

// Restore all documents
function restoreAllDocuments() {
    if (deletedDocuments.length === 0) {
        alert('No documents to restore.');
        return;
    }
    
    if (confirm('Are you sure you want to restore all documents?')) {
        fetch('/api/trash/restore-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Laravel?.csrfToken || document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(() => fetchTrashedDocuments());
    }
}

// Event listeners
searchEl.addEventListener('input', filterDocuments);
sortByEl.addEventListener('change', () => filterDocuments());
emptyTrashBtn.addEventListener('click', emptyTrash);
restoreAllBtn.addEventListener('click', restoreAllDocuments);

// Initial render
fetchTrashedDocuments();