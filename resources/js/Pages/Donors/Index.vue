<script setup>
/**
 * Donors/Index.vue — Find Donors Page
 * ────────────────────────────────────
 * List all blood donors with search, filter, and sorting functionality.
 * Each donor card shows:
 *   - Badge level with icon
 *   - Blood group
 *   - Location (Upazila/District)
 *   - Availability status
 *   - Link to view full profile
 */

import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  donors: {
    type: Array,
    default: () => []
  }
})

const searchQuery = ref('')
const selectedBloodGroup = ref('')
const sortBy = ref('name')

const filteredDonors = computed(() => {
  let result = [...props.donors]

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(donor => 
      donor.name.toLowerCase().includes(query) ||
      donor.upazila?.name.toLowerCase().includes(query) ||
      donor.district?.name.toLowerCase().includes(query)
    )
  }

  // Filter by blood group
  if (selectedBloodGroup.value) {
    result = result.filter(donor => 
      donor.blood_group?.id === parseInt(selectedBloodGroup.value)
    )
  }

  // Sort
  result.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.name.localeCompare(b.name)
      case 'availability':
        return b.is_available - a.is_available
      case 'points':
        return (b.badge?.total_points || 0) - (a.badge?.total_points || 0)
      default:
        return 0
    }
  })

  return result
})

const getBadgeIcon = (level) => {
  const icons = {
    bronze: '🥉',
    silver: '🥈',
    gold: '🥇',
    hero: '🦸'
  }
  return icons[level] || '🥉'
}
</script>

<template>
  <Head title="Find Blood Donors — Digital Blood Connect" />

  <div style="min-height:100vh; background:#FAFAFA; font-family:sans-serif;">

    <!-- Navigation -->
    <nav style="background:#BC0000; padding:1rem 0; box-shadow:0 2px 10px rgba(188,0,0,0.4); position:sticky; top:0; z-index:50;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <Link href="/" style="color:white; text-decoration:none; font-weight:700; font-family:'Georgia',serif; font-size:1.1rem;">
            🩸 Digital Blood Connect
          </Link>
          <div class="hidden md:flex items-center gap-8">
            <Link href="/donors" style="color:white; text-decoration:none; font-weight:600;">Find Donors</Link>
            <Link href="/requests" style="color:rgba(255,255,255,0.9); text-decoration:none;">Blood Requests</Link>
            <Link v-if="!$page.props.auth?.user" href="/register" style="background:white; color:#BC0000; padding:0.5rem 1.25rem; border-radius:6px; text-decoration:none; font-weight:600;">Register</Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section style="background:linear-gradient(135deg, #8B0000, #BC0000); padding:3rem 0; color:white; text-align:center;">
      <div class="max-w-7xl mx-auto px-4">
        <h1 style="font-size:2.5rem; font-weight:700; margin-bottom:1rem; font-family:'Georgia',serif;">
          Find Registered Blood Donors
        </h1>
        <p style="font-size:1.1rem; opacity:0.95; margin-bottom:2rem;">
          Search for donors in your area. Connect safely with verified donors across Chittagong.
        </p>
      </div>
    </section>

    <!-- Search & Filter Section -->
    <div style="background:white; padding:2rem 0; border-bottom:1px solid #EEE;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-4 items-end">
          <!-- Search Input -->
          <div>
            <label style="display:block; font-weight:600; color:#374151; margin-bottom:0.5rem; font-size:0.9rem;">
              🔍 Search
            </label>
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Name, location..."
              style="width:100%; padding:0.75rem; border:1px solid #E5E7EB; border-radius:8px; font-size:0.95rem;"
            />
          </div>

          <!-- Blood Group Filter -->
          <div>
            <label style="display:block; font-weight:600; color:#374151; margin-bottom:0.5rem; font-size:0.9rem;">
              🩸 Blood Group
            </label>
            <select 
              v-model="selectedBloodGroup"
              style="width:100%; padding:0.75rem; border:1px solid #E5E7EB; border-radius:8px; font-size:0.95rem;"
            >
              <option value="">All Groups</option>
              <option value="1">O+</option>
              <option value="2">O-</option>
              <option value="3">A+</option>
              <option value="4">A-</option>
              <option value="5">B+</option>
              <option value="6">B-</option>
              <option value="7">AB+</option>
              <option value="8">AB-</option>
            </select>
          </div>

          <!-- Sort By -->
          <div>
            <label style="display:block; font-weight:600; color:#374151; margin-bottom:0.5rem; font-size:0.9rem;">
              ⬆️ Sort By
            </label>
            <select 
              v-model="sortBy"
              style="width:100%; padding:0.75rem; border:1px solid #E5E7EB; border-radius:8px; font-size:0.95rem;"
            >
              <option value="name">Name (A-Z)</option>
              <option value="availability">Available First</option>
              <option value="points">Highest Points</option>
            </select>
          </div>
        </div>

        <!-- Results Count -->
        <div style="margin-top:1.5rem; color:#6B7280; font-size:0.95rem;">
          Showing {{ filteredDonors.length }} of {{ donors.length }} donors
        </div>
      </div>
    </div>

    <!-- Donors Grid -->
    <section style="padding:3rem 0;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div v-if="filteredDonors.length === 0" style="text-align:center; padding:3rem; background:white; border-radius:12px;">
          <div style="font-size:3rem; margin-bottom:1rem;">🔍</div>
          <h3 style="font-size:1.25rem; font-weight:700; color:#374151; margin-bottom:0.5rem;">No donors found</h3>
          <p style="color:#6B7280;">Try adjusting your search filters</p>
        </div>

        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link 
            v-for="donor in filteredDonors" 
            :key="donor.id"
            :href="`/donors/${donor.id}`"
            style="text-decoration:none; color:inherit;"
          >
            <div style="background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); transition:all 0.3s ease; border:1px solid #E5E7EB;"
                 class="hover:shadow-lg hover:border-red-400 cursor-pointer">

              <!-- Header -->
              <div style="background:linear-gradient(135deg, #8B0000, #BC0000); padding:1.5rem; text-align:center; color:white;">
                <div style="font-size:2rem; margin-bottom:0.5rem;">
                  {{ donor.name.charAt(0).toUpperCase() }}
                </div>
                <h3 style="font-size:1.1rem; font-weight:700; margin:0; font-family:'Georgia',serif;">
                  {{ donor.name }}
                </h3>
                <div style="margin-top:0.5rem; font-size:0.9rem; opacity:0.9;">
                  🩸 {{ donor.blood_group?.name || 'N/A' }}
                </div>
              </div>

              <!-- Body -->
              <div style="padding:1.25rem;">

                <!-- Badge -->
                <div v-if="donor.badge" style="background:#F9FAFB; border-radius:8px; padding:0.75rem; margin-bottom:1rem; text-align:center; font-size:0.85rem;">
                  <span style="font-size:1.2rem;">{{ getBadgeIcon(donor.badge.level) }}</span>
                  <span style="margin-left:0.5rem; font-weight:600; color:#374151;">
                    {{ donor.badge.level.charAt(0).toUpperCase() + donor.badge.level.slice(1) }} Donor
                  </span>
                </div>

                <!-- Status Badges -->
                <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1rem;">
                  <div :style="donor.is_available ? 'background:#F0FDF4; color:#166534;' : 'background:#FEF2F2; color:#991B1B;'"
                       style="padding:0.5rem; border-radius:6px; font-size:0.85rem; font-weight:600; text-align:center;">
                    {{ donor.is_available ? '✅ Available' : '❌ Not Available' }}
                  </div>
                </div>

                <!-- Location -->
                <div style="font-size:0.85rem; color:#6B7280; margin-bottom:1rem;">
                  <div v-if="donor.upazila" style="margin-bottom:0.3rem;">
                    📍 {{ donor.upazila.name }}
                  </div>
                  <div v-if="donor.district">
                    {{ donor.district.name }}
                  </div>
                </div>

                <!-- Points -->
                <div style="background:#FEF2F2; padding:0.75rem; border-radius:8px; text-align:center;">
                  <div style="font-size:1.25rem; font-weight:700; color:#BC0000;">
                    {{ donor.badge?.total_points || 0 }}
                  </div>
                  <div style="font-size:0.75rem; color:#6B7280; margin-top:0.25rem;">
                    Points Earned
                  </div>
                </div>

              </div>
            </div>
          </Link>
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
