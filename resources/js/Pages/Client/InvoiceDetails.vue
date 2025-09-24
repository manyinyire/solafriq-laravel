<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import {
  ArrowLeft,
  Download,
  CreditCard,
  Calendar,
  DollarSign,
  CheckCircle,
  Clock,
  AlertCircle,
  XCircle,
  FileText,
  Package
} from 'lucide-vue-next'

const props = defineProps({
  invoiceId: String
})

const loading = ref(true)
const invoice = ref({})
const error = ref(null)

onMounted(async () => {
  await loadInvoice()
})

const loadInvoice = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await fetch(`/api/v1/invoices/${props.invoiceId}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      invoice.value = data.data
    } else {
      error.value = 'Failed to load invoice'
    }
  } catch (err) {
    console.error('Failed to load invoice:', err)
    error.value = 'Failed to load invoice'
  } finally {
    loading.value = false
  }
}

const downloadInvoice = async () => {
  try {
    const response = await fetch(`/api/v1/invoices/${props.invoiceId}/download`, {
      headers: {
        'Accept': 'application/pdf',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `invoice-${invoice.value.invoice_number}.pdf`
      a.click()
      window.URL.revokeObjectURL(url)
    }
  } catch (error) {
    console.error('Failed to download invoice:', error)
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
    month: 'long',
    day: 'numeric'
  })
}

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'overdue':
      return 'bg-red-100 text-red-800'
    case 'cancelled':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusIcon = (status) => {
  switch (status?.toLowerCase()) {
    case 'paid':
      return CheckCircle
    case 'pending':
      return Clock
    case 'overdue':
      return AlertCircle
    case 'cancelled':
      return XCircle
    default:
      return FileText
  }
}
</script>

<template>
  <ClientLayout>
    <Head :title="`Invoice #${invoiceId}`" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <Link href="/invoices" class="text-gray-600 hover:text-gray-900">
              <ArrowLeft class="h-5 w-5" />
            </Link>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Invoice #{{ invoiceId }}</h1>
              <p class="text-gray-600 mt-1">Invoice details and payment information</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button
              @click="downloadInvoice"
              class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100"
            >
              <Download class="h-4 w-4 mr-2" />
              Download PDF
            </button>
            <button
              v-if="invoice.status !== 'PAID'"
              class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg"
            >
              <CreditCard class="h-4 w-4 mr-2" />
              Pay Now
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-6">
        <div class="animate-pulse">
          <div class="space-y-4">
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-white rounded-lg shadow p-6">
        <div class="text-center py-8">
          <AlertCircle class="h-16 w-16 text-red-300 mx-auto mb-4" />
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading Invoice</h3>
          <p class="text-gray-600 mb-6">{{ error }}</p>
          <button
            @click="loadInvoice"
            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium"
          >
            Try Again
          </button>
        </div>
      </div>

      <!-- Invoice Details -->
      <div v-else class="space-y-6">
        <!-- Invoice Header -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Information</h3>
              <div class="space-y-3">
                <div class="flex items-center">
                  <FileText class="h-5 w-5 text-gray-400 mr-3" />
                  <div>
                    <p class="text-sm text-gray-600">Invoice Number</p>
                    <p class="font-medium">{{ invoice.invoice_number }}</p>
                  </div>
                </div>
                <div class="flex items-center">
                  <Calendar class="h-5 w-5 text-gray-400 mr-3" />
                  <div>
                    <p class="text-sm text-gray-600">Due Date</p>
                    <p class="font-medium">{{ formatDate(invoice.due_date) }}</p>
                  </div>
                </div>
                <div class="flex items-center">
                  <component :is="getStatusIcon(invoice.status)" class="h-5 w-5 text-gray-400 mr-3" />
                  <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span :class="getStatusColor(invoice.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                      {{ invoice.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Information</h3>
              <div class="space-y-3" v-if="invoice.order">
                <div class="flex items-center">
                  <Package class="h-5 w-5 text-gray-400 mr-3" />
                  <div>
                    <p class="text-sm text-gray-600">Order Number</p>
                    <p class="font-medium">#{{ invoice.order.id }}</p>
                  </div>
                </div>
                <div class="flex items-center">
                  <div class="w-5 h-5 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-xs text-gray-600">@</span>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Customer</p>
                    <p class="font-medium">{{ invoice.order.customer_name }}</p>
                    <p class="text-sm text-gray-500">{{ invoice.order.customer_email }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Amount Summary -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Amount Details</h3>
          <div class="space-y-4">
            <div class="flex justify-between">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-medium">{{ formatCurrency(invoice.subtotal || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tax</span>
              <span class="font-medium">{{ formatCurrency(invoice.tax || 0) }}</span>
            </div>
            <div class="border-t pt-4">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                <span class="text-2xl font-bold text-gray-900">{{ formatCurrency(invoice.total_amount) }}</span>
              </div>
            </div>
            <div v-if="invoice.paid_amount > 0" class="border-t pt-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Amount Paid</span>
                <span class="font-medium text-green-600">{{ formatCurrency(invoice.paid_amount) }}</span>
              </div>
              <div class="flex justify-between mt-2">
                <span class="text-gray-600">Remaining Balance</span>
                <span class="font-medium text-orange-600">{{ formatCurrency(invoice.total_amount - invoice.paid_amount) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Progress -->
        <div v-if="invoice.status !== 'PAID' && invoice.paid_amount > 0" class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Progress</h3>
          <div class="space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Progress</span>
              <span>{{ Math.round((invoice.paid_amount / invoice.total_amount) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div
                class="bg-green-500 h-3 rounded-full transition-all duration-300"
                :style="{ width: `${(invoice.paid_amount / invoice.total_amount) * 100}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>