<script setup>
import { ref, onMounted, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Home, ChevronRight, User, Gift, Truck, DollarSign, Package, Info } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
  orderId: [String, Number],
});

const order = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchOrder = async () => {
  try {
    const response = await axios.get(`/admin/orders/${props.orderId}/data`);
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
          <router-link to="/admin/dashboard" class="hover:underline">Admin Dashboard</router-link>
          <ChevronRight class="w-4 h-4 mx-2" />
          <router-link to="/admin/orders" class="hover:underline">Orders</router-link>
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
                <!-- Add admin actions here, e.g., update status, etc. -->
                <div class="flex flex-col space-y-3">
                    <button class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Update Status</button>
                    <button class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300">Download Invoice</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
