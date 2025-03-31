document.addEventListener("DOMContentLoaded", function() {
    // CSRF Token setup for AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // DOM Elements
    const userForm = document.getElementById("userForm");
    const userTableBody = document.getElementById("userTableBody");
    const messageElement = document.getElementById("message");

    // Form submission event
    userForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        // Get form values
        const formData = {
            name: document.getElementById("name").value.trim(),
            email: document.getElementById("email").value.trim(),
            id_number: document.getElementById("idNumber").value.trim(),
            department: document.getElementById("department").value,
            password: document.getElementById("password").value
        };

        // Validate form
        if (!validateForm(formData)) return;

        try {
            const response = await fetch('/admin_controls/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok) {
                // Add new user to table
                addUserToTable(data.user);
                showMessage("User added successfully!", "success");
                userForm.reset();
            } else {
                showMessage(data.message || "Error adding user", "error");
            }
        } catch (error) {
            showMessage("Network error occurred", "error");
        }
    });

    // Delete user event delegation
    userTableBody.addEventListener("click", async (event) => {
        if (event.target.classList.contains("btn-delete")) {
            const userId = event.target.getAttribute("data-id");
            const row = event.target.closest("tr");

            try {
                const response = await fetch(`/admin_controls/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (response.ok) {
                    row.remove();
                    showMessage("User deleted successfully!", "success");
                } else {
                    showMessage("Error deleting user", "error");
                }
            } catch (error) {
                showMessage("Network error occurred", "error");
            }
        }
    });

    // Validate form inputs
    function validateForm({name, email, id_number, department, password}) {
        messageElement.className = "message";
        messageElement.style.display = "none";

        if (!name || !email || !id_number || !department || !password) {
            showMessage("All fields are required", "error");
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showMessage("Please enter a valid email address", "error");
            return false;
        }

        if (password.length < 6) {
            showMessage("Password must be at least 6 characters long", "error");
            return false;
        }

        return true;
    }

    // Add user to the table
    function addUserToTable(user) {
        const row = document.createElement("tr");
        row.setAttribute("data-id", user.id);

        row.innerHTML = `
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.id_number}</td>
            <td>${user.department}</td>
            <td>
                <button class="btn-delete" data-id="${user.id}">Delete</button>
            </td>
        `;

        userTableBody.appendChild(row);
    }

    // Show message
    function showMessage(message, type) {
        messageElement.textContent = message;
        messageElement.className = `message ${type}`;
        messageElement.style.display = "block";

        setTimeout(() => {
            messageElement.style.display = "none";
        }, 3000);
    }
});