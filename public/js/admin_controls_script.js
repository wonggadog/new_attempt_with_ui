// DOM Elements
const userForm = document.getElementById("userForm");
const userTableBody = document.getElementById("userTableBody");
const messageElement = document.getElementById("message");

// Form submission event
userForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    // Get form values
    const formData = new FormData(userForm);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch("{{ route('admin.users.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            // Reload the page to show the new user
            window.location.reload();
        } else {
            // Handle validation errors
            if (result.errors) {
                const errorMessages = Object.values(result.errors).flat().join('<br>');
                showMessage(errorMessages, "error");
            } else {
                showMessage(result.message || "An error occurred", "error");
            }
        }
    } catch (error) {
        showMessage("Network error: " + error.message, "error");
    }
});

// Delete user event delegation
document.addEventListener("click", async (event) => {
    if (event.target.classList.contains("btn-delete")) {
        const userId = event.target.dataset.id;
        if (confirm("Are you sure you want to delete this user?")) {
            try {
                const response = await fetch(`/admin/users/${userId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    // Remove the row from the table
                    event.target.closest("tr").remove();
                    showMessage("User deleted successfully!", "success");
                } else {
                    showMessage(result.message || "Failed to delete user", "error");
                }
            } catch (error) {
                showMessage("Network error: " + error.message, "error");
            }
        }
    }
});

// Show message
function showMessage(message, type) {
    messageElement.innerHTML = message;
    messageElement.className = `message ${type}`;
    messageElement.style.display = "block";

    // Hide message after 5 seconds
    setTimeout(() => {
        messageElement.style.display = "none";
    }, 5000);
}