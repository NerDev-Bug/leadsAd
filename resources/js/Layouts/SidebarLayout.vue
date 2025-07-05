<template>
    <div class="flex min-h-screen">
        <!-- Sidebar overlay for mobile -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black bg-opacity-40 md:hidden"
            @click="sidebarOpen = false"></div>
        <!-- Sidebar -->
        <aside :class="[
            'fixed z-40 inset-y-0 left-0 w-64 bg-gray-800 text-white flex flex-col p-4 transform transition-transform duration-200 md:static md:translate-x-0 md:flex',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            'md:relative md:translate-x-0 md:w-64 md:min-h-screen'
        ]">
            <div class="text-2xl font-bold mb-8 flex items-center justify-between">
                Leads admin Side
                <hr>
                <button class="md:hidden text-white text-2xl focus:outline-none" @click="sidebarOpen = false">
                    &times;
                </button>
            </div>
            <nav class="flex-1">
                <ul class="space-y-2">
                    <li>
                        <a href="/"
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
                </ul>
            </nav>
        </aside>
        <!-- Main content -->
        <div class="flex-1 p-4 md:p-8 bg-gray-100 min-h-screen w-full">
            <!-- Mobile top bar -->
            <div class="md:hidden flex items-center justify-between mb-4">
                <div class="text-xl font-bold">Leads admin Side</div>
                <button class="text-gray-800 text-2xl focus:outline-none" @click="sidebarOpen = true">
                    &#9776;
                </button>
            </div>
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const sidebarOpen = ref(false);
</script>
