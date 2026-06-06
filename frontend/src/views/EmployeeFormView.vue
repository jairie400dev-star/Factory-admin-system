<script setup>
// Create / edit an employee. Same component for both — edit mode is keyed off the `id` prop.
// Loads the full factory list (all pages) to populate the factory <select>.
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppIcon from '@/components/AppIcon.vue'
import { employeesApi, factoriesApi } from '@/api/resources'
import { useResourceForm } from '@/composables/useResourceForm'
import { isValidEmail } from '@/utils'
import { ROUTE_NAMES } from '@/config'

const props = defineProps({ id: { type: [String, Number], default: null } })
const router = useRouter()

const factories = ref([]) // options for the factory <select>

const { form, errors, saving, isEdit, submit } = useResourceForm({
  getId: () => props.id,
  api: employeesApi,
  initialForm: () => ({ firstname: '', lastname: '', factory_id: '', email: '', phone: '' }),
  toForm: (e) => ({
    firstname: (e.firstname ?? e.first_name) || '',
    lastname: (e.lastname ?? e.last_name) || '',
    factory_id: String(e.factory_id ?? ''),
    email: e.email || '',
    phone: e.phone || '',
  }),
  validate: (form) => {
    const e = {}
    if (!form.firstname.trim()) e.firstname = 'First name is required.'
    if (!form.lastname.trim()) e.lastname = 'Last name is required.'
    if (!form.factory_id) e.factory_id = 'Please choose a factory.'
    if (form.email && !isValidEmail(form.email)) e.email = 'Enter a valid email.'
    return e
  },
  // factory_id is a string in the <select>; the API expects a number.
  toPayload: (form) => ({ ...form, factory_id: Number(form.factory_id) }),
  messages: {
    created: 'Employee created.',
    updated: 'Employee updated.',
    loadFailed: 'Failed to load employee.',
    saveFailed: 'Failed to save employee.',
  },
  redirect: { name: ROUTE_NAMES.EMPLOYEES },
})

// Load factory options for the dropdown (all pages).
onMounted(async () => {
  try {
    factories.value = await factoriesApi.listAll()
  } catch {
    /* ignore — the dropdown just stays empty */
  }
})
</script>

<template>
  <div>
    <nav class="flex items-center gap-1.5 text-sm text-primary-600">
      <router-link :to="{ name: ROUTE_NAMES.EMPLOYEES }" class="hover:underline">Employees</router-link>
      <AppIcon name="chevron" :size="14" class="-rotate-90 text-ink/30" />
      <span class="text-ink/50">{{ isEdit ? 'Edit employee' : 'New employee' }}</span>
    </nav>

    <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
      {{ isEdit ? 'Edit employee' : 'New employee' }}
    </h1>
    <p class="mt-1.5 text-sm text-ink/55">Add a new person to a factory.</p>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_300px]">
      <form class="card p-6 sm:p-7" @submit.prevent="submit">
        <div class="space-y-5">
          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="field-label">First name <span class="text-primary-500">*</span></label>
              <input v-model="form.firstname" class="field-input" placeholder="First name" />
              <p v-if="errors.firstname" class="mt-1 text-xs text-rose-600">{{ errors.firstname }}</p>
            </div>
            <div>
              <label class="field-label">Last name <span class="text-primary-500">*</span></label>
              <input v-model="form.lastname" class="field-input" placeholder="Last name" />
              <p v-if="errors.lastname" class="mt-1 text-xs text-rose-600">{{ errors.lastname }}</p>
            </div>
          </div>

          <div>
            <label class="field-label">Factory <span class="text-primary-500">*</span></label>
            <div class="relative">
              <select v-model="form.factory_id" class="field-input appearance-none pr-9">
                <option value="" disabled>Select a factory</option>
                <option v-for="f in factories" :key="f.id" :value="String(f.id)">{{ f.factory_name }}</option>
              </select>
              <AppIcon name="chevron" :size="16" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink/40" />
            </div>
            <p v-if="errors.factory_id" class="mt-1 text-xs text-rose-600">{{ errors.factory_id }}</p>
          </div>

          <div class="grid gap-5 sm:grid-cols-2">
            <div>
              <label class="field-label">Email <span class="text-ink/40 font-normal">optional</span></label>
              <input v-model="form.email" type="email" class="field-input" placeholder="name@example.com" />
              <p v-if="errors.email" class="mt-1 text-xs text-rose-600">{{ errors.email }}</p>
            </div>
            <div>
              <label class="field-label">Phone <span class="text-ink/40 font-normal">optional</span></label>
              <input v-model="form.phone" class="field-input" placeholder="+1 555 000 000" />
            </div>
          </div>
        </div>

        <div class="mt-7 flex items-center gap-2 border-t border-line pt-6">
          <button type="submit" class="btn-primary" :disabled="saving">
            <AppIcon name="check" :size="16" />
            {{ saving ? 'Saving…' : isEdit ? 'Save changes' : 'Create employee' }}
          </button>
          <button type="button" class="btn-ghost" @click="router.push({ name: ROUTE_NAMES.EMPLOYEES })">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>
