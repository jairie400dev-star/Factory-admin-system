<script setup>
// Create / edit a factory. Same component for both — edit mode is keyed off the `id` prop.
import { useRouter } from 'vue-router'
import AppIcon from '@/components/AppIcon.vue'
import { factoriesApi } from '@/api/resources'
import { useResourceForm } from '@/composables/useResourceForm'
import { isValidEmail } from '@/utils'
import { ROUTE_NAMES } from '@/config'

const props = defineProps({ id: { type: [String, Number], default: null } })
const router = useRouter()

const { form, errors, saving, isEdit, submit } = useResourceForm({
  getId: () => props.id,
  api: factoriesApi,
  initialForm: () => ({ factory_name: '', location: '', email: '', website: '' }),
  toForm: (f) => ({
    factory_name: f.factory_name,
    location: f.location,
    email: f.email || '',
    website: f.website || '',
  }),
  validate: (form) => {
    const e = {}
    if (!form.factory_name.trim()) e.factory_name = 'Factory name is required.'
    if (!form.location.trim()) e.location = 'Location is required.'
    if (form.email && !isValidEmail(form.email)) e.email = 'Enter a valid email.'
    return e
  },
  messages: {
    created: 'Factory created.',
    updated: 'Factory updated.',
    loadFailed: 'Failed to load factory.',
    saveFailed: 'Failed to save factory.',
  },
  redirect: { name: ROUTE_NAMES.FACTORIES },
})
</script>

<template>
  <div>
    <nav class="flex items-center gap-1.5 text-sm text-primary-600">
      <router-link :to="{ name: ROUTE_NAMES.FACTORIES }" class="hover:underline">Factories</router-link>
      <AppIcon name="chevron" :size="14" class="-rotate-90 text-ink/30" />
      <span class="text-ink/50">{{ isEdit ? 'Edit factory' : 'New factory' }}</span>
    </nav>

    <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
      {{ isEdit ? 'Edit factory' : 'New factory' }}
    </h1>
    <p class="mt-1.5 text-sm text-ink/55">Add a new manufacturing site to your network.</p>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_300px]">
      <!-- Form card -->
      <form class="card p-6 sm:p-7" @submit.prevent="submit">
        <div class="space-y-5">
          <div>
            <label class="field-label">Factory name <span class="text-primary-500">*</span></label>
            <input v-model="form.factory_name" class="field-input" placeholder="e.g. Meridian Steelworks" />
            <p v-if="errors.factory_name" class="mt-1 text-xs text-rose-600">{{ errors.factory_name }}</p>
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="field-label">Location <span class="text-primary-500">*</span></label>
              <input v-model="form.location" class="field-input" placeholder="City, Country" />
              <p v-if="errors.location" class="mt-1 text-xs text-rose-600">{{ errors.location }}</p>
            </div>
            <div>
              <label class="field-label">Email <span class="text-ink/40 font-normal">optional</span></label>
              <input v-model="form.email" type="email" class="field-input" placeholder="ops@example.com" />
              <p v-if="errors.email" class="mt-1 text-xs text-rose-600">{{ errors.email }}</p>
            </div>
          </div>

          <div>
            <label class="field-label">Website <span class="text-ink/40 font-normal">optional</span></label>
            <input v-model="form.website" class="field-input sm:max-w-sm" placeholder="example.com" />
          </div>
        </div>

        <div class="mt-7 flex items-center gap-2 border-t border-line pt-6">
          <button type="submit" class="btn-primary" :disabled="saving">
            <AppIcon name="check" :size="16" />
            {{ saving ? 'Saving…' : isEdit ? 'Save changes' : 'Create factory' }}
          </button>
          <button type="button" class="btn-ghost" @click="router.push({ name: 'factories' })">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>
