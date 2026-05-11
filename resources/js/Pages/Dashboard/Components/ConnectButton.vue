<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    donorId: {
        type: Number,
        required: true
    },
    status: {
        type: String,
        default: 'none' // none, pending, accepted
    }
});

const form = useForm({
    donor_id: props.donorId
});

const isSent = ref(props.status === 'pending');

const sendRequest = () => {
    form.post(route('donor.connect'), {
        preserveScroll: true,
        onSuccess: () => {
            isSent.ref = true;
        },
    });
};
</script>

<template>
    <div class="connect-wrapper">
        <button 
            v-if="!isSent"
            @click="sendRequest"
            :disabled="form.processing"
            class="connect-btn"
        >
            <span v-if="form.processing">Sending...</span>
            <span v-else>🩸 Request Blood</span>
        </button>

        <button 
            v-else 
            disabled 
            class="sent-btn"
        >
            ⏳ Request Sent
        </button>
    </div>
</template>

<style scoped>
.connect-btn {
    background: #BC0000;
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.connect-btn:hover {
    background: #990000;
    transform: translateY(-2px);
}

.connect-btn:disabled {
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.sent-btn {
    background: #FEF2F2;
    color: #BC0000;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-weight: 600;
    border: 1px solid #FCA5A5;
    width: 100%;
    cursor: not-allowed;
}
</style>