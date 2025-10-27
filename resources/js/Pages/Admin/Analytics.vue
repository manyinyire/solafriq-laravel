<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  Users,
  DollarSign,
  Package,
  Calendar,
  Download,
  RefreshCw
} from 'lucide-vue-next'

const loading = ref(true)
const analyticsData = ref({})
const dateRange = ref('30')

onMounted(async () => {
  await loadAnalytics()
})

const loadAnalytics = async () => {
  loading.value = true
  try {
    // Load multiple analytics endpoints using existing routes
    const [salesResponse, customerResponse, performanceResponse] = await Promise.all([
      fetch('/admin/dashboard/sales-analytics', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      }),
      fetch('/admin/dashboard/system-metrics', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      }),
      fetch('/admin/dashboard/overview', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
      })
    ])

    const [salesData, customerData, performanceData] = await Promise.all([
      salesResponse.json(),
      customerResponse.json(),
      performanceResponse.json()
    ])

    analyticsData.value = {
      sales: salesData,
      customers: customerData,
      performance: performanceData
    }
  } catch (error) {
    console.error('Failed to load analytics:', error)
    alert('Failed to load analytics data. Please try again.')
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

const formatPercentage = (value) => {
  return `${value > 0 ? '+' : ''}${value.toFixed(1)}%`
}

const getGrowthColor = (growth) => {
  if (growth > 0) return 'text-green-600'
  if (growth < 0) return 'text-red-600'
  return 'text-gray-500'
}

const getGrowthIcon = (growth) => {
  if (growth > 0) return TrendingUp
  if (growth < 0) return TrendingDown
  return BarChart3
}
</script>

<template>
  <AdminLayout>
    <Head title="Analytics Dashboard" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Analytics</h1>
          <p class="text-gray-600 mt-1">Comprehensive business insights and performance metrics</p>
        </div>
        <div class="flex items-center space-x-3">
          <select
            v-model="dateRange"
            @change="loadAnalytics"
            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="365">Last year</option>
          </select>
          <button @click="loadAnalytics" class="border border-gray-300 hover:border-gray-400 px-4 py-2 rounded-lg flex items-center">
            <RefreshCw class="h-4 w-4 mr-2" />
            Refresh
          </button>
          <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
            <Download class="h-4 w-4 mr-2" />
            Export Report
          </button>
        </div>
      </div>

      <!-- Key Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <DollarSign class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Revenue</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(analyticsData.sales?.total_revenue || 0) }}
              </p>
              <div class="flex items-center mt-1">
                <component
                  :is="getGrowthIcon(analyticsData.sales?.revenue_growth || 0)"
                  class="h-4 w-4 mr-1"
                  :class="getGrowthColor(analyticsData.sales?.revenue_growth || 0)"
                />
                <span
                  class="text-sm font-medium"
                  :class="getGrowthColor(analyticsData.sales?.revenue_growth || 0)"
                >
                  {{ formatPercentage(analyticsData.sales?.revenue_growth || 0) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <Package class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Orders</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ (analyticsData.sales?.total_orders || 0).toLocaleString() }}
              </p>
              <div class="flex items-center mt-1">
                <component
                  :is="getGrowthIcon(analyticsData.sales?.orders_growth || 0)"
                  class="h-4 w-4 mr-1"
                  :class="getGrowthColor(analyticsData.sales?.orders_growth || 0)"
                />
                <span
                  class="text-sm font-medium"
                  :class="getGrowthColor(analyticsData.sales?.orders_growth || 0)"
                >
                  {{ formatPercentage(analyticsData.sales?.orders_growth || 0) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <Users class="h-6 w-6 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">New Customers</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ (analyticsData.customers?.new_customers || 0).toLocaleString() }}
              </p>
              <div class="flex items-center mt-1">
                <component
                  :is="getGrowthIcon(analyticsData.customers?.customer_growth || 0)"
                  class="h-4 w-4 mr-1"
                  :class="getGrowthColor(analyticsData.customers?.customer_growth || 0)"
                />
                <span
                  class="text-sm font-medium"
                  :class="getGrowthColor(analyticsData.customers?.customer_growth || 0)"
                >
                  {{ formatPercentage(analyticsData.customers?.customer_growth || 0) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <BarChart3 class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Avg. Order Value</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(analyticsData.sales?.avg_order_value || 0) }}
              </p>
              <div class="flex items-center mt-1">
                <component
                  :is="getGrowthIcon(analyticsData.sales?.aov_growth || 0)"
                  class="h-4 w-4 mr-1"
                  :class="getGrowthColor(analyticsData.sales?.aov_growth || 0)"
                />
                <span
                  class="text-sm font-medium"
                  :class="getGrowthColor(analyticsData.sales?.aov_growth || 0)"
                >
                  {{ formatPercentage(analyticsData.sales?.aov_growth || 0) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Sales Performance</h3>
            <Calendar class="h-5 w-5 text-gray-400" />
          </div>
          <div class="h-80 flex items-center justify-center">
            <div class="text-center">
              <BarChart3 class="h-16 w-16 text-gray-300 mx-auto mb-4" />
              <p class="text-gray-500">Sales chart will display here</p>
              <p class="text-sm text-gray-400">Chart integration needed</p>
            </div>
          </div>
        </div>

        <!-- Customer Analytics -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Customer Insights</h3>
            <Users class="h-5 w-5 text-gray-400" />
          </div>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">Customer Retention Rate</p>
                <p class="text-sm text-gray-600">Returning customers</p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-green-600">
                  {{ (analyticsData.customers?.retention_rate || 0).toFixed(1) }}%
                </p>
                <p class="text-sm text-gray-500">+2.3% from last month</p>
              </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">Customer Lifetime Value</p>
                <p class="text-sm text-gray-600">Average CLV</p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-blue-600">
                  {{ formatCurrency(analyticsData.customers?.lifetime_value || 0) }}
                </p>
                <p class="text-sm text-gray-500">+5.7% from last month</p>
              </div>
            </div>

            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">Conversion Rate</p>
                <p class="text-sm text-gray-600">Visitors to customers</p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-bold text-purple-600">
                  {{ (analyticsData.customers?.conversion_rate || 0).toFixed(1) }}%
                </p>
                <p class="text-sm text-gray-500">-0.2% from last month</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Performance Metrics -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">System Performance</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Package class="h-8 w-8 text-green-600" />
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Active Systems</h4>
            <p class="text-3xl font-bold text-gray-900 mb-1">
              {{ (analyticsData.performance?.active_systems || 0).toLocaleString() }}
            </p>
            <p class="text-sm text-gray-600">Currently operational</p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <BarChart3 class="h-8 w-8 text-blue-600" />
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">System Efficiency</h4>
            <p class="text-3xl font-bold text-gray-900 mb-1">
              {{ (analyticsData.performance?.efficiency_rate || 0).toFixed(1) }}%
            </p>
            <p class="text-sm text-gray-600">Average performance</p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <TrendingUp class="h-8 w-8 text-orange-600" />
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Energy Generated</h4>
            <p class="text-3xl font-bold text-gray-900 mb-1">
              {{ (analyticsData.performance?.energy_generated || 0).toLocaleString() }}
            </p>
            <p class="text-sm text-gray-600">kWh this month</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>