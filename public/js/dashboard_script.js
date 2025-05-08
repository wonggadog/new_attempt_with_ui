document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebarMenu")
  const toggleButton = document.getElementById("sidebarToggle")

  // REMOVE or COMMENT OUT the following block:
  // const documents = [
  //   {
  //     id: "DOC-2023-0542",
  //     subject: "Budget Proposal for Q2",
  //     sender: "Finance Department",
  //     dateReceived: new Date(2023, 4, 4), // May 4, 2023
  //     status: "Approved",
  //     statusClass: "success",
  //   },
  //   {
  //     id: "DOC-2023-0541",
  //     subject: "Faculty Meeting Minutes",
  //     sender: "Dean's Office",
  //     dateReceived: new Date(2023, 4, 3), // May 3, 2023
  //     status: "Pending",
  //     statusClass: "warning text-dark",
  //   },
  //   {
  //     id: "DOC-2023-0538",
  //     subject: "Research Grant Application",
  //     sender: "Research Office",
  //     dateReceived: new Date(2023, 4, 2), // May 2, 2023
  //     status: "In Review",
  //     statusClass: "info",
  //   },
  //   {
  //     id: "DOC-2023-0535",
  //     subject: "Equipment Requisition Form",
  //     sender: "IT Department",
  //     dateReceived: new Date(2023, 4, 1), // May 1, 2023
  //     status: "Rejected",
  //     statusClass: "danger",
  //   },
  //   {
  //     id: "DOC-2023-0532",
  //     subject: "Curriculum Development Proposal",
  //     sender: "Academic Affairs",
  //     dateReceived: new Date(2023, 3, 28), // April 28, 2023
  //     status: "Approved",
  //     statusClass: "success",
  //   },
  // ];

  // Calculate due dates for all documents (5 business days after received date)
  // documents.forEach((doc) => {
  //   doc.dueDate = calculateDueDate(doc.dateReceived, 5)
  // })

  // Populate the documents table
  // populateDocumentsTable(documents)

  // Generate calendar with due dates
  generateCalendar(new Date())

  // Sidebar toggle functionality
  toggleButton.addEventListener("click", () => {
    sidebar.classList.toggle("show")
  })

  document.addEventListener("click", (event) => {
    const isClickInsideSidebar = sidebar.contains(event.target)
    const isClickOnToggle = toggleButton.contains(event.target)

    if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains("show")) {
      sidebar.classList.remove("show")
    }
  })

  window.addEventListener("scroll", () => {
    if (window.innerWidth <= 768 && sidebar.classList.contains("show")) {
      sidebar.classList.remove("show")
    }
  })
})

/**
 * Calculate a due date that is X business days after the received date
 * @param {Date} startDate - The date the document was received
 * @param {number} businessDays - Number of business days to add
 * @returns {Date} The calculated due date
 */
function calculateDueDate(startDate, businessDays) {
  const dueDate = new Date(startDate)
  let daysAdded = 0

  while (daysAdded < businessDays) {
    dueDate.setDate(dueDate.getDate() + 1)
    // Skip weekends (0 = Sunday, 6 = Saturday)
    if (dueDate.getDay() !== 0 && dueDate.getDay() !== 6) {
      daysAdded++
    }
  }

  return dueDate
}

/**
 * Format a date as a string (e.g., "May 4, 2023")
 * @param {Date} date - The date to format
 * @returns {string} Formatted date string
 */
function formatDate(date) {
  const options = { year: "numeric", month: "long", day: "numeric" }
  return date.toLocaleDateString("en-US", options)
}

/**
 * Populate the documents table with data
 * @param {Array} documents - Array of document objects
 */
function populateDocumentsTable(documents) {
  const tableBody = document.getElementById("documents-table-body")
  tableBody.innerHTML = ""

  documents.forEach((doc) => {
    const today = new Date()
    const dueDateClass =
      doc.dueDate < today && doc.status !== "Approved" && doc.status !== "Rejected" ? "text-danger fw-bold" : ""

    const row = document.createElement("tr")
    row.className = "clickable-row"
    row.setAttribute("data-href", "#")

    row.innerHTML = `
      <td><span class="fw-medium">${doc.id}</span></td>
      <td>${doc.subject}</td>
      <td>${doc.sender}</td>
      <td>${formatDate(doc.dateReceived)}</td>
      <td class="${dueDateClass}">${formatDate(doc.dueDate)}</td>
      <td><span class="badge bg-${doc.statusClass}">${doc.status}</span></td>
      <td>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i>
          </button>
          <button type="button" class="btn btn-outline-secondary">
            <i class="bi bi-download"></i>
          </button>
        </div>
      </td>
    `

    tableBody.appendChild(row)
  })

  // Row click navigation
  document.querySelectorAll(".clickable-row").forEach((row) => {
    row.style.cursor = "pointer"
    row.addEventListener("click", (e) => {
      // Don't navigate if clicking on buttons
      if (e.target.closest(".btn") || e.target.closest(".btn-group")) {
        return
      }
      const href = row.getAttribute("data-href")
      if (href) window.location.href = href
    })
  })
}

/**
 * Generate calendar with due dates highlighted
 * @param {Date} date - The month to display
 * @param {Array} documents - Array of document objects with due dates
 */
function generateCalendar(date, documents = []) {
  const monthYearEl = document.getElementById("calendar-month-year")
  const calendarBody = document.getElementById("calendar-body")

  const year = date.getFullYear()
  const month = date.getMonth()
  const firstDay = new Date(year, month, 1).getDay()
  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const today = new Date()

  const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ]
  monthYearEl.textContent = `${monthNames[month].toUpperCase()} ${year}`

  calendarBody.innerHTML = ""
  let row = document.createElement("tr")

  // Empty cells before the first day
  for (let i = 0; i < firstDay; i++) {
    row.appendChild(document.createElement("td"))
  }

  // Get all due dates for the current month
  const dueDatesMap = {}
  documents.forEach((doc) => {
    if (doc.dueDate.getMonth() === month && doc.dueDate.getFullYear() === year) {
      const day = doc.dueDate.getDate()
      dueDatesMap[day] = doc
    }
  })

  // Create calendar days
  for (let day = 1; day <= daysInMonth; day++) {
    if (row.children.length === 7) {
      calendarBody.appendChild(row)
      row = document.createElement("tr")
    }

    const cell = document.createElement("td")

    // Check if this day is a due date
    if (dueDatesMap[day]) {
      // Create a container for the day to position the marker
      const dayContainer = document.createElement("div")
      dayContainer.className = "position-relative"

      // Add the day number
      const dayNumber = document.createElement("span")
      dayNumber.textContent = day

      // Add the due date marker
      const marker = document.createElement("span")
      marker.className = "due-date-marker"

      // Add elements to the container
      dayContainer.appendChild(dayNumber)
      dayContainer.appendChild(marker)

      // Add the container to the cell
      cell.appendChild(dayContainer)
      cell.className = "due-date-cell"
    } else {
      cell.textContent = day
    }

    // Highlight today's date
    if (today.getDate() === day && today.getMonth() === month && today.getFullYear() === year) {
      cell.classList.add("today")
    }

    row.appendChild(cell)
  }

  if (row.children.length) {
    while (row.children.length < 7) {
      row.appendChild(document.createElement("td"))
    }
    calendarBody.appendChild(row)
  }
}
