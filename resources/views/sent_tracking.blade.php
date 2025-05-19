<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCS DocuManage</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/received_docs_styles.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="css/sent_tracking_styles.css">
    <link rel="icon" type="image/png" href="{{ asset('images/bucslogo1.png') }}">
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
                        <a href="{{ route('admin_controls') }}" class="nav-link">
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
                        <a href="{{ route('sent.tracking') }}" class="nav-link active">
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
                    @if(Auth::user()->profile_picture && file_exists(public_path('storage/' . Auth::user()->profile_picture)))
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="Profile Picture" class="avatar" style="object-fit:cover; width:40px; height:40px; border-radius:50%; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    @else
                        <img src="{{ asset('images/default-avatar.png') }}" alt="Profile Picture" class="avatar" style="object-fit:cover; width:40px; height:40px; border-radius:50%; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    @endif
                    <div>
                        <div class="fw-medium">{{ Auth::user()->name }}</div>
                    </div>
                </div>
            </div>
        </aside>
        <div class="d-flex flex-column flex-grow-1 main-content">
            <!-- Header -->
            <header class="header">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-icon d-md-none me-2" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="position-relative search-container">
                            <i class="bi bi-search position-absolute search-icon"></i>
                            <input type="search" class="form-control search-input" placeholder="Search" id="searchInput">
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-icon" id="themeToggle">
                            <i class="bi bi-sun-fill" id="lightIcon"></i>
                            <i class="bi bi-moon-fill d-none" id="darkIcon"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-icon avatar-dropdown-btn" id="avatarDropdown">
                                <div class="avatar" data-user="current">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                            </button>
                            <div class="dropdown-content" id="avatarDropdownContent">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">Profile</a>
                                <a href="/settings">Settings</a>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="tracking-container">
                <h4 class="mb-4">My Sent Documents</h4>
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by recipient, subject, or type..." style="max-width: 250px;">
                    <select id="statusFilter" class="form-select" style="max-width: 150px;">
                        <option value="">All Statuses</option>
                        <option value="sent">Sent</option>
                        <option value="delivered">Delivered</option>
                        <option value="read">Read</option>
                        <option value="acknowledged">Acknowledged</option>
                    </select>
                    <select id="typeFilter" class="form-select" style="max-width: 150px;">
                        <option value="">All Types</option>
                        @foreach($documents->pluck('file_type')->unique() as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                    <input type="date" id="dateFilter" class="form-control" style="max-width: 180px;">
                </div>
                <table class="table table-hover table-bordered align-middle shadow-sm" id="sentDocsTable" style="background: #fff; border-radius: 8px; overflow: hidden;">
                    <thead class="table-light">
                        <tr>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Date Sent</th>
                            <th>Status</th>
                            <th>View Timeline</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr data-doc-id="{{ $doc->id }}" data-to="{{ strtolower($doc->to) }}" data-subject="{{ strtolower($doc->attention) }}" data-type="{{ strtolower($doc->file_type) }}" data-date="{{ $doc->created_at->format('Y-m-d') }}" data-status="{{ $doc->statuses->last() ? strtolower($doc->statuses->last()->status) : 'sent' }}">
                            <td class="fw-semibold">{{ $doc->to }}</td>
                            <td>{{ $doc->attention }}</td>
                            <td><span class="badge bg-info text-dark">{{ $doc->file_type }}</span></td>
                            <td>{{ $doc->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @php $lastStatus = $doc->statuses->last(); @endphp
                                <span class="badge bg-{{ $lastStatus ? ($lastStatus->status === 'acknowledged' ? 'success' : ($lastStatus->status === 'read' ? 'primary' : ($lastStatus->status === 'delivered' ? 'warning text-dark' : 'secondary'))) : 'secondary' }}">
                                    {{ $lastStatus ? ucfirst($lastStatus->status) : 'Sent' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm view-timeline-btn" data-doc-id="{{ $doc->id }}">View Timeline</button>
                            </td>
                            <td>
                                <button class="btn btn-outline-danger btn-sm delete-sent-btn" data-doc-id="{{ $doc->id }}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div id="timelinePanel" class="mt-4"></div>
                @if(method_exists($documents, 'links'))
                    <div class="d-flex justify-content-end mt-2">
                        {{ $documents->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/sent_tracking_script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const timelinePanel = document.getElementById('timelinePanel');
        let selectedRow = null;
        document.querySelectorAll('.view-timeline-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const docId = this.getAttribute('data-doc-id');
                fetch(`/sent-tracking/timeline/${docId}`)
                    .then(response => response.text())
                    .then(html => {
                        timelinePanel.innerHTML = html;
                        timelinePanel.scrollIntoView({ behavior: 'smooth' });
                    });
                // Highlight selected row
                if (selectedRow) selectedRow.classList.remove('table-active');
                selectedRow = this.closest('tr');
                selectedRow.classList.add('table-active');
            });
        });
        // Filtering logic
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const typeFilter = document.getElementById('typeFilter');
        const dateFilter = document.getElementById('dateFilter');
        const tableRows = document.querySelectorAll('#sentDocsTable tbody tr');
        function filterTable() {
            const search = searchInput.value.toLowerCase();
            const status = statusFilter.value;
            const type = typeFilter.value.toLowerCase();
            const date = dateFilter.value;
            tableRows.forEach(row => {
                const to = row.getAttribute('data-to');
                const subject = row.getAttribute('data-subject');
                const rowType = row.getAttribute('data-type');
                const rowDate = row.getAttribute('data-date');
                const rowStatus = row.getAttribute('data-status');
                let show = true;
                if (search && !(to.includes(search) || subject.includes(search) || rowType.includes(search))) show = false;
                if (status && rowStatus !== status) show = false;
                if (type && rowType !== type) show = false;
                if (date && rowDate !== date) show = false;
                row.style.display = show ? '' : 'none';
            });
        }
        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);
        typeFilter.addEventListener('change', filterTable);
        dateFilter.addEventListener('change', filterTable);
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
    <style>
        #sentDocsTable th, #sentDocsTable td {
            vertical-align: middle;
            text-align: center;
        }
        #sentDocsTable th {
            font-weight: 600;
            background: #f8f9fa;
        }
        #sentDocsTable tr.table-active {
            background: #e9f5ff !important;
        }
        .pagination {
            margin-bottom: 0;
        }
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
        }
        .tracking-container {
            background: #f6f8fa;
            border-radius: 10px;
            padding: 2rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
    </style>
</body>
</html>