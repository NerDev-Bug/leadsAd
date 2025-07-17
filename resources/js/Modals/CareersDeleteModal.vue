<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-md mx-2 sm:mx-4 md:mx-0 bg-white rounded-2xl shadow-2xl p-6 flex flex-col overflow-y-auto" @click.stop>
            <!-- Close Button -->
            <button
                class="absolute top-3 right-3 text-gray-700 text-3xl w-10 h-10 flex items-center justify-center rounded-full hover:text-black hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                @click="$emit('update:modelValue', false)"
                aria-label="Close modal"
            >&times;</button>
            <h2 class="text-2xl font-bold text-center mb-4 text-gray-800">Delete Career Posting</h2>
            <div v-if="career" class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex flex-col gap-2">
                    <div>
                        <span class="font-semibold">Position:</span> {{ career.position }}
                    </div>
                    <div>
                        <span class="font-semibold">Employment Type:</span> {{ career.employment_type }}
                    </div>
                    <div>
                        <span class="font-semibold">Location:</span> {{ career.location }}
                    </div>
                </div>
            </div>
            <div class="text-center text-red-600 mb-6">
                Are you sure you want to delete this career posting? This action cannot be undone.
            </div>
            <div class="flex justify-center gap-4">
                <button @click="$emit('update:modelValue', false)"
                    class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition">
                    Cancel
                </button>
                <button @click="confirmDelete"
                    class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold transition" :disabled="isDeleting">
                    {{ isDeleting ? 'Deleting...' : 'Delete' }}
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
    career: Object
});
const emit = defineEmits(['update:modelValue']);

const isDeleting = ref(false);

const confirmDelete = async () => {
    if (!props.career || !props.career.id) {
        Swal.fire({
            title: 'Error!',
            text: 'No career selected for deletion.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
        return;
    }

    isDeleting.value = true;

    try {
        await router.delete(`/careers/${props.career.id}`, {
            onSuccess: () => {
                emit('update:modelValue', false);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Career posting deleted successfully!',
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                }).then(() => {
                    router.visit('/careers');
                });
            },
            onError: (errors) => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete career posting.',
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
