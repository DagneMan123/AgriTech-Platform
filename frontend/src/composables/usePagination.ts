import { ref, computed } from 'vue'

export function usePagination(initialPage = 1, initialLimit = 10) {
  const currentPage = ref(initialPage)
  const limit = ref(initialLimit)
  const total = ref(0)
  const lastPage = ref(1)

  const pages = computed(() => {
    const pages: number[] = []
    const maxPages = 5
    let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2))
    const end = Math.min(lastPage.value, start + maxPages - 1)

    if (end - start < maxPages - 1) {
      start = Math.max(1, end - maxPages + 1)
    }

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    return pages
  })

  const hasNextPage = computed(() => currentPage.value < lastPage.value)
  const hasPrevPage = computed(() => currentPage.value > 1)

  const nextPage = () => {
    if (hasNextPage.value) {
      currentPage.value++
    }
  }

  const prevPage = () => {
    if (hasPrevPage.value) {
      currentPage.value--
    }
  }

  const goToPage = (page: number) => {
    if (page >= 1 && page <= lastPage.value) {
      currentPage.value = page
    }
  }

  const reset = () => {
    currentPage.value = 1
    total.value = 0
    lastPage.value = 1
  }

  return {
    currentPage,
    limit,
    total,
    lastPage,
    pages,
    hasNextPage,
    hasPrevPage,
    nextPage,
    prevPage,
    goToPage,
    reset
  }
}
