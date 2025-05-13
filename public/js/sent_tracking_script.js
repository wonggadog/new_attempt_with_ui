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

    // Use event delegation for Delete buttons
    const sentDocsTable = document.getElementById('sentDocsTable');
    if (sentDocsTable) {
        sentDocsTable.addEventListener('click', function(e) {
            const btn = e.target.closest('.delete-sent-btn');
            if (btn) {
                e.stopPropagation();
                const docId = btn.getAttribute('data-doc-id');
                console.log('Delete sent doc clicked', docId); // DEBUG
                if (confirm('Are you sure you want to move this sent document to Trash?')) {
                    fetch(`/api/trash/${docId}/delete`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Remove row from table
                            const row = btn.closest('tr');
                            if (row) row.remove();
                        } else {
                            alert('Failed to delete document.');
                        }
                    });
                }
            }
        });
    }
});