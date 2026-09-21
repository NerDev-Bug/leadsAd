<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="modelValue && user" class="admin-modal-overlay" @click.self="close">
                <div class="admin-modal-panel max-w-md" @click.stop>
                    <div class="admin-modal-header">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50">
                                <User class="h-5 w-5 text-green-700" />
                            </div>
                            <div>
                                <h2 class="admin-modal-title">Account details</h2>
                                <p class="admin-modal-subtitle">View user account information</p>
                            </div>
                        </div>
                        <button type="button" class="admin-modal-close" @click="close">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="admin-modal-body space-y-4 pb-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Full name</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ user.username || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                            <p class="mt-1 break-all text-sm font-medium text-slate-900">{{ user.email || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Role</p>
                            <p class="mt-1 text-sm font-medium capitalize text-slate-900">{{ user.role || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Member since</p>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ formatDate(user.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { User } from '@lucide/vue';

defineProps({
    modelValue: { type: Boolean, default: false },
    user: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue']);

function close() {
    emit('update:modelValue', false);
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>
