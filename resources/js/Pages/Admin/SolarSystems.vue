<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Zap,
  Plus,
  Edit,
  Trash2,
  Eye,
  DollarSign,
  Settings,
  Search
} from 'lucide-vue-next'

const loading = ref(true)
const systems = ref([])
const searchQuery = ref('')

onMounted(async () => {
  await loadSystems()
})

const loadSystems = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/v1/solar-systems', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      systems.value = data.data
    }
  } catch (error) {
    console.error('Failed to load solar systems:', error)
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

const filteredSystems = computed(() => {
  if (!searchQuery.value) return systems.value
  return systems.value.filter(system =>
    system.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    system.description.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})
</script>

<template>
  <AdminLayout>
    <Head title="Solar Systems Management" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Solar Systems</h1>
          <p class="text-gray-600 mt-1">Manage your solar system products and packages</p>
        </div>
        <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
          <Plus class="h-4 w-4 mr-2" />
          Add System
        </button>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-4">
          <div class="flex-1 relative">
            <Search class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search systems..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <button class="border border-gray-300 hover:border-gray-400 px-4 py-2 rounded-lg flex items-center">
            <Settings class="h-4 w-4 mr-2" />
            Filters
          </button>
        </div>
      </div>

      <!-- Systems Grid -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="bg-white rounded-lg shadow p-6">
          <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
            <div class="h-20 bg-gray-200 rounded mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          </div>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="system in filteredSystems"
          :key="system.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                  <Zap class="h-5 w-5 text-orange-600" />
                </div>
                <div class="ml-3">
                  <h3 class="font-semibold text-gray-900">{{ system.name }}</h3>
                  <p class="text-sm text-gray-500">{{ system.capacity }}kW</p>
                </div>
              </div>
              <span
                :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  system.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800'
                ]"
              >
                {{ system.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
              {{ system.short_description }}
            </p>

            <div class="space-y-2 mb-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Price:</span>
                <span class="font-semibold text-gray-900">{{ formatCurrency(system.price) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Installment:</span>
                <span class="text-sm text-gray-600">{{ formatCurrency(system.installment_price) }}/mo</span>
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <button class="flex-1 bg-orange-50 hover:bg-orange-100 text-orange-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Eye class="h-4 w-4 mr-1" />
                View
              </button>
              <button class="flex-1 border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Edit class="h-4 w-4 mr-1" />
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredSystems.length === 0" class="text-center py-12">
        <Zap class="h-12 w-12 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No solar systems found</h3>
        <p class="text-gray-500 mb-6">Get started by adding your first solar system product.</p>
        <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
          Add Solar System
        </button>
      </div>
    </div>
  </AdminLayout>
</template>