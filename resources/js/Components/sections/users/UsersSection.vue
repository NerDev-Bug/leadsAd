<template>
    <div class="max-w-7xl rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Users</h2>
                <p class="mt-1 text-xs text-gray-500">
                    View and manage all accounts in the system.
                </p>
            </div>
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                :disabled="loading"
                @click="fetchUsers"
            >
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                Refresh
            </button>
        </div>

        <div v-if="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-th">Name</th>
                        <th class="admin-table-th">Email</th>
                        <th class="admin-table-th">Role</th>
                        <th class="admin-table-th">Member since</th>
                        <th class="admin-table-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <tr v-if="loading && !users.length">
                        <td colspan="5" class="admin-table-empty">Loading accounts...</td>
                    </tr>
                    <tr v-else-if="!users.length">
                        <td colspan="5" class="admin-table-empty">No accounts found.</td>
                    </tr>
                    <tr
                        v-for="user in users"
                        :key="user.id"
                        class="admin-table-row"
                    >
                        <td class="admin-table-td admin-table-td-primary">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-600/10 text-sm font-bold text-brand-700">
                                    {{ initial(user.username) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ user.username }}</p>
                                    <p v-if="user.id === currentUserId" class="text-xs text-brand-700">You</p>
                                </div>
                            </div>
                        </td>
                        <td class="admin-table-td">{{ user.email }}</td>
                        <td class="admin-table-td">
                            <span class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold capitalize text-slate-700">
                                {{ user.role || '—' }}
                            </span>
                        </td>
                        <td class="admin-table-td">{{ formatDate(user.created_at) }}</td>
                        <td class="admin-table-td admin-table-td-action text-right">
                            <UserActionsMenu
                                :disable-remove="user.id === currentUserId"
                                @view="openView(user)"
                                @remove="openRemove(user)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <UserViewModal v-model="viewOpen" :user="selectedUser" />
        <UserRemoveModal
            v-model="removeOpen"
            :user="selectedUser"
            :loading="removing"
            :error="removeError"
            @confirm="confirmRemove"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { RefreshCw } from '@lucide/vue';
import { csrfHeaders } from '@/utils/csrf';
import UserActionsMenu from '@/Components/sections/users/UserActionsMenu.vue';
import UserViewModal from '@/Components/sections/users/UserViewModal.vue';
import UserRemoveModal from '@/Components/sections/users/UserRemoveModal.vue';

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const users = ref([]);
const loading = ref(false);
const error = ref('');
const selectedUser = ref(null);
const viewOpen = ref(false);
const removeOpen = ref(false);
const removing = ref(false);
const removeError = ref('');

function initial(name) {
    return (name?.charAt(0) || 'U').toUpperCase();
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

async function fetchUsers() {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/admin-users', {
            headers: csrfHeaders(),
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Unable to load accounts.');
        }

        const data = await response.json();
        users.value = data.users || [];
    } catch (e) {
        error.value = e.message || 'Unable to load accounts.';
    } finally {
        loading.value = false;
    }
}

function openView(user) {
    selectedUser.value = user;
    viewOpen.value = true;
}

function openRemove(user) {
    selectedUser.value = user;
    removeError.value = '';
    removeOpen.value = true;
}

async function confirmRemove(password) {
    if (!selectedUser.value) return;

    removing.value = true;
    removeError.value = '';

    try {
        const response = await fetch(`/admin-users/${selectedUser.value.id}`, {
            method: 'DELETE',
            headers: {
                ...csrfHeaders(),
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ password }),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            removeError.value =
                data?.errors?.password?.[0]
                || data?.message
                || 'Unable to remove this account.';
            return;
        }

        users.value = users.value.filter((u) => u.id !== selectedUser.value.id);
        removeOpen.value = false;
        selectedUser.value = null;
    } catch {
        removeError.value = 'Unable to remove this account.';
    } finally {
        removing.value = false;
    }
}

onMounted(fetchUsers);
</script>
