<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCS Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <div class="container py-4">
            <div class="welcome-section mb-4">
                <h1>BICOL UNIVERSITY COLLEGE OF SCIENCE</h1>
                <p class="welcome-text">Communication Form Management System</p>
            </div>

            <div class="content-wrapper">
                <!-- Communication Form Section -->
                <div class="content-card communication-section">
                    <form id="communicationForm">
                        <div class="row g-4">
                            <!-- Main recipient fields -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">To:</label>
                                    <input type="text" class="form-control" id="recipientTo" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Attention:</label>
                                    <input type="text" class="form-control" id="recipientAttention" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Department Section -->
                                <div class="bu-card">
                                    <div class="bu-card-header">
                                        <h3>Department</h3>
                                    </div>
                                    <div class="bu-card-body" id="departmentSection">
                                        <!-- Departments will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="row g-4">
                                    <!-- Action Items Section -->
                                    <div class="col-md-6">
                                        <div class="bu-card">
                                            <div class="bu-card-header">
                                                <h3>Action Items</h3>
                                            </div>
                                            <div class="bu-card-body" id="actionItemsSection">
                                                <!-- Action items will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Actions Section -->
                                    <div class="col-md-6">
                                        <div class="bu-card">
                                            <div class="bu-card-header">
                                                <h3>Additional Actions</h3>
                                            </div>
                                            <div class="bu-card-body" id="additionalActionsSection">
                                                <!-- Additional actions will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Document Upload Section -->
                            <div class="bu-card">
                                <div class="bu-card-header">
                                    <h3>Document Upload</h3>
                                </div>
                                <div class="bu-card-body">
                                    <div class="file-upload-container">
                                        <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                                            <img src="images/upload.png" alt="Upload" class="upload">
                                            <p id="fileLabel">Upload a File<br><small>Drag and drop files here</small></p>
                                            <input type="file" id="fileInput" accept=".pdf, .jpg, .png" required style="display: none;">
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
                    <button type="submit" class="bu-button" form="communicationForm">Submit Form</button>
                    <button type="button" id="historyButton" class="bu-button secondary">View History</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <p>Thank you for filling up, please wait for our email for further information.</p>
            <button id="modalOkButton" class="bu-button">OK</button>
        </div>
    </div>
    
    <!-- History Modal -->
    <div id="historyModal" class="modal">
        <div class="modal-content">
            <p>Submission History:</p>
            <ul id="historyList"></ul>
            <div class="modal-buttons">
                <button id="historyOkButton" class="bu-button secondary">OK</button>
                <button id="clearHistoryButton" class="bu-button secondary">Clear</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>