<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Login</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #5470c6;
            --secondary-color: #91cc75;
            --light-gray: #f8f9fa;
            --border-color: #e3e6f0;
            --text-muted: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Background Image */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%23f8f9fa;stop-opacity:1" /><stop offset="100%" style="stop-color:%23e9ecef;stop-opacity:1" /></linearGradient></defs><rect width="1200" height="800" fill="url(%23bg)"/><g opacity="0.1"><circle cx="200" cy="150" r="80" fill="%23007bff"/><circle cx="1000" cy="200" r="60" fill="%2328a745"/><circle cx="300" cy="600" r="40" fill="%23ffc107"/><circle cx="900" cy="650" r="50" fill="%23dc3545"/></g></svg>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .brand-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 12px;
            margin-bottom: 15px;
        }
        
        .brand-logo i {
            color: white;
            font-size: 24px;
        }
        
        .brand-name {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        
        .brand-subtitle {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            position: relative;
        }
        
        .login-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(84, 112, 198, 0.25);
        }
        
        .form-control::placeholder {
            color: #adb5bd;
            opacity: 1;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .form-check-input {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            margin-top: 0;
            vertical-align: top;
            background-color: #fff;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-label {
            font-size: 14px;
            color: #495057;
            cursor: pointer;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            color: #3d5aa3;
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            color: #fff;
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            margin-bottom: 25px;
        }
        
        .btn-login:hover {
            background-color: #3d5aa3;
            border-color: #3d5aa3;
            transform: translateY(-1px);
        }
        
        .btn-login:focus {
            box-shadow: 0 0 0 0.2rem rgba(84, 112, 198, 0.5);
        }
        
        .signup-link {
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .signup-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 12px 16px;
            font-size: 14px;
        }
        
        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        
        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        
        .is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            width: 100%;
            margin-top: 4px;
            font-size: 12px;
            color: #dc3545;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 30px 20px;
            }
            
            .brand-name {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Brand Section -->
        <div class="brand-section">
            <div class="brand-logo">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="brand-name">مزرعتي - Mazra3ty</div>
            <div class="brand-subtitle">Admin Panel Design by Coderthemes</div>
        </div>
        
        <!-- Login Card -->
        <div class="login-card">
            <h4 class="login-title">Log in to your account</h4>
            
            <!-- Success Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.login') }}">
                @csrf
                
                <!-- Phone Number Field -->
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" 
                           name="phone" 
                           placeholder="Enter your phone number"
                           value="{{ old('phone') }}" 
                           required 
                           autofocus>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="remember" 
                               name="remember" 
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login">
                    Login
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="footer-text">
            2025 © mazra3ty - By <a href="#" target="_blank">Kings Team</a>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>