<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import {
  FileText,
  Download,
  CreditCard,
  Eye,
  Calendar,
  DollarSign,
  CheckCircle,
  Clock,
  AlertCircle,
  XCircle,
  Search,
  Filter,
  Plus
} from 'lucide-vue-next'

const loading = ref(true)
const invoices = ref([])
const pagination = ref({})
const stats = ref({})
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)

const statusOptions = [
  { value: '', label: 'All Invoices' },
  { value: 'PENDING', label: 'Pending' },
  { value: 'PAID', label: 'Paid' },
  { value: 'OVERDUE', label: 'Overdue' },
  { value: 'CANCELLED', label: 'Cancelled' }
]

onMounted(async () => {
  await Promise.all([loadInvoices(), loadStats()])
})

const loadInvoices = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: '10'
    })

    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }

    if (statusFilter.value) {
      params.append('status', statusFilter.value)
    }

    const response = await fetch(`/api/v1/invoices?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      invoices.value = data.data
      pagination.value = data.meta
      currentPage.value = page
    }
  } catch (error) {
    console.error('Failed to load invoices:', error)
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await fetch('/api/v1/invoices/stats', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      stats.value = data.data
    }
  } catch (error) {
    console.error('Failed to load invoice stats:', error)
  }
}

const downloadInvoice = async (invoice) => {
  try {
    const response = await fetch(`/api/v1/invoices/${invoice.id}/download`, {
      headers: {
        'Accept': 'application/pdf',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `invoice-${invoice.invoice_number}.pdf`
      a.click()
      window.URL.revokeObjectURL(url)
    }
  } catch (error) {
    console.error('Failed to download invoice:', error)
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'overdue':
      return 'bg-red-100 text-red-800'
    case 'cancelled':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusIcon = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid':
      return CheckCircle
    case 'pending':
      return Clock
    case 'overdue':
      return AlertCircle
    case 'cancelled':
      return XCircle
    default:
      return FileText
  }
}

const handleSearch = () => {
  loadInvoices(1)
}

const handleStatusFilter = () => {
  loadInvoices(1)
}

const goToPage = (page) => {
  loadInvoices(page)
}

const filteredInvoices = computed(() => {
  return invoices.value || []
})
</script>

<template>
  <ClientLayout>
    <Head title="My Invoices" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">My Invoices</h1>
            <p class="text-gray-600 mt-1">View and manage your invoices and payments</p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <FileText class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Invoices</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ stats.total_invoices || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <CheckCircle class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Paid</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ stats.paid_invoices || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <Clock class="h-6 w-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Pending</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ stats.pending_invoices || 0 }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <DollarSign class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Outstanding</p>
              <p class="text-xl font-bold text-gray-900">
                {{ formatCurrency(stats.outstanding_amount || 0) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search invoices..."
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              @keyup.enter="handleSearch"
            />
          </div>

          <!-- Status Filter -->
          <div class="relative">
            <Filter class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <select
              v-model="statusFilter"
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              @change="handleStatusFilter"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <!-- Search Button -->
          <button
            @click="handleSearch"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
          >
            Search
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-6">
        <div class="space-y-4">
          <div v-for="i in 5" :key="i" class="animate-pulse">
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

      <!-- Invoices List -->
      <div v-else-if="filteredInvoices.length > 0" class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            Invoices ({{ pagination.total || 0 }})
          </h3>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="invoice in filteredInvoices"
            :key="invoice.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                  <component :is="getStatusIcon(invoice.status)" class="h-8 w-8 text-blue-600" />
                </div>
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">
                    Invoice #{{ invoice.invoice_number }}
                  </h4>
                  <p class="text-sm text-gray-600">
                    Due: {{ formatDate(invoice.due_date) }}
                  </p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span :class="getStatusColor(invoice.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                      {{ invoice.status }}
                    </span>
                    <span class="text-sm text-gray-600">
                      Order #{{ invoice.order?.id }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="text-right">
                <p class="text-2xl font-bold text-gray-900">
                  {{ formatCurrency(invoice.total_amount) }}
                </p>
                <p v-if="invoice.paid_amount > 0" class="text-sm text-gray-600">
                  Paid: {{ formatCurrency(invoice.paid_amount) }}
                </p>
                <div class="flex items-center space-x-2 mt-2">
                  <Link
                    :href="`/invoices/${invoice.id}`"
                    class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <Eye class="h-4 w-4 mr-1" />
                    View
                  </Link>
                  <button
                    @click="downloadInvoice(invoice)"
                    class="inline-flex items-center px-3 py-1 border border-blue-300 rounded-md text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100"
                  >
                    <Download class="h-4 w-4 mr-1" />
                    PDF
                  </button>
                  <button
                    v-if="invoice.status !== 'PAID'"
                    class="inline-flex items-center px-3 py-1 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100"
                  >
                    <CreditCard class="h-4 w-4 mr-1" />
                    Pay
                  </button>
                </div>
              </div>
            </div>

            <!-- Payment Progress -->
            <div v-if="invoice.status !== 'PAID' && invoice.paid_amount > 0" class="mt-4 pt-4 border-t border-gray-100">
              <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Payment Progress</span>
                <span>{{ Math.round((invoice.paid_amount / invoice.total_amount) * 100) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-green-500 h-2 rounded-full"
                  :style="{ width: `${(invoice.paid_amount / invoice.total_amount) * 100}%` }"
                ></div>
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
        <FileText class="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No invoices found</h3>
        <p class="text-gray-600 mb-6">
          {{ searchQuery || statusFilter ? 'No invoices match your current filters.' : "You don't have any invoices yet." }}
        </p>
        <div class="flex justify-center space-x-4">
          <button
            v-if="searchQuery || statusFilter"
            @click="searchQuery = ''; statusFilter = ''; loadInvoices(1)"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Clear Filters
          </button>
          <Link
            href="/packages"
            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition-colors"
          >
            Browse Systems
          </Link>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>