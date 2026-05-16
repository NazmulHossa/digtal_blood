<script setup>
/**
 * Auth/Register.vue
 * ──────────────────
 * Laravel Breeze-এর default Register page replace করা হয়েছে।
 *
 * Submit হলে → RegisteredUserController@store
 *   → User তৈরি হয়
 *   → BadgeService::awardRegistration() → +10 pts + bronze badge
 *   → Login হয় → Dashboard redirect
 *
 * Breeze-এর route() helper ব্যবহার করা হয়েছে (Ziggy package)।
 */
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    name:                  '',
    email:                 '',
    phone:                 '',
    role:                  'donor',
    password:              '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Register — Digital Blood Connect" />

    <div class="auth-bg">

        <!-- Left Panel — Branding -->
        <div class="auth-left">
            <div class="auth-brand">
                <div style="font-size:3.5rem; margin-bottom:1rem;">🩸</div>
                <h1 class="auth-brand-title">
                    Join the Community.<br/>Save Lives.
                </h1>
                <p class="auth-brand-sub">
                    Thousands of patients in Chittagong need blood every day.
                    Your registration could save a life.
                </p>

                <!-- Badge preview -->
                <div class="badge-preview">
                    <p class="badge-preview-title">🎯 Start earning badges today!</p>
                    <div class="badge-list">
                        <div v-for="b in [
                            { icon:'🥉', name:'Bronze', pts:'0 pts' },
                            { icon:'🥈', name:'Silver', pts:'100 pts' },
                            { icon:'🥇', name:'Gold',   pts:'300 pts' },
                            { icon:'🦸', name:'Hero',   pts:'600 pts' },
                        ]" :key="b.name" class="badge-item">
                            <span style="font-size:1.4rem;">{{ b.icon }}</span>
                            <div>
                                <div class="badge-item-name">{{ b.name }}</div>
                                <div class="badge-item-pts">{{ b.pts }}</div>
                            </div>
                        </div>
                    </div>
                    <p class="badge-welcome-note">
                        🎁 Joining today gives you <strong>+10 Welcome Points</strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel — Register Form -->
        <div class="auth-right">
            <div class="auth-card">

                <!-- Mobile logo -->
                <div class="auth-mobile-logo">
                    <span style="font-size:1.75rem;">🩸</span>
                    <span class="auth-mobile-logo-text">Digital Blood Connect</span>
                </div>

                <h2 class="auth-card-title">Create Your Account</h2>
                <p class="auth-card-sub">Register as a donor or recipient — it's free</p>

                <!-- ── Role Selector ──────────────────────────── -->
                <div class="role-grid">
                    <label
                        v-for="r in [
                            { value:'donor',     icon:'🩸', title:'Blood Donor',     desc:'I want to donate' },
                            { value:'recipient', icon:'🏥', title:'Blood Recipient', desc:'I need to find blood' },
                        ]"
                        :key="r.value"
                        class="role-option"
                        :class="{ 'role-option-active': form.role === r.value }"
                    >
                        <input
                            v-model="form.role"
                            type="radio"
                            :value="r.value"
                            style="display:none;"
                        />
                        <span class="role-icon">{{ r.icon }}</span>
                        <div>
                            <div class="role-title"
                                 :class="{ 'role-title-active': form.role === r.value }">
                                {{ r.title }}
                            </div>
                            <div class="role-desc">{{ r.desc }}</div>
                        </div>
                        <span v-if="form.role === r.value" class="role-check">✓</span>
                    </label>
                </div>

                <!-- ── Name ──────────────────────────────────── -->
                <div class="field">
                    <label class="field-label">Full Name <span class="required">*</span></label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            placeholder="Your full name"
                            class="field-input"
                            :class="{ 'field-input-err': form.errors.name }"
                            required
                            autofocus
                        />
                    </div>
                    <p v-if="form.errors.name" class="field-err">{{ form.errors.name }}</p>
                </div>

                <!-- ── Email ─────────────────────────────────── -->
                <div class="field">
                    <label class="field-label">Email Address <span class="required">*</span></label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
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
                        />
                    </div>
                    <p v-if="form.errors.email" class="field-err">{{ form.errors.email }}</p>
                </div>

                <!-- ── Phone ─────────────────────────────────── -->
                <div class="field">
                    <label class="field-label">
                        Phone Number
                        <span class="optional">(Optional — add later from dashboard)</span>
                    </label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                                         19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0
                                         0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2
                                         2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2
                                         2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22
                                         16.92z"/>
                            </svg>
                        </span>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="01XXXXXXXXX"
                            class="field-input"
                        />
                    </div>
                </div>

                <!-- ── Two-column: Password fields ────────────── -->
                <div class="two-col">

                    <!-- Password -->
                    <div class="field">
                        <label class="field-label">Password <span class="required">*</span></label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Min. 8 characters"
                                class="field-input"
                                :class="{ 'field-input-err': form.errors.password }"
                                required
                            />
                        </div>
                        <p v-if="form.errors.password" class="field-err">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="field">
                        <label class="field-label">Confirm Password <span class="required">*</span></label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Repeat password"
                                class="field-input"
                                :class="{ 'field-input-err': form.errors.password_confirmation }"
                                required
                            />
                        </div>
                        <p v-if="form.errors.password_confirmation" class="field-err">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>
                </div>

                <!-- Welcome points notice -->
                <div class="welcome-bonus">
                    🎁 You'll receive <strong>+10 Welcome Points</strong> and a
                    <strong>🥉 Bronze Donor Badge</strong> upon registration!
                </div>

                <!-- ── Submit ─────────────────────────────────── -->
                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="btn-submit"
                >
                    <span v-if="form.processing" class="spinner"></span>
                    {{ form.processing ? 'Creating Account...' : '🩸 Create Account' }}
                </button>

                <!-- Login link -->
                <p class="auth-switch">
                    Already have an account?
                    <Link :href="route('login')" class="auth-switch-link">
                        Login here →
                    </Link>
                </p>

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

.auth-left {
    display: none;
    background: linear-gradient(160deg, #8B0000 0%, #BC0000 55%, #D10000 100%);
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
    top: -80px; right: -80px;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}

@media (min-width: 1024px) {
    .auth-left { display: flex; }
}

.auth-brand { position: relative; z-index: 1; }

.auth-brand-title {
    font-size: 2rem;
    font-weight: 800;
    color: white;
    line-height: 1.25;
    margin: 0 0 0.85rem;
    font-family: 'Georgia', serif;
}

.auth-brand-sub {
    color: rgba(255,255,255,0.8);
    font-size: 0.95rem;
    line-height: 1.65;
    margin: 0 0 2rem;
}

/* Badge preview on left panel */
.badge-preview {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 1.1rem;
}

.badge-preview-title {
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    margin: 0 0 0.75rem;
}

.badge-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.badge-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    padding: 0.5rem 0.65rem;
}

.badge-item-name {
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
}

.badge-item-pts {
    color: rgba(255,255,255,0.65);
    font-size: 0.7rem;
}

.badge-welcome-note {
    color: #FFD700;
    font-size: 0.8rem;
    margin: 0;
    text-align: center;
}

/* ── Right panel ─────────────────────────────────────────── */
.auth-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.auth-card {
    background: white;
    border-radius: 16px;
    padding: 2rem 2rem 1.75rem;
    width: 100%;
    max-width: 460px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    border: 1px solid #E5E7EB;
}

.auth-mobile-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

.auth-mobile-logo-text {
    font-size: 1rem;
    font-weight: 800;
    color: #BC0000;
    font-family: 'Georgia', serif;
}

@media (min-width: 1024px) {
    .auth-mobile-logo { display: none; }
}

.auth-card-title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0 0 0.2rem;
    font-family: 'Georgia', serif;
}

.auth-card-sub {
    font-size: 0.85rem;
    color: #6B7280;
    margin: 0 0 1.25rem;
}

/* ── Role Selector ───────────────────────────────────────── */
.role-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
    margin-bottom: 1.1rem;
}

.role-option {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 2px solid #E5E7EB;
    border-radius: 10px;
    padding: 0.75rem 0.85rem;
    cursor: pointer;
    background: white;
    transition: border-color 0.2s, background 0.2s;
}

.role-option-active {
    border-color: #BC0000;
    background: #FEF2F2;
}

.role-icon { font-size: 1.3rem; }

.role-title {
    font-weight: 700;
    font-size: 0.82rem;
    color: #374151;
}

.role-title-active { color: #BC0000; }

.role-desc {
    font-size: 0.7rem;
    color: #9CA3AF;
    margin-top: 1px;
}

.role-check {
    position: absolute;
    top: 6px;
    right: 8px;
    font-size: 0.75rem;
    font-weight: 800;
    color: #BC0000;
}

/* ── Fields ──────────────────────────────────────────────── */
.field { margin-bottom: 0.9rem; }

.field-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.required { color: #BC0000; }

.optional {
    font-weight: 400;
    color: #9CA3AF;
    font-size: 0.72rem;
    margin-left: 4px;
}

.field-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.field-icon {
    position: absolute;
    left: 10px;
    color: #9CA3AF;
    display: flex;
    align-items: center;
    pointer-events: none;
}

.field-input {
    width: 100%;
    padding: 0.65rem 0.75rem 0.65rem 2.25rem;
    border: 1.5px solid #D1D5DB;
    border-radius: 8px;
    font-size: 0.875rem;
    outline: none;
    box-sizing: border-box;
    color: #1a1a1a;
    font-family: sans-serif;
    transition: border-color 0.2s, box-shadow 0.2s;
    background: white;
}

.field-input:focus {
    border-color: #BC0000;
    box-shadow: 0 0 0 3px rgba(188, 0, 0, 0.09);
}

.field-input-err { border-color: #BC0000 !important; }

.field-err {
    color: #BC0000;
    font-size: 0.73rem;
    margin: 3px 0 0;
}

/* Two column grid for passwords */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

/* ── Welcome bonus notice ────────────────────────────────── */
.welcome-bonus {
    background: #FEF3C7;
    border: 1px solid #FDE68A;
    border-radius: 8px;
    padding: 0.65rem 0.85rem;
    font-size: 0.78rem;
    color: #92400E;
    margin-bottom: 1.1rem;
    line-height: 1.5;
}

/* ── Submit button ───────────────────────────────────────── */
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
    margin-bottom: 1rem;
    font-family: sans-serif;
}

.btn-submit:hover:not(:disabled) {
    background: #8B0000;
    transform: translateY(-1px);
}

.btn-submit:disabled {
    opacity: 0.72;
    cursor: not-allowed;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Auth switch */
.auth-switch {
    text-align: center;
    font-size: 0.85rem;
    color: #6B7280;
    margin: 0;
}

.auth-switch-link {
    color: #BC0000;
    font-weight: 700;
    text-decoration: none;
}

.auth-switch-link:hover { text-decoration: underline; }
</style>