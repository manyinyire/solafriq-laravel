<script setup>
import { ref, computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Package,
  Plus,
  Edit,
  Trash2,
  Eye,
  Search,
  X,
  Save,
  Filter,
  Zap,
  Battery,
  Box,
  Wrench
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  products: Array,
  filters: Object,
});

const loading = ref(false)
const searchQuery = ref(props.filters?.search || '')
const selectedCategory = ref(props.filters?.category || 'all')
const showModal = ref(false)
const showViewModal = ref(false)
const modalMode = ref('create')
const selectedProduct = ref(null)
const saving = ref(false)
const deleting = ref(false)
const errors = ref({})

const categories = [
  { value: 'all', label: 'All Categories', icon: Package },
  { value: 'panel', label: 'Solar Panels', icon: Zap },
  { value: 'inverter', label: 'Inverters', icon: Zap },
  { value: 'battery', label: 'Batteries', icon: Battery },
  { value: 'mounting', label: 'Mounting', icon: Box },
  { value: 'accessory', label: 'Accessories', icon: Wrench },
]

const formData = reactive({
  name: '',
  description: '',
  category: 'panel',
  brand: '',
  model: '',
  price: 0,
  image_url: '',
  specifications: {},
  stock_quantity: 0,
  unit: 'piece',
  power_rating: null,
  capacity: null,
  is_active: true,
  sort_order: 0,
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

const filteredProducts = computed(() => {
  let filtered = props.products

  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(p => p.category === selectedCategory.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(p =>
      p.name.toLowerCase().includes(query) ||
      p.brand?.toLowerCase().includes(query) ||
      p.model?.toLowerCase().includes(query) ||
      p.description?.toLowerCase().includes(query)
    )
  }

  return filtered
})

const getCategoryIcon = (category) => {
  const cat = categories.find(c => c.value === category)
  return cat?.icon || Package
}

const getCategoryLabel = (category) => {
  const cat = categories.find(c => c.value === category)
  return cat?.label || category
}

const getCategoryColor = (category) => {
  const colors = {
    panel: 'bg-blue-100 text-blue-800',
    inverter: 'bg-purple-100 text-purple-800',
    battery: 'bg-green-100 text-green-800',
    mounting: 'bg-yellow-100 text-yellow-800',
    accessory: 'bg-gray-100 text-gray-800',
  }
  return colors[category] || 'bg-gray-100 text-gray-800'
}

const filterByCategory = (category) => {
  selectedCategory.value = category
  router.get('/admin/products', {
    category: category,
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const performSearch = () => {
  router.get('/admin/products', {
    category: selectedCategory.value,
    search: searchQuery.value
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const openCreateModal = () => {
  modalMode.value = 'create'
  resetForm()
  showModal.value = true
}

const openEditModal = async (product) => {
  modalMode.value = 'edit'
  selectedProduct.value = product

  try {
    const response = await axios.get(`/admin/products/${product.id}`)
    const data = response.data

    Object.keys(formData).forEach(key => {
      formData[key] = data[key] ?? formData[key]
    })

    showModal.value = true
  } catch (error) {
    console.error('Error loading product:', error)
    alert('Failed to load product details')
  }
}

const openViewModal = async (product) => {
  try {
    const response = await axios.get(`/admin/products/${product.id}`)
    selectedProduct.value = response.data
    showViewModal.value = true
  } catch (error) {
    console.error('Error loading product:', error)
    alert('Failed to load product details')
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
  formData.category = 'panel'
  formData.brand = ''
  formData.model = ''
  formData.price = 0
  formData.image_url = ''
  formData.specifications = {}
  formData.stock_quantity = 0
  formData.unit = 'piece'
  formData.power_rating = null
  formData.capacity = null
  formData.is_active = true
  formData.sort_order = 0
  selectedProduct.value = null
}

const saveProduct = async () => {
  saving.value = true
  errors.value = {}

  try {
    if (modalMode.value === 'create') {
      await axios.post('/admin/products', formData)
    } else {
      await axios.put(`/admin/products/${selectedProduct.value.id}`, formData)
    }

    closeModal()
    router.reload({ only: ['products'] })
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Failed to save product')
    }
  } finally {
    saving.value = false
  }
}

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete "${product.name}"?`)) {
    return
  }

  deleting.value = true

  try {
    await axios.delete(`/admin/products/${product.id}`)
    router.reload({ only: ['products'] })
  } catch (error) {
    console.error('Error deleting product:', error)
    alert('Failed to delete product')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Products Management" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Products</h1>
          <p class="text-gray-600 mt-1">Manage solar products for systems and custom builder</p>
        </div>
        <button @click="openCreateModal" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
          <Plus class="h-4 w-4 mr-2" />
          Add Product
        </button>
      </div>

      <!-- Category Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap gap-2">
          <button
            v-for="cat in categories"
            :key="cat.value"
            @click="filterByCategory(cat.value)"
            :class="[
              'px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors',
              selectedCategory === cat.value
                ? 'bg-orange-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <component :is="cat.icon" class="h-4 w-4" />
            <span>{{ cat.label }}</span>
          </button>
        </div>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-4">
          <div class="flex-1 relative">
            <Search class="h-5 w-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" />
            <input
              v-model="searchQuery"
              @keyup.enter="performSearch"
              type="text"
              placeholder="Search products by name, brand, model..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <button @click="performSearch" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg">
            Search
          </button>
        </div>
      </div>

      <!-- Products Grid -->
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
          v-for="product in filteredProducts"
          :key="product.id"
          class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center flex-1">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                  <component :is="getCategoryIcon(product.category)" class="h-5 w-5 text-orange-600" />
                </div>
                <div class="ml-3 flex-1">
                  <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500">{{ product.brand }} {{ product.model }}</p>
                </div>
              </div>
              <span :class="['px-2 py-1 text-xs font-medium rounded-full', getCategoryColor(product.category)]">
                {{ getCategoryLabel(product.category) }}
              </span>
            </div>

            <p v-if="product.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
              {{ product.description }}
            </p>

            <div class="space-y-2 mb-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Price:</span>
                <span class="font-semibold text-gray-900">{{ formatCurrency(product.price) }}</span>
              </div>
              <div v-if="product.power_rating" class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Power:</span>
                <span class="text-sm text-gray-600">{{ product.power_rating }}W</span>
              </div>
              <div v-if="product.capacity" class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Capacity:</span>
                <span class="text-sm text-gray-600">{{ product.capacity }}kWh</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Stock:</span>
                <span :class="[
                  'text-sm font-medium',
                  product.stock_quantity > 0 ? 'text-green-600' : 'text-red-600'
                ]">
                  {{ product.stock_quantity }} {{ product.unit }}
                </span>
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <button @click="openViewModal(product)" class="flex-1 bg-orange-50 hover:bg-orange-100 text-orange-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Eye class="h-4 w-4 mr-1" />
                View
              </button>
              <button @click="openEditModal(product)" class="flex-1 border border-gray-300 hover:border-gray-400 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Edit class="h-4 w-4 mr-1" />
                Edit
              </button>
              <button @click="deleteProduct(product)" :disabled="deleting" class="border border-red-300 hover:border-red-400 text-red-600 px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredProducts.length === 0" class="text-center py-12">
        <Package class="h-12 w-12 text-gray-300 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
        <p class="text-gray-500 mb-6">Get started by adding your first product.</p>
        <button @click="openCreateModal" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
          Add Product
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ modalMode === 'create' ? 'Add New Product' : 'Edit Product' }}
          </h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <X class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="saveProduct" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
              <input v-model="formData.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
              <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
              <select v-model="formData.category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="panel">Solar Panel</option>
                <option value="inverter">Inverter</option>
                <option value="battery">Battery</option>
                <option value="mounting">Mounting</option>
                <option value="accessory">Accessory</option>
              </select>
              <p v-if="errors.category" class="text-red-500 text-sm mt-1">{{ errors.category[0] }}</p>
            </div>

            <!-- Brand -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
              <input v-model="formData.brand" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Model -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
              <input v-model="formData.model" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
              <input v-model.number="formData.price" type="number" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
              <p v-if="errors.price" class="text-red-500 text-sm mt-1">{{ errors.price[0] }}</p>
            </div>

            <!-- Stock Quantity -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
              <input v-model.number="formData.stock_quantity" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Unit -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
              <input v-model="formData.unit" type="text" placeholder="piece, meter, set" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Power Rating -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Power Rating (W)</label>
              <input v-model.number="formData.power_rating" type="number" step="0.01" placeholder="For panels/inverters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Capacity -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Capacity (kWh)</label>
              <input v-model.number="formData.capacity" type="number" step="0.01" placeholder="For batteries" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

            <!-- Sort Order -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
              <input v-model.number="formData.sort_order" type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea v-model="formData.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
          </div>

          <!-- Image URL -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
            <input v-model="formData.image_url" type="text" placeholder="https://example.com/image.jpg" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>

          <!-- Active Checkbox -->
          <div class="flex items-center">
            <input v-model="formData.is_active" type="checkbox" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded" />
            <label class="text-sm font-medium text-gray-700">Active</label>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-4 pt-4 border-t">
            <button type="button" @click="closeModal" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center">
              <Save class="h-4 w-4 mr-2" />
              {{ saving ? 'Saving...' : 'Save Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal && selectedProduct" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-gray-900">{{ selectedProduct.name }}</h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <X class="h-6 w-6" />
          </button>
        </div>

        <div class="p-6 space-y-6">
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Category</h3>
              <p class="mt-1">
                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getCategoryColor(selectedProduct.category)]">
                  {{ getCategoryLabel(selectedProduct.category) }}
                </span>
              </p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Price</h3>
              <p class="mt-1 text-lg font-semibold">{{ formatCurrency(selectedProduct.price) }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Brand</h3>
              <p class="mt-1 text-lg">{{ selectedProduct.brand || 'N/A' }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Model</h3>
              <p class="mt-1 text-lg">{{ selectedProduct.model || 'N/A' }}</p>
            </div>
            <div v-if="selectedProduct.power_rating">
              <h3 class="text-sm font-medium text-gray-500">Power Rating</h3>
              <p class="mt-1 text-lg">{{ selectedProduct.power_rating }}W</p>
            </div>
            <div v-if="selectedProduct.capacity">
              <h3 class="text-sm font-medium text-gray-500">Capacity</h3>
              <p class="mt-1 text-lg">{{ selectedProduct.capacity }}kWh</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Stock</h3>
              <p class="mt-1 text-lg">{{ selectedProduct.stock_quantity }} {{ selectedProduct.unit }}</p>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500">Status</h3>
              <p class="mt-1">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  selectedProduct.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ selectedProduct.is_active ? 'Active' : 'Inactive' }}
                </span>
              </p>
            </div>
          </div>

          <div v-if="selectedProduct.description">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
            <p class="text-gray-700">{{ selectedProduct.description }}</p>
          </div>

          <div class="flex justify-end space-x-4 pt-4 border-t">
            <button @click="closeModal" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
              Close
            </button>
            <button @click="openEditModal(selectedProduct); showViewModal = false" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 flex items-center">
              <Edit class="h-4 w-4 mr-2" />
              Edit Product
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
