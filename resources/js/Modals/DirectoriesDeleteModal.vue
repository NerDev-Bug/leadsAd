<template>
    <AdminConfirmModal
        :model-value="modelValue"
        title="Delete Directory Entry"
        message="Are you sure you want to delete this directory entry? This action cannot be undone."
        confirm-label="Delete"
        :loading="isDeleting"
        @update:model-value="$emit('update:modelValue', $event)"
        @confirm="confirmDelete"
    >
        <template #preview>
            <div v-if="directory" class="space-y-2 text-sm">
                <div><span class="font-semibold text-slate-700">Business:</span> {{ directory.business_name }}</div>
                <div><span class="font-semibold text-slate-700">Location:</span> {{ directory.place }}, {{ directory.region }}</div>
                <div><span class="font-semibold text-slate-700">Contact:</span> {{ directory.contact_name }} — {{ directory.contact_no }}</div>
            </div>
        </template>
    </AdminConfirmModal>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminConfirmModal from '@/Components/Admin/AdminConfirmModal.vue';

const props = defineProps({ modelValue: Boolean, directory: Object });
const emit = defineEmits(['update:modelValue']);

const isDeleting = ref(false);

const confirmDelete = async () => {
    if (!props.directory?.id) {
        Swal.fire({ title: 'Error!', text: 'No directory selected.', icon: 'error', confirmButtonColor: '#d33' });
        return;
    }
    isDeleting.value = true;
    try {
        await router.delete(`/directories/${props.directory.id}`, {
            onSuccess: () => {
                emit('update:modelValue', false);
                Swal.fire({ title: 'Deleted!', text: 'Directory entry deleted successfully!', icon: 'success', confirmButtonColor: '#057A31' })
                    .then(() => router.visit('/directories'));
            },
            onError: () => {
                Swal.fire({ title: 'Error!', text: 'Failed to delete directory entry.', icon: 'error', confirmButtonColor: '#d33' });
            },
            onFinish: () => { isDeleting.value = false; },
        });
    } catch {
        isDeleting.value = false;
        Swal.fire({ title: 'Error!', text: 'An unexpected error occurred.', icon: 'error', confirmButtonColor: '#d33' });
    }
};
</script>
