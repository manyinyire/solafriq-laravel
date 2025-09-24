<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  ShoppingCart,
  Search,
  Filter,
  Eye,
  Edit,
  Download,
  Calendar,
  User,
  DollarSign,
  Package,
  CheckCircle,
  XCircle,
  MoreHorizontal,
  Truck,
  RefreshCw,
  DollarSign as RefundIcon,
  Mail
} from 'lucide-vue-next'

const loading = ref(true)
const orders = ref([])
const searchQuery = ref('')
const statusFilter = ref('all')

// Modal states
const showStatusModal = ref(false)
const showTrackingModal = ref(false)
const showRefundModal = ref(false)
const showNotificationModal = ref(false)
const selectedOrder = ref(null)

// Form data
const newStatus = ref('')
const trackingNumber = ref('')
const refundAmount = ref(0)
const refundReason = ref('')
const notificationType = ref('')
const dropdownOpen = ref(null)

onMounted(async () => {
  await loadOrders()
})

const loadOrders = async () => {
  loading.value = true
  try {
    const response = await fetch('/admin/orders-data', {
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
  const colors = {
    'PENDING': 'bg-yellow-100 text-yellow-800',
    'ACCEPTED': 'bg-blue-100 text-blue-800',
    'SCHEDULED': 'bg-indigo-100 text-indigo-800',
    'INSTALLED': 'bg-green-100 text-green-800',
    'RETURNED': 'bg-red-100 text-red-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusColor = (status) => {
  const colors = {
    'PENDING': 'bg-orange-100 text-orange-800',
    'PAID': 'bg-green-100 text-green-800',
    'FAILED': 'bg-red-100 text-red-800',
    'REFUNDED': 'bg-gray-100 text-gray-800',
    'OVERDUE': 'bg-red-100 text-red-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getDisplayPaymentStatus = (status) => {
  return status === 'PENDING' ? 'UNPAID' : status
}

const filteredOrders = computed(() => {
  let filtered = orders.value

  if (searchQuery.value) {
    filtered = filtered.filter(order =>
      order.id.toString().includes(searchQuery.value) ||
      order.customer_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      order.customer_email?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(order => order.status === statusFilter.value)
  }

  return filtered
})

const acceptOrder = async (orderId) => {
  try {
    const response = await fetch(`/admin/orders/${orderId}/accept`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (response.ok) {
      loadOrders()
    } else {
      console.error('Failed to accept order:', data)
    }
  } catch (error) {
    console.error('Failed to accept order:', error)
  }
}

const declineOrder = async (orderId) => {
  try {
    const response = await fetch(`/admin/orders/${orderId}/decline`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (response.ok) {
      loadOrders()
    } else {
      console.error('Failed to decline order:', data)
    }
  } catch (error) {
    console.error('Failed to decline order:', error)
  }
}

const openStatusModal = (order) => {
  selectedOrder.value = order
  newStatus.value = order.status
  showStatusModal.value = true
}

const updateOrderStatus = async () => {
  try {
    const response = await fetch(`/admin/orders/${selectedOrder.value.id}/status`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        status: newStatus.value
      })
    })

    const data = await response.json()
    if (data.success) {
      showStatusModal.value = false
      loadOrders()
    }
  } catch (error) {
    console.error('Failed to update order status:', error)
  }
}

const openTrackingModal = (order) => {
  selectedOrder.value = order
  trackingNumber.value = order.tracking_number || ''
  showTrackingModal.value = true
}

const updateTracking = async () => {
  try {
    const response = await fetch(`/admin/orders/${selectedOrder.value.id}/tracking`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        tracking_number: trackingNumber.value
      })
    })

    const data = await response.json()
    if (data.success) {
      showTrackingModal.value = false
      loadOrders()
    }
  } catch (error) {
    console.error('Failed to update tracking number:', error)
  }
}

const openRefundModal = (order) => {
  selectedOrder.value = order
  refundAmount.value = order.total_amount
  refundReason.value = ''
  showRefundModal.value = true
}

const processRefund = async () => {
  try {
    const response = await fetch(`/admin/orders/${selectedOrder.value.id}/refund`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        refund_amount: refundAmount.value,
        refund_reason: refundReason.value
      })
    })

    const data = await response.json()
    if (data.success) {
      showRefundModal.value = false
      loadOrders()
    }
  } catch (error) {
    console.error('Failed to process refund:', error)
  }
}

const openNotificationModal = (order) => {
  selectedOrder.value = order
  notificationType.value = ''
  showNotificationModal.value = true
}

const resendNotification = async () => {
  try {
    const response = await fetch(`/admin/orders/${selectedOrder.value.id}/resend-notification`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        notification_type: notificationType.value
      })
    })

    const data = await response.json()
    if (data.success) {
      showNotificationModal.value = false
    }
  } catch (error) {
    console.error('Failed to resend notification:', error)
  }
}

const toggleDropdown = (orderId) => {
  dropdownOpen.value = dropdownOpen.value === orderId ? null : orderId
}
</script>

<template>
  <AdminLayout>
    <Head title="Orders Management" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Orders</h1>
          <p class="text-gray-600 mt-1">Manage customer orders and track deliveries</p>
        </div>
        <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center">
          <Download class="h-4 w-4 mr-2" />
          Export Orders
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
              placeholder="Search orders by ID, customer name, or email..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <select
            v-model="statusFilter"
            class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            <option value="all">All Status</option>
            <option value="PENDING">Pending</option>
            <option value="CONFIRMED">Confirmed</option>
            <option value="PROCESSING">Processing</option>
            <option value="SHIPPED">Shipped</option>
            <option value="DELIVERED">Delivered</option>
            <option value="CANCELLED">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Order
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Customer
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Payment
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" v-for="i in 5" :key="i">
                <td v-for="j in 7" :key="j" class="px-6 py-4">
                  <div class="animate-pulse h-4 bg-gray-200 rounded w-3/4"></div>
                </td>
              </tr>
              <tr v-else v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 cursor-pointer" @click="$inertia.visit(`/admin/orders/${order.id}`)">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                      <ShoppingCart class="h-5 w-5 text-orange-600" />
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900">#{{ order.id }}</div>
                      <div class="text-sm text-gray-500">{{ order.tracking_number || 'No tracking' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <User class="h-4 w-4 text-gray-400 mr-2" />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ order.customer_name }}</div>
                      <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <DollarSign class="h-4 w-4 text-gray-400 mr-1" />
                    <span class="text-sm font-medium text-gray-900">{{ formatCurrency(order.total_amount) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(order.status)]">
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getPaymentStatusColor(order.payment_status)]">
                    {{ getDisplayPaymentStatus(order.payment_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <Calendar class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-900">{{ formatDate(order.created_at) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" @click.stop>
                  <div class="flex items-center space-x-2">
                    <!-- Primary View Button -->
                    <Link :href="`/admin/orders/${order.id}`" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded-md text-xs font-medium flex items-center">
                      <Eye class="h-3 w-3 mr-1" />
                      View
                    </Link>

                    <!-- Quick Actions for Pending Orders -->
                    <button v-if="order.status === 'PENDING'" @click.stop="acceptOrder(order.id)" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1.5 rounded-md text-xs" title="Accept Order">
                      <CheckCircle class="h-3 w-3" />
                    </button>
                    <button v-if="order.status === 'PENDING'" @click.stop="declineOrder(order.id)" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded-md text-xs" title="Decline Order">
                      <XCircle class="h-3 w-3" />
                    </button>

                    <!-- More Actions Dropdown -->
                    <div class="relative">
                      <button @click.stop="toggleDropdown(order.id)" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-2 py-1.5 rounded-md text-xs">
                        <MoreHorizontal class="h-3 w-3" />
                      </button>
                      <div v-if="dropdownOpen === order.id" class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-50 border">
                        <div class="py-1">
                          <button @click="openStatusModal(order)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            <RefreshCw class="h-4 w-4 mr-2" />
                            Update Status
                          </button>
                          <button @click="openTrackingModal(order)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            <Truck class="h-4 w-4 mr-2" />
                            Update Tracking
                          </button>
                          <button v-if="order.payment_status === 'PAID'" @click="openRefundModal(order)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            <RefundIcon class="h-4 w-4 mr-2" />
                            Process Refund
                          </button>
                          <button @click="openNotificationModal(order)" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                            <Mail class="h-4 w-4 mr-2" />
                            Resend Email
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredOrders.length === 0" class="text-center py-12">
          <ShoppingCart class="h-12 w-12 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
          <p class="text-gray-500">No orders match your current search criteria.</p>
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Update Order Status</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
          <select v-model="newStatus" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <option value="PENDING">Pending</option>
            <option value="CONFIRMED">Confirmed</option>
            <option value="PROCESSING">Processing</option>
            <option value="SHIPPED">Shipped</option>
            <option value="DELIVERED">Delivered</option>
            <option value="CANCELLED">Cancelled</option>
            <option value="REFUNDED">Refunded</option>
          </select>
        </div>
        <div class="flex space-x-3">
          <button @click="updateOrderStatus" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
            Update Status
          </button>
          <button @click="showStatusModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Tracking Update Modal -->
    <div v-if="showTrackingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Update Tracking Number</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Tracking Number</label>
          <input v-model="trackingNumber" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Enter tracking number">
        </div>
        <div class="flex space-x-3">
          <button @click="updateTracking" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg">
            Update Tracking
          </button>
          <button @click="showTrackingModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Refund Modal -->
    <div v-if="showRefundModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Process Refund</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount</label>
          <input v-model="refundAmount" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="0.00">
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Refund Reason</label>
          <textarea v-model="refundReason" class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="3" placeholder="Enter refund reason"></textarea>
        </div>
        <div class="flex space-x-3">
          <button @click="processRefund" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
            Process Refund
          </button>
          <button @click="showRefundModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Notification Modal -->
    <div v-if="showNotificationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Resend Notification</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
          <select v-model="notificationType" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <option value="">Select notification type</option>
            <option value="order_confirmation">Order Confirmation</option>
            <option value="payment_confirmation">Payment Confirmation</option>
            <option value="shipping_notification">Shipping Notification</option>
            <option value="delivery_confirmation">Delivery Confirmation</option>
          </select>
        </div>
        <div class="flex space-x-3">
          <button @click="resendNotification" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Send Notification
          </button>
          <button @click="showNotificationModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>