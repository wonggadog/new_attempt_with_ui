document.addEventListener("DOMContentLoaded", function() {
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // DOM Elements
    const elements = {
        form: document.getElementById("userForm"),
        tableBody: document.getElementById("userTableBody"),
        message: document.getElementById("message"),
        prevPage: document.getElementById("prevPage"),
        nextPage: document.getElementById("nextPage"),
        pageInfo: document.getElementById("pageInfo"),
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
    elements.prevPage.addEventListener("click", () => navigatePage(-1));
    elements.nextPage.addEventListener("click", () => navigatePage(1));
    elements.departmentFilter.addEventListener("change", handleFilterChange);
    elements.clearFilters.addEventListener("click", clearFilters);

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
                updatePagination(data.pagination.current_page, data.pagination.last_page);
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

    function updatePagination(currentPage, lastPage) {
        state.currentPage = currentPage;
        state.totalPages = lastPage;
        elements.pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
        elements.prevPage.disabled = currentPage <= 1;
        elements.nextPage.disabled = currentPage >= lastPage;
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
        const userId = elements.form.dataset.editing;
        const formData = getFormData();

        if (!validateForm(formData)) return;

        try {
            showLoading(true);
            const response = await fetch(`/admin_controls/users/${userId}`, {
                method: userId ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok) {
                loadUsers();
                showMessage(userId ? 'User updated successfully!' : 'User added successfully!', 'success');
                elements.form.reset();
                delete elements.form.dataset.editing;
            } else {
                showMessage(data.message || 'Error saving user', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Network error occurred', 'error');
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

    function navigatePage(direction) {
        const newPage = state.currentPage + direction;
        if (newPage > 0 && newPage <= state.totalPages) {
            state.currentPage = newPage;
            loadUsers();
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
        setTimeout(() => elements.message.style.display = "none", 3000);
    }

    function showLoading(loading) {
        const buttons = document.querySelectorAll('button:not(.btn-delete)');
        buttons.forEach(btn => {
            if (loading) {
                btn.setAttribute('data-original-text', btn.textContent);
                btn.innerHTML = '<span class="spinner"></span> ' + (btn.textContent.includes('Add') ? 'Adding...' : 'Loading...');
                btn.disabled = true;
            } else {
                const originalText = btn.getAttribute('data-original-text');
                if (originalText) btn.textContent = originalText;
                btn.disabled = false;
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

        // Show the modal
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