console.log("Script is running");

// Form data configuration
const formData = {
    departments: [
        'Admin', 'Budget', 'Accounting', 'Supply', 'BACS', 'Cashier', 
        'Registrar', 'Biology', 'Chemistry', 'CS/IT', 'Phys/Met', 
        'Math', 'Comp Lab', 'NatSci Lab', 'Others'
    ],
    actionItems: [
        'Comments/recommendations', 'Action', 
        'Information/notation and return/dissemination', 'Compliance',
        'Guidance', 'Study', 'Investigation and report', 'File/reference'
    ],
    additionalActions: [
        'Please endorse to', 'Please confer with', 'Please coordinate with',
        'Please prepare an endorsement/answer/draft', 
        'Please communicate directly with the party',
        'Please review/revise', 'Please complete the attached forms'
    ]
};

// Helper function to create checkboxes
function createCheckbox(id, label) {
    const sanitizedId = id.toLowerCase().replace(/[^a-z0-9]/g, '');
    return `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="${sanitizedId}">
            <label class="form-check-label" for="${sanitizedId}">${label}</label>
        </div>
    `;
}

// Helper function to create checkboxes with dynamic text field for names
function createCheckboxWithTextField(id, label) {
    const sanitizedId = id.toLowerCase().replace(/[^a-z0-9]/g, '');
    return `
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="${sanitizedId}">
            <label class="form-check-label" for="${sanitizedId}">${label}</label>
            <div id="${sanitizedId}TextField" class="name-input-field" style="display:none;">
                <input type="text" class="form-control" placeholder="Enter name">
            </div>
        </div>
    `;
}

// Initialize form
function initializeForm() {
    // Populate departments
    const departmentSection = document.getElementById('departmentSection');
    departmentSection.innerHTML = formData.departments
        .map(dept => createCheckbox(dept, dept))
        .join('');

    // Populate action items
    const actionItemsSection = document.getElementById('actionItemsSection');
    actionItemsSection.innerHTML = formData.actionItems
        .map(item => createCheckbox(item, item))
        .join('');

    // Populate additional actions with checkboxes and name input fields where applicable
    const additionalActionsSection = document.getElementById('additionalActionsSection');
    additionalActionsSection.innerHTML = formData.additionalActions
        .map(action => {
            if (["Please endorse to", "Please confer with", "Please coordinate with"].includes(action)) {
                return createCheckboxWithTextField(action, action);
            } else {
                return createCheckbox(action, action);
            }
        })
        .join('');

    // Set up checkbox listeners for text field visibility
    setupCheckboxListeners();

    // Set up file upload listeners
    setupFileUpload();
}

// Set up checkbox listeners for additional actions that trigger name input field
function setupCheckboxListeners() {
    const actionsWithTextFields = [
        "Please endorse to",
        "Please confer with",
        "Please coordinate with"
    ];

    actionsWithTextFields.forEach(action => {
        const checkboxId = action.toLowerCase().replace(/[^a-z0-9]/g, '');
        const checkbox = document.getElementById(checkboxId);
        const textField = document.getElementById(`${checkboxId}TextField`);

        checkbox.addEventListener('change', function() {
            if (checkbox.checked) {
                textField.style.display = 'block';
            } else {
                textField.style.display = 'none';
            }
        });
    });
}

// Helper function to collect checked items
function collectCheckedItems(sectionId) {
    const section = document.getElementById(sectionId);
    const checkedBoxes = section.querySelectorAll('input[type="checkbox"]:checked');
    return Array.from(checkedBoxes).map(box => box.nextElementSibling.textContent);
}

// Set up file upload functionality
function setupFileUpload() {
    const fileInput = document.getElementById("fileInput");
    const fileLabel = document.getElementById("fileLabel");
    const fileUpload = document.querySelector(".file-upload");

    // Drag and drop handlers
    fileUpload.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileUpload.classList.add('drag-over');
    });

    fileUpload.addEventListener('dragleave', () => {
        fileUpload.classList.remove('drag-over');
    });

    fileUpload.addEventListener('drop', (e) => {
        e.preventDefault();
        fileUpload.classList.remove('drag-over');
        fileInput.files = e.dataTransfer.files;
        updateFileLabel(fileInput.files);
    });

    // File input change handler
    fileInput.addEventListener("change", function() {
        updateFileLabel(this.files);
    });
}

// Update file label
function updateFileLabel(files) {
    const fileLabel = document.getElementById("fileLabel");
    if (files.length > 0) {
        fileLabel.innerHTML = Array.from(files)
            .map(file => file.name)
            .join("<br>");
    } else {
        fileLabel.innerHTML = "Upload a File<br><small>Drag and drop files here</small>";
    }
}

// Form submission handler
function handleFormSubmit(event) {
    event.preventDefault();
    
    const formData = {
        to: document.getElementById('recipientTo').value,
        attention: document.getElementById('recipientAttention').value,
        departments: collectCheckedItems('departmentSection'),
        actionItems: collectCheckedItems('actionItemsSection'),
        additionalActions: collectCheckedItems('additionalActionsSection'),
        fileType: document.getElementById('fileInput').value,
        files: document.getElementById('fileInput').files
    };

    // Save to submission history
    saveToHistory(formData);

    // Show confirmation
    document.getElementById('confirmationModal').style.display = "flex";
}

// Save submission to history
function saveToHistory(formData) {
    let submissionHistory = JSON.parse(localStorage.getItem("submissionHistory")) || [];
    
    const submission = {
        timestamp: new Date().toLocaleString(),
        to: formData.to,
        attention: formData.attention,
        fileType: formData.fileType,
        files: Array.from(formData.files).map(f => f.name)
    };

    submissionHistory.unshift(submission);
    localStorage.setItem("submissionHistory", JSON.stringify(submissionHistory));
}

// Initialize everything when DOM is loaded
document.addEventListener("DOMContentLoaded", function() {
    initializeForm();

    // Form submission
    document.getElementById('communicationForm').addEventListener('submit', handleFormSubmit);

    // Modal handlers
    const confirmationModal = document.getElementById("confirmationModal");
    const historyModal = document.getElementById("historyModal");
    const modalOkButton = document.getElementById("modalOkButton");
    const historyButton = document.getElementById("historyButton");
    const historyOkButton = document.getElementById("historyOkButton");
    const clearHistoryButton = document.getElementById("clearHistoryButton");

    modalOkButton.addEventListener("click", function() {
        confirmationModal.style.display = "none";
        document.getElementById('communicationForm').reset();
        document.getElementById('fileLabel').innerHTML = "Upload a File<br><small>Drag and drop files here</small>";
    });

    historyButton.addEventListener("click", function() {
        const submissionHistory = JSON.parse(localStorage.getItem("submissionHistory")) || [];
        const historyList = document.getElementById("historyList");
        historyList.innerHTML = "";

        if (submissionHistory.length > 0) {
            submissionHistory.forEach(entry => {
                const listItem = document.createElement("li");
                listItem.innerHTML = ` 
                    <strong>${entry.timestamp}</strong><br>
                    To: ${entry.to}<br>
                    Attention: ${entry.attention}<br>
                    Files: ${entry.files.join(", ")}
                `;
                historyList.appendChild(listItem);
            });
        } else {
            historyList.innerHTML = "<li>No submissions yet</li>";
        }

        historyModal.style.display = "flex";
    });

    historyOkButton.addEventListener("click", function() {
        historyModal.style.display = "none";
    });

    clearHistoryButton.addEventListener("click", function() {
        localStorage.removeItem("submissionHistory");
        document.getElementById("historyList").innerHTML = "<li>No submissions yet</li>";
    });
});
