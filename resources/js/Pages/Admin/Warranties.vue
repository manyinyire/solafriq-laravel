<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Shield,
  Search,
  Filter,
  Eye,
  Edit,
  Calendar,
  User,
  Package,
  AlertTriangle,
  CheckCircle,
  Clock,
  FileText
} from 'lucide-vue-next'

const loading = ref(true)
const warranties = ref([])
const searchQuery = ref('')
const statusFilter = ref('all')

onMounted(async () => {
  await loadWarranties()
})

const loadWarranties = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/v1/admin/warranties/all', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      warranties.value = data.data
    }
  } catch (error) {
    console.error('Failed to load warranties:', error)
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusColor = (status) => {
  const colors = {
    'ACTIVE': 'bg-green-100 text-green-800',
    'EXPIRED': 'bg-red-100 text-red-800',
    'CLAIMED': 'bg-yellow-100 text-yellow-800',
    'VOID': 'bg-gray-100 text-gray-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getStatusIcon = (status) => {
  const icons = {
    'ACTIVE': CheckCircle,
    'EXPIRED': AlertTriangle,
    'CLAIMED': Clock,
    'VOID': Shield
  }
  return icons[status] || Shield
}

const getTimeRemaining = (endDate) => {
  const now = new Date()
  const end = new Date(endDate)
  const diffTime = end - now
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays < 0) return 'Expired'
  if (diffDays === 0) return 'Expires today'
  if (diffDays === 1) return '1 day remaining'
  if (diffDays < 30) return `${diffDays} days remaining`
  if (diffDays < 365) return `${Math.ceil(diffDays / 30)} months remaining`
  return `${Math.ceil(diffDays / 365)} years remaining`
}

const filteredWarranties = computed(() => {
  let filtered = warranties.value

  if (searchQuery.value) {
    filtered = filtered.filter(warranty =>
      warranty.warranty_number?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      warranty.customer?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      warranty.system?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(warranty => warranty.status === statusFilter.value)
  }

  return filtered
})
</script>

<template>
  <AdminLayout>
    <Head title="Warranties Management" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Warranties</h1>
          <p class="text-gray-600 mt-1">Manage system warranties and claims</p>
        </div>
        <div class="flex items-center space-x-3">
          <button class="border border-gray-300 hover:border-gray-400 px-4 py-2 rounded-lg flex items-center">
            <FileText class="h-4 w-4 mr-2" />
            Generate Report
          </button>
          <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
            <Shield class="h-4 w-4 mr-2" />
            New Warranty
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <CheckCircle class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Active Warranties</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ warranties.filter(w => w.status === 'ACTIVE').length }}
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
              <p class="text-sm font-medium text-gray-600">Expiring Soon</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ warranties.filter(w => {
                  const daysLeft = Math.ceil((new Date(w.end_date) - new Date()) / (1000 * 60 * 60 * 24))
                  return daysLeft <= 30 && daysLeft > 0
                }).length }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
              <AlertTriangle class="h-6 w-6 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Expired</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ warranties.filter(w => w.status === 'EXPIRED').length }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Shield class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Claims</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ warranties.filter(w => w.status === 'CLAIMED').length }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-4">
          <div class="flex-1 relative">
            <Search class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search warranties by number, customer, or system..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <select
            v-model="statusFilter"
            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="all">All Status</option>
            <option value="ACTIVE">Active</option>
            <option value="EXPIRED">Expired</option>
            <option value="CLAIMED">Claimed</option>
            <option value="VOID">Void</option>
          </select>
        </div>
      </div>

      <!-- Warranties Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Warranty
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Customer
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  System
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Start Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  End Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Time Remaining
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" v-for="i in 5" :key="i">
                <td v-for="j in 8" :key="j" class="px-6 py-4">
                  <div class="animate-pulse h-4 bg-gray-200 rounded w-3/4"></div>
                </td>
              </tr>
              <tr v-else v-for="warranty in filteredWarranties" :key="warranty.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                      <Shield class="h-5 w-5 text-blue-600" />
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">{{ warranty.warranty_number }}</div>
                      <div class="text-sm text-gray-500">{{ warranty.type || 'Standard' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <User class="h-4 w-4 text-gray-400 mr-2" />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ warranty.customer?.name || 'N/A' }}</div>
                      <div class="text-sm text-gray-500">{{ warranty.customer?.email || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <Package class="h-4 w-4 text-gray-400 mr-2" />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ warranty.system?.name || 'N/A' }}</div>
                      <div class="text-sm text-gray-500">{{ warranty.system?.capacity || 'N/A' }}kW</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <component :is="getStatusIcon(warranty.status)" class="h-4 w-4 mr-2 text-gray-400" />
                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(warranty.status)]">
                      {{ warranty.status }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <Calendar class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-900">{{ formatDate(warranty.start_date) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <Calendar class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-900">{{ formatDate(warranty.end_date) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600">{{ getTimeRemaining(warranty.end_date) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button class="text-orange-600 hover:text-orange-900">
                      <Eye class="h-4 w-4" />
                    </button>
                    <button class="text-blue-600 hover:text-blue-900">
                      <Edit class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredWarranties.length === 0" class="text-center py-12">
          <Shield class="h-12 w-12 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No warranties found</h3>
          <p class="text-gray-500 mb-6">No warranties match your current search criteria.</p>
          <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
            Create New Warranty
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>