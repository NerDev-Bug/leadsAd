<template>
    <main>
        <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

            <!-- Header -->
            <div class="mb-6 flex items-center gap-4">

                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50">
                    <User class="h-6 w-6 text-green-600" />
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Account Information
                    </h2>

                    <p class="mt-1 text-xs text-gray-500">
                        View and manage your account details.
                    </p>
                </div>

            </div>

            <!-- Form -->
            <div class="space-y-5">

                <!-- Full Name -->
                <div class="grid grid-cols-12 items-center gap-4">

                    <label class="col-span-3 text-sm font-semibold text-gray-700">
                        Full Name
                    </label>

                    <input
                        type="text"
                        value="Test"
                        disabled
                        class="col-span-9 h-10 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600"
                    />

                </div>

                <!-- Email -->
                <div class="grid grid-cols-12 items-center gap-4">

                    <label class="col-span-3 text-sm font-semibold text-gray-700">
                        Email Address
                    </label>

                    <input
                        type="email"
                        value="testing@gmail.com"
                        disabled
                        class="col-span-9 h-10 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600"
                    />

                </div>

                <!-- Role -->
                <div class="grid grid-cols-12 items-center gap-4">

                    <label class="col-span-3 text-sm font-semibold text-gray-700">
                        Role
                    </label>

                    <input
                        type="text"
                        value="Administrator"
                        disabled
                        class="col-span-9 h-10 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600"
                    />

                </div>

                <!-- Member Since -->
                <div class="grid grid-cols-12 items-center gap-4">

                    <label class="col-span-3 text-sm font-semibold text-gray-700">
                        Member Since
                    </label>

                    <input
                        type="text"
                        value="Jul 21, 2024 08:30 AM"
                        disabled
                        class="col-span-9 h-10 w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-100 px-4 text-sm text-gray-600"
                    />

                </div>

            </div>

        </div>
    </main>
</template>

<script setup>
import { User } from "@lucide/vue";
</script>