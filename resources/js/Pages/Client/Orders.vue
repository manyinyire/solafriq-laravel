<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import {
  ShoppingCart,
  Package,
  Eye,
  Download,
  CreditCard,
  Calendar,
  DollarSign,
  Truck,
  CheckCircle,
  Clock,
  AlertCircle,
  XCircle,
  Search,
  Filter
} from 'lucide-vue-next'

const loading = ref(true)
const orders = ref([])
const pagination = ref({})
const searchQuery = ref('')
const statusFilter = ref('')
const currentPage = ref(1)

const statusOptions = [
  { value: '', label: 'All Orders' },
  { value: 'PENDING', label: 'Pending' },
  { value: 'CONFIRMED', label: 'Confirmed' },
  { value: 'PROCESSING', label: 'Processing' },
  { value: 'SHIPPED', label: 'Shipped' },
  { value: 'DELIVERED', label: 'Delivered' },
  { value: 'CANCELLED', label: 'Cancelled' }
]

onMounted(async () => {
  await loadOrders()
})

const loadOrders = async (page = 1) => {
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

    const response = await fetch(`/orders-data?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      orders.value = data.data
      pagination.value = data.meta
      currentPage.value = page
    }
  } catch (error) {
    console.error('Failed to load orders:', error)
  } finally {
    loading.value = false
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
    case 'delivered':
      return 'bg-green-100 text-green-800'
    case 'shipped':
      return 'bg-blue-100 text-blue-800'
    case 'processing':
    case 'confirmed':
      return 'bg-yellow-100 text-yellow-800'
    case 'pending':
      return 'bg-gray-100 text-gray-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusIcon = (status) => {
  switch (status?.toLowerCase()) {
    case 'delivered':
      return CheckCircle
    case 'shipped':
      return Truck
    case 'processing':
    case 'confirmed':
      return Package
    case 'pending':
      return Clock
    case 'cancelled':
      return XCircle
    default:
      return AlertCircle
  }
}

const handleSearch = () => {
  loadOrders(1)
}

const handleStatusFilter = () => {
  loadOrders(1)
}

const goToPage = (page) => {
  loadOrders(page)
}

const filteredOrders = computed(() => {
  return orders.value || []
})
</script>

<template>
  <ClientLayout>
    <Head title="My Orders" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
            <p class="text-gray-600 mt-1">Track and manage your solar system orders</p>
          </div>
          <div class="flex items-center space-x-3">
            <a href="/" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
              Browse Systems
            </a>
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
              placeholder="Search orders..."
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

      <!-- Orders List -->
      <div v-else-if="filteredOrders.length > 0" class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            Orders ({{ pagination.total || 0 }})
          </h3>
        </div>
        <div class="divide-y divide-gray-200">
          <div
            v-for="order in filteredOrders"
            :key="order.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                  <component :is="getStatusIcon(order.status)" class="h-8 w-8 text-orange-600" />
                </div>
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">Order #{{ order.id }}</h4>
                  <p class="text-sm text-gray-600">
                    Placed on {{ formatDate(order.created_at) }}
                  </p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span :class="getStatusColor(order.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                      {{ order.status }}
                    </span>
                    <span class="text-sm text-gray-600">
                      {{ order.items?.length || 0 }} item(s)
                    </span>
                  </div>
                </div>
              </div>

              <div class="text-right">
                <p class="text-2xl font-bold text-gray-900">
                  {{ formatCurrency(order.total_amount) }}
                </p>
                <div class="flex items-center space-x-2 mt-2">
                  <Link
                    :href="`/orders/${order.id}`"
                    class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    <Eye class="h-4 w-4 mr-1" />
                    View
                  </Link>
                  <button
                    v-if="order.invoice_id"
                    class="inline-flex items-center px-3 py-1 border border-orange-300 rounded-md text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100"
                  >
                    <Download class="h-4 w-4 mr-1" />
                    Invoice
                  </button>
                </div>
              </div>
            </div>

            <!-- Order Items Preview -->
            <div v-if="order.items && order.items.length > 0" class="mt-4 pt-4 border-t border-gray-100">
              <h5 class="text-sm font-medium text-gray-700 mb-2">Items:</h5>
              <div class="space-y-2">
                <div
                  v-for="item in order.items.slice(0, 2)"
                  :key="item.id"
                  class="flex items-center justify-between text-sm"
                >
                  <span class="text-gray-600">
                    {{ item.solar_system?.name || 'Solar System' }} × {{ item.quantity }}
                  </span>
                  <span class="font-medium text-gray-900">
                    {{ formatCurrency(item.unit_price * item.quantity) }}
                  </span>
                </div>
                <div v-if="order.items.length > 2" class="text-sm text-gray-500">
                  +{{ order.items.length - 2 }} more item(s)
                </div>
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
        <ShoppingCart class="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No orders found</h3>
        <p class="text-gray-600 mb-6">
          {{ searchQuery || statusFilter ? 'No orders match your current filters.' : "You haven't placed any orders yet." }}
        </p>
        <div class="flex justify-center space-x-4">
          <button
            v-if="searchQuery || statusFilter"
            @click="searchQuery = ''; statusFilter = ''; loadOrders(1)"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Clear Filters
          </button>
          <a
            href="/"
            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition-colors"
          >
            Browse Systems
          </a>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>