<script setup>
/**
 * Connections/Inbox.vue — Donor-এর Connection Request Inbox
 * ──────────────────────────────────────────────────────────
 * ডোনার এখানে তার কাছে আসা সব "Request to Connect" দেখতে পারবেন।
 * Accept বা Decline করতে পারবেন।
 *
 * Accept করলে:
 *   - উভয়ের phone number visible হবে
 *   - Requester-এর "Sent Requests" page-এ phone দেখাবে
 */

import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  pendingRequests:     Array,
  acceptedConnections: Array,
})

// Loading state while responding
const respondingId = ref(null)

// Accept বা Decline করো
const respond = (connectionId, action) => {
  respondingId.value = connectionId

  router.patch(`/connections/${connectionId}/respond`, { action }, {
    onFinish: () => { respondingId.value = null }
  })
}

// Date format helper
const formatDate = (d) =>
  new Date(d).toLocaleDateString('en-BD', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
</script>

<template>
  <Head title="Connection Inbox — Digital Blood Connect" />

  <div style="min-height:100vh; background:#F9FAFB; font-family:sans-serif;">

    <!-- Nav -->
    <nav style="background:#BC0000; padding:1rem 0; box-shadow:0 2px 10px rgba(188,0,0,0.3);">
      <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
        <Link href="/" style="color:white; text-decoration:none; font-weight:700;
                              font-family:'Georgia',serif; font-size:1.1rem;">
          🩸 Digital Blood Connect
        </Link>
        <Link href="/dashboard" style="color:rgba(255,255,255,0.85);
                                       text-decoration:none; font-size:0.875rem;">
          ← Dashboard
        </Link>
      </div>
    </nav>

    <!-- Header -->
    <div style="background:linear-gradient(to right,#8B0000,#BC0000); padding:2rem 0; color:white;">
      <div class="max-w-4xl mx-auto px-4">
        <h1 style="font-size:1.5rem; font-weight:700; margin:0 0 0.25rem;
                   font-family:'Georgia',serif;">
          📬 Connection Requests
        </h1>
        <p style="color:rgba(255,255,255,0.8); font-size:0.875rem; margin:0;">
          <strong>{{ pendingRequests.length }}</strong> pending request(s) waiting for your response
        </p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">

      <!-- ══════════════════════════════════════════════════
           PENDING REQUESTS
           ══════════════════════════════════════════════════ -->
      <h2 style="font-size:1rem; font-weight:700; color:#1a1a1a;
                 margin:0 0 1rem; display:flex; align-items:center; gap:8px;">
        ⏳ Pending Requests
        <span v-if="pendingRequests.length"
              style="background:#BC0000; color:white; font-size:0.72rem;
                     padding:0.15rem 0.55rem; border-radius:999px; font-weight:700;">
          {{ pendingRequests.length }}
        </span>
      </h2>

      <!-- Pending cards -->
      <div v-if="pendingRequests.length" class="flex flex-col gap-4 mb-8">
        <div v-for="req in pendingRequests" :key="req.id"
             style="background:white; border:1px solid #E5E7EB; border-radius:12px;
                    padding:1.25rem; border-left:4px solid #D97706;">

          <div style="display:flex; align-items:flex-start;
                      justify-content:space-between; gap:1rem; flex-wrap:wrap;">

            <!-- Requester info -->
            <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:200px;">
              <!-- Avatar -->
              <div style="width:48px; height:48px; background:#FEF3C7; border-radius:50%;
                          display:flex; align-items:center; justify-content:center;
                          font-size:1.25rem; font-weight:800; color:#D97706; flex-shrink:0;">
                {{ req.requester?.name?.charAt(0) }}
              </div>
              <div>
                <div style="font-weight:700; color:#1a1a1a; font-size:0.95rem;">
                  {{ req.requester?.name }}
                </div>
                <div style="font-size:0.8rem; color:#6B7280; margin-top:2px;">
                  Needs
                  <strong style="color:#BC0000;">
                    {{ req.blood_request?.blood_group?.name || req.requester?.blood_group?.name || '?' }}
                  </strong>
                  blood ·
                  {{ formatDate(req.created_at) }}
                </div>
                <!-- Hospital info if from a specific request -->
                <div v-if="req.blood_request?.hospital_name"
                     style="font-size:0.78rem; color:#6B7280; margin-top:2px;">
                  🏥 {{ req.blood_request.hospital_name }}
                </div>
              </div>
            </div>

            <!-- Accept / Decline buttons -->
            <div style="display:flex; gap:0.6rem; flex-shrink:0;">
              <button
                @click="respond(req.id, 'accept')"
                :disabled="respondingId === req.id"
                style="padding:0.55rem 1.1rem; background:#16A34A; color:white;
                       border:none; border-radius:8px; font-weight:700;
                       font-size:0.85rem; cursor:pointer;"
              >
                {{ respondingId === req.id ? '...' : '✅ Accept' }}
              </button>
              <button
                @click="respond(req.id, 'decline')"
                :disabled="respondingId === req.id"
                style="padding:0.55rem 1.1rem; background:#F3F4F6; color:#6B7280;
                       border:1px solid #E5E7EB; border-radius:8px; font-weight:600;
                       font-size:0.85rem; cursor:pointer;"
              >
                {{ respondingId === req.id ? '...' : '✕ Decline' }}
              </button>
            </div>
          </div>

          <!-- Message from requester (if provided) -->
          <div v-if="req.message"
               style="margin-top:0.85rem; background:#FFFBEB; border:1px solid #FDE68A;
                      border-radius:8px; padding:0.7rem 0.85rem;
                      font-size:0.83rem; color:#374151; line-height:1.6;">
            <strong style="color:#D97706;">💬 Their message:</strong>
            <br/>
            "{{ req.message }}"
          </div>
        </div>
      </div>

      <!-- Empty pending -->
      <div v-else
           style="background:white; border-radius:12px; border:2px dashed #E5E7EB;
                  text-align:center; padding:2.5rem; margin-bottom:2rem;">
        <div style="font-size:2.5rem; margin-bottom:0.5rem;">📭</div>
        <p style="color:#6B7280; font-size:0.9rem; margin:0;">
          No pending requests. Keep your profile updated to receive more requests!
        </p>
      </div>

      <!-- ══════════════════════════════════════════════════
           ACCEPTED CONNECTIONS
           ══════════════════════════════════════════════════ -->
      <h2 style="font-size:1rem; font-weight:700; color:#1a1a1a; margin:0 0 1rem;">
        ✅ Accepted Connections
      </h2>

      <div v-if="acceptedConnections.length" class="flex flex-col gap-3">
        <div v-for="conn in acceptedConnections" :key="conn.id"
             style="background:white; border:1px solid #E5E7EB; border-radius:10px;
                    padding:1rem; border-left:4px solid #16A34A;
                    display:flex; align-items:center; justify-content:space-between;
                    gap:1rem; flex-wrap:wrap;">

          <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:40px; height:40px; background:#DCFCE7; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        font-weight:800; color:#16A34A; font-size:1rem; flex-shrink:0;">
              {{ conn.requester?.name?.charAt(0) }}
            </div>
            <div>
              <div style="font-weight:600; color:#1a1a1a; font-size:0.9rem;">
                {{ conn.requester?.name }}
              </div>
              <div style="font-size:0.78rem; color:#6B7280;">
                Connected {{ formatDate(conn.responded_at) }}
              </div>
            </div>
          </div>

          <!-- Requester's phone — visible after acceptance -->
          <a v-if="conn.requester?.phone"
             :href="`tel:${conn.requester.phone}`"
             style="display:flex; align-items:center; gap:6px;
                    background:#DCFCE7; color:#166534; padding:0.45rem 0.85rem;
                    border-radius:8px; text-decoration:none;
                    font-weight:600; font-size:0.85rem; flex-shrink:0;">
            📞 {{ conn.requester.phone }}
          </a>
        </div>
      </div>

      <div v-else
           style="background:white; border-radius:10px; border:1px solid #E5E7EB;
                  text-align:center; padding:2rem;">
        <p style="color:#9CA3AF; font-size:0.875rem; margin:0;">
          No accepted connections yet.
        </p>
      </div>

    </div>
  </div>
</template>