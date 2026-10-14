<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

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

        // Form Submit Feedback
        const loginForm = document.getElementById('loginForm');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');

        if (loginForm && btnSubmit && btnText) {
            loginForm.addEventListener('submit', () => {
                btnSubmit.classList.add('opacity-80', 'cursor-not-allowed');
                btnText.textContent = 'LOGGING IN...';
            });
        }
    });
</script>
