<script setup>
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';
import { ShoppingCart, Plus, Minus, Trash2, ArrowLeft, ArrowRight, Zap, Shield, CheckCircle, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
  cart: Object,
  cartItems: Array,
  total: Number,
  itemCount: Number,
});

const isUpdating = ref({});

const subtotal = computed(() => {
  return props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const tax = computed(() => {
  return subtotal.value * 0.05; // 5% tax
});

const shipping = computed(() => {
  return subtotal.value > 5000 ? 0 : 250; // Free shipping over $5000
});

const finalTotal = computed(() => {
  return subtotal.value + tax.value + shipping.value;
});

const updateQuantity = (itemId, quantity) => {
  if (quantity < 1) return;

  isUpdating.value[itemId] = true;

  router.patch(`/cart/items/${itemId}`, {
    quantity: quantity,
  }, {
    preserveState: true,
    onSuccess: () => {
      isUpdating.value[itemId] = false;
    },
    onError: () => {
      isUpdating.value[itemId] = false;
    }
  });
};

const removeItem = (itemId) => {
  if (confirm('Are you sure you want to remove this item from your cart?')) {
    router.delete(`/cart/items/${itemId}`, {
      preserveState: true,
    });
  }
};

const clearCart = () => {
  if (confirm('Are you sure you want to clear your entire cart?')) {
    router.delete('/cart/clear', {
      preserveState: true,
    });
  }
};

const proceedToCheckout = () => {
  router.visit('/checkout');
};

const continueShopping = () => {
  router.visit('/');
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
                <ShoppingCart class="w-8 h-8 mr-3 text-orange-500" />
                Shopping Cart
              </h1>
              <p class="text-gray-600 mt-2">
                {{ itemCount }} item{{ itemCount !== 1 ? 's' : '' }} in your cart
              </p>
            </div>
            <button @click="continueShopping"
                    class="flex items-center text-orange-600 hover:text-orange-700 font-medium">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Continue Shopping
            </button>
          </div>
        </div>

        <!-- Empty Cart State -->
        <div v-if="!cartItems || cartItems.length === 0" class="text-center py-16">
          <div class="max-w-md mx-auto">
            <ShoppingCart class="w-24 h-24 text-gray-300 mx-auto mb-6" />
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Your cart is empty</h2>
            <p class="text-gray-600 mb-8">
              Looks like you haven't added any solar systems to your cart yet.
            </p>
            <button @click="continueShopping"
                    class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-8 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300">
              Browse Solar Systems
            </button>
          </div>
        </div>

        <!-- Cart Content -->
        <div v-else class="grid lg:grid-cols-3 gap-8">
          <!-- Cart Items -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Actions Bar -->
            <div class="bg-white rounded-xl p-4 shadow-sm border flex justify-between items-center">
              <span class="text-gray-700 font-medium">{{ cartItems.length }} item{{ cartItems.length !== 1 ? 's' : '' }}</span>
              <button @click="clearCart"
                      class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center">
                <Trash2 class="w-4 h-4 mr-1" />
                Clear Cart
              </button>
            </div>

            <!-- Cart Items List -->
            <div class="space-y-4">
              <div v-for="item in cartItems" :key="item.id"
                   class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow duration-300">
                <div class="p-6">
                  <div class="flex items-start space-x-4">
                    <!-- Product Image -->
                    <div class="flex-shrink-0">
                      <div
                        class="w-24 h-24 bg-gradient-to-br rounded-xl flex items-center justify-center"
                        :style="{ background: item.solar_system.gradient_colors || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
                      >
                        <Zap class="w-8 h-8 text-white opacity-80" />
                      </div>
                    </div>

                    <!-- Product Details -->
                    <div class="flex-1 min-w-0">
                      <div class="flex justify-between items-start">
                        <div>
                          <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            {{ item.solar_system.name }}
                          </h3>
                          <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                            <div class="flex items-center">
                              <Zap class="w-4 h-4 mr-1 text-orange-500" />
                              {{ item.solar_system.capacity }}kW
                            </div>
                            <div class="flex items-center">
                              <Shield class="w-4 h-4 mr-1 text-green-500" />
                              25yr Warranty
                            </div>
                          </div>
                          <p class="text-gray-600 text-sm">
                            {{ item.solar_system.short_description }}
                          </p>
                        </div>
                        <button @click="removeItem(item.id)"
                                class="text-gray-400 hover:text-red-500 transition-colors">
                          <Trash2 class="w-5 h-5" />
                        </button>
                      </div>

                      <!-- Quantity and Price -->
                      <div class="flex items-center justify-between mt-4">
                        <!-- Quantity Controls -->
                        <div class="flex items-center space-x-3">
                          <span class="text-sm text-gray-700 font-medium">Qty:</span>
                          <div class="flex items-center border border-gray-300 rounded-lg">
                            <button @click="updateQuantity(item.id, item.quantity - 1)"
                                    :disabled="item.quantity <= 1 || isUpdating[item.id]"
                                    class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                              <Minus class="w-4 h-4" />
                            </button>
                            <span class="px-4 py-2 font-medium">{{ item.quantity }}</span>
                            <button @click="updateQuantity(item.id, item.quantity + 1)"
                                    :disabled="isUpdating[item.id]"
                                    class="p-2 hover:bg-gray-100 disabled:opacity-50">
                              <Plus class="w-4 h-4" />
                            </button>
                          </div>
                        </div>

                        <!-- Price -->
                        <div class="text-right">
                          <div class="text-lg font-bold text-gray-900">
                            ${{ (item.price * item.quantity).toLocaleString() }}
                          </div>
                          <div class="text-sm text-gray-600">
                            ${{ item.price.toLocaleString() }} each
                          </div>
                        </div>
                      </div>
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

              <div class="space-y-4">
                <!-- Subtotal -->
                <div class="flex justify-between">
                  <span class="text-gray-600">Subtotal</span>
                  <span class="font-medium">${{ subtotal.toLocaleString() }}</span>
                </div>

                <!-- Tax -->
                <div class="flex justify-between">
                  <span class="text-gray-600">Tax (5%)</span>
                  <span class="font-medium">${{ tax.toFixed(2) }}</span>
                </div>

                <!-- Shipping -->
                <div class="flex justify-between">
                  <span class="text-gray-600">Shipping</span>
                  <span class="font-medium">
                    <span v-if="shipping === 0" class="text-green-600">Free</span>
                    <span v-else>${{ shipping.toLocaleString() }}</span>
                  </span>
                </div>

                <!-- Free Shipping Notice -->
                <div v-if="shipping > 0 && subtotal < 5000" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                  <div class="flex items-start space-x-2">
                    <AlertTriangle class="w-4 h-4 text-blue-600 mt-0.5" />
                    <p class="text-sm text-blue-700">
                      Add ${{ (5000 - subtotal).toLocaleString() }} more to qualify for free shipping!
                    </p>
                  </div>
                </div>

                <hr class="border-gray-200" />

                <!-- Total -->
                <div class="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span class="text-gray-900">${{ finalTotal.toLocaleString() }}</span>
                </div>

                <!-- Security Notice -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                  <div class="flex items-center space-x-2">
                    <CheckCircle class="w-4 h-4 text-green-600" />
                    <span class="text-sm text-green-700 font-medium">Secure checkout</span>
                  </div>
                </div>

                <!-- Checkout Button -->
                <button @click="proceedToCheckout"
                        class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white py-4 px-6 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
                  Proceed to Checkout
                  <ArrowRight class="w-5 h-5 ml-2 inline" />
                </button>

                <!-- Continue Shopping -->
                <button @click="continueShopping"
                        class="w-full border border-gray-300 hover:border-orange-500 hover:text-orange-600 py-3 px-6 rounded-xl font-medium transition-all duration-300">
                  Continue Shopping
                </button>
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