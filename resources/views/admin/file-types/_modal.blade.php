<!-- File Types Management Modal -->
<div class="file-types-modal modal fade" id="fileTypesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Types Management</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="file-types-container">
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="file-types-card card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div></div>
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
                                                                    <input type="text" class="form-control" name="file_types[{{ $fileType->id }}][name]" value="{{ $fileType->name }}" required>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="view-mode">
                                                                    <button type="button" class="btn btn-sm btn-primary edit-btn">
                                                                        <i class="fas fa-edit"></i> Edit
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $fileType->id }}">
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
                <!-- Add New File Type Modal (nested) -->
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
            </div>
        </div>
    </div>
</div>
