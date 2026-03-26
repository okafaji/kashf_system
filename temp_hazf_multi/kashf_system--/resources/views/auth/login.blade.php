<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" autocomplete="on" id="loginForm">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="'البريد الإلكتروني'" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" placeholder="أدخل بريدك الإلكتروني" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="'كلمة المرور'" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="أدخل كلمة المرور" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">تذكرني</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <div class="flex gap-2">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        هل نسيت كلمة المرور؟
                    </a>
                @endif

                @if (Route::has('register'))
                    <span class="text-gray-300">|</span>
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
                        إنشاء حساب جديد
                    </a>
                @endif
            </div>

            <x-primary-button class="ms-3">
                تسجيل الدخول
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const rememberCheckbox = document.getElementById('remember_me');
            const loginForm = document.getElementById('loginForm');

            const STORE_KEY = 'login_remember_map';
            const LAST_EMAIL_KEY = 'login_last_email';

            const readStore = function() {
                try {
                    return JSON.parse(localStorage.getItem(STORE_KEY) || '{}');
                } catch (e) {
                    return {};
                }
            };

            const writeStore = function(store) {
                localStorage.setItem(STORE_KEY, JSON.stringify(store));
            };

            const applyRemembered = function(email) {
                const store = readStore();
                const entry = store[email];
                if (entry && entry.remember) {
                    passwordInput.value = entry.password || '';
                    rememberCheckbox.checked = true;
                } else {
                    passwordInput.value = '';
                    rememberCheckbox.checked = false;
                }
            };

            // استرجاع آخر بريد مستخدم محفوظ
            const lastEmail = localStorage.getItem(LAST_EMAIL_KEY);
            if (lastEmail) {
                emailInput.value = lastEmail;
                applyRemembered(lastEmail);
            }

            // تحديث الحقول عند تغيير البريد
            emailInput.addEventListener('input', function() {
                const email = emailInput.value.trim();
                if (email) {
                    applyRemembered(email);
                } else {
                    passwordInput.value = '';
                    rememberCheckbox.checked = false;
                }
            });

            // حفظ البيانات عند الضغط على الدخول
            loginForm.addEventListener('submit', function() {
                const email = emailInput.value.trim();
                if (!email) {
                    return;
                }

                const store = readStore();

                if (rememberCheckbox.checked) {
                    store[email] = {
                        password: passwordInput.value,
                        remember: true,
                    };
                } else if (store[email]) {
                    delete store[email];
                }

                writeStore(store);
                localStorage.setItem(LAST_EMAIL_KEY, email);
            });

            // إزالة بيانات هذا البريد عند إلغاء التذكر
            rememberCheckbox.addEventListener('change', function() {
                const email = emailInput.value.trim();
                if (!this.checked && email) {
                    const store = readStore();
                    if (store[email]) {
                        delete store[email];
                        writeStore(store);
                    }
                }
            });
        });
    </script>
</x-guest-layout>
