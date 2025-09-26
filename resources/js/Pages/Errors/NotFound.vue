<template>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 text-center">
      <h5 class="text-[28px] md:text-[50px] font-bold text-black mb-6">This page isn't availabe</h5>
      <p class="text-xl text-gray-700 mb-8">The link you followed may be broken, or the page may not existed</p>
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
