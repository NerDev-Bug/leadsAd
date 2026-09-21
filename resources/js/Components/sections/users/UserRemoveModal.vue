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
                    <div class="admin-modal-confirm-body">
                        <div class="admin-modal-danger-icon">
                            <svg class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>

                        <h2 class="admin-modal-title text-center">Remove account</h2>
                        <p class="admin-modal-confirm-message">
                            You are about to permanently remove
                            <span class="font-semibold text-slate-800">{{ user.username }}</span>
                            ({{ user.email }}). This action cannot be undone. Enter your admin password to confirm.
                        </p>

                        <div class="w-full text-left">
                            <label for="admin-confirm-password" class="mb-1.5 block text-sm font-semibold text-slate-700">
                                Your password
                            </label>
                            <input
                                id="admin-confirm-password"
                                v-model="password"
                                type="password"
                                autocomplete="current-password"
                                class="h-10 w-full rounded-xl border border-gray-200 px-4 text-sm text-slate-800 outline-none transition focus:border-brand-600 focus:ring-2 focus:ring-brand-600/20"
                                placeholder="Enter your password"
                                :disabled="loading"
                                @keyup.enter="submit"
                            />
                            <p v-if="error" class="mt-1.5 text-xs font-medium text-red-600">{{ error }}</p>
                        </div>

                        <div class="admin-modal-actions mt-6 pt-1">
                            <button type="button" class="admin-modal-btn-cancel" :disabled="loading" @click="close">
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="admin-modal-btn-danger"
                                :disabled="loading || !password.trim()"
                                @click="submit"
                            >
                                <svg v-if="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                {{ loading ? 'Removing...' : 'Remove account' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    user: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'confirm']);

const password = ref('');

watch(
    () => props.modelValue,
    (open) => {
        if (open) {
            password.value = '';
        }
    },
);

function close() {
    if (props.loading) return;
    password.value = '';
    emit('update:modelValue', false);
}

function submit() {
    if (!password.value.trim() || props.loading) return;
    emit('confirm', password.value);
}
</script>
