<script setup>
/**
 * Auth/ConfirmPassword.vue
 * ─────────────────────────
 * Sensitive action-এর আগে password confirm করার page।
 * e.g., payment, account deletion-এর আগে।
 */
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({ password: '' })
const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Confirm Password — Digital Blood Connect" />

    <div class="auth-bg">
        <div class="auth-card">

            <div class="logo-wrap">
                <span style="font-size:2.5rem;">🔐</span>
                <h1 class="logo-title">Security Check</h1>
            </div>

            <p class="card-sub">
                This is a secure area. Please confirm your password before continuing.
            </p>

            <div class="field">
                <label class="field-label">Your Password</label>
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
                        placeholder="Enter your password"
                        class="field-input"
                        :class="{ 'field-input-err': form.errors.password }"
                        required autofocus
                    />
                </div>
                <p v-if="form.errors.password" class="field-err">{{ form.errors.password }}</p>
            </div>

            <button @click="submit" :disabled="form.processing" class="btn-submit">
                <span v-if="form.processing" class="spinner"></span>
                {{ form.processing ? 'Confirming...' : '🔓 Confirm Password' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.auth-bg {
    min-height: 100vh; background: linear-gradient(135deg, #8B0000, #BC0000);
    display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.auth-card {
    background: white; border-radius: 16px; padding: 2.5rem;
    width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.logo-wrap { text-align: center; margin-bottom: 1.25rem; }
.logo-title { font-size: 1.2rem; font-weight: 800; color: #1a1a1a; margin: 0.25rem 0 0; font-family: 'Georgia', serif; }
.card-sub { font-size: 0.875rem; color: #6B7280; margin: 0 0 1.5rem; line-height: 1.6; }
.field { margin-bottom: 1.25rem; }
.field-label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.field-wrap { position: relative; display: flex; align-items: center; }
.field-icon { position: absolute; left: 10px; color: #9CA3AF; display: flex; align-items: center; pointer-events: none; }
.field-input {
    width: 100%; padding: 0.7rem 0.75rem 0.7rem 2.25rem; border: 1.5px solid #D1D5DB;
    border-radius: 8px; font-size: 0.9rem; outline: none; box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.field-input:focus { border-color: #BC0000; box-shadow: 0 0 0 3px rgba(188,0,0,0.1); }
.field-input-err { border-color: #BC0000 !important; }
.field-err { color: #BC0000; font-size: 0.75rem; margin-top: 3px; }
.btn-submit {
    width: 100%; padding: 0.85rem; background: #BC0000; color: white; border: none;
    border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: background 0.2s; font-family: sans-serif;
}
.btn-submit:hover:not(:disabled) { background: #8B0000; }
.btn-submit:disabled { opacity: 0.72; cursor: not-allowed; }
.spinner {
    width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.4);
    border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>