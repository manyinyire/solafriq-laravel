<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Notification from '@/Components/Notification.vue'
import {
  ArrowLeft,
  RefreshCw,
  Truck,
  DollarSign as RefundIcon,
  Mail,
  Plus,
  Edit3,
  Package,
  CheckCircle,
  XCircle,
  Calendar,
  User,
  Download
} from 'lucide-vue-next'

const props = defineProps({
  orderId: {
    type: [String, Number],
    required: true
  }
})

const loading = ref(true)
const order = ref(null)
const paymentMethod = ref('')
const transactionReference = ref('')

// Modal states
const showStatusModal = ref(false)
const showTrackingModal = ref(false)
const showRefundModal = ref(false)
const showNotificationModal = ref(false)
const showNoteModal = ref(false)

// Form data
const newStatus = ref('')
const trackingNumber = ref('')
const refundAmount = ref(0)
const refundReason = ref('')
const notificationType = ref('')
const newNote = ref('')

// Notification state
const showNotification = ref(false)
const notificationData = ref({
  type: 'success',
  title: '',
  message: ''
})

const loadOrder = async () => {
  loading.value = true
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/data`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    console.log('Response status:', response.status, response.statusText)

    if (!response.ok) {
      const errorText = await response.text()
      console.error('API Error Response:', errorText)
      throw new Error(`HTTP error! status: ${response.status} - ${errorText.substring(0, 200)}`)
    }

    const data = await response.json()
    console.log('Order data received:', data) // Debug log

    if (data.success && data.data) {
      order.value = data.data
      console.log('Order loaded successfully:', order.value)
    } else {
      console.error('No order data found in response:', data)
    }
  } catch (error) {
    console.error('Failed to load order:', error)
  } finally {
    loading.value = false
  }
}

const confirmPayment = async () => {
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/confirm-payment`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        payment_method: paymentMethod.value,
        transaction_reference: transactionReference.value
      })
    })

    const data = await response.json()
    if (response.ok) {
      loadOrder()
    } else {
      console.error('Failed to confirm payment:', data)
    }
  } catch (error) {
    console.error('Failed to confirm payment:', error)
  }
}

const acceptOrder = async () => {
  try {
    console.log('Attempting to accept order:', props.orderId)
    const response = await fetch(`/admin/orders/${props.orderId}/accept`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    console.log('Accept response status:', response.status, response.statusText)

    if (!response.ok) {
      const errorText = await response.text()
      console.error('Accept order error response:', errorText)
      throw new Error(`HTTP error! status: ${response.status} - ${errorText.substring(0, 200)}`)
    }

    const data = await response.json()
    console.log('Accept order response:', data)

    if (data.success) {
      console.log('Order accepted successfully')
      showNotificationMessage('success', 'Order Accepted', 'Order has been accepted and is now being processed.')
      await loadOrder()
    } else {
      console.error('Failed to accept order:', data)
      showNotificationMessage('error', 'Accept Failed', data.message || 'Unknown error occurred')
    }
  } catch (error) {
    console.error('Failed to accept order:', error)
    alert('Failed to accept order: ' + error.message)
  }
}

const declineOrder = async () => {
  try {
    console.log('Attempting to decline order:', props.orderId)
    const response = await fetch(`/admin/orders/${props.orderId}/decline`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    console.log('Decline response status:', response.status, response.statusText)

    if (!response.ok) {
      const errorText = await response.text()
      console.error('Decline order error response:', errorText)
      throw new Error(`HTTP error! status: ${response.status} - ${errorText.substring(0, 200)}`)
    }

    const data = await response.json()
    console.log('Decline order response:', data)

    if (data.success) {
      console.log('Order declined successfully')
      showNotificationMessage('warning', 'Order Declined', 'Order has been declined and cancelled.')
      await loadOrder()
    } else {
      console.error('Failed to decline order:', data)
      showNotificationMessage('error', 'Decline Failed', data.message || 'Unknown error occurred')
    }
  } catch (error) {
    console.error('Failed to decline order:', error)
    alert('Failed to decline order: ' + error.message)
  }
}

// New action functions
const openStatusModal = () => {
  newStatus.value = order.value.status
  showStatusModal.value = true
}

const updateOrderStatus = async () => {
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/status`, {
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
    if (response.ok) {
      showStatusModal.value = false
      loadOrder()
    }
  } catch (error) {
    console.error('Failed to update order status:', error)
  }
}

const openTrackingModal = () => {
  trackingNumber.value = order.value.tracking_number || ''
  showTrackingModal.value = true
}

const updateTracking = async () => {
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/tracking`, {
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
    if (response.ok) {
      showTrackingModal.value = false
      loadOrder()
    }
  } catch (error) {
    console.error('Failed to update tracking number:', error)
  }
}

const openRefundModal = () => {
  refundAmount.value = order.value.total_amount
  refundReason.value = ''
  showRefundModal.value = true
}

const processRefund = async () => {
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/refund`, {
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
    if (response.ok) {
      showRefundModal.value = false
      loadOrder()
    }
  } catch (error) {
    console.error('Failed to process refund:', error)
  }
}

const openNotificationModal = () => {
  notificationType.value = ''
  showNotificationModal.value = true
}

const resendNotification = async () => {
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/resend-notification`, {
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
    if (response.ok) {
      showNotificationModal.value = false
    }
  } catch (error) {
    console.error('Failed to resend notification:', error)
  }
}

const openNoteModal = () => {
  newNote.value = ''
  showNoteModal.value = true
}

const addNote = async () => {
  try {
    const response = await fetch(`/admin/orders/${props.orderId}/notes`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        note: newNote.value
      })
    })

    const data = await response.json()
    if (response.ok) {
      showNoteModal.value = false
      loadOrder()
    }
  } catch (error) {
    console.error('Failed to add note:', error)
  }
}

const downloadInvoice = () => {
  try {
    // Create a direct link to download the PDF
    const downloadUrl = `/admin/orders/${props.orderId}/invoice-pdf`

    // Create a temporary link element and click it
    const link = document.createElement('a')
    link.href = downloadUrl
    link.download = `Invoice-${order.value.id}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    showNotification.value = true
    notificationData.value = {
      type: 'success',
      title: 'Invoice Downloaded',
      message: 'Invoice PDF has been downloaded successfully.'
    }
  } catch (error) {
    console.error('Failed to download invoice:', error)
    showNotification.value = true
    notificationData.value = {
      type: 'error',
      title: 'Download Failed',
      message: 'Failed to download invoice PDF.'
    }
  }
}

// Helper functions
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const parseNotes = (notesData) => {
  if (!notesData) return []

  try {
    // Try to parse as JSON first
    return JSON.parse(notesData)
  } catch (e) {
    // If JSON parsing fails, check if it starts with JSON
    const lines = notesData.split('\n')
    let jsonArray = []

    // Try to extract JSON array from the beginning
    if (notesData.trim().startsWith('[')) {
      try {
        const jsonEnd = notesData.indexOf(']') + 1
        const jsonPart = notesData.substring(0, jsonEnd)
        jsonArray = JSON.parse(jsonPart)

        // Get remaining text after JSON
        const remainingText = notesData.substring(jsonEnd).trim()
        if (remainingText) {
          // Add remaining text as plain note entries
          const textLines = remainingText.split('\n').filter(line => line.trim())
          textLines.forEach(line => {
            jsonArray.push({
              note: line.trim(),
              added_by: 'System',
              added_at: new Date().toISOString()
            })
          })
        }
      } catch (e2) {
        // If still fails, treat entire thing as plain text
        jsonArray = [{
          note: notesData,
          added_by: 'System',
          added_at: new Date().toISOString()
        }]
      }
    } else {
      // No JSON, treat as plain text
      jsonArray = [{
        note: notesData,
        added_by: 'System',
        added_at: new Date().toISOString()
      }]
    }

    return jsonArray
  }
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

// Notification helper
const showNotificationMessage = (type, title, message) => {
  notificationData.value = { type, title, message }
  showNotification.value = true
}

onMounted(() => {
  loadOrder()
})
</script>

<template>
  <AdminLayout>
    <Head :title="`Order #${orderId}`" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Link href="/admin/orders" class="flex items-center text-gray-600 hover:text-gray-900">
          <ArrowLeft class="h-5 w-5 mr-2" />
          Back to Orders
        </Link>
      </div>

      <div v-if="loading" class="bg-white rounded-lg shadow p-6">
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-1/4 mb-4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>
        <p class="text-sm text-gray-500 mt-4">Loading order {{ orderId }}...</p>
      </div>

      <!-- Debug Info -->
      <div v-if="!loading && !order" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-red-800 font-medium mb-2">Debug Information</h3>
        <p class="text-red-700 text-sm mb-2">Order ID: {{ orderId }}</p>
        <p class="text-red-700 text-sm mb-2">Loading: {{ loading }}</p>
        <p class="text-red-700 text-sm mb-2">Order Data: {{ order }}</p>
        <p class="text-red-700 text-sm">Check browser console for more details.</p>
      </div>

      <div v-else-if="order" class="space-y-4">
        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Order #{{ order.id }}</h1>
              <p class="text-sm text-gray-600">{{ order.customer_name }} - {{ formatDate(order.created_at) }}</p>
            </div>
            <div class="flex items-center space-x-2">
              <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(order.status)]">
                {{ order.status }}
              </span>
              <span :class="['px-2 py-1 text-xs font-medium rounded-full', getPaymentStatusColor(order.payment_status)]">
                {{ getDisplayPaymentStatus(order.payment_status) }}
              </span>
              <button
                v-if="order.invoice"
                @click="downloadInvoice"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs flex items-center gap-1"
                title="Download Invoice PDF"
              >
                <Download :size="14" />
                Invoice
              </button>
            </div>
          </div>

          <!-- Quick Info -->
          <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
              <span class="text-gray-500">Total:</span>
              <span class="font-medium ml-1">{{ formatCurrency(order.total_amount) }}</span>
            </div>
            <div>
              <span class="text-gray-500">Items:</span>
              <span class="font-medium ml-1">{{ order.items?.length || 0 }}</span>
            </div>
            <div>
              <span class="text-gray-500">Phone:</span>
              <span class="font-medium ml-1">{{ order.customer_phone || 'N/A' }}</span>
            </div>
            <div>
              <span class="text-gray-500">Tracking:</span>
              <span class="font-medium ml-1">{{ order.tracking_number || 'Not assigned' }}</span>
            </div>
          </div>
        </div>

        <!-- Order Items (Simplified) -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Items</h2>
          <div class="space-y-2">
            <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
              <div class="flex-1">
                <div class="font-medium text-gray-900">{{ item.name }}</div>
                <div class="text-sm text-gray-500">Qty: {{ item.quantity }} × {{ formatCurrency(item.price) }}</div>
              </div>
              <div class="font-medium text-gray-900">{{ formatCurrency(item.price * item.quantity) }}</div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Actions</h2>

          <!-- Step 1: Order Decision (PENDING orders only) -->
          <div v-if="order.status === 'PENDING'" class="p-3 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-sm text-yellow-800 mb-2">📝 Order requires your decision:</p>
            <div class="flex space-x-3">
              <button @click="acceptOrder" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                Accept Order
              </button>
              <button @click="declineOrder" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                Decline Order
              </button>
            </div>
          </div>

          <!-- Step 2: Payment Confirmation (ACCEPTED orders with UNPAID status) -->
          <div v-else-if="order.status === 'ACCEPTED' && order.payment_status === 'PENDING'" class="p-3 bg-blue-50 border border-blue-200 rounded">
            <p class="text-sm text-blue-800 mb-2">💳 Waiting for payment confirmation:</p>
            <div class="flex items-center space-x-3 flex-wrap">
              <select v-model="paymentMethod" class="border rounded px-2 py-1 text-sm">
                <option value="">Payment Method</option>
                <option value="CASH">Cash</option>
                <option value="BANK_TRANSFER">Bank Transfer</option>
                <option value="CREDIT_CARD">Credit Card</option>
              </select>
              <input
                v-if="paymentMethod === 'BANK_TRANSFER'"
                v-model="transactionReference"
                type="text"
                placeholder="Reference"
                class="border rounded px-2 py-1 text-sm"
              >
              <button @click="confirmPayment" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                Confirm Payment
              </button>
            </div>
          </div>

          <!-- Step 3: Order Management (PAID orders only) -->
          <div v-else-if="order.payment_status === 'PAID'" class="space-y-3">
            <div class="p-3 bg-green-50 border border-green-200 rounded">
              <p class="text-sm text-green-800 mb-2">✅ Payment received - Order management:</p>
              <div class="flex flex-wrap gap-2">
                <button @click="openStatusModal" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                  Update Status
                </button>
                <button @click="openTrackingModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm">
                  Add Tracking
                </button>
                <button @click="openNotificationModal" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                  Send Email
                </button>
                <button @click="downloadInvoice" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm flex items-center gap-1">
                  <Download :size="16" />
                  Invoice PDF
                </button>
              </div>
            </div>

            <!-- Advanced Actions -->
            <div class="flex flex-wrap gap-2">
              <button @click="openRefundModal" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                Process Refund
              </button>
              <button @click="openNoteModal" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                Add Note
              </button>
            </div>
          </div>

          <!-- Cancelled/Declined Orders -->
          <div v-else-if="order.status === 'CANCELLED'" class="p-3 bg-red-50 border border-red-200 rounded">
            <p class="text-sm text-red-800">❌ Order has been cancelled</p>
          </div>

          <!-- Other statuses with limited actions -->
          <div v-else class="p-3 bg-gray-50 border border-gray-200 rounded">
            <p class="text-sm text-gray-600 mb-2">Order Status: {{ order.status }}</p>
            <div class="flex flex-wrap gap-2">
              <button @click="openNoteModal" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm">
                Add Note
              </button>
              <button @click="openNotificationModal" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm">
                Send Email
              </button>
            </div>
          </div>
        </div>

        <!-- Notes (if any) -->
        <div v-if="order.notes && parseNotes(order.notes).length > 0" class="bg-white rounded-lg shadow p-4">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
          <div class="space-y-2">
            <div v-for="(note, index) in parseNotes(order.notes)" :key="index" class="bg-gray-50 rounded p-2 border-l-2 border-orange-500">
              <p class="text-sm text-gray-800">{{ note.note }}</p>
              <div class="text-xs text-gray-500 mt-1">{{ note.added_by }} - {{ formatDate(note.added_at) }}</div>
            </div>
          </div>
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
            <option value="ACCEPTED">Accepted</option>
            <option value="SCHEDULED">Scheduled</option>
            <option value="INSTALLED">Installed</option>
            <option value="RETURNED">Returned</option>
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

    <!-- Add Note Modal -->
    <div v-if="showNoteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Add Order Note</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
          <textarea v-model="newNote" class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="4" placeholder="Enter your note here..."></textarea>
        </div>
        <div class="flex space-x-3">
          <button @click="addNote" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
            Add Note
          </button>
          <button @click="showNoteModal = false" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
            Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- Notification -->
    <Notification
      :show="showNotification"
      :type="notificationData.type"
      :title="notificationData.title"
      :message="notificationData.message"
      @close="showNotification = false"
    />
  </AdminLayout>
</template>
