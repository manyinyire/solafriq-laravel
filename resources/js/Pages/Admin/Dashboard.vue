<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Users,
  Zap,
  Wrench,
  DollarSign,
  Shield,
  TrendingUp,
  TrendingDown,
  Activity,
  Calendar,
  Eye,
  Plus,
  BarChart3,
  PieChart,
  User,
  Package,
  AlertTriangle,
  CheckCircle
} from 'lucide-vue-next'
// Chart.js imports removed - install vue-chartjs and chart.js first
// import { Line, Doughnut } from 'vue-chartjs'
// import {
//   Chart as ChartJS,
//   Title,
//   Tooltip,
//   Legend,
//   LineElement,
//   LinearScale,
//   CategoryScale,
//   PointElement,
//   ArcElement
// } from 'chart.js'

// ChartJS.register(Title, Tooltip, Legend, LineElement, LinearScale, CategoryScale, PointElement, ArcElement)

const page = usePage()
const loading = ref(true)
const dashboardData = ref({})

// Chart options
const lineChartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: true,
      text: 'Monthly Revenue Trend'
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          return '$' + value.toLocaleString()
        }
      }
    }
  }
}

const doughnutChartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'right',
    },
    title: {
      display: true,
      text: 'System Status Distribution'
    }
  }
}

onMounted(async () => {
  await loadDashboardData()
})

const loadDashboardData = async () => {
  loading.value = true
  try {
    // Load dashboard overview data
    const overviewResponse = await fetch('/admin/dashboard/overview', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    // Load sales analytics
    const analyticsResponse = await fetch('/admin/dashboard/sales-analytics', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    if (overviewResponse.ok && analyticsResponse.ok) {
      const overviewData = await overviewResponse.json()
      const analyticsData = await analyticsResponse.json()

      dashboardData.value = {
        stats: overviewData, // This contains total_users, total_revenue, etc.
        analytics: analyticsData // This contains monthly_sales, top_products, etc.
      }
    }
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
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
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getActivityIcon = (type) => {
  switch (type) {
    case 'user_registered':
      return User
    case 'system_created':
      return Package
    case 'order_created':
      return DollarSign
    default:
      return Activity
  }
}

const getGrowthColor = (growth) => {
  if (growth > 0) return 'text-green-600'
  if (growth < 0) return 'text-red-600'
  return 'text-gray-500'
}

const getGrowthIcon = (growth) => {
  if (growth > 0) return TrendingUp
  if (growth < 0) return TrendingDown
  return Activity
}

// Computed chart data
const revenueChartData = computed(() => {
  if (!dashboardData.value.monthly_revenue) return null

  return {
    labels: dashboardData.value.monthly_revenue.labels,
    datasets: [
      {
        label: 'Revenue',
        backgroundColor: 'rgba(249, 115, 22, 0.1)',
        borderColor: 'rgb(249, 115, 22)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        data: dashboardData.value.monthly_revenue.data
      }
    ]
  }
})

const systemStatusChartData = computed(() => {
  if (!dashboardData.value.system_status) return null

  return {
    labels: dashboardData.value.system_status.labels,
    datasets: [
      {
        backgroundColor: [
          'rgb(34, 197, 94)',   // Active - Green
          'rgb(249, 115, 22)',  // Pending - Orange
          'rgb(239, 68, 68)',   // Maintenance - Red
          'rgb(107, 114, 128)'  // Inactive - Gray
        ],
        data: dashboardData.value.system_status.data
      }
    ]
  }
})
</script>

<template>
  <AdminLayout>
    <Head title="Admin Dashboard" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <p class="text-orange-100 mt-1">Welcome back! Here's what's happening with your solar business.</p>
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
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Users class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Users</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.total_users?.toLocaleString() || 0 }}
              </p>
              <div class="flex items-center mt-1">
                <component
                  :is="getGrowthIcon(dashboardData.stats?.growth?.users || 0)"
                  class="h-4 w-4 mr-1"
                  :class="getGrowthColor(dashboardData.stats?.growth?.users || 0)"
                />
                <span
                  class="text-sm font-medium"
                  :class="getGrowthColor(dashboardData.stats?.growth?.users || 0)"
                >
                  {{ Math.abs(dashboardData.stats?.growth?.users || 0) }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <Package class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Orders</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.total_orders?.toLocaleString() || 0 }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                {{ dashboardData.stats?.pending_orders || 0 }} pending
              </p>
            </div>
          </div>
        </div>

        <!-- Scheduled Installations -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
              <Wrench class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Scheduled</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.scheduled_orders?.toLocaleString() || 0 }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                {{ dashboardData.stats?.installed_orders || 0 }} installed
              </p>
            </div>
          </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <DollarSign class="h-6 w-6 text-purple-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Total Revenue</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(dashboardData.stats?.total_revenue || 0) }}
              </p>
              <p class="text-sm text-gray-500 mt-1">
                {{ formatCurrency(dashboardData.stats?.monthly_revenue || 0) }} this month
              </p>
            </div>
          </div>
        </div>

        <!-- Active Systems -->
        <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
              <Shield class="h-6 w-6 text-indigo-600" />
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-600">Active Systems</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.active_systems?.toLocaleString() || 0 }}
              </p>
              <p class="text-sm text-gray-500 mt-1">Operational</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue Breakdown -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Daily Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Today</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(dashboardData.stats?.daily_revenue || 0) }}
              </p>
            </div>
            <DollarSign class="h-8 w-8 text-blue-500" />
          </div>
        </div>

        <!-- Weekly Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">This Week</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(dashboardData.stats?.weekly_revenue || 0) }}
              </p>
            </div>
            <TrendingUp class="h-8 w-8 text-green-500" />
          </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Pending</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(dashboardData.stats?.pending_payments || 0) }}
              </p>
            </div>
            <AlertTriangle class="h-8 w-8 text-orange-500" />
          </div>
        </div>

        <!-- Conversion Rate -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Conversion</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ dashboardData.stats?.conversion_rate || 0 }}%
              </p>
            </div>
            <CheckCircle class="h-8 w-8 text-purple-500" />
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <BarChart3 class="h-5 w-5 text-gray-400 mr-3" />
              <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
            </div>
            <button class="text-orange-600 hover:text-orange-700 text-sm font-medium">
              View Details
            </button>
          </div>
          <div class="h-80 flex items-center justify-center">
            <div class="text-center">
              <BarChart3 class="h-16 w-16 text-gray-300 mx-auto mb-4" />
              <p class="text-gray-500">Chart will display after installing vue-chartjs</p>
              <p class="text-sm text-gray-400">Run: npm install vue-chartjs chart.js</p>
            </div>
          </div>
        </div>

        <!-- System Status Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <PieChart class="h-5 w-5 text-gray-400 mr-3" />
              <h3 class="text-lg font-semibold text-gray-900">System Status</h3>
            </div>
            <button class="text-orange-600 hover:text-orange-700 text-sm font-medium">
              View All
            </button>
          </div>
          <div class="h-80 flex items-center justify-center">
            <div class="text-center">
              <PieChart class="h-16 w-16 text-gray-300 mx-auto mb-4" />
              <p class="text-gray-500">Chart will display after installing vue-chartjs</p>
              <p class="text-sm text-gray-400">Run: npm install vue-chartjs chart.js</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <Activity class="h-5 w-5 text-gray-400 mr-3" />
              <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
            </div>
            <button class="text-orange-600 hover:text-orange-700 text-sm font-medium">
              View All
            </button>
          </div>
        </div>
        <div class="p-6">
          <div v-if="dashboardData.recent_activity?.length" class="space-y-4">
            <div
              v-for="(activity, index) in dashboardData.recent_activity"
              :key="index"
              class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                <component :is="getActivityIcon(activity.type)" class="h-4 w-4 text-orange-600" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                <p class="text-sm text-gray-600">{{ activity.description }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(activity.created_at) }}</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <Activity class="h-12 w-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">No recent activity</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <a
            href="/admin/users"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200">
              <Users class="h-5 w-5 text-blue-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">Manage Users</p>
              <p class="text-sm text-gray-600">View and edit users</p>
            </div>
          </a>

          <a
            href="/admin/systems"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200">
              <Zap class="h-5 w-5 text-green-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">Solar Systems</p>
              <p class="text-sm text-gray-600">Manage systems</p>
            </div>
          </a>

          <a
            href="/admin/analytics"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200">
              <BarChart3 class="h-5 w-5 text-purple-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">View Analytics</p>
              <p class="text-sm text-gray-600">Detailed reports</p>
            </div>
          </a>

          <a
            href="/admin/settings"
            class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors group"
          >
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200">
              <Wrench class="h-5 w-5 text-orange-600" />
            </div>
            <div class="ml-3">
              <p class="font-medium text-gray-900">System Settings</p>
              <p class="text-sm text-gray-600">Configure system</p>
            </div>
          </a>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>