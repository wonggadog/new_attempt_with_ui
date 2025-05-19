<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCS DocuManage</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Main App Styles -->
    <link href="css/styles.css" rel="stylesheet">
    <!-- Admin Controls specific styles -->
    <link rel="stylesheet" href="{{ asset('css/admin_controls_styles.css') }}">
    <link href="{{ asset('css/received_docs_styles.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/bucslogo1.png') }}">
    <style>
        /* Modal backdrop */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            display: none; /* Hidden by default */
            justify-content: center;
            align-items: center;
            z-index: 1000; /* Ensure it appears above other elements */
        }

        /* Modal content */
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
        }

        /* Close button */
        .btn-reset {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-reset:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
  <div class="d-flex wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header d-flex align-items-center">
        <i class="bi bi-file-text fs-4 me-2"></i>
        <span class="fw-semibold">BUCS DocuManage</span>
      </div>
      <nav class="sidebar-nav">
        <div class="px-3 py-2">
          <h6 class="sidebar-heading px-2 mb-2">Options</h6>
          <div class="nav-items">
            <a href="{{ route('dashboard') }}" class="nav-link">
              <i class="bi bi-house-door me-2"></i>
              Home
            </a>
            <a href="{{ route('admin_controls') }}" class="nav-link active">
              <i class="bi bi-shield-lock me-2"></i>
              Admin Controls
            </a>
            <a href="{{ route('home') }}" class="nav-link">
              <i class="bi bi-upload me-2"></i>
              Upload Documents
            </a>
            <a href="{{ route('received.documents') }}" class="nav-link">
              <i class="bi bi-inbox me-2"></i>
              Received Documents
            </a>
            <a href="{{ route('sent.tracking') }}" class="nav-link">
              <i class="bi bi-send me-2"></i>
              Sent Documents
            </a>
            <a href="{{ route('trash') }}" class="nav-link">
              <i class="bi bi-trash me-2"></i>
              Trash
            </a>
          </div>
        </div>
      </nav>
      <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2">
          <div class="avatar" data-user="current">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
          <div>
            <div class="fw-medium">{{ Auth::user()->name }}</div>
            <!-- <div class="text-muted small">{{ Auth::user()->email }}</div> -->
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="d-flex flex-column flex-grow-1 main-content mt-4 px-4">
      <!-- Move the existing container content here -->
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
                            <option value="Computer Science">Computer Science</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Physics">Physics</option>
                            <option value="Meteorology">Meteorology</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Computer Laboratory">Computer Laboratory</option>
                            <option value="NatSci Lab">NatSci Lab</option>
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
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Physics">Physics</option>
                        <option value="Meteorology">Meteorology</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Computer Laboratory">Computer Laboratory</option>
                        <option value="NatSci Lab">NatSci Lab</option>
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
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
      </div>
    </div>
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
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Physics">Physics</option>
                        <option value="Meteorology">Meteorology</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Computer Laboratory">Computer Laboratory</option>
                        <option value="NatSci Lab">NatSci Lab</option>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.avatar-dropdown-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const dropdown = btn.nextElementSibling;
          dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
      });
      document.addEventListener('click', function(e) {
        document.querySelectorAll('.avatar-dropdown-btn').forEach(function(btn) {
          const dropdown = btn.nextElementSibling;
          if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
          }
        });
      });
    });
    </script>

    <div class="dropdown-content" id="avatarDropdownContent">
      <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">Profile</a>
      <a href="/settings">Settings</a>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="dropdown-item">Logout</button>
      </form>
    </div>
</body>
</html>