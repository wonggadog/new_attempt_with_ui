document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebarMenu");
    const toggleButton = document.getElementById("sidebarToggle");

    // Toggle sidebar when clicking the button
    toggleButton.addEventListener("click", function () {
        sidebar.classList.toggle("show");
    });

    // Close sidebar when clicking outside
    document.addEventListener("click", function (event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = toggleButton.contains(event.target);

        if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains("show")) {
            sidebar.classList.remove("show"); // Hide sidebar
        }
    });

    // Close sidebar when scrolling outside (on mobile)
    window.addEventListener("scroll", function () {
        if (window.innerWidth <= 768 && sidebar.classList.contains("show")) {
            sidebar.classList.remove("show");
        }
    });
});