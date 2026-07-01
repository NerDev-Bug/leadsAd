<template>
    <div class="flex min-h-screen bg-slate-50">
        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed z-40 inset-y-0 left-0 w-72 flex flex-col transform transition-transform duration-300 ease-out lg:static lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex flex-col h-full bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
                <!-- Brand -->
                <div class="px-5 pt-6 pb-5 border-b border-white/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 shadow-lg shadow-brand-600/30">
                                <img src="/logo-green-CYWgDgZa.png" alt="LAPC" class="h-7 w-7 object-contain brightness-0 invert" />
                            </div>
                            <div>
                                <p class="text-sm font-bold tracking-wide text-white">LAPC Admin</p>
                                <p class="text-xs text-slate-400">Management Panel</p>
                            </div>
                        </div>
                        <button
                            class="lg:hidden rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white transition-colors"
                            @click="sidebarOpen = false"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto px-3 py-5">
                    <p class="px-3 mb-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">Menu</p>
                    <ul class="space-y-1">
                        <li v-for="item in navItems" :key="item.href">
                            <Link
                                :href="item.href"
                                :class="[
                                    'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200',
                                    isActive(item.href)
                                        ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25'
                                        : 'text-slate-300 hover:bg-white/8 hover:text-white',
                                ]"
                                @click="sidebarOpen = false"
                            >
                                <span
                                    :class="[
                                        'flex h-9 w-9 items-center justify-center rounded-lg transition-colors',
                                        isActive(item.href)
                                            ? 'bg-white/20'
                                            : 'bg-white/5 group-hover:bg-white/10',
                                    ]"
                                >
                                    <component :is="item.icon" class="h-5 w-5" />
                                </span>
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <!-- User footer -->
                <div class="border-t border-white/10 p-4">
                    <div class="mb-3 flex items-center gap-3 rounded-xl bg-white/5 px-3 py-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600/80 text-sm font-bold text-white">
                            {{ userInitial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">{{ userName }}</p>
                            <p class="truncate text-xs text-slate-400">{{ userEmail }}</p>
                        </div>
                    </div>
                    <button
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-medium text-red-300 transition-all duration-200 hover:bg-red-500 hover:text-white hover:border-red-500 hover:shadow-md hover:shadow-red-500/20"
                        @click="logoutModalOpen = true"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex min-h-screen flex-1 flex-col overflow-x-hidden">
            <!-- Mobile header -->
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-md lg:hidden">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600">
                            <img src="/logo-green-CYWgDgZa.png" alt="LAPC" class="h-5 w-5 object-contain brightness-0 invert" />
                        </div>
                        <span class="text-base font-bold text-slate-800">LAPC Admin</span>
                    </div>
                    <button
                        class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 transition-colors"
                        @click="sidebarOpen = true"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 bg-gradient-to-br from-slate-50 via-emerald-50/30 to-slate-100/80">
                <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>

    <LogoutModal v-model="logoutModalOpen" @confirm="handleLogout" />
</template>

<script setup>
import { ref, computed, h } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import LogoutModal from '@/Modals/LogoutModal.vue';

const sidebarOpen = ref(false);
const logoutModalOpen = ref(false);

const page = usePage();
const currentPath = computed(() => (page.url || '').split('?')[0] || '/');

const user = computed(() => page.props.auth?.user);
const userName = computed(() => user.value?.username || 'Admin');
const userEmail = computed(() => user.value?.email || '');
const userInitial = computed(() => (userName.value.charAt(0) || 'A').toUpperCase());

const iconProps = { fill: 'none', stroke: 'currentColor', 'stroke-width': '1.75', viewBox: '0 0 24 24' };

const DashboardIcon = () => h('svg', iconProps, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z' }),
]);
const ProductsIcon = () => h('svg', iconProps, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3 7h18l-1.5 12.75A2.25 2.25 0 0117.25 22.5H6.75a2.25 2.25 0 01-2.25-2.25L3 7z' }),
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 7V5.25A3.75 3.75 0 0112.75 1.5h0A3.75 3.75 0 0116.5 5.25V7' }),
]);
const NewsIcon = () => h('svg', iconProps, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M12 6.75h6m-6 3h6m-6 3h6M4.5 6.75h3v3h-3v-3zm0 6h3v6h-3v-6z' }),
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3 4.5h18v15.75A1.5 1.5 0 0119.5 21.75h-15A1.5 1.5 0 013 20.25V4.5z' }),
]);
const CareersIcon = () => h('svg', iconProps, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a8 8 0 00-8 8h16a8 8 0 00-8-8z' }),
]);
const DirectoriesIcon = () => h('svg', iconProps, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M3.75 6.75h16.5m-16.5 3.75h16.5m-16.5 3.75h16.5m-16.5 3.75h16.5M9 6.75v10.5M15 6.75v10.5' }),
]);

const navItems = [
    { href: '/dashboard', label: 'Dashboard', icon: DashboardIcon },
    { href: '/products', label: 'Products', icon: ProductsIcon },
    { href: '/news', label: 'News', icon: NewsIcon },
    { href: '/careers', label: 'Careers', icon: CareersIcon },
    { href: '/directories', label: 'Directories', icon: DirectoriesIcon },
];

function isActive(path) {
    return currentPath.value === path || currentPath.value.startsWith(path + '/');
}

function handleLogout() {
    router.post('/access-logout', {}, {
        onFinish: () => {
            logoutModalOpen.value = false;
            router.visit('/');
        },
    });
}
</script>
