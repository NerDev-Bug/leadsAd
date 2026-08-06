<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-1.5 text-gray-600 transition hover:bg-gray-100"
            :aria-expanded="open"
            aria-haspopup="menu"
            @click.stop="toggle"
        >
            <MoreVertical class="h-4 w-4" />
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            :enter-from-class="openUpward ? 'opacity-0 -translate-y-1' : 'opacity-0 translate-y-1'"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            :leave-to-class="openUpward ? 'opacity-0 -translate-y-1' : 'opacity-0 translate-y-1'"
        >
            <div
                v-if="open"
                ref="menuRef"
                role="menu"
                class="absolute right-0 z-50 w-52 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
                :class="[
                    openUpward ? 'bottom-full mb-2' : 'top-full mt-2',
                    placementReady ? 'visible' : 'invisible',
                ]"
                @click.stop
            >
                <span
                    v-if="!openUpward"
                    class="pointer-events-none absolute -top-1.5 right-3 h-3 w-3 rotate-45 border-l border-t border-slate-200 bg-white"
                    aria-hidden="true"
                />
                <span
                    v-else
                    class="pointer-events-none absolute -bottom-1.5 right-3 h-3 w-3 rotate-45 border-r border-b border-slate-200 bg-white"
                    aria-hidden="true"
                />

                <button
                    v-if="!hideViewDetails"
                    type="button"
                    role="menuitem"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-800 transition hover:bg-slate-50"
                    @click="choose('view')"
                >
                    <Eye class="h-4 w-4 shrink-0 text-slate-700" />
                    View Details
                </button>
                <button
                    type="button"
                    role="menuitem"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-800 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="busy"
                    @click="choose('restore')"
                >
                    <RotateCcw class="h-4 w-4 shrink-0 text-slate-700" />
                    Restore News
                </button>
                <button
                    type="button"
                    role="menuitem"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-800 transition hover:bg-slate-50"
                    @click="choose('edit')"
                >
                    <Pencil class="h-4 w-4 shrink-0 text-slate-700" />
                    Edit News
                </button>

                <div class="my-1 border-t border-slate-100" />

                <button
                    type="button"
                    role="menuitem"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="busy"
                    @click="choose('delete')"
                >
                    <Trash2 class="h-4 w-4 shrink-0 text-red-600" />
                    Delete Permanently
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import { Eye, RotateCcw, Pencil, Trash2, MoreVertical } from '@lucide/vue';

defineProps({
    busy: { type: Boolean, default: false },
    hideViewDetails: { type: Boolean, default: false },
});

const emit = defineEmits(['view', 'restore', 'edit', 'delete']);

const MENU_GAP_PX = 8;
const VIEWPORT_PADDING_PX = 8;

const open = ref(false);
const openUpward = ref(false);
const placementReady = ref(false);
const root = ref(null);
const menuRef = ref(null);

function updatePlacement() {
    if (!root.value || !menuRef.value) return;

    const triggerRect = root.value.getBoundingClientRect();
    const menuHeight = menuRef.value.offsetHeight;
    const spaceBelow = window.innerHeight - triggerRect.bottom - MENU_GAP_PX - VIEWPORT_PADDING_PX;
    const spaceAbove = triggerRect.top - MENU_GAP_PX - VIEWPORT_PADDING_PX;

    const fitsBelow = spaceBelow >= menuHeight;
    const fitsAbove = spaceAbove >= menuHeight;

    if (fitsBelow) {
        openUpward.value = false;
    } else if (fitsAbove) {
        openUpward.value = true;
    } else {
        openUpward.value = spaceAbove > spaceBelow;
    }
}

async function refreshPlacement() {
    if (!open.value) return;
    placementReady.value = false;
    await nextTick();
    updatePlacement();
    placementReady.value = true;
}

async function toggle() {
    if (open.value) {
        open.value = false;
        placementReady.value = false;
        return;
    }

    open.value = true;
    await refreshPlacement();
}

function close() {
    open.value = false;
    placementReady.value = false;
}

function choose(action) {
    close();
    emit(action);
}

function onDocumentClick(event) {
    if (!open.value) return;
    if (root.value && !root.value.contains(event.target)) {
        close();
    }
}

function onViewportChange() {
    if (open.value) {
        refreshPlacement();
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
});

defineExpose({ close });
</script>
