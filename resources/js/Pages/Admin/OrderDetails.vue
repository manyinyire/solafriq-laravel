<script setup>
import { ref, onMounted, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Home, ChevronRight, User, Gift, Truck, DollarSign, Package, Info } from 'lucide-vue-next';
import { orderService } from '@/Services';

const props = defineProps({
  orderId: {
    type: [String, Number],
    required: true,
  },
});

const order = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchOrder = async () => {
  try {
    const response = await orderService.getOrder(props.orderId);
    order.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to load order details.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchOrder);

const subtotal = computed(() => {
  if (!order.value) return 0;
  return order.value.items.reduce((acc, item) => acc + item.price * item.quantity, 0);
});

const tax = computed(() => subtotal.value * 0.05); // Assuming 5% tax
const shipping = computed(() => 0); // Assuming free shipping for now
const total = computed(() => {
    if (!order.value) return 0;
    return parseFloat(order.value.total_amount);
});


const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const isUpdating = ref(false);
const installationDate = ref('');
const showPaymentModal = ref(false);
const paymentMethod = ref('CASH');
const transactionReference = ref('');

const updateStatus = async (status) => {
  isUpdating.value = true;
  try {
    if (status === 'PROCESSING') {
      await orderService.acceptOrder(props.orderId);
    } else if (status === 'CANCELLED') {
      await orderService.declineOrder(props.orderId);
    } else {
      await orderService.updateStatus(props.orderId, status);
    }
    await fetchOrder(); // Refresh order details
  } catch (err) {
    console.error('Failed to update order status:', err);
    alert('Failed to update order status. Please try again.');
  } finally {
    isUpdating.value = false;
  }
};

const openPaymentModal = () => {
  showPaymentModal.value = true;
};

const closePaymentModal = () => {
  showPaymentModal.value = false;
  paymentMethod.value = 'CASH';
  transactionReference.value = '';
};

const confirmPayment = async () => {
  // Validate transaction reference for bank transfer
  if (paymentMethod.value === 'BANK_TRANSFER' && !transactionReference.value.trim()) {
    alert('Please enter a transaction reference for bank transfer.');
    return;
  }

  isUpdating.value = true;
  try {
    const payload = {
      payment_method: paymentMethod.value,
    };
    
    if (paymentMethod.value === 'BANK_TRANSFER') {
      payload.transaction_reference = transactionReference.value;
    }
    
    await orderService.confirmPayment(props.orderId, payload);
    closePaymentModal();
    await fetchOrder();
  } catch (err) {
    console.error('Failed to confirm payment:', err);
    alert('Failed to confirm payment. Please try again.');
  } finally {
    isUpdating.value = false;
  }
};

const scheduleInstallation = async () => {
  isUpdating.value = true;
  try {
    await orderService.scheduleInstallation(props.orderId, installationDate.value);
    await fetchOrder();
  } catch (err) {
    console.error('Failed to schedule installation:', err);
    alert('Failed to schedule installation. Please try again.');
  } finally {
    isUpdating.value = false;
  }
};

const downloadInvoice = async () => {
  try {
    const response = await orderService.downloadInvoice(props.orderId);
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `invoice-order-${props.orderId}.pdf`);
    document.body.appendChild(link);
    link.click();
    // Cleanup
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error('Failed to download invoice:', err);
    alert('Failed to download invoice. Please try again.');
  }
};
</script>

<template>
  <Head :title="'Order Details - ' + orderId" />
  <AdminLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Loading and Error States -->
      <div v-if="loading" class="text-center py-16">
        <p>Loading order details...</p>
      </div>
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">{{ error }}</span>
      </div>

      <div v-if="order">
        <!-- Breadcrumbs -->
        <div class="flex items-center text-sm text-gray-600 mb-6">
          <Home class="w-4 h-4 mr-2" />
          <Link href="/admin/dashboard" class="hover:underline">Admin Dashboard</Link>
          <ChevronRight class="w-4 h-4 mx-2" />
          <Link href="/admin/orders" class="hover:underline">Orders</Link>
          <ChevronRight class="w-4 h-4 mx-2" />
          <span class="font-medium text-gray-800">Order #{{ order.id }}</span>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Order #{{ order.id }}</h1>
              <p class="text-gray-600 mt-2">
                Placed on {{ formatDate(order.created_at) }}
              </p>
            </div>
            <div class="mt-4 md:mt-0">
              <span :class="['px-3 py-1.5 text-sm font-semibold rounded-full',
                order.status === 'COMPLETED' ? 'bg-green-100 text-green-800' :
                order.status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' :
                'bg-red-100 text-red-800'
              ]">
                {{ order.status }}
              </span>
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-md">
              <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                  <Package class="w-6 h-6 mr-3 text-orange-500" />
                  Order Items
                </h2>
              </div>
              <div class="divide-y">
                <div v-for="item in order.items" :key="item.id" class="p-6 flex items-center space-x-4">
                  <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0">
                    <!-- Image can be added here -->
                  </div>
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-800">{{ item.name }}</h3>
                    <p class="text-sm text-gray-600">{{ item.description }}</p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-gray-900">${{ item.price.toFixed(2) }}</p>
                    <p class="text-sm text-gray-600">Qty: {{ item.quantity }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Customer & Shipping -->
            <div class="grid md:grid-cols-2 gap-8">
              <!-- Customer Information -->
              <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b">
                  <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <User class="w-6 h-6 mr-3 text-orange-500" />
                    Customer Information
                  </h2>
                </div>
                <div class="p-6">
                  <p class="font-semibold">{{ order.user ? order.user.name : order.customer_name }}</p>
                  <p>{{ order.user ? order.user.email : order.customer_email }}</p>
                  <p>{{ order.user ? order.user.phone : order.customer_phone }}</p>
                </div>
              </div>

              <!-- Shipping Information -->
              <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b">
                  <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <Truck class="w-6 h-6 mr-3 text-orange-500" />
                    Shipping Information
                  </h2>
                </div>
                <div class="p-6">
                  <div v-if="order.is_gift" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                      <Gift class="w-5 h-5 text-yellow-600 mr-3" />
                      <p class="text-sm text-yellow-800 font-medium">This order is a gift for:</p>
                    </div>
                    <div class="mt-3 text-gray-700">
                      <p class="font-semibold">{{ order.recipient_name }}</p>
                      <p>{{ order.recipient_email }}</p>
                      <p>{{ order.recipient_phone }}</p>
                      <p>{{ order.recipient_address }}</p>
                    </div>
                  </div>
                  <div v-else>
                    <p class="font-semibold">{{ order.customer_name }}</p>
                    <p>{{ order.customer_email }}</p>
                    <p>{{ order.customer_phone }}</p>
                    <p>{{ order.customer_address }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary & Actions -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
              <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
              <div class="space-y-4 mb-6">
                <div class="flex justify-between">
                  <span class="text-gray-600">Subtotal</span>
                  <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Tax (5%)</span>
                  <span class="font-medium">${{ tax.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Shipping</span>
                  <span class="font-medium text-green-600">Free</span>
                </div>
                <hr />
                <div class="flex justify-between font-bold text-lg">
                  <span>Total</span>
                  <span>${{ total.toFixed(2) }}</span>
                </div>
              </div>

              <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>
                <div class="flex flex-col space-y-3">
                  <!-- Step 1: Approve/Decline -->
                  <div v-if="order.status === 'PENDING'" class="flex space-x-2">
                    <button @click="updateStatus('PROCESSING')" :disabled="isUpdating" class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600">Approve</button>
                    <button @click="updateStatus('CANCELLED')" :disabled="isUpdating" class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600">Decline</button>
                  </div>

                  <!-- Step 2: Confirm Payment -->
                  <div v-if="order.status === 'PROCESSING' && order.payment_status === 'PENDING'">
                    <button @click="openPaymentModal" :disabled="isUpdating" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Confirm Payment</button>
                  </div>

                  <!-- Step 3: Schedule Installation -->
                  <div v-if="order.status === 'PROCESSING' && order.payment_status === 'PAID'">
                    <div class="flex flex-col space-y-2">
                      <input type="datetime-local" v-model="installationDate" class="w-full border-gray-300 rounded-lg">
                      <button @click="scheduleInstallation" :disabled="isUpdating || !installationDate" class="w-full bg-purple-500 text-white py-2 px-4 rounded-lg hover:bg-purple-600">Schedule Installation</button>
                    </div>
                  </div>

                  <!-- Step 4: Mark as Installed -->
                  <div v-if="order.status === 'SCHEDULED'">
                    <button @click="updateStatus('INSTALLED')" :disabled="isUpdating" class="w-full bg-teal-500 text-white py-2 px-4 rounded-lg hover:bg-teal-600">Mark as Installed</button>
                  </div>

                  <button @click="downloadInvoice" :disabled="isUpdating" class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300">Download Invoice</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Confirm Payment</h2>
        
        <div class="space-y-4">
          <!-- Payment Method Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
            <div class="space-y-2">
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="paymentMethod === 'CASH' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                <input type="radio" v-model="paymentMethod" value="CASH" class="mr-3">
                <span class="font-medium">Cash Payment</span>
              </label>
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="paymentMethod === 'BANK_TRANSFER' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                <input type="radio" v-model="paymentMethod" value="BANK_TRANSFER" class="mr-3">
                <span class="font-medium">Bank Transfer</span>
              </label>
            </div>
          </div>

          <!-- Transaction Reference (only for Bank Transfer) -->
          <div v-if="paymentMethod === 'BANK_TRANSFER'">
            <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Reference *</label>
            <input 
              type="text" 
              v-model="transactionReference" 
              placeholder="Enter transaction reference number"
              class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              required
            >
            <p class="text-xs text-gray-500 mt-1">Enter the bank transaction reference number</p>
          </div>

          <!-- Order Summary -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between mb-2">
              <span class="text-gray-600">Order Total:</span>
              <span class="font-bold text-lg">${{ total.toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex space-x-3 mt-6">
          <button 
            @click="closePaymentModal" 
            :disabled="isUpdating"
            class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 disabled:opacity-50"
          >
            Cancel
          </button>
          <button 
            @click="confirmPayment" 
            :disabled="isUpdating"
            class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 disabled:opacity-50"
          >
            {{ isUpdating ? 'Processing...' : 'Confirm Payment' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
