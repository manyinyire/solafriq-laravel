<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Users,
  Search,
  Filter,
  Plus,
  Edit,
  Trash2,
  Eye,
  UserCheck,
  UserX,
  Calendar,
  Mail,
  Phone,
  MapPin,
  Shield,
  AlertCircle,
  Check,
  Loader2,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

const users = ref([])
const loading = ref(false)
const selectedUsers = ref([])
const showFilters = ref(false)
const message = ref('')

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const totalUsers = ref(0)

// Filters
const filters = ref({
  search: '',
  role: '',
  status: '',
  date_from: '',
  date_to: ''
})

// User details modal
const selectedUser = ref(null)
const showUserModal = ref(false)

onMounted(async () => {
  await loadUsers()
})

const loadUsers = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      ...Object.fromEntries(Object.entries(filters.value).filter(([_, v]) => v))
    })

    const response = await fetch(`/api/v1/admin/users?${params}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    
    if (!response.ok) {
      console.error('API Error:', data)
      message.value = data.message || 'Failed to load users'
      return
    }
    
    users.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
    totalUsers.value = data.total || 0
  } catch (error) {
    console.error('Failed to load users:', error)
    message.value = 'Failed to load users'
  } finally {
    loading.value = false
  }
}

const searchUsers = async () => {
  currentPage.value = 1
  await loadUsers(1)
}

const clearFilters = () => {
  filters.value = {
    search: '',
    role: '',
    status: '',
    date_from: '',
    date_to: ''
  }
  searchUsers()
}

const viewUser = async (userId) => {
  try {
    const response = await fetch(`/api/v1/admin/users/${userId}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })

    const user = await response.json()
    selectedUser.value = user
    showUserModal.value = true
  } catch (error) {
    console.error('Failed to load user details:', error)
  }
}

const toggleUserSelection = (userId) => {
  const index = selectedUsers.value.indexOf(userId)
  if (index > -1) {
    selectedUsers.value.splice(index, 1)
  } else {
    selectedUsers.value.push(userId)
  }
}

const selectAllUsers = () => {
  if (selectedUsers.value.length === users.value.length) {
    selectedUsers.value = []
  } else {
    selectedUsers.value = users.value.map(user => user.id)
  }
}

const bulkAction = async (action) => {
  if (selectedUsers.value.length === 0) return

  if (!confirm(`Are you sure you want to ${action} ${selectedUsers.value.length} user(s)?`)) {
    return
  }

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch('/api/v1/admin/users/bulk', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        user_ids: selectedUsers.value,
        action: action
      })
    })

    const data = await response.json()
    if (response.ok) {
      message.value = data.message
      selectedUsers.value = []
      await loadUsers(currentPage.value)
    } else {
      message.value = data.message || 'Action failed'
    }
  } catch (error) {
    console.error('Bulk action failed:', error)
    message.value = 'An error occurred'
  }
}

const updateUserStatus = async (user, status) => {
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/v1/admin/users/${user.id}`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin',
      body: JSON.stringify({ status })
    })

    if (response.ok) {
      message.value = `User ${status.toLowerCase()} successfully`
      await loadUsers(currentPage.value)
    }
  } catch (error) {
    console.error('Failed to update user status:', error)
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
    return
  }

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch(`/api/v1/admin/users/${user.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (response.ok) {
      message.value = data.message
      await loadUsers(currentPage.value)
    } else {
      message.value = data.message || 'Failed to delete user'
    }
  } catch (error) {
    console.error('Failed to delete user:', error)
    message.value = 'An error occurred'
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const getRoleColor = (role) => {
  return role === 'ADMIN' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'
}

const getStatusColor = (status) => {
  switch (status) {
    case 'ACTIVE': return 'bg-green-100 text-green-800'
    case 'SUSPENDED': return 'bg-yellow-100 text-yellow-800'
    case 'BANNED': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Users Management" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
              <Users class="w-6 h-6 mr-3 text-orange-500" />
              Users Management
            </h1>
            <p class="text-gray-600 mt-1">Manage system users and their permissions</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="showFilters = !showFilters"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              <Filter class="w-4 h-4 mr-2" />
              Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Alert Messages -->
      <div v-if="message" class="rounded-md p-4" :class="message.includes('success') || message.includes('successfully') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
        <div class="flex">
          <component :is="message.includes('success') || message.includes('successfully') ? Check : AlertCircle" class="w-5 h-5 mr-3 mt-0.5" />
          <div class="text-sm">{{ message }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div v-if="showFilters" class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Name, email, phone..."
                class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500"
                @keyup.enter="searchUsers"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select v-model="filters.role" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
              <option value="">All Roles</option>
              <option value="ADMIN">Admin</option>
              <option value="CLIENT">Client</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-orange-500 focus:border-orange-500">
              <option value="">All Status</option>
              <option value="ACTIVE">Active</option>
              <option value="SUSPENDED">Suspended</option>
              <option value="BANNED">Banned</option>
            </select>
          </div>

          <div class="flex items-end space-x-2">
            <button
              @click="searchUsers"
              class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 focus:ring-2 focus:ring-orange-500"
            >
              Search
            </button>
            <button
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedUsers.length > 0" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <span class="text-sm text-orange-800">{{ selectedUsers.length }} user(s) selected</span>
          <div class="flex space-x-2">
            <button
              @click="bulkAction('activate')"
              class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600"
            >
              <UserCheck class="w-4 h-4 inline mr-1" />
              Activate
            </button>
            <button
              @click="bulkAction('suspend')"
              class="px-3 py-1 bg-yellow-500 text-white text-sm rounded hover:bg-yellow-600"
            >
              <UserX class="w-4 h-4 inline mr-1" />
              Suspend
            </button>
            <button
              @click="bulkAction('delete')"
              class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
            >
              <Trash2 class="w-4 h-4 inline mr-1" />
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
              Users ({{ totalUsers }})
            </h2>
            <div v-if="loading" class="flex items-center text-gray-500">
              <Loader2 class="w-4 h-4 mr-2 animate-spin" />
              Loading...
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="selectedUsers.length === users.length && users.length > 0"
                    @change="selectAllUsers"
                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <input
                    type="checkbox"
                    :checked="selectedUsers.includes(user.id)"
                    @change="toggleUserSelection(user.id)"
                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                  />
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">{{ user.name.charAt(0).toUpperCase() }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500 flex items-center">
                        <Mail class="w-3 h-3 mr-1" />
                        {{ user.email }}
                      </div>
                      <div v-if="user.phone" class="text-sm text-gray-500 flex items-center">
                        <Phone class="w-3 h-3 mr-1" />
                        {{ user.phone }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getRoleColor(user.role)">
                    <Shield class="w-3 h-3 mr-1" />
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusColor(user.status || 'ACTIVE')">
                    {{ user.status || 'ACTIVE' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <div>{{ user.orders_count || 0 }} orders</div>
                  <div>{{ user.installment_plans_count || 0 }} plans</div>
                  <div>{{ user.warranties_count || 0 }} warranties</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <div class="flex items-center">
                    <Calendar class="w-3 h-3 mr-1" />
                    {{ formatDate(user.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="viewUser(user.id)"
                      class="text-orange-600 hover:text-orange-900"
                      title="View Details"
                    >
                      <Eye class="w-4 h-4" />
                    </button>
                    <button
                      v-if="user.status !== 'ACTIVE'"
                      @click="updateUserStatus(user, 'ACTIVE')"
                      class="text-green-600 hover:text-green-900"
                      title="Activate"
                    >
                      <UserCheck class="w-4 h-4" />
                    </button>
                    <button
                      v-if="user.status === 'ACTIVE'"
                      @click="updateUserStatus(user, 'SUSPENDED')"
                      class="text-yellow-600 hover:text-yellow-900"
                      title="Suspend"
                    >
                      <UserX class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteUser(user)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="loadUsers(currentPage - 1)"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="loadUsers(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing page {{ currentPage }} of {{ totalPages }} ({{ totalUsers }} total users)
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="loadUsers(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <ChevronLeft class="h-5 w-5" />
                </button>
                <button
                  @click="loadUsers(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <ChevronRight class="h-5 w-5" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Details Modal -->
    <div v-if="showUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="showUserModal = false">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">User Details</h3>
            <button @click="showUserModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div v-if="selectedUser" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedUser.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedUser.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <p class="mt-1 text-sm text-gray-900">{{ selectedUser.phone || 'N/A' }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <p class="mt-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getRoleColor(selectedUser.role)">
                    {{ selectedUser.role }}
                  </span>
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <p class="mt-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusColor(selectedUser.status || 'ACTIVE')">
                    {{ selectedUser.status || 'ACTIVE' }}
                  </span>
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Joined</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedUser.created_at) }}</p>
              </div>
            </div>
            
            <div v-if="selectedUser.address">
              <label class="block text-sm font-medium text-gray-700">Address</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedUser.address }}</p>
            </div>

            <div class="grid grid-cols-3 gap-4 pt-4 border-t">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ selectedUser.orders_count || 0 }}</div>
                <div class="text-sm text-gray-500">Orders</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ selectedUser.installment_plans_count || 0 }}</div>
                <div class="text-sm text-gray-500">Installment Plans</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ selectedUser.warranties_count || 0 }}</div>
                <div class="text-sm text-gray-500">Warranties</div>
              </div>
            </div>

            <div v-if="selectedUser.orders && selectedUser.orders.length > 0" class="pt-4 border-t">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Orders</h4>
              <div class="space-y-2">
                <div v-for="order in selectedUser.orders" :key="order.id" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                  <span class="text-sm text-gray-900">Order #{{ order.id }}</span>
                  <span class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
