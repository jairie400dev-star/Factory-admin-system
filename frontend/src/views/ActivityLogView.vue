<script setup>
// Activity log: model events from GET /api/logs, shown as a timeline or a faux laravel.log.
// Filter pills (created/updated/deleted) filter client-side; the timeline is virtualized
// (LazyItem) so only cards near the viewport are rendered.
import { ref, computed, onMounted } from 'vue'
import PageHeader from '@/components/PageHeader.vue'
import AppIcon from '@/components/AppIcon.vue'
import LazyItem from '@/components/LazyItem.vue'
import { activityApi } from '@/api/resources'
import { useToast } from '@/composables/useToast'
import { formatShortDateTime } from '@/utils'

const toast = useToast()
const events = ref([])
const loading = ref(true) // first load → skeleton
const mode = ref('timeline') // 'timeline' | 'log'
const actionFilter = ref('all') // all | created | updated | deleted

const actionStyle = {
  created: 'bg-emerald-100 text-emerald-700',
  updated: 'bg-amber-100 text-amber-700',
  deleted: 'bg-rose-100 text-rose-700',
}
const actionIcon = { created: 'plus', updated: 'pencil', deleted: 'trash' }

const filters = computed(() => [
  { key: 'all', label: `All (${events.value.length})` },
  { key: 'created', label: 'Created' },
  { key: 'updated', label: 'Updated' },
  { key: 'deleted', label: 'Deleted' },
])

// Filter client-side so it works regardless of backend query-param support.
const filtered = computed(() =>
  actionFilter.value === 'all'
    ? events.value
    : events.value.filter((e) => String(e.action).toLowerCase() === actionFilter.value),
)

// Render each event as a laravel.log line.
const logLines = computed(() =>
  filtered.value.map((e) => {
    const payload = { model: e.model, id: e.record_id ?? e.model_id, user_id: e.user_id }
    if (e.changes) {
      payload.changes = Object.fromEntries(
        Object.entries(e.changes).map(([k, v]) => [k, { old: v.old, new: v.new }]),
      )
    }
    return {
      time: e.created_at,
      action: e.action,
      json: JSON.stringify(payload),
    }
  }),
)

async function load() {
  loading.value = true
  try {
    events.value = await activityApi.list()
  } catch (e) {
    toast.error(e?.message || 'Failed to load activity.')
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <PageHeader
      eyebrow="Model event service"
      title="Activity log"
      subtitle="Every create, update, and delete on Factories and Employees is captured and written to laravel.log."
    >
      <template #actions>
        <div class="inline-flex rounded-lg border border-line bg-surface p-1">
          <button
            v-for="m in [{ k: 'timeline', l: 'Timeline' }, { k: 'log', l: 'laravel.log' }]"
            :key="m.k"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
            :class="mode === m.k ? 'bg-ink text-white' : 'text-ink/55 hover:text-ink'"
            @click="mode = m.k"
          >
            {{ m.l }}
          </button>
        </div>
      </template>
    </PageHeader>

    <!-- Filter pills -->
    <div class="mt-7 flex flex-wrap gap-2">
      <button
        v-for="f in filters"
        :key="f.key"
        class="rounded-full border px-3.5 py-1.5 text-sm font-medium transition-colors"
        :class="actionFilter === f.key
          ? 'border-primary-300 bg-primary-50 text-primary-700'
          : 'border-line bg-surface text-ink/60 hover:border-ink/20'"
        @click="actionFilter = f.key"
      >
        {{ f.label }}
      </button>
    </div>

    <!-- Loading skeleton (first load only) -->
    <div v-if="loading" class="mt-6 space-y-3">
      <div v-for="n in 3" :key="n" class="h-28 animate-pulse rounded-2xl bg-surface" />
    </div>

    <div v-else class="mt-6">
      <!-- Timeline -->
      <div v-if="mode === 'timeline'" class="space-y-4">
        <p v-if="!filtered.length" class="card p-10 text-center text-sm text-ink/45">No events to show.</p>

      <LazyItem v-for="(e, index) in filtered" :key="`${actionFilter}-${index}`" :min-height="110">
        <article class="card p-5">
        <div class="flex flex-wrap items-start gap-3">
          <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg" :class="actionStyle[e.action]">
            <AppIcon :name="actionIcon[e.action]" :size="16" />
          </span>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-md px-2 py-0.5 text-xs font-semibold capitalize" :class="actionStyle[e.action]">
                {{ e.action }}
              </span>
              <span class="font-semibold text-ink">{{ e.model }}</span>
              <span class="text-sm text-ink/40">#{{ e.record_id ?? e.model_id }}</span>
            </div>
            <p class="mt-1 text-sm text-ink/70">{{ e.summary }}</p>
          </div>
          <time class="shrink-0 font-mono text-xs text-ink/40">
            {{ e.created_at ? formatShortDateTime(e.created_at) : e.time_ago }}
          </time>
        </div>

        <!-- Change diff table -->
        <div v-if="e.changes" class="mt-4 overflow-hidden rounded-lg border border-line">
          <table class="w-full border-collapse text-sm">
            <thead class="bg-canvas/70">
              <tr>
                <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-ink/45">Field</th>
                <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-ink/45">Old</th>
                <th class="px-4 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-ink/45">New</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(val, field) in e.changes" :key="field" class="border-t border-line/70">
                <td class="px-4 py-2 font-mono text-[13px] text-ink/70">{{ field }}</td>
                <td class="px-4 py-2 font-mono text-[13px] text-rose-600">{{ val.old }}</td>
                <td class="px-4 py-2 font-mono text-[13px] text-emerald-600">{{ val.new }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="mt-3 flex items-center gap-1.5 text-xs text-ink/40">
          <AppIcon name="employees" :size="13" />
          user_id: {{ e.user_id }}{{ e.user_email ? ` · ${e.user_email}` : '' }}
        </p>
        </article>
      </LazyItem>
    </div>

      <!-- laravel.log terminal -->
      <div v-else class="overflow-hidden rounded-xl bg-ink-900 shadow-card">
      <div class="flex items-center gap-2 border-b border-white/10 px-4 py-3">
        <span class="h-3 w-3 rounded-full bg-rose-400" />
        <span class="h-3 w-3 rounded-full bg-amber-400" />
        <span class="h-3 w-3 rounded-full bg-emerald-400" />
        <span class="ml-3 font-mono text-xs text-white/50">storage/logs/laravel.log</span>
      </div>
      <div class="scroll-thin max-h-[60vh] overflow-auto px-5 py-4 font-mono text-[13px] leading-relaxed">
        <p v-for="(line, i) in logLines" :key="i" class="whitespace-pre-wrap break-words text-white/70">
          <span class="text-white/40">[{{ line.time }}]</span>
          <span class="text-sky-300"> local.INFO:</span>
          <span class="text-white/90"> Model </span>
          <span :class="{
            'text-emerald-400': line.action === 'created',
            'text-amber-400': line.action === 'updated',
            'text-rose-400': line.action === 'deleted',
          }">{{ line.action }}</span>
          <span class="text-white/70"> {{ line.json }}</span>
        </p>
        <p class="mt-1 text-emerald-400">$ <span class="animate-pulse">▌</span></p>
      </div>
    </div>
    </div>
  </div>
</template>
