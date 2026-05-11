<script setup>
/**
 * Dashboard.vue — Final Complete Version
 * ─────────────────────────────────────────
 * Login করা ইউজারের ব্যক্তিগত dashboard।
 *
 * নতুন sections:
 *   ① BadgeCard   — donation badge, points, progress bar
 *   ② Timer       — ৯০-day countdown to next eligible donation
 *   ③ Inbox Badge — pending connection requests count
 *   ④ Phone Mask  — toggle to hide/show phone publicly
 */

import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import axios from 'axios'
import BadgeCard from './Dashboard/Components/BadgeCard.vue'

// ── Props from DashboardController ───────────────────────────
const props = defineProps({
  user:                    Object,
  badge:                   Object,
  timer:                   Object,
  pendingConnectionsCount: { type: Number, default: 0 },
  myRequests:              Array,
  stats:                   Object,
  bloodGroups:             Array,
  districts:               Array,
  upazilas:                Array,
})

// ── Active tab ────────────────────────────────────────────────
const activeTab = ref('overview')

// ── Inertia useForm for profile editing ──────────────────────
const form = useForm({
  blood_group_id:     props.user.blood_group_id    || '',
  district_id:        props.user.district_id       || '',
  upazila_id:         props.user.upazila_id        || '',
  phone:              props.user.phone             || '',
  phone_masked:       props.user.phone_masked      ?? false,
  last_donation_date: props.user.last_donation_date || '',
  is_available:       props.user.is_available,
})

// ── Cascading upazila dropdown ────────────────────────────────
const availableUpazilas = ref(props.upazilas || [])
const loadingUpazilas   = ref(false)

watch(() => form.district_id, async (newId) => {
  form.upazila_id         = ''
  availableUpazilas.value = []
  if (!newId) return
  loadingUpazilas.value = true
  try {
    const { data } = await axios.get('/api/upazilas', { params: { district_id: newId } })
    availableUpazilas.value = data
  } finally {
    loadingUpazilas.value = false
  }
})

// ── Save profile ──────────────────────────────────────────────
const saveProfile = () => {
  form.patch('/dashboard/profile')
}

// ── Helpers ───────────────────────────────────────────────────
const formatDate = (d) => {
  if (!d) return 'Not set'
  return new Date(d).toLocaleDateString('en-BD', {
    year: 'numeric', month: 'long', day: 'numeric'
  })
}

const statusStyle = (s) => ({
  open:      'background:#DCFCE7; color:#166534;',
  fulfilled: 'background:#DBEAFE; color:#1E40AF;',
  closed:    'background:#F3F4F6; color:#6B7280;',
}[s] || 'background:#F3F4F6; color:#6B7280;')
</script>

<template>
  <Head title="My Dashboard — Digital Blood Connect" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- ══════════════════════════════════════════════════════
         NAVIGATION
         ══════════════════════════════════════════════════════ -->
    <nav style="background:#BC0000; padding:1rem 0;
                box-shadow:0 2px 10px rgba(188,0,0,0.3); position:sticky; top:0; z-index:50;">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
        <Link href="/" style="color:white; text-decoration:none; font-size:1.1rem;
                              font-weight:700; font-family:'Georgia',serif;">
          🩸 Digital Blood Connect
        </Link>

        <div class="flex items-center gap-4">
          <Link href="/donors"
                style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.875rem;">
            Find Donors
          </Link>

          <!-- ★ Connection Inbox Link with pending count badge -->
          <Link href="/connections/inbox"
                style="position:relative; color:rgba(255,255,255,0.85);
                       text-decoration:none; font-size:0.875rem;
                       display:flex; align-items:center; gap:5px;">
            📬 Inbox
            <!-- Red badge if pending requests exist -->
            <span v-if="pendingConnectionsCount > 0"
                  style="background:white; color:#BC0000; font-size:0.68rem;
                         font-weight:800; padding:0.1rem 0.45rem; border-radius:999px;
                         line-height:1.4; min-width:18px; text-align:center;">
              {{ pendingConnectionsCount }}
            </span>
          </Link>

          <Link href="/logout" method="post" as="button"
                style="background:rgba(255,255,255,0.15); color:white; padding:0.4rem 1rem;
                       border-radius:6px; border:none; cursor:pointer;
                       font-size:0.875rem; text-decoration:none;">
            Logout
          </Link>
        </div>
      </div>
    </nav>

    <!-- ══════════════════════════════════════════════════════
         WELCOME BANNER
         ══════════════════════════════════════════════════════ -->
    <div style="background:linear-gradient(135deg, #8B0000, #BC0000);
                padding:2rem 0; color:white;">
      <div class="max-w-7xl mx-auto px-4">
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
          <!-- Avatar with badge icon overlay -->
          <div style="position:relative; flex-shrink:0;">
            <div style="width:60px; height:60px; background:rgba(255,255,255,0.2);
                        border-radius:50%; display:flex; align-items:center;
                        justify-content:center; font-size:1.5rem; font-weight:800;
                        border:2px solid rgba(255,255,255,0.4);">
              {{ user.name.charAt(0) }}
            </div>
            <!-- Badge icon overlay -->
            <div style="position:absolute; bottom:-2px; right:-4px;
                        font-size:1.1rem; line-height:1;">
              {{ badge?.config?.icon || '🥉' }}
            </div>
          </div>

          <div>
            <h1 style="font-size:1.4rem; font-weight:700; margin:0 0 0.3rem;
                       font-family:'Georgia',serif;">
              Welcome, {{ user.name }}
            </h1>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
              <span style="background:rgba(255,255,255,0.2); border-radius:999px;
                           padding:0.2rem 0.75rem; font-size:0.8rem; font-weight:600;">
                🩸 {{ user.blood_group?.name || 'Blood group not set' }}
              </span>
              <span style="font-size:0.82rem; opacity:0.85;">
                📍 {{ user.upazila?.name || user.district?.name || 'Location not set' }}
              </span>
              <span :style="user.is_available
                ? 'background:rgba(144,238,144,0.3); color:#90EE90;'
                : 'background:rgba(255,100,100,0.3); color:#FFA0A0;'"
                    style="border-radius:999px; padding:0.2rem 0.75rem;
                           font-size:0.8rem; font-weight:600;">
                {{ user.is_available ? '✓ Available' : '✗ Not Available' }}
              </span>
              <!-- Badge level chip -->
              <span :style="`background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3);`"
                    style="border-radius:999px; padding:0.2rem 0.75rem;
                           font-size:0.8rem; font-weight:600; color:white;">
                {{ badge?.config?.icon }} {{ badge?.config?.label || 'Bronze Donor' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- ══════════════════════════════════════════════════
           STATS CARDS
           ══════════════════════════════════════════════════ -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div v-for="(card, i) in [
               { label:'Total Donors',  value:stats.total_donors,  icon:'👥', color:'#BC0000' },
               { label:'Available Now', value:stats.available_now, icon:'✅', color:'#16A34A' },
               { label:'Open Requests', value:stats.open_requests, icon:'🩸', color:'#D97706' },
               { label:'Urgent Now',    value:stats.urgent_now,    icon:'🚨', color:'#DC2626' },
             ]"
             :key="i"
             style="background:white; border-radius:12px; padding:1.25rem;
                    box-shadow:0 1px 6px rgba(0,0,0,0.06); border:1px solid #E5E7EB;">
          <div style="font-size:1.75rem; font-weight:800; margin-bottom:0.25rem;"
               :style="{ color: card.color }">{{ card.value }}</div>
          <div style="font-size:0.8rem; color:#6B7280;">{{ card.icon }} {{ card.label }}</div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           TAB NAVIGATION
           ══════════════════════════════════════════════════ -->
      <div style="display:flex; border-bottom:2px solid #E5E7EB;
                  margin-bottom:1.5rem; overflow-x:auto; gap:0;">
        <button v-for="tab in [
                  { id:'overview', label:'🏠 Overview' },
                  { id:'profile',  label:'👤 Profile' },
                  { id:'requests', label:'📋 My Requests' },
                ]"
                :key="tab.id"
                @click="activeTab = tab.id"
                :style="activeTab === tab.id
                  ? 'border-bottom:3px solid #BC0000; color:#BC0000;'
                  : 'border-bottom:3px solid transparent; color:#6B7280;'"
                style="padding:0.75rem 1.5rem; border:none; cursor:pointer;
                       font-size:0.875rem; font-weight:600; white-space:nowrap;
                       background:transparent; margin-bottom:-2px;">
          {{ tab.label }}
        </button>
      </div>

      <!-- ══════════════════════════════════════════════════
           TAB 1: OVERVIEW — Badge + Timer
           ══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'overview'">
        <div class="grid lg:grid-cols-2 gap-6">

          <!-- ★ BadgeCard component (full mode) -->
          <div>
            <h3 style="font-size:0.95rem; font-weight:700; color:#374151;
                       margin:0 0 0.75rem; display:flex; align-items:center; gap:6px;">
              🏅 Your Donor Badge
            </h3>
            <BadgeCard :badge="badge" :timer="timer" />
          </div>

          <!-- Quick Actions -->
          <div>
            <h3 style="font-size:0.95rem; font-weight:700; color:#374151;
                       margin:0 0 0.75rem;">
              ⚡ Quick Actions
            </h3>
            <div style="display:flex; flex-direction:column; gap:0.75rem;">

              <!-- Availability quick toggle -->
              <div style="background:white; border:1px solid #E5E7EB; border-radius:12px;
                          padding:1.1rem; display:flex; align-items:center;
                          justify-content:space-between;">
                <div>
                  <div style="font-weight:600; font-size:0.9rem; color:#1a1a1a;">
                    {{ form.is_available ? '✅ You are Available' : '❌ Not Available' }}
                  </div>
                  <div style="font-size:0.78rem; color:#6B7280; margin-top:2px;">
                    Toggle your donation availability status
                  </div>
                </div>
                <!-- Toggle switch -->
                <button @click="form.is_available = !form.is_available; saveProfile()"
                        :style="form.is_available ? 'background:#16A34A;' : 'background:#D1D5DB;'"
                        style="width:52px; height:28px; border-radius:999px; border:none;
                               cursor:pointer; position:relative; transition:background 0.3s; flex-shrink:0;">
                  <span :style="form.is_available ? 'left:26px;' : 'left:2px;'"
                        style="position:absolute; top:2px; width:24px; height:24px;
                               background:white; border-radius:50%; transition:left 0.3s;
                               box-shadow:0 1px 4px rgba(0,0,0,0.2);"></span>
                </button>
              </div>

              <!-- Request blood -->
              <Link href="/requests/create"
                    style="display:flex; align-items:center; gap:10px; padding:1.1rem;
                           background:#FEF2F2; border:1.5px solid #FECACA; border-radius:12px;
                           text-decoration:none; color:#BC0000; font-weight:600;
                           font-size:0.9rem; transition:background 0.2s;">
                <span style="font-size:1.3rem;">🚨</span>
                <div>
                  <div>Post Emergency Blood Request</div>
                  <div style="font-size:0.75rem; font-weight:400; color:#9B7373;">
                    Alert donors in your area instantly
                  </div>
                </div>
              </Link>

              <!-- Search donors -->
              <Link href="/donors"
                    style="display:flex; align-items:center; gap:10px; padding:1.1rem;
                           background:#F0FDF4; border:1.5px solid #86EFAC; border-radius:12px;
                           text-decoration:none; color:#166534; font-weight:600;
                           font-size:0.9rem;">
                <span style="font-size:1.3rem;">🔍</span>
                <div>
                  <div>Find Blood Donors</div>
                  <div style="font-size:0.75rem; font-weight:400; color:#5E9E6E;">
                    Search by district, upazila, blood group
                  </div>
                </div>
              </Link>

              <!-- Inbox link -->
              <Link href="/connections/inbox"
                    style="display:flex; align-items:center; gap:10px; padding:1.1rem;
                           background:#F5F3FF; border:1.5px solid #C4B5FD; border-radius:12px;
                           text-decoration:none; color:#5B21B6; font-weight:600; font-size:0.9rem;">
                <span style="font-size:1.3rem;">📬</span>
                <div>
                  <div style="display:flex; align-items:center; gap:8px;">
                    Connection Inbox
                    <span v-if="pendingConnectionsCount > 0"
                          style="background:#BC0000; color:white; font-size:0.68rem;
                                 font-weight:800; padding:0.1rem 0.45rem;
                                 border-radius:999px; line-height:1.4;">
                      {{ pendingConnectionsCount }}
                    </span>
                  </div>
                  <div style="font-size:0.75rem; font-weight:400; color:#7C5CB8;">
                    Manage incoming connection requests
                  </div>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           TAB 2: PROFILE EDIT
           ══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'profile'">

        <!-- Flash message -->
        <div v-if="$page.props.flash?.success"
             style="background:#DCFCE7; border:1px solid #86EFAC; color:#166534;
                    padding:0.75rem 1rem; border-radius:8px; margin-bottom:1.5rem;
                    font-size:0.875rem;">
          ✓ {{ $page.props.flash.success }}
        </div>

        <div class="grid lg:grid-cols-3 gap-6">

          <!-- Availability + Phone Masking Card -->
          <div style="background:white; border-radius:12px; padding:1.5rem;
                      box-shadow:0 1px 6px rgba(0,0,0,0.06); border:1px solid #E5E7EB;">
            <h3 style="font-size:0.95rem; font-weight:700; color:#1a1a1a; margin:0 0 1rem;">
              Settings
            </h3>

            <!-- Availability toggle -->
            <div :style="form.is_available
                   ? 'background:#F0FDF4; border-color:#86EFAC;'
                   : 'background:#FEF2F2; border-color:#FECACA;'"
                 style="border:1.5px solid; border-radius:10px; padding:0.9rem;
                        display:flex; align-items:center; justify-content:space-between;
                        margin-bottom:0.75rem;">
              <div>
                <div :style="form.is_available ? 'color:#166534;' : 'color:#991B1B;'"
                     style="font-weight:600; font-size:0.875rem;">
                  {{ form.is_available ? '✓ Available to Donate' : '✗ Not Available' }}
                </div>
                <div style="font-size:0.72rem; color:#6B7280; margin-top:1px;">
                  Shown publicly on your profile
                </div>
              </div>
              <button @click="form.is_available = !form.is_available"
                      :style="form.is_available ? 'background:#16A34A;' : 'background:#D1D5DB;'"
                      style="width:48px; height:26px; border-radius:999px; border:none;
                             cursor:pointer; position:relative; transition:background 0.3s; flex-shrink:0;">
                <span :style="form.is_available ? 'left:24px;' : 'left:2px;'"
                      style="position:absolute; top:2px; width:22px; height:22px;
                             background:white; border-radius:50%; transition:left 0.3s;"></span>
              </button>
            </div>

            <!-- ★ Phone Masking toggle -->
            <div style="background:#F9FAFB; border:1.5px solid #E5E7EB; border-radius:10px;
                        padding:0.9rem; display:flex; align-items:center;
                        justify-content:space-between; margin-bottom:1rem;">
              <div>
                <div style="font-weight:600; font-size:0.875rem; color:#374151;">
                  {{ form.phone_masked ? '🔒 Phone Hidden' : '📞 Phone Visible' }}
                </div>
                <div style="font-size:0.72rem; color:#6B7280; margin-top:1px;">
                  {{ form.phone_masked
                    ? 'People must send a connection request'
                    : 'Phone shown publicly on profile' }}
                </div>
              </div>
              <button @click="form.phone_masked = !form.phone_masked"
                      :style="form.phone_masked ? 'background:#BC0000;' : 'background:#D1D5DB;'"
                      style="width:48px; height:26px; border-radius:999px; border:none;
                             cursor:pointer; position:relative; transition:background 0.3s; flex-shrink:0;">
                <span :style="form.phone_masked ? 'left:24px;' : 'left:2px;'"
                      style="position:absolute; top:2px; width:22px; height:22px;
                             background:white; border-radius:50%; transition:left 0.3s;"></span>
              </button>
            </div>

            <!-- Eligibility status -->
            <div :style="timer?.eligible
                   ? 'background:#F0FDF4; border-color:#86EFAC; color:#166534;'
                   : 'background:#FFFBEB; border-color:#FDE68A; color:#92400E;'"
                 style="padding:0.75rem; border-radius:8px; border:1px solid;
                        font-size:0.78rem; line-height:1.5;">
              <div style="font-weight:700; margin-bottom:2px;">Donation Eligibility</div>
              {{ timer?.message }}
            </div>
          </div>

          <!-- Profile Form -->
          <div style="background:white; border-radius:12px; padding:1.5rem;
                      box-shadow:0 1px 6px rgba(0,0,0,0.06); border:1px solid #E5E7EB;"
               class="lg:col-span-2">
            <h3 style="font-size:0.95rem; font-weight:700; color:#1a1a1a; margin:0 0 1.25rem;">
              Edit Profile
            </h3>

            <div class="grid sm:grid-cols-2 gap-4">

              <!-- Phone -->
              <div>
                <label class="form-label">Phone Number</label>
                <input v-model="form.phone" type="tel" placeholder="01XXXXXXXXX"
                       class="form-input" />
              </div>

              <!-- Blood Group -->
              <div>
                <label class="form-label">Blood Group</label>
                <select v-model="form.blood_group_id" class="form-input">
                  <option value="">Select blood group</option>
                  <option v-for="g in bloodGroups" :key="g.id" :value="g.id">{{ g.name }}</option>
                </select>
              </div>

              <!-- District -->
              <div>
                <label class="form-label">District</label>
                <select v-model="form.district_id" class="form-input">
                  <option value="">Select district</option>
                  <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>

              <!-- Upazila -->
              <div>
                <label class="form-label">Upazila / Area</label>
                <select v-model="form.upazila_id" :disabled="!form.district_id" class="form-input">
                  <option value="">{{ loadingUpazilas ? 'Loading...' : 'Select area' }}</option>
                  <option v-for="u in availableUpazilas" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>

              <!-- Last Donation Date -->
              <div class="sm:col-span-2">
                <label class="form-label">
                  Last Donation Date
                  <span style="font-weight:400; color:#6B7280;">
                    — updating this awards +50 points 🎯
                  </span>
                </label>
                <input v-model="form.last_donation_date" type="date"
                       :max="new Date().toISOString().split('T')[0]"
                       class="form-input" />
                <p style="font-size:0.72rem; color:#6B7280; margin-top:4px;">
                  💡 Medical guideline: minimum 90 days between donations
                </p>
              </div>
            </div>

            <div style="margin-top:1.25rem; display:flex; align-items:center; gap:0.75rem;">
              <button @click="saveProfile"
                      :disabled="form.processing"
                      class="btn-primary">
                {{ form.processing ? '⏳ Saving...' : '💾 Save Changes' }}
              </button>
              <span style="font-size:0.78rem; color:#6B7280;">
                Donation date changes award points automatically
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════
           TAB 3: MY REQUESTS
           ══════════════════════════════════════════════════ -->
      <div v-show="activeTab === 'requests'">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem;">
          <h3 style="font-size:1rem; font-weight:700; color:#1a1a1a; margin:0;">My Blood Requests</h3>
          <Link href="/requests/create" class="btn-primary" style="text-decoration:none;">
            + New Request
          </Link>
        </div>

        <div v-if="myRequests.length" class="flex flex-col gap-4">
          <div v-for="req in myRequests" :key="req.id"
               style="background:white; border:1px solid #E5E7EB; border-radius:12px;
                      padding:1.25rem; display:flex; align-items:center;
                      justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:0.85rem;">
              <div style="width:48px; height:48px; background:#BC0000; border-radius:10px;
                          display:flex; align-items:center; justify-content:center;
                          color:white; font-weight:800; font-size:1rem; flex-shrink:0;">
                {{ req.blood_group?.name }}
              </div>
              <div>
                <div style="font-weight:600; color:#1a1a1a; font-size:0.9rem;">
                  {{ req.hospital_name || 'Hospital not specified' }}
                </div>
                <div style="color:#6B7280; font-size:0.78rem; margin-top:2px;">
                  {{ req.district?.name }} · {{ req.units_needed }} unit(s) ·
                  {{ formatDate(req.created_at) }}
                </div>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
              <span :style="statusStyle(req.status)"
                    style="padding:0.2rem 0.65rem; border-radius:999px;
                           font-size:0.75rem; font-weight:600;">
                {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
              </span>
              <Link v-if="req.status === 'open'"
                    :href="`/requests/${req.id}/fulfill`"
                    method="patch" as="button"
                    style="padding:0.35rem 0.75rem; background:#DCFCE7; color:#166534;
                           border:1px solid #86EFAC; border-radius:6px; font-size:0.78rem;
                           font-weight:600; cursor:pointer; text-decoration:none;">
                ✓ Mark Fulfilled
              </Link>
            </div>
          </div>
        </div>

        <div v-else
             style="text-align:center; padding:3rem; background:white;
                    border-radius:12px; border:2px dashed #E5E7EB;">
          <div style="font-size:2.5rem; margin-bottom:0.5rem;">📋</div>
          <p style="color:#6B7280; font-size:0.9rem; margin:0 0 1rem;">No requests yet.</p>
          <Link href="/requests/create" class="btn-primary" style="text-decoration:none;">
            Create First Request
          </Link>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.form-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 4px;
}

.form-input {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1.5px solid #D1D5DB;
  border-radius: 8px;
  font-size: 0.875rem;
  outline: none;
  font-family: sans-serif;
  box-sizing: border-box;
  background: white;
  color: #1a1a1a;
  transition: border-color 0.2s;
}

.form-input:focus {
  border-color: #BC0000;
  box-shadow: 0 0 0 3px rgba(188,0,0,0.08);
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #BC0000;
  color: white;
  padding: 0.65rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #8B0000;
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>