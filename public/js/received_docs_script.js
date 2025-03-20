// Sample data for documents
const documents = [
    {
      id: 1,
      sender: "Sarah Johnson",
      senderEmail: "sarah.j@company.com",
      subject: "Q3 Financial Report",
      fileType: "PDF",
      iconClass: "bi-file-earmark-pdf",
      iconColor: "pdf",
      dateReceived: "2023-10-15T09:30:00",
      action: "Review and Approve",
      additionalAction: "Forward to Finance Team",
      notes: "Please review by Friday and provide feedback on the Q3 projections.",
      isStarred: true,
      isUrgent: true,
    },
    {
      id: 2,
      sender: "Michael Chen",
      senderEmail: "m.chen@tech.co",
      subject: "Product Design Mockups",
      fileType: "Image",
      iconClass: "bi-file-earmark-image",
      iconColor: "image",
      dateReceived: "2023-10-14T14:45:00",
      action: "Review and Comment",
      additionalAction: "Share with Design Team",
      notes: "These are the final mockups for the new dashboard interface.",
      isStarred: false,
      isUrgent: false,
    },
    {
      id: 3,
      sender: "Alex Rodriguez",
      senderEmail: "alex.r@legal.org",
      subject: "Contract Agreement",
      fileType: "Document",
      iconClass: "bi-file-earmark-text",
      iconColor: "doc",
      dateReceived: "2023-10-13T11:20:00",
      action: "Sign and Return",
      additionalAction: "Keep Copy for Records",
      notes: "Please sign the contract and return it by end of week.",
      isStarred: true,
      isUrgent: true,
    },
    {
      id: 4,
      sender: "Emily Watson",
      senderEmail: "e.watson@marketing.net",
      subject: "Marketing Campaign Proposal",
      fileType: "Presentation",
      iconClass: "bi-file-earmark-slides",
      iconColor: "presentation",
      dateReceived: "2023-10-12T16:10:00",
      action: "Review and Approve",
      additionalAction: "Schedule Meeting to Discuss",
      notes: "Proposal for the holiday marketing campaign. Need approval by Monday.",
      isStarred: false,
      isUrgent: false,
    },
  ]
  
  // DOM Elements
  const documentsList = document.getElementById("documentsList")
  const documentDetail = document.getElementById("documentDetail")
  const allDocumentsContainer = document.getElementById("allDocumentsContainer")
  const starredDocumentsContainer = document.getElementById("starredDocumentsContainer")
  const urgentDocumentsContainer = document.getElementById("urgentDocumentsContainer")
  const backToDocumentsBtn = document.getElementById("backToDocuments")
  const sidebarToggle = document.getElementById("sidebarToggle")
  const sidebar = document.getElementById("sidebar")
  const themeToggle = document.getElementById("themeToggle")
  const lightIcon = document.getElementById("lightIcon")
  const darkIcon = document.getElementById("darkIcon")
  
  // Initialize the application
  document.addEventListener("DOMContentLoaded", () => {
    // Render documents
    renderDocuments()
  
    // Event listeners
    backToDocumentsBtn.addEventListener("click", showDocumentsList)
  
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", toggleSidebar)
    }
  
    if (themeToggle) {
      themeToggle.addEventListener("click", toggleTheme)
    }
  })
  
  // Render all documents
  function renderDocuments() {
    // Clear containers
    allDocumentsContainer.innerHTML = ""
    starredDocumentsContainer.innerHTML = ""
    urgentDocumentsContainer.innerHTML = ""
  
    // Render documents in each container
    documents.forEach((doc) => {
      const docCard = createDocumentCard(doc)
      allDocumentsContainer.appendChild(docCard)
  
      if (doc.isStarred) {
        const starredCard = createDocumentCard(doc)
        starredDocumentsContainer.appendChild(starredCard)
      }
  
      if (doc.isUrgent) {
        const urgentCard = createDocumentCard(doc)
        urgentDocumentsContainer.appendChild(urgentCard)
      }
    })
  }
  
  // Create a document card
  function createDocumentCard(doc) {
    const col = document.createElement("div")
    col.className = "col"
  
    const card = document.createElement("div")
    card.className = "card document-card"
    card.dataset.docId = doc.id
    card.addEventListener("click", () => showDocumentDetail(doc))
  
    const cardHeader = document.createElement("div")
    cardHeader.className = "card-header"
    cardHeader.innerHTML = `
      <div class="d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center">
          <div class="avatar me-2">${doc.sender.charAt(0)}</div>
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
    `
  
    const cardBody = document.createElement("div")
    cardBody.className = "card-body"
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
    `
  
    const cardFooter = document.createElement("div")
    cardFooter.className = "card-footer bg-transparent d-flex justify-content-between"
    cardFooter.innerHTML = `
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); showDocumentDetail(${JSON.stringify(doc).replace(/"/g, "&quot;")})">
        <i class="bi bi-eye me-1"></i> View Details
      </button>
      <button class="btn btn-primary btn-sm" onclick="event.stopPropagation()">
        <i class="bi bi-download me-1"></i> Download
      </button>
    `
  
    card.appendChild(cardHeader)
    card.appendChild(cardBody)
    card.appendChild(cardFooter)
    col.appendChild(card)
  
    return col
  }
  
  // Show document detail
  function showDocumentDetail(doc) {
    // Hide documents list and show detail view
    documentsList.classList.add("d-none")
    documentDetail.classList.remove("d-none")
  
    // Populate detail view
    document.getElementById("detailSubject").textContent = doc.subject
    document.getElementById("detailSender").textContent = doc.sender
    document.getElementById("detailDate").textContent = formatDate(doc.dateReceived, true)
    document.getElementById("detailSenderAvatar").textContent = doc.sender.charAt(0)
  
    // Set urgent badge
    const urgentBadge = document.getElementById("detailUrgentBadge")
    if (doc.isUrgent) {
      urgentBadge.classList.remove("d-none")
    } else {
      urgentBadge.classList.add("d-none")
    }
  
    // Set star button
    const starButton = document.getElementById("detailStarButton")
    if (doc.isStarred) {
      starButton.classList.add("star-active")
      starButton.innerHTML = '<i class="bi bi-star-fill"></i>'
    } else {
      starButton.classList.remove("star-active")
      starButton.innerHTML = '<i class="bi bi-star"></i>'
    }
  
    // Set actions
    const actionsContainer = document.getElementById("detailActions")
    actionsContainer.innerHTML = `
      <div class="action-item">
        <div class="action-number">1</div>
        <div>${doc.action}</div>
      </div>
    `
  
    if (doc.additionalAction) {
      actionsContainer.innerHTML += `
        <div class="action-item">
          <div class="action-number">2</div>
          <div>${doc.additionalAction}</div>
        </div>
      `
    }
  
    // Set notes
    document.getElementById("detailNotes").textContent = doc.notes
  
    // Set file information
    document.getElementById("detailFileName").textContent = `${doc.subject}.${doc.fileType.toLowerCase()}`
    document.getElementById("detailFileType").textContent = `${doc.fileType} Document`
    document.getElementById("detailInfoFileType").textContent = doc.fileType
    document.getElementById("detailInfoDate").textContent = new Date(doc.dateReceived).toLocaleDateString()
  
    // Set file icon
    const fileIconContainer = document.getElementById("detailFileIcon")
    fileIconContainer.innerHTML = `<div class="file-preview"><i class="bi ${doc.iconClass} ${doc.iconColor}"></i></div>`
  }
  
  // Show documents list
  function showDocumentsList() {
    documentDetail.classList.add("d-none")
    documentsList.classList.remove("d-none")
  }
  
  // Format date
  function formatDate(dateString, detailed = false) {
    const date = new Date(dateString)
  
    if (detailed) {
      return date.toLocaleDateString("en-US", {
        weekday: "long",
        month: "long",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      })
    }
  
    return date.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    })
  }
  
  // Toggle sidebar on mobile
  function toggleSidebar() {
    sidebar.classList.toggle("show")
  }
  
  // Toggle theme (dark/light mode)
  function toggleTheme() {
    document.body.classList.toggle("dark-mode")
  
    // Toggle icons
    lightIcon.classList.toggle("d-none")
    darkIcon.classList.toggle("d-none")
  
    // Save preference to localStorage
    const isDarkMode = document.body.classList.contains("dark-mode")
    localStorage.setItem("darkMode", isDarkMode)
  }
  
  // Check for saved theme preference
  const savedDarkMode = localStorage.getItem("darkMode") === "true"
  if (savedDarkMode) {
    document.body.classList.add("dark-mode")
    lightIcon.classList.add("d-none")
    darkIcon.classList.remove("d-none")
  }
  
  