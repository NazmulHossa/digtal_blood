<script setup>
import { computed } from 'vue'

const props = defineProps({
  badge: {
    type: Object,
    default: () => ({
      level: 'bronze',
      total_points: 0,
      donation_count: 0,
      progress_percentage: 0,
      next_level_points: 100,
      config: {
        label: 'Bronze Donor',
        icon: '🥉',
        color: '#CD7F32',
        bg: '#FDF3E7',
        description: 'Welcome to the community!',
      }
    })
  },
  timer: {
    type: Object,
    default: () => ({
      eligible: true,
      days_left: 0,
      days_since: 0,
      percentage: 100,
      message: 'Eligible to donate now!'
    })
  },
  compact: {
    type: Boolean,
    default: false
  }
})

// Progress bar width
const progressWidth = computed(() => `${props.badge?.progress_percentage ?? 0}%`)

// Countdown timer progress
const timerWidth = computed(() => `${props.timer?.percentage ?? 0}%`)

// Next badge level
const nextLevelName = computed(() => {
  const map = { bronze: 'Silver', silver: 'Gold', gold: 'Hero', hero: null }
  return map[props.badge?.level] ?? null
})

// Timer color based on eligibility
const timerColor = computed(() => props.timer?.eligible ? '#16A34A' : '#D97706')
</script>

<template>
  <div v-if="compact" class="compact-badge" :style="`background:${badge?.config?.bg}; border-color:${badge?.config?.color}30;`">
    <span style="font-size:1.1rem;">{{ badge?.config?.icon }}</span>
    <span :style="`color:${badge?.config?.color}; font-weight:700; font-size:0.75rem;`">{{ badge?.config?.label }}</span>
  </div>

  <div v-else class="badge-card">
    <div class="badge-header" :style="`background: linear-gradient(135deg, ${badge?.config?.color}22, ${badge?.config?.bg}); border-bottom: 2px solid ${badge?.config?.color}33;`">
      <div class="badge-icon-wrap" :style="`border-color:${badge?.config?.color}; box-shadow: 0 0 20px ${badge?.config?.color}40;`">
        <span style="font-size:2.5rem; line-height:1;">{{ badge?.config?.icon }}</span>
      </div>
      <div style="text-align:center;">
        <div class="badge-level-name" :style="`color:${badge?.config?.color};`">{{ badge?.config?.label }}</div>
        <div class="badge-description">{{ badge?.config?.description }}</div>
        <div class="badge-stats">
          <div class="badge-stat">
            <div class="badge-stat-value" :style="`color:${badge?.config?.color};`">{{ badge?.total_points ?? 0 }}</div>
            <div class="badge-stat-label">Total Points</div>
          </div>
          <div class="badge-stat-divider"></div>
          <div class="badge-stat">
            <div class="badge-stat-value" :style="`color:${badge?.config?.color};`">{{ badge?.donation_count ?? 0 }}</div>
            <div class="badge-stat-label">Donations</div>
          </div>
        </div>
      </div>
    </div>

    <div style="padding:1.25rem;">
      <div v-if="nextLevelName" style="margin-bottom:1.25rem;">
        <div class="progress-label">
          <span style="font-size:0.8rem; font-weight:600; color:#374151;">Progress to {{ nextLevelName }}</span>
          <span style="font-size:0.8rem; color:#6B7280;">{{ badge?.total_points }} / {{ badge?.next_level_points }} pts</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" :style="`width:${progressWidth}; background:${badge?.config?.color};`"></div>
        </div>
      </div>
      <div v-else class="max-level-msg">🏆 Maximum Level Reached — Community Hero!</div>

      <div class="timer-section" :style="timer?.eligible ? 'background:#F0FDF4; border-color:#86EFAC;' : 'background:#FFFBEB; border-color:#FCD34D;'">
        <div class="timer-header">
          <span style="font-size:1rem;">{{ timer?.eligible ? '✅' : '⏳' }}</span>
          <span style="font-weight:700; font-size:0.85rem;" :style="timer?.eligible ? 'color:#166534;' : 'color:#92400E;'">Donation Eligibility</span>
        </div>
        <p style="font-size:0.82rem; margin:0.4rem 0 0.75rem; color:#374151; line-height:1.5;">{{ timer?.message }}</p>
        <div v-if="!timer?.eligible">
          <div class="progress-track">
            <div class="progress-fill" :style="`width:${timerWidth}; background:${timerColor};`"></div>
          </div>
        </div>
        <div v-else class="eligible-msg">You can save a life today! 💪</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.compact-badge { display: inline-flex; align-items: center; gap: 5px; padding: 0.25rem 0.65rem; border-radius: 999px; border: 1px solid; }
.badge-card { background: white; border-radius: 14px; border: 1px solid #E5E7EB; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
.badge-header { padding: 1.75rem 1.25rem 1.25rem; display: flex; flex-direction: column; align-items: center; gap: 1rem; }
.badge-icon-wrap { width: 80px; height: 80px; border-radius: 50%; border: 3px solid; display: flex; align-items: center; justify-content: center; background: white; }
.badge-level-name { font-size: 1.2rem; font-weight: 800; margin-bottom: 0.25rem; }
.badge-description { font-size: 0.82rem; color: #6B7280; margin-bottom: 0.75rem; }
.badge-stats { display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.7); padding: 0.6rem 1.25rem; border-radius: 10px; }
.badge-stat-value { font-size: 1.4rem; font-weight: 800; line-height: 1; }
.badge-stat-label { font-size: 0.7rem; color: #6B7280; }
.badge-stat-divider { width: 1px; height: 30px; background: #E5E7EB; }
.progress-track { height: 10px; background: #F3F4F6; border-radius: 999px; overflow: hidden; }
.progress-fill { height: 100%; transition: width 1s ease-out; }
.timer-section { border: 1.5px solid; border-radius: 10px; padding: 1rem; }
.max-level-msg { text-align:center; padding:0.6rem; background:#FEF2F2; border-radius:8px; color:#BC0000; font-size:0.85rem; font-weight:600; }
.eligible-msg { text-align:center; padding:0.5rem; background:#DCFCE7; border-radius:6px; font-size:0.82rem; font-weight:600; color:#166534; }
</style>