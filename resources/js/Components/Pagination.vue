<template>
    <div v-if="pagination && pagination.total > 0" class="flex items-center justify-start">
        <nav class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm flex" aria-label="Pagination">
            <!-- Previous button -->
            <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="!pagination.has_previous_page"
                class="w-12 h-12 flex items-center justify-center rounded-l-lg text-gray-400 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300 border-r border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                <span class="sr-only">Previous</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Page numbers -->
            <template v-for="page in visiblePages" :key="page">
                <button
                    v-if="page !== '...'"
                    @click="goToPage(page)"
                    :class="[
                        'w-12 h-12 flex items-center justify-center text-lg font-semibold border-r border-gray-200 focus:outline-none transition',
                        page === pagination.current_page
                            ? 'bg-green-500 text-white shadow font-bold z-10'
                            : 'bg-white text-rose-500 hover:bg-green-50',
                        page === visiblePages[visiblePages.length-1] ? 'border-r-0' : ''
                    ]"
                    :aria-current="page === pagination.current_page ? 'page' : undefined"
                >
                    {{ page }}
                </button>
                <span v-else class="w-12 h-12 flex items-center justify-center text-lg text-gray-400 select-none">…</span>
            </template>

            <!-- Next button -->
            <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="!pagination.has_more_pages"
                class="w-12 h-12 flex items-center justify-center rounded-r-lg text-gray-400 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300 border-l border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                <span class="sr-only">Next</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </nav>
    </div>
</template>

<script setup>
import { computed, defineProps, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    },
    routeName: {   // ✅ dynamic route
        type: String,
        required: true
    },
    extraParams: { // ✅ optional for searches/filters
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['page-changed']);

// Compute visible page numbers with ellipsis
const visiblePages = computed(() => {
    if (!props.pagination) return [];

    const current = props.pagination.current_page;
    const last = props.pagination.last_page;
    const delta = 2;

    let start = Math.max(1, current - delta);
    let end = Math.min(last, current + delta);

    if (start > 1) start = Math.max(1, start - 1);
    if (end < last) end = Math.min(last, end + 1);

    const pages = [];

    if (start > 1) {
        pages.push(1);
        if (start > 2) pages.push('...');
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (end < last) {
        if (end < last - 1) pages.push('...');
        pages.push(last);
    }

    return pages;
});

const goToPage = (page) => {
    if (page < 1 || page > props.pagination.last_page || page === props.pagination.current_page) {
        return;
    }

    // ✅ Reusable for any resource
    router.get(props.routeName, { ...props.extraParams, page }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });

    emit('page-changed', page);
};
</script>
