import { ref, computed, watch } from 'vue'
import { debounce } from '@/utils'
import { useToast } from '@/composables/useToast'

/**
 * Shared logic for a server-paginated, searchable list (e.g. Factories, Employees).
 *
 * Handles the items + pagination state, a debounced search box, page navigation,
 * and loading/error handling — so a view only needs to supply the fetcher and any
 * extra query params.
 *
 * @param {(params: object) => Promise<{ data, pagination, [extra] }>} fetchPage
 *   Resource list function, e.g. factoriesApi.list.
 * @param {object} [options]
 * @param {() => object} [options.extraParams] Extra query params merged into each request (e.g. a filter).
 * @param {(res: object) => void} [options.onLoaded] Called with the raw response after each load (e.g. to read res.factories).
 * @param {string} [options.errorMessage] Toast shown when a load fails.
 * @param {number} [options.debounceMs] Debounce delay for the search box.
 */
export function useResourceList(fetchPage, options = {}) {
  const {
    extraParams = () => ({}),
    onLoaded,
    errorMessage = 'Failed to load.',
    debounceMs = 350,
  } = options

  const toast = useToast()

  const items = ref([])
  const pagination = ref({ total: 0, current_page: 1, total_page: 1 })
  const loading = ref(true)
  const search = ref('')
  const page = ref(1)

  const totalPages = computed(() => pagination.value.total_page ?? 1)
  const pageLabel = computed(() => pagination.value.current_page ?? page.value)

  // Guards against overlapping requests: only the most recent load() may update
  // state, so a slow earlier response can't clobber newer results.
  let requestSeq = 0

  async function load() {
    const seq = ++requestSeq
    loading.value = true
    try {
      const params = { page: page.value, search: search.value || undefined, ...extraParams() }
      const res = await fetchPage(params)
      if (seq !== requestSeq) return // a newer request started — drop this stale response
      items.value = res.data ?? []
      pagination.value =
        res.pagination || { total: items.value.length, current_page: page.value, total_page: 1 }
      onLoaded?.(res)
    } catch (e) {
      if (seq !== requestSeq) return // stale failure — the newer request owns the UI
      toast.error(e?.message || errorMessage)
    } finally {
      if (seq === requestSeq) loading.value = false
    }
  }

  // Reset to the first page, then reload. Setting page to 1 reloads via watch(page);
  // if we're already on page 1, load directly so the request still fires.
  function reload() {
    if (page.value !== 1) page.value = 1
    else load()
  }

  // Debounce typing so we hit the API once the user pauses, not on every keystroke.
  watch(search, debounce(reload, debounceMs))
  watch(page, load)

  return { items, pagination, loading, search, page, totalPages, pageLabel, load, reload }
}
