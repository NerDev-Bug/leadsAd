<template>

    <main>

        <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">



            <!-- Header -->

            <div class="mb-6 flex items-center gap-4">



                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50">

                    <Lock class="h-6 w-6 text-green-600" />

                </div>



                <div>

                    <h2 class="text-xl font-bold text-gray-900">

                        Change Password

                    </h2>



                    <p class="mt-1 text-xs text-gray-500">

                        Update your password to keep your account secure.

                    </p>

                </div>



            </div>



            <!-- Form -->

            <form class="space-y-5" @submit.prevent="submit">



                <!-- Current Password -->

                <div>



                    <label for="current_password" class="mb-2 block text-sm font-semibold text-gray-700">

                        Current Password

                    </label>



                    <div class="relative">



                        <input

                            id="current_password"

                            v-model="form.current_password"

                            :type="showCurrent ? 'text' : 'password'"

                            placeholder="Enter your current password"

                            autocomplete="current-password"

                            class="h-10 w-full rounded-xl border px-4 pr-10 text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-1"

                            :class="form.errors.current_password

                                ? 'border-red-400 focus:border-red-500 focus:ring-red-500'

                                : 'border-gray-300 focus:border-green-500 focus:ring-green-500'"

                        >



                        <button

                            type="button"

                            @click="showCurrent = !showCurrent"

                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"

                        >

                            <Eye

                                v-if="!showCurrent"

                                class="h-4 w-4"

                            />



                            <EyeOff

                                v-else

                                class="h-4 w-4"

                            />

                        </button>



                    </div>



                    <p v-if="form.errors.current_password" class="mt-1.5 text-xs font-medium text-red-500">

                        {{ form.errors.current_password }}

                    </p>



                </div>



                <!-- New Password -->

                <div>



                    <label for="password" class="mb-2 block text-sm font-semibold text-gray-700">

                        New Password

                    </label>



                    <div class="relative">



                        <input

                            id="password"

                            v-model="form.password"

                            :type="showNew ? 'text' : 'password'"

                            placeholder="Enter your new password"

                            autocomplete="new-password"

                            class="h-10 w-full rounded-xl border px-4 pr-10 text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-1"

                            :class="form.errors.password

                                ? 'border-red-400 focus:border-red-500 focus:ring-red-500'

                                : 'border-gray-300 focus:border-green-500 focus:ring-green-500'"

                        >



                        <button

                            type="button"

                            @click="showNew = !showNew"

                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"

                        >

                            <Eye

                                v-if="!showNew"

                                class="h-4 w-4"

                            />



                            <EyeOff

                                v-else

                                class="h-4 w-4"

                            />

                        </button>



                    </div>



                    <p v-if="form.errors.password" class="mt-1.5 text-xs font-medium text-red-500">

                        {{ form.errors.password }}

                    </p>



                </div>



                <!-- Confirm Password -->

                <div>



                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-gray-700">

                        Confirm New Password

                    </label>



                    <div class="relative">



                        <input

                            id="password_confirmation"

                            v-model="form.password_confirmation"

                            :type="showConfirm ? 'text' : 'password'"

                            placeholder="Confirm your new password"

                            autocomplete="new-password"

                            class="h-10 w-full rounded-xl border px-4 pr-10 text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-1"

                            :class="form.errors.password_confirmation

                                ? 'border-red-400 focus:border-red-500 focus:ring-red-500'

                                : 'border-gray-300 focus:border-green-500 focus:ring-green-500'"

                        >



                        <button

                            type="button"

                            @click="showConfirm = !showConfirm"

                            class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600"

                        >

                            <Eye

                                v-if="!showConfirm"

                                class="h-4 w-4"

                            />



                            <EyeOff

                                v-else

                                class="h-4 w-4"

                            />

                        </button>



                    </div>



                    <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs font-medium text-red-500">

                        {{ form.errors.password_confirmation }}

                    </p>



                </div>



                <!-- Update Button -->

                <div class="pt-1">



                    <button

                        type="submit"

                        :disabled="form.processing"

                        class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"

                    >

                        <Lock class="h-4 w-4" />



                        {{ form.processing ? 'Updating...' : 'Update Password' }}

                    </button>



                </div>



            </form>



        </div>

    </main>

</template>



<script setup>

import { ref } from 'vue';

import { useForm } from '@inertiajs/vue3';

import Swal from 'sweetalert2';

import {

    Lock,

    Eye,

    EyeOff,

} from '@lucide/vue';



const showCurrent = ref(false);

const showNew = ref(false);

const showConfirm = ref(false);



const form = useForm({

    current_password: '',

    password: '',

    password_confirmation: '',

});



function submit() {

    form.put(route('access-register.password.update'), {

        preserveScroll: true,

        onSuccess: () => {

            form.reset();

            showCurrent.value = false;

            showNew.value = false;

            showConfirm.value = false;

            Swal.fire({

                icon: 'success',

                title: 'Password updated',

                text: 'Your password has been changed successfully.',

                confirmButtonColor: '#057A31',

            });

        },

        onError: () => {

            if (form.errors.password || form.errors.password_confirmation) {

                form.password = '';

                form.password_confirmation = '';

            }

            if (form.errors.current_password) {

                form.current_password = '';

            }

        },

    });

}

</script>

