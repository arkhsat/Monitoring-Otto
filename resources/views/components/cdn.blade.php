<!-- CDN Dependencies & Scripts -->
{{-- Lucide Icons --}}
<script src="https://unpkg.com/lucide@latest"></script>

{{-- Tailwind CSS CDN & Theme Config --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    otto: {
                        blue: '#066AB7',
                        blueDark: '#055696',
                        sky: '#0079C6',
                        border: '#8F9EB3',
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        }
    }
</script>

<style>
    .bg-login-otto {
        background-image: url('{{ asset('images/bg-login.png') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>

{{-- Vite Asset Fallback (Production / Bundled CSS & JS) --}}
@if (file_exists(public_path('build/manifest.json')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
