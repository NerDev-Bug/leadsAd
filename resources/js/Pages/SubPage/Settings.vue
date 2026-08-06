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
            Manage your news archive, backups, and account settings.
          </p>
        </div>

        <!-- Segmented Tabs -->
        <div
          class="w-full max-w-xl bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm"
        >
          <div class="grid grid-cols-1 sm:grid-cols-3">

            <!-- News Archive -->
            <button
              @click="setActiveTab('archive')"
              :class="[
                'flex items-center justify-center gap-3 px-4 py-3 font-semibold text-sm transition-colors',
                activeTab === 'archive'
                  ? 'bg-green-700 text-white'
                  : 'bg-white text-gray-800 hover:bg-gray-50'
              ]"
            >
              <Archive class="w-4 h-4" />
              <span>News Archive</span>
            </button>

            <!-- Backup -->
            <button
              @click="setActiveTab('backup')"
              :class="[
                'flex items-center justify-center gap-3 px-4 py-3 font-semibold text-sm border-t sm:border-t-0 sm:border-l border-gray-200 transition-colors',
                activeTab === 'backup'
                  ? 'bg-green-700 text-white'
                  : 'bg-white text-gray-800 hover:bg-gray-50'
              ]"
            >
              <Database class="w-4 h-4" />
              <span>Backup</span>
            </button>

            <!-- Account -->
            <button
              @click="setActiveTab('account')"
              :class="[
                'flex items-center justify-center gap-3 px-4 py-3 font-semibold text-sm border-t sm:border-t-0 sm:border-l border-gray-200 transition-colors',
                activeTab === 'account'
                  ? 'bg-green-700 text-white'
                  : 'bg-white text-gray-800 hover:bg-gray-50'
              ]"
            >
              <User class="w-4 h-4" />
              <span>Account</span>
            </button>

          </div>
        </div>

        <!-- Dynamic Content -->
        <div class="mt-8">
          <div v-if="activeTab === 'archive'">
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

          <div v-else-if="activeTab === 'backup'">
            <div class="space-y-6">
              <BackupOverviewSection />
              <RecentBackupSection />
            </div>
          </div>

          <div v-else-if="activeTab === 'account'">
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
import { ref } from 'vue'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'
import ChangePasswordSection from '@/Components/sections/accounts/ChangePasswordSection.vue'
import AccountInformationSection from '@/Components/sections/accounts/AccountInformationSection.vue'
import AccountPreferencesSection from '@/Components/sections/accounts/AccountPreferencesSection.vue'
import BackupOverviewSection from '@/Components/sections/backup/BackupOverviewSection.vue'
import RecentBackupSection from '@/Components/sections/backup/RecentBackupSection.vue'
import NewsArchivedSection from '@/Components/sections/archived/NewsArchivedSection.vue'
import NewsArchivedDataSection from '@/Components/sections/archived/NewsArchivedDataSection.vue'
import { Archive, Database, User } from '@lucide/vue'

const activeTab = ref('archive')

// Shared search and filter state for archived news
const search = ref('')
const filter = ref('')
const archiveDetailOpen = ref(false)

const setActiveTab = (tab) => {
  if (tab !== 'archive') {
    archiveDetailOpen.value = false
  }
  activeTab.value = tab
}
</script>