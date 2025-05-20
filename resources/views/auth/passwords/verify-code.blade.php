<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Stock Manager') }} - Verify Code</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #3b82f6;
            --accent-color: #dbeafe;
            --text-color: #1f2937;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            color: var(--text-color);
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .login-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .login-form-section {
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-image-section {
            width: 50%;
            background-image: url('back.jpeg');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .login-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.15),
                        0 5px 10px rgba(59, 130, 246, 0.1),
                        0 1px 1px rgba(30, 64, 175, 0.05);
            overflow: hidden;
        }

        .login-header {
            background: var(--accent-color);
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .app-logo {
            max-width: 80px;
            margin-bottom: 1rem;
        }

        .login-form-content {
            padding: 2rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #1e40af;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(30, 64, 175, 0.1);
        }

        .code-input {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .code-input input {
            width: 3rem;
            height: 3.5rem;
            font-size: 1.5rem;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin: 0 0.25rem;
        }

        .code-input input:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .footer {
            background-color: #f3f4f6;
            padding: 1rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
        }

        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                height: auto;
            }

            .login-form-section, .login-image-section {
                width: 100%;
            }

            .login-image-section {
                height: 250px;
                order: -1;
            }

            .login-form-section {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="login-card">
                <div class="login-header">
                    <img src="{{ asset('img/logo.jpg') }}" alt="Stock Manager Logo" class="app-logo">
                    <h1 class="h4" style="color: var(--primary-color); margin-bottom: 0.5rem;">{{ __('Verify Code') }}</h1>
                    <p style="color: #6b7280; margin: 0;">Enter the 6-digit code sent to {{ $email }}</p>
                </div>

                <div class="login-form-content">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.code.verify') }}" id="codeForm">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <input type="hidden" name="code" id="codeInput">

                        <div class="code-input">
                            <input type="text" maxlength="1" pattern="[0-9]" class="code-digit" inputmode="numeric" autofocus>
                            <input type="text" maxlength="1" pattern="[0-9]" class="code-digit" inputmode="numeric">
                            <input type="text" maxlength="1" pattern="[0-9]" class="code-digit" inputmode="numeric">
                            <input type="text" maxlength="1" pattern="[0-9]" class="code-digit" inputmode="numeric">
                            <input type="text" maxlength="1" pattern="[0-9]" class="code-digit" inputmode="numeric">
                            <input type="text" maxlength="1" pattern="[0-9]" class="code-digit" inputmode="numeric">
                        </div>

                        @error('code')
                            <div class="alert alert-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                        @enderror

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> {{ __('Verify Code') }}
                        </button>

                        <div style="text-align: center; margin-top: 1.5rem;">
                            <a href="{{ route('password.code.request') }}" style="color: var(--secondary-color); text-decoration: none;">
                                Request a new code
                            </a>
                        </div>
                    </form>
                </div>

                <div class="footer">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Stock Manager') }}. All rights reserved.
                </div>
            </div>
        </div>

        <div class="login-image-section" style="background-image: url('{{ asset('img/back.jpeg') }}');">
            <div class="login-image-overlay">
                <!-- Content for the image overlay can be added here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeDigits = document.querySelectorAll('.code-digit');
            const codeInput = document.getElementById('codeInput');
            const form = document.getElementById('codeForm');

            // Auto-focus next input when a digit is entered
            codeDigits.forEach((digit, index) => {
                digit.addEventListener('input', function() {
                    if (this.value.length === 1) {
                        if (index < codeDigits.length - 1) {
                            codeDigits[index + 1].focus();
                        }
                    }
                });

                // Handle backspace
                digit.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        codeDigits[index - 1].focus();
                    }
                });
            });

            // Combine all digits when submitting
            form.addEventListener('submit', function(e) {
                let code = '';
                codeDigits.forEach(digit => {
                    code += digit.value;
                });

                if (code.length !== 6) {
                    e.preventDefault();
                    alert('Please enter all 6 digits of the verification code.');
                    return;
                }

                codeInput.value = code;
            });
        });
    </script>
</body>
</html> 