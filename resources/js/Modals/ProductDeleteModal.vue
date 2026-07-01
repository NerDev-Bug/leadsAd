<template>
    <AdminConfirmModal
        :model-value="modelValue"
        title="Delete Product"
        message="Are you sure you want to delete this product? This action cannot be undone and will permanently remove the product and its associated images."
        confirm-label="Delete Product"
        :loading="isDeleting"
        loading-label="Deleting..."
        @update:model-value="$emit('update:modelValue', $event)"
        @confirm="confirmDelete"
    >
        <template #preview>
            <div v-if="product" class="flex items-center gap-3">
                <img v-if="product.image1" :src="`/products_image/${product.image1.replace('products/', '')}`" alt="Product" class="admin-table-image" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-800">{{ product.description }}</p>
                    <p class="text-sm text-slate-500">{{ product.category }} — {{ product.type }}</p>
                </div>
            </div>
        </template>
    </AdminConfirmModal>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminConfirmModal from '@/Components/Admin/AdminConfirmModal.vue';

const props = defineProps({
    modelValue: Boolean,
    product: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'deleted']);

const isDeleting = ref(false);

const confirmDelete = async () => {
    if (!props.product?.id) {
        Swal.fire({ title: 'Error!', text: 'No product selected for deletion.', icon: 'error', confirmButtonColor: '#d33' });
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/products/${props.product.id}`, {
            onSuccess: () => {
                emit('update:modelValue', false);
                emit('deleted', props.product);
                Swal.fire({ title: 'Success!', text: 'Product deleted successfully!', icon: 'success', confirmButtonColor: '#057A31' })
                    .then(() => router.visit('/products'));
            },
            onError: () => {
                Swal.fire({ title: 'Error!', text: 'Failed to delete product. Please try again.', icon: 'error', confirmButtonColor: '#d33' });
            },
            onFinish: () => { isDeleting.value = false; },
        });
    } catch {
        isDeleting.value = false;
        Swal.fire({ title: 'Error!', text: 'An unexpected error occurred.', icon: 'error', confirmButtonColor: '#d33' });
    }
};
</script>
