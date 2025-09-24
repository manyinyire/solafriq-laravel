<script setup>
import { computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { CheckCircle, Package, Mail, Phone, MapPin, CreditCard, Calendar, Download, Home, ArrowRight, DollarSign, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
  order: Object,
});

const formattedDate = computed(() => {
  return new Date(props.order.created_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const estimatedDelivery = computed(() => {
  const deliveryDate = new Date(props.order.created_at);
  deliveryDate.setDate(deliveryDate.getDate() + 14); // 2 weeks delivery
  return deliveryDate.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const goHome = () => {
  window.location.href = '/';
};

const downloadInvoice = () => {
  // This would typically generate and download a PDF invoice
  alert('Invoice download feature will be implemented soon!');
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="container mx-auto px-4">
        <!-- Success Header -->
        <div class="text-center mb-12">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <CheckCircle class="w-12 h-12 text-green-600" />
          </div>
          <h1 class="text-4xl font-bold text-gray-900 mb-4">Order Confirmed!</h1>
          <p class="text-xl text-gray-600 max-w-2xl mx-auto">
            Thank you for your order. We've received your purchase and will begin processing it shortly.
          </p>
        </div>

        <!-- Order Details -->
        <div class="max-w-4xl mx-auto grid lg:grid-cols-3 gap-8">
          <!-- Order Summary -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Order Info -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6">Order Information</h2>

              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <h3 class="font-medium text-gray-900 mb-3">Order Details</h3>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Order Number:</span>
                      <span class="font-medium">#{{ order.id }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tracking Number:</span>
                      <span class="font-medium">{{ order.tracking_number }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Order Date:</span>
                      <span class="font-medium">{{ formattedDate }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Status:</span>
                      <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                        {{ order.status }}
                      </span>
                    </div>
                  </div>
                </div>

                <div>
                  <h3 class="font-medium text-gray-900 mb-3">Customer Information</h3>
                  <div class="space-y-2 text-sm">
                    <div class="flex items-center space-x-2">
                      <Package class="w-4 h-4 text-gray-400" />
                      <span>{{ order.customer_name }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <Mail class="w-4 h-4 text-gray-400" />
                      <span>{{ order.customer_email }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <Phone class="w-4 h-4 text-gray-400" />
                      <span>{{ order.customer_phone }}</span>
                    </div>
                    <div class="flex items-start space-x-2">
                      <MapPin class="w-4 h-4 text-gray-400 mt-0.5" />
                      <span class="text-gray-700">{{ order.customer_address }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6">Order Items</h2>

              <div class="space-y-4">
                <div v-for="item in order.items" :key="item.id"
                     class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                  <div
                    class="w-16 h-16 bg-gradient-to-br rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%)"
                  >
                    <Package class="w-6 h-6 text-white opacity-80" />
                  </div>

                  <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900">{{ item.name }}</h3>
                    <p class="text-gray-600 text-sm">{{ item.description }}</p>
                    <p class="text-gray-600 text-sm">Quantity: {{ item.quantity }}</p>
                  </div>

                  <div class="text-right">
                    <div class="font-bold text-gray-900">${{ (item.price * item.quantity).toLocaleString() }}</div>
                    <div class="text-sm text-gray-600">${{ item.price.toLocaleString() }} each</div>
                  </div>
                </div>
              </div>

              <div class="border-t border-gray-200 pt-4 mt-6">
                <div class="flex justify-between items-center">
                  <span class="text-lg font-bold text-gray-900">Total Amount:</span>
                  <span class="text-2xl font-bold text-gray-900">${{ order.total_amount.toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <!-- What's Next -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6">What's Next?</h2>

              <div class="space-y-4">
                <div class="flex items-start space-x-4">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-600 font-bold text-sm">1</span>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Order Processing</h3>
                    <p class="text-gray-600 text-sm">We'll review your order and begin processing within 24 hours.</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4">
                  <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-orange-600 font-bold text-sm">2</span>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Preparation & Shipping</h3>
                    <p class="text-gray-600 text-sm">Your solar system will be prepared and shipped to your address.</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-green-600 font-bold text-sm">3</span>
                  </div>
                  <div>
                    <h3 class="font-medium text-gray-900">Installation Support</h3>
                    <p class="text-gray-600 text-sm">Our team will contact you to schedule installation and provide support.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Payment Info -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                <CreditCard class="w-5 h-5 mr-2 text-orange-500" />
                Payment Information
              </h3>

              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Method:</span>
                  <span class="font-medium capitalize">
                    {{ order.payment_method.toLowerCase().replace('_', ' ') }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Status:</span>
                  <span :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    order.payment_status === 'PAID'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  ]">
                    {{ order.payment_status }}
                  </span>
                </div>
              </div>

              <!-- Cash on Delivery Instructions -->
              <div v-if="order.payment_method.toLowerCase() === 'cash_on_delivery'" class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                  <DollarSign class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" />
                  <div>
                    <h4 class="font-medium text-green-900 text-sm mb-2">Cash on Delivery Instructions</h4>
                    <ul class="text-green-700 text-sm space-y-1">
                      <li>• Prepare <strong>${{ order.total_amount.toLocaleString() }}</strong> in cash</li>
                      <li>• Our team will contact you to schedule delivery</li>
                      <li>• Payment due upon installation completion</li>
                      <li>• Cash, bank transfer, or mobile money accepted</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delivery Info -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                <Calendar class="w-5 h-5 mr-2 text-orange-500" />
                Delivery Information
              </h3>

              <div class="space-y-3">
                <div>
                  <span class="text-gray-600 text-sm">Estimated Delivery:</span>
                  <div class="font-medium">{{ estimatedDelivery }}</div>
                </div>
                <div>
                  <span class="text-gray-600 text-sm">Tracking Number:</span>
                  <div class="font-medium">{{ order.tracking_number }}</div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>

              <div class="space-y-3">
                <button @click="downloadInvoice"
                        class="w-full flex items-center justify-center space-x-2 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors">
                  <Download class="w-4 h-4" />
                  <span>Download Invoice</span>
                </button>

                <button @click="goHome"
                        class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white py-3 px-4 rounded-lg font-medium transition-all">
                  <Home class="w-4 h-4" />
                  <span>Continue Shopping</span>
                </button>
              </div>
            </div>

            <!-- Support -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
              <h3 class="font-bold text-blue-900 mb-3">Need Help?</h3>
              <p class="text-blue-700 text-sm mb-4">
                Our customer support team is here to help with any questions about your order.
              </p>
              <a href="/contact"
                 class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium text-sm">
                Contact Support
                <ArrowRight class="w-4 h-4 ml-1" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>