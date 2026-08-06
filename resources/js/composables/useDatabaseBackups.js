import { ref } from 'vue';
import { csrfHeaders } from '@/utils/csrf';

const overview = ref({
    last_backup: null,
    total: 0,
    next_scheduled: null,
});
const backups = ref([]);
const loading = ref(false);
const creating = ref(false);

export function useDatabaseBackups() {
    async function fetchBackups() {
        loading.value = true;
        try {
            const res = await fetch('/backups', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('Failed to load backups');
            const data = await res.json();
            overview.value = data.overview || { last_backup: null, total: 0, next_scheduled: null };
            backups.value = data.backups || [];
        } catch (e) {
            console.error(e);
            overview.value = { last_backup: null, total: 0, next_scheduled: null };
            backups.value = [];
        } finally {
            loading.value = false;
        }
    }

    async function createBackup() {
        creating.value = true;
        try {
            const res = await fetch('/backups', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    ...csrfHeaders(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({}),
            });
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Backup failed');
            }
            if (data.data) {
                overview.value = data.data.overview || overview.value;
                backups.value = data.data.backups || backups.value;
            } else {
                await fetchBackups();
            }
            return data;
        } finally {
            creating.value = false;
        }
    }

    function downloadBackup(filename) {
        window.location.href = `/backups/${encodeURIComponent(filename)}/download`;
    }

    async function deleteBackup(filename) {
        const res = await fetch(`/backups/${encodeURIComponent(filename)}`, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                ...csrfHeaders(),
            },
            credentials: 'same-origin',
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Delete failed');
        }
        if (data.data) {
            overview.value = data.data.overview || overview.value;
            backups.value = data.data.backups || backups.value;
        } else {
            await fetchBackups();
        }
    }

    return {
        overview,
        backups,
        loading,
        creating,
        fetchBackups,
        createBackup,
        downloadBackup,
        deleteBackup,
    };
}
