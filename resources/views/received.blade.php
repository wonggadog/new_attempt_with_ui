<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BUCS DocuManage</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link href="{{ asset('css/received_docs_styles.css') }}" rel="stylesheet">
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
            <a href="{{ route('received.documents') }}" class="nav-link active">
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
    <div class="d-flex flex-column flex-grow-1 main-content">
      <!-- Header -->
      <header class="header">
        <div class="d-flex align-items-center justify-content-between w-100">
          <!-- Left side with toggle and search -->
          <div class="d-flex align-items-center">
            <button class="btn btn-icon d-md-none me-2" id="sidebarToggle">
              <i class="bi bi-list"></i>
            </button>
            <div class="position-relative search-container">
              <i class="bi bi-search position-absolute search-icon"></i>
              <input type="search" class="form-control search-input" placeholder="Search" id="searchInput">
            </div>
          </div>
          
          <!-- Right side with icons and avatar -->
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

      <!-- Main -->
      <main class="content-area p-4">
        <div id="documentsList">
          <h1 class="mb-4 fw-bold">Received Documents</h1>
          
          <!-- Tabs -->
          <ul class="nav nav-tabs custom-tabs mb-4" id="documentTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-docs" type="button" role="tab" aria-controls="all-docs" aria-selected="true">All Documents</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread-docs" type="button" role="tab" aria-controls="unread-docs" aria-selected="false">Unread</button>
            </li>
          </ul>
          
          <!-- Tab Content -->
          <div class="tab-content" id="documentTabsContent">
            <!-- All Documents Tab -->
            <div class="tab-pane fade show active" id="all-docs" role="tabpanel" aria-labelledby="all-tab">
              <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="allDocumentsContainer">
                <!-- Document cards will be inserted here by JavaScript -->
              </div>
            </div>
            
            <!-- Unread Documents Tab -->
            <div class="tab-pane fade" id="unread-docs" role="tabpanel" aria-labelledby="unread-tab">
              <div class="d-flex align-items-center justify-content-center empty-state">
                <p class="text-muted">No unread documents</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Document Detail View (Hidden by default) -->
        <div id="documentDetail" class="d-none">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <button class="btn btn-outline back-btn" id="backToDocuments">
              <i class="bi bi-arrow-left me-2"></i>
              Back to Documents
            </button>
            <div class="d-flex gap-2">
              <button class="btn btn-outline" id="deleteDetailBtn">
                <i class="bi bi-trash me-2"></i>
                Delete
              </button>
              <button class="btn btn-primary">
                <i class="bi bi-download me-2"></i>
                Download
              </button>
            </div>
          </div>
          
          <div class="card detail-card">
            <div class="card-header">
              <div class="d-flex justify-content-between">
                <div class="d-flex gap-3">
                  <div class="avatar avatar-lg" id="detailSenderAvatar" data-name=""></div>
                  <div>
                    <h2 class="fs-3 fw-bold" id="detailSubject"></h2>
                    <div class="d-flex align-items-center mt-1">
                      <i class="bi bi-person text-muted me-1"></i>
                      <span class="small text-muted me-3">
                        From: <span class="fw-medium" id="detailSender"></span>
                      </span>
                      <i class="bi bi-calendar text-muted me-1"></i>
                      <span class="small text-muted" id="detailDate"></span>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="badge bg-danger d-none" id="detailUrgentBadge">Urgent</span>
                  <button class="btn btn-icon" id="detailStarButton">
                    <i class="bi bi-star"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-4">
                    <h3 class="fs-5 fw-medium mb-3">Required Actions</h3>
                    <div class="d-flex flex-column gap-2" id="detailActions">
                      <!-- Actions will be inserted here by JavaScript -->
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <h3 class="fs-5 fw-medium mb-3">Notes & Remarks</h3>
                    <div class="p-3 bg-light rounded notes-box" id="detailNotes">
                      <!-- Notes will be inserted here by JavaScript -->
                    </div>
                  </div>
                  
                  <div>
                    <h3 class="fs-5 fw-medium mb-3">Add Comment</h3>
                    <textarea class="form-control custom-textarea mb-2" rows="4" placeholder="Type your comment here..." id="commentBox"></textarea>
                    <div class="d-flex justify-content-end">
                      <button class="btn btn-primary" id="sendCommentBtn">
                        <i class="bi bi-chat-square-text me-2"></i>
                        Send Comment
                      </button>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="border rounded p-4 text-center mb-4 file-box">
                    <div id="detailFileIcon" class="mb-3 mx-auto" style="font-size: 3rem;"></div>
                    <div class="mb-2">
                        <span id="detailFileName" class="fw-semibold"></span>
                    </div>
                    <p class="small text-muted" id="detailFileType"></p>
                    <button class="btn btn-primary w-100 mb-2">
                      <i class="bi bi-download me-2"></i>
                      Download File
                    </button>
                  </div>
                  
                  <div class="border rounded p-3 info-box">
                    <h3 class="fs-6 fw-medium mb-3">Document Information</h3>
                    <div class="small">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">File Type:</span>
                        <span id="detailInfoFileType"></span>
                      </div>
                      <hr class="my-2">
                      <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Date Received:</span>
                        <span id="detailInfoDate"></span>
                      </div>
                      <hr class="my-2">
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Status:</span>
                        <span class="badge status-badge">Pending Action</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <button class="btn btn-outline btn-mark-complete" data-doc-id="" id="markAsCompleteDetail">Mark as Complete</button>
                <div>
                  <button class="btn btn-primary" id="takeActionButton">Take Action</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Avatar Helper -->
  <script src="{{ asset('js/avatar-helper.js') }}"></script>
  
  <!-- Pass PHP data to JavaScript -->
  <script>
    const documents = @json($documents);
    const unreadDocuments = @json($unreadDocuments ?? []);
    const currentUserName = "{{ Auth::user()->name }}";
  </script>

  <!-- Custom JavaScript -->
  <script src="{{ asset('js/received_docs_script.js') }}"></script>

  <!-- Send Back Modal -->
  <div class="modal fade" id="sendBackModal" tabindex="-1" aria-labelledby="sendBackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sendBackModalLabel">Send Back Document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="sendBackForm">
            <div class="mb-3">
              <label for="sendBackFile" class="form-label">Upload New Document</label>
              <input type="file" class="form-control" id="sendBackFile" required>
              <small class="text-muted">This will replace the original document and send it back to the sender.</small>
            </div>
            <div class="mb-3">
              <label for="sendBackNote" class="form-label">Optional Note</label>
              <textarea class="form-control" id="sendBackNote" rows="3" placeholder="Add a note (optional)"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="sendBackSubmitBtn">Send Back</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add this JS at the end of the file to enable dropdown on all pages -->
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
</body>
</html>