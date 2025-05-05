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

  });

  document.querySelectorAll('.toggle-switch').forEach(toggle => {
    toggle.addEventListener('change', (event) => {
      console.log(`${event.target.id} is ${event.target.checked ? 'ON' : 'OFF'}`);
    });
  });
  
  document.querySelector('#deleteAccountModal .btn-danger')?.addEventListener('click', () => {
    console.log("Account deletion process initiated.");
  });