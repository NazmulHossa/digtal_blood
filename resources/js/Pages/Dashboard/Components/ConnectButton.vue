<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    donor: {
        type: Object,
        required: true
    },
    connectionStatus: {
        type: String,
        default: 'none' // none, pending, accepted
    },
    donorPhone: {
        type: String,
        default: null
    }
});

const form = useForm({
    donor_id: props.donor.id
});

const isSent = ref(props.connectionStatus === 'pending');

const sendRequest = () => {
    form.post(route('connections.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isSent.value = true;
        },
        onError: (errors) => {
            console.error('Connection error:', errors);
        }
    });
};
</script>

<template>
    <div class="connect-wrapper">
        <button 
            v-if="!isSent && !donorPhone"
            @click="sendRequest"
            :disabled="form.processing"
            class="connect-btn"
        >
            <span v-if="form.processing">Requesting...</span>
            <span v-else>🔗 Request to Connect</span>
        </button>

        <a 
            v-else-if="donorPhone"
            :href="`tel:${donorPhone}`"
            class="connect-btn"
            style="display:flex; align-items:center; justify-content:center; text-decoration:none;"
        >
            📞 Call {{ donor.name }}
        </a>

        <button 
            v-else 
            disabled 
            class="sent-btn"
        >
            ⏳ Request Pending
        </button>
    </div>
</template>

<style scoped>
.connect-wrapper {
    width: 100%;
}

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
    font-size: 0.95rem;
}

.connect-btn:hover:not(:disabled) {
    background: #990000;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(188, 0, 0, 0.3);
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
    font-size: 0.95rem;
}
</style>
