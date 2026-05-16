<script setup>
/**
 * BloodRequests/Index.vue — Public Blood Requests Feed
 * ─────────────────────────────────────────────────────
 * সব open রিকোয়েস্ট এই পেজে দেখানো হয়।
 * Urgent গুলো সবার আগে দেখায়।
 * এই পেজটি সবার জন্য public — login লাগে না।
 *
 * VUE CONCEPT: computed() — reactive derived values
 *   computed() একটি reactive value থেকে নতুন value বানায়।
 *   source change হলে computed value-ও auto-update হয়।
 */

import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

// Props from BloodRequestController@index
const props = defineProps({
  requests: Object,   // Paginated blood requests
})

// ── Helper functions ──────────────────────────────────────────

// কতক্ষণ আগে post হয়েছে সেটি human-readable format-এ
const timeAgo = (dateStr) => {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000)
  if (diff < 60)   return 'এইমাত্র'
  if (diff < 3600) return `${Math.floor(diff / 60)} মিনিট আগে`
  if (diff < 86400) return `${Math.floor(diff / 3600)} ঘণ্টা আগে`
  return `${Math.floor(diff / 86400)} দিন আগে`
}

// কতক্ষণের মধ্যে দরকার
const timeUntil = (dateStr) => {
  if (!dateStr) return null
  const diff = Math.floor((new Date(dateStr) - Date.now()) / 1000)
  if (diff < 0) return 'সময় পার হয়ে গেছে'
  if (diff < 3600) return `${Math.floor(diff / 60)} মিনিটের মধ্যে`
  if (diff < 86400) return `${Math.floor(diff / 3600)} ঘণ্টার মধ্যে`
  return `${Math.floor(diff / 86400)} দিনের মধ্যে`
}

// Blood group badge color
const badgeStyle = (name) => {
  if (name === 'O-') return 'background:#B8860B; color:#fff;'
  if (name === 'O+') return 'background:#1B5E20; color:#fff;'
  return 'background:#BC0000; color:#fff;'
}
</script>

<template>
  <Head title="Blood Requests — Digital Blood Connect" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- Navigation -->
    <nav style="background:#BC0000; padding:1rem 0; position:sticky; top:0; z-index:50;
                box-shadow:0 2px 10px rgba(188,0,0,0.3);">
      <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">
        <Link href="/" style="color:white; text-decoration:none; font-size:1.1rem;
                              font-weight:700; font-family:'Georgia',serif;">
          🩸 Digital Blood Connect
        </Link>
        <div class="flex gap-3 items-center flex-wrap">
          <Link href="/donors"
                style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.875rem;">
            Find Donors
          </Link>
          <Link href="/requests/create"
                style="background:white; color:#BC0000; padding:0.4rem 1rem;
                       border-radius:6px; text-decoration:none; font-weight:600; font-size:0.875rem;">
            + Post Request
          </Link>
          <Link v-if="$page.props.auth.user"
                href="/dashboard"
                style="color:rgba(255,255,255,0.9); text-decoration:none; font-size:0.875rem;
                       border:1px solid rgba(255,255,255,0.5); padding:0.35rem 0.85rem; border-radius:6px;">
            Dashboard
          </Link>
          <template v-else>
            <Link href="/login"
                  style="color:rgba(255,255,255,0.9); text-decoration:none; font-size:0.875rem;">
              Login
            </Link>
            <Link href="/register"
                  style="background:rgba(255,255,255,0.95); color:#BC0000; padding:0.4rem 1rem;
                         border-radius:6px; text-decoration:none; font-weight:600; font-size:0.875rem;">
              Register
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Header -->
    <div style="background:linear-gradient(to right, #8B0000, #BC0000); padding:2.5rem 0; color:white;">
      <div class="max-w-7xl mx-auto px-4">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:0.5rem;">
          <span style="width:10px; height:10px; background:#90EE90; border-radius:50%;
                       display:inline-block; animation:pulse 1.5s infinite;"></span>
          <h1 style="font-size:1.75rem; font-weight:700; margin:0; font-family:'Georgia',serif;">
            Live Blood Requests
          </h1>
        </div>
        <p style="color:rgba(255,255,255,0.8); font-size:0.9rem; margin:0;">
          {{ requests.total }} টি active রিকোয়েস্ট — সরাসরি ডোনারের সাথে যোগাযোগ করুন
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">

      <!-- Request Cards -->
      <div v-if="requests.data && requests.data.length > 0"
           class="grid md:grid-cols-2 gap-5">

        <div v-for="req in requests.data" :key="req.id"
             style="background:white; border:1px solid #E5E7EB; border-radius:12px;
                    overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);
                    transition:transform 0.2s, box-shadow 0.2s; position:relative;"
             class="hover:shadow-md hover:-translate-y-0.5">

          <!-- Urgent Top Banner -->
          <div v-if="req.is_urgent"
               style="background:#BC0000; color:white; padding:0.35rem 1rem;
                      font-size:0.75rem; font-weight:700; letter-spacing:1px;
                      display:flex; align-items:center; gap:6px;">
            <span style="animation:pulse 1s infinite; display:inline-block;
                         width:7px; height:7px; background:white; border-radius:50%;"></span>
            URGENT — তাৎক্ষণিক রক্ত দরকার
          </div>

          <div style="padding:1.25rem;">
            <!-- Top Row: Blood Badge + Hospital -->
            <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:1rem;">
              <!-- Blood Group Badge -->
              <div :style="badgeStyle(req.blood_group?.name)"
                   style="min-width:56px; height:56px; border-radius:10px;
                          display:flex; align-items:center; justify-content:center;
                          font-weight:800; font-size:1.1rem; flex-shrink:0;">
                {{ req.blood_group?.name }}
              </div>

              <div style="flex:1; min-width:0;">
                <div style="font-weight:700; color:#1a1a1a; font-size:0.95rem;
                            white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  {{ req.hospital_name || 'Hospital not specified' }}
                </div>
                <div style="color:#6B7280; font-size:0.8rem; margin-top:3px;
                            display:flex; align-items:center; gap:4px;">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  {{ req.upazila?.name || req.district?.name }}
                  <span v-if="req.upazila" style="color:#D1D5DB;">·</span>
                  <span v-if="req.upazila">{{ req.district?.name }}</span>
                </div>
              </div>
            </div>

            <!-- Details Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;
                        margin-bottom:1rem; font-size:0.82rem; color:#555;">
              <!-- Units -->
              <div style="display:flex; align-items:center; gap:5px; background:#F9FAFB;
                          padding:0.4rem 0.6rem; border-radius:6px;">
                <span style="color:#BC0000;">💉</span>
                <span><strong>{{ req.units_needed }}</strong> ব্যাগ দরকার</span>
              </div>
              <!-- Needed by -->
              <div v-if="req.needed_by"
                   style="display:flex; align-items:center; gap:5px; background:#FFF7ED;
                          padding:0.4rem 0.6rem; border-radius:6px;">
                <span>⏰</span>
                <span style="color:#C2410C; font-weight:600;">
                  {{ timeUntil(req.needed_by) }}
                </span>
              </div>
            </div>

            <!-- Notes -->
            <p v-if="req.notes"
               style="font-size:0.82rem; color:#6B7280; margin:0 0 1rem;
                      display:-webkit-box; -webkit-line-clamp:2;
                      -webkit-box-orient:vertical; overflow:hidden;">
              {{ req.notes }}
            </p>

            <!-- Action Row -->
            <div style="display:flex; align-items:center; justify-content:space-between;
                        padding-top:0.75rem; border-top:1px solid #F3F4F6;">
              <!-- Posted time -->
              <span style="font-size:0.75rem; color:#9CA3AF;">
                {{ timeAgo(req.created_at) }}
              </span>

              <!-- Call Button -->
              <a v-if="req.contact_phone" :href="'tel:' + req.contact_phone"
                 style="display:inline-flex; align-items:center; gap:6px;
                        background:#BC0000; color:white; padding:0.45rem 1.1rem;
                        border-radius:8px; text-decoration:none; font-size:0.85rem;
                        font-weight:600;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="white" stroke-width="2.5">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                           19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0
                           0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2
                           2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2
                           2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22
                           16.92z"/>
                </svg>
                Call Now
              </a>
              <span v-else style="font-size:0.8rem; color:#9CA3AF; font-style:italic;">
                No phone listed
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else
           style="text-align:center; padding:5rem 2rem; background:white;
                  border-radius:12px; border:2px dashed #E5E7EB;">
        <div style="font-size:4rem; margin-bottom:1rem;">🩸</div>
        <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin:0 0 0.5rem;">
          কোনো সক্রিয় রিকোয়েস্ট নেই
        </h3>
        <p style="color:#6B7280; font-size:0.9rem; margin:0 0 1.5rem;">
          এখন কেউ রক্তের জন্য অনুরোধ করেননি। ধন্যবাদ!
        </p>
        <Link href="/"
              style="background:#BC0000; color:white; padding:0.6rem 1.5rem;
                     border-radius:8px; text-decoration:none; font-weight:600;">
          ← হোম পেজে যান
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="requests.last_page > 1"
           style="display:flex; justify-content:center; gap:0.5rem;
                  margin-top:2rem; flex-wrap:wrap;">
        <template v-for="link in requests.links" :key="link.label">
          <Link v-if="link.url" :href="link.url"
                :style="link.active
                  ? 'background:#BC0000; color:white; border-color:#BC0000;'
                  : 'background:white; color:#374151; border-color:#D1D5DB;'"
                style="padding:0.5rem 0.85rem; border:1px solid; border-radius:6px;
                       text-decoration:none; font-size:0.875rem;"
                v-html="link.label">
          </Link>
        </template>
      </div>

      <!-- CTA -->
      <div style="margin-top:3rem; text-align:center; padding:2rem;
                  background:linear-gradient(135deg, #8B0000, #BC0000);
                  border-radius:12px; color:white;">
        <h3 style="font-size:1.25rem; font-weight:700; margin:0 0 0.5rem;
                   font-family:'Georgia',serif;">
          আপনার কি রক্তের প্রয়োজন?
        </h3>
        <p style="color:rgba(255,255,255,0.85); font-size:0.9rem; margin:0 0 1.25rem;">
          এখনই একটি জরুরি রিকোয়েস্ট পোস্ট করুন। হাজারো ডোনার আপনার সাহায্যে প্রস্তুত।
        </p>
        <Link href="/requests/create"
              style="display:inline-block; background:white; color:#BC0000;
                     padding:0.7rem 2rem; border-radius:8px; text-decoration:none;
                     font-weight:700; font-size:0.95rem;">
          রক্তের রিকোয়েস্ট করুন →
        </Link>
      </div>
    </div>
  </div>
</template>

<style>
@keyframes pulse {
  0%, 100% { opacity:1; transform:scale(1); }
  50% { opacity:0.5; transform:scale(1.2); }
}
</style>