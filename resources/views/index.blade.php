<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCS Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div id="currentDateTime"></div>
    </div>

    <!-- Left NavBar -->
    <nav class="left-navbar">
        <ul>
            <li><a href="#" class="nav-link"><i class="fas fa-upload"></i> Document Upload</a></li>
        </ul>
    </nav>

    <div class="main-container">
        <div class="container py-4">
            <div class="welcome-section mb-4 text-center">
                <h1>BICOL UNIVERSITY COLLEGE OF SCIENCE</h1>
                <p class="welcome-text">Document Sending System</p>
            </div>

            <div class="content-wrapper">
                <!-- Communication Form Section -->
                <div class="content-card communication-section">
                    <form id="communicationForm">
                        <input type="hidden" id="submitFormUrl" value="{{ route('submit.form') }}">
                        <div class="row g-4">
                            <!-- Department Selection -->
                            <div class="col-md-4">
                                <div class="bu-card">
                                    <div class="bu-card-header">
                                        <h3><i class="fas fa-building"></i> Department</h3>
                                    </div>
                                    <div class="bu-card-body">
                                        <div class="form-group">
                                            <label class="form-label">Select Department(s):</label>
                                            <div id="departmentSection" class="department-checkboxes">
                                                <!-- Departments will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recipient and Attention Fields -->
                            <div class="col-md-8">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label"><i class="fas fa-user"></i> To:</label>
                                            <input type="text" class="form-control" id="recipientTo" required>
                                            <!-- Recipient Dropdown -->
                                            <div id="recipientDropdown" class="recipient-dropdown">
                                                <select id="recipientSelect" class="form-control">
                                                    <option value="">Select a recipient</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label"><i class="fas fa-bullhorn"></i> Attention:</label>
                                            <input type="text" class="form-control" id="recipientAttention" required>
                                        </div>
                                    </div>

                                    <!-- File Type Section -->
                                    <div class="col-12">
                                        <div class="bu-card">
                                            <div class="bu-card-header">
                                                <h3><i class="fas fa-file"></i> File Type</h3>
                                            </div>
                                            <div class="bu-card-body" id="fileTypeSection">
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
                                        <div class="bu-card">
                                            <div class="bu-card-header">
                                                <h3><i class="fas fa-tasks"></i> Action Items</h3>
                                            </div>
                                            <div class="bu-card-body" id="actionItemsSection">
                                                <!-- Action items will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Actions Section -->
                                    <div class="col-12">
                                        <div class="bu-card">
                                            <div class="bu-card-header">
                                                <h3><i class="fas fa-plus-circle"></i> Additional Actions</h3>
                                            </div>
                                            <div class="bu-card-body" id="additionalActionsSection">
                                                <!-- Additional actions will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Notes Section -->
                            <div class="col-md-8">
                                <div class="bu-card">
                                    <div class="bu-card-header">
                                        <h3><i class="fas fa-sticky-note"></i> Additional Notes</h3>
                                    </div>
                                    <div class="bu-card-body">
                                        <div class="form-group">
                                            <textarea class="form-control" id="additionalNotes" rows="5" placeholder="Enter any additional notes or remarks..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Upload Section -->
                            <div class="col-12">
                                <div class="bu-card">
                                    <div class="bu-card-header">
                                        <h3><i class="fas fa-upload"></i> Document Upload</h3>
                                    </div>
                                    <div class="bu-card-body">
                                        <div class="file-upload-container">
                                            <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                                <p id="fileLabel">Upload a File<br><small>Drag and drop files here</small></p>
                                                <input type="file" id="fileInput" accept=".pdf, .jpg, .png" required style="display: none;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <div class="button-group">
                    <button type="submit" class="bu-button" form="communicationForm"><i class="fas fa-paper-plane"></i> Submit Form</button>
                    <button type="button" id="historyButton" class="bu-button secondary"><i class="fas fa-history"></i> View History</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Thank you for filling up, please wait for our email for further information.</p>
            <button id="modalOkButton" class="bu-button"><i class="fas fa-check"></i> OK</button>
        </div>
    </div>
    
    <!-- History Modal -->
    <div id="historyModal" class="modal">
        <div class="modal-content">
            <p>Submission History:</p>
            <ul id="historyList"></ul>
            <div class="modal-buttons">
                <button id="historyOkButton" class="bu-button primary"><i class="fas fa-check"></i> OK</button>
                <button id="clearHistoryButton" class="bu-button secondary"><i class="fas fa-trash"></i> Clear</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>