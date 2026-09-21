<template>
  <SidebarLayout>
    <div class="max-w-7xl mx-auto animate-fade-in">
      <section>
        <!-- Header -->
        <div class="mb-8">
          <h1 class="admin-page-title">
            Settings
          </h1>
          <p class="admin-page-subtitle">
            Manage your news archive, backups, users, and account settings.
          </p>
        </div>

        <!-- Segmented Tabs -->
        <div
          v-if="visibleTabs.length"
          class="w-full max-w-3xl bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm"
        >
          <div
            class="grid grid-cols-1"
            :class="{
              'sm:grid-cols-1': visibleTabs.length === 1,
              'sm:grid-cols-2': visibleTabs.length === 2,
              'sm:grid-cols-3': visibleTabs.length === 3,
              'sm:grid-cols-4': visibleTabs.length >= 4,
            }"
          >
            <button
              v-for="(tab, index) in visibleTabs"
              :key="tab.id"
              @click="setActiveTab(tab.id)"
              :class="[
                'flex items-center justify-center gap-3 px-4 py-3 font-semibold text-sm transition-colors',
                index > 0 ? 'border-t sm:border-t-0 sm:border-l border-gray-200' : '',
                activeTab === tab.id
                  ? 'bg-green-700 text-white'
                  : 'bg-white text-gray-800 hover:bg-gray-50'
              ]"
            >
              <component :is="tab.icon" class="w-4 h-4" />
              <span>{{ tab.label }}</span>
            </button>
          </div>
        </div>

        <!-- Dynamic Content -->
        <div class="mt-8">
          <div v-if="activeTab === 'archive' && canSeeTab('archive')">
            <div class="max-w-7xl rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
              <NewsArchivedSection
                v-if="!archiveDetailOpen"
                v-model:search="search"
                v-model:filter="filter"
              />
              <NewsArchivedDataSection
                v-model:detail-open="archiveDetailOpen"
                :search="search"
                :filter="filter"
              />
            </div>
          </div>

          <div v-else-if="activeTab === 'backup' && canSeeTab('backup')">
            <div class="space-y-6">
              <BackupOverviewSection />
              <RecentBackupSection />
            </div>
          </div>

          <div v-else-if="activeTab === 'users' && canSeeTab('users')">
            <UsersSection />
          </div>

          <div v-else-if="activeTab === 'account' && canSeeTab('account')">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="space-y-6">
                    <AccountInformationSection />
                    <ChangePasswordSection />
                </div>
                <div class="space-y-6">
                    <AccountPreferencesSection />
                </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'
import ChangePasswordSection from '@/Components/sections/accounts/ChangePasswordSection.vue'
import AccountInformationSection from '@/Components/sections/accounts/AccountInformationSection.vue'
import AccountPreferencesSection from '@/Components/sections/accounts/AccountPreferencesSection.vue'
import BackupOverviewSection from '@/Components/sections/backup/BackupOverviewSection.vue'
import RecentBackupSection from '@/Components/sections/backup/RecentBackupSection.vue'
import NewsArchivedSection from '@/Components/sections/archived/NewsArchivedSection.vue'
import NewsArchivedDataSection from '@/Components/sections/archived/NewsArchivedDataSection.vue'
import UsersSection from '@/Components/sections/users/UsersSection.vue'
import { Archive, Database, User, Users } from '@lucide/vue'

const page = usePage()
const allowedTabs = computed(() => page.props.auth?.permissions?.settings_tabs || [])

const allTabs = [
  { id: 'archive', label: 'News Archive', icon: Archive },
  { id: 'backup', label: 'Backup', icon: Database },
  { id: 'users', label: 'Users', icon: Users },
  { id: 'account', label: 'Account', icon: User },
]

const visibleTabs = computed(() =>
  allTabs.filter((tab) => allowedTabs.value.includes(tab.id)),
)

const activeTab = ref(visibleTabs.value[0]?.id || 'account')

watch(
  visibleTabs,
  (tabs) => {
    if (!tabs.some((tab) => tab.id === activeTab.value)) {
      activeTab.value = tabs[0]?.id || 'account'
    }
  },
  { immediate: true },
)

const search = ref('')
const filter = ref('')
const archiveDetailOpen = ref(false)

function canSeeTab(tabId) {
  return allowedTabs.value.includes(tabId)
}

const setActiveTab = (tab) => {
  if (!canSeeTab(tab)) {
    return
  }
  if (tab !== 'archive') {
    archiveDetailOpen.value = false
  }
  activeTab.value = tab
}
</script>
