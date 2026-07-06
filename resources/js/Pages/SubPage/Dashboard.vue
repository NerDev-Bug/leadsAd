<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { computed, ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
);

const VISITOR_STATS_URL = 'https://www.leadsagri.com/api/visitor-stats';
const REFRESH_INTERVAL_MS = 30_000;

const page = usePage();
const productsCount = computed(() => page.props.productsCount ?? 0);
const newsCount = computed(() => page.props.newsCount ?? 0);
const careersCount = computed(() => page.props.careersCount ?? 0);
const directoriesCount = computed(() => page.props.directoriesCount ?? 0);

const visitorStats = ref({ visits: 0 });
const dailyRecords = ref([]);

function parseTodayVisits(today) {
    if (typeof today === 'number') return today;
    if (today && typeof today === 'object') return today.visits ?? 0;
    return 0;
}

function normalizeDaily(records) {
    return [...(records ?? [])]
        .map((row) => ({
            date: row.date,
            visits: row.total_visits ?? row.visits ?? 0,
        }))
        .sort((a, b) => a.date.localeCompare(b.date));
}

function formatDayLabel(dateStr) {
    const date = new Date(`${dateStr}T00:00:00`);
    return date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
}

function getWeekStart(dateStr) {
    const date = new Date(`${dateStr}T00:00:00`);
    const day = date.getDay();
    const diff = date.getDate() - day + (day === 0 ? -6 : 1);
    const monday = new Date(date);
    monday.setDate(diff);
    return monday.toISOString().slice(0, 10);
}

function chartFromDaily(period) {
    const records = normalizeDaily(dailyRecords.value);

    if (!records.length) {
        return { labels: [], data: [], title: '' };
    }

    if (period === 'days') {
        return {
            title: `Daily visitors (last ${records.length} days)`,
            labels: records.map((row) => formatDayLabel(row.date)),
            data: records.map((row) => row.visits),
        };
    }

    if (period === 'weeks') {
        const buckets = new Map();
        for (const row of records) {
            const key = getWeekStart(row.date);
            buckets.set(key, (buckets.get(key) ?? 0) + row.visits);
        }
        const sorted = [...buckets.entries()].sort((a, b) => a[0].localeCompare(b[0]));
        return {
            title: 'Weekly visitors',
            labels: sorted.map(([key]) => `Week of ${formatDayLabel(key)}`),
            data: sorted.map(([, visits]) => visits),
        };
    }

    if (period === 'months') {
        const buckets = new Map();
        for (const row of records) {
            const key = row.date.slice(0, 7);
            buckets.set(key, (buckets.get(key) ?? 0) + row.visits);
        }
        const sorted = [...buckets.entries()].sort((a, b) => a[0].localeCompare(b[0]));
        return {
            title: 'Monthly visitors',
            labels: sorted.map(([key]) => {
                const [year, month] = key.split('-');
                const date = new Date(Number(year), Number(month) - 1, 1);
                return date.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
            }),
            data: sorted.map(([, visits]) => visits),
        };
    }

    const buckets = new Map();
    for (const row of records) {
        const key = row.date.slice(0, 4);
        buckets.set(key, (buckets.get(key) ?? 0) + row.visits);
    }
    const sorted = [...buckets.entries()].sort((a, b) => a[0].localeCompare(b[0]));
    return {
        title: 'Yearly visitors',
        labels: sorted.map(([year]) => year),
        data: sorted.map(([, visits]) => visits),
    };
}

async function renderChartFromDaily() {
    const { labels, data, title } = chartFromDaily(activePeriod.value);
    chartTitle.value = title || 'Daily visitors';
    chartTotal.value = data.reduce((sum, value) => sum + value, 0);

    chartLoading.value = false;
    await nextTick();
    buildChart(labels, data);

    if (chartInstance) {
        chartInstance.resize();
    }
}

const statsLoading = ref(true);
const statsRefreshing = ref(false);
const lastUpdated = ref(null);
let refreshTimer = null;

const chartPeriods = [
    { id: 'days', label: 'Days' },
    { id: 'weeks', label: 'Weeks' },
    { id: 'months', label: 'Months' },
    { id: 'years', label: 'Years' },
];

const activePeriod = ref('days');
const chartLoading = ref(true);
const chartTitle = ref('Daily visitors');
const chartTotal = ref(0);
const chartCanvas = ref(null);
let chartInstance = null;

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
        chartLoading.value = true;
    } else {
        statsRefreshing.value = true;
    }

    try {
        const res = await fetch(VISITOR_STATS_URL);
        if (!res.ok) throw new Error('Failed to fetch visitor stats');
        const data = await res.json();
        if (!Array.isArray(data.daily)) throw new Error('Invalid visitor stats response');
        dailyRecords.value = data.daily;
        visitorStats.value = { visits: parseTodayVisits(data.today) };
        lastUpdated.value = new Date();
        await renderChartFromDaily();
    } catch {
        chartLoading.value = false;
        // Keep previous values on silent refresh failure
    } finally {
        statsLoading.value = false;
        statsRefreshing.value = false;
    }
}

function buildChart(labels, data) {
    if (!chartCanvas.value) return;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    const ctx = chartCanvas.value.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(5, 122, 49, 0.22)');
    gradient.addColorStop(1, 'rgba(5, 122, 49, 0.01)');

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Visitors',
                    data,
                    borderColor: '#057A31',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#057A31',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#057A31',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                    fill: true,
                    tension: 0.38,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        label: (ctx) => `${ctx.parsed.y.toLocaleString()} visitor${ctx.parsed.y === 1 ? '' : 's'}`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 8,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.15)',
                        drawBorder: false,
                    },
                    border: { display: false, dash: [4, 4] },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        padding: 8,
                        precision: 0,
                    },
                },
            },
        },
    });
}

function setChartPeriod(period) {
    if (activePeriod.value === period) return;
    activePeriod.value = period;
}

function startAutoRefresh() {
    refreshTimer = setInterval(() => fetchVisitorStats({ silent: true }), REFRESH_INTERVAL_MS);
}

watch(activePeriod, () => {
    renderChartFromDaily();
});

onMounted(() => {
    fetchVisitorStats();
    startAutoRefresh();
});

onUnmounted(() => {
    if (refreshTimer) clearInterval(refreshTimer);
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }
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
        label: 'Page Views Today',
        value: visitorStats.value.visits,
        description: `Resets tomorrow · ${todayFormatted.value}`,
        icon: 'eye',
        accent: 'from-violet-500 to-purple-600',
        bg: 'bg-violet-50',
        text: 'text-violet-600',
        ring: 'ring-violet-100',
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
                        <h2 class="text-lg font-semibold text-slate-800">Today's Visitors</h2>
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

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <!-- Today card -->
                    <div
                        v-for="(stat, index) in visitorCards"
                        :key="stat.label"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover animate-slide-up lg:col-span-1"
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 mt-4">
                    <!-- Visitor line chart -->
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-card lg:col-span-3">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-brand-500 to-emerald-500" />

                        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-800">Visitor Trends</h3>
                                <p class="mt-0.5 text-sm text-slate-500">{{ chartTitle }}</p>
                                <p v-if="!chartLoading && chartTotal > 0" class="mt-2 text-xs font-medium text-brand-600">
                                    {{ chartTotal.toLocaleString() }} total in this period
                                </p>
                            </div>

                            <div class="inline-flex shrink-0 self-start rounded-xl bg-slate-100 p-1">
                                <button
                                    v-for="period in chartPeriods"
                                    :key="period.id"
                                    type="button"
                                    :class="[
                                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200',
                                        activePeriod === period.id
                                            ? 'bg-white text-brand-700 shadow-sm ring-1 ring-slate-200/80'
                                            : 'text-slate-500 hover:text-slate-700',
                                    ]"
                                    @click="setChartPeriod(period.id)"
                                >
                                    {{ period.label }}
                                </button>
                            </div>
                        </div>

                        <div class="relative p-5 pt-4">
                            <div
                                v-if="chartLoading"
                                class="flex h-64 items-center justify-center rounded-xl bg-slate-50"
                            >
                                <div class="flex flex-col items-center gap-3">
                                    <div class="h-8 w-8 animate-spin rounded-full border-2 border-brand-200 border-t-brand-600" />
                                    <p class="text-xs font-medium text-slate-400">Loading chart...</p>
                                </div>
                            </div>

                            <div
                                v-if="!chartLoading"
                                class="relative h-64 w-full"
                            >
                                <canvas ref="chartCanvas" />
                            </div>

                            <div
                                v-if="!chartLoading && !dailyRecords.length"
                                class="absolute inset-0 flex items-center justify-center p-5"
                            >
                                <p class="text-sm text-slate-400">No visitor data available yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </SidebarLayout>
</template>
