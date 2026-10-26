<!-- Status / Alert Message -->
@if (session('status'))
    <div class="w-full mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-sm font-medium flex items-center gap-2">
        <i data-lucide="info" class="w-5 h-5 flex-shrink-0 text-blue-600"></i>
        <span>{{ session('status') }}</span>
    </div>
@endif

<!-- Login Form -->
<form method="POST" action="{{ route('login.post') }}" id="loginForm" class="w-full flex flex-col gap-6">
    @csrf

    <!-- Username Field -->
    <div class="flex flex-col gap-2">
        <label for="username" class="text-slate-400 font-black text-lg md:text-xl tracking-tight">
            Username
        </label>
        <div class="relative">
            <input type="text" 
                   id="username" 
                   name="username" 
                   value="{{ old('username') }}"
                   required 
                   autocomplete="username"
                   autofocus
                   placeholder="Enter the username Ex: admin" 
                   class="w-full h-14 md:h-16 px-6 text-slate-800 text-lg border @error('username') border-rose-500 focus:ring-rose-400 @else border-[#8F9EB3] focus:ring-[#066AB7] @enderror rounded-[20px] focus:outline-none focus:ring-2 focus:border-transparent transition-all shadow-sm">
        </div>
        @error('username')
            <span class="text-sm font-semibold text-rose-500 mt-1 flex items-center gap-1">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                {{ $message }}
            </span>
        @enderror
    </div>

    <!-- Password Field -->
    <div class="flex flex-col gap-2">
        <label for="password" class="text-slate-400 font-black text-lg md:text-xl tracking-tight">
            Password
        </label>
        <div class="relative flex items-center">
            <input type="password" 
                   id="password" 
                   name="password" 
                   required 
                   autocomplete="current-password"
                   placeholder="Enter the Password" 
                   class="w-full h-14 md:h-16 px-6 pr-14 text-slate-800 text-lg border @error('password') border-rose-500 focus:ring-rose-400 @else border-[#8F9EB3] focus:ring-[#066AB7] @enderror rounded-[20px] focus:outline-none focus:ring-2 focus:border-transparent transition-all shadow-sm">
            
            <!-- Toggle Password Visibility -->
            <button type="button" 
                    id="togglePassword" 
                    tabindex="-1"
                    aria-label="Toggle password visibility"
                    class="absolute right-4 p-2 text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                <i data-lucide="eye" id="passwordIcon" class="w-6 h-6"></i>
            </button>
        </div>
        @error('password')
            <span class="text-sm font-semibold text-rose-500 mt-1 flex items-center gap-1">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                {{ $message }}
            </span>
        @enderror
    </div>

    <!-- Remember Me Checkbox -->
    <div class="flex items-center justify-between text-sm px-1">
        <label class="inline-flex items-center gap-2 cursor-pointer select-none text-slate-600 font-medium">
            <input type="checkbox" 
                   name="remember" 
                   class="w-4 h-4 rounded text-[#066AB7] focus:ring-[#066AB7] border-slate-300 transition cursor-pointer">
            <span>Remember Me</span>
        </label>
    </div>

    <!-- Submit Button -->
    <div class="mt-2">
        <button type="submit" 
                id="btnSubmit"
                class="w-full h-16 md:h-20 bg-[#066AB7] hover:bg-[#055696] active:scale-[0.99] text-white font-black text-xl md:text-2xl rounded-[20px] border border-[#9C9C9C] shadow-lg transition-all flex items-center justify-center cursor-pointer tracking-wide uppercase">
            <span id="btnText">LOGIN</span>
        </button>
    </div>
</form>
