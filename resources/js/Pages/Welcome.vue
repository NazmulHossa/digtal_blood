<script setup>
/**
 * ============================================================
 * Welcome.vue — Landing Page / Home (FIXED)
 * ============================================================
 * All buttons now properly use Inertia Link with correct routing
 * ============================================================
 */

import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  urgentRequests: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  }
})

const mobileMenuOpen = ref(false)

const timeAgo = (dateString) => {
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000)
  if (seconds < 60) return 'Just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  return `${Math.floor(seconds / 86400)}d ago`
}
</script>

<template>
  <Head title="Digital Blood Connect — Save Lives in Chittagong" />

  <div class="min-h-screen" style="font-family: 'Georgia', 'Times New Roman', serif; background: #FAFAFA;">

    <!-- ══════════════════════════════════════════════════════
         NAVIGATION BAR (FIXED)
         ══════════════════════════════════════════════════════ -->
    <nav style="background: #BC0000; box-shadow: 0 2px 20px rgba(188,0,0,0.4);" class="sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

          <!-- Logo & Brand Name -->
          <Link href="/" class="flex items-center gap-3 no-underline hover:opacity-90 transition">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
              <path d="M16 3C16 3 6 14 6 20C6 25.52 10.48 30 16 30C21.52 30 26 25.52 26 20C26 14 16 3 16 3Z"
                    fill="white" opacity="0.9"/>
              <path d="M12 20C12 22.21 13.79 24 16 24" stroke="#BC0000" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span style="color: white; font-size: 1.25rem; font-weight: 700; letter-spacing: 0.5px;">
              Digital Blood Connect
            </span>
          </Link>

          <!-- Desktop Navigation Links -->
          <div class="hidden md:flex items-center gap-8">
            <!-- Find Donors Button -->
            <Link 
              href="/donors"
              as="button"
              method="get"
              style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem; background: none; border: none; cursor: pointer; padding: 0;"
              class="hover:text-white transition-colors duration-200">
              Find Donors
            </Link>

            <!-- Blood Requests Button -->
            <Link 
              href="/requests"
              as="button"
              method="get"
              style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem; background: none; border: none; cursor: pointer; padding: 0;"
              class="hover:text-white transition-colors duration-200">
              Blood Requests
            </Link>

            <!-- Auth Buttons -->
            <template v-if="$page.props.auth.user">
              <Link 
                href="/dashboard"
                style="background: white; color: #BC0000; padding: 0.5rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-block; cursor: pointer;"
                class="hover:bg-gray-100 transition">
                My Dashboard
              </Link>
            </template>
            <template v-else>
              <!-- Login Button -->
              <Link 
                href="/login"
                style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem; background: none; border: none; cursor: pointer; padding: 0;"
                class="hover:text-white transition-colors duration-200">
                Login
              </Link>

              <!-- Register Button (Primary CTA) -->
              <Link 
                href="/register"
                style="background: white; color: #BC0000; padding: 0.5rem 1.25rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-block; cursor: pointer;"
                class="hover:bg-gray-100 transition">
                Register as Donor
              </Link>
            </template>
          </div>

          <!-- Mobile Menu Button -->
          <button 
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden text-white p-2 hover:bg-red-700 rounded transition"
            aria-label="Toggle menu">
            <svg v-if="!mobileMenuOpen" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg v-else width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Mobile Menu Dropdown (FIXED) -->
        <div v-show="mobileMenuOpen" class="md:hidden pb-4 border-t border-red-700 pt-4">
          <div class="flex flex-col gap-4">
            <Link 
              href="/donors"
              style="color: white; text-decoration: none; padding: 0.5rem 0; display: block; cursor: pointer;"
              class="hover:text-gray-200 transition">
              Find Donors
            </Link>

            <Link 
              href="/requests"
              style="color: white; text-decoration: none; padding: 0.5rem 0; display: block; cursor: pointer;"
              class="hover:text-gray-200 transition">
              Blood Requests
            </Link>

            <Link v-if="$page.props.auth.user"
              href="/dashboard"
              style="background: white; color: #BC0000; text-align: center; padding: 0.6rem; border-radius: 6px; text-decoration: none; font-weight: 600; display: block; cursor: pointer;"
              class="hover:bg-gray-100 transition">
              My Dashboard
            </Link>

            <template v-else>
              <Link 
                href="/login"
                style="color: white; text-decoration: none; padding: 0.5rem 0; display: block; cursor: pointer;"
                class="hover:text-gray-200 transition">
                Login
              </Link>

              <Link 
                href="/register"
                style="background: white; color: #BC0000; text-align: center; padding: 0.6rem; border-radius: 6px; text-decoration: none; font-weight: 600; display: block; cursor: pointer;"
                class="hover:bg-gray-100 transition">
                Register as Donor
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- ══════════════════════════════════════════════════════
         HERO SECTION
         ══════════════════════════════════════════════════════ -->
    <section style="background: linear-gradient(135deg, #8B0000 0%, #BC0000 50%, #D10000 100%);
                    min-height: 85vh; display: flex; align-items: center;
                    position: relative; overflow: hidden;">

      <div style="position:absolute; top:-100px; right:-100px; width:400px; height:400px;
                  border-radius:50%; background:rgba(255,255,255,0.05);"></div>
      <div style="position:absolute; bottom:-150px; left:-80px; width:350px; height:350px;
                  border-radius:50%; background:rgba(0,0,0,0.1);"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

          <!-- Left: Headline & CTA -->
          <div>
            <div style="display:inline-flex; align-items:center; gap:8px;
                        background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3);
                        border-radius:999px; padding:0.4rem 1rem; margin-bottom:1.5rem;">
              <span style="width:8px; height:8px; background:#90EE90; border-radius:50%;
                           display:inline-block; animation: pulse 2s infinite;"></span>
              <span style="color:white; font-size:0.85rem; font-weight:500;">
                Live — Chittagong & Sitakunda Region
              </span>
            </div>

            <h1 style="color: white; font-size: clamp(2.5rem, 5vw, 4rem);
                       font-weight: 700; line-height: 1.15; margin-bottom: 1.5rem;
                       font-family: 'Georgia', serif;">
              Every Drop<br/>
              <span style="color: #FFD700;">Saves a Life.</span>
            </h1>

            <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem;
                      line-height: 1.7; margin-bottom: 2.5rem; max-width: 500px;
                      font-family: sans-serif;">
              Connect with registered blood donors across
              <strong style="color:white;">Chittagong</strong> and
              <strong style="color:white;">Sitakunda</strong> instantly.
              In an emergency, every second counts.
            </p>

            <!-- CTA Buttons -->
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
              <Link 
                href="/requests"
                style="background: white; color: #BC0000; padding: 0.9rem 2rem;
                       border-radius: 8px; font-weight: 700; font-size: 1rem;
                       text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
                       box-shadow: 0 4px 20px rgba(0,0,0,0.2); cursor: pointer;"
                class="hover:bg-gray-100 transition">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#BC0000">
                  <path d="M12 2L13.5 8.5H20L14.5 12.5L16.5 19L12 15L7.5 19L9.5 12.5L4 8.5H10.5L12 2Z"/>
                </svg>
                Request Blood Now
              </Link>

              <Link 
                href="/donors"
                style="background: rgba(255,255,255,0.2); color: white; padding: 0.9rem 2rem;
                       border-radius: 8px; font-weight: 700; font-size: 1rem;
                       text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
                       border: 2px solid white; cursor: pointer;"
                class="hover:bg-rgba(255,255,255,0.3) transition">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Find a Donor
              </Link>
            </div>
          </div>

          <!-- Right: Illustration -->
          <div style="text-align: center;">
            <svg width="100%" height="auto" viewBox="0 0 400 500" fill="none">
              <!-- Blood drop animation -->
              <circle cx="200" cy="150" r="80" fill="rgba(255,255,255,0.1)" style="animation: pulse 3s infinite;"/>
              <path d="M200 80 L210 140 Q200 160 190 140 Z" fill="white" opacity="0.9"/>
              <circle cx="150" cy="300" r="60" fill="rgba(255,255,255,0.08)"/>
              <circle cx="250" cy="320" r="50" fill="rgba(255,255,255,0.08)"/>
            </svg>
          </div>
        </div>
      </div>
    </section>

    <!-- Live Stats Section -->
    <section style="background: white; padding: 3rem 0; border-top: 1px solid #EEE;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 text-center">
          <div>
            <div style="font-size: 2.5rem; font-weight: 700; color: #BC0000;">
              {{ stats.total_donors || 0 }}
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Active Blood Donors</div>
          </div>
          <div>
            <div style="font-size: 2.5rem; font-weight: 700; color: #BC0000;">
              {{ stats.open_requests || 0 }}
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Open Blood Requests</div>
          </div>
          <div>
            <div style="font-size: 2.5rem; font-weight: 700; color: #BC0000;">
              {{ stats.districts || 0 }}
            </div>
            <div style="color: #666; margin-top: 0.5rem;">Districts Covered</div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<style scoped>
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}
</style>
