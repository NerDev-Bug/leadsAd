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
                        <a href="/dashboard"
                            class="block px-4 py-3 rounded-lg border border-transparent transition-all duration-200 bg-gray-700 hover:bg-gray-600 hover:border-gray-400 hover:shadow-md hover:text-white">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/products"
                            class="block px-4 py-3 rounded-lg border border-transparent transition-all duration-200 bg-gray-700 hover:bg-gray-600 hover:border-gray-400 hover:shadow-md hover:text-white">
                            Products
                        </a>
                    </li>
                    <li>
                        <a href="/news"
                            class="block px-4 py-3 rounded-lg border border-transparent transition-all duration-200 bg-gray-700 hover:bg-gray-600 hover:border-gray-400 hover:shadow-md hover:text-white">
                            News
                        </a>
                    </li>
                    <!-- <li>
                        <a href="/careers"
                            class="block px-4 py-3 rounded-lg border border-transparent transition-all duration-200 bg-gray-700 hover:bg-gray-600 hover:border-gray-400 hover:shadow-md hover:text-white">
                            Careers
                        </a>
                    </li> -->
                    <li>
                        <button @click="logoutModalOpen = true"
                            class="w-full text-left block px-4 py-3 rounded-lg border border-transparent transition-all duration-200 bg-red-700 hover:bg-red-600 hover:border-red-400 hover:shadow-md hover:text-white">
                            Logout
                        </button>
                    </li>
                </ul>
            </nav>
        </aside>
        <!-- Main content -->
        <div class="flex-1 bg-gray-100 min-h-screen overflow-x-auto">
            <!-- Header bar for mobile/tablet -->
            <div class="lg:hidden w-full bg-green-400">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="text-xl font-bold">LAPC admin Side</div>
                    <button class="text-gray-800 text-2xl focus:outline-none" @click="sidebarOpen = true">
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
import { ref } from 'vue';
import LogoutModal from '@/Modals/LogoutModal.vue';
import { router } from '@inertiajs/vue3';
const sidebarOpen = ref(false);
const logoutModalOpen = ref(false);

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
