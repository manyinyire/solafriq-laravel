<script setup>
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Notification from '@/Components/Notification.vue';
import { ArrowLeft, ShoppingCart, Star, Zap, Shield, Award, Leaf, Plus, Minus, CheckCircle, Info, Sun, Battery, Monitor, Home, ArrowRight } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  system: Object,
  features: Array,
  products: Array,
  specifications: Array,
});

const quantity = ref(1);
const selectedTab = ref('overview');
const selectedImageIndex = ref(0);
const showNotification = ref(false);
const notificationConfig = ref({
  type: 'success',
  title: '',
  message: ''
});
const showCheckoutButton = ref(false);

const tabs = [
  { id: 'overview', label: 'Overview' },
  { id: 'features', label: 'Features' },
  { id: 'specifications', label: 'Specifications' },
  { id: 'included', label: 'What\'s Included' },
];

const images = computed(() => {
  const galleryImages = props.system.gallery_images || [];
  return [props.system.image_url, ...galleryImages].filter(Boolean);
});

const savings = computed(() => {
  if (props.system.original_price && props.system.price) {
    return props.system.original_price - props.system.price;
  }
  return 0;
});

const savingsPercentage = computed(() => {
  if (savings.value && props.system.original_price) {
    return Math.round((savings.value / props.system.original_price) * 100);
  }
  return 0;
});

const totalPrice = computed(() => {
  return props.system.price * quantity.value;
});

const incrementQuantity = () => {
  quantity.value++;
};

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

const showNotificationMessage = (type, title, message) => {
  notificationConfig.value = { type, title, message };
  showNotification.value = true;
};

const closeNotification = () => {
  showNotification.value = false;
};

const addToCart = () => {
  router.post('/cart/add', {
    type: 'solar_system',
    system_id: props.system.id,
    quantity: quantity.value,
  }, {
    preserveState: true,
    onSuccess: () => {
      showNotificationMessage('success', 'Added to Cart!', `${props.system.name} (Qty: ${quantity.value}) has been added to your cart successfully.`);
      showCheckoutButton.value = true;

      setTimeout(() => {
        showCheckoutButton.value = false;
      }, 8000);
    },
    onError: (errors) => {
      console.error('Cart add error:', errors);
      const errorMessage = errors.type || errors.error || 'Error adding product to cart. Please try again.';
      showNotificationMessage('error', 'Cart Error', errorMessage);
    }
  });
};

const goToCheckout = () => {
  router.visit('/cart');
};

const goBack = () => {
  window.history.back();
};

const selectImage = (index) => {
  selectedImageIndex.value = index;
};

const powerOptions = [
  {
    icon: Sun,
    title: "Solar Panel Array",
    description: "High-efficiency panels that convert sunlight into electricity"
  },
  {
    icon: Battery,
    title: "Battery Storage",
    description: "Reliable energy storage for 24/7 power availability"
  },
  {
    icon: Monitor,
    title: "Smart Monitoring",
    description: "Real-time system monitoring and performance tracking"
  },
  {
    icon: Home,
    title: "Home Integration",
    description: "Seamless integration with your home's electrical system"
  }
];
</script>

<template>
  <MainLayout>
    <!-- Notification Component -->
    <Notification
      :show="showNotification"
      :type="notificationConfig.type"
      :title="notificationConfig.title"
      :message="notificationConfig.message"
      @close="closeNotification"
    />

    <!-- Floating Checkout Button -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-300"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showCheckoutButton"
        class="fixed bottom-6 right-6 z-40"
      >
        <button
          @click="goToCheckout"
          class="bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white px-6 py-3 rounded-full font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center space-x-2 animate-bounce"
        >
          <ShoppingCart class="h-5 w-5" />
          <span>Go to Checkout</span>
          <ArrowRight class="h-4 w-4" />
        </button>
      </div>
    </Transition>
    <div class="min-h-screen bg-gray-50">
      <!-- Breadcrumb -->
      <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-4">
          <div class="flex items-center space-x-2 text-sm">
            <button @click="goBack" class="flex items-center text-gray-600 hover:text-orange-600 transition-colors">
              <ArrowLeft class="w-4 h-4 mr-1" />
              Back
            </button>
            <span class="text-gray-400">/</span>
            <span class="text-gray-600">Solar Systems</span>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900 font-medium">{{ system.name }}</span>
          </div>
        </div>
      </div>

      <div class="container mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-2 gap-12">
          <!-- Product Images -->
          <div class="space-y-4">
            <!-- Main Image -->
            <div class="aspect-square bg-white rounded-2xl shadow-lg overflow-hidden border">
              <div
                class="w-full h-full bg-gradient-to-br flex items-center justify-center"
                :style="{ background: system.gradient_colors || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
              >
                <div class="text-white text-center">
                  <Zap class="w-24 h-24 mx-auto mb-4 opacity-80" />
                  <h3 class="text-2xl font-bold">{{ system.name }}</h3>
                  <p class="text-lg opacity-90">{{ system.capacity }}kW Solar System</p>
                </div>
              </div>
            </div>

            <!-- Thumbnail Images -->
            <div v-if="images.length > 1" class="grid grid-cols-4 gap-2">
              <button
                v-for="(image, index) in images.slice(0, 4)"
                :key="index"
                @click="selectImage(index)"
                :class="[
                  'aspect-square bg-white rounded-lg border-2 overflow-hidden transition-all duration-200',
                  selectedImageIndex === index
                    ? 'border-orange-500 ring-2 ring-orange-200'
                    : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <div
                  class="w-full h-full bg-gradient-to-br flex items-center justify-center"
                  :style="{ background: system.gradient_colors || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
                >
                  <Zap class="w-8 h-8 text-white opacity-60" />
                </div>
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="space-y-8">
            <!-- Product Header -->
            <div>
              <div class="flex items-center space-x-2 mb-2">
                <span v-if="system.is_popular" class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                  <Star class="w-4 h-4 inline mr-1" />
                  Most Popular
                </span>
                <span v-if="savings > 0" class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                  Save ${{ savings.toLocaleString() }} ({{ savingsPercentage }}%)
                </span>
              </div>

              <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ system.name }}</h1>

              <div class="flex items-center space-x-6 text-gray-600 mb-6">
                <div class="flex items-center space-x-2">
                  <Zap class="w-5 h-5 text-orange-500" />
                  <span class="font-medium">{{ system.capacity }}kW Capacity</span>
                </div>
                <div class="flex items-center space-x-2">
                  <Shield class="w-5 h-5 text-green-500" />
                  <span class="font-medium">25 Year Warranty</span>
                </div>
                <div class="flex items-center space-x-2">
                  <Award class="w-5 h-5 text-blue-500" />
                  <span class="font-medium">Certified Quality</span>
                </div>
              </div>

              <p class="text-xl text-gray-700 leading-relaxed">
                {{ system.description || system.short_description }}
              </p>
            </div>

            <!-- Pricing -->
            <div class="bg-white rounded-xl p-6 border shadow-sm">
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="flex items-center space-x-3">
                      <span class="text-4xl font-bold text-gray-900">${{ system.price.toLocaleString() }}</span>
                      <span v-if="system.original_price && system.original_price > system.price"
                            class="text-xl text-gray-500 line-through">
                        ${{ system.original_price.toLocaleString() }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Quantity Selector -->
                <div class="flex items-center space-x-4">
                  <span class="text-gray-700 font-medium">Quantity:</span>
                  <div class="flex items-center border border-gray-300 rounded-lg">
                    <button @click="decrementQuantity"
                            :disabled="quantity <= 1"
                            class="p-2 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                      <Minus class="w-4 h-4" />
                    </button>
                    <span class="px-4 py-2 font-medium">{{ quantity }}</span>
                    <button @click="incrementQuantity" class="p-2 hover:bg-gray-100">
                      <Plus class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <!-- Total Price -->
                <div v-if="quantity > 1" class="border-t pt-4">
                  <div class="flex justify-between items-center">
                    <span class="text-lg font-medium text-gray-700">Total:</span>
                    <span class="text-2xl font-bold text-gray-900">${{ totalPrice.toLocaleString() }}</span>
                  </div>
                </div>

                <!-- Add to Cart Button -->
                <button @click="addToCart"
                        class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white py-4 px-6 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
                  <ShoppingCart class="w-5 h-5 mr-2 inline" />
                  Add to Cart
                </button>

                <div class="grid grid-cols-3 gap-4 pt-4 border-t">
                  <div class="text-center">
                    <Leaf class="w-6 h-6 text-green-500 mx-auto mb-1" />
                    <span class="text-xs text-gray-600">Eco-Friendly</span>
                  </div>
                  <div class="text-center">
                    <Shield class="w-6 h-6 text-blue-500 mx-auto mb-1" />
                    <span class="text-xs text-gray-600">25yr Warranty</span>
                  </div>
                  <div class="text-center">
                    <CheckCircle class="w-6 h-6 text-green-500 mx-auto mb-1" />
                    <span class="text-xs text-gray-600">Certified</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Tabs -->
        <div class="mt-16">
          <!-- Tab Navigation -->
          <div class="border-b border-gray-200">
            <nav class="flex space-x-8">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="selectedTab = tab.id"
                :class="[
                  'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                  selectedTab === tab.id
                    ? 'border-orange-500 text-orange-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                {{ tab.label }}
              </button>
            </nav>
          </div>

          <!-- Tab Content -->
          <div class="py-8">
            <!-- Overview Tab -->
            <div v-if="selectedTab === 'overview'" class="space-y-8">
              <div class="prose max-w-none">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">About This System</h3>
                <p class="text-gray-700 leading-relaxed text-lg">
                  {{ system.description || system.short_description }}
                </p>
              </div>

              <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">How It Works</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                  <div v-for="(option, index) in powerOptions" :key="index"
                       class="bg-white rounded-xl p-6 border shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-xl flex items-center justify-center mb-4">
                      <component :is="option.icon" class="w-6 h-6 text-white" />
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">{{ option.title }}</h4>
                    <p class="text-gray-600 text-sm">{{ option.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Features Tab -->
            <div v-if="selectedTab === 'features'">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">Key Features</h3>
              <div v-if="features && features.length" class="grid md:grid-cols-2 gap-6">
                <div v-for="feature in features" :key="feature.id"
                     class="bg-white rounded-xl p-6 border shadow-sm hover:shadow-md transition-shadow">
                  <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
                      <CheckCircle class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900 mb-2">{{ feature.feature_name }}</h4>
                      <p class="text-gray-600">{{ feature.feature_value }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Info class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">No specific features listed for this system.</p>
              </div>
            </div>

            <!-- Specifications Tab -->
            <div v-if="selectedTab === 'specifications'">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">Technical Specifications</h3>
              <div v-if="specifications && specifications.length" class="bg-white rounded-xl border overflow-hidden">
                <div v-for="(spec, index) in specifications" :key="spec.id"
                     :class="['px-6 py-4 flex justify-between items-center', index % 2 === 0 ? 'bg-gray-50' : 'bg-white']">
                  <span class="font-medium text-gray-900">{{ spec.spec_name }}</span>
                  <span class="text-gray-700">{{ spec.spec_value }}</span>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Info class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">No specifications available for this system.</p>
              </div>
            </div>

            <!-- What's Included Tab -->
            <div v-if="selectedTab === 'included'">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">What's Included</h3>
              <div v-if="products && products.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="product in products" :key="product.id"
                     class="bg-white rounded-xl p-6 border shadow-sm hover:shadow-md transition-shadow">
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                      <CheckCircle class="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ product.product_name }}</h4>
                      <p class="text-gray-600 text-sm">Qty: {{ product.quantity }}</p>
                      <p v-if="product.product_description" class="text-gray-600 text-sm mt-1">{{ product.product_description }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-12">
                <Info class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-500">No products listed for this system.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>