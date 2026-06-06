<script setup>
// Factories list: server-side search + pagination, view-details modal, and delete.
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/PageHeader.vue'
import AppIcon from '@/components/AppIcon.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import { factoriesApi } from '@/api/resources'
import { useResourceList } from '@/composables/useResourceList'
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import { useCounts } from '@/composables/useCounts'
import { ROUTE_NAMES } from '@/config'

const router = useRouter()
const { set: setCount } = useCounts()

// Shared list state: items + pagination + debounced search + load/reload.
// Keep the sidebar's factory badge in sync with the server total on every load.
const {
  items: factories,
  pagination,
  loading,
  search,
  page,
  totalPages,
  pageLabel,
  load,
} = useResourceList(factoriesApi.list, {
  errorMessage: 'Failed to load factories.',
  // Only sync the badge when unfiltered, so it reflects the true total (not a search subset).
  onLoaded: (res) => {
    if (!search.value) setCount('factories', res.pagination?.total)
  },
})

// Shared confirm + delete flow (reloads the list afterwards to keep totals accurate).
const {
  target: toDelete,
  deleting,
  confirm: confirmDelete,
} = useConfirmDelete(factoriesApi.remove, {
  successMessage: (f) => `${f.factory_name} deleted.`,
  errorMessage: 'Failed to delete factory.',
  onDeleted: load,
})

const viewing = ref(null)

onMounted(load)
</script>

<template>
  <div>
    <PageHeader
      title="Factories"
      subtitle="Manufacturing sites across your network."
    >
      <template #actions>
        <button class="btn-primary" @click="router.push({ name: ROUTE_NAMES.FACTORY_CREATE })">
          <AppIcon name="plus" :size="16" /> New factory
        </button>
      </template>
    </PageHeader>

    <div class="card mt-8 overflow-hidden">
      <!-- Toolbar -->
      <div class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
          <AppIcon name="search" :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink/35" />
          <input
            v-model="search"
            type="search"
            placeholder="Search factories…"
            class="field-input pl-10"
          />
        </div>
        <span class="shrink-0 px-1 text-sm text-ink/45">{{ pagination.total ?? factories.length }} factories</span>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full min-w-[680px] border-collapse">
          <thead class="bg-canvas/60">
            <tr class="border-b border-line">
              <th class="table-th">Factory</th>
              <th class="table-th">Location</th>
              <th class="table-th">Email</th>
              <th class="table-th text-center">Employees</th>
              <th class="table-th text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading skeleton -->
            <tr v-if="loading" v-for="n in 5" :key="`s${n}`" class="border-b border-line/70">
              <td class="table-td" colspan="5">
                <div class="h-5 w-full animate-pulse rounded bg-canvas" />
              </td>
            </tr>

            <tr v-else-if="!factories.length">
              <td colspan="5" class="px-5 py-16 text-center text-sm text-ink/45">
                No factories match your search.
              </td>
            </tr>

            <tr
              v-else
              v-for="f in factories"
              :key="f.id"
              class="border-b border-line/70 transition-colors last:border-0 hover:bg-canvas/50"
            >
              <td class="table-td">
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600">
                    <AppIcon name="building" :size="16" />
                  </span>
                  <div class="leading-tight">
                    <p class="font-semibold text-ink">{{ f.factory_name }}</p>
                    <p class="text-xs text-primary-600">{{ f.website }}</p>
                  </div>
                </div>
              </td>
              <td class="table-td">{{ f.location }}</td>
              <td class="table-td">{{ f.email }}</td>
              <td class="table-td text-center">
                <span class="inline-grid h-6 min-w-6 place-items-center rounded-full bg-canvas px-2 text-xs font-semibold text-ink/70">
                  {{ f.employees_count ?? 0 }}
                </span>
              </td>
              <td class="table-td">
                <div class="flex items-center justify-end gap-1 text-ink/40">
                  <button class="rounded-md p-1.5 hover:bg-canvas hover:text-ink" title="View" @click="viewing = f">
                    <AppIcon name="eye" :size="17" />
                  </button>
                  <button
                    class="rounded-md p-1.5 hover:bg-canvas hover:text-ink"
                    title="Edit"
                    @click="router.push({ name: 'factory-edit', params: { id: f.id } })"
                  >
                    <AppIcon name="pencil" :size="17" />
                  </button>
                  <button
                    class="rounded-md p-1.5 hover:bg-rose-50 hover:text-rose-600"
                    title="Delete"
                    @click="toDelete = f"
                  >
                    <AppIcon name="trash" :size="17" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-line bg-surface px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <span class="text-sm text-ink/45">Page {{ pageLabel }} of {{ totalPages }}</span>
        <Pagination v-model="page" :total-pages="totalPages" />
      </div>
    </div>

    <!-- Detail modal -->
    <div
      v-if="viewing"
      class="fixed inset-0 z-50 grid place-items-center bg-ink/40 p-4"
      @click.self="viewing = null"
    >
      <div class="card w-full max-w-md p-6">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-primary-50 text-primary-600">
              <AppIcon name="building" :size="20" />
            </span>
            <div class="leading-tight">
              <h2 class="font-serif text-xl font-semibold text-ink">{{ viewing.factory_name }}</h2>
              <p class="text-xs text-ink/40">#{{ viewing.id }}</p>
            </div>
          </div>
          <button class="rounded-md p-1.5 text-ink/40 hover:bg-canvas hover:text-ink" title="Close" @click="viewing = null">
            <AppIcon name="x" :size="18" />
          </button>
        </div>
        <dl class="mt-5 divide-y divide-line/70 text-sm">
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Location</dt>
            <dd class="font-medium text-ink">{{ viewing.location || '—' }}</dd>
          </div>
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Email</dt>
            <dd class="font-medium text-ink">{{ viewing.email || '—' }}</dd>
          </div>
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Website</dt>
            <dd class="font-medium text-primary-600">{{ viewing.website || '—' }}</dd>
          </div>
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Employees</dt>
            <dd class="font-medium text-ink">{{ viewing.employees_count ?? 0 }}</dd>
          </div>
        </dl>
        <div class="mt-6 flex justify-end gap-2">
          <button class="btn-outline" @click="viewing = null">Close</button>
          <button
            class="btn-primary"
            @click="router.push({ name: ROUTE_NAMES.FACTORY_EDIT, params: { id: viewing.id } })"
          >
            <AppIcon name="pencil" :size="16" /> Edit
          </button>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :open="!!toDelete"
      :busy="deleting"
      title="Delete factory?"
      :message="toDelete ? `${toDelete.factory_name} and its association will be removed. This cannot be undone.` : ''"
      @confirm="confirmDelete"
      @cancel="toDelete = null"
    />
  </div>
</template>
