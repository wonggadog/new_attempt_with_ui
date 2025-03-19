<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('images/bucslogo1.png') }}">
    <title>BUCS Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login_styles.css') }}">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-container">
            <div class="row g-0">
                <div class="col-md-6 left-column">
                    <div class="text-center p-4">
                        <img src="{{ asset('images/bucslogo1.png') }}" alt="BUCS Logo" class="logo">
                        <div class="mt-4">
                            <h4 class="system-title">BUCS Document</h4>
                            <h4 class="system-title">Management System</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 right-column">
                    <div class="login-form-container p-4">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="id_number" class="form-label">ID Number</label>
                                <input type="text" class="form-control @error('id_number') is-invalid @enderror" id="id_number" name="id_number" value="{{ old('id_number') }}" required autofocus>
                                @error('id_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="mb-3 password-container">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                <span class="password-toggle" onclick="togglePassword()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                    </svg>
                                </span>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary login-btn">Log In</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a>
                        </div>
                        <div class="divider my-4">
                            <span class="divider-line"></span>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-light google-btn" onclick="handleGoogleSignIn()">
                                <img src="{{ asset('images/googlelogo.png') }}" alt="Google Logo" class="google-icon">
                                Sign in with Google
                            </button>
                            <a href="#" class="bu-icto">Bicol University ICTO</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/login_script.js') }}"></script>
</body>
</html>