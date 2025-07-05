<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div
            class="relative w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-6"
            @click.stop
        >
            <!-- Close Button -->
            <button
                class="absolute top-3 right-3 text-gray-400 text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 transition"
                @click="$emit('update:modelValue', false)"
                aria-label="Close modal"
            >
                &times;
            </button>

            <!-- Warning Icon -->
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>

            <!-- Modal Title -->
            <h2 class="text-xl font-bold text-center mb-2 text-gray-800">Delete Product</h2>

            <!-- Product Info -->
            <div v-if="product" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <img v-if="product.image1" :src="`/storage/${product.image1}`" alt="Product" class="w-12 h-12 object-cover rounded" />
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ product.description }}</p>
                        <p class="text-sm text-gray-600">{{ product.category }} - {{ product.type }}</p>
                    </div>
                </div>
            </div>

            <!-- Confirmation Message -->
            <p class="text-center text-gray-600 mb-6">
                Are you sure you want to delete this product? This action cannot be undone and will permanently remove the product and its associated images.
            </p>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button
                    @click="$emit('update:modelValue', false)"
                    class="flex-1 bg-gray-200 text-gray-800 font-semibold py-3 px-4 rounded-lg hover:bg-gray-300 focus:ring-2 focus:ring-gray-500 focus:outline-none transition"
                >
                    Cancel
                </button>
                <button
                    @click="confirmDelete"
                    :disabled="isDeleting"
                    class="flex-1 bg-red-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:outline-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isDeleting" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Deleting...
                    </span>
                    <span v-else>Delete Product</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    modelValue: Boolean,
    product: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'deleted']);

const isDeleting = ref(false);

const confirmDelete = async () => {
    if (!props.product || !props.product.id) {
        Swal.fire({
            title: 'Error!',
            text: 'No product selected for deletion.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/products/${props.product.id}`, {
            onSuccess: () => {
                emit('update:modelValue', false);
                emit('deleted', props.product);

                Swal.fire({
                    title: 'Success!',
                    text: 'Product deleted successfully!',
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    router.visit('/products');
                });
            },
            onError: (errors) => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete product. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                });
            },
            onFinish: () => {
                isDeleting.value = false;
            }
        });
    } catch (error) {
        isDeleting.value = false;
        Swal.fire({
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
    }
};
</script>
