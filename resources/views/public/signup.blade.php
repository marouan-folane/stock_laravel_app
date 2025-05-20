<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign Up - {{ config('app.name', 'Stock Management System') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f8fa;
        }
        .navbar-top {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .signup-container {
            max-width: 650px;
            margin: 2rem auto;
        }
        .form-card {
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="bi bi-box-seam text-primary me-2"></i>{{ config('app.name', 'Stock Management System') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-2 text-white" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Signup Section -->
    <div class="container signup-container">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Create Your Account</h1>
            <p class="text-muted">Join our platform and start managing your inventory efficiently</p>
        </div>

        <div class="card form-card">
            <div class="card-body p-4 p-md-5">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.register') }}">
                    @csrf

                    @if(isset($redirect))
                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                    @endif

                    <div class="row g-3">
                        <!-- Name -->
                        <div class="col-12">
                            <label for="name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address') }}" required>
                                @error('address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                            <button type="button" class="btn btn-light w-100 mt-2" onclick="window.location.href='{{ route('google-auth') }}'" style="border: 1px solid #e5e7eb; color: #1e3a8a; font-weight: 500;">
                                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo" style="width:20px; vertical-align:middle; margin-right:8px;">
                                S'inscrire avec Google
                            </button>
                        </div>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 bg-light mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">© {{ date('Y') }} {{ config('app.name', 'Stock Management System') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
