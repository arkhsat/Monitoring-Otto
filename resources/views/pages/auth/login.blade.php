@extends('layouts.guest', ['title' => 'Login - Otto System Monitoring'])

@section('content')
    @include('pages.auth.partials.form')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Toggle Password Visibility
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                // Swap icon
                toggleBtn.innerHTML = isPassword 
                    ? '<i data-lucide="eye-off" class="w-6 h-6"></i>' 
                    : '<i data-lucide="eye" class="w-6 h-6"></i>';

                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        }

        // RESTful API Form Submission
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const loginAlert = document.getElementById('loginAlert');
        const loginAlertText = document.getElementById('loginAlertText');

        if (loginForm && btnSubmit && btnText) {
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (loginAlert) loginAlert.classList.add('hidden');
                btnSubmit.classList.add('opacity-80', 'cursor-not-allowed');
                btnText.textContent = 'LOGGING IN...';

                const username = document.getElementById('username')?.value || '';
                const password = document.getElementById('password')?.value || '';
                const remember = document.querySelector('input[name="remember"]')?.checked || false;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: JSON.stringify({ username, password, remember })
                    });

                    const json = await response.json();

                    if (!response.ok || !json.success) {
                        const errMsg = json.message || (json.errors ? Object.values(json.errors).flat().join(', ') : 'Username atau password salah.');
                        throw new Error(errMsg);
                    }

                    // Simpan token Bearer di client storage
                    if (json.token) {
                        localStorage.setItem('auth_token', json.token);
                    }

                    btnText.textContent = 'BERHASIL! MENGALIHKAN...';

                    // Redirect ke dashboard
                    window.location.href = json.redirect_url || '/main';

                } catch (err) {
                    if (loginAlert && loginAlertText) {
                        loginAlertText.textContent = err.message || 'Gagal login ke sistem.';
                        loginAlert.classList.remove('hidden');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                    btnSubmit.classList.remove('opacity-80', 'cursor-not-allowed');
                    btnText.textContent = 'LOGIN';
                }
            });
        }
    });
</script>
@endpush
