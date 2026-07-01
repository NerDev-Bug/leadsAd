<template>
    <AdminConfirmModal
        :model-value="modelValue"
        title="Delete Career Posting"
        message="Are you sure you want to delete this career posting? This action cannot be undone."
        confirm-label="Delete"
        :loading="isDeleting"
        @update:model-value="$emit('update:modelValue', $event)"
        @confirm="confirmDelete"
    >
        <template #preview>
            <div v-if="career" class="space-y-2 text-sm">
                <div><span class="font-semibold text-slate-700">Position:</span> {{ career.position }}</div>
                <div><span class="font-semibold text-slate-700">Type:</span> {{ career.employment_type }}</div>
                <div><span class="font-semibold text-slate-700">Location:</span> {{ career.location }}</div>
            </div>
        </template>
    </AdminConfirmModal>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminConfirmModal from '@/Components/Admin/AdminConfirmModal.vue';

const props = defineProps({ modelValue: Boolean, career: Object });
const emit = defineEmits(['update:modelValue']);

const isDeleting = ref(false);

const confirmDelete = async () => {
    if (!props.career?.id) {
        Swal.fire({ title: 'Error!', text: 'No career selected.', icon: 'error', confirmButtonColor: '#d33' });
        return;
    }
    isDeleting.value = true;
    try {
        await router.delete(`/careers/${props.career.id}`, {
            onSuccess: () => {
                emit('update:modelValue', false);
                Swal.fire({ title: 'Deleted!', text: 'Career posting deleted successfully!', icon: 'success', confirmButtonColor: '#057A31' })
                    .then(() => router.visit('/careers'));
            },
            onError: () => {
                Swal.fire({ title: 'Error!', text: 'Failed to delete career posting.', icon: 'error', confirmButtonColor: '#d33' });
            },
            onFinish: () => { isDeleting.value = false; },
        });
    } catch {
        isDeleting.value = false;
        Swal.fire({ title: 'Error!', text: 'An unexpected error occurred.', icon: 'error', confirmButtonColor: '#d33' });
    }
};
</script>
