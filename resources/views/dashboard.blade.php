<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BUCS DocuManage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="{{ asset('css/received_docs_styles.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard_styles.css" />
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
            <a href="{{ route('dashboard') }}" class="nav-link active">
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
            <a href="{{ route('trash') }}" class="nav-link">
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
    <div class="d-flex flex-column flex-grow-1 main-content">
      <!-- Success Message -->
      @if(session('success'))
        <div class="alert alert-success m-3">
          {{ session('success') }}
        </div>
      @endif
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
            <button class="btn btn-icon position-relative">
              <i class="bi bi-bell"></i>
              <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger rounded-circle">
                <span class="visually-hidden">New alerts</span>
              </span>
            </button>
            <div class="dropdown">
              <button class="btn btn-icon" id="avatarDropdown">
                <div class="avatar" data-user="current"></div>
              </button>
              <div class="dropdown-content" id="avatarDropdownContent">
                <a href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal">Profile</a>
                <a href="#">Settings</a>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item">Logout</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <div class="content-area p-4">
        <div class="row flex-column flex-lg-row">
          <!-- Left Section: Greeting and History -->
          <div class="col-lg-8 order-2 order-lg-1">
            <h2 class="fw-bold">Good Morning {{ explode(' ', Auth::user()->name)[0] }}</h2>
            <h5 class="text-muted mb-4">History</h5>
            <!-- History Table -->
            <div class="card shadow-sm mb-4">
              <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 fw-bold">Recent Received Documents</h6>
                  <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" class="form-control" placeholder="Search documents...">
                    <button class="btn btn-outline-secondary" type="button">
                      <i class="bi bi-search"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th scope="col">Reference #</th>
                        <th scope="col">Subject</th>
                        <th scope="col">From</th>
                        <th scope="col">Date Received</th>
                        <th scope="col">Action</th>
                        <th scope="col">Notes</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="documents-table-body">
                      @forelse ($paginator as $document)
                      <tr>
                          <td>{{ $document->id ?? '' }}</td>
                          <td>{{ $document->file_type ?? '' }}</td>
                          <td>{{ $document->from ?? '' }}</td>
                          <td>{{ $document->created_at ? $document->created_at->format('Y-m-d H:i') : '' }}</td>
                          <td>{{ $document->action_items ? implode(', ', $document->action_items) : 'No action required' }}</td>
                          <td>{{ $document->additional_notes ?? 'No notes' }}</td>
                          <td>
                              <a href="#" class="btn btn-sm btn-primary">View</a>
                          </td>
                      </tr>
                      @empty
                      <tr>
                          <td colspan="7" class="text-center">No received documents found.</td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="card-footer bg-white">
                <div class="d-flex justify-content-end align-items-center">
                  <div class="pagination-container">
                    {{ $paginator->links('pagination::bootstrap-4') }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Section: Profile Card + Calendar -->
          <div class="col-lg-4 order-1 order-lg-2 mb-4 mb-lg-0">
            <!-- Profile Card -->
            <div class="card mb-4 shadow-sm profile-id-card position-relative">
              <img src="images/BICOL-UNIVERSITY.jpg" class="card-img-top" alt="Background Image">
              <div class="profile-picture-box position-absolute">
                <img src="images/russ.jpg" alt="Profile" class="img-thumbnail" id="profilePicturePreview">
              </div>
              <div class="card-body text-start">
                <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                <p class="mb-1">{{ Auth::user()->position ?: 'Position not set' }}</p>
                <p class="mb-1">{{ Auth::user()->rank ?: 'Rank not set' }}</p>
                <p class="mb-2">College of Science</p>
              </div>
            </div>

            <!-- Calendar -->
            <div class="card shadow-sm">
              <div class="card-body">
                <h6 class="fw-bold text-center" id="calendar-month-year">Loading...</h6>
                <table class="table table-bordered text-center calendar-table mt-3">
                  <thead class="table-light">
                    <tr>
                      <th>SUN</th><th>MON</th><th>TUE</th><th>WED</th><th>THU</th><th>FRI</th><th>SAT</th>
                    </tr>
                  </thead>
                  <tbody id="calendar-body">
                    <!-- Calendar dynamically generated -->
                  </tbody>
                </table>
                <div class="mt-2 d-flex align-items-center justify-content-end">
                  <span class="due-date-indicator me-2"></span>
                  <small class="text-muted">Document Due Date</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Profile Modal -->
  <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold" id="editProfileModalLabel">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pt-3">
          <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
              <img src="images/russ.jpg" alt="Profile" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" id="profilePicturePreview">
            </div>
          </div>

          <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
            @csrf
            <div class="mb-3">
              <label for="profileName" class="form-label text-muted small">Full Name</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-person"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" id="profileName" name="name" 
                       value="{{ Auth::user()->name }}" placeholder="Enter your full name">
              </div>
            </div>

            <div class="mb-3">
              <label for="profilePosition" class="form-label text-muted small">Position</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-briefcase"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" id="profilePosition" name="position" 
                       value="{{ Auth::user()->position }}" placeholder="Enter your position">
              </div>
            </div>

            <div class="mb-3">
              <label for="profileRank" class="form-label text-muted small">Academic Rank</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-award"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" id="profileRank" name="rank" 
                       value="{{ Auth::user()->rank }}" placeholder="Enter your academic rank">
              </div>
            </div>

            <div id="profileUpdateSuccess" class="alert alert-success d-none" role="alert">
              Profile updated successfully!
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/dashboard_script.js"></script>
  <style>
  .pagination-container {
    display: flex;
    justify-content: flex-end;
    width: 100%;
  }
  .pagination {
    margin-bottom: 0;
    font-size: 0.85rem;
  }
  </style>
</body>
</html>
