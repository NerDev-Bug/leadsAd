<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

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
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
      <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-gray-700 mb-1" for="name">Username</label>
          <input :value="form.name" @input="onNameInput" id="name" type="text" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300" required />
          <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1" for="email">Email</label>
          <input v-model="form.email" id="email" type="email" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300" required />
          <div v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1" for="password">Password</label>
          <input v-model="form.password" id="password" type="password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300" required />
          <div v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1" for="password_confirmation">Confirm Password</label>
          <input v-model="form.password_confirmation" id="password_confirmation" type="password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300" required />
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200" :disabled="form.processing">
          Register
        </button>
        <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</div>
      </form>
    </div>
  </div>
</template>
