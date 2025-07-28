<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const productsCount = computed(() => page.props.productsCount ?? 0);
const newsCount = computed(() => page.props.newsCount ?? 0);
const careersCount = computed(() => page.props.careersCount ?? 0);
</script>

<template>
    <SidebarLayout>
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8 text-gray-800">Dashboard</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Products Card -->
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center justify-center transition hover:shadow-lg">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <path d="M16 3v4a1 1 0 001 1h4" />
                        </svg>
                    </div>
                    <div class="text-4xl font-extrabold text-blue-700">{{ productsCount }}</div>
                    <div class="text-lg text-gray-600 mt-2">Total Products</div>
                </div>
                <!-- News Card -->
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center justify-center transition hover:shadow-lg">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
                            <path d="M16 3v4a1 1 0 001 1h4" />
                        </svg>
                    </div>
                    <div class="text-4xl font-extrabold text-green-700">{{ newsCount }}</div>
                    <div class="text-lg text-gray-600 mt-2">Total News</div>
                </div>
                <!-- Careers Card -->
                <!-- <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center justify-center transition hover:shadow-lg">
                    <div class="flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 00-3-3.87" />
                            <path d="M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </div>
                    <div class="text-4xl font-extrabold text-yellow-700">{{ careersCount }}</div>
                    <div class="text-lg text-gray-600 mt-2">Total Careers</div>
                </div> -->
            </div>
        </div>
    </SidebarLayout>
</template>
