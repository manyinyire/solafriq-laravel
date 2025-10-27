<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import { Shield, Download, Calendar, Clock, CheckCircle, AlertCircle } from 'lucide-vue-next'
import { formatDate, getStatusColor } from '@/utils/formatters'

const loading = ref(true)
const warranties = ref([])
const pagination = ref({})

onMounted(async () => {
  await loadWarranties()
})

const loadWarranties = async (page = 1) => {
  loading.value = true
  try {
    const response = await fetch(`/warranties-data?page=${page}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    warranties.value = data.data || []
    pagination.value = data.meta || {}
  } catch (error) {
    console.error('Failed to load warranties:', error)
  } finally {
    loading.value = false
  }
}

// All formatting functions imported from @/utils/formatters

const getWarrantyStatusColor = (status) => {
  return getStatusColor(status, 'warranty')
}

const downloadCertificate = async (warrantyId) => {
  try {
    window.open(`/warranties/${warrantyId}/certificate`, '_blank')
  } catch (error) {
    console.error('Failed to download certificate:', error)
  }
}

const goToPage = (page) => {
  loadWarranties(page)
}
</script>

<template>
  <ClientLayout>
    <Head title="My Warranties" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">My Warranties</h1>
            <p class="text-gray-600 mt-1">View and manage your product warranties</p>
          </div>
          <Shield class="h-12 w-12 text-orange-500" />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-6">
        <div class="space-y-4">
          <div v-for="i in 3" :key="i" class="animate-pulse">
            <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
              <div class="w-16 h-16 bg-gray-200 rounded-lg"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 rounded w-1/4 mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
              <div class="w-20 h-6 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Warranties List -->
      <div v-else-if="warranties.length > 0" class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            Active Warranties ({{ pagination.total || 0 }})
          </h3>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="warranty in warranties"
            :key="warranty.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                  <Shield class="h-8 w-8 text-orange-600" />
                </div>
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ warranty.product_name }}</h4>
                  <p class="text-sm text-gray-600">
                    Serial: {{ warranty.serial_number }}
                  </p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span :class="getStatusColor(warranty.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                      {{ warranty.status }}
                    </span>
                    <span class="text-sm text-gray-600 flex items-center">
                      <Calendar class="h-4 w-4 mr-1" />
                      {{ warranty.warranty_period_months }} months
                    </span>
                  </div>
                </div>
              </div>

              <div class="text-right">
                <div class="space-y-2">
                  <div class="text-sm text-gray-600">
                    <div class="flex items-center justify-end">
                      <Clock class="h-4 w-4 mr-1" />
                      {{ warranty.remaining_days }} days left
                    </div>
                  </div>
                  <div class="text-sm text-gray-600">
                    Expires: {{ formatDate(warranty.end_date) }}
                  </div>
                  <button
                    @click="downloadCertificate(warranty.id)"
                    class="inline-flex items-center px-3 py-1 border border-orange-300 rounded-md text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100"
                  >
                    <Download class="h-4 w-4 mr-1" />
                    Certificate
                  </button>
                </div>
              </div>
            </div>

            <!-- Warranty Details -->
            <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm font-medium text-gray-700">Start Date</p>
                <p class="text-sm text-gray-600">{{ formatDate(warranty.start_date) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-700">End Date</p>
                <p class="text-sm text-gray-600">{{ formatDate(warranty.end_date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
              {{ pagination.total }} results
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <span class="px-3 py-1 text-sm font-medium text-gray-700">
                Page {{ pagination.current_page }} of {{ pagination.last_page }}
              </span>
              <button
                @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow p-12 text-center">
        <Shield class="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No warranties found</h3>
        <p class="text-gray-600 mb-6">
          You don't have any active warranties yet. Warranties are automatically created when your orders are installed.
        </p>
      </div>
    </div>
  </ClientLayout>
</template>
