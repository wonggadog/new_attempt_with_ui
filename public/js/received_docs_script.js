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
  allDocumentsContainer.innerHTML = "";
  starredDocumentsContainer.innerHTML = "";
  urgentDocumentsContainer.innerHTML = "";

  // Check if documents array is valid
  if (!Array.isArray(documentsToRender) || documentsToRender.length === 0) {
    allDocumentsContainer.innerHTML = '<p class="text-muted">No documents found.</p>';
    return;
  }

  // Render documents in each container
  documentsToRender.forEach((doc) => {
    const docCard = createDocumentCard(doc);
    allDocumentsContainer.appendChild(docCard);

    if (doc.isStarred) {
      const starredCard = createDocumentCard(doc);
      starredDocumentsContainer.appendChild(starredCard);
    }

    if (doc.isUrgent) {
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
  cardBody.innerHTML = `
    <div class="d-flex align-items-center mb-3">
      <div class="document-icon ${doc.iconColor} me-3">
        <i class="bi ${doc.iconClass}"></i>
      </div>
      <div>
        <h5 class="card-title mb-1">${doc.subject}</h5>
        <p class="card-text small text-muted">${formatDate(doc.dateReceived)}</p>
      </div>
    </div>
    <div>
      <div class="mb-1"><span class="fw-medium">Action: </span>${doc.action}</div>
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

// Show document detail
function showDocumentDetail(doc) {
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
  document.getElementById("detailFileName").textContent = `${doc.subject}.${doc.fileType.toLowerCase()}`;
  document.getElementById("detailFileType").textContent = `${doc.fileType} Document`;
  document.getElementById("detailInfoFileType").textContent = doc.fileType;
  document.getElementById("detailInfoDate").textContent = new Date(doc.dateReceived).toLocaleDateString();

  // Set file icon
  const fileIconContainer = document.getElementById("detailFileIcon");
  fileIconContainer.innerHTML = `<div class="file-preview"><i class="bi ${doc.iconClass} ${doc.iconColor}"></i></div>`;
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