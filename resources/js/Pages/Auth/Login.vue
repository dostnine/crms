<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ScienceBackground from '@/Components/ScienceBackground.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="auth">
        <ScienceBackground />

        <a href="/" class="auth-brand">
            <img src="/images/dost-logo.png" alt="DOST Logo" class="brand-logo" />
            <span>Department of Science and Technology</span>
        </a>

        <main class="auth-main">
            <section class="auth-card">
                <div class="auth-head">
                    <img src="/images/dost-logo.png" alt="DOST Logo" class="card-logo" />
                    <h1>Welcome back</h1>
                    <p>Sign in to the Customer Relation Management System</p>
                </div>

                <div v-if="status" class="auth-status">{{ status }}</div>

                <form @submit.prevent="submit" novalidate>
                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6" />
                            </svg>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="you@dost.gov.ph"
                                required
                                autofocus
                                autocomplete="username"
                                :class="{ 'has-error': form.errors.email }"
                            />
                        </div>
                        <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="11" width="14" height="9" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V8a4 4 0 018 0v3" />
                            </svg>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                                :class="{ 'has-error': form.errors.password }"
                            />
                            <button
                                type="button"
                                class="toggle"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                @click="showPassword = !showPassword"
                            >
                                <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 004.2 4.2M9.9 5.2A9.6 9.6 0 0112 5c6.5 0 10 7 10 7a17 17 0 01-3.2 4M6.3 6.3A17 17 0 002 12s3.5 7 10 7a9.7 9.7 0 003.9-.8" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                    </div>

                    <div class="field-row">
                        <label class="remember">
                            <input v-model="form.remember" type="checkbox" />
                            <span>Remember me</span>
                        </label>
                        <a v-if="canResetPassword" href="/forgot-password" class="forgot">Forgot password?</a>
                    </div>

                    <button type="submit" class="submit" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner" aria-hidden="true"></span>
                        <span>{{ form.processing ? 'Signing in…' : 'Sign in' }}</span>
                    </button>
                </form>
            </section>

            <p class="auth-foot">Authorized personnel only · Your data is protected under the DOST privacy policy.</p>
        </main>
    </div>
</template>

<style scoped>
.auth {
    position: relative;
    min-height: 100vh;
    color: #e7ecff;
    background: radial-gradient(120% 120% at 50% 0%, #0d1533 0%, #070b1e 55%, #05060f 100%);
    overflow-x: hidden;
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.auth-brand {
    position: fixed;
    top: 22px;
    left: 26px;
    z-index: 20;
    display: flex;
    align-items: center;
    gap: 11px;
    text-decoration: none;
    color: #e7ecff;
    font-weight: 700;
    font-size: 0.98rem;
}
.brand-logo { height: 38px; width: 38px; object-fit: contain; }

.auth-main {
    position: relative;
    z-index: 10;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100px 20px 48px;
}

.auth-card {
    width: 100%;
    max-width: 440px;
    padding: 40px 38px;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(18px);
    box-shadow: 0 30px 80px rgba(3, 6, 20, 0.55);
    animation: rise 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.auth-head { text-align: center; margin-bottom: 28px; }
.card-logo { height: 62px; width: 62px; object-fit: contain; margin-bottom: 16px; }
.auth-head h1 {
    font-size: 1.7rem;
    font-weight: 800;
    letter-spacing: -0.4px;
    margin: 0 0 6px;
}
.auth-head p { color: #9fabd4; font-size: 0.96rem; margin: 0; }

.auth-status {
    margin-bottom: 20px;
    padding: 12px 16px;
    border-radius: 12px;
    background: rgba(52, 211, 153, 0.12);
    border: 1px solid rgba(52, 211, 153, 0.4);
    color: #6ee7b7;
    font-size: 0.9rem;
    text-align: center;
}

.field { margin-bottom: 18px; }
.field label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #b7c2e8;
    margin-bottom: 8px;
    letter-spacing: 0.2px;
}
.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon {
    position: absolute;
    left: 14px;
    width: 20px;
    height: 20px;
    color: #7d88b0;
    pointer-events: none;
}
.input-wrap input {
    width: 100%;
    padding: 13px 14px 13px 44px;
    border-radius: 12px;
    background: rgba(9, 13, 30, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #eaf0ff;
    font-size: 0.98rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}
.input-wrap input::placeholder { color: #6b76a0; }
.input-wrap input:focus {
    outline: none;
    border-color: #38bdf8;
    background: rgba(9, 13, 30, 0.85);
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.16);
}
.input-wrap input.has-error {
    border-color: #f87171;
    box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.14);
}
.toggle {
    position: absolute;
    right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: none;
    background: transparent;
    color: #8b97c2;
    cursor: pointer;
    border-radius: 8px;
    transition: color 0.2s ease, background 0.2s ease;
}
.toggle:hover { color: #cdd6f5; background: rgba(255, 255, 255, 0.06); }
.toggle svg { width: 20px; height: 20px; }

.field-error { margin: 7px 2px 0; color: #fca5a5; font-size: 0.82rem; }

.field-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 6px 0 24px;
}
.remember {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #b7c2e8;
    cursor: pointer;
}
.remember input {
    width: 16px;
    height: 16px;
    accent-color: #38bdf8;
    cursor: pointer;
}
.forgot {
    font-size: 0.9rem;
    color: #7dd3fc;
    text-decoration: none;
    transition: color 0.2s ease;
}
.forgot:hover { color: #bae6fd; text-decoration: underline; }

.submit {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px;
    border: none;
    border-radius: 999px;
    font-size: 1rem;
    font-weight: 700;
    color: #06122b;
    cursor: pointer;
    background: linear-gradient(135deg, #67e8f9 0%, #38bdf8 45%, #818cf8 100%);
    box-shadow: 0 12px 34px rgba(56, 189, 248, 0.35);
    transition: transform 0.22s ease, box-shadow 0.22s ease, opacity 0.22s ease;
}
.submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 18px 46px rgba(56, 189, 248, 0.5); }
.submit:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(6, 18, 43, 0.35);
    border-top-color: #06122b;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

.auth-foot {
    margin: 26px 0 0;
    max-width: 420px;
    text-align: center;
    font-size: 0.82rem;
    color: #6f7aa4;
    line-height: 1.5;
}

@keyframes rise {
    from { opacity: 0; transform: translateY(26px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 560px) {
    .auth-brand span { display: none; }
    .auth-card { padding: 32px 24px; }
}

@media (prefers-reduced-motion: reduce) {
    .auth-card { animation: none; }
    .submit:hover:not(:disabled) { transform: none; }
}
</style>
