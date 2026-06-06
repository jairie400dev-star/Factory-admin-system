import { ref } from 'vue'
import { factoriesApi, employeesApi } from '@/api/resources'

// Shared, app-wide resource counts for the sidebar badges. Module-level state so
// every caller sees the same reactive object (same pattern as useToast).
const counts = ref({ factories: null, employees: null })

export function useCounts() {
  // Update one count (ignores null/undefined so a failed lookup won't blank the badge).
  function set(key, value) {
    if (value != null) counts.value[key] = value
  }

  // Fetch both totals fresh (used on first load). List views keep these current
  // afterwards by calling set() with the pagination.total from their own requests.
  async function refresh() {
    try {
      const [f, e] = await Promise.all([factoriesApi.list(), employeesApi.list()])
      set('factories', f?.pagination?.total ?? (Array.isArray(f?.data) ? f.data.length : null))
      set('employees', e?.pagination?.total ?? (Array.isArray(e?.data) ? e.data.length : null))
    } catch {
      /* ignore — badges just keep their previous value */
    }
  }

  return { counts, set, refresh }
}
