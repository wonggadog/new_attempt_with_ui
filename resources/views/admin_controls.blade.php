<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/admin_controls_styles.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Dashboard</h1>
            <nav class="admin-nav">
                <a href="{{ route('admin.file-types.index') }}" class="nav-link">Manage File Types</a>
            </nav>
        </header>
        
        <main>
            <section class="form-section">
                <h2>Add New User</h2>
                <form id="userForm">
                    <!-- Existing form fields remain the same -->
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="idNumber">ID Number</label>
                        <input type="text" id="idNumber" name="id_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Admin">Admin</option>
                            <option value="Budget">Budget</option>
                            <option value="Accounting">Accounting</option>
                            <option value="Supply">Supply</option>
                            <option value="BACS">BACS</option>
                            <option value="Cashier">Cashier</option>
                            <option value="Registrar">Registrar</option>
                            <option value="Biology">Biology</option>
                            <option value="Chemistry">Chemistry</option>
                            <option value="Computer_Science">Computer Science</option>
                            <option value="Information_Technology">Information Technology</option>
                            <option value="Physics">Physics</option>
                            <option value="Meteorology">Meteorology</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Computer_Laboratory">Computer Laboratory</option>
                            <option value="NatSci_Lab">NatSci Lab</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Add User</button>
                        <button type="reset" class="btn-reset">Reset</button>
                        <button type="button" onclick="window.location.href='{{ route('home') }}'" class="btn-home">Home</button>
                    </div>
                </form>
                <div id="message" class="message"></div>
            </section>
            
            <section class="data-section">
                <h2>User Database</h2>
                <div class="filters">
                    <select id="departmentFilter">
                        <option value="all">All Departments</option>
                        <option value="Admin">Admin</option>
                        <option value="Budget">Budget</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Supply">Supply</option>
                        <option value="BACS">BACS</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Registrar">Registrar</option>
                        <option value="Biology">Biology</option>
                        <option value="Chemistry">Chemistry</option>
                        <option value="Computer_Science">Computer Science</option>
                        <option value="Information_Technology">Information Technology</option>
                        <option value="Physics">Physics</option>
                        <option value="Meteorology">Meteorology</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Computer_Laboratory">Computer Laboratory</option>
                        <option value="NatSci_Lab">NatSci Lab</option>
                        <option value="Others">Others</option>
                    </select>
                    <button id="clearFilters" class="btn-filter">Clear Filters</button>
                    <span id="filterStatus" class="filter-status"></span>
                </div>
                <div class="table-container">
                    <table id="userTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>ID Number</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <!-- Users will be loaded via JavaScript -->
                            <!-- Removed PHP code referencing undefined $user variable -->
                        </tbody>
                    </table>
                    
                    <div class="pagination">
                        <button id="prevPage" disabled>Previous</button>
                        <span id="pageInfo">Page 1 of 1</span>
                        <button id="nextPage" disabled>Next</button>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Add a modal for editing user accounts -->
    <div class="modal" id="editUserModal" style="display: none;">
        <div class="modal-content">
            <h2>Edit User</h2>
            <form id="editUserForm">
                <div class="form-group">
                    <label for="editName">Name</label>
                    <input type="text" id="editName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editIdNumber">ID Number</label>
                    <input type="text" id="editIdNumber" name="id_number" required>
                </div>
                <div class="form-group">
                    <label for="editDepartment">Department</label>
                    <select id="editDepartment" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Admin">Admin</option>
                        <option value="Budget">Budget</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Supply">Supply</option>
                        <option value="BACS">BACS</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Registrar">Registrar</option>
                        <option value="Biology">Biology</option>
                        <option value="Chemistry">Chemistry</option>
                        <option value="Computer_Science">Computer Science</option>
                        <option value="Information_Technology">Information Technology</option>
                        <option value="Physics">Physics</option>
                        <option value="Meteorology">Meteorology</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Computer_Laboratory">Computer Laboratory</option>
                        <option value="NatSci_Lab">NatSci Lab</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editPassword">Password</label>
                    <input type="password" id="editPassword" name="password">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <button type="button" class="btn-reset" id="closeEditModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/admin_controls_script.js') }}"></script>
</body>
</html>