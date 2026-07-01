<template>
    <AdminConfirmModal
        :model-value="modelValue"
        title="Delete News Article"
        :message="deleteMessage"
        confirm-label="Delete Article"
        :loading="isDeleting"
        @update:model-value="$emit('update:modelValue', $event)"
        @confirm="confirmDelete"
    >
        <template #preview>
            <div v-if="news" class="flex items-start gap-3">
                <img v-if="news.featured_image" :src="`/news_image/${news.featured_image.replace('news/', '')}`" alt="" class="h-14 w-20 rounded-xl object-cover ring-2 ring-white" />
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-800 truncate">{{ news.title }}</p>
                    <p class="text-xs text-slate-500">{{ formatDate(news.published_at) }}</p>
                </div>
            </div>
        </template>
    </AdminConfirmModal>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AdminConfirmModal from '@/Components/Admin/AdminConfirmModal.vue';

const props = defineProps({ modelValue: Boolean, news: { type: Object, default: null } });
const emit = defineEmits(['update:modelValue', 'deleted']);

const isDeleting = ref(false);

const deleteMessage = computed(() =>
    `Are you sure you want to delete "${props.news?.title || 'this article'}"? This action cannot be undone.`
);

function formatDate(date) {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

const confirmDelete = async () => {
    if (!props.news?.id) {
        Swal.fire({ title: 'Error!', text: 'No news article selected.', icon: 'error', confirmButtonColor: '#d33' });
        return;
    }
    isDeleting.value = true;
    try {
        await router.delete(`/news/${props.news.id}`, {
            onSuccess: () => {
                emit('update:modelValue', false);
                emit('deleted', props.news);
                Swal.fire({ title: 'Deleted!', text: 'News article deleted successfully!', icon: 'success', confirmButtonColor: '#057A31' })
                    .then(() => router.visit('/news'));
            },
            onError: () => {
                Swal.fire({ title: 'Error!', text: 'Failed to delete news article.', icon: 'error', confirmButtonColor: '#d33' });
            },
            onFinish: () => { isDeleting.value = false; },
        });
    } catch {
        isDeleting.value = false;
        Swal.fire({ title: 'Error!', text: 'An unexpected error occurred.', icon: 'error', confirmButtonColor: '#d33' });
    }
};
</script>
