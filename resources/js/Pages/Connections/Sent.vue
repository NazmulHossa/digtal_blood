<script setup>
/**
 * Connections/Sent.vue — Requester-এর পাঠানো Requests
 * ──────────────────────────────────────────────────────
 * Recipient তার পাঠানো সব connection requests এখানে দেখবে।
 * Accepted হলে donor-এর phone দেখা যাবে।
 */
import { Head, Link } from '@inertiajs/vue3'

defineProps({ connections: Object })

const statusConfig = (s) => ({
  pending:  { label:'⏳ Pending',  style:'background:#FEF3C7; color:#92400E;' },
  accepted: { label:'✅ Accepted', style:'background:#DCFCE7; color:#166534;' },
  declined: { label:'❌ Declined', style:'background:#F3F4F6; color:#6B7280;' },
}[s] ?? { label: s, style: '' })
</script>

<template>
  <Head title="Sent Requests — Digital Blood Connect" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <nav style="background:#BC0000; padding:1rem 0; box-shadow:0 2px 10px rgba(188,0,0,0.3);">
      <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <Link href="/" style="color:white; text-decoration:none; font-weight:700;
                              font-family:'Georgia',serif;">🩸 Digital Blood Connect</Link>
        <Link href="/dashboard" style="color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.875rem;">
          ← Dashboard
        </Link>
      </div>
    </nav>

    <div style="background:linear-gradient(to right,#8B0000,#BC0000); padding:2rem 0; color:white;">
      <div class="max-w-4xl mx-auto px-4">
        <h1 style="font-size:1.5rem; font-weight:700; margin:0; font-family:'Georgia',serif;">
          📤 My Sent Requests
        </h1>
        <p style="color:rgba(255,255,255,0.8); font-size:0.875rem; margin:0.25rem 0 0;">
          Track the status of your connection requests to donors
        </p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">

      <div v-if="connections.data?.length" class="flex flex-col gap-4">
        <div v-for="conn in connections.data" :key="conn.id"
             style="background:white; border:1px solid #E5E7EB; border-radius:12px; padding:1.25rem;">

          <div style="display:flex; align-items:center; gap:1rem;
                      justify-content:space-between; flex-wrap:wrap;">

            <!-- Donor info -->
            <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:200px;">
              <div style="width:48px; height:48px; background:#FEF2F2; border-radius:50%;
                          display:flex; align-items:center; justify-content:center;
                          font-weight:800; color:#BC0000; font-size:1.25rem; flex-shrink:0;">
                {{ conn.donor?.name?.charAt(0) }}
              </div>
              <div>
                <div style="font-weight:700; color:#1a1a1a; font-size:0.9rem;">
                  {{ conn.donor?.name }}
                </div>
                <div style="font-size:0.78rem; color:#6B7280; margin-top:2px;">
                  <span style="background:#BC0000; color:white; padding:0.1rem 0.4rem;
                               border-radius:999px; font-weight:700; font-size:0.72rem; margin-right:6px;">
                    {{ conn.donor?.blood_group?.name }}
                  </span>
                  {{ conn.donor?.upazila?.name || conn.donor?.district?.name || 'Chittagong' }}
                </div>
              </div>
            </div>

            <!-- Status + Action -->
            <div style="display:flex; align-items:center; gap:0.75rem; flex-shrink:0;">
              <span :style="statusConfig(conn.status).style"
                    style="padding:0.25rem 0.75rem; border-radius:999px;
                           font-size:0.78rem; font-weight:600;">
                {{ statusConfig(conn.status).label }}
              </span>

              <!-- Accepted: show call button -->
              <a v-if="conn.status === 'accepted' && conn.donor?.phone"
                 :href="`tel:${conn.donor.phone}`"
                 style="background:#16A34A; color:white; padding:0.45rem 0.85rem;
                        border-radius:8px; text-decoration:none; font-weight:600;
                        font-size:0.82rem; display:flex; align-items:center; gap:5px;">
                📞 {{ conn.donor.phone }}
              </a>

              <!-- View donor profile -->
              <Link :href="`/donors/${conn.donor_id}`"
                    style="color:#BC0000; font-size:0.8rem; text-decoration:none; font-weight:600;">
                View Profile →
              </Link>
            </div>
          </div>

          <!-- Sent message -->
          <div v-if="conn.message"
               style="margin-top:0.75rem; background:#F9FAFB; border-radius:8px;
                      padding:0.65rem 0.85rem; font-size:0.8rem; color:#555; line-height:1.5;">
            <strong style="color:#374151;">Your message:</strong> "{{ conn.message }}"
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else
           style="background:white; border-radius:12px; border:2px dashed #E5E7EB;
                  text-align:center; padding:3rem;">
        <div style="font-size:2.5rem; margin-bottom:0.5rem;">📭</div>
        <p style="color:#6B7280; font-size:0.9rem; margin:0 0 1rem;">
          You haven't sent any connection requests yet.
        </p>
        <Link href="/donors"
              style="background:#BC0000; color:white; padding:0.6rem 1.5rem;
                     border-radius:8px; text-decoration:none; font-weight:600; font-size:0.875rem;">
          Find Donors
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="connections.last_page > 1"
           style="display:flex; justify-content:center; gap:0.5rem; margin-top:1.5rem;">
        <template v-for="link in connections.links" :key="link.label">
          <Link v-if="link.url" :href="link.url"
                :style="link.active ? 'background:#BC0000; color:white;' : 'background:white; color:#374151;'"
                style="padding:0.5rem 0.85rem; border:1px solid #D1D5DB; border-radius:6px;
                       text-decoration:none; font-size:0.875rem;"
                v-html="link.label" />
        </template>
      </div>

    </div>
  </div>
</template>