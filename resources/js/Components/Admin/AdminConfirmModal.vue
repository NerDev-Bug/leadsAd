<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, required: true },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Delete' },
    loading: { type: Boolean, default: false },
    loadingLabel: { type: String, default: 'Deleting...' },
    variant: { type: String, default: 'danger' },
});

const emit = defineEmits(['update:modelValue', 'confirm']);

function close() {
    emit('update:modelValue', false);
}

function confirm() {
    emit('confirm');
}
</script>

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
            <div v-if="modelValue" class="admin-modal-overlay" @click.self="close">
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-4 scale-95"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 translate-y-4 scale-95"
                    appear
                >
                    <div v-if="modelValue" class="admin-modal-panel max-w-md" @click.stop>
                        <div class="admin-modal-confirm-body">
                            <div :class="variant === 'logout' ? 'admin-modal-logout-icon' : 'admin-modal-danger-icon'">
                                <svg v-if="variant === 'logout'" class="h-7 w-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <svg v-else class="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <h2 class="admin-modal-title text-center">{{ title }}</h2>
                            <p v-if="message" class="admin-modal-confirm-message">{{ message }}</p>
                            <div v-if="$slots.preview" class="admin-modal-preview">
                                <slot name="preview" />
                            </div>
                            <div class="admin-modal-actions">
                                <button type="button" class="admin-modal-btn-cancel" @click="close">Cancel</button>
                                <button
                                    type="button"
                                    :class="variant === 'logout' ? 'admin-modal-btn-logout' : 'admin-modal-btn-danger'"
                                    :disabled="loading"
                                    @click="confirm"
                                >
                                    <svg v-if="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    {{ loading ? loadingLabel : confirmLabel }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
