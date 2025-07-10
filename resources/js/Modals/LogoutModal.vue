<template>
    <Modal :show="modelValue" @close="emit('update:modelValue', false)">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Confirm Logout</h2>
            <p class="mb-6">Are you sure you want to logout?</p>
            <div class="flex justify-end space-x-2">
                <button @click="emit('update:modelValue', false)" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button @click="confirmLogout" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Logout</button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';
const emit = defineEmits(['update:modelValue', 'confirm']);
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true
    }
});
function confirmLogout() {
    emit('confirm');
    emit('update:modelValue', false);
}
</script>
