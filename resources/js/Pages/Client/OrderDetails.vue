<script setup>
import { computed } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Home, ChevronRight, FileText, User, Gift, Truck, DollarSign, Package } from 'lucide-vue-next';

const props = defineProps({
  order: Object,
});

const subtotal = computed(() => {
  return props.order.items.reduce((acc, item) => acc + item.price * item.quantity, 0);
});

const tax = computed(() => subtotal.value * 0.05); // Assuming 5% tax
const shipping = computed(() => 0); // Assuming free shipping for now
const total = computed(() => subtotal.value + tax.value + shipping.value);

const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};
</script>

<template>
  <Head :title="'Order Details - ' + order.id" />
  <ClientLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Breadcrumbs -->
      <div class="flex items-center text-sm text-gray-600 mb-6">
        <Home class="w-4 h-4 mr-2" />
        <Link href="/dashboard" class="hover:underline">Dashboard</Link>
        <ChevronRight class="w-4 h-4 mx-2" />
        <Link href="/orders" class="hover:underline">My Orders</Link>
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
                  <!-- You can add an image here if available -->
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

          <!-- Payment & Shipping -->
          <div class="grid md:grid-cols-2 gap-8">
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

            <!-- Payment Details -->
            <div class="bg-white rounded-lg shadow-md">
              <div class="p-6 border-b">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                  <DollarSign class="w-6 h-6 mr-3 text-orange-500" />
                  Payment Details
                </h2>
              </div>
              <div class="p-6 space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Payment Method:</span>
                  <span class="font-semibold">{{ order.payment_method }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Payment Status:</span>
                  <span class="font-semibold">{{ order.payment_status }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
            <div class="space-y-4">
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
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
