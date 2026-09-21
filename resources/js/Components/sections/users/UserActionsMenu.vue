<template>
    <div ref="root" class="relative inline-flex">
        <button
            type="button"
            class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-1.5 text-gray-600 transition hover:bg-gray-100"
            :aria-expanded="open"
            aria-haspopup="menu"
            aria-label="User actions"
            @click.stop="toggle"
        >
            <MoreVertical class="h-4 w-4" />
        </button>

        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-150"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="open"
                    ref="menuRef"
                    role="menu"
                    class="fixed z-[100] w-44 rounded-xl border border-slate-200 bg-white py-1.5 shadow-lg"
                    :style="menuStyle"
                    @click.stop
                >
                    <button
                        type="button"
                        role="menuitem"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-800 transition hover:bg-slate-50"
                        @click="choose('view')"
                    >
                        <Eye class="h-4 w-4 shrink-0 text-slate-700" />
                        View
                    </button>
                    <button
                        type="button"
                        role="menuitem"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="disableRemove"
                        @click="choose('remove')"
                    >
                        <Trash2 class="h-4 w-4 shrink-0 text-red-600" />
                        Remove
                    </button>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { Eye, MoreVertical, Trash2 } from '@lucide/vue';

defineProps({
    disableRemove: { type: Boolean, default: false },
});

const emit = defineEmits(['view', 'remove']);

const MENU_WIDTH = 176;
const MENU_GAP = 8;
const VIEWPORT_PADDING = 8;
const OPEN_EVENT = 'user-actions-menu:open';

const menuId = `user-actions-${Math.random().toString(36).slice(2)}`;
const open = ref(false);
const root = ref(null);
const menuRef = ref(null);
const coords = ref({ top: 0, left: 0 });

const menuStyle = computed(() => ({
    top: `${coords.value.top}px`,
    left: `${coords.value.left}px`,
}));

function updatePosition() {
    if (!root.value) return;

    const trigger = root.value.getBoundingClientRect();
    const menuHeight = menuRef.value?.offsetHeight || 96;
    const spaceBelow = window.innerHeight - trigger.bottom - MENU_GAP - VIEWPORT_PADDING;
    const openUpward = spaceBelow < menuHeight && trigger.top > menuHeight + MENU_GAP;

    let top = openUpward
        ? trigger.top - menuHeight - MENU_GAP
        : trigger.bottom + MENU_GAP;

    let left = trigger.right - MENU_WIDTH;

    top = Math.max(VIEWPORT_PADDING, Math.min(top, window.innerHeight - menuHeight - VIEWPORT_PADDING));
    left = Math.max(VIEWPORT_PADDING, Math.min(left, window.innerWidth - MENU_WIDTH - VIEWPORT_PADDING));

    coords.value = { top, left };
}

function close() {
    open.value = false;
}

async function openMenu() {
    window.dispatchEvent(new CustomEvent(OPEN_EVENT, { detail: { id: menuId } }));
    open.value = true;
    await nextTick();
    updatePosition();
}

async function toggle() {
    if (open.value) {
        close();
        return;
    }

    await openMenu();
}

function choose(action) {
    close();
    emit(action);
}

function onSiblingOpen(event) {
    if (event.detail?.id !== menuId) {
        close();
    }
}

function onDocumentClick(event) {
    if (!open.value) return;
    const inTrigger = root.value?.contains(event.target);
    const inMenu = menuRef.value?.contains(event.target);
    if (!inTrigger && !inMenu) {
        close();
    }
}

function onViewportChange() {
    if (open.value) {
        updatePosition();
    }
}

onMounted(() => {
    window.addEventListener(OPEN_EVENT, onSiblingOpen);
    document.addEventListener('click', onDocumentClick);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
});

onUnmounted(() => {
    window.removeEventListener(OPEN_EVENT, onSiblingOpen);
    document.removeEventListener('click', onDocumentClick);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
});
</script>
