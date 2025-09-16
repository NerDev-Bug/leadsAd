<template>
    <div class="flex min-h-screen">
        <!-- Sidebar overlay for mobile and tablet -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black bg-opacity-40 lg:hidden"
            @click="sidebarOpen = false"></div>
        <!-- Sidebar -->
        <aside :class="[
            'fixed z-40 inset-y-0 left-0 w-64 bg-gray-800 text-white flex flex-col p-4 transform transition-transform duration-200 lg:static lg:translate-x-0 lg:flex',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            'lg:relative lg:translate-x-0 lg:w-64 lg:min-h-screen'
        ]">
            <div class="text-2xl font-bold mb-8 flex items-center justify-between">
                LAPC admin Side
                <hr>
                <button class="lg:hidden text-white text-2xl focus:outline-none" @click="sidebarOpen = false">
                    &times;
                </button>
            </div>
            <nav class="flex-1">
                <ul class="space-y-2">
                    <li>
                        <a href="/dashboard" :class="[baseLink, isActive('/dashboard') ? activeLink : inactiveLink]">
                            <!-- Dashboard Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="/products" :class="[baseLink, isActive('/products') ? activeLink : inactiveLink]">
                            <!-- Products Icon (Shopping Bag) -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7h18l-1.5 12.75A2.25 2.25 0 0117.25 22.5H6.75a2.25 2.25 0 01-2.25-2.25L3 7z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 7V5.25A3.75 3.75 0 0112.75 1.5h0A3.75 3.75 0 0116.5 5.25V7" />
                            </svg>
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="/news" :class="[baseLink, isActive('/news') ? activeLink : inactiveLink]">
                            <!-- News Icon (Detailed Newspaper) -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.75h6m-6 3h6m-6 3h6M4.5 6.75h3v3h-3v-3zm0 6h3v6h-3v-6z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 4.5h18v15.75A1.5 1.5 0 0119.5 21.75h-15A1.5 1.5 0 013 20.25V4.5z" />
                            </svg>
                            News
                        </a>
                    </li>
                    <li>
                        <a href="/careers" :class="[baseLink, isActive('/careers') ? activeLink : inactiveLink]">
                            <!-- Careers Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a8 8 0 00-8 8h16a8 8 0 00-8-8z" />
                            </svg>
                            Careers
                        </a>
                    </li>
                    <li>
                        <a href="/directories"
                            :class="[baseLink, isActive('/directories') ? activeLink : inactiveLink]">
                            <!-- Directories Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 6.75h16.5m-16.5 3.75h16.5m-16.5 3.75h16.5m-16.5 3.75h16.5M9 6.75v10.5M15 6.75v10.5" />
                            </svg>
                            Directories
                        </a>
                    </li>
                    <li>
                        <button @click="logoutModalOpen = true"
                            class="w-full flex items-center gap-2 text-left px-4 py-3 rounded-lg border border-transparent transition-all duration-200 bg-red-700 hover:bg-red-600 hover:border-red-400 hover:shadow-md hover:text-white">
                            <!-- Heroicons logout icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3-3H9m0 0l3-3m-3 3l3 3" />
                            </svg>

                            Logout
                        </button>
                    </li>
                </ul>
            </nav>
        </aside>
        <!-- Main content -->
        <div class="flex-1 bg-gray-100 min-h-screen overflow-x-auto">
            <!-- Header bar for mobile/tablet -->
            <div class="lg:hidden w-full bg-[#057A31]">
                <div class="flex items-center justify-between text-white px-4 py-3">
                    <div class="text-xl font-bold">LAPC admin Side</div>
                    <button class="text-white text-2xl focus:outline-none" @click="sidebarOpen = true">
                        &#9776;
                    </button>
                </div>
            </div>
            <div class="p-4 lg:p-8">
                <slot />
            </div>
        </div>
    </div>
    <LogoutModal v-model="logoutModalOpen" @confirm="handleLogout" />
</template>

<script setup>
import { ref, computed } from 'vue';
import LogoutModal from '@/Modals/LogoutModal.vue';
import { router, usePage } from '@inertiajs/vue3';
const sidebarOpen = ref(false);
const logoutModalOpen = ref(false);

const page = usePage();
const currentPath = computed(() => (page.url || '').split('?')[0] || '/');

const baseLink = 'flex items-center gap-2 px-4 py-3 rounded-lg border border-transparent transition-all duration-200';
const activeLink = 'bg-[#057A31] text-white shadow-md';
const inactiveLink = 'bg-gray-700 hover:bg-gray-600 hover:border-gray-400 hover:shadow-md hover:text-white';

function isActive(path) {
    return currentPath.value === path || currentPath.value.startsWith(path + '/');
}

function handleLogout() {
    router.post('/access-logout', {}, {
        onFinish: () => {
            logoutModalOpen.value = false;
            // Use Inertia.visit for SPA navigation
            router.visit('/');
        }
    });
}
</script>
