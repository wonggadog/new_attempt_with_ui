document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebarMenu");
    const toggleButton = document.getElementById("sidebarToggle");
  
    toggleButton.addEventListener("click", function () {
      sidebar.classList.toggle("show");
    });
  
    document.addEventListener("click", function (event) {
      const isClickInsideSidebar = sidebar.contains(event.target);
      const isClickOnToggle = toggleButton.contains(event.target);
  
      if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains("show")) {
        sidebar.classList.remove("show");
      }
    });
  
    window.addEventListener("scroll", function () {
      if (window.innerWidth <= 768 && sidebar.classList.contains("show")) {
        sidebar.classList.remove("show");
      }
    });
  
    // Row click navigation
    document.querySelectorAll(".clickable-row").forEach(row => {
      row.style.cursor = "pointer";
      row.addEventListener("click", () => {
        const href = row.getAttribute("data-href");
        if (href) window.location.href = href;
      });
    });
  
    // Dynamic Calendar
    generateCalendar(new Date());
  });
  
  function generateCalendar(date) {
    const monthYearEl = document.getElementById("calendar-month-year");
    const calendarBody = document.getElementById("calendar-body");
  
    const year = date.getFullYear();
    const month = date.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
  
    const monthNames = ["January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"];
    monthYearEl.textContent = `${monthNames[month].toUpperCase()} ${year}`;
  
    calendarBody.innerHTML = "";
    let row = document.createElement("tr");
  
    // Empty cells before the first day
    for (let i = 0; i < firstDay; i++) {
      row.appendChild(document.createElement("td"));
    }
  
    for (let day = 1; day <= daysInMonth; day++) {
      if (row.children.length === 7) {
        calendarBody.appendChild(row);
        row = document.createElement("tr");
      }
  
      const cell = document.createElement("td");
      cell.textContent = day;
      row.appendChild(cell);
    }
  
    if (row.children.length) {
      while (row.children.length < 7) {
        row.appendChild(document.createElement("td"));
      }
      calendarBody.appendChild(row);
    }
  }
