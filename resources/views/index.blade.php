<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCS Document Upload System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
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
                    <h6 class="sidebar-heading px-2 mb-2">Actions</h6>
                    <div class="nav-items">
                        <a href="#" class="nav-link active">
                            <i class="bi bi-upload me-2"></i>
                            Upload Documents
                        </a>
                    </div>
                </div>
            </nav>
            <div class="sidebar-footer">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar">AD</div>
                    <div>
                        <div class="fw-medium">Admin User</div>
                        <div class="text-muted small">admin@bucs.edu</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="d-flex flex-column flex-grow-1 main-content">
            <!-- Header -->
            <header class="header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-icon d-md-none me-2" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="position-relative search-container">
                            <i class="bi bi-search position-absolute search-icon"></i>
                            <input type="search" class="form-control search-input" placeholder="Search...">
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-icon" id="themeToggle">
                            <i class="bi bi-sun-fill" id="lightIcon"></i>
                            <i class="bi bi-moon-fill d-none" id="darkIcon"></i>
                        </button>
                        <button class="btn btn-icon position-relative">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger rounded-circle">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        </button>
                        <button class="btn btn-icon">
                            <i class="bi bi-gear"></i>
                        </button>
                        <div class="avatar">AD</div>
                    </div>
                </div>
            </header>

            <!-- Main -->
            <main class="content-area p-4">
                <h1 class="mb-4 fw-bold">Upload A Document</h1>
                <form id="communicationForm">
                    <input type="hidden" id="submitFormUrl" value="{{ route('submit.form') }}">
                    <div class="row g-4">
                        <!-- Department Selection -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="fs-5 fw-medium"><i class="bi bi-building me-2"></i>Department</h3>
                                </div>
                                <div class="card-body">
                                    <label class="form-label">Select Department(s):</label>
                                    <div id="departmentSection" class="department-checkboxes">
                                        <!-- Departments will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User and Attention Fields -->
                        <div class="col-md-8">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label"><i class="bi bi-person me-2"></i>To:</label>
                                        <input type="text" class="form-control" id="userTo" required>
                                        <div id="userDropdown" class="user-dropdown" style="display: none;">
                                            <select id="userSelect" class="form-control">
                                                <option value="">Select a user</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label"><i class="bi bi-bullhorn me-2"></i>Attention/Subject:</label>
                                        <input type="text" class="form-control" id="userAttention" required>
                                    </div>
                                </div>

                                <!-- File Type Section -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="fs-5 fw-medium"><i class="bi bi-file-earmark me-2"></i>File Type</h3>
                                        </div>
                                        <div class="card-body" id="fileTypeSection">
                                            <!-- File type options will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Items and Additional Actions -->
                        <div class="col-md-4">
                            <div class="row g-4">
                                <!-- Action Items Section -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="fs-5 fw-medium"><i class="bi bi-list-task me-2"></i>Action Items</h3>
                                        </div>
                                        <div class="card-body" id="actionItemsSection">
                                            <!-- Action items will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Actions Section -->
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="fs-5 fw-medium"><i class="bi bi-plus-circle me-2"></i>Additional Actions</h3>
                                        </div>
                                        <div class="card-body" id="additionalActionsSection">
                                            <!-- Additional actions will be populated by JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Notes Section -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="fs-5 fw-medium"><i class="bi bi-sticky me-2"></i>Additional Notes</h3>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" id="additionalNotes" rows="5" placeholder="Enter any additional notes or remarks..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Document Upload Section -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="fs-5 fw-medium"><i class="bi bi-upload me-2"></i>Document Upload</h3>
                                </div>
                                <div class="card-body">
                                    <div class="file-upload-container">
                                        <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                                            <i class="bi bi-cloud-upload upload-icon"></i>
                                            <p id="fileLabel">Upload a File<br><small>Drag and drop files here</small></p>
                                            <input type="file" id="fileInput" accept=".pdf, .jpg, .png" required style="display: none;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions mt-4">
                        <div class="button-group">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-2"></i>Submit Form</button>
                            <button type="button" id="historyButton" class="btn btn-outline"><i class="bi bi-clock-history me-2"></i>View History</button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Thank you for filling up, please wait for our email for further information.</p>
            <button id="modalOkButton" class="btn btn-primary"><i class="bi bi-check me-2"></i>OK</button>
        </div>
    </div>

    <!-- History Modal -->
    <div id="historyModal" class="modal">
        <div class="modal-content">
            <p>Submission History:</p>
            <ul id="historyList"></ul>
            <div class="modal-buttons">
                <button id="historyOkButton" class="btn btn-primary"><i class="bi bi-check me-2"></i>OK</button>
                <button id="clearHistoryButton" class="btn btn-outline"><i class="bi bi-trash me-2"></i>Clear</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="js/script.js"></script>
</body>
</html>