<script setup>
import { useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function capitalizeFirstLetter(value) {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
}

function onNameInput(event) {
    form.name = capitalizeFirstLetter(event.target.value);
}

function submit() {
    form.post(route('access-registers.store'), {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Account Created!',
                text: 'Your registration was successful.',
                confirmButtonColor: '#057A31',
                confirmButtonText: 'OK',
            }).then(() => {
                router.visit('/');
            });
        },
    });
}
</script>

<template>
    <div class="auth-page">
        <div class="auth-overlay" />

        <div class="auth-card animate-fade-in">
            <div class="auth-header">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 shadow-lg shadow-brand-600/25">
                    <img src="/logo-green-CYWgDgZa.png" alt="LAPC" class="h-7 w-7 object-contain brightness-0 invert" />
                </div>
                <div>
                    <h1 class="auth-title">Create Account</h1>
                    <p class="auth-subtitle">Register for LAPC Admin access</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="auth-label" for="name">Username</label>
                    <input
                        :value="form.name"
                        @input="onNameInput"
                        id="name"
                        type="text"
                        class="auth-input"
                        placeholder="Enter username"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs font-medium text-red-500">{{ form.errors.name }}</p>
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
                        placeholder="Create a password"
                        required
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs font-medium text-red-500">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="auth-label" for="password_confirmation">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        id="password_confirmation"
                        type="password"
                        class="auth-input"
                        placeholder="Confirm your password"
                        required
                    />
                    <p v-if="form.errors.password_confirmation" class="mt-1 text-xs font-medium text-red-500">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <button type="submit" class="auth-btn" :disabled="form.processing">
                    <svg v-if="form.processing" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    {{ form.processing ? 'Registering...' : 'Register' }}
                </button>
            </form>

            <p class="auth-footer">
                Already have an account?
                <a href="/" class="auth-link">Sign in</a>
            </p>
        </div>
    </div>
</template>
