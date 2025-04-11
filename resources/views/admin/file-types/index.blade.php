@extends('layouts.app')

@section('content')
<div class="file-types-container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="file-types-card card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>File Types Management</h3>
                    <div>
                        <button type="button" class="btn btn-primary" id="addFileTypeBtn">
                            <i class="fas fa-plus"></i> Add New File Type
                        </button>
                        <button type="button" class="btn btn-success" id="saveChangesBtn" style="display: none;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="file-types-alert alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="file-types-alert alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <form id="fileTypesForm" action="{{ route('admin.file-types.bulk-update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <table class="file-types-table table">
                                <thead>
                                    <tr>
                                        <th style="width: 80%">Name</th>
                                        <th style="width: 20%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fileTypesTableBody">
                                    @forelse($fileTypes as $fileType)
                                        <tr data-id="{{ $fileType->id }}">
                                            <td>
                                                <div class="view-mode">
                                                    <span class="file-type-name">{{ $fileType->name }}</span>
                                                </div>
                                                <div class="edit-mode" style="display: none;">
                                                    <input type="text" class="form-control" name="file_types[{{ $fileType->id }}][name]" 
                                                           value="{{ $fileType->name }}" required>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="view-mode">
                                                    <button type="button" class="btn btn-sm btn-primary edit-btn">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                            data-id="{{ $fileType->id }}">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                                <div class="edit-mode" style="display: none;">
                                                    <button type="button" class="btn btn-sm btn-success save-btn">
                                                        <i class="fas fa-check"></i> Save
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary cancel-btn">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No file types found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add New File Type Modal -->
<div class="file-types-modal modal fade" id="addFileTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New File Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addFileTypeForm" action="{{ route('admin.file-types.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="newName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="{{ asset('css/file-types.css') }}" rel="stylesheet">
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('fileTypesTableBody');
    const addFileTypeBtn = document.getElementById('addFileTypeBtn');
    const saveChangesBtn = document.getElementById('saveChangesBtn');
    const fileTypesForm = document.getElementById('fileTypesForm');
    let isEditing = false;

    // Add new file type
    const addFileTypeForm = document.getElementById('addFileTypeForm');
    addFileTypeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showAlert('danger', data.message || 'Error creating file type.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Error creating file type. Please try again.');
        });
    });

    // Add new file type button
    addFileTypeBtn.addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('addFileTypeModal'));
        modal.show();
    });

    // Edit button click
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.edit-btn')) {
            const row = e.target.closest('tr');
            toggleEditMode(row, true);
        }
    });

    // Cancel button click
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.cancel-btn')) {
            const row = e.target.closest('tr');
            toggleEditMode(row, false);
        }
    });

    // Save Changes button click
    saveChangesBtn.addEventListener('click', function() {
        const editingRows = document.querySelectorAll('tr .edit-mode[style*="block"]');
        if (editingRows.length === 0) return;

        const updates = [];
        editingRows.forEach(editMode => {
            const row = editMode.closest('tr');
            const id = row.dataset.id;
            const name = row.querySelector('input[name^="file_types"]').value;
            updates.push({ id, name });
        });

        // Send all updates
        Promise.all(updates.map(update => 
            fetch(`/admin/file-types/${update.id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: update.name })
            }).then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
        ))
        .then(results => {
            results.forEach((data, index) => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${updates[index].id}"]`);
                    row.querySelector('.file-type-name').textContent = data.name;
                    toggleEditMode(row, false);
                }
            });
            showAlert('success', 'All changes saved successfully.');
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', error.message || 'Error saving changes. Please try again.');
        });
    });

    // Individual save button click
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.save-btn')) {
            const row = e.target.closest('tr');
            const id = row.dataset.id;
            const name = row.querySelector('input[name^="file_types"]').value;
            
            fetch(`/admin/file-types/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    row.querySelector('.file-type-name').textContent = data.name;
                    toggleEditMode(row, false);
                    showAlert('success', 'File type updated successfully.');
                } else {
                    showAlert('danger', data.message || 'Error updating file type.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', error.message || 'Error updating file type. Please try again.');
            });
        }
    });

    // Delete button click
    tableBody.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            if (confirm('Are you sure you want to delete this file type?')) {
                const row = e.target.closest('tr');
                const id = row.dataset.id;
                
                fetch(`/admin/file-types/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        row.remove();
                        showAlert('success', 'File type deleted successfully.');
                    } else {
                        showAlert('danger', data.message || 'Error deleting file type.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('danger', error.message || 'Error deleting file type. Please try again.');
                });
            }
        }
    });

    function toggleEditMode(row, edit) {
        const viewMode = row.querySelector('.view-mode');
        const editMode = row.querySelector('.edit-mode');
        
        if (edit) {
            viewMode.style.display = 'none';
            editMode.style.display = 'block';
            isEditing = true;
            saveChangesBtn.style.display = 'inline-block';
            
            // Focus on the input field
            const input = row.querySelector('input[name^="file_types"]');
            input.focus();
            input.select();
        } else {
            viewMode.style.display = 'block';
            editMode.style.display = 'none';
            isEditing = false;
            saveChangesBtn.style.display = 'none';
        }
    }

    function showAlert(type, message) {
        // Remove any existing alerts first
        const existingAlerts = document.querySelectorAll('.file-types-alert');
        existingAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `file-types-alert alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert the alert after the card-header
        const cardHeader = document.querySelector('.card-header');
        cardHeader.insertAdjacentElement('afterend', alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
@endsection

