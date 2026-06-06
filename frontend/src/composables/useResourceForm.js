import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'

/**
 * Shared logic for a create/edit form backed by a REST resource.
 *
 * Owns the form state, validation, submit (create vs. update), Laravel 422 error
 * mapping, and the edit-mode record fetch — so a view only declares its fields,
 * rules, and messages.
 *
 * @param {object} options
 * @param {() => (string|number|null)} options.getId Returns the record id (null = create mode).
 * @param {{ get, create, update }} options.api Resource service.
 * @param {() => object} options.initialForm Factory for the blank form object.
 * @param {(record: object) => object} [options.toForm] Maps a fetched record onto the form shape.
 * @param {(form: object) => object} [options.validate] Returns an errors map ({} = valid).
 * @param {(form: object) => object} [options.toPayload] Maps the form to the request body.
 * @param {{ created, updated, loadFailed, saveFailed }} [options.messages]
 * @param {object} options.redirect Route location to push to after a successful save.
 */
export function useResourceForm(options) {
  const {
    getId,
    api,
    initialForm,
    toForm = (record) => ({ ...record }),
    validate = () => ({}),
    toPayload = (form) => form,
    messages = {},
    redirect,
  } = options

  const router = useRouter()
  const toast = useToast()

  const isEdit = computed(() => getId() != null)
  const form = ref(initialForm())
  const errors = ref({})
  const saving = ref(false)
  const loading = ref(false)

  async function submit() {
    errors.value = validate(form.value)
    if (Object.keys(errors.value).length) return

    saving.value = true
    try {
      if (isEdit.value) {
        await api.update(getId(), toPayload(form.value))
        toast.success(messages.updated || 'Saved.')
      } else {
        await api.create(toPayload(form.value))
        toast.success(messages.created || 'Created.')
      }
      router.push(redirect)
    } catch (e) {
      // Surface Laravel validation errors ({ field: [msg] }) on the matching fields.
      if (e?.errors) {
        errors.value = Object.fromEntries(Object.entries(e.errors).map(([k, v]) => [k, v[0]]))
      }
      toast.error(e?.message || messages.saveFailed || 'Failed to save.')
    } finally {
      saving.value = false
    }
  }

  // In edit mode, load the existing record and populate the form.
  onMounted(async () => {
    if (!isEdit.value) return
    loading.value = true
    try {
      const record = await api.get(getId())
      if (record) form.value = toForm(record)
    } catch (e) {
      toast.error(e?.message || messages.loadFailed || 'Failed to load.')
    } finally {
      loading.value = false
    }
  })

  return { form, errors, saving, loading, isEdit, submit }
}
