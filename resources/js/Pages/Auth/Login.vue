<script setup>
/**
 * Auth/Login.vue
 * ──────────────
 * Laravel Breeze-এর default Login page replace করা হয়েছে।
 *
 * Breeze যে props পাঠায়:
 *   canResetPassword → true/false (forgot password link দেখাবে কিনা)
 *   status           → password reset success message
 *
 * useForm() থেকে:
 *   form.post('/login') → Laravel-এর AuthenticatedSessionController@store
 *   form.errors.email / form.errors.password → server validation errors
 *   form.processing → submit button loading state
 */
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status:           String,
})

const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Login — Digital Blood Connect" />

    <div class="auth-bg">

        <!-- Left Panel — Branding (desktop only) -->
        <div class="auth-left">
            <div class="auth-brand">
                <div class="auth-brand-icon">🩸</div>
                <h1 class="auth-brand-title">Digital Blood<br/>Connect</h1>
                <p class="auth-brand-sub">
                    Connecting blood donors with patients<br/>
                    across Chittagong &amp; Sitakunda
                </p>
                <!-- Feature list -->
                <div class="auth-features">
                    <div v-for="f in [
                        '🏅 Earn badges for every donation',
                        '🔒 Your phone number stays private',
                        '⚡ Find donors in seconds',
                        '🚨 Post emergency blood requests',
                    ]" :key="f" class="auth-feature-item">
                        {{ f }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel — Login Form -->
        <div class="auth-right">
            <div class="auth-card">

                <!-- Mobile logo (hidden on desktop) -->
                <div class="auth-mobile-logo">
                    <span style="font-size:2rem;">🩸</span>
                    <span class="auth-mobile-logo-text">Digital Blood Connect</span>
                </div>

                <h2 class="auth-card-title">Welcome back</h2>
                <p class="auth-card-sub">Login to your donor account</p>

                <!-- Password reset success message -->
                <div v-if="status" class="alert-success">
                    {{ status }}
                </div>

                <!-- ── Email ───────────────────────────────────── -->
                <div class="field">
                    <label class="field-label">Email Address</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            placeholder="your@email.com"
                            class="field-input"
                            :class="{ 'field-input-err': form.errors.email }"
                            required
                            autofocus
                        />
                    </div>
                    <p v-if="form.errors.email" class="field-err">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- ── Password ───────────────────────────────── -->
                <div class="field">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <label class="field-label" style="margin:0;">Password</label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="forgot-link"
                        >
                            Forgot password?
                        </Link>
                    </div>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="field-input"
                            :class="{ 'field-input-err': form.errors.password }"
                            required
                        />
                    </div>
                    <p v-if="form.errors.password" class="field-err">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- ── Remember Me ────────────────────────────── -->
                <label class="remember-wrap">
                    <input v-model="form.remember" type="checkbox" class="remember-check" />
                    <span class="remember-label">Remember me for 30 days</span>
                </label>

                <!-- ── Submit ─────────────────────────────────── -->
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="btn-submit"
                    :class="{ 'btn-submit-loading': form.processing }"
                >
                    <span v-if="form.processing" class="spinner"></span>
                    {{ form.processing ? 'Logging in...' : '🩸 Login to Dashboard' }}
                </button>

                <!-- ── Divider ────────────────────────────────── -->
                <div class="divider"><span>or</span></div>

                <!-- ── Register Link ─────────────────────────── -->
                <p class="auth-switch">
                    Don't have an account?
                    <Link :href="route('register')" class="auth-switch-link">
                        Register as Donor →
                    </Link>
                </p>

                <!-- Demo credentials (Science Fair) -->
                <div class="demo-box">
                    <p class="demo-title">🧪 Demo Accounts</p>
                    <div class="demo-grid">
                        <div>
                            <p class="demo-role">Admin</p>
                            <p class="demo-cred">admin@digitalbloodconnect.bd</p>
                            <p class="demo-cred">admin123</p>
                        </div>
                        <div>
                            <p class="demo-role">Donor</p>
                            <p class="demo-cred">rahim@example.com</p>
                            <p class="demo-cred">password</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

<style scoped>
/* ── Layout ──────────────────────────────────────────────── */
.auth-bg {
    min-height: 100vh;
    display: flex;
    background: #F9FAFB;
}

/* Left branding panel */
.auth-left {
    display: none;
    background: linear-gradient(160deg, #8B0000 0%, #BC0000 50%, #D10000 100%);
    flex: 1;
    padding: 3rem;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.auth-left::before {
    content: '';
    position: absolute;
    top: -100px; right: -100px;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}

.auth-left::after {
    content: '';
    position: absolute;
    bottom: -120px; left: -80px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(0,0,0,0.1);
}

@media (min-width: 1024px) {
    .auth-left { display: flex; }
}

.auth-brand { position: relative; z-index: 1; }

.auth-brand-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    display: block;
}

.auth-brand-title {
    font-size: 2.25rem;
    font-weight: 800;
    color: white;
    line-height: 1.2;
    margin: 0 0 1rem;
    font-family: 'Georgia', serif;
}

.auth-brand-sub {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
    line-height: 1.6;
    margin: 0 0 2rem;
}

.auth-features { display: flex; flex-direction: column; gap: 0.65rem; }

.auth-feature-item {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    padding: 0.65rem 1rem;
    color: white;
    font-size: 0.875rem;
}

/* Right form panel */
.auth-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    max-width: 100%;
}

@media (min-width: 1024px) {
    .auth-right { max-width: 520px; }
}

/* Card */
.auth-card {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    border: 1px solid #E5E7EB;
}

/* Mobile logo (shows on small screens) */
.auth-mobile-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.auth-mobile-logo-text {
    font-size: 1.1rem;
    font-weight: 800;
    color: #BC0000;
    font-family: 'Georgia', serif;
}
@media (min-width: 1024px) {
    .auth-mobile-logo { display: none; }
}

.auth-card-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0 0 0.25rem;
    font-family: 'Georgia', serif;
}

.auth-card-sub {
    font-size: 0.875rem;
    color: #6B7280;
    margin: 0 0 1.75rem;
}

/* ── Alert ───────────────────────────────────────────────── */
.alert-success {
    background: #DCFCE7;
    border: 1px solid #86EFAC;
    color: #166534;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    margin-bottom: 1.25rem;
}

/* ── Form Fields ─────────────────────────────────────────── */
.field { margin-bottom: 1.1rem; }

.field-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.field-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.field-icon {
    position: absolute;
    left: 12px;
    color: #9CA3AF;
    display: flex;
    align-items: center;
    pointer-events: none;
}

.field-input {
    width: 100%;
    padding: 0.7rem 0.75rem 0.7rem 2.5rem;
    border: 1.5px solid #D1D5DB;
    border-radius: 8px;
    font-size: 0.9rem;
    outline: none;
    box-sizing: border-box;
    color: #1a1a1a;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: sans-serif;
}

.field-input:focus {
    border-color: #BC0000;
    box-shadow: 0 0 0 3px rgba(188, 0, 0, 0.1);
}

.field-input-err {
    border-color: #BC0000 !important;
}

.field-err {
    color: #BC0000;
    font-size: 0.75rem;
    margin: 4px 0 0;
}

/* Forgot link */
.forgot-link {
    font-size: 0.78rem;
    color: #BC0000;
    text-decoration: none;
    font-weight: 500;
}
.forgot-link:hover { text-decoration: underline; }

/* ── Remember Me ─────────────────────────────────────────── */
.remember-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    margin-bottom: 1.5rem;
}

.remember-check {
    width: 16px;
    height: 16px;
    accent-color: #BC0000;
    cursor: pointer;
}

.remember-label {
    font-size: 0.82rem;
    color: #4B5563;
}

/* ── Submit Button ───────────────────────────────────────── */
.btn-submit {
    width: 100%;
    padding: 0.85rem;
    background: #BC0000;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.2s, transform 0.1s, opacity 0.2s;
    font-family: sans-serif;
}

.btn-submit:hover:not(:disabled) {
    background: #8B0000;
    transform: translateY(-1px);
}

.btn-submit:disabled,
.btn-submit-loading {
    opacity: 0.75;
    cursor: not-allowed;
    transform: none !important;
}

/* Loading spinner */
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    flex-shrink: 0;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ── Divider ─────────────────────────────────────────────── */
.divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1.25rem 0;
    color: #D1D5DB;
    font-size: 0.8rem;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #E5E7EB;
}

.divider span { color: #9CA3AF; }

/* ── Auth switch link ────────────────────────────────────── */
.auth-switch {
    text-align: center;
    font-size: 0.875rem;
    color: #6B7280;
    margin: 0;
}

.auth-switch-link {
    color: #BC0000;
    font-weight: 700;
    text-decoration: none;
}
.auth-switch-link:hover { text-decoration: underline; }

/* ── Demo box (Science Fair) ─────────────────────────────── */
.demo-box {
    margin-top: 1.5rem;
    background: #FFFBEB;
    border: 1px dashed #FCD34D;
    border-radius: 10px;
    padding: 0.85rem 1rem;
}

.demo-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #92400E;
    margin: 0 0 0.5rem;
}

.demo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.demo-role {
    font-size: 0.72rem;
    font-weight: 700;
    color: #BC0000;
    margin: 0 0 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.demo-cred {
    font-size: 0.72rem;
    color: #374151;
    margin: 0;
    font-family: monospace;
}
</style>