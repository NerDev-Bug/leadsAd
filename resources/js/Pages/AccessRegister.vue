<script setup>
import { useForm } from '@inertiajs/vue3';

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
  form.post(route('access-registers.store'));
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center
           bg-gradient-to-br from-green-200 via-emerald-100 to-amber-100 relative overflow-hidden px-4"
  >
    <!-- Decorative blobs -->
    <div class="pointer-events-none absolute -top-32 -left-32 h-80 w-80 rounded-full bg-green-500/40 blur-[100px]"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-amber-400/40 blur-[120px]"></div>

    <!-- Subtle texture -->
    <div
      class="pointer-events-none absolute inset-0 opacity-15"
      style="
        background-image: url('https://www.transparenttextures.com/patterns/green-dust-and-scratches.png');
        background-size: 200px 200px;
      "
    ></div>

    <!-- Registration Card -->
    <div
      class="w-full max-w-md bg-white/90 backdrop-blur-lg rounded-2xl shadow-2xl border border-green-300 relative z-10 p-8"
    >
      <!-- Logo + Heading -->
      <div class="flex items-center justify-center gap-3 mb-6">
        <img
          src="/logo-green-CYWgDgZa.png"
          alt="Agriculture Logo"
          class="w-12 h-12 object-contain"
        />
        <h2 class="text-2xl font-bold text-green-800">Create Account</h2>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Username -->
        <div>
          <label class="block text-green-900 font-medium mb-1" for="name">Username</label>
          <input
            :value="form.name"
            @input="onNameInput"
            id="name"
            type="text"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none
                   focus:ring-2 focus:ring-green-500 focus:border-green-400"
            required
          />
          <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-green-900 font-medium mb-1" for="email">Email</label>
          <input
            v-model="form.email"
            id="email"
            type="email"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none
                   focus:ring-2 focus:ring-green-500 focus:border-green-400"
            required
          />
          <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-green-900 font-medium mb-1" for="password">Password</label>
          <input
            v-model="form.password"
            id="password"
            type="password"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none
                   focus:ring-2 focus:ring-green-500 focus:border-green-400"
            required
          />
          <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
        </div>

        <!-- Confirm Password -->
        <div>
          <label class="block text-green-900 font-medium mb-1" for="password_confirmation">Confirm Password</label>
          <input
            v-model="form.password_confirmation"
            id="password_confirmation"
            type="password"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none
                   focus:ring-2 focus:ring-green-500 focus:border-green-400"
            required
          />
          <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">
            {{ form.errors.password_confirmation }}
          </div>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-2 px-4 rounded-lg
                 transition duration-200 flex items-center justify-center shadow-md"
          :disabled="form.processing"
        >
          <span v-if="form.processing" class="flex items-center">
            <svg
              class="animate-spin h-5 w-5 mr-2 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
              ></path>
            </svg>
            Registering...
          </span>
          <span v-else>Register</span>
        </button>
      </form>
    </div>
  </div>
</template>
