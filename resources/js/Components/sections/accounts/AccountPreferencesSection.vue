<template>
    <main>
        <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <!-- Header -->
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                    <Settings2 class="h-5 w-5 text-green-600" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">
                        Account Preferences
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-600">
                        Manage your account preferences.
                    </p>
                </div>
            </div>
            <div class="divide-y divide-gray-200" />

            <!-- Login Sessions -->
            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200">
                <div class="flex items-center gap-3 bg-green-50 px-4 py-3">
                    <Monitor class="h-5 w-5 text-green-600" />
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">
                           Login Sessions
                        </h3>
                        <p class="text-xs text-gray-600">
                            Manage your active sessions.
                        </p>
                    </div>
                </div>
                <div v-if="loading" class="border-t px-4 py-6 text-center text-xs text-gray-500">
                    Loading sessions...
                </div>
                <template v-else>
                    <div
                        v-if="currentSession"
                        class="flex items-center justify-between gap-3 border-t px-4 py-3"
                    >
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold">
                                    Current Session
                                </span>
                                <span
                                    class="rounded bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700"
                                >
                                    This Device
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-600">
                                {{ sessionLabel(currentSession) }}
                            </p>
                        </div>
                        <span class="shrink-0 text-xs text-gray-500">
                            {{ formatSessionDate(currentSession.logged_in_at || currentSession.last_activity) }}
                        </span>
                    </div>
                    <div v-else class="border-t px-4 py-3 text-xs text-gray-500">
                        No active session recorded for this device yet.
                    </div>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between border-t px-4 py-3 text-left transition hover:bg-gray-50"
                        @click="showOthers = !showOthers"
                    >
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">
                                Other Sessions ({{ otherSessions.length }})
                            </h4>
                            <p class="mt-1 text-xs text-gray-600">
                                {{
                                    otherSessions.length
                                        ? 'Review and sign out other devices'
                                        : 'No other active sessions'
                                }}
                            </p>
                        </div>
                        <ChevronRight
                            class="h-4 w-4 shrink-0 text-gray-400 transition"
                            :class="showOthers ? 'rotate-90' : ''"
                        />
                    </button>
                    <div v-if="showOthers && otherSessions.length" class="border-t bg-gray-50/50">
                        <div
                            v-for="session in otherSessions"
                            :key="session.id"
                            class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 last:border-b-0"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ sessionLabel(session) }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    Last active {{ formatSessionDate(session.last_activity || session.logged_in_at) }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-lg border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:opacity-50"
                                :disabled="revokingId === session.id"
                                @click="revokeSession(session)"
                            >
                                {{ revokingId === session.id ? '...' : 'Revoke' }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <button
                type="button"
                class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg border border-red-300 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="loggingOutAll"
                @click="logoutAllDevices"
            >
                <LogOut class="h-4 w-4" />
                {{ loggingOutAll ? 'Signing out...' : 'Logout from All Devices' }}
            </button>
        </div>
    </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import {
    Settings2,
    Monitor,
    ChevronRight,
    LogOut,
} from '@lucide/vue';
import { csrfHeaders } from '@/utils/csrf';

const sessions = ref([]);
const loading = ref(true);
const showOthers = ref(false);
const revokingId = ref(null);
const loggingOutAll = ref(false);
const currentSession = computed(() => sessions.value.find((s) => s.is_current) || null);
const otherSessions = computed(() => sessions.value.filter((s) => !s.is_current));

function sessionLabel(session) {
    const platform = session.platform || 'Unknown';
    const browser = session.browser || 'Unknown';
    return `${platform} • ${browser}`;
}

function formatSessionDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

async function fetchSessions() {
    loading.value = true;
    try {
        const res = await fetch('/user-sessions', {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) throw new Error('Failed to load sessions');
        const data = await res.json();
        sessions.value = data.sessions || [];
    } catch (e) {
        console.error(e);
        sessions.value = [];
    } finally {
        loading.value = false;
    }
}

async function revokeSession(session) {
    const result = await Swal.fire({
        icon: 'question',
        title: 'Revoke this session?',
        text: 'That device will be signed out on its next request.',
        showCancelButton: true,
        confirmButtonText: 'Revoke',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;
    revokingId.value = session.id;
    try {
        const res = await fetch(`/user-sessions/${session.id}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                ...csrfHeaders(),
            },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('Revoke failed');
        await fetchSessions();
    } catch (e) {
        console.error(e);
        await Swal.fire({ icon: 'error', title: 'Error', text: 'Could not revoke that session.' });
    } finally {
        revokingId.value = null;
    }
}

async function logoutAllDevices() {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Logout from all devices?',
        text: 'You will be signed out everywhere, including this browser.',
        showCancelButton: true,
        confirmButtonText: 'Logout all',
        confirmButtonColor: '#dc2626',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) return;
    loggingOutAll.value = true;
    router.post(route('user-sessions.logout-all'), {}, {
        onFinish: () => {
            loggingOutAll.value = false;
        },
    });
}

onMounted(() => {
    fetchSessions();
});
</script>
