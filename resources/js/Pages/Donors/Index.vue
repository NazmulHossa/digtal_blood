<script setup>
/**
 * Donors/Show.vue — Donor Profile Page (Final)
 * ──────────────────────────────────────────────
 * একজন ডোনারের public profile।
 * Phone Masking + Badge দুটো নতুন feature যুক্ত।
 *
 * Phone Masking flow:
 *   phone_masked = false → phone সরাসরি দেখায়
 *   phone_masked = true  → ConnectButton দেখায়
 *     → "Request to Connect" → modal → POST /connections
 *     → Donor accepts → phone reveal হয়
 */

import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import ConnectButton from '../Dashboard/Components/ConnectButton.vue'
import BadgeCard     from '../Dashboard/Components/BadgeCard.vue'

const props = defineProps({
  donor:            Object,
  connectionStatus: { type: String, default: 'none' },
  donorPhone:       { type: String, default: null },
  isOwnProfile:     { type: Boolean, default: false },
})

// Badge data with display config
const badge = computed(() => {
  if (!props.donor.badge) return null
  const levels = {
    bronze: { label:'Bronze Donor', icon:'🥉', color:'#CD7F32', bg:'#FDF3E7', description:'Welcome to the community!', min_points:0 },
    silver: { label:'Silver Donor', icon:'🥈', color:'#9CA3AF', bg:'#F3F4F6', description:"You've made a real difference!", min_points:100 },
    gold:   { label:'Gold Donor',   icon:'🥇', color:'#D97706', bg:'#FFFBEB', description:'Outstanding commitment!', min_points:300 },
    hero:   { label:'Community Hero',icon:'🦸',color:'#BC0000', bg:'#FEF2F2', description:'A true hero of Chittagong!', min_points:600 },
  }
  return {
    ...props.donor.badge,
    config: levels[props.donor.badge.level] ?? levels.bronze,
  }
})

// Donation eligibility (computed from last_donation_date)
const eligibility = computed(() => {
  if (!props.donor.last_donation_date) {
    return { ok: true, text: 'Never donated — eligible now' }
  }
  const days = Math.floor((Date.now() - new Date(props.donor.last_donation_date)) / 86400000)
  if (days >= 90) {
    return { ok: true, text: `Last donated ${days} days ago — eligible now` }
  }
  const next = new Date(props.donor.last_donation_date)
  next.setDate(next.getDate() + 90)
  return {
    ok: false,
    text: `Next eligible: ${next.toLocaleDateString('en-BD', { dateStyle: 'medium' })}`
  }
})

// Badge border color
const badgeColor = computed(() => badge.value?.config?.color ?? '#BC0000')
</script>

<template>
  <Head :title="`${donor.name} — Donor Profile | Digital Blood Connect`" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- Nav -->
    <nav style="background:#BC0000; padding:1rem 0;
                box-shadow:0 2px 10px rgba(188,0,0,0.3); position:sticky; top:0; z-index:50;">
      <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <Link href="/" style="color:white; text-decoration:none; font-weight:700;
                              font-family:'Georgia',serif; font-size:1.1rem;">
          🩸 Digital Blood Connect
        </Link>
        <div class="flex gap-4">
          <Link href="/donors"
                style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.875rem;">
            ← All Donors
          </Link>
          <Link v-if="!$page.props.auth?.user"
                href="/register"
                style="background:white; color:#BC0000; padding:0.4rem 0.9rem;
                       border-radius:6px; text-decoration:none; font-weight:600;
                       font-size:0.875rem;">
            Register as Donor
          </Link>
        </div>
      </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="grid lg:grid-cols-3 gap-6">

        <!-- ════════════════════════════════════════════════
             LEFT COLUMN: Profile Card
             ════════════════════════════════════════════════ -->
        <div class="lg:col-span-1">
          <div style="background:white; border-radius:16px; overflow:hidden;
                      box-shadow:0 4px 20px rgba(0,0,0,0.08); border:1px solid #E5E7EB;">

            <!-- Profile header gradient -->
            <div :style="`background:linear-gradient(135deg, #8B0000, ${badgeColor});`"
                 style="padding:2rem 1.25rem; text-align:center;">

              <!-- Avatar with badge overlay -->
              <div style="position:relative; display:inline-block; margin-bottom:0.75rem;">
                <div style="width:80px; height:80px; background:rgba(255,255,255,0.25);
                            border-radius:50%; display:flex; align-items:center;
                            justify-content:center; font-size:2rem; font-weight:800;
                            color:white; border:3px solid rgba(255,255,255,0.4);
                            margin:0 auto;">
                  {{ donor.name.charAt(0) }}
                </div>
                <!-- Badge icon -->
                <div v-if="badge"
                     style="position:absolute; bottom:0; right:0; font-size:1.3rem;">
                  {{ badge.config.icon }}
                </div>
              </div>

              <h1 style="color:white; font-size:1.25rem; font-weight:700; margin:0 0 0.5rem;
                         font-family:'Georgia',serif;">
                {{ donor.name }}
              </h1>

              <!-- Blood group pill -->
              <div style="display:inline-flex; align-items:center; gap:6px;
                          background:rgba(255,255,255,0.2); border:2px solid rgba(255,255,255,0.4);
                          border-radius:999px; padding:0.3rem 1rem;
                          font-size:1rem; font-weight:800; color:white;">
                🩸 {{ donor.blood_group?.name || 'N/A' }}
              </div>
            </div>

            <!-- Profile body -->
            <div style="padding:1.25rem;">

              <!-- Status badges -->
              <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1.25rem;">

                <!-- Availability -->
                <div :style="donor.is_available
                       ? 'background:#F0FDF4; border-color:#86EFAC;'
                       : 'background:#FEF2F2; border-color:#FECACA;'"
                     style="border:1.5px solid; border-radius:8px; padding:0.6rem 0.75rem;
                            display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.1rem;">{{ donor.is_available ? '✅' : '❌' }}</span>
                  <div>
                    <div :style="donor.is_available ? 'color:#166534;' : 'color:#991B1B;'"
                         style="font-weight:600; font-size:0.82rem;">
                      {{ donor.is_available ? 'Available to Donate' : 'Not Available Now' }}
                    </div>
                  </div>
                </div>

                <!-- Eligibility -->
                <div :style="eligibility.ok
                       ? 'background:#F0FDF4; border-color:#86EFAC;'
                       : 'background:#FFF7ED; border-color:#FED7AA;'"
                     style="border:1.5px solid; border-radius:8px; padding:0.6rem 0.75rem;
                            display:flex; align-items:center; gap:8px;">
                  <span style="font-size:1.1rem;">{{ eligibility.ok ? '💪' : '⏳' }}</span>
                  <div :style="eligibility.ok ? 'color:#166534;' : 'color:#C2410C;'"
                       style="font-size:0.78rem; line-height:1.4;">
                    {{ eligibility.text }}
                  </div>
                </div>
              </div>

              <!-- Location info -->
              <div style="background:#F9FAFB; border-radius:8px; padding:0.85rem;
                          margin-bottom:1.25rem;">
                <div style="font-size:0.78rem; font-weight:700; color:#374151; margin-bottom:0.4rem;">
                  📍 Location
                </div>
                <div style="font-size:0.85rem; color:#4B5563; line-height:1.8;">
                  <div v-if="donor.upazila">
                    <strong>Upazila:</strong> {{ donor.upazila.name }}
                    <span v-if="donor.upazila.bn_name" style="color:#9CA3AF;">
                      ({{ donor.upazila.bn_name }})
                    </span>
                  </div>
                  <div v-if="donor.district">
                    <strong>District:</strong> {{ donor.district.name }}
                  </div>
                  <div v-if="!donor.district && !donor.upazila" style="color:#9CA3AF;">
                    Location not provided
                  </div>
                </div>
              </div>

              <!-- ★ CONTACT SECTION — Phone Masking logic ────── -->
              <!--
                SCIENCE FAIR EXPLANATION:
                ─────────────────────────
                Case 1: phone_masked = false
                  → phone সরাসরি দেখায় (Call button)

                Case 2: phone_masked = true + isOwnProfile = true
                  → নিজের profile → phone দেখায় (private)

                Case 3: phone_masked = true + logged in user
                  → ConnectButton component দেখায়
                  → "Request to Connect" → modal → POST /connections
                  → Donor accepts → donorPhone prop filled → phone reveal

                Case 4: phone_masked = true + guest (not logged in)
                  → Login করতে বলা হয়
              -->

              <!-- Case 1 & 2: Phone directly visible -->
              <div v-if="!donor.phone_masked || isOwnProfile">
                <a v-if="donor.phone"
                   :href="`tel:${donor.phone}`"
                   style="display:flex; align-items:center; justify-content:center; gap:8px;
                          background:#BC0000; color:white; padding:0.75rem; border-radius:10px;
                          text-decoration:none; font-weight:700; font-size:0.95rem;
                          margin-bottom:0.75rem;">
                  📞 {{ isOwnProfile ? donor.phone : 'Call Donor' }}
                </a>
                <p v-else style="text-align:center; color:#9CA3AF; font-size:0.82rem;">
                  Phone not provided
                </p>
              </div>

              <!-- Case 3: Logged in → ConnectButton -->
              <ConnectButton
                v-else-if="$page.props.auth?.user"
                :donor="donor"
                :connection-status="connectionStatus"
                :donor-phone="donorPhone"
              />

              <!-- Case 4: Guest → login prompt -->
              <div v-else
                   style="text-align:center; padding:1rem; background:#F9FAFB;
                          border:1.5px dashed #D1D5DB; border-radius:10px;">
                <p style="font-size:0.82rem; color:#6B7280; margin:0 0 0.6rem;">
                  🔒 Login to contact this donor
                </p>
                <Link href="/login"
                      style="background:#BC0000; color:white; padding:0.5rem 1.25rem;
                             border-radius:8px; text-decoration:none; font-weight:600;
                             font-size:0.85rem;">
                  Login / Register
                </Link>
              </div>

              <!-- Request blood link -->
              <Link href="/requests/create"
                    style="display:flex; align-items:center; justify-content:center; gap:6px;
                           padding:0.65rem; background:#FEF2F2; color:#BC0000;
                           border-radius:10px; text-decoration:none; font-size:0.85rem;
                           font-weight:600; border:1px solid #FECACA; margin-top:0.75rem;">
                🩸 Post a Blood Request
              </Link>
            </div>
          </div>
        </div>

        <!-- ════════════════════════════════════════════════
             RIGHT COLUMN: Badge + Stats
             ════════════════════════════════════════════════ -->
        <div class="lg:col-span-2">

          <!-- Badge Card (compact on profile, full if badge exists) -->
          <div v-if="badge" style="margin-bottom:1.5rem;">
            <h3 style="font-size:0.9rem; font-weight:700; color:#374151; margin:0 0 0.75rem;">
              🏅 Donor Achievement
            </h3>
            <BadgeCard :badge="badge" :compact="false" :timer="null" />
          </div>

          <!-- Stats grid -->
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div style="background:white; border-radius:12px; padding:1.25rem;
                        border:1px solid #E5E7EB; text-align:center;">
              <div style="font-size:2rem; font-weight:800; color:#BC0000;">
                {{ badge?.donation_count ?? 0 }}
              </div>
              <div style="font-size:0.8rem; color:#6B7280; margin-top:4px;">Total Donations</div>
            </div>
            <div style="background:white; border-radius:12px; padding:1.25rem;
                        border:1px solid #E5E7EB; text-align:center;">
              <div style="font-size:2rem; font-weight:800; color:#D97706;">
                {{ badge?.total_points ?? 0 }}
              </div>
              <div style="font-size:0.8rem; color:#6B7280; margin-top:4px;">Points Earned</div>
            </div>
          </div>

          <!-- About / Last donation -->
          <div style="background:white; border-radius:12px; padding:1.5rem;
                      border:1px solid #E5E7EB;">
            <h3 style="font-size:0.9rem; font-weight:700; color:#374151; margin:0 0 1rem;">
              📅 Donation History
            </h3>
            <div style="display:flex; flex-direction:column; gap:0.6rem;
                        font-size:0.875rem; color:#4B5563;">
              <div style="display:flex; justify-content:space-between; padding:0.5rem 0;
                          border-bottom:1px solid #F3F4F6;">
                <span style="color:#6B7280;">Last Donation</span>
                <strong>
                  {{ donor.last_donation_date
                     ? new Date(donor.last_donation_date).toLocaleDateString('en-BD', { dateStyle:'medium' })
                     : 'Not recorded' }}
                </strong>
              </div>
              <div style="display:flex; justify-content:space-between; padding:0.5rem 0;
                          border-bottom:1px solid #F3F4F6;">
                <span style="color:#6B7280;">Eligibility</span>
                <strong :style="eligibility.ok ? 'color:#166534;' : 'color:#C2410C;'">
                  {{ eligibility.ok ? '✓ Eligible Now' : '⏳ Not Yet' }}
                </strong>
              </div>
              <div style="display:flex; justify-content:space-between; padding:0.5rem 0;">
                <span style="color:#6B7280;">Badge Level</span>
                <strong>{{ badge?.config?.icon }} {{ badge?.config?.label ?? 'Bronze Donor' }}</strong>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Back link -->
      <div style="text-align:center; margin-top:2rem;">
        <Link href="/donors" style="color:#6B7280; text-decoration:none; font-size:0.875rem;">
          ← Find more donors
        </Link>
      </div>
    </div>
  </div>
</template>