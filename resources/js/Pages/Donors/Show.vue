<script setup>
/**
 * Donors/Show.vue — একজন Donor-এর Public Profile
 * ─────────────────────────────────────────────────
 * Phone Masking system:
 *   - phone_masked = false → সরাসরি phone দেখায়
 *   - phone_masked = true  → "Request to Connect" button দেখায়
 *   - Connection accepted  → phone দেখায়
 */

import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  donor:            Object,
  connectionStatus: { type: String, default: 'none' },
  donorPhone:       { type: String, default: null },
  isOwnProfile:     { type: Boolean, default: false },
})

// ── Connect Request Form ──────────────────────────────────────
const connectForm = useForm({
  donor_id:         props.donor.id,
  blood_request_id: null,
  message:          '',
})

const sendConnectRequest = () => {
  connectForm.post('/connections', {
    preserveScroll: true,
  })
}

// ── Badge helpers ─────────────────────────────────────────────
const badgeConfig = computed(() => {
  const configs = {
    bronze: { icon: '🥉', label: 'Bronze Donor', color: '#CD7F32', bg: '#FDF3E7' },
    silver: { icon: '🥈', label: 'Silver Donor', color: '#9CA3AF', bg: '#F3F4F6' },
    gold:   { icon: '🥇', label: 'Gold Donor',   color: '#D97706', bg: '#FFFBEB' },
    hero:   { icon: '🦸', label: 'Community Hero', color: '#BC0000', bg: '#FEF2F2' },
  }
  return configs[props.donor.badge?.level] || configs.bronze
})

// ── Last donation eligibility ─────────────────────────────────
const eligibilityInfo = computed(() => {
  if (!props.donor.last_donation_date) {
    return { eligible: true, text: 'Eligible to donate now', color: '#166534', bg: '#DCFCE7' }
  }
  const last    = new Date(props.donor.last_donation_date)
  const daysSince = Math.floor((Date.now() - last.getTime()) / 86400000)
  const daysLeft  = Math.max(0, 90 - daysSince)

  if (daysLeft === 0) {
    return { eligible: true,  text: 'Eligible to donate now', color: '#166534', bg: '#DCFCE7' }
  }
  return {
    eligible: false,
    text: `Eligible after ${daysLeft} more day${daysLeft > 1 ? 's' : ''}`,
    color: '#92400E',
    bg: '#FFFBEB',
  }
})

// ── Format date ───────────────────────────────────────────────
const formatDate = (d) => {
  if (!d) return 'Never donated'
  return new Date(d).toLocaleDateString('en-BD', {
    year: 'numeric', month: 'long', day: 'numeric',
  })
}
</script>

<template>
  <Head :title="`${donor.name} — Blood Donor Profile`" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- ══════════════════════════════════════════════════════
         NAVIGATION
         ══════════════════════════════════════════════════════ -->
    <nav style="background:#BC0000; padding:1rem 0;
                box-shadow:0 2px 10px rgba(188,0,0,0.3);
                position:sticky; top:0; z-index:50;">
      <div style="max-width:900px; margin:0 auto; padding:0 1rem;
                  display:flex; align-items:center; justify-content:space-between;">
        <Link href="/"
              style="color:white; text-decoration:none; font-size:1.1rem;
                     font-weight:700; font-family:'Georgia',serif;">
          🩸 Digital Blood Connect
        </Link>
        <div style="display:flex; align-items:center; gap:1rem;">
          <Link href="/donors"
                style="color:rgba(255,255,255,0.85); text-decoration:none;
                       font-size:0.875rem;">
            ← All Donors
          </Link>
          <Link v-if="$page.props.auth.user"
                href="/dashboard"
                style="background:white; color:#BC0000; padding:0.4rem 1rem;
                       border-radius:6px; text-decoration:none;
                       font-weight:600; font-size:0.875rem;">
            Dashboard
          </Link>
        </div>
      </div>
    </nav>

    <div style="max-width:900px; margin:0 auto; padding:2rem 1rem;">

      <div style="display:grid; grid-template-columns:1fr; gap:1.5rem;">

        <!-- ══════════════════════════════════════════════════
             LEFT / TOP — Profile Card
             ══════════════════════════════════════════════════ -->
        <div style="display:grid; gap:1.5rem;"
             :style="'grid-template-columns: 1fr'">

          <!-- Profile Header Card -->
          <div style="background:white; border-radius:16px; overflow:hidden;
                      box-shadow:0 2px 12px rgba(0,0,0,0.08);
                      border:1px solid #E5E7EB;">

            <!-- Gradient Banner -->
            <div style="background:linear-gradient(135deg, #8B0000, #BC0000);
                        padding:2.5rem 2rem; text-align:center; position:relative;">

              <!-- Own profile badge -->
              <div v-if="isOwnProfile"
                   style="position:absolute; top:12px; right:12px;
                          background:rgba(255,255,255,0.2);
                          border:1px solid rgba(255,255,255,0.4);
                          color:white; padding:0.25rem 0.75rem;
                          border-radius:999px; font-size:0.75rem; font-weight:600;">
                Your Profile
              </div>

              <!-- Avatar -->
              <div style="width:90px; height:90px; background:rgba(255,255,255,0.2);
                          border-radius:50%; display:flex; align-items:center;
                          justify-content:center; font-size:2.5rem; font-weight:800;
                          color:white; margin:0 auto 1rem;
                          border:3px solid rgba(255,255,255,0.5);">
                {{ donor.name.charAt(0).toUpperCase() }}
              </div>

              <!-- Name -->
              <h1 style="color:white; font-size:1.5rem; font-weight:700;
                         margin:0 0 0.5rem; font-family:'Georgia',serif;">
                {{ donor.name }}
              </h1>

              <!-- Blood Group -->
              <span style="background:white; color:#BC0000; padding:0.3rem 1rem;
                           border-radius:999px; font-size:1rem; font-weight:800;
                           display:inline-block; margin-bottom:0.75rem;">
                🩸 {{ donor.blood_group?.name || 'Not set' }}
              </span>

              <!-- Availability -->
              <div>
                <span :style="donor.is_available
                       ? 'background:rgba(144,238,144,0.25); color:#90EE90; border-color:rgba(144,238,144,0.4);'
                       : 'background:rgba(255,100,100,0.2); color:#FFA0A0; border-color:rgba(255,100,100,0.3);'"
                      style="padding:0.3rem 1rem; border-radius:999px;
                             font-size:0.82rem; font-weight:700; border:1px solid;">
                  {{ donor.is_available ? '✓ Available to Donate' : '✗ Currently Unavailable' }}
                </span>
              </div>
            </div>

            <!-- Profile Details -->
            <div style="padding:1.5rem;">

              <!-- Info Grid -->
              <div style="display:grid; grid-template-columns:1fr 1fr;
                          gap:1rem; margin-bottom:1.5rem;">

                <!-- Location -->
                <div style="background:#F9FAFB; border-radius:10px; padding:1rem;">
                  <div style="font-size:0.72rem; font-weight:700; color:#9CA3AF;
                               text-transform:uppercase; letter-spacing:0.5px;
                               margin-bottom:0.35rem;">
                    📍 Location
                  </div>
                  <div style="font-size:0.9rem; font-weight:600; color:#1a1a1a;">
                    {{ donor.upazila?.name || '—' }}
                  </div>
                  <div style="font-size:0.78rem; color:#6B7280; margin-top:2px;">
                    {{ donor.district?.name || 'Location not set' }}
                  </div>
                </div>

                <!-- Last Donation -->
                <div style="background:#F9FAFB; border-radius:10px; padding:1rem;">
                  <div style="font-size:0.72rem; font-weight:700; color:#9CA3AF;
                               text-transform:uppercase; letter-spacing:0.5px;
                               margin-bottom:0.35rem;">
                    🗓️ Last Donation
                  </div>
                  <div style="font-size:0.85rem; font-weight:600; color:#1a1a1a;">
                    {{ formatDate(donor.last_donation_date) }}
                  </div>
                </div>

                <!-- Donations Count -->
                <div style="background:#F9FAFB; border-radius:10px; padding:1rem;">
                  <div style="font-size:0.72rem; font-weight:700; color:#9CA3AF;
                               text-transform:uppercase; letter-spacing:0.5px;
                               margin-bottom:0.35rem;">
                    💉 Total Donations
                  </div>
                  <div style="font-size:1.25rem; font-weight:800; color:#BC0000;">
                    {{ donor.badge?.donation_count || 0 }}
                  </div>
                </div>

                <!-- Points -->
                <div style="background:#F9FAFB; border-radius:10px; padding:1rem;">
                  <div style="font-size:0.72rem; font-weight:700; color:#9CA3AF;
                               text-transform:uppercase; letter-spacing:0.5px;
                               margin-bottom:0.35rem;">
                    ⭐ Points Earned
                  </div>
                  <div style="font-size:1.25rem; font-weight:800; color:#BC0000;">
                    {{ donor.badge?.total_points || 0 }}
                  </div>
                </div>
              </div>

              <!-- Eligibility Status -->
              <div :style="`background:${eligibilityInfo.bg}; color:${eligibilityInfo.color};`"
                   style="border-radius:10px; padding:0.85rem 1rem;
                          font-size:0.85rem; font-weight:600; margin-bottom:1.5rem;
                          display:flex; align-items:center; gap:8px;">
                <span>{{ eligibilityInfo.eligible ? '✅' : '⏳' }}</span>
                {{ eligibilityInfo.text }}
              </div>

              <!-- ══════════════════════════════════════════════
                   PHONE / CONNECT SECTION
                   ══════════════════════════════════════════════ -->

              <!-- Own Profile: phone সরাসরি দেখাও -->
              <div v-if="isOwnProfile">
                <div style="background:#F0FDF4; border:1.5px solid #86EFAC;
                            border-radius:10px; padding:1rem; text-align:center;">
                  <div style="font-size:0.75rem; font-weight:700; color:#166534;
                               margin-bottom:0.35rem; text-transform:uppercase;">
                    Your Phone Number
                  </div>
                  <div style="font-size:1.2rem; font-weight:700; color:#166534;">
                    {{ donorPhone || 'Not set — add from Dashboard' }}
                  </div>
                </div>
              </div>

              <!-- Phone directly visible (phone_masked = false + accepted) -->
              <div v-else-if="donorPhone">
                <a :href="`tel:${donorPhone}`"
                   style="display:flex; align-items:center; justify-content:center;
                          gap:10px; background:#BC0000; color:white;
                          padding:0.9rem; border-radius:10px; text-decoration:none;
                          font-weight:700; font-size:1rem; margin-bottom:0.75rem;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                       stroke="white" stroke-width="2.5">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                             19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0
                             0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2
                             2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2
                             2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22
                             16.92z"/>
                  </svg>
                  Call {{ donor.name }} — {{ donorPhone }}
                </a>
              </div>

              <!-- Not logged in -->
              <div v-else-if="!$page.props.auth.user">
                <div style="background:#F9FAFB; border:1.5px solid #E5E7EB;
                            border-radius:10px; padding:1.25rem; text-align:center;
                            margin-bottom:0.75rem;">
                  <div style="font-size:0.875rem; color:#6B7280; margin-bottom:0.75rem;">
                    🔒 Login to contact this donor
                  </div>
                  <div style="display:flex; gap:0.5rem; justify-content:center;">
                    <Link href="/login"
                          style="background:#BC0000; color:white; padding:0.6rem 1.25rem;
                                 border-radius:8px; text-decoration:none;
                                 font-weight:600; font-size:0.875rem;">
                      Login
                    </Link>
                    <Link href="/register"
                          style="background:#F3F4F6; color:#374151; padding:0.6rem 1.25rem;
                                 border-radius:8px; text-decoration:none;
                                 font-weight:600; font-size:0.875rem;
                                 border:1px solid #E5E7EB;">
                      Register
                    </Link>
                  </div>
                </div>
              </div>

              <!-- Already accepted -->
              <div v-else-if="connectionStatus === 'accepted'">
                <div style="background:#DCFCE7; border:1.5px solid #86EFAC;
                            border-radius:10px; padding:1rem; text-align:center;
                            margin-bottom:0.75rem;">
                  <div style="color:#166534; font-weight:700; font-size:0.875rem;">
                    ✅ Connection Accepted — phone is visible above
                  </div>
                </div>
              </div>

              <!-- Pending request -->
              <div v-else-if="connectionStatus === 'pending'">
                <div style="background:#FFFBEB; border:1.5px solid #FDE68A;
                            border-radius:10px; padding:1.25rem; text-align:center;">
                  <div style="font-size:1.5rem; margin-bottom:0.4rem;">⏳</div>
                  <div style="font-weight:700; color:#92400E; font-size:0.9rem;
                               margin-bottom:0.25rem;">
                    Request Pending
                  </div>
                  <div style="color:#A16207; font-size:0.8rem;">
                    Waiting for {{ donor.name }} to accept your connection request.
                  </div>
                </div>
              </div>

              <!-- Declined -->
              <div v-else-if="connectionStatus === 'declined'">
                <div style="background:#FEF2F2; border:1.5px solid #FECACA;
                            border-radius:10px; padding:1.25rem; text-align:center;">
                  <div style="font-size:1.5rem; margin-bottom:0.4rem;">❌</div>
                  <div style="font-weight:700; color:#991B1B; font-size:0.9rem;">
                    Request Declined
                  </div>
                  <div style="color:#9B7373; font-size:0.8rem; margin-top:0.25rem;">
                    This donor is not available at this time.
                  </div>
                </div>
              </div>

              <!-- No connection yet → show Connect form -->
              <div v-else>
                <!-- Message input -->
                <div style="margin-bottom:0.75rem;">
                  <label style="display:block; font-size:0.8rem; font-weight:600;
                                 color:#374151; margin-bottom:4px;">
                    💬 Message (optional)
                  </label>
                  <textarea
                    v-model="connectForm.message"
                    rows="3"
                    placeholder="e.g., I need B+ blood urgently at Chittagong Medical. My patient is in critical condition."
                    style="width:100%; padding:0.7rem; border:1.5px solid #D1D5DB;
                           border-radius:8px; font-size:0.875rem; outline:none;
                           resize:vertical; box-sizing:border-box; font-family:sans-serif;"
                  ></textarea>
                </div>

                <!-- Connect button -->
                <button
                  @click="sendConnectRequest"
                  :disabled="connectForm.processing"
                  style="width:100%; padding:0.9rem; background:#BC0000; color:white;
                         border:none; border-radius:10px; font-weight:700;
                         font-size:1rem; cursor:pointer; display:flex;
                         align-items:center; justify-content:center; gap:8px;
                         transition:background 0.2s;"
                  @mouseenter="$event.currentTarget.style.background='#8B0000'"
                  @mouseleave="$event.currentTarget.style.background='#BC0000'"
                >
                  <span v-if="connectForm.processing">⏳ Sending...</span>
                  <span v-else>🔗 Request to Connect</span>
                </button>

                <!-- Info box -->
                <div style="margin-top:0.75rem; background:#F9FAFB;
                            border:1px solid #E5E7EB; border-radius:8px;
                            padding:0.75rem; font-size:0.78rem; color:#6B7280;
                            line-height:1.6;">
                  🔒 <strong>Privacy Protected:</strong> The donor's phone number
                  will only be shared after they accept your request.
                </div>

                <!-- Error message -->
                <p v-if="connectForm.errors.donor_id"
                   style="color:#BC0000; font-size:0.78rem; margin-top:0.5rem;">
                  {{ connectForm.errors.donor_id }}
                </p>
              </div>
            </div>
          </div>

          <!-- ══════════════════════════════════════════════════
               BADGE CARD
               ══════════════════════════════════════════════════ -->
          <div v-if="donor.badge"
               :style="`background:${badgeConfig.bg}; border-color:${badgeConfig.color}33;`"
               style="border-radius:14px; border:2px solid; padding:1.5rem;
                      text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

            <div style="font-size:3rem; margin-bottom:0.5rem;">
              {{ badgeConfig.icon }}
            </div>
            <div :style="`color:${badgeConfig.color};`"
                 style="font-size:1.1rem; font-weight:800; margin-bottom:0.25rem;
                        font-family:'Georgia',serif;">
              {{ badgeConfig.label }}
            </div>
            <div style="font-size:0.8rem; color:#6B7280; margin-bottom:1.25rem;">
              {{ donor.badge.donation_count }} donations · {{ donor.badge.total_points }} points
            </div>

            <!-- Progress Bar -->
            <div v-if="donor.badge.next_level_points">
              <div style="display:flex; justify-content:space-between;
                          font-size:0.75rem; color:#6B7280; margin-bottom:4px;">
                <span>{{ donor.badge.total_points }} pts</span>
                <span>{{ donor.badge.next_level_points }} pts</span>
              </div>
              <div style="height:8px; background:#E5E7EB; border-radius:999px; overflow:hidden;">
                <div :style="`width:${donor.badge.progress_percentage}%;
                              background:${badgeConfig.color};`"
                     style="height:100%; border-radius:999px;
                            transition:width 1s ease-out;">
                </div>
              </div>
              <div style="font-size:0.72rem; color:#9CA3AF; margin-top:4px;">
                Progress to next level
              </div>
            </div>
            <div v-else
                 style="background:rgba(188,0,0,0.1); border-radius:8px;
                        padding:0.5rem; font-size:0.8rem; font-weight:600;
                        color:#BC0000;">
              🏆 Maximum Level Reached!
            </div>
          </div>

          <!-- ══════════════════════════════════════════════════
               BACK BUTTON
               ══════════════════════════════════════════════════ -->
          <Link href="/donors"
                style="display:flex; align-items:center; justify-content:center;
                       gap:6px; padding:0.75rem; background:white;
                       border:1.5px solid #E5E7EB; border-radius:10px;
                       text-decoration:none; color:#6B7280; font-weight:600;
                       font-size:0.875rem;">
            ← Back to All Donors
          </Link>

        </div>
      </div>
    </div>
  </div>
</template>