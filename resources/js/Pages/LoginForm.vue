<template>
    <div class="min-h-screen relative overflow-hidden flex items-center justify-center
             bg-gradient-to-br from-green-200 via-emerald-100 to-amber-100 px-4">
        <!-- Decorative organic blobs -->
        <div class="pointer-events-none absolute -top-32 -left-32 h-80 w-80 rounded-full bg-green-500/40 blur-[100px]">
        </div>
        <div
            class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-amber-400/40 blur-[120px]">
        </div>

        <!-- Subtle agriculture pattern overlay -->
        <div class="pointer-events-none absolute inset-0 opacity-15" style="
          background-image: url('https://www.transparenttextures.com/patterns/green-dust-and-scratches.png');
          background-size: 200px 200px;
        "></div>

        <!-- Centered Card -->
        <div
            class="w-full max-w-md relative bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl border border-green-300">
            <!-- Header -->
            <div class="flex items-center justify-center gap-3 px-8 pt-8">
                <!-- Agriculture Logo Image -->
                <img src="/logo-green-CYWgDgZa.png" alt="Agriculture Logo" class="w-10 h-10 object-contain" />
                <h2 class="text-2xl font-bold text-green-800">Login</h2>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5 p-8 pt-6">
                <div v-if="form.errors.general" class="text-red-500 text-xs text-center">
                    {{ form.errors.general }}
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-green-900 font-medium mb-1" for="email">Email</label>
                    <input v-model="form.email" id="email" type="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none
                     focus:ring-2 focus:ring-green-500 focus:border-green-400" required autocomplete="email" />
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-green-900 font-medium mb-1" for="password">Password</label>
                    <input v-model="form.password" id="password" type="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none
                     focus:ring-2 focus:ring-green-500 focus:border-green-400" required
                        autocomplete="current-password" />
                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-lg
                   transition duration-200 flex items-center justify-center shadow-md" :disabled="form.processing">
                    <span v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Logging in...
                    </span>
                    <span v-else>Login</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

function submit() {
    form.post(route('access-register.login'), {
        onSuccess: () => {
            window.Inertia.visit(route('dashboard'), { replace: true });
        },
    });
}
</script>
