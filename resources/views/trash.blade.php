<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash - Deleted Documents</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Main App Styles -->
    <link href="css/styles.css" rel="stylesheet">
    <!-- Trash-specific styles (optional, for trash content only) -->
    <link rel="stylesheet" href="css/trash_styles.css">
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
                        <a href="{{ route('sent.tracking') }}" class="nav-link">
                            <i class="bi bi-send me-2"></i>
                            Sent Documents
                        </a>
                        <a href="{{ route('trash') }}" class="nav-link active">
                            <i class="bi bi-trash me-2"></i>
                            Trash
                        </a>
                    </div>
                </div>
            </nav>
            <div class="sidebar-footer">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar" data-user="current"></div>
                    <div>
                        <div class="fw-medium">{{ Auth::user()->name }}</div>
                        <!-- <div class="text-muted small">{{ Auth::user()->email }}</div> -->
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="d-flex flex-column flex-grow-1 main-content mt-4 px-4">
            <header>
                <h1>
                    <svg class="trash-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Trash
                </h1>
                <div class="actions">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" id="search" placeholder="Search deleted documents">
                    </div>
                    <button id="restore-all" class="btn-ghost btn-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Restore All
                    </button>
                    <button id="empty-trash" class="btn-danger btn-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Empty Trash
                    </button>
                </div>
            </header>

            <div class="info-bar">
                <div id="item-count">7 items in trash • Will be automatically deleted after 30 days</div>
                <div class="sort-options">
                    <span>Sort by:</span>
                    <select id="sort-by">
                        <option value="date-desc">Date (newest first)</option>
                        <option value="date-asc">Date (oldest first)</option>
                        <option value="name-asc">Name (A-Z)</option>
                        <option value="name-desc">Name (Z-A)</option>
                    </select>
                </div>
            </div>

            <div id="document-list" class="document-list">
                <!-- Documents will be populated here by JavaScript -->
            </div>
        </div>
    </div>
    <script src="js/trash_script.js"></script>
</body>
</html>