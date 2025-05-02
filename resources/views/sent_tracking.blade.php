<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUCS DocuManage</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/sent_tracking_styles.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Replaced with index.blade.php's nav-bar) -->
            <aside class="col-md-3 col-lg-2 sidebar collapse d-md-block" id="sidebarMenu">
                <div class="position-sticky">
                    <!-- Sidebar Header -->
                    <div class="sidebar-header d-flex align-items-center">
                        <i class="bi bi-file-text fs-4 me-2"></i>
                        <span class="fw-semibold">BUCS DocuManage</span>
                    </div>

                    <!-- Navigation Links -->
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
                                <a href="#" class="nav-link active">
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
                                <a href="#" class="nav-link">
                                    <i class="bi bi-trash me-2"></i>
                                    Trash
                                </a>
                            </div>
                        </div>
                    </nav>

                    <!-- Sidebar Footer -->
                    <div class="sidebar-footer mt-auto">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar" data-user="current"></div>
                            <div>
                                <div class="fw-medium">{{ Auth::user()->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="tracking-container">
                    <!-- Header with Toggle Button -->
                    <div class="tracking-header d-flex justify-content-between align-items-center">
                        <nav class="navbar navbar-light bg-light d-md-none">
                            <button class="navbar-toggler d-md-none" type="button" id="sidebarToggle">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                        </nav>
                        <h5>Tracking Details</h5>
                        <div class="actions">
                            <i class="bi bi-bell"></i>
                            <div class="user-avatar header-avatar">JD</div>
                        </div>
                    </div>

                    <!-- Courier Info -->
                    <div class="courier-info">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="info-label">Courier Info</p>
                                <p class="courier-name">Delivery Partner: J&T Express PH</p>
                                <p class="courier-person">Courier: Ocw Jorge T. Alesib</p>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between">
                                    <p class="info-label">Tracking Number</p>
                                    <div class="tracking-number">
                                        828018L942398 <i class="bi bi-clipboard"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Progress -->
                    <div class="tracking-progress">
                        <div class="stepper-wrapper">
                            <div class="stepper-item completed">
                                <div class="step-counter">
                                    <i class="bi bi-pencil-square"></i>
                                </div>
                                <div class="step-name">Processing</div>
                            </div>
                            <div class="stepper-item completed">
                                <div class="step-counter">
                                    <i class="bi bi-house"></i>
                                </div>
                                <div class="step-name">Packed</div>
                            </div>
                            <div class="stepper-item completed">
                                <div class="step-counter">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div class="step-name">Shipped</div>
                            </div>
                            <div class="stepper-item completed">
                                <div class="step-counter">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="step-name">Delivered</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="tracking-timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">Jan 29 10:51</div>
                            <div class="timeline-badge completed">
                                <i class="bi bi-check"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-title">Delivered</div>
                                <div class="timeline-description">Package has been delivered.</div>
                                <div class="timeline-notice">
                                    Please check item if complete and in good condition. For filing a Return/Refund, click "View Order Detail" on the top right portion of this page. [<a href="#">BARAGA</a>]
                                </div>
                            </div>
                        </div>
                        <!-- Additional timeline items omitted for brevity -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/sent_tracking_script.js"></script>
</body>
</html>