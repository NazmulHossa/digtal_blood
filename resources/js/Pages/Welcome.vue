<script setup>
/**
 * ============================================================
 * Welcome.vue — Landing Page / Home
 * ============================================================
 * This is the PUBLIC landing page for Digital Blood Connect.
 *
 * SECTIONS:
 *  1. Navigation Bar
 *  2. Hero Section — Main headline + CTA buttons
 *  3. Live Stats Bar — Total donors, open requests, etc.
 *  4. Emergency Requests Feed — Urgent blood needs
 *  5. How It Works — 3-step process explanation
 *  6. Footer
 *
 * VUE CONCEPT: defineProps
 * ─────────────────────────
 * Inertia passes data from the Laravel controller as Vue props.
 * We declare them with `defineProps` so Vue knows what to expect.
 * No separate API call needed — data arrives with the page load!
 * ============================================================
 */

import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// ── Props (data from Laravel Controller) ─────────────────────
// These are passed via Inertia::render('Welcome', [...])
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

// ── Reactive State ────────────────────────────────────────────
// Controls the mobile nav menu open/close state
const mobileMenuOpen = ref(false)

// ── Helper: time since request was posted ────────────────────
const timeAgo = (dateString) => {
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000)
  if (seconds < 60) return 'Just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`
  return `${Math.floor(seconds / 86400)}d ago`
}
</script>

<template>
  <!-- Head: Sets the browser tab title and meta description -->
  <Head title="Digital Blood Connect — Save Lives in Chittagong" />

  <div class="min-h-screen" style="font-family: 'Georgia', 'Times New Roman', serif; background: #FAFAFA;">

    <!-- ══════════════════════════════════════════════════════
         NAVIGATION BAR
         ══════════════════════════════════════════════════════ -->
    <nav style="background: #BC0000; box-shadow: 0 2px 20px rgba(188,0,0,0.4);" class="sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

          <!-- Logo & Brand Name -->
          <Link href="/" class="flex items-center gap-3 no-underline">
            <!-- Droplet SVG Icon -->
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
          <div class="hidden md:flex items-center gap-6">
            <Link href="/donors"
                  style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem;"
                  class="hover:text-white transition-colors">
              Find Donors
            </Link>
            <Link href="/requests"
                  style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem;"
                  class="hover:text-white transition-colors">
              Blood Requests
            </Link>

            <!-- Auth Buttons -->
            <Link v-if="$page.props.auth.user" href="/dashboard"
                  style="background: white; color: #BC0000; padding: 0.5rem 1.25rem;
                         border-radius: 6px; text-decoration: none; font-weight: 600;
                         font-size: 0.9rem;">
              My Dashboard
            </Link>
            <template v-else>
              <Link href="/login"
                    style="color: rgba(255,255,255,0.9); text-decoration: none; font-size: 0.95rem;">
                Login
              </Link>
              <Link href="/register"
                    style="background: white; color: #BC0000; padding: 0.5rem 1.25rem;
                           border-radius: 6px; text-decoration: none; font-weight: 600;
                           font-size: 0.9rem;">
                Register as Donor
              </Link>
            </template>
          </div>

          <!-- Mobile menu toggle button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen"
                  class="md:hidden text-white p-2">
            <svg v-if="!mobileMenuOpen" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg v-else width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div v-show="mobileMenuOpen" class="md:hidden pb-4 border-t border-red-700 pt-3">
          <div class="flex flex-col gap-3">
            <Link href="/donors" style="color:white; text-decoration:none; padding: 0.5rem 0;">Find Donors</Link>
            <Link href="/requests" style="color:white; text-decoration:none; padding: 0.5rem 0;">Blood Requests</Link>
            <Link href="/register"
                  style="background:white; color:#BC0000; text-align:center; padding:0.6rem;
                         border-radius:6px; text-decoration:none; font-weight:600;">
              Register as Donor
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- ══════════════════════════════════════════════════════
         HERO SECTION
         The most important section — first impression!
         Uses a rich blood-red gradient background.
         ══════════════════════════════════════════════════════ -->
    <section style="background: linear-gradient(135deg, #8B0000 0%, #BC0000 50%, #D10000 100%);
                    min-height: 85vh; display: flex; align-items: center;
                    position: relative; overflow: hidden;">

      <!-- Decorative background circles for depth -->
      <div style="position:absolute; top:-100px; right:-100px; width:400px; height:400px;
                  border-radius:50%; background:rgba(255,255,255,0.05);"></div>
      <div style="position:absolute; bottom:-150px; left:-80px; width:350px; height:350px;
                  border-radius:50%; background:rgba(0,0,0,0.1);"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

          <!-- Left: Headline & CTA -->
          <div>
            <!-- Badge -->
            <div style="display:inline-flex; align-items:center; gap:8px;
                        background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3);
                        border-radius:999px; padding:0.4rem 1rem; margin-bottom:1.5rem;">
              <span style="width:8px; height:8px; background:#90EE90; border-radius:50%;
                           display:inline-block; animation: pulse 2s infinite;"></span>
              <span style="color:white; font-size:0.85rem; font-weight:500;">
                Live — Chittagong & Sitakunda Region
              </span>
            </div>

            <!-- Main Headline -->
            <h1 style="color: white; font-size: clamp(2.5rem, 5vw, 4rem);
                       font-weight: 700; line-height: 1.15; margin-bottom: 1.5rem;
                       font-family: 'Georgia', serif;">
              Every Drop<br/>
              <span style="color: #FFD700;">Saves a Life.</span>
            </h1>

            <!-- Subtitle -->
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
              <!-- PRIMARY: Emergency Request Button -->
              <Link href="/requests/create"
                    style="background: white; color: #BC0000; padding: 0.9rem 2rem;
                           border-radius: 8px; font-weight: 700; font-size: 1rem;
                           text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
                           box-shadow: 0 4px 20px rgba(0,0,0,0.2); transition: transform 0.2s;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#BC0000">
                  <path d="M12 2L13.5 8.5H20L14.5 12.5L16.5 19L12 15L7.5 19L9.5 12.5L4 8.5H10.5L12 2Z"/>
                </svg>
                Request Blood Now
              </Link>

              <!-- SECONDARY: Find Donors -->
              <Link href="/donors"
                    style="background: transparent; color: white; padding: 0.9rem 2rem;
                           border: 2px solid rgba(255,255,255,0.7); border-radius: 8px;
                           font-weight: 600; font-size: 1rem; text-decoration: none;
                           display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                Search Donors
              </Link>
            </div>

            <!-- Trust indicators -->
            <p style="color:rgba(255,255,255,0.6); font-size:0.8rem; margin-top:1.5rem; font-family:sans-serif;">
              🏥 Connected to CMCH, Sitakunda Upazila Health Complex & more
            </p>
          </div>

          <!-- Right: Stats Cards -->
          <div class="hidden lg:grid grid-cols-2 gap-4">
            <!-- Stat Card 1 -->
            <div style="background:rgba(255,255,255,0.12); backdrop-filter:blur(10px);
                        border:1px solid rgba(255,255,255,0.2); border-radius:12px; padding:1.5rem;
                        text-align:center;">
              <div style="font-size:2.5rem; font-weight:800; color:white;">{{ stats.total_donors }}</div>
              <div style="color:rgba(255,255,255,0.75); font-size:0.875rem; font-family:sans-serif; margin-top:4px;">
                Registered Donors
              </div>
            </div>
            <!-- Stat Card 2 -->
            <div style="background:rgba(255,255,255,0.12); backdrop-filter:blur(10px);
                        border:1px solid rgba(255,255,255,0.2); border-radius:12px; padding:1.5rem;
                        text-align:center;">
              <div style="font-size:2.5rem; font-weight:800; color:#FFD700;">{{ stats.open_requests }}</div>
              <div style="color:rgba(255,255,255,0.75); font-size:0.875rem; font-family:sans-serif; margin-top:4px;">
                Open Requests
              </div>
            </div>
            <!-- Stat Card 3 -->
            <div style="background:rgba(255,255,255,0.12); backdrop-filter:blur(10px);
                        border:1px solid rgba(255,255,255,0.2); border-radius:12px; padding:1.5rem;
                        text-align:center;">
              <div style="font-size:2.5rem; font-weight:800; color:white;">{{ stats.districts }}</div>
              <div style="color:rgba(255,255,255,0.75); font-size:0.875rem; font-family:sans-serif; margin-top:4px;">
                Districts Covered
              </div>
            </div>
            <!-- Stat Card 4: Blood types -->
            <div style="background:rgba(255,255,255,0.12); backdrop-filter:blur(10px);
                        border:1px solid rgba(255,255,255,0.2); border-radius:12px; padding:1.5rem;
                        text-align:center;">
              <div style="font-size:2.5rem; font-weight:800; color:#90EE90;">8</div>
              <div style="color:rgba(255,255,255,0.75); font-size:0.875rem; font-family:sans-serif; margin-top:4px;">
                Blood Groups
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         URGENT REQUESTS SECTION
         Shows real-time emergency blood requests
         ══════════════════════════════════════════════════════ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="flex items-center justify-between mb-8">
        <div>
          <div style="display:flex; align-items:center; gap:10px;">
            <span style="width:10px; height:10px; background:#BC0000; border-radius:50%;
                         display:inline-block; animation:pulse 1.5s infinite;"></span>
            <h2 style="font-size:1.75rem; font-weight:700; color:#1a1a1a; margin:0;">
              Emergency Requests
            </h2>
          </div>
          <p style="color:#666; margin-top:4px; font-family:sans-serif; font-size:0.9rem;">
            Live blood requests from patients in Chittagong region
          </p>
        </div>
        <Link href="/requests"
              style="color:#BC0000; text-decoration:none; font-weight:600; font-size:0.9rem;">
          View All →
        </Link>
      </div>

      <!-- If there are urgent requests, show them -->
      <div v-if="urgentRequests.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="req in urgentRequests" :key="req.id"
             style="background:white; border:1px solid #e5e7eb; border-radius:12px;
                    padding:1.25rem; position:relative; overflow:hidden;
                    box-shadow:0 2px 8px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s;"
             class="hover:shadow-lg hover:-translate-y-0.5">

          <!-- Urgent badge (only for urgent requests) -->
          <div v-if="req.is_urgent"
               style="position:absolute; top:0; right:0; background:#BC0000; color:white;
                      font-size:0.7rem; font-weight:700; padding:0.25rem 0.75rem;
                      border-bottom-left-radius:8px; letter-spacing:0.5px;">
            URGENT
          </div>

          <!-- Blood group badge -->
          <div style="display:flex; align-items:center; gap:12px; margin-bottom:1rem;">
            <div style="width:52px; height:52px; background:#BC0000; border-radius:10px;
                        display:flex; align-items:center; justify-content:center;
                        color:white; font-weight:800; font-size:1.1rem; flex-shrink:0;">
              {{ req.blood_group?.name }}
            </div>
            <div>
              <div style="font-weight:600; color:#1a1a1a; font-size:0.95rem;">
                {{ req.hospital_name || 'Location not specified' }}
              </div>
              <div style="color:#666; font-size:0.8rem; font-family:sans-serif; margin-top:2px;">
                {{ req.district?.name }}
                <span v-if="req.upazila"> · {{ req.upazila.name }}</span>
              </div>
            </div>
          </div>

          <!-- Details -->
          <div style="font-family:sans-serif; font-size:0.85rem; color:#555; margin-bottom:1rem;
                      display:flex; flex-direction:column; gap:6px;">
            <div style="display:flex; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#BC0000" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <span>{{ req.units_needed }} unit(s) needed</span>
            </div>
            <div v-if="req.contact_phone" style="display:flex; align-items:center; gap:6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#BC0000" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6"/>
              </svg>
              <a :href="'tel:' + req.contact_phone"
                 style="color:#BC0000; text-decoration:none; font-weight:600;">
                {{ req.contact_phone }}
              </a>
            </div>
          </div>

          <!-- Posted time -->
          <div style="font-size:0.75rem; color:#999; font-family:sans-serif;">
            Posted {{ timeAgo(req.created_at) }}
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else
           style="text-align:center; padding:3rem; background:white;
                  border-radius:12px; border:2px dashed #e5e7eb;">
        <div style="font-size:3rem; margin-bottom:0.5rem;">🩸</div>
        <p style="color:#666; font-family:sans-serif;">No urgent requests at the moment. Thank you!</p>
      </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         HOW IT WORKS SECTION
         ══════════════════════════════════════════════════════ -->
    <section style="background:#F3F4F6; padding:4rem 0;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 style="text-align:center; font-size:1.75rem; font-weight:700; color:#1a1a1a; margin-bottom:3rem;">
          How It Works
        </h2>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Step 1 -->
          <div style="text-align:center; padding:2rem;">
            <div style="width:64px; height:64px; background:#BC0000; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        margin:0 auto 1.25rem; color:white; font-size:1.5rem; font-weight:800;">
              1
            </div>
            <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin-bottom:0.5rem;">
              Register as Donor
            </h3>
            <p style="color:#666; font-size:0.9rem; line-height:1.6; font-family:sans-serif;">
              Create a free account with your blood type, district, and upazila. Takes less than 2 minutes.
            </p>
          </div>

          <!-- Step 2 -->
          <div style="text-align:center; padding:2rem;">
            <div style="width:64px; height:64px; background:#BC0000; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        margin:0 auto 1.25rem; color:white; font-size:1.5rem; font-weight:800;">
              2
            </div>
            <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin-bottom:0.5rem;">
              Search or Post Request
            </h3>
            <p style="color:#666; font-size:0.9rem; line-height:1.6; font-family:sans-serif;">
              Search donors by blood group & area. Or post an emergency request to alert the community instantly.
            </p>
          </div>

          <!-- Step 3 -->
          <div style="text-align:center; padding:2rem;">
            <div style="width:64px; height:64px; background:#BC0000; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        margin:0 auto 1.25rem; color:white; font-size:1.5rem; font-weight:800;">
              3
            </div>
            <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin-bottom:0.5rem;">
              Save a Life
            </h3>
            <p style="color:#666; font-size:0.9rem; line-height:1.6; font-family:sans-serif;">
              Connect directly with donors. Update your availability after each donation. Be a hero.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ══════════════════════════════════════════════════════
         FOOTER
         ══════════════════════════════════════════════════════ -->
    <footer style="background:#1a1a1a; color:rgba(255,255,255,0.7); padding:3rem 0;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 pb-8"
             style="border-bottom:1px solid rgba(255,255,255,0.1);">
          <div>
            <div style="color:white; font-size:1.1rem; font-weight:700; margin-bottom:0.75rem;">
              🩸 Digital Blood Connect
            </div>
            <p style="font-size:0.85rem; line-height:1.6; font-family:sans-serif;">
              A Science Fair project by students of Govt City College, Chattogram.
              Built to save lives in our community.
            </p>
          </div>
          <div>
            <div style="color:white; font-weight:600; margin-bottom:0.75rem; font-size:0.9rem;">
              Quick Links
            </div>
            <div style="display:flex; flex-direction:column; gap:8px; font-family:sans-serif; font-size:0.85rem;">
              <Link href="/donors" style="color:rgba(255,255,255,0.7); text-decoration:none;">Find Donors</Link>
              <Link href="/requests" style="color:rgba(255,255,255,0.7); text-decoration:none;">Blood Requests</Link>
              <Link href="/register" style="color:rgba(255,255,255,0.7); text-decoration:none;">Register as Donor</Link>
            </div>
          </div>
          <div>
            <div style="color:white; font-weight:600; margin-bottom:0.75rem; font-size:0.9rem;">
              Emergency Contact
            </div>
            <div style="font-family:sans-serif; font-size:0.85rem; line-height:1.8;">
              <div>CMCH Emergency: 031-630600</div>
              <div>Blood Bank Hotline: 16000</div>
              <div>Chittagong, Bangladesh</div>
            </div>
          </div>
        </div>
        <div style="text-align:center; padding-top:1.5rem;
                    font-size:0.8rem; font-family:sans-serif;">
          © 2025 Digital Blood Connect — Govt City College Science Fair, Chattogram
        </div>
      </div>
    </footer>

  </div>
</template>

<style>
/* Pulse animation for live indicators */
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.15); }
}
</style>