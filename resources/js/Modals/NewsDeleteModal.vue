<template>
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div
            class="relative w-full max-w-md mx-4 bg-white rounded-2xl shadow-2xl p-6"
            @click.stop
        >
            <!-- Close Button -->
            <button
                class="absolute top-3 right-3 text-gray-700 text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:text-black hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                @click="$emit('update:modelValue', false)"
                aria-label="Close modal"
            >
                &times;
            </button>

            <!-- Warning Icon -->
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                Delete News Article
            </h3>

            <!-- Message -->
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete "<strong>{{ news?.title }}</strong>"? This action cannot be undone.
            </p>

            <!-- News Preview -->
            <div v-if="news" class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <div class="flex space-x-2">
                        <!-- Featured Image 1 -->
                        <div>
                            <img v-if="news.featured_image"
                                 :src="`/news_image/${news.featured_image.replace('news/', '')}`"
                                 alt="Featured Image"
                                 class="w-16 h-12 object-cover rounded" />
                            <div v-else class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center">
                                <span class="text-gray-400 text-xs">No Image</span>
                            </div>
                        </div>
                        <!-- Featured Image 2 -->
                        <div>
                            <img v-if="news.featured_image_2"
                                 :src="`/news_image/${news.featured_image_2.replace('news/', '')}`"
                                 alt="Featured Image 2"
                                 class="w-16 h-12 object-cover rounded" />
                            <div v-else class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center">
                                <span class="text-gray-400 text-xs">No Image</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ news.title }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(news.published_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button
                    @click="$emit('update:modelValue', false)"
                    class="flex-1 bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-400 focus:ring-2 focus:ring-gray-500 focus:outline-none transition"
                >
                    Cancel
                </button>
                <button
                    @click="confirmDelete"
                    :disabled="processing"
                    class="flex-1 bg-red-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:outline-none transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="processing">Deleting...</span>
                    <span v-else>Delete</span>
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
    news: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'deleted']);

const processing = ref(false);

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

async function confirmDelete() {
    if (!props.news || !props.news.id) {
        Swal.fire({
            title: 'Error!',
            text: 'No news article selected for deletion.',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
        return;
    }

    processing.value = true;

    router.delete(`/news/${props.news.id}`, {
        onSuccess: () => {
            emit('update:modelValue', false);
            emit('deleted', props.news);
            Swal.fire({
                title: 'Success!',
                text: 'News article deleted successfully!',
                icon: 'success',
                confirmButtonColor: '#3085d6',
            }).then(() => {
                router.visit('/news');
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to delete news article. Please try again.',
                icon: 'error',
                confirmButtonColor: '#d33',
            });
        },
        onFinish: () => {
            processing.value = false;
        }
    });
}
</script>
