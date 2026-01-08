<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Register - Hasta Travel & Tours</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <title>Register - Hasta Travel & Tours</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>

    
    <style>
        :root {
            --primary-orange: #dc2626;
            --primary-dark-orange: #991b1b;
            --success-green: #059669;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --error-red: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-light);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Layout */
        .container {
            min-height: 100vh;
            display: flex;
        }

        .register-section {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--bg-light);
            overflow-y: auto;
        }

        .register-wrapper {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin: 20px 0;
        }

        /* Input Styles */
        .input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .input-field {
            width: 100%;
            height: 56px;
            padding: 16px 16px 16px 48px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.2s ease;
            background: white;
        }

        .input-field:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
            outline: none;
        }

        .input-field.error {
            border-color: var(--error-red);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 4px;
        }

        .toggle-password:hover {
            color: var(--text-primary);
        }

        .error-message {
            color: var(--error-red);
            font-size: 13px;
            margin-top: 6px;
        }

        /* IC Card Upload */
        .ic-upload-container {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--bg-light);
            cursor: pointer;
            margin-bottom: 20px;
        }

        .ic-upload-container:hover {
            border-color: var(--primary-orange);
            background: #fee2e2;
        }

        .ic-upload-container.drag-over {
            border-color: var(--primary-orange);
            background: #fee2e2;
        }

        .ic-upload-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            color: var(--text-secondary);
        }

        .ic-upload-text {
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .ic-upload-subtext {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .ic-preview {
            margin-top: 16px;
            display: none;
        }

        .ic-preview.show {
            display: block;
        }

        .ic-preview-image {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .ic-remove-btn {
            margin-top: 12px;
            padding: 8px 16px;
            background: var(--error-red);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .ic-remove-btn:hover {
            background: #b91c1c;
        }

        .scanning-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .scanning-overlay.show {
            display: flex;
        }

        .scanning-content {
            background: white;
            padding: 32px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
        }

        .scanning-spinner {
            width: 64px;
            height: 64px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-orange);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 24px;
        }

        /* Button Styles */
        .btn-primary {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-dark-orange) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 24px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
        }

        /* Brand Header */
        .brand-header {
            margin-bottom: 32px;
        }

        /* Form Title */
        .form-title {
            margin-bottom: 32px;
        }

        .form-title h2 {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-title p {
            font-size: 15px;
            color: var(--text-secondary);
        }

        /* Footer Links */
        .footer-links {
            margin-top: 24px;
            text-align: center;
        }

        .footer-links p {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }

        .footer-links a {
            color: var(--primary-orange);
            font-weight: 600;
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .footer-sub-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .footer-sub-links a {
            color: var(--text-secondary);
            font-weight: 400;
        }

        /* Branded Section */
        .gradient-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.9) 0%, rgba(153, 27, 27, 0.85) 100%);
        }

        .branded-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            padding: 48px;
            color: white;
        }

        .branded-content h2 {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .branded-content h2 span {
            color: #fee2e2;
        }

        .logo-text {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .branded-content > div:first-child p {
            font-size: 18px;
            opacity: 0.9;
            max-width: 480px;
            margin-bottom: 32px;
        }

        .feature-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-4px);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feature-text h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-text p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }

        .contact-banner {
            margin-top: 32px;
            padding: 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .contact-banner p {
            font-size: 14px;
            color: white;
        }

        .contact-banner span {
            font-weight: 700;
        }

        /* Responsive */
        @media (min-width: 1024px) {
            .register-wrapper {
                padding: 48px;
            }
        }

        /* Icons */
        .icon {
            width: 20px;
            height: 20px;
        }

        .icon-lg {
            width: 24px;
            height: 24px;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-section">
            <div class="register-wrapper animate-fade-in-up">
                <div class="logo-container" style="text-align: center; margin-bottom: 32px;">
                    <a href="/" style="text-decoration: none;">
                        <div class="logo-box">
                            <span class="logo-text">HASTA Travel</span>
                        </div>
                    </a>
                </div>

                <div class="form-title animate-fade-in" style="animation-delay: 0.2s;">
                    <h2>Create Account</h2>
                    <p>Enter your details to create an account</p>
                </div>

                @if ($errors->has('error'))
                    <div class="animate-fade-in" style="background-color: #fee2e2; border: 1px solid #dc2626; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        <strong>Error:</strong> {{ $errors->first('error') }}
                    </div>
                @endif
                
                @if ($errors->any() && !$errors->has('error'))
                     <div class="animate-fade-in" style="background-color: #fee2e2; border: 1px solid #dc2626; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        <ul style="list-style-type: disc; padding-left: 1.5rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('register') }}" id="registerForm" enctype="multipart/form-data">

                <form method="POST" action="{{ route('register') }}" id="registerForm" enctype="multipart/form-data">
                    @csrf


                    <div class="input-wrapper animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="input-field"
                            placeholder="Full Name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                        />
                        <x-input-error :messages="$errors->get('name')" class="error-message" />
                    </div>

                    <div class="input-wrapper animate-fade-in" style="animation-delay: 0.5s;">
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="input-field"
                            placeholder="Email address"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                        />
                        <x-input-error :messages="$errors->get('email')" class="error-message" />
                    </div>


                    <div class="input-wrapper animate-fade-in" style="animation-delay: 0.6s;">
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="input-field"
                            placeholder="Password"
                            required
                            autocomplete="new-password"
                        />
                        <button type="button" class="toggle-password" id="togglePassword">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <x-input-error :messages="$errors->get('password')" class="error-message" />
                    </div>

                    <div class="input-wrapper animate-fade-in" style="animation-delay: 0.7s;">
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="input-field"
                            placeholder="Confirm Password"
                            required
                            autocomplete="new-password"
                        />
                        <button type="button" class="toggle-password" id="togglePasswordConfirm">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="error-message" />
                    </div>

                    <button type="submit" class="btn-primary animate-fade-in" style="animation-delay: 0.8s;" id="submitBtn">
                        <span id="buttonText">Create Account</span>
                        <div class="spinner hidden" id="buttonSpinner"></div>
                    </button>
                </form>

                <div class="footer-links animate-fade-in" style="animation-delay: 0.9s;">
                    <p>Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
                    <div class="footer-sub-links">
                        <a href="#">Help</a>
                        <span>•</span>
                        <a href="#">Privacy Policy</a>
                        <span>•</span>
                        <a href="#">Terms</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


<script>
        // ... (Your scanner code) ...

        // Password Toggle for Password field
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                
                // Update the icon
                this.innerHTML = type === 'password'
                    ? `<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>`
                    : `<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>`;
            });
        }

        // Password Toggle for Confirm Password field
        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const passwordConfirmInput = document.getElementById('password_confirmation');

        if (togglePasswordConfirm && passwordConfirmInput) {
            togglePasswordConfirm.addEventListener('click', function() {
                const type = passwordConfirmInput.type === 'password' ? 'text' : 'password';
                passwordConfirmInput.type = type;
                
                // Update the icon
                this.innerHTML = type === 'password'
                    ? `<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>`
                    : `<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>`;
            });
        }

        // Form Submission with Loading State
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const buttonText = document.getElementById('buttonText');
        const buttonSpinner = document.getElementById('buttonSpinner');

        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                // Only show loading if form is valid (browser validation)
                if (registerForm.checkValidity()) {
                    submitBtn.disabled = true;
                    buttonText.classList.add('hidden');
                    buttonSpinner.classList.remove('hidden');
                }
            });
        }

        // Error Animation
        @if($errors->any())
            const form = document.getElementById('registerForm');
            if (form) {
                form.classList.add('animate-shake');
                setTimeout(() => {
                    form.classList.remove('animate-shake');
                }, 500);
            }
        @endif

    </script> </body>
</html>