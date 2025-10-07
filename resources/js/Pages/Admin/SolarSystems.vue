<script setup>
import { ref, computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Zap,
  Plus,
  Edit,
  Trash2,
  Eye,
  DollarSign,
  Settings,
  Search,
  X,
  Save,
  AlertCircle
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  systems: Array,
});

const loading = ref(false)
const searchQuery = ref('')
const showModal = ref(false)
const showViewModal = ref(false)
const modalMode = ref('create') // 'create' or 'edit'
const selectedSystem = ref(null)
const saving = ref(false)
const deleting = ref(false)
const errors = ref({})

const formData = reactive({
  name: '',
  description: '',
  short_description: '',
  capacity: '',
  price: 0,
  original_price: null,
  installment_price: null,
  installment_months: null,
  image_url: '',
  gallery_images: [],
  use_case: '',
  gradient_colors: '',
  is_popular: false,
  is_featured: false,
  is_active: true,
  sort_order: 0,
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

const filteredSystems = computed(() => {
  if (!searchQuery.value) return props.systems
  return props.systems.filter(system =>
    system.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    system.description.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const openCreateModal = () => {
  modalMode.value = 'create'
  resetForm()
  showModal.value = true
}

const openEditModal = async (system) => {
  modalMode.value = 'edit'
  selectedSystem.value = system
  
  // Load full system data
  try {
    const response = await axios.get(`/admin/systems/${system.id}`)
    const data = response.data
    
    Object.keys(formData).forEach(key => {
      formData[key] = data[key] ?? formData[key]
    })
    
    showModal.value = true
  } catch (error) {
    console.error('Error loading system:', error)
    alert('Failed to load system details')
  }
}

const openViewModal = async (system) => {
  try {
    const response = await axios.get(`/admin/systems/${system.id}`)
    selectedSystem.value = response.data
    showViewModal.value = true
  } catch (error) {
    console.error('Error loading system:', error)
    alert('Failed to load system details')
  }
}

const closeModal = () => {
  showModal.value = false
  showViewModal.value = false
  resetForm()
  errors.value = {}
}

const resetForm = () => {
  formData.name = ''
  formData.description = ''
  formData.short_description = ''
  formData.capacity = ''
  formData.price = 0
  formData.original_price = null
  formData.installment_price = null
  formData.installment_months = null
  formData.image_url = ''
  formData.gallery_images = []
  formData.use_case = ''
  formData.gradient_colors = ''
  formData.is_popular = false
  formData.is_featured = false
  formData.is_active = true
  formData.sort_order = 0
  selectedSystem.value = null
}

const saveSystem = async () => {
  saving.value = true
  errors.value = {}
  
  try {
    if (modalMode.value === 'create') {
      await axios.post('/admin/systems', formData)
    } else {
      await axios.put(`/admin/systems/${selectedSystem.value.id}`, formData)
    }
    
    closeModal()
    router.reload({ only: ['systems'] })
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Failed to save system')
    }
  } finally {
    saving.value = false
  }
}

const deleteSystem = async (system) => {
  if (!confirm(`Are you sure you want to delete "${system.name}"?`)) {
    return
  }
  
  deleting.value = true
  
  try {
    await axios.delete(`/admin/systems/${system.id}`)
    router.reload({ only: ['systems'] })
  } catch (error) {
    console.error('Error deleting system:', error)
    alert('Failed to delete system')
  } finally {
    deleting.value = false
  }
}
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
        <button @click="openCreateModal" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
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
              <button @click="openViewModal(system)" class="flex-1 bg-orange-50 hover:bg-orange-100 text-orange-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Eye class="h-4 w-4 mr-1" />
                View
              </button>
              <button @click="openEditModal(system)" class="flex-1 border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Edit class="h-4 w-4 mr-1" />
                Edit
              </button>
              <button @click="deleteSystem(system)" :disabled="deleting" class="border border-red-300 hover:border-red-400 text-red-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Trash2 class="h-4 w-4" />
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
        <button @click="openCreateModal" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
          Add Solar System
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ modalMode === 'create' ? 'Add New Solar System' : 'Edit Solar System' }}
          </h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <X class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="saveSystem" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
              <input v-model="formData.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
              <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- Capacity -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Capacity *</label>
              <input v-model="formData.capacity" type="text" required placeholder="e.g., 5kW" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
              <p v-if="errors.capacity" class="text-red-500 text-sm mt-1">{{ errors.capacity[0] }}</p>
            </div>

            <!-- Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
              <input v-model.number="formData.price" type="number" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
              <p v-if="errors.price" class="text-red-500 text-sm mt-1">{{ errors.price[0] }}</p>
            </div>

            <!-- Original Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Original Price</label>
              <input v-model.number="formData.original_price" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Installment Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Installment Price</label>
              <input v-model.number="formData.installment_price" type="number" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Installment Months -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Installment Months</label>
              <input v-model.number="formData.installment_months" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Use Case -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Use Case</label>
              <input v-model="formData.use_case" type="text" placeholder="e.g., Residential, Commercial" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Sort Order -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
              <input v-model.number="formData.sort_order" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>
          </div>

          <!-- Short Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Short Description *</label>
            <textarea v-model="formData.short_description" required rows="2" maxlength="500" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
            <p v-if="errors.short_description" class="text-red-500 text-sm mt-1">{{ errors.short_description[0] }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Description *</label>
            <textarea v-model="formData.description" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
            <p v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description[0] }}</p>
          </div>

          <!-- Image URL -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
            <input v-model="formData.image_url" type="text" placeholder="https://example.com/image.jpg" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>

          <!-- Checkboxes -->
          <div class="flex flex-wrap gap-6">
            <label class="flex items-center">
              <input v-model="formData.is_active" type="checkbox" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" />
              <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
            <label class="flex items-center">
              <input v-model="formData.is_popular" type="checkbox" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" />
              <span class="text-sm font-medium text-gray-700">Popular</span>
            </label>
            <label class="flex items-center">
              <input v-model="formData.is_featured" type="checkbox" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" />
              <span class="text-sm font-medium text-gray-700">Featured</span>
            </label>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-4 pt-4 border-t">
            <button type="button" @click="closeModal" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center">
              <Save class="h-4 w-4 mr-2" />
              {{ saving ? 'Saving...' : 'Save System' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal && selectedSystem" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-gray-900">{{ selectedSystem.name }}</h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <X class="h-6 w-6" />
          </button>
        </div>

        <div class="p-6 space-y-6">
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Capacity</h3>
              <p class="mt-1 text-lg font-semibold">{{ selectedSystem.capacity }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Price</h3>
              <p class="mt-1 text-lg font-semibold">{{ formatCurrency(selectedSystem.price) }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Installment</h3>
              <p class="mt-1 text-lg font-semibold">{{ formatCurrency(selectedSystem.installment_price) }}/mo</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Status</h3>
              <p class="mt-1">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  selectedSystem.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ selectedSystem.is_active ? 'Active' : 'Inactive' }}
                </span>
              </p>
            </div>
          </div>

          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
            <p class="text-gray-700">{{ selectedSystem.description }}</p>
          </div>

          <div v-if="selectedSystem.features?.length" class="border-t pt-4">
            <h3 class="text-lg font-semibold mb-3">Features</h3>
            <ul class="space-y-2">
              <li v-for="feature in selectedSystem.features" :key="feature.id" class="flex items-start">
                <Zap class="h-5 w-5 text-orange-600 mr-2 mt-0.5" />
                <span>{{ feature.name }}</span>
              </li>
            </ul>
          </div>

          <div class="flex justify-end space-x-4 pt-4 border-t">
            <button @click="closeModal" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Close
            </button>
            <button @click="openEditModal(selectedSystem); showViewModal = false" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center">
              <Edit class="h-4 w-4 mr-2" />
              Edit System
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>