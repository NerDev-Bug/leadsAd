<template>
    <SidebarLayout>
        <div class="max-w-7xl mx-auto animate-fade-in">
            <section>
                <div class="mb-6">
                    <h1 class="admin-page-title">Settings</h1>
                    <p class="admin-page-subtitle">Manage your account settings and preferences</p>
                </div>
                <div>
                    <div>
                        <button class="admin-btn-primary sm:ml-0 sm:mt-0 mt-2">
                            Profile Settings
                        </button> 
                        <button class="admin-btn-secondary sm:ml-0 sm:mt-0 mt-2">
                            News Archive Settings
                        </button>
                    </div>
                </div>
            </section>


            <main>
                <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                    <h2 class="admin-page-title">Change Password</h2>
                    <p class="admin-page-subtitle">Update your account information and preferences</p>

                    <div class="mt-4">
                        <label for="new-password" class="block text-sm font-medium text-gray-700 mt-4">New Password</label>
                        <input type="password" id="new-password" name="new-password" class="admin-input mt-1 block w-full" placeholder="Enter your new password">

                        <label for="password" class="block text-sm font-medium text-gray-700 mt-4">Confirm Password</label>
                        <input type="password" id="password" name="password" class="admin-input mt-1 block w-full" placeholder="Confirm your new password">

                        <button class="admin-btn-primary mt-2">
                            Save Changes
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </SidebarLayout>
</template>
<script setup>
import SidebarLayout from '@/Layouts/SidebarLayout.vue';


</script>