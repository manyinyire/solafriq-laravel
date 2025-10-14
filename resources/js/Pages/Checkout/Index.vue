<script setup>
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';
import { CreditCard, Truck, Shield, CheckCircle, ArrowLeft, Lock, User, Mail, Phone, MapPin, Zap, AlertCircle, DollarSign } from 'lucide-vue-next';

const props = defineProps({
  cart: Object,
  cartItems: Array,
  total: Number,
  itemCount: Number,
  customer: Object,
});

const checkoutMode = ref('quote'); // 'quote' or 'buy'

const form = ref({
  customer_name: props.customer?.name || '',
  customer_email: props.customer?.email || '',
  customer_phone: props.customer?.phone || '',
  customer_address: props.customer?.address || '',
  notes: '',
  payment_method: 'card',
});

const processing = ref(false);
const errors = ref({});

const subtotal = computed(() => {
  return props.cartItems?.reduce((sum, item) => sum + (item.price * item.quantity), 0) || 0;
});

const tax = computed(() => {
  return subtotal.value * 0.05;
});

const shipping = computed(() => {
  return subtotal.value > 5000 ? 0 : 250;
});

const finalTotal = computed(() => {
  return subtotal.value + tax.value + shipping.value;
});

const requestQuote = () => {
  if (!validateForm(false)) {
    return;
  }

  processing.value = true;
  errors.value = {};

  const quoteData = {
    customer_name: form.value.customer_name,
    customer_email: form.value.customer_email,
    customer_phone: form.value.customer_phone,
    customer_address: form.value.customer_address,
    notes: form.value.notes,
  };

  router.post('/checkout/request-quote', quoteData, {
    preserveScroll: true,
    onSuccess: (response) => {
      processing.value = false;
      // Redirect will be handled by the server
    },
    onError: (responseErrors) => {
      processing.value = false;
      errors.value = responseErrors;
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const buyNow = () => {
  if (!validateForm(true)) {
    return;
  }

  processing.value = true;
  errors.value = {};

  const orderData = {
    customer_name: form.value.customer_name,
    customer_email: form.value.customer_email,
    customer_phone: form.value.customer_phone,
    customer_address: form.value.customer_address,
    payment_method: form.value.payment_method,
    notes: form.value.notes,
  };

  router.post('/checkout/process', orderData, {
    preserveScroll: true,
    onSuccess: (response) => {
      processing.value = false;
      // Redirect will be handled by the server
    },
    onError: (responseErrors) => {
      processing.value = false;
      errors.value = responseErrors;
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const validateForm = (requirePayment = false) => {
  const newErrors = {};

  if (!form.value.customer_name.trim()) {
    newErrors.customer_name = 'Name is required';
  }

  if (!form.value.customer_email.trim()) {
    newErrors.customer_email = 'Email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.customer_email)) {
    newErrors.customer_email = 'Please enter a valid email address';
  }

  if (!form.value.customer_phone.trim()) {
    newErrors.customer_phone = 'Phone number is required';
  }

  if (!form.value.customer_address.trim()) {
    newErrors.customer_address = 'Address is required';
  }

  // Only validate payment method if buying now
  if (requirePayment && !form.value.payment_method) {
    newErrors.payment_method = 'Please select a payment method';
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

const goBack = () => {
  router.visit('/cart');
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <CreditCard class="w-8 h-8 mr-3 text-orange-500" />
                Checkout
              </h1>
              <p class="text-gray-600 mt-2">
                {{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} in your cart
              </p>
            </div>
            <button @click="goBack"
                    class="flex items-center text-orange-600 hover:text-orange-700 font-medium">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Back to Cart
            </button>
          </div>
        </div>

        <!-- Checkout Mode Selector -->
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Choose Your Option</h2>
          <div class="grid md:grid-cols-2 gap-4">
            <button
              @click="checkoutMode = 'quote'"
              :class="[
                'p-6 rounded-xl border-2 transition-all duration-200 text-left',
                checkoutMode === 'quote'
                  ? 'border-orange-500 bg-orange-50'
                  : 'border-gray-300 hover:border-orange-300'
              ]"
            >
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <div :class="[
                    'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                    checkoutMode === 'quote' ? 'border-orange-500' : 'border-gray-300'
                  ]">
                    <div v-if="checkoutMode === 'quote'" class="w-3 h-3 rounded-full bg-orange-500"></div>
                  </div>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-900 mb-1">Request Quote</h3>
                  <p class="text-sm text-gray-600">Get a detailed quote from our team. We'll review your request and send you a customized quote within 24-48 hours.</p>
                  <div class="mt-2 flex items-center text-xs text-green-600">
                    <CheckCircle class="w-4 h-4 mr-1" />
                    No payment required now
                  </div>
                </div>
              </div>
            </button>

            <button
              @click="checkoutMode = 'buy'"
              :class="[
                'p-6 rounded-xl border-2 transition-all duration-200 text-left',
                checkoutMode === 'buy'
                  ? 'border-orange-500 bg-orange-50'
                  : 'border-gray-300 hover:border-orange-300'
              ]"
            >
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <div :class="[
                    'w-6 h-6 rounded-full border-2 flex items-center justify-center',
                    checkoutMode === 'buy' ? 'border-orange-500' : 'border-gray-300'
                  ]">
                    <div v-if="checkoutMode === 'buy'" class="w-3 h-3 rounded-full bg-orange-500"></div>
                  </div>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-gray-900 mb-1">Buy Now</h3>
                  <p class="text-sm text-gray-600">Complete your purchase immediately with your preferred payment method. Fast processing and quick delivery.</p>
                  <div class="mt-2 flex items-center text-xs text-blue-600">
                    <Zap class="w-4 h-4 mr-1" />
                    Instant order processing
                  </div>
                </div>
              </div>
            </button>
          </div>
        </div>

        <!-- Checkout Form -->
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Form Section -->
          <div class="lg:col-span-2 space-y-8">
            <!-- Customer Information -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <User class="w-5 h-5 mr-2 text-orange-500" />
                Customer Information
              </h2>

              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name *
                  </label>
                  <input
                    v-model="form.customer_name"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Enter your full name"
                    :class="{ 'border-red-500': errors.customer_name, 'bg-gray-100': !!customer }"
                    :disabled="!!customer"
                  />
                  <p v-if="errors.customer_name" class="text-red-500 text-sm mt-1">{{ errors.customer_name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email Address *
                  </label>
                  <input
                    v-model="form.customer_email"
                    type="email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Enter your email"
                    :class="{ 'border-red-500': errors.customer_email, 'bg-gray-100': !!customer }"
                    :disabled="!!customer"
                  />
                  <p v-if="errors.customer_email" class="text-red-500 text-sm mt-1">{{ errors.customer_email }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number *
                  </label>
                  <input
                    v-model="form.customer_phone"
                    type="tel"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Enter your phone number"
                    :class="{ 'border-red-500': errors.customer_phone, 'bg-gray-100': !!customer }"
                    :disabled="!!customer"
                  />
                  <p v-if="errors.customer_phone" class="text-red-500 text-sm mt-1">{{ errors.customer_phone }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Address *
                  </label>
                  <textarea
                    v-model="form.customer_address"
                    rows="3"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Enter your complete address"
                    :class="{ 'border-red-500': errors.customer_address, 'bg-gray-100': !!customer }"
                    :disabled="!!customer"
                  ></textarea>
                  <p v-if="errors.customer_address" class="text-red-500 text-sm mt-1">{{ errors.customer_address }}</p>
                </div>
              </div>

              <div class="mt-6">
                <label class="flex items-center">
                  <input type="checkbox" v-model="form.is_gift" class="h-4 w-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                  <span class="ml-2 text-sm text-gray-900">Is this a gift for someone else?</span>
                </label>
              </div>

              <div v-if="form.is_gift" class="mt-6 border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recipient's Details</h3>
                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Recipient's Full Name *
                    </label>
                    <input
                      v-model="form.recipient_name"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Enter recipient's full name"
                      :class="{ 'border-red-500': errors.recipient_name }"
                    />
                    <p v-if="errors.recipient_name" class="text-red-500 text-sm mt-1">{{ errors.recipient_name }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Recipient's Email Address *
                    </label>
                    <input
                      v-model="form.recipient_email"
                      type="email"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Enter recipient's email"
                      :class="{ 'border-red-500': errors.recipient_email }"
                    />
                    <p v-if="errors.recipient_email" class="text-red-500 text-sm mt-1">{{ errors.recipient_email }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Recipient's Phone Number *
                    </label>
                    <input
                      v-model="form.recipient_phone"
                      type="tel"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Enter recipient's phone number"
                      :class="{ 'border-red-500': errors.recipient_phone }"
                    />
                    <p v-if="errors.recipient_phone" class="text-red-500 text-sm mt-1">{{ errors.recipient_phone }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Recipient's Address *
                    </label>
                    <textarea
                      v-model="form.recipient_address"
                      rows="3"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="Enter recipient's complete address"
                      :class="{ 'border-red-500': errors.recipient_address }"
                    ></textarea>
                    <p v-if="errors.recipient_address" class="text-red-500 text-sm mt-1">{{ errors.recipient_address }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Notes -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <CreditCard class="w-5 h-5 mr-2 text-orange-500" />
                Additional Notes
              </h2>

              <textarea
                v-model="form.notes"
                rows="5"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                placeholder="Enter any additional notes or comments"
              ></textarea>
            </div>

            <!-- Payment Information (Only for Buy Now) -->
            <div v-if="checkoutMode === 'buy'" class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <CreditCard class="w-5 h-5 mr-2 text-orange-500" />
                Payment Information
              </h2>

              <!-- Payment Method Selection -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Payment Method
                </label>
                <div class="grid grid-cols-3 gap-4">
                  <label class="relative cursor-pointer">
                    <input
                      v-model="form.payment_method"
                      type="radio"
                      value="card"
                      class="sr-only"
                    />
                    <div :class="[
                      'border-2 rounded-lg p-4 text-center transition-all duration-200',
                      form.payment_method === 'card'
                        ? 'border-orange-500 bg-orange-50'
                        : 'border-gray-300 hover:border-gray-400'
                    ]">
                      <CreditCard class="w-6 h-6 mx-auto mb-2" />
                      <span class="font-medium text-sm">Credit Card</span>
                    </div>
                  </label>

                  <label class="relative cursor-pointer">
                    <input
                      v-model="form.payment_method"
                      type="radio"
                      value="installment"
                      class="sr-only"
                    />
                    <div :class="[
                      'border-2 rounded-lg p-4 text-center transition-all duration-200',
                      form.payment_method === 'installment'
                        ? 'border-orange-500 bg-orange-50'
                        : 'border-gray-300 hover:border-gray-400'
                    ]">
                      <Truck class="w-6 h-6 mx-auto mb-2" />
                      <span class="font-medium text-sm">Installment Plan</span>
                    </div>
                  </label>

                  <label class="relative cursor-pointer">
                    <input
                      v-model="form.payment_method"
                      type="radio"
                      value="cash_on_delivery"
                      class="sr-only"
                    />
                    <div :class="[
                      'border-2 rounded-lg p-4 text-center transition-all duration-200',
                      form.payment_method === 'cash_on_delivery'
                        ? 'border-orange-500 bg-orange-50'
                        : 'border-gray-300 hover:border-gray-400'
                    ]">
                      <DollarSign class="w-6 h-6 mx-auto mb-2" />
                      <span class="font-medium text-sm">Cash on Delivery</span>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Credit Card Form -->
              <div v-if="form.payment_method === 'card'" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Card Number *
                  </label>
                  <input
                    v-model="form.card_number"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="1234 5678 9012 3456"
                    :class="{ 'border-red-500': errors.card_number }"
                  />
                  <p v-if="errors.card_number" class="text-red-500 text-sm mt-1">{{ errors.card_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Expiry Date *
                    </label>
                    <input
                      v-model="form.card_expiry"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="MM/YY"
                      :class="{ 'border-red-500': errors.card_expiry }"
                    />
                    <p v-if="errors.card_expiry" class="text-red-500 text-sm mt-1">{{ errors.card_expiry }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      CVC *
                    </label>
                    <input
                      v-model="form.card_cvc"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                      placeholder="123"
                      :class="{ 'border-red-500': errors.card_cvc }"
                    />
                    <p v-if="errors.card_cvc" class="text-red-500 text-sm mt-1">{{ errors.card_cvc }}</p>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Cardholder Name *
                  </label>
                  <input
                    v-model="form.cardholder_name"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                    placeholder="Name as it appears on card"
                    :class="{ 'border-red-500': errors.cardholder_name }"
                  />
                  <p v-if="errors.cardholder_name" class="text-red-500 text-sm mt-1">{{ errors.cardholder_name }}</p>
                </div>
              </div>

              <!-- Installment Plan Info -->
              <div v-else-if="form.payment_method === 'installment'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                  <AlertCircle class="w-5 h-5 text-blue-600 mt-0.5" />
                  <div>
                    <h4 class="font-medium text-blue-900 mb-2">Installment Plan Available</h4>
                    <p class="text-blue-700 text-sm">
                      Pay in monthly installments starting at $150/month. Our team will contact you after order confirmation to set up your payment plan.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cash on Delivery Info -->
              <div v-else-if="form.payment_method === 'cash_on_delivery'" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                  <DollarSign class="w-5 h-5 text-green-600 mt-0.5" />
                  <div>
                    <h4 class="font-medium text-green-900 mb-2">Cash on Delivery</h4>
                    <p class="text-green-700 text-sm mb-3">
                      Pay with cash when your solar system is delivered and installed. No upfront payment required.
                    </p>
                    <div class="bg-green-100 rounded-md p-3">
                      <h5 class="font-medium text-green-900 text-sm mb-2">What to expect:</h5>
                      <ul class="text-green-700 text-sm space-y-1">
                        <li>• Our team will contact you to schedule delivery</li>
                        <li>• Professional installation included</li>
                        <li>• Pay the full amount upon completion</li>
                        <li>• Cash, bank transfer, or mobile money accepted</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border p-6 sticky top-8">
              <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>

              <!-- Order Items -->
              <div class="space-y-4 mb-6">
                <div v-for="item in cartItems" :key="item.id" class="flex items-center space-x-3">
                  <div
                    class="w-12 h-12 bg-gradient-to-br rounded-lg flex items-center justify-center flex-shrink-0"
                    :style="{ background: (item.solar_system?.gradient_colors || item.product?.gradient_colors) || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
                  >
                    <Zap class="w-4 h-4 text-white opacity-80" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-gray-900 text-sm truncate">{{ item.item_name || item.solar_system?.name || item.product?.name || 'Item' }}</h4>
                    <p class="text-gray-600 text-xs">Qty: {{ item.quantity }}</p>
                  </div>
                  <span class="font-medium text-gray-900 text-sm">
                    ${{ (item.price * item.quantity).toLocaleString() }}
                  </span>
                </div>
              </div>

              <hr class="border-gray-200 mb-4" />

              <!-- Price Breakdown -->
              <div class="space-y-3 mb-6">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal</span>
                  <span class="font-medium">${{ subtotal.toLocaleString() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tax (5%)</span>
                  <span class="font-medium">${{ tax.toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Shipping</span>
                  <span class="font-medium">
                    <span v-if="shipping === 0" class="text-green-600">Free</span>
                    <span v-else>${{ shipping.toLocaleString() }}</span>
                  </span>
                </div>
                <hr class="border-gray-200" />
                <div class="flex justify-between font-bold">
                  <span>Total</span>
                  <span class="text-gray-900">${{ finalTotal.toLocaleString() }}</span>
                </div>
              </div>

              <!-- Security Notice -->
              <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-6">
                <div class="flex items-center space-x-2">
                  <Lock class="w-4 h-4 text-green-600" />
                  <span class="text-sm text-green-700 font-medium">Secure 256-bit SSL encryption</span>
                </div>
              </div>

              <!-- Submit Button -->
              <button 
                v-if="checkoutMode === 'quote'"
                @click="requestQuote"
                :disabled="processing"
                class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:from-gray-400 disabled:to-gray-500 text-white py-4 px-6 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] disabled:transform-none disabled:cursor-not-allowed">
                <span v-if="processing">Submitting Request...</span>
                <span v-else class="flex items-center justify-center">
                  <CheckCircle class="w-5 h-5 mr-2" />
                  Request Quote
                </span>
              </button>

              <button 
                v-else
                @click="buyNow"
                :disabled="processing"
                class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 disabled:from-gray-400 disabled:to-gray-500 text-white py-4 px-6 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] disabled:transform-none disabled:cursor-not-allowed">
                <span v-if="processing">Processing Order...</span>
                <span v-else class="flex items-center justify-center">
                  <CheckCircle class="w-5 h-5 mr-2" />
                  Complete Purchase
                </span>
              </button>

              <!-- Trust Badges -->
              <div class="grid grid-cols-3 gap-2 mt-6 text-center">
                <div class="text-center">
                  <Shield class="w-6 h-6 text-gray-400 mx-auto mb-1" />
                  <span class="text-xs text-gray-600">Secure</span>
                </div>
                <div class="text-center">
                  <Truck class="w-6 h-6 text-gray-400 mx-auto mb-1" />
                  <span class="text-xs text-gray-600">Fast Shipping</span>
                </div>
                <div class="text-center">
                  <CheckCircle class="w-6 h-6 text-gray-400 mx-auto mb-1" />
                  <span class="text-xs text-gray-600">Guaranteed</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.sticky {
  position: sticky;
}
</style>