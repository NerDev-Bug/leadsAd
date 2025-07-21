<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div v-if="form.errors.general" class="text-red-500 text-xs mt-1 text-center">{{ form.errors.general }}
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" for="email">Email</label>
                    <input v-model="form.email" id="email" type="email"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        required autocomplete="email" />
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1" for="password">Password</label>
                    <input v-model="form.password" id="password" type="password"
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                        required autocomplete="current-password" />
                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center justify-center"
                    :disabled="form.processing">
                    <span v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
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
        }
    });
}
</script>
