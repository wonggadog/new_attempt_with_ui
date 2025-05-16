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

  // Ensure calendar elements exist before updating
  if (document.getElementById('calendar-month-year') && document.getElementById('calendar-body')) {
    updateCalendar();
  } else {
    console.error('Calendar elements not found in the DOM.');
  }

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
function updateCalendar() {
  const today = new Date();
  const currentMonth = today.getMonth();
  const currentYear = today.getFullYear();
  const firstDay = new Date(currentYear, currentMonth, 1);
  const lastDay = new Date(currentYear, currentMonth + 1, 0);
  const startingDay = firstDay.getDay();
  const totalDays = lastDay.getDate();
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

  const monthYearElement = document.getElementById('calendar-month-year');
  const calendarBodyElement = document.getElementById('calendar-body');
  if (!monthYearElement || !calendarBodyElement) {
    console.error('Calendar elements not found in the DOM.');
    return;
  }

  monthYearElement.textContent = monthNames[currentMonth] + ' ' + currentYear;

  let calendarHTML = '<tr>';
  for (let i = 0; i < startingDay; i++) {
    calendarHTML += '<td></td>';
  }

  for (let day = 1; day <= totalDays; day++) {
    if ((day + startingDay - 1) % 7 === 0) {
      calendarHTML += '</tr><tr>';
    }
    const isToday = day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear();
    const isDueDate = checkIfDueDate(day, currentMonth, currentYear); // Check if this day is a due date
    let cellContent = day;
    if (isDueDate) {
      cellContent = `<span class='due-date-circle'>${day}</span>`;
    }
    calendarHTML += `<td class="${isToday ? 'today' : ''} ${isDueDate ? 'due-date' : ''}">${cellContent}</td>`;
  }

  let totalCells = totalDays + startingDay;
  while (totalCells % 7 !== 0) {
    calendarHTML += '<td></td>';
    totalCells++;
  }
  calendarHTML += '</tr>';
  calendarBodyElement.innerHTML = calendarHTML;
}

function checkIfDueDate(day, month, year) {
  if (!window.documentDueDates) return false;
  const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  return window.documentDueDates.includes(dateStr);
}
