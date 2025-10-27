<template>
  <ClientLayout>
    <Head title="Client Dashboard" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold">Welcome back, {{ $page.props.auth?.user?.name }}!</h1>
            <p class="text-orange-100 mt-1">Here's an overview of your solar systems and account.</p>
          </div>
          <div class="text-right">
            <p class="text-orange-100 text-sm">Today</p>
            <p class="text-xl font-semibold">{{ new Date().toLocaleDateString('en-US', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            }) }}</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="i in 4" :key="i" class="bg-white rounded-lg shadow p-6">
          <div class="animate-pulse">
            <div class="flex items-center">
              <div class="w-12 h-12 bg-gray-200 rounded-lg"></div>
              <div class="ml-4 flex-1">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-6 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <ShoppingCart class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Orders</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.total_orders?.toLocaleString() || 0 }}
              </p>
              <p class="text-sm text-gray-500 mt-1">All time</p>
            </div>
          </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <Clock class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Pending Orders</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.pending_orders?.toLocaleString() || 0 }}
              </p>
              <p class="text-sm text-gray-500 mt-1">In progress</p>
            </div>
          </div>
        </div>

        <!-- Total Spent -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <DollarSign class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Spent</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(dashboardData.stats?.total_spent || 0) }}
              </p>
              <p class="text-sm text-gray-500 mt-1">Investment</p>
            </div>
          </div>
        </div>

        <!-- Active Warranties -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <Shield class="h-6 w-6 text-purple-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Active Warranties</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.active_warranties?.toLocaleString() || 0 }}
              </p>
              <p class="text-sm text-gray-500 mt-1">Protected</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Dashboard Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <ShoppingCart class="h-5 w-5 text-gray-400 mr-3" />
                <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
              </div>
              <Link href="/orders" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                View All
              </Link>
            </div>
          </div>
          <div class="p-6">
            <div v-if="dashboardData.recent_orders?.length" class="space-y-4">
              <div
                v-for="(order, index) in dashboardData.recent_orders"
                :key="index"
                class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Package class="h-5 w-5 text-orange-600" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">Order #{{ order.id }}</p>
                    <p class="text-sm text-gray-600">{{ formatDate(order.created_at) }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">{{ formatCurrency(order.total_amount) }}</p>
                  <span :class="getStatusColor(order.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                    {{ order.status }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <ShoppingCart class="h-12 w-12 text-gray-300 mx-auto mb-4" />
              <p class="text-gray-500">No orders yet</p>
              <Link href="/packages" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                Browse packages
              </Link>
            </div>
          </div>
        </div>

        <!-- Installment Summary -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <CreditCard class="h-5 w-5 text-gray-400 mr-3" />
                <h3 class="text-lg font-semibold text-gray-900">Payment Plans</h3>
              </div>
              <Link href="/installments" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                View All
              </Link>
            </div>
          </div>
          <div class="p-6">
            <div v-if="dashboardData.installment_summary?.total_plans > 0">
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Active Plans:</span>
                  <span class="font-semibold">{{ dashboardData.installment_summary.total_plans }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total Remaining:</span>
                  <span class="font-semibold text-orange-600">
                    {{ formatCurrency(dashboardData.installment_summary.total_remaining || 0) }}
                  </span>
                </div>
                <div v-if="dashboardData.installment_summary.next_payment_date" class="border-t pt-4">
                  <p class="text-sm text-gray-600 mb-2">Next Payment Due:</p>
                  <p class="font-semibold">{{ formatDate(dashboardData.installment_summary.next_payment_date) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <CreditCard class="h-12 w-12 text-gray-300 mx-auto mb-4" />
              <p class="text-gray-500">No active payment plans</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Link
            href="/packages"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200">
              <Zap class="h-5 w-5 text-blue-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">Browse Systems</p>
              <p class="text-sm text-gray-600">View solar packages</p>
            </div>
          </Link>

          <Link
            href="/orders"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200">
              <ShoppingCart class="h-5 w-5 text-green-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">My Orders</p>
              <p class="text-sm text-gray-600">Track your orders</p>
            </div>
          </Link>

          <Link
            href="/invoices"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200">
              <FileText class="h-5 w-5 text-indigo-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">Invoices</p>
              <p class="text-sm text-gray-600">View and pay bills</p>
            </div>
          </Link>

          <Link
            href="/warranties"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200">
              <Shield class="h-5 w-5 text-purple-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">Warranties</p>
              <p class="text-sm text-gray-600">View coverage</p>
            </div>
          </Link>

          <Link
            href="/support"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200">
              <Wrench class="h-5 w-5 text-orange-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">Get Support</p>
              <p class="text-sm text-gray-600">Contact our team</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import {
  ShoppingCart,
  DollarSign,
  Shield,
  Zap,
  Clock,
  CreditCard,
  Package,
  Wrench,
  FileText
} from 'lucide-vue-next'
import { formatCurrency, formatDate, getStatusColor } from '@/utils/formatters'

const page = usePage()
const loading = ref(true)
const dashboardData = ref({
  stats: {},
  recent_orders: [],
  installment_summary: {}
})

onMounted(async () => {
  await loadDashboardData()
})

const loadDashboardData = async () => {
  loading.value = true
  try {
    // Load all dashboard data in parallel
    const [statsResponse, ordersResponse, installmentResponse] = await Promise.all([
      fetch('/dashboard/stats', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      }),
      fetch('/dashboard/recent-orders', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      }),
      fetch('/dashboard/installment-summary', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      })
    ])

    const [stats, orders, installments] = await Promise.all([
      statsResponse.json(),
      ordersResponse.json(),
      installmentResponse.json()
    ])

    // Handle standardized response format (with data wrapper)
    dashboardData.value = {
      stats: stats.success ? stats.data : stats,
      recent_orders: orders.success ? orders.data : orders,
      installment_summary: installments.success ? installments.data : installments
    }
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
    // Enhanced error handling
    if (error.response?.status === 401) {
      window.location.href = '/login'
    } else if (error.response?.status === 403) {
      alert('You do not have permission to view this data')
    } else {
      alert('Failed to load dashboard. Please refresh the page.')
    }
  } finally {
    loading.value = false
  }
}

// All formatting functions are now imported from @/utils/formatters
</script>