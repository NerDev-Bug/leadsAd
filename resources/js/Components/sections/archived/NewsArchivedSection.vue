<template>
    <main>
        <div>
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold">Archived News</h2>
                    <p class="text-gray-600 text-sm">
                        These news items are archived and hidden from the public view.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <input
                        :value="props.search"
                        @input="(e) => emit('update:search', e.target.value)"
                        type="text"
                        placeholder="Search archived news..."
                        class="admin-input"
                    />

                    <!-- Date Filter -->
                    <div class="relative">
                        <select
                            :value="props.filter"
                            @change="(e) => emit('update:filter', e.target.value)"
                            class="h-10 w-36 rounded-lg border border-gray-200 pl-10 pr-9 text-sm font-medium text-gray-700 shadow-sm transition focus:border-gray-300 focus:outline-none focus:ring-1 focus:ring-gray-300"
                        >
                            <option value="">All Dates</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>

                        <!-- Calendar Icon -->
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-500">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>

                        <!-- Chevron -->
                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-500">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
    search: { type: String, default: '' },
    filter: { type: String, default: '' }
})

const emit = defineEmits(['update:search', 'update:filter'])
</script>