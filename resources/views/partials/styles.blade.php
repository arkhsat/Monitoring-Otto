<style>
    body {
        font-family: 'Inter', sans-serif;
    }
    /* Custom scrollbar matching Figma style */
    .figma-scrollbar::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    .figma-scrollbar::-webkit-scrollbar-track {
        background: #BBE4FF;
        border-radius: 9999px;
        margin: 4px;
    }
    .figma-scrollbar::-webkit-scrollbar-thumb {
        background: #0079C6;
        border-radius: 9999px;
    }
    .figma-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #005f9e;
    }

    /* Login Page Background */
    .bg-login-otto {
        background-image: url('{{ asset('images/bg-login.png') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>
