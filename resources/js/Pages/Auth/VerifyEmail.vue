<script setup>
/**
 * Auth/VerifyEmail.vue
 * ─────────────────────
 * Email verification prompt page।
 * Registration-এর পর email verify করতে বলা হয়।
 *
 * Breeze props:
 *   status → 'verification-link-sent' (resend success)
 */
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ status: String })

const form = useForm({})
const submit = () => form.post(route('verification.send'))

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent'
)
</script>

<template>
    <Head title="Email Verification — Digital Blood Connect" />

    <div class="auth-bg">
        <div class="auth-card">

            <div class="logo-wrap">
                <span style="font-size:2.5rem;">🩸</span>
                <h1 class="logo-title">Digital Blood Connect</h1>
            </div>

            <!-- Email icon -->
            <div style="text-align:center; margin-bottom:1.25rem;">
                <div style="width:70px; height:70px; background:#FEF2F2; border-radius:50%;
                            display:flex; align-items:center; justify-content:center;
                            margin:0 auto; font-size:2rem;">
                    📧
                </div>
            </div>

            <h2 class="card-title">Verify Your Email</h2>
            <p class="card-sub">
                Thanks for registering! Before getting started, please verify your email
                address by clicking the link we just sent to your inbox.
            </p>
            <p class="card-sub" style="margin-top:-0.5rem;">
                If you didn't receive the email, click below to request another.
            </p>

            <!-- Success resend notice -->
            <div v-if="verificationLinkSent" class="alert-success">
                ✅ A new verification link has been sent to your email address.
            </div>

            <!-- Resend button -->
            <button @click="submit" :disabled="form.processing" class="btn-submit">
                <span v-if="form.processing" class="spinner"></span>
                {{ form.processing ? 'Sending...' : '📧 Resend Verification Email' }}
            </button>

            <!-- Logout -->
            <div style="text-align:center; margin-top:1rem;">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    style="font-size:0.875rem; color:#6B7280; background:none;
                           border:none; cursor:pointer; text-decoration:underline;"
                >
                    Logout
                </Link>
            </div>
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
    width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.logo-wrap { text-align: center; margin-bottom: 1.25rem; }
.logo-title { font-size: 1.2rem; font-weight: 800; color: #BC0000; margin: 0.25rem 0 0; font-family: 'Georgia', serif; }
.card-title { font-size: 1.25rem; font-weight: 800; color: #1a1a1a; margin: 0 0 0.5rem; text-align: center; font-family: 'Georgia', serif; }
.card-sub { font-size: 0.875rem; color: #6B7280; margin: 0 0 0.75rem; line-height: 1.6; text-align: center; }
.alert-success {
    background: #DCFCE7; border: 1px solid #86EFAC; color: #166534;
    padding: 0.75rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1rem;
}
.btn-submit {
    width: 100%; padding: 0.85rem; background: #BC0000; color: white; border: none;
    border-radius: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer;
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