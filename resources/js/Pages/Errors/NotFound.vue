<template>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 text-center">
      <h5 class="text-[28px] md:text-[50px] font-bold text-red-600 mb-6">404 Not Found</h5>
      <p class="text-xl text-gray-700 mb-8">Oops! The page you are looking for does not exist.</p>
      <button
        @click="goLogin"
        class="px-6 py-3 bg-green-600 text-white rounded-lg shadow-md hover:bg-green-700"
      >
        Go to Login
      </button>
    </div>
  </template>

  <script setup>
  import { router } from '@inertiajs/vue3'

  function goLogin() {
    router.visit('/')
  }
  </script>
