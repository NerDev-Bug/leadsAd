<template>
    <div v-if="pagination && pagination.total > 0" class="flex items-center justify-start">
        <nav class="inline-flex overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" aria-label="Pagination">
            <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="!pagination.has_previous_page"
                class="flex h-10 w-10 items-center justify-center border-r border-slate-200 text-slate-500 transition hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <span class="sr-only">Previous</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <template v-for="page in visiblePages" :key="page">
                <button
                    v-if="page !== '...'"
                    @click="goToPage(page)"
                    :class="[
                        'flex h-10 min-w-[2.5rem] items-center justify-center border-r border-slate-200 px-3 text-sm font-semibold transition last:border-r-0',
                        page === pagination.current_page
                            ? 'bg-brand-600 text-white shadow-inner'
                            : 'bg-white text-slate-600 hover:bg-brand-50 hover:text-brand-700',
                    ]"
                    :aria-current="page === pagination.current_page ? 'page' : undefined"
                >
                    {{ page }}
                </button>
                <span v-else class="flex h-10 w-10 items-center justify-center border-r border-slate-200 text-sm text-slate-400">…</span>
            </template>

            <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="!pagination.has_more_pages"
                class="flex h-10 w-10 items-center justify-center text-slate-500 transition hover:bg-brand-50 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-40"
            >
                <span class="sr-only">Next</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
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
        required: true,
    },
    routeName: {
        type: String,
        required: true,
    },
    extraParams: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['page-changed']);

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

    router.get(props.routeName, { ...props.extraParams, page }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });

    emit('page-changed', page);
};
</script>
