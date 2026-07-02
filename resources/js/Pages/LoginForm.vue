<template>
    <div class="auth-page">
        <div class="auth-overlay" />

        <div class="auth-card animate-fade-in">
            <div class="auth-header">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 shadow-lg shadow-brand-600/25">
                    <img src="/logo-green-CYWgDgZa.png" alt="LAPC" class="h-7 w-7 object-contain brightness-0 invert" />
                </div>
                <div>
                    <h1 class="auth-title">Login</h1>
                    <p class="auth-subtitle">Sign in to LAPC Admin Panel</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div v-if="form.errors.general" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-center text-xs font-medium text-red-600">
                    {{ form.errors.general }}
                </div>

                <div>
                    <label class="auth-label" for="email">Email</label>
                    <input
                        v-model="form.email"
                        id="email"
                        type="email"
                        class="auth-input"
                        placeholder="you@example.com"
                        required
                        autocomplete="email"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs font-medium text-red-500">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="auth-label" for="password">Password</label>
                    <input
                        v-model="form.password"
                        id="password"
                        type="password"
                        class="auth-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs font-medium text-red-500">{{ form.errors.password }}</p>
                </div>

                <button type="submit" class="auth-btn" :disabled="form.processing">
                    <svg v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    {{ form.processing ? 'Logging in...' : 'Login' }}
                </button>
            </form>

            <p class="auth-footer">
                Don't have an account?
                <a href="/register" class="auth-link">Create account</a>
            </p>
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
