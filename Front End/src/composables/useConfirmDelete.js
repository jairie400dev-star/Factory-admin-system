import { ref } from 'vue'
import { useToast } from '@/composables/useToast'

/**
 * Shared confirm-and-delete flow for list rows.
 *
 * `target` holds the row pending deletion (also drives the ConfirmDialog's open
 * state); `confirm()` performs the delete with toasts and an optional callback.
 *
 * @param {(id: string|number) => Promise<any>} removeFn Resource remove function, e.g. factoriesApi.remove.
 * @param {object} [options]
 * @param {(item: object) => string} [options.successMessage] Toast on success.
 * @param {string} [options.errorMessage] Toast on failure.
 * @param {(item: object) => any} [options.onDeleted] Runs after a successful delete (e.g. reload the list).
 */
export function useConfirmDelete(removeFn, options = {}) {
  const {
    successMessage = () => 'Deleted.',
    errorMessage = 'Failed to delete.',
    onDeleted,
  } = options

  const toast = useToast()

  const target = ref(null)
  const deleting = ref(false)

  async function confirm() {
    if (!target.value) return
    const item = target.value
    deleting.value = true
    try {
      await removeFn(item.id)
      toast.success(successMessage(item))
      target.value = null
      await onDeleted?.(item)
    } catch (e) {
      toast.error(e?.message || errorMessage)
    } finally {
      deleting.value = false
    }
  }

  return { target, deleting, confirm }
}
