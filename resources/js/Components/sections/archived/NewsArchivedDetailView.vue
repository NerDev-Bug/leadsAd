<template>
    <div>
        <button
            type="button"
            class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-brand-700"
            @click="$emit('back')"
        >
            <ArrowLeft class="h-4 w-4" />
            Back to Archived News
        </button>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span class="rounded-md bg-brand-600 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                        Archived
                    </span>
                    <span>Archived on {{ formatDate(item.created_at || item.published_at) }}</span>
                </div>

                <NewsArchivedCardActions
                    :busy="busy"
                    hide-view-details
                    @restore="$emit('restore')"
                    @edit="$emit('edit')"
                    @delete="$emit('delete')"
                />
            </div>

            <div class="grid min-w-0 grid-cols-1 gap-6 p-5 sm:p-6 lg:grid-cols-2 lg:gap-8">
                <div class="min-w-0">
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-slate-50">
                        <div v-if="images.length" class="relative aspect-[4/3]">
                            <img
                                :src="images[activeImage]"
                                :alt="item.title"
                                class="h-full w-full object-cover"
                            />
                            <button
                                v-if="images.length > 1"
                                type="button"
                                class="absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow hover:bg-white"
                                @click="prevImage"
                            >
                                <ChevronLeft class="h-5 w-5" />
                            </button>
                            <button
                                v-if="images.length > 1"
                                type="button"
                                class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow hover:bg-white"
                                @click="nextImage"
                            >
                                <ChevronRight class="h-5 w-5" />
                            </button>
                        </div>
                        <div
                            v-else
                            class="flex aspect-[4/3] items-center justify-center text-sm text-slate-500"
                        >
                            No Image
                        </div>
                    </div>

                    <div v-if="images.length > 1" class="mt-3 flex gap-2 overflow-x-auto pb-1">
                        <button
                            v-for="(src, index) in images"
                            :key="`${src}-${index}`"
                            type="button"
                            class="h-16 w-20 shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="index === activeImage ? 'border-brand-600' : 'border-transparent opacity-70 hover:opacity-100'"
                            @click="activeImage = index"
                        >
                            <img :src="src" alt="" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <div class="min-w-0 overflow-hidden">
                    <h2 class="break-words text-xl font-bold text-slate-900 sm:text-2xl">
                        {{ item.title }}
                    </h2>
                    <div
                        class="admin-scrollbar prose prose-sm prose-slate mt-3 max-h-44 max-w-none overflow-y-auto break-words pr-2 text-slate-600 [overflow-wrap:anywhere] sm:max-h-52 lg:max-h-72
                            [&_*]:max-w-full [&_code]:break-words [&_img]:h-auto [&_img]:max-w-full
                            [&_pre]:overflow-x-auto [&_table]:w-full [&_table]:table-fixed"
                        v-html="formattedContent"
                    />

                    <ul class="mt-6 space-y-3 border-t border-gray-100 pt-5 text-sm text-slate-700">
                        <li class="flex items-start gap-3">
                            <Calendar class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" />
                            <div class="min-w-0">
                                <p class="font-medium text-slate-500">Published Date</p>
                                <p class="break-words">{{ formatDate(item.published_at) || '—' }}</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <User class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" />
                            <div class="min-w-0">
                                <p class="font-medium text-slate-500">Published By</p>
                                <p class="break-words">{{ publishedBy }}</p>
                            </div>
                        </li>
                    </ul>

                    <div v-if="tags.length" class="mt-5">
                        <p class="mb-2 text-sm font-medium text-slate-500">Tags</p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in tags"
                                :key="tag"
                                class="rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700"
                            >
                                {{ tag }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="mx-5 mb-5 flex flex-col gap-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 sm:mx-6 sm:flex-row sm:items-center sm:justify-between sm:px-5"
            >
                <div class="flex min-w-0 gap-3 text-sm text-blue-900">
                    <Info class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />
                    <p class="break-words [overflow-wrap:anywhere]">
                        This news is archived. Archived news items are hidden from the public and can be restored if needed.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50 disabled:opacity-50"
                    :disabled="busy"
                    @click="$emit('restore')"
                >
                    Restore News
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    User,
    ChevronLeft,
    ChevronRight,
    Info,
} from '@lucide/vue';
import NewsArchivedCardActions from '@/Components/sections/archived/NewsArchivedCardActions.vue';

const props = defineProps({
    item: { type: Object, required: true },
    busy: { type: Boolean, default: false },
    imageUrl: { type: Function, required: true },
    formatDate: { type: Function, required: true },
});

defineEmits(['back', 'restore', 'edit', 'delete']);

const page = usePage();
const activeImage = ref(0);

const images = computed(() => {
    const list = [];
    if (props.item.featured_image) {
        list.push(props.imageUrl(props.item.featured_image));
    }
    if (props.item.featured_image_2) {
        props.item.featured_image_2.split(',').forEach((path) => {
            const trimmed = path.trim();
            if (trimmed) {
                list.push(props.imageUrl(trimmed));
            }
        });
    }
    return list;
});

const publishedBy = computed(() => page.props.auth?.user?.name || 'Admin');

const tags = computed(() => {
    if (Array.isArray(props.item.tags) && props.item.tags.length) {
        return props.item.tags;
    }
    if (typeof props.item.tags === 'string' && props.item.tags.trim()) {
        return props.item.tags.split(',').map((t) => t.trim()).filter(Boolean);
    }
    return [];
});

const formattedContent = computed(() => {
    if (!props.item.content || typeof props.item.content !== 'string') {
        return '';
    }

    if (/<[a-z][\s\S]*>/i.test(props.item.content)) {
        return props.item.content;
    }

    return props.item.content
        .split(':')
        .map((segment) => segment.trim())
        .filter(Boolean)
        .map((segment) => `<p class="mb-4">${segment}</p>`)
        .join('');
});

watch(
    () => props.item.id,
    () => {
        activeImage.value = 0;
    },
);

function prevImage() {
    if (!images.value.length) return;
    activeImage.value = (activeImage.value - 1 + images.value.length) % images.value.length;
}

function nextImage() {
    if (!images.value.length) return;
    activeImage.value = (activeImage.value + 1) % images.value.length;
}
</script>
