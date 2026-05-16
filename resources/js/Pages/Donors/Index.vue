<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import axios from 'axios'

// ── Props from DonorController@index ─────────────────────────
const props = defineProps({
  donors:      Object,  // paginated object
  bloodGroups: Array,
  districts:   Array,
  upazilas:    Array,
  filters:     Object,
})

// ── Filter state (URL থেকে initial value নাও) ────────────────
const selectedBloodGroup = ref(props.filters?.blood_group_id || '')
const selectedDistrict   = ref(props.filters?.district_id    || '')
const selectedUpazila    = ref(props.filters?.upazila_id     || '')

const availableUpazilas  = ref(props.upazilas || [])
const loadingUpazilas    = ref(false)

// ── District change হলে Upazila load করো ─────────────────────
watch(selectedDistrict, async (newId) => {
  selectedUpazila.value    = ''
  availableUpazilas.value  = []
  if (!newId) return
  loadingUpazilas.value = true
  try {
    const { data } = await axios.get('/api/upazilas', {
      params: { district_id: newId }
    })
    availableUpazilas.value = data
  } finally {
    loadingUpazilas.value = false
  }
})

// ── Filter apply করো (Inertia router দিয়ে) ───────────────────
const applyFilters = () => {
  router.get('/donors', {
    blood_group_id: selectedBloodGroup.value || undefined,
    district_id:    selectedDistrict.value   || undefined,
    upazila_id:     selectedUpazila.value    || undefined,
  }, {
    preserveState: true,
    replace: true,
  })
}

const clearFilters = () => {
  selectedBloodGroup.value = ''
  selectedDistrict.value   = ''
  selectedUpazila.value    = ''
  router.get('/donors')
}

// ── Badge helper ──────────────────────────────────────────────
const getBadgeIcon = (level) => ({
  bronze: '🥉',
  silver: '🥈',
  gold:   '🥇',
  hero:   '🦸',
}[level] || '🥉')
</script>

<template>
  <Head title="Find Blood Donors — Digital Blood Connect" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- Navigation -->
    <nav style="background:#BC0000; padding:1rem 0;
                box-shadow:0 2px 10px rgba(188,0,0,0.3);
                position:sticky; top:0; z-index:50;">
      <div style="max-width:1200px; margin:0 auto; padding:0 1rem;
                  display:flex; align-items:center; justify-content:space-between;">
        <Link href="/"
              style="color:white; text-decoration:none; font-size:1.1rem;
                     font-weight:700; font-family:'Georgia',serif;">
          🩸 Digital Blood Connect
        </Link>
        <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
          <Link href="/requests"
                style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.875rem;">
            Blood Requests
          </Link>
          <Link v-if="$page.props.auth.user"
                href="/dashboard"
                style="background:white; color:#BC0000; padding:0.4rem 1rem;
                       border-radius:6px; text-decoration:none;
                       font-weight:600; font-size:0.875rem;">
            Dashboard
          </Link>
          <template v-else>
            <Link href="/login"
                  style="color:rgba(255,255,255,0.9); text-decoration:none; font-size:0.875rem;">
              Login
            </Link>
            <Link href="/register"
                  style="background:white; color:#BC0000; padding:0.4rem 1rem;
                         border-radius:6px; text-decoration:none;
                         font-weight:600; font-size:0.875rem;">
              Register
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Header -->
    <div style="background:linear-gradient(135deg, #8B0000, #BC0000);
                padding:2.5rem 0; color:white; text-align:center;">
      <div style="max-width:1200px; margin:0 auto; padding:0 1rem;">
        <h1 style="font-size:2rem; font-weight:700; margin:0 0 0.5rem;
                   font-family:'Georgia',serif;">
          🔍 Find Blood Donors
        </h1>
        <p style="color:rgba(255,255,255,0.85); font-size:0.95rem; margin:0;">
          Search for registered donors across Chittagong and surrounding areas
        </p>
      </div>
    </div>

    <div style="max-width:1200px; margin:0 auto; padding:2rem 1rem;">

      <!-- ── Filter Panel ─────────────────────────────────────── -->
      <div style="background:white; border-radius:12px; padding:1.5rem;
                  box-shadow:0 2px 8px rgba(0,0,0,0.06);
                  border:1px solid #E5E7EB; margin-bottom:2rem;">
        <h3 style="font-size:0.95rem; font-weight:700; color:#374151;
                   margin:0 0 1rem;">
          🎯 Filter Donors
        </h3>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
                    gap:1rem; align-items:end;">

          <!-- Blood Group -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:600;
                          color:#374151; margin-bottom:4px;">
              🩸 Blood Group
            </label>
            <select v-model="selectedBloodGroup"
                    style="width:100%; padding:0.6rem 0.75rem;
                           border:1.5px solid #D1D5DB; border-radius:8px;
                           font-size:0.875rem; outline:none;">
              <option value="">All Groups</option>
              <option v-for="g in bloodGroups" :key="g.id" :value="g.id">
                {{ g.name }}
              </option>
            </select>
          </div>

          <!-- District -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:600;
                          color:#374151; margin-bottom:4px;">
              📍 District
            </label>
            <select v-model="selectedDistrict"
                    style="width:100%; padding:0.6rem 0.75rem;
                           border:1.5px solid #D1D5DB; border-radius:8px;
                           font-size:0.875rem; outline:none;">
              <option value="">All Districts</option>
              <option v-for="d in districts" :key="d.id" :value="d.id">
                {{ d.name }}
              </option>
            </select>
          </div>

          <!-- Upazila -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:600;
                          color:#374151; margin-bottom:4px;">
              🏘️ Upazila
            </label>
            <select v-model="selectedUpazila"
                    :disabled="!selectedDistrict"
                    style="width:100%; padding:0.6rem 0.75rem;
                           border:1.5px solid #D1D5DB; border-radius:8px;
                           font-size:0.875rem; outline:none;">
              <option value="">
                {{ selectedDistrict ? 'All Areas' : 'Select district first' }}
              </option>
              <option v-for="u in availableUpazilas" :key="u.id" :value="u.id">
                {{ u.name }}
              </option>
            </select>
          </div>

          <!-- Buttons -->
          <div style="display:flex; gap:0.5rem;">
            <button @click="applyFilters"
                    style="flex:1; padding:0.6rem; background:#BC0000; color:white;
                           border:none; border-radius:8px; font-weight:600;
                           font-size:0.875rem; cursor:pointer;">
              Search
            </button>
            <button @click="clearFilters"
                    style="padding:0.6rem 0.85rem; background:#F3F4F6; color:#6B7280;
                           border:1px solid #E5E7EB; border-radius:8px;
                           font-size:0.875rem; cursor:pointer;">
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Results count -->
      <div style="display:flex; align-items:center; justify-content:space-between;
                  margin-bottom:1rem;">
        <p style="color:#6B7280; font-size:0.875rem; margin:0;">
          Showing <strong>{{ donors.data?.length || 0 }}</strong>
          of <strong>{{ donors.total || 0 }}</strong> donors
        </p>
        <Link href="/requests/create"
              style="background:#BC0000; color:white; padding:0.5rem 1rem;
                     border-radius:8px; text-decoration:none;
                     font-weight:600; font-size:0.875rem;">
          🚨 Post Request
        </Link>
      </div>

      <!-- ── Donor Cards Grid ─────────────────────────────────── -->
      <div v-if="donors.data && donors.data.length > 0"
           style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));
                  gap:1.25rem; margin-bottom:2rem;">

        <div v-for="donor in donors.data" :key="donor.id"
             style="background:white; border-radius:12px; overflow:hidden;
                    box-shadow:0 2px 8px rgba(0,0,0,0.07);
                    border:1px solid #E5E7EB; transition:transform 0.2s, box-shadow 0.2s;"
             @mouseenter="$event.currentTarget.style.transform='translateY(-2px)'"
             @mouseleave="$event.currentTarget.style.transform='translateY(0)'">

          <!-- Card Header -->
          <div style="background:linear-gradient(135deg, #8B0000, #BC0000);
                      padding:1.25rem; text-align:center; position:relative;">
            <!-- Available badge -->
            <div :style="donor.is_available
                   ? 'background:#DCFCE7; color:#166534;'
                   : 'background:#FEF2F2; color:#991B1B;'"
                 style="position:absolute; top:10px; right:10px;
                        padding:0.2rem 0.6rem; border-radius:999px;
                        font-size:0.7rem; font-weight:700;">
              {{ donor.is_available ? '● Available' : '● Unavailable' }}
            </div>

            <!-- Avatar -->
            <div style="width:56px; height:56px; background:rgba(255,255,255,0.2);
                        border-radius:50%; display:flex; align-items:center;
                        justify-content:center; font-size:1.5rem; font-weight:800;
                        color:white; margin:0 auto 0.6rem;
                        border:2px solid rgba(255,255,255,0.4);">
              {{ donor.name.charAt(0).toUpperCase() }}
            </div>

            <h3 style="color:white; font-size:0.95rem; font-weight:700;
                       margin:0 0 0.4rem; font-family:'Georgia',serif;">
              {{ donor.name }}
            </h3>

            <!-- Blood Group Badge -->
            <span style="background:white; color:#BC0000; padding:0.2rem 0.75rem;
                         border-radius:999px; font-size:0.85rem; font-weight:800;">
              🩸 {{ donor.blood_group?.name || 'N/A' }}
            </span>
          </div>

          <!-- Card Body -->
          <div style="padding:1rem;">

            <!-- Badge Level -->
            <div v-if="donor.badge"
                 style="background:#F9FAFB; border-radius:8px; padding:0.6rem;
                        text-align:center; margin-bottom:0.75rem;
                        font-size:0.82rem; font-weight:600; color:#374151;">
              {{ getBadgeIcon(donor.badge.level) }}
              {{ donor.badge.level.charAt(0).toUpperCase() + donor.badge.level.slice(1) }} Donor
              <span style="color:#9CA3AF; font-weight:400;">
                · {{ donor.badge.total_points }} pts
              </span>
            </div>

            <!-- Location -->
            <div style="font-size:0.82rem; color:#6B7280; margin-bottom:1rem;
                        display:flex; align-items:center; gap:5px;">
              <span>📍</span>
              <span>
                {{ donor.upazila?.name
                   ? donor.upazila.name + ', '
                   : '' }}{{ donor.district?.name || 'Location not set' }}
              </span>
            </div>

            <!-- View Profile Button -->
            <Link :href="`/donors/${donor.id}`"
                  style="display:block; text-align:center; padding:0.6rem;
                         background:#BC0000; color:white; border-radius:8px;
                         text-decoration:none; font-weight:600; font-size:0.875rem;">
              View Profile →
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else
           style="text-align:center; padding:4rem 2rem; background:white;
                  border-radius:12px; border:2px dashed #E5E7EB;">
        <div style="font-size:3.5rem; margin-bottom:1rem;">🔍</div>
        <h3 style="font-size:1.1rem; font-weight:700; color:#1a1a1a; margin:0 0 0.5rem;">
          No donors found
        </h3>
        <p style="color:#6B7280; font-size:0.875rem; margin:0 0 1.5rem;">
          Try changing the filters or check back later.
        </p>
        <button @click="clearFilters"
                style="background:#BC0000; color:white; padding:0.6rem 1.5rem;
                       border:none; border-radius:8px; font-weight:600; cursor:pointer;">
          Clear Filters
        </button>
      </div>

      <!-- ── Pagination ────────────────────────────────────────── -->
      <div v-if="donors.last_page > 1"
           style="display:flex; justify-content:center; gap:0.5rem;
                  margin-top:2rem; flex-wrap:wrap;">
        <template v-for="link in donors.links" :key="link.label">
          <Link v-if="link.url" :href="link.url"
                :style="link.active
                  ? 'background:#BC0000; color:white; border-color:#BC0000;'
                  : 'background:white; color:#374151; border-color:#D1D5DB;'"
                style="padding:0.5rem 0.85rem; border:1px solid;
                       border-radius:6px; text-decoration:none; font-size:0.875rem;"
                v-html="link.label">
          </Link>
          <span v-else
                :style="link.active
                  ? 'background:#BC0000; color:white;'
                  : 'background:#F3F4F6; color:#9CA3AF;'"
                style="padding:0.5rem 0.85rem; border-radius:6px;
                       font-size:0.875rem; border:1px solid #E5E7EB;"
                v-html="link.label">
          </span>
        </template>
      </div>

    </div>
  </div>
</template>