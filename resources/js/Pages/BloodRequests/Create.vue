<script setup>
/**
 * ============================================================
 * BloodRequests/Create.vue — Emergency Blood Request Form
 * ============================================================
 *
 * This form allows authenticated users to post an emergency
 * blood request that appears publicly on the landing page
 * and the requests feed.
 *
 * VUE CONCEPT: useForm() from Inertia
 * ─────────────────────────────────────
 * useForm() is a powerful utility that:
 *   1. Holds form field values reactively
 *   2. Tracks .processing (loading state while submitting)
 *   3. Collects .errors from Laravel validation responses
 *   4. Provides .post(), .patch(), .delete() methods that
 *      submit via Inertia (no page reload!)
 *
 * Laravel returns validation errors in $response->errors,
 * and Inertia automatically maps them to form.errors.fieldName
 * ============================================================
 */

import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import axios from 'axios'

// ── Props from BloodRequestController@create ─────────────────
const props = defineProps({
  bloodGroups: Array,
  districts:   Array,
})

// ── useForm: The form state object ───────────────────────────
const form = useForm({
  blood_group_id: '',
  district_id:    '',
  upazila_id:     '',
  hospital_name:  '',
  address:        '',
  needed_by:      '',
  units_needed:   1,
  contact_phone:  '',
  notes:          '',
  is_urgent:      false,
})

// ── Cascading dropdown: district → upazila ───────────────────
const availableUpazilas = ref([])
const loadingUpazilas   = ref(false)

watch(() => form.district_id, async (newId) => {
  form.upazila_id         = ''
  availableUpazilas.value = []

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

// ── Submit the form ───────────────────────────────────────────
const submitRequest = () => {
  // form.post() sends POST /requests with form data
  // Laravel's BloodRequestController@store handles it
  form.post('/requests')
}
</script>

<template>
  <Head title="Request Blood — Digital Blood Connect" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- Navigation -->
    <nav style="background:#BC0000; padding:1rem 0; box-shadow:0 2px 10px rgba(188,0,0,0.3);">
      <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <Link href="/" style="color:white; text-decoration:none; font-size:1.1rem;
                              font-weight:700; font-family:'Georgia',serif;">
          🩸 Digital Blood Connect
        </Link>
        <Link href="/dashboard" style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.875rem;">
          ← My Dashboard
        </Link>
      </div>
    </nav>

    <!-- Header -->
    <div style="background:linear-gradient(135deg, #8B0000, #BC0000); padding:2rem 0; color:white;">
      <div class="max-w-4xl mx-auto px-4">
        <h1 style="font-size:1.75rem; font-weight:700; margin:0 0 0.25rem; font-family:'Georgia',serif;">
          🚨 Post Emergency Blood Request
        </h1>
        <p style="color:rgba(255,255,255,0.8); font-size:0.9rem; margin:0;">
          Your request will be visible publicly to help find donors faster.
        </p>
      </div>
    </div>

    <!-- Form -->
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div style="background:white; border-radius:12px; padding:2rem;
                  box-shadow:0 2px 12px rgba(0,0,0,0.08); border-top:4px solid #BC0000;">

        <!-- Urgent Toggle Banner -->
        <div @click="form.is_urgent = !form.is_urgent"
             :style="form.is_urgent
               ? 'background:#FEF2F2; border-color:#FECACA;'
               : 'background:#F9FAFB; border-color:#E5E7EB;'"
             style="border:2px solid; border-radius:10px; padding:1rem 1.25rem;
                    cursor:pointer; display:flex; align-items:center; justify-content:space-between;
                    margin-bottom:1.5rem; user-select:none;">
          <div>
            <div :style="form.is_urgent ? 'color:#991B1B;' : 'color:#374151;'"
                 style="font-weight:700; font-size:0.9rem;">
              🚨 Mark as Urgent Emergency
            </div>
            <div style="font-size:0.8rem; color:#6B7280; margin-top:2px;">
              Urgent requests appear at the top of the feed with a red badge
            </div>
          </div>
          <!-- Visual toggle -->
          <div :style="form.is_urgent ? 'background:#BC0000;' : 'background:#D1D5DB;'"
               style="width:44px; height:24px; border-radius:999px; position:relative; transition:background 0.3s;">
            <span :style="form.is_urgent ? 'left:22px;' : 'left:2px;'"
                  style="position:absolute; top:2px; width:20px; height:20px;
                         background:white; border-radius:50%; transition:left 0.3s;"></span>
          </div>
        </div>

        <!-- Form Grid -->
        <div class="grid sm:grid-cols-2 gap-5">

          <!-- Blood Group Required -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Blood Group Required *
            </label>
            <select v-model="form.blood_group_id"
                    :class="{ 'border-red-500': form.errors.blood_group_id }"
                    style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                           border-radius:8px; font-size:0.9rem; outline:none;">
              <option value="">Select blood group</option>
              <option v-for="group in bloodGroups" :key="group.id" :value="group.id">
                {{ group.name }}
              </option>
            </select>
            <p v-if="form.errors.blood_group_id" style="color:#BC0000; font-size:0.75rem; margin-top:2px;">
              {{ form.errors.blood_group_id }}
            </p>
          </div>

          <!-- Units Needed -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Units (Bags) Needed *
            </label>
            <input v-model.number="form.units_needed" type="number" min="1" max="10"
                   style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                          border-radius:8px; font-size:0.9rem; outline:none; box-sizing:border-box;" />
          </div>

          <!-- District -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              District *
            </label>
            <select v-model="form.district_id"
                    :class="{ 'border-red-500': form.errors.district_id }"
                    style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                           border-radius:8px; font-size:0.9rem; outline:none;">
              <option value="">Select district</option>
              <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>

          <!-- Upazila -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Upazila / Area
            </label>
            <select v-model="form.upazila_id"
                    :disabled="!form.district_id"
                    style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                           border-radius:8px; font-size:0.9rem; outline:none;">
              <option value="">{{ form.district_id ? 'All areas' : 'Select district first' }}</option>
              <option v-for="u in availableUpazilas" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>

          <!-- Hospital Name -->
          <div class="sm:col-span-2">
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Hospital / Clinic Name
            </label>
            <input v-model="form.hospital_name" type="text"
                   placeholder="e.g., Chittagong Medical College Hospital"
                   style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                          border-radius:8px; font-size:0.9rem; outline:none; box-sizing:border-box;" />
          </div>

          <!-- Contact Phone -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Contact Phone
            </label>
            <input v-model="form.contact_phone" type="tel"
                   placeholder="01XXXXXXXXX"
                   style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                          border-radius:8px; font-size:0.9rem; outline:none; box-sizing:border-box;" />
          </div>

          <!-- Needed By -->
          <div>
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Blood Needed By (Date & Time)
            </label>
            <input v-model="form.needed_by" type="datetime-local"
                   style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                          border-radius:8px; font-size:0.9rem; outline:none; box-sizing:border-box;" />
          </div>

          <!-- Notes -->
          <div class="sm:col-span-2">
            <label style="display:block; font-size:0.8rem; font-weight:700; color:#1a1a1a; margin-bottom:4px;">
              Additional Notes
            </label>
            <textarea v-model="form.notes" rows="3"
                      placeholder="Any additional information for potential donors..."
                      style="width:100%; padding:0.65rem 0.75rem; border:1.5px solid #D1D5DB;
                             border-radius:8px; font-size:0.9rem; outline:none; box-sizing:border-box;
                             resize:vertical;"></textarea>
          </div>
        </div>

        <!-- Submit -->
        <div style="margin-top:1.5rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
          <button @click="submitRequest"
                  :disabled="form.processing"
                  style="background:#BC0000; color:white; padding:0.75rem 2rem;
                         border:none; border-radius:8px; font-weight:700; font-size:1rem;
                         cursor:pointer; display:flex; align-items:center; gap:8px;">
            {{ form.processing ? '⏳ Posting...' : '🚨 Post Blood Request' }}
          </button>
          <Link href="/dashboard"
                style="color:#6B7280; text-decoration:none; font-size:0.9rem;">
            Cancel
          </Link>
        </div>

      </div>
    </div>
  </div>
</template>