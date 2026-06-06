<script setup>
// Employees list: server-side search, factory filter, pagination, view-details modal, and delete.
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/PageHeader.vue'
import AppIcon from '@/components/AppIcon.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import { employeesApi } from '@/api/resources'
import { useResourceList } from '@/composables/useResourceList'
import { useConfirmDelete } from '@/composables/useConfirmDelete'
import { useCounts } from '@/composables/useCounts'
import { ROUTE_NAMES } from '@/config'

const router = useRouter()
const { set: setCount } = useCounts()

const factories = ref([]) // factory lookup for the filter dropdown (comes back with the list)
const factoryFilter = ref('')

// Shared list state, with the factory filter mixed into the query and the
// factory lookup captured from each response.
const {
  items: employees,
  pagination,
  loading,
  search,
  page,
  totalPages,
  pageLabel,
  load,
  reload,
} = useResourceList(employeesApi.list, {
  extraParams: () => ({ factory_id: factoryFilter.value || undefined }),
  onLoaded: (res) => {
    factories.value = res.factories ?? factories.value
    // Keep the sidebar's employee badge in sync — but only when unfiltered, so it's the true total.
    if (!search.value && !factoryFilter.value) setCount('employees', res.pagination?.total)
  },
  errorMessage: 'Failed to load employees.',
})

// The factory dropdown filters instantly (search is already debounced in the composable).
watch(factoryFilter, reload)

// Shared confirm + delete flow (reloads the list afterwards to keep totals accurate).
const {
  target: toDelete,
  deleting,
  confirm: confirmDelete,
} = useConfirmDelete(employeesApi.remove, {
  successMessage: () => 'Employee deleted.',
  errorMessage: 'Failed to delete employee.',
  onDeleted: load,
})

const viewing = ref(null)

// Deterministic avatar colour per employee.
const palette = [
  'bg-amber-100 text-amber-700',
  'bg-rose-100 text-rose-700',
  'bg-emerald-100 text-emerald-700',
  'bg-sky-100 text-sky-700',
  'bg-violet-100 text-violet-700',
  'bg-orange-100 text-orange-700',
]
const avatarClass = (e) => palette[(e.id ?? 0) % palette.length]
const initials = (e) => `${e.firstname?.[0] ?? e.first_name?.[0] ?? ''}${e.lastname?.[0] ?? e.last_name?.[0] ?? ''}`.toUpperCase()

onMounted(load)
</script>

<template>
  <div>
    <PageHeader title="Employees" subtitle="Everyone working across your factories.">
      <template #actions>
        <button class="btn-primary" @click="router.push({ name: ROUTE_NAMES.EMPLOYEE_CREATE })">
          <AppIcon name="plus" :size="16" /> New employee
        </button>
      </template>
    </PageHeader>

    <div class="card mt-8 overflow-hidden">
      <div class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
          <AppIcon name="search" :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink/35" />
          <input v-model="search" type="search" placeholder="Search employees…" class="field-input pl-10" />
        </div>
        <div class="relative w-full sm:w-56">
          <select v-model="factoryFilter" class="field-input appearance-none pr-9">
            <option value="">All factories</option>
            <option v-for="f in factories" :key="f.id" :value="String(f.id)">{{ f.factory_name }}</option>
          </select>
          <AppIcon name="chevron" :size="16" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink/40" />
        </div>
        <span class="shrink-0 px-1 text-sm text-ink/45">{{ pagination.total ?? employees.length }} employees</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse">
          <thead class="bg-canvas/60">
            <tr class="border-b border-line">
              <th class="table-th">Name</th>
              <th class="table-th">Factory</th>
              <th class="table-th">Email</th>
              <th class="table-th">Phone</th>
              <th class="table-th text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" v-for="n in 6" :key="`s${n}`" class="border-b border-line/70">
              <td class="table-td" colspan="5"><div class="h-5 w-full animate-pulse rounded bg-canvas" /></td>
            </tr>

            <tr v-else-if="!employees.length">
              <td colspan="5" class="px-5 py-16 text-center text-sm text-ink/45">No employees match your filters.</td>
            </tr>

            <tr
              v-else
              v-for="e in employees"
              :key="e.id"
              class="border-b border-line/70 transition-colors last:border-0 hover:bg-canvas/50"
            >
              <td class="table-td">
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-semibold" :class="avatarClass(e)">
                    {{ initials(e) }}
                  </span>
                  <div class="leading-tight">
                    <p class="font-semibold text-ink">{{ e.firstname ?? e.first_name }} {{ e.lastname ?? e.last_name }}</p>
                    <p class="text-xs text-ink/40">#{{ e.id }}</p>
                  </div>
                </div>
              </td>
              <td class="table-td">
                <span class="inline-flex items-center gap-1.5 text-primary-600">
                  <AppIcon name="link" :size="14" /> {{ e.factory ?? e.factory_name }}
                </span>
              </td>
              <td class="table-td">{{ e.email }}</td>
              <td class="table-td font-mono text-[13px] text-ink/70">{{ e.phone }}</td>
              <td class="table-td">
                <div class="flex items-center justify-end gap-1 text-ink/40">
                  <button class="rounded-md p-1.5 hover:bg-canvas hover:text-ink" title="View" @click="viewing = e">
                    <AppIcon name="eye" :size="17" />
                  </button>
                  <button
                    class="rounded-md p-1.5 hover:bg-canvas hover:text-ink"
                    title="Edit"
                    @click="router.push({ name: ROUTE_NAMES.EMPLOYEE_EDIT, params: { id: e.id } })"
                  >
                    <AppIcon name="pencil" :size="17" />
                  </button>
                  <button class="rounded-md p-1.5 hover:bg-rose-50 hover:text-rose-600" title="Delete" @click="toDelete = e">
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
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-sm font-semibold" :class="avatarClass(viewing)">
              {{ initials(viewing) }}
            </span>
            <div class="leading-tight">
              <h2 class="font-serif text-xl font-semibold text-ink">
                {{ viewing.firstname ?? viewing.first_name }} {{ viewing.lastname ?? viewing.last_name }}
              </h2>
              <p class="text-xs text-ink/40">#{{ viewing.id }}</p>
            </div>
          </div>
          <button class="rounded-md p-1.5 text-ink/40 hover:bg-canvas hover:text-ink" title="Close" @click="viewing = null">
            <AppIcon name="x" :size="18" />
          </button>
        </div>
        <dl class="mt-5 divide-y divide-line/70 text-sm">
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Factory</dt>
            <dd class="font-medium text-ink">{{ viewing.factory ?? viewing.factory_name ?? '—' }}</dd>
          </div>
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Email</dt>
            <dd class="font-medium text-ink">{{ viewing.email || '—' }}</dd>
          </div>
          <div class="flex justify-between gap-4 py-2.5">
            <dt class="text-ink/45">Phone</dt>
            <dd class="font-mono text-ink">{{ viewing.phone || '—' }}</dd>
          </div>
        </dl>
        <div class="mt-6 flex justify-end gap-2">
          <button class="btn-outline" @click="viewing = null">Close</button>
          <button
            class="btn-primary"
            @click="router.push({ name: ROUTE_NAMES.EMPLOYEE_EDIT, params: { id: viewing.id } })"
          >
            <AppIcon name="pencil" :size="16" /> Edit
          </button>
        </div>
      </div>
    </div>

    <ConfirmDialog
      :open="!!toDelete"
      :busy="deleting"
      title="Delete employee?"
      :message="toDelete ? `${toDelete.firstname ?? toDelete.first_name} ${toDelete.lastname ?? toDelete.last_name} will be removed. This cannot be undone.` : ''"
      @confirm="confirmDelete"
      @cancel="toDelete = null"
    />
  </div>
</template>
