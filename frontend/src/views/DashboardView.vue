<script setup>
// Dashboard: stat cards, recent activity, quick actions, and top factories by headcount.
// All figures come from GET /api/dashboard (plus a factory list for the "Locations" stat).
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import PageHeader from '@/components/PageHeader.vue'
import AppIcon from '@/components/AppIcon.vue'
import { factoriesApi, dashboardApi } from '@/api/resources'
import { formatShortDateTime } from '@/utils'
import { ROUTE_NAMES } from '@/config'

const router = useRouter()
const factories = ref([])
const dashboard = ref(null)
const loading = ref(true)

const stats = computed(() => [
  { label: 'Factories', value: dashboard.value?.factories ?? 0, icon: 'factory', to: ROUTE_NAMES.FACTORIES },
  { label: 'Employees', value: dashboard.value?.employees ?? 0, icon: 'employees', to: ROUTE_NAMES.EMPLOYEES },
  { label: 'Locations', value: new Set(factories.value.map((f) => f.location)).size, icon: 'building', to: ROUTE_NAMES.FACTORIES },
  { label: 'Logged events', value: dashboard.value?.logged_events ?? 0, icon: 'activity', to: ROUTE_NAMES.ACTIVITY },
])

const recent = computed(() => dashboard.value?.recent_activity?.slice(0, 4) ?? [])
const activityLabel = (e) => {
  if (e.summary) return e.summary
  const id = e.record_id ?? e.model_id
  return [e.model, id != null ? `#${id}` : ''].filter(Boolean).join(' ')
}
const topFactories = computed(() => dashboard.value?.top_factories ?? [])
const maxHeadcount = computed(() =>
  Math.max(1, ...topFactories.value.map((f) => f.employees_count ?? 0)),
)
const actionStyle = {
  created: 'bg-emerald-100 text-emerald-700',
  updated: 'bg-amber-100 text-amber-700',
  deleted: 'bg-rose-100 text-rose-700',
}

onMounted(async () => {
  try {
    const [f, d] = await Promise.all([factoriesApi.list(), dashboardApi.get()])
    factories.value = f.data ?? []
    dashboard.value = d
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <PageHeader eyebrow="Overview" title="Dashboard" subtitle="A snapshot of your manufacturing network." />

    <!-- Stat cards -->
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <button
        v-for="s in stats"
        :key="s.label"
        class="card flex items-center gap-4 p-5 text-left transition-shadow hover:shadow-md"
        @click="router.push({ name: s.to })"
      >
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-primary-50 text-primary-600">
          <AppIcon :name="s.icon" :size="20" />
        </span>
        <div>
          <p class="font-serif text-3xl font-semibold leading-none text-ink">
            {{ loading ? '—' : s.value }}
          </p>
          <p class="mt-1 text-sm text-ink/50">{{ s.label }}</p>
        </div>
      </button>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
      <!-- Recent activity -->
      <section class="card p-6">
        <div class="flex items-center justify-between">
          <h2 class="font-serif text-xl font-semibold text-ink">Recent activity</h2>
          <router-link :to="{ name: ROUTE_NAMES.ACTIVITY }" class="text-sm font-medium text-primary-600 hover:underline">
            View all
          </router-link>
        </div>
        <ul class="mt-4 divide-y divide-line/70">
          <li v-for="(e, index) in recent" :key="e.id ?? e.record_id ?? index" class="flex items-center gap-3 py-3">
            <span class="rounded-md px-2 py-0.5 text-xs font-semibold capitalize" :class="actionStyle[e.action]">
              {{ e.action }}
            </span>
            <span class="min-w-0 flex-1 truncate text-sm text-ink/75">{{ activityLabel(e) }}</span>
            <time class="shrink-0 font-mono text-xs text-ink/40">
              {{ e.created_at ? formatShortDateTime(e.created_at) : e.time_ago }}
            </time>
          </li>
          <li v-if="!loading && !recent.length" class="py-6 text-center text-sm text-ink/45">No activity yet.</li>
        </ul>
      </section>

      <!-- Quick actions -->
      <section class="card p-6">
        <h2 class="font-serif text-xl font-semibold text-ink">Quick actions</h2>
        <div class="mt-4 space-y-3">
          <button class="btn-outline w-full justify-start" @click="router.push({ name: ROUTE_NAMES.FACTORY_CREATE })">
            <AppIcon name="plus" :size="16" class="text-primary-600" /> New factory
          </button>
          <button class="btn-outline w-full justify-start" @click="router.push({ name: ROUTE_NAMES.EMPLOYEE_CREATE })">
            <AppIcon name="plus" :size="16" class="text-primary-600" /> New employee
          </button>
          <button class="btn-outline w-full justify-start" @click="router.push({ name: ROUTE_NAMES.ACTIVITY })">
            <AppIcon name="activity" :size="16" class="text-primary-600" /> View activity log
          </button>
        </div>
      </section>
    </div>

    <!-- Top factories by headcount -->
    <section class="card mt-6 p-6">
      <div class="flex items-center justify-between">
        <h2 class="font-serif text-xl font-semibold text-ink">Top factories by headcount</h2>
        <router-link :to="{ name: ROUTE_NAMES.FACTORIES }" class="text-sm font-medium text-primary-600 hover:underline">
          View all →
        </router-link>
      </div>
      <ul class="mt-5 space-y-4">
        <li v-for="f in topFactories" :key="f.id">
          <div class="flex items-center justify-between gap-4">
            <span class="truncate font-semibold text-ink">{{ f.factory_name }}</span>
            <span class="shrink-0 text-sm text-ink/45">{{ f.employees_count ?? 0 }} employees</span>
          </div>
          <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-canvas">
            <div
              class="h-full rounded-full bg-primary transition-all"
              :style="{ width: `${((f.employees_count ?? 0) / maxHeadcount) * 100}%` }"
            />
          </div>
        </li>
        <li v-if="loading" v-for="n in 5" :key="`s${n}`" class="space-y-2">
          <div class="h-4 w-40 animate-pulse rounded bg-canvas" />
          <div class="h-2 w-full animate-pulse rounded-full bg-canvas" />
        </li>
        <li v-if="!loading && !topFactories.length" class="py-6 text-center text-sm text-ink/45">
          No factory data yet.
        </li>
      </ul>
    </section>
  </div>
</template>
