document.addEventListener("DOMContentLoaded", function() {
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // DOM Elements
    const elements = {
        form: document.getElementById("userForm"),
        tableBody: document.getElementById("userTableBody"),
        message: document.getElementById("message"),
        departmentFilter: document.getElementById("departmentFilter"),
        clearFilters: document.getElementById("clearFilters"),
        filterStatus: document.getElementById("filterStatus"),
        name: document.getElementById("name"),
        email: document.getElementById("email"),
        idNumber: document.getElementById("idNumber"),
        department: document.getElementById("department"),
        password: document.getElementById("password")
    };

    // State
    const state = {
        currentPage: 1,
        totalPages: 1,
        filters: {
            department: 'all'
        },
        loading: false
    };

    // Initial load
    loadUsers();

    // Event Listeners
    elements.form.addEventListener("submit", handleFormSubmit);
    elements.tableBody.addEventListener("click", handleTableClick);
    if (elements.departmentFilter) {
        elements.departmentFilter.addEventListener("change", handleFilterChange);
    }
    if (elements.clearFilters) {
        elements.clearFilters.addEventListener("click", clearFilters);
    }

    // Functions
    async function loadUsers() {
        try {
            if (state.loading) return;
            state.loading = true;
            showLoading(true);
            
            const params = new URLSearchParams({
                page: state.currentPage,
                ...(state.filters.department !== 'all' && { department: state.filters.department })
            });

            const response = await fetch(`/admin_controls/users?${params}`);
            const data = await response.json();

            if (data.success) {
                renderUsers(data.users);
                updateFilterStatus(data.filters.department);
            } else {
                showMessage("Error loading users", "error");
            }
        } catch (error) {
            console.error("Error:", error);
            showMessage("Network error occurred", "error");
        } finally {
            state.loading = false;
            showLoading(false);
        }
    }

    function renderUsers(users) {
        elements.tableBody.innerHTML = users.length > 0 
            ? users.map(user => `
                <tr data-id="${user.id}">
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.id_number}</td>
                    <td>${user.department}</td>
                    <td>
                        <button class="btn-edit" data-id="${user.id}">Edit</button>
                        <button class="btn-delete" data-id="${user.id}">Delete</button>
                    </td>
                </tr>
              `).join('')
            : `<tr><td colspan="5" class="no-data">No users found</td></tr>`;
    }

    function updateFilterStatus(activeFilter) {
        state.filters.department = activeFilter || 'all';
        elements.departmentFilter.value = state.filters.department;
        
        if (state.filters.department === 'all') {
            elements.filterStatus.textContent = '';
            elements.filterStatus.className = 'filter-status';
        } else {
            elements.filterStatus.textContent = `Showing: ${state.filters.department}`;
            elements.filterStatus.className = 'filter-status active';
        }
    }

    function handleFilterChange() {
        state.filters.department = elements.departmentFilter.value;
        state.currentPage = 1;
        loadUsers();
    }

    function clearFilters() {
        elements.departmentFilter.value = 'all';
        state.filters.department = 'all';
        state.currentPage = 1;
        loadUsers();
    }

    async function handleFormSubmit(event) {
        event.preventDefault();
        
        try {
            showLoading(true);
            
            const formData = {
                name: elements.name.value.trim(),
                email: elements.email.value.trim(),
                id_number: elements.idNumber.value.trim(),
                department: elements.department.value,
                password: elements.password.value
            };

            if (!validateForm(formData)) {
                showLoading(false);
                return;
            }

            const response = await fetch('/admin_controls/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to create user');
            }

            loadUsers();
            showMessage('User added successfully!', 'success');
            elements.form.reset();

        } catch (error) {
            console.error('Error:', error);
            showMessage(error.message || 'Network error occurred', 'error');
        } finally {
            showLoading(false);
        }
    }

    async function handleTableClick(event) {
        if (event.target.classList.contains('btn-edit')) {
            const userId = event.target.getAttribute('data-id');
            showEditUserModal(userId);
        } else if (event.target.classList.contains('btn-delete')) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            const userId = event.target.getAttribute('data-id');
            try {
                showLoading(true);
                const response = await fetch(`/admin_controls/users/${userId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });

                if (response.ok) {
                    loadUsers();
                    showMessage('User deleted successfully!', 'success');
                } else {
                    showMessage('Error deleting user', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Network error occurred', 'error');
            } finally {
                showLoading(false);
            }
        }
    }

    function getFormData() {
        return {
            name: elements.name.value.trim(),
            email: elements.email.value.trim(),
            id_number: elements.idNumber.value.trim(),
            department: elements.department.value,
            password: elements.password.value
        };
    }

    function validateForm(formData) {
        if (!formData.name || !formData.email || !formData.id_number || !formData.department || !formData.password) {
            showMessage("All fields are required", "error");
            return false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            showMessage("Please enter a valid email address", "error");
            return false;
        }

        if (formData.password.length < 6) {
            showMessage("Password must be at least 6 characters long", "error");
            return false;
        }

        return true;
    }

    function showMessage(message, type) {
        elements.message.textContent = message;
        elements.message.className = `message ${type}`;
        elements.message.style.display = "block";
        setTimeout(() => elements.message.style.display = "none", 5000);
    }

    function showLoading(loading) {
        // Only target the form submission button and reset button
        const formButtons = document.querySelectorAll('#userForm button');
        formButtons.forEach(btn => {
            if (loading) {
                btn.disabled = true;
                if (btn.type === 'submit') {
                    btn.innerHTML = '<span class="spinner"></span> Adding...';
                }
            } else {
                btn.disabled = false;
                if (btn.type === 'submit') {
                    btn.textContent = 'Add User';
                }
            }
        });
    }

    async function handleEditUser(userId) {
        const userRow = document.querySelector(`tr[data-id='${userId}']`);
        const name = userRow.querySelector('td:nth-child(1)').textContent;
        const email = userRow.querySelector('td:nth-child(2)').textContent;
        const idNumber = userRow.querySelector('td:nth-child(3)').textContent;
        const department = userRow.querySelector('td:nth-child(4)').textContent;

        // Populate the modal with user data
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editIdNumber').value = idNumber;
        document.getElementById('editDepartment').value = department;

        // Re-apply the visible container styles for the modal
        const editUserModal = document.getElementById('editUserModal');
        editUserModal.style.display = 'block'; // Set modal display style to block

        // Attach the user ID to the form for submission
        const editUserForm = document.getElementById('editUserForm');
        editUserForm.dataset.editing = userId;
    }

    // Close modal functionality
    document.getElementById('closeEditModal').addEventListener('click', () => {
        const editUserModal = document.getElementById('editUserModal');
        editUserModal.style.display = 'none';
    });

    // Handle edit form submission
    document.getElementById('editUserForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const userId = this.dataset.editing;
        const formData = {
            name: document.getElementById('editName').value.trim(),
            email: document.getElementById('editEmail').value.trim(),
            id_number: document.getElementById('editIdNumber').value.trim(),
            department: document.getElementById('editDepartment').value,
            password: document.getElementById('editPassword').value.trim()
        };

        try {
            const response = await fetch(`/admin_controls/users/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok) {
                loadUsers();
                showMessage('User updated successfully!', 'success');
                this.reset();
                delete this.dataset.editing;
                document.getElementById('editUserModal').style.display = 'none';
            } else {
                showMessage(data.message || 'Error updating user', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Network error occurred', 'error');
        }
    });

    // Show the modal when editing a user
    function showEditUserModal(userId) {
        const userRow = document.querySelector(`tr[data-id='${userId}']`);
        const name = userRow.querySelector('td:nth-child(1)').textContent;
        const email = userRow.querySelector('td:nth-child(2)').textContent;
        const idNumber = userRow.querySelector('td:nth-child(3)').textContent;
        const department = userRow.querySelector('td:nth-child(4)').textContent;

        // Populate the modal with user data
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editIdNumber').value = idNumber;
        document.getElementById('editDepartment').value = department;

        // Show the modal as a pop-up dialog box
        const editUserModal = document.getElementById('editUserModal');
        editUserModal.style.display = 'flex';

        // Attach the user ID to the form for submission
        const editUserForm = document.getElementById('editUserForm');
        editUserForm.dataset.editing = userId;
    }

    // Close the modal
    function closeEditUserModal() {
        const editUserModal = document.getElementById('editUserModal');
        editUserModal.style.display = 'none';
    }

    // Attach event listener to close button
    document.getElementById('closeEditModal').addEventListener('click', closeEditUserModal);
});