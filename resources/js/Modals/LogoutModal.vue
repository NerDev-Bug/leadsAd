<template>
    <AdminConfirmModal
        :model-value="modelValue"
        title="Confirm Logout"
        message="Are you sure you want to end your session?"
        confirm-label="Logout"
        variant="logout"
        loading-label="Logging out..."
        @update:model-value="emit('update:modelValue', $event)"
        @confirm="confirmLogout"
    />
</template>

<script setup>
import AdminConfirmModal from '@/Components/Admin/AdminConfirmModal.vue';

const emit = defineEmits(['update:modelValue', 'confirm']);
defineProps({ modelValue: { type: Boolean, required: true } });

function confirmLogout() {
    emit('confirm');
    emit('update:modelValue', false);
}
</script>
