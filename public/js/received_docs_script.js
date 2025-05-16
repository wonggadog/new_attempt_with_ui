// DOM Elements
const documentsList = document.getElementById("documentsList");
const documentDetail = document.getElementById("documentDetail");
const allDocumentsContainer = document.getElementById("allDocumentsContainer");
const starredDocumentsContainer = document.getElementById("starredDocumentsContainer");
const urgentDocumentsContainer = document.getElementById("urgentDocumentsContainer");
const backToDocumentsBtn = document.getElementById("backToDocuments");
const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.getElementById("sidebar");
const themeToggle = document.getElementById("themeToggle");
const lightIcon = document.getElementById("lightIcon");
const darkIcon = document.getElementById("darkIcon");
const searchInput = document.getElementById("searchInput");

// Pagination variables
let currentPage = 1;
const itemsPerPage = 6; // Adjust as needed

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  // Initialize avatars
  AvatarHelper.initAvatars(
    '.avatar[data-user="current"]',
    '.avatar:not([data-user="current"])',
    currentUserName
  );

  // Show loading spinner
  allDocumentsContainer.innerHTML = `
    <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  `;

  // Simulate a delay (remove this in production)
  setTimeout(() => {
    renderPaginatedDocuments();
  }, 1000); // Simulate 1-second delay

  // Event listeners
  backToDocumentsBtn.addEventListener("click", showDocumentsList);

  if (sidebarToggle) {
    sidebarToggle.addEventListener("click", toggleSidebar);
  }

  if (themeToggle) {
    themeToggle.addEventListener("click", toggleTheme);
  }

  if (searchInput) {
    searchInput.addEventListener("input", handleSearch);
  }

  // Pagination event listeners
  document.getElementById("prevPage")?.addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--;
      renderPaginatedDocuments();
    }
  });

  document.getElementById("nextPage")?.addEventListener("click", () => {
    if (currentPage * itemsPerPage < documents.length) {
      currentPage++;
      renderPaginatedDocuments();
    }
  });

  // Apply saved theme preference
  applySavedTheme();

  // Send Back Submit Button Handler (moved here to ensure it attaches after DOM is ready)
  const sendBackSubmitBtn = document.getElementById('sendBackSubmitBtn');
  if (sendBackSubmitBtn) {
      console.log('Setting up send back submit button handler');
      sendBackSubmitBtn.addEventListener('click', function() {
          console.log('Send back submit button clicked');
          const fileInput = document.getElementById('sendBackFile');
          const note = document.getElementById('sendBackNote').value;
          
          if (!fileInput.files.length) {
              alert('Please select a file to upload.');
              return;
          }

          console.log('Preparing to send back document:', currentSendBackDocId);
          const formData = new FormData();
          formData.append('file', fileInput.files[0]);
          formData.append('note', note);
          formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

          fetch(`/send-back/${currentSendBackDocId}`, {
              method: 'POST',
              body: formData
          })
          .then(res => res.json())
          .then(data => {
              console.log('Send back response:', data);
              if (data.success) {
                  alert('Document sent back successfully!');
                  // Close modal
                  bootstrap.Modal.getInstance(document.getElementById('sendBackModal')).hide();
                  // Return to documents list
                  showDocumentsList();
              } else {
                  alert(data.message || 'Failed to send back document.');
              }
          })
          .catch(error => {
              console.error('Send back error:', error);
              alert('Failed to send back document.');
          });
      });
  } else {
      console.log('Send back submit button not found');
  }

  document.querySelectorAll('.btn-mark-complete').forEach(btn => {
    btn.addEventListener('click', function() {
      const docId = this.getAttribute('data-doc-id');
      fetch(`/received-documents/mark-complete/${docId}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
        },
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Remove row from table
          const row = document.querySelector(`tr[data-doc-id='${docId}']`);
          if (row) row.remove();
          // Remove due date from calendar
          if (window.documentDueDates) {
            window.documentDueDates = window.documentDueDates.filter(date => date !== data.due_date);
            if (typeof updateCalendar === 'function') updateCalendar();
          }
        }
      });
    });
  });
});

// Render paginated documents
function renderPaginatedDocuments() {
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const paginatedDocuments = documents.slice(startIndex, endIndex);
  renderDocuments(paginatedDocuments);
}

// Render all documents
function renderDocuments(documentsToRender = documents) {
  // Clear containers
  if (allDocumentsContainer) allDocumentsContainer.innerHTML = "";
  if (starredDocumentsContainer) starredDocumentsContainer.innerHTML = "";
  if (urgentDocumentsContainer) urgentDocumentsContainer.innerHTML = "";

  // Check if documents array is valid
  if (!Array.isArray(documentsToRender) || documentsToRender.length === 0) {
    if (allDocumentsContainer) allDocumentsContainer.innerHTML = '<p class="text-muted">No documents found.</p>';
    return;
  }

  // Render documents in each container
  documentsToRender.forEach((doc) => {
    const docCard = createDocumentCard(doc);
    if (allDocumentsContainer) allDocumentsContainer.appendChild(docCard);

    if (doc.isStarred && starredDocumentsContainer) {
      const starredCard = createDocumentCard(doc);
      starredDocumentsContainer.appendChild(starredCard);
    }

    if (doc.isUrgent && urgentDocumentsContainer) {
      const urgentCard = createDocumentCard(doc);
      urgentDocumentsContainer.appendChild(urgentCard);
    }
  });
}

// Create a document card
function createDocumentCard(doc) {
  const col = document.createElement("div");
  col.className = "col";

  const card = document.createElement("div");
  card.className = "card document-card";
  card.dataset.docId = doc.id;
  card.addEventListener("click", () => showDocumentDetail(doc));

  const cardHeader = document.createElement("div");
  cardHeader.className = "card-header";
  cardHeader.innerHTML = `
    <div class="d-flex justify-content-between align-items-start">
      <div class="d-flex align-items-center">
        <div class="avatar me-2" data-name="${doc.sender}">${AvatarHelper.getInitial(doc.sender)}</div>
        <div>
          <div class="fw-medium">${doc.sender}</div>
          <div class="small text-muted">${doc.senderEmail}</div>
        </div>
      </div>
      <div class="d-flex align-items-center">
        ${doc.isUrgent ? '<span class="badge bg-danger me-2">Urgent</span>' : ""}
        <button class="btn btn-icon ${doc.isStarred ? "star-active" : ""}" onclick="event.stopPropagation()">
          <i class="bi ${doc.isStarred ? "bi-star-fill" : "bi-star"}"></i>
        </button>
      </div>
    </div>
  `;

  const cardBody = document.createElement("div");
  cardBody.className = "card-body";
  // Get the filename from doc.files[0] if available
  let fileName = '';
  if (doc.files && doc.files.length > 0) {
    if (typeof doc.files[0] === 'object' && doc.files[0].original) {
      fileName = doc.files[0].original;
    } else if (typeof doc.files[0] === 'string') {
      fileName = doc.files[0].split('/').pop();
    }
  }
  // Prefix with [ACTION TAKEN] if fileType or subject starts with [Sent Back]
  let showFileName = fileName || doc.subject;
  if ((doc.fileType && doc.fileType.startsWith('[Sent Back]')) || (doc.subject && doc.subject.startsWith('[Sent Back]'))) {
    showFileName = '[ACTION TAKEN] ' + showFileName;
  }
  cardBody.innerHTML = `
    <div class="d-flex align-items-center mb-3">
      <div class="document-icon ${doc.iconColor} me-3">
        <i class="bi ${doc.iconClass}"></i>
      </div>
      <div>
        <h5 class="card-title mb-1">${showFileName}</h5>
        <p class="card-text small text-muted">${formatDate(doc.dateReceived)}</p>
      </div>
    </div>
    <div>
      <div class="mb-1"><span class="fw-medium">Action: </span>${doc.action}</div>
      <p class="small text-muted">${doc.fileType ? doc.fileType : 'Unknown'} Document</p>
      <p class="card-text text-muted small mt-2">${doc.notes}</p>
    </div>
  `;

  const cardFooter = document.createElement("div");
  cardFooter.className = "card-footer bg-transparent d-flex justify-content-between";
  cardFooter.innerHTML = `
    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); showDocumentDetail(${JSON.stringify(doc).replace(/"/g, "&quot;")})">
      <i class="bi bi-eye me-1"></i> View Details
    </button>
    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
      <i class="bi bi-download me-1"></i> Download
    </button>
  `;

  card.appendChild(cardHeader);
  card.appendChild(cardBody);
  card.appendChild(cardFooter);
  col.appendChild(card);

  return col;
}

// Define action items that don't require a response
const noResponseRequiredActions = [
    'file/reference',
    'information/notation and return/dissemination'
];

function requiresResponse(action) {
    // Normalize: trim and lowercase
    const normalized = action.trim().toLowerCase();
    return !noResponseRequiredActions.includes(normalized);
}

// Function to update Take Action button state
function updateTakeActionButtonState(actions) {
    const takeActionBtn = document.getElementById('takeActionButton');
    if (!takeActionBtn) {
        console.log('Take Action button not found in DOM');
        return;
    }

    console.log('Updating Take Action button state');
    // Always enable the button - we'll handle the action in the click handler
    takeActionBtn.disabled = false;
    takeActionBtn.classList.add('btn-primary');
    takeActionBtn.classList.remove('btn-secondary');
    takeActionBtn.title = 'Take Action';
}

// Show document detail
function showDocumentDetail(doc) {
    console.log('Showing document detail:', doc);
    // Hide documents list and show detail view
    documentsList.classList.add("d-none");
    documentDetail.classList.remove("d-none");

    // Populate detail view
    document.getElementById("detailSubject").textContent = doc.subject;
    document.getElementById("detailSender").textContent = doc.sender;
    document.getElementById("detailDate").textContent = formatDate(doc.dateReceived, true);
    
    // Set sender avatar with initials
    const detailSenderAvatar = document.getElementById("detailSenderAvatar");
    detailSenderAvatar.setAttribute("data-name", doc.sender);
    detailSenderAvatar.textContent = AvatarHelper.getInitial(doc.sender);

    // Set urgent badge
    const urgentBadge = document.getElementById("detailUrgentBadge");
    if (doc.isUrgent) {
        urgentBadge.classList.remove("d-none");
    } else {
        urgentBadge.classList.add("d-none");
    }

    // Set star button
    const starButton = document.getElementById("detailStarButton");
    if (doc.isStarred) {
        starButton.classList.add("star-active");
        starButton.innerHTML = '<i class="bi bi-star-fill"></i>';
    } else {
        starButton.classList.remove("star-active");
        starButton.innerHTML = '<i class="bi bi-star"></i>';
    }

    // Set actions
    const actionsContainer = document.getElementById("detailActions");
    actionsContainer.innerHTML = `
        <div class="action-item">
            <div class="action-number">1</div>
            <div>${doc.action}</div>
        </div>
    `;

    if (doc.additionalAction) {
        actionsContainer.innerHTML += `
            <div class="action-item">
                <div class="action-number">2</div>
                <div>${doc.additionalAction}</div>
            </div>
        `;
    }

    // Set notes
    document.getElementById("detailNotes").textContent = doc.notes;

    // Set file information
    document.getElementById("detailFileType").textContent = `${doc.fileType ? doc.fileType : 'Unknown'} Document`;
    document.getElementById("detailInfoFileType").textContent = doc.fileType ? doc.fileType : 'Unknown';
    document.getElementById("detailInfoDate").textContent = new Date(doc.dateReceived).toLocaleDateString();

    // Set file icon
    const fileIconContainer = document.getElementById("detailFileIcon");
    fileIconContainer.innerHTML = `<div class="file-preview"><i class="bi ${doc.iconClass} ${doc.iconColor}"></i></div>`;

    // Set filename below the icon
    let detailFileName = '';
    if (doc.files && doc.files.length > 0) {
        if (typeof doc.files[0] === 'object' && doc.files[0].original) {
            detailFileName = doc.files[0].original;
        } else if (typeof doc.files[0] === 'string') {
            detailFileName = doc.files[0].split('/').pop();
        }
    }
    document.getElementById("detailFileName").textContent = detailFileName;

    // Set up Download File button
    const downloadBtn = document.querySelector('.file-box .btn-primary.w-100.mb-2');
    if (downloadBtn) {
        downloadBtn.onclick = function() {
            window.location.href = `/download/${doc.id}`;
        };
    }

    // Make Delete button functional (always re-attach listener)
    const deleteDetailBtn = document.getElementById('deleteDetailBtn');
    if (deleteDetailBtn) {
        // Remove previous listeners by replacing the node
        const newBtn = deleteDetailBtn.cloneNode(true);
        deleteDetailBtn.parentNode.replaceChild(newBtn, deleteDetailBtn);
        newBtn.addEventListener('click', function() {
            console.log('Delete clicked', doc.id); // DEBUG
            if (!doc.id) return;
            if (confirm('Are you sure you want to move this document to Trash?')) {
                fetch(`/api/trash/${doc.id}/delete`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showDocumentsList();
                        // Remove from documents array and re-render
                        const idx = documents.findIndex(d => d.id == doc.id);
                        if (idx !== -1) {
                            documents.splice(idx, 1);
                            renderPaginatedDocuments();
                        }
                    } else {
                        alert('Failed to delete document.');
                    }
                });
            }
        });
    }

    // In showDocumentDetail, set window.currentDetailDocId = doc.id;
    window.currentDetailDocId = doc.id;

    // Update Take Action button state based on actions
    const actions = doc.action.split(',').map(action => action.trim());
    updateTakeActionButtonState(actions);

    // Add click handler for Take Action button
    const takeActionBtn = document.getElementById('takeActionButton');
    if (takeActionBtn) {
        console.log('Setting up Take Action button click handler');
        // Remove any existing listeners
        const newBtn = takeActionBtn.cloneNode(true);
        takeActionBtn.parentNode.replaceChild(newBtn, takeActionBtn);
        
        // Add new click handler
        newBtn.addEventListener('click', function(e) {
            console.log('Take Action button clicked');
            e.preventDefault();
            e.stopPropagation();
            
            // Store the current document ID
            currentSendBackDocId = doc.id;
            console.log('Current document ID for send back:', currentSendBackDocId);
            
            // Show the send back modal
            const sendBackModal = new bootstrap.Modal(document.getElementById('sendBackModal'));
            sendBackModal.show();
        });
    } else {
        console.log('Take Action button not found in document detail view');
    }
}

// Show documents list
function showDocumentsList() {
    documentDetail.classList.add("d-none");
    documentsList.classList.remove("d-none");
}

// Format date
function formatDate(dateString, detailed = false) {
    const date = new Date(dateString);

    if (detailed) {
        return date.toLocaleDateString("en-US", {
            weekday: "long",
            month: "long",
            day: "numeric",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

// Toggle sidebar on mobile
function toggleSidebar() {
    sidebar.classList.toggle("show");
}

// Toggle theme (dark/light mode)
function toggleTheme() {
    document.body.classList.toggle("dark-mode");

    // Toggle icons
    lightIcon.classList.toggle("d-none");
    darkIcon.classList.toggle("d-none");

    // Save preference to localStorage
    const isDarkMode = document.body.classList.contains("dark-mode");
    localStorage.setItem("darkMode", isDarkMode);
}

// Apply saved theme preference
function applySavedTheme() {
    const savedDarkMode = localStorage.getItem("darkMode") === "true";
    if (savedDarkMode) {
        document.body.classList.add("dark-mode");
        lightIcon.classList.add("d-none");
        darkIcon.classList.remove("d-none");
    }
}

// Handle search functionality
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredDocuments = documents.filter((doc) =>
        doc.subject.toLowerCase().includes(searchTerm) ||
        doc.sender.toLowerCase().includes(searchTerm) ||
        doc.action.toLowerCase().includes(searchTerm)
    );
    renderDocuments(filteredDocuments);
}

// Dropdown Functionality
document.addEventListener('DOMContentLoaded', function () {
    const avatarDropdown = document.getElementById('avatarDropdown');
    const avatarDropdownContent = document.getElementById('avatarDropdownContent');

    // Toggle dropdown on avatar click
    avatarDropdown.addEventListener('click', function (e) {
        e.preventDefault();
        avatarDropdownContent.style.display = avatarDropdownContent.style.display === 'block' ? 'none' : 'block';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!avatarDropdown.contains(e.target)) {
            avatarDropdownContent.style.display = 'none';
        }
    });
});

// --- Forwarding Logic ---
let currentForwardDocId = null;

// Open forward modal and populate dropdown
const forwardButton = document.getElementById('forwardButton');
if (forwardButton) {
    forwardButton.addEventListener('click', function() {
        // Store the current document ID
        const docId = documentDetail.querySelector('[data-doc-id]')?.getAttribute('data-doc-id') || (window.currentDetailDocId || null);
        currentForwardDocId = docId;
        // Fetch all users for dropdown
        fetch('/admin_controls/users')
            .then(res => res.json())
            .then(data => {
                const dropdown = document.getElementById('forwardRecipient');
                dropdown.innerHTML = '<option value="">Select a user</option>';
                if (data.success && Array.isArray(data.users)) {
                    data.users.forEach(user => {
                        // Exclude current user and current recipient
                        if (user.name !== currentUserName && user.name !== document.getElementById('detailSender').textContent) {
                            const option = document.createElement('option');
                            option.value = user.name;
                            option.textContent = user.name;
                            dropdown.appendChild(option);
                        }
                    });
                }
                // Show modal
                const forwardModal = new bootstrap.Modal(document.getElementById('forwardModal'));
                forwardModal.show();
            });
    });
}

// Handle forward form submission
const forwardSubmitBtn = document.getElementById('forwardSubmitBtn');
if (forwardSubmitBtn) {
    forwardSubmitBtn.addEventListener('click', function() {
        const recipient = document.getElementById('forwardRecipient').value;
        const note = document.getElementById('forwardNote').value;
        if (!recipient) {
            alert('Please select a recipient.');
            return;
        }
        fetch(`/forward/${currentForwardDocId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ recipient, note }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Document forwarded successfully!');
                    // Optionally close modal
                    bootstrap.Modal.getInstance(document.getElementById('forwardModal')).hide();
                } else {
                    alert(data.message || 'Failed to forward document.');
                }
            })
            .catch(() => alert('Failed to forward document.'));
    });
}

// --- Send Back Logic ---
let currentSendBackDocId = null;