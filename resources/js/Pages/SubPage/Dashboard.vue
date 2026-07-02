<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const VISITOR_STATS_URL = 'https://www.leadsagri.com/api/visitor-stats';
const REFRESH_INTERVAL_MS = 30_000;

const page = usePage();
const productsCount = computed(() => page.props.productsCount ?? 0);
const newsCount = computed(() => page.props.newsCount ?? 0);
const careersCount = computed(() => page.props.careersCount ?? 0);
const directoriesCount = computed(() => page.props.directoriesCount ?? 0);

const visitorStats = ref({
    total_visits: 0,
    unique_visitors: 0,
});
const statsLoading = ref(true);
const statsRefreshing = ref(false);
const lastUpdated = ref(null);
let refreshTimer = null;

const lastUpdatedLabel = computed(() => {
    if (!lastUpdated.value) return '';
    return lastUpdated.value.toLocaleTimeString('en-PH', {
        hour: 'numeric',
        minute: '2-digit',
    });
});

async function fetchVisitorStats({ silent = false } = {}) {
    if (!silent) {
        statsLoading.value = true;
    } else {
        statsRefreshing.value = true;
    }

    try {
        const res = await fetch(VISITOR_STATS_URL);
        if (!res.ok) throw new Error('Failed to fetch visitor stats');
        visitorStats.value = await res.json();
        lastUpdated.value = new Date();
    } catch {
        // Keep previous values on silent refresh failure
    } finally {
        statsLoading.value = false;
        statsRefreshing.value = false;
    }
}

function startAutoRefresh() {
    refreshTimer = setInterval(() => fetchVisitorStats({ silent: true }), REFRESH_INTERVAL_MS);
}

onMounted(() => {
    fetchVisitorStats();
    startAutoRefresh();
});

onUnmounted(() => {
    if (refreshTimer) clearInterval(refreshTimer);
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});

const todayFormatted = computed(() =>
    new Date().toLocaleDateString('en-PH', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
);

const contentStats = computed(() => [
    {
        label: 'Total Products',
        value: productsCount.value,
        icon: 'products',
        accent: 'from-blue-500 to-blue-600',
        bg: 'bg-blue-50',
        text: 'text-blue-600',
        ring: 'ring-blue-100',
    },
    {
        label: 'Total News',
        value: newsCount.value,
        icon: 'news',
        accent: 'from-emerald-500 to-brand-600',
        bg: 'bg-emerald-50',
        text: 'text-emerald-600',
        ring: 'ring-emerald-100',
    },
    {
        label: 'Total Careers',
        value: careersCount.value,
        icon: 'careers',
        accent: 'from-amber-500 to-orange-500',
        bg: 'bg-amber-50',
        text: 'text-amber-600',
        ring: 'ring-amber-100',
    },
    {
        label: 'Total Directories',
        value: directoriesCount.value,
        icon: 'folder',
        accent: 'from-purple-500 to-pink-500',
        bg: 'bg-purple-50',
        text: 'text-purple-600',
        ring: 'ring-purple-100',
    },
]);

const visitorCards = computed(() => [
    {
        label: 'Total Viewed',
        value: visitorStats.value.total_visits,
        description: 'All page views',
        icon: 'eye',
        accent: 'from-violet-500 to-purple-600',
        bg: 'bg-violet-50',
        text: 'text-violet-600',
        ring: 'ring-violet-100',
    },
    {
        label: 'Unique Visitors',
        value: visitorStats.value.unique_visitors,
        description: 'Distinct visitors',
        icon: 'users',
        accent: 'from-indigo-500 to-blue-600',
        bg: 'bg-indigo-50',
        text: 'text-indigo-600',
        ring: 'ring-indigo-100',
    },
]);

const userName = computed(() => page.props.auth?.user?.username || 'Admin');
</script>

<template>
    <SidebarLayout>
        <div class="animate-fade-in space-y-8">
            <!-- Welcome header -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-600 via-brand-500 to-emerald-500 p-6 sm:p-8 shadow-lg shadow-brand-600/20">
                <div class="pointer-events-none absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-12 -left-8 h-32 w-32 rounded-full bg-white/10 blur-xl" />
                <div class="relative">
                    <p class="text-sm font-medium text-emerald-100">{{ todayFormatted }}</p>
                    <h1 class="mt-1 text-2xl font-bold text-white sm:text-3xl">
                        {{ greeting }}, {{ userName }}!
                    </h1>
                    <p class="mt-2 max-w-xl text-sm text-emerald-50/90">
                        Welcome to your admin dashboard. Here's a quick overview of your content and website traffic.
                    </p>
                </div>
            </div>

            <!-- Content stats -->
            <section>
                <div class="mb-4 flex items-center gap-2">
                    <div class="h-5 w-1 rounded-full bg-brand-600" />
                    <h2 class="text-lg font-semibold text-slate-800">Content Overview</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="(stat, index) in contentStats"
                        :key="stat.label"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover animate-slide-up"
                        :style="{ animationDelay: `${index * 80}ms` }"
                    >
                        <div :class="['absolute inset-x-0 top-0 h-1 bg-gradient-to-r', stat.accent]" />
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">{{ stat.label }}</p>
                                <p class="mt-2 text-3xl font-bold text-slate-800">{{ stat.value }}</p>
                            </div>
                            <div :class="['flex h-12 w-12 items-center justify-center rounded-xl ring-4', stat.bg, stat.text, stat.ring]">
                                <svg v-if="stat.icon === 'products'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <svg v-else-if="stat.icon === 'news'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3v4a1 1 0 001 1h4" />
                                </svg>
                                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Visitor stats -->
            <section>
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="h-5 w-1 rounded-full bg-violet-500" />
                        <h2 class="text-lg font-semibold text-slate-800">Website Analytics</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            v-if="statsLoading"
                            class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500"
                        >
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-500" />
                            Loading...
                        </span>
                        <template v-else>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                <span
                                    :class="[
                                        'h-1.5 w-1.5 rounded-full',
                                        statsRefreshing ? 'animate-pulse bg-brand-500' : 'bg-emerald-500',
                                    ]"
                                />
                                Updated {{ lastUpdatedLabel }}
                            </span>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                                :disabled="statsRefreshing"
                                @click="fetchVisitorStats({ silent: true })"
                            >
                                <svg
                                    :class="['h-3.5 w-3.5', statsRefreshing ? 'animate-spin' : '']"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh
                            </button>
                        </template>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div
                        v-for="(stat, index) in visitorCards"
                        :key="stat.label"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover animate-slide-up"
                        :style="{ animationDelay: `${(index + 3) * 80}ms` }"
                    >
                        <div :class="['absolute inset-x-0 top-0 h-1 bg-gradient-to-r', stat.accent]" />
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">{{ stat.label }}</p>
                                <div v-if="statsLoading" class="mt-3 space-y-2">
                                    <div class="h-8 w-20 animate-pulse rounded-lg bg-slate-100" />
                                </div>
                                <p v-else class="mt-2 text-3xl font-bold text-slate-800">{{ stat.value }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ stat.description }}</p>
                            </div>
                            <div :class="['flex h-12 w-12 items-center justify-center rounded-xl ring-4', stat.bg, stat.text, stat.ring]">
                                <svg v-if="stat.icon === 'eye'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </SidebarLayout>
</template>
