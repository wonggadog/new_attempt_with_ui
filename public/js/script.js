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
    ],
    fileTypes: [
        'Memos', 'Reports', 'Financial Documents', 'Student Records'
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

    // Populate additional actions
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

    // Populate file types
    const fileTypeSection = document.getElementById('fileTypeSection');
    fileTypeSection.innerHTML = formData.fileTypes
        .map(type => createRadioButton(type, type))
        .join('');

    // Set up checkbox listeners for text field visibility
    setupCheckboxListeners();

    // Set up file upload listeners
    setupFileUpload();

    // Set up department checkbox listeners for recipient dropdown
    setupDepartmentCheckboxListeners();

    // Set up typing listener for "To:" field with debouncing
    setupRecipientSearch();
}

// Helper function to create radio buttons
function createRadioButton(id, label) {
    const sanitizedId = id.toLowerCase().replace(/[^a-z0-9]/g, '');
    return `
        <div class="form-check">
            <input class="form-check-input" type="radio" name="fileType" id="${sanitizedId}" value="${label}">
            <label class="form-check-label" for="${sanitizedId}">${label}</label>
        </div>
    `;
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
        departments: Array.from(document.querySelectorAll('#departmentSection input[type="checkbox"]:checked'))
            .map(box => box.nextElementSibling.textContent.trim()),
        actionItems: collectCheckedItems('actionItemsSection'),
        additionalActions: collectCheckedItems('additionalActionsSection'),
        fileType: document.querySelector('input[name="fileType"]:checked')?.value || '',
        files: document.getElementById('fileInput').files,
        additionalNotes: document.getElementById('additionalNotes').value, // New field
    };

    // Log the collected data
    console.log('Form data:', formData);

    // Convert files to FormData for upload
    const formDataToSend = new FormData();
    formDataToSend.append('to', formData.to);
    formDataToSend.append('attention', formData.attention);

    // Append departments as array items
    formData.departments.forEach((dept, index) => {
        formDataToSend.append(`departments[${index}]`, dept);
    });

    // Append action items as array items
    formData.actionItems.forEach((item, index) => {
        formDataToSend.append(`action_items[${index}]`, item);
    });

    // Append additional actions as array items
    formData.additionalActions.forEach((action, index) => {
        formDataToSend.append(`additional_actions[${index}]`, action);
    });

    formDataToSend.append('file_type', formData.fileType); // Append selected file type
    formDataToSend.append('additional_notes', formData.additionalNotes); // Append additional notes

    // Append files
    for (let i = 0; i < formData.files.length; i++) {
        formDataToSend.append('files[]', formData.files[i]);
    }

    // Get the route URL from the hidden input
    const submitFormUrl = document.getElementById('submitFormUrl').value;

    // Send data to Laravel backend
    fetch(submitFormUrl, {
        method: 'POST',
        body: formDataToSend,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || err.message || 'An error occurred while submitting the form.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('confirmationModal').style.display = "flex";
            saveToHistory(formData);
        } else {
            alert('Error submitting form: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error submitting form. Please try again.');
    });
}

// Save submission to history
function saveToHistory(formData) {
    let submissionHistory = JSON.parse(localStorage.getItem("submissionHistory")) || [];
    
    const submission = {
        timestamp: new Date().toLocaleString(),
        to: formData.to,
        attention: formData.attention,
        departments: formData.departments,
        actionItems: formData.actionItems,
        additionalActions: formData.additionalActions,
        fileType: formData.fileType,
        files: Array.from(formData.files).map(f => f.name),
    };

    submissionHistory.unshift(submission);
    localStorage.setItem("submissionHistory", JSON.stringify(submissionHistory));
}

// Function to fetch recipients based on selected departments and search term
function fetchRecipients(departments, searchTerm = '') {
    console.log('Fetching recipients for departments:', departments, 'and search term:', searchTerm); // Debugging

    fetch('/fetch-recipients', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ 
            departments: departments,
            search: searchTerm, // Include the search term in the request
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Recipients Data:', data); // Debugging
        const dropdown = document.getElementById('recipientSelect');
        dropdown.innerHTML = '<option value="">Select a recipient</option>'; // Reset the dropdown options
        
        // Populate the dropdown with recipients
        data.forEach(recipient => {
            const option = document.createElement('option');
            option.value = recipient.name;
            option.textContent = recipient.name;
            dropdown.appendChild(option);
        });

        // Show the dropdown
        document.getElementById('recipientDropdown').style.display = 'block';

        // Automatically open the dropdown if there's a search term
        if (searchTerm.trim() !== '') {
            dropdown.size = data.length + 1; // Show all options (including the default)
        } else {
            dropdown.size = 1; // Collapse the dropdown
        }
    })
    .catch(error => {
        console.error('Error fetching recipients:', error);
    });
}

// Debounce function to limit how often fetchRecipients is called
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Set up typing listener for "To:" field with debouncing
function setupRecipientSearch() {
    const recipientToField = document.getElementById('recipientTo');
    const debouncedFetchRecipients = debounce(function() {
        const searchTerm = recipientToField.value.trim(); // Get the typed text
        const selectedDepartments = Array.from(document.querySelectorAll('#departmentSection input[type="checkbox"]:checked'))
            .map(box => box.nextElementSibling.textContent.trim());

        if (selectedDepartments.length > 0) {
            fetchRecipients(selectedDepartments, searchTerm); // Fetch recipients with the search term
        } else {
            document.getElementById('recipientDropdown').style.display = 'none';
        }
    }, 300); // 300ms delay

    recipientToField.addEventListener('input', debouncedFetchRecipients);
}

// Event listener for department checkboxes
function setupDepartmentCheckboxListeners() {
    document.querySelectorAll('#departmentSection input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selectedDepartments = Array.from(document.querySelectorAll('#departmentSection input[type="checkbox"]:checked'))
                .map(box => box.nextElementSibling.textContent.trim());

            console.log('Selected Departments:', selectedDepartments); // Debugging

            if (selectedDepartments.length > 0) {
                const searchTerm = document.getElementById('recipientTo').value.trim(); // Get the current search term
                fetchRecipients(selectedDepartments, searchTerm); // Fetch recipients with the search term
            } else {
                document.getElementById('recipientDropdown').style.display = 'none';
            }
        });
    });
}

// Event listener for recipient dropdown
document.getElementById('recipientSelect').addEventListener('change', function() {
    const recipientToField = document.getElementById('recipientTo');
    recipientToField.value = this.value; // Set the selected recipient in the "To:" field

    // Hide the dropdown after selection
    document.getElementById('recipientDropdown').style.display = 'none';
});

// Initialize everything when DOM is loaded
document.addEventListener("DOMContentLoaded", function() {
    initializeForm();

    // Hide the recipient dropdown initially
    document.getElementById('recipientDropdown').style.display = 'none';

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

    historyButton.addEventListener("click", function () {
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
                    Departments: ${entry.departments.join(", ")}<br>
                    Action Items: ${entry.actionItems.join(", ")}<br>
                    Additional Actions: ${entry.additionalActions.join(", ")}<br>
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

// Function to update the date and time
//function updateDateTime() {
//    const now = new Date();
//    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
//    const formattedDateTime = now.toLocaleDateString('en-US', options);
//    document.getElementById('currentDateTime').textContent = formattedDateTime;
//}

// Update the date and time every second
//setInterval(updateDateTime, 1000);

// Initialize the date and time on page load
//updateDateTime();