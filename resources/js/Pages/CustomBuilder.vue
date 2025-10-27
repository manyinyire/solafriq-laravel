<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Minus, ShoppingCart, Zap, Battery, Sun, Settings, Check, X } from 'lucide-vue-next';
import MainLayout from '@/Layouts/MainLayout.vue';
import axios from 'axios';

const products = ref({
  SOLAR_PANEL: [],
  INVERTER: [],
  BATTERY: [],
  CHARGE_CONTROLLER: [],
  MOUNTING: [],
  CABLES: [],
  ACCESSORIES: []
});

const selectedComponents = ref([]);
const systemName = ref('My Custom Solar System');
const loading = ref(true);
const activeCategory = ref('SOLAR_PANEL');
const showCart = ref(false);

const categories = [
  { key: 'SOLAR_PANEL', name: 'Solar Panels', icon: Sun, required: true },
  { key: 'INVERTER', name: 'Inverters', icon: Zap, required: true },
  { key: 'BATTERY', name: 'Batteries', icon: Battery, required: true },
  { key: 'CHARGE_CONTROLLER', name: 'Charge Controllers', icon: Settings, required: false },
  { key: 'MOUNTING', name: 'Mounting Hardware', icon: Settings, required: true },
  { key: 'CABLES', name: 'Cables & Wiring', icon: Settings, required: false },
  { key: 'ACCESSORIES', name: 'Accessories', icon: Settings, required: false },
];

// Fetch products from the database
const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/custom-builder/products');
    // Handle both new standardized response and old response format
    const data = response.data?.data || response.data;
    if (data && typeof data === 'object') {
      products.value = data;
    } else {
      console.error('Invalid products data format:', data);
      alert('Failed to load products. Please try again.');
    }
  } catch (error) {
    console.error('Failed to load products:', error);
    alert('Failed to load products. Please try again.');
  } finally {
    loading.value = false;
  }
};

// Apply query parameters from Welcome.vue quick builder
const applyQueryParameters = () => {
  const urlParams = new URLSearchParams(window.location.search);
  const panelId = urlParams.get('panel');
  const batteryId = urlParams.get('battery');
  const inverterId = urlParams.get('inverter');
  const panelQty = urlParams.get('panelQty');
  const batteryQty = urlParams.get('batteryQty');

  // Find and add products if they exist
  if (panelId) {
    const panel = products.value.SOLAR_PANEL?.find(p => p.id === parseInt(panelId));
    if (panel && panelQty) {
      const quantity = parseInt(panelQty);
      for (let i = 0; i < quantity; i++) {
        addComponent(panel);
      }
    }
  }

  if (batteryId) {
    const battery = products.value.BATTERY?.find(b => b.id === parseInt(batteryId));
    if (battery && batteryQty) {
      const quantity = parseInt(batteryQty);
      for (let i = 0; i < quantity; i++) {
        addComponent(battery);
      }
    }
  }

  if (inverterId) {
    const inverter = products.value.INVERTER?.find(i => i.id === parseInt(inverterId));
    if (inverter) {
      addComponent(inverter);
    }
  }
};

onMounted(async () => {
  await fetchProducts();
  applyQueryParameters();
});

// Add or update component
const addComponent = (product) => {
  const existingIndex = selectedComponents.value.findIndex(c => c.product_id === product.id);

  if (existingIndex >= 0) {
    // Check if adding one more exceeds stock
    const currentQuantity = selectedComponents.value[existingIndex].quantity;
    if (currentQuantity >= product.stock_quantity) {
      alert(`Cannot add more. Only ${product.stock_quantity} units available in stock.`);
      return;
    }
    selectedComponents.value[existingIndex].quantity += 1;
  } else {
    selectedComponents.value.push({
      product_id: product.id,
      name: product.full_name || product.name,
      brand: product.brand,
      model: product.model,
      category: product.category,
      price: parseFloat(product.price),
      quantity: 1,
      power_rating: product.power_rating,
      capacity: product.capacity,
      image_url: product.image_url,
      specifications: product.specifications,
      stock_quantity: product.stock_quantity,
    });
  }
};

// Remove component
const removeComponent = (productId) => {
  const index = selectedComponents.value.findIndex(c => c.product_id === productId);
  if (index >= 0) {
    selectedComponents.value.splice(index, 1);
  }
};

// Update quantity
const updateQuantity = (productId, change) => {
  const component = selectedComponents.value.find(c => c.product_id === productId);
  if (component) {
    const newQuantity = component.quantity + change;

    // Check stock availability
    if (newQuantity > component.stock_quantity) {
      alert(`Cannot add more. Only ${component.stock_quantity} units available in stock.`);
      return;
    }

    component.quantity = Math.max(1, newQuantity);
  }
};

// Calculate total price
const totalPrice = computed(() => {
  return selectedComponents.value.reduce((sum, component) => {
    return sum + (component.price * component.quantity);
  }, 0);
});

// Calculate total power
const totalPower = computed(() => {
  return selectedComponents.value
    .filter(c => c.power_rating)
    .reduce((sum, component) => {
      return sum + (component.power_rating * component.quantity);
    }, 0);
});

// Calculate total capacity
const totalCapacity = computed(() => {
  return selectedComponents.value
    .filter(c => c.capacity)
    .reduce((sum, component) => {
      return sum + (component.capacity * component.quantity);
    }, 0);
});

// Check if required components are selected
const hasRequiredComponents = computed(() => {
  const hasSolarPanel = selectedComponents.value.some(c => c.category === 'SOLAR_PANEL');
  const hasInverter = selectedComponents.value.some(c => c.category === 'INVERTER');
  const hasBattery = selectedComponents.value.some(c => c.category === 'BATTERY');
  const hasMounting = selectedComponents.value.some(c => c.category === 'MOUNTING');
  return hasSolarPanel && hasInverter && hasBattery && hasMounting;
});

// Get components by category
const getComponentsByCategory = (category) => {
  return selectedComponents.value.filter(c => c.category === category);
};

// Add to cart
const addToCart = async () => {
  if (!hasRequiredComponents.value) {
    alert('Please select required components: Solar Panel, Inverter, Battery, and Mounting Hardware.');
    return;
  }

  if (!systemName.value.trim()) {
    alert('Please enter a name for your custom system.');
    return;
  }

  try {
    const response = await axios.post('/custom-builder/add-to-cart', {
      system_name: systemName.value,
      components: selectedComponents.value.map(c => ({
        product_id: c.product_id,
        quantity: c.quantity
      }))
    });

    alert(response.data.message);
    
    // Redirect to cart
    router.visit('/cart');
  } catch (error) {
    console.error('Failed to add to cart:', error);
    alert('Failed to add to cart. Please try again.');
  }
};

// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};
</script>

<template>
  <Head title="Custom Solar System Builder" />

  <MainLayout>
    <div class="bg-white shadow-sm">
      <div class="container mx-auto px-4 py-6">
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900">Custom Solar System Builder</h1>
          <p class="text-gray-600 mt-2">Build your perfect solar system from our product catalog and request a custom quote</p>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Product Selection -->
        <div class="lg:col-span-2">
          <!-- Category Tabs -->
          <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b overflow-x-auto">
              <div class="flex">
                <button
                  v-for="category in categories"
                  :key="category.key"
                  @click="activeCategory = category.key"
                  :class="[
                    'flex items-center px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap',
                    activeCategory === category.key
                      ? 'border-orange-500 text-orange-600'
                      : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
                  ]"
                >
                  <component :is="category.icon" class="w-5 h-5 mr-2" />
                  {{ category.name }}
                  <span v-if="category.required" class="ml-2 text-xs text-red-500">*</span>
                  <span
                    v-if="getComponentsByCategory(category.key).length > 0"
                    class="ml-2 bg-orange-100 text-orange-800 text-xs font-semibold px-2 py-0.5 rounded-full"
                  >
                    {{ getComponentsByCategory(category.key).length }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Products Grid -->
            <div class="p-6">
              <div v-if="loading" class="text-center py-12">
                <p class="text-gray-600">Loading products...</p>
              </div>

              <div v-else-if="!products[activeCategory] || products[activeCategory].length === 0" class="text-center py-12">
                <p class="text-gray-600">No products available in this category.</p>
              </div>

              <div v-else class="grid md:grid-cols-2 gap-4">
                <div
                  v-for="product in products[activeCategory] || []"
                  :key="product.id"
                  class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <div class="flex items-start space-x-4">
                    <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0 flex items-center justify-center">
                      <img
                        v-if="product.image_url"
                        :src="product.image_url"
                        :alt="product.name"
                        class="w-full h-full object-cover rounded-lg"
                      />
                      <Sun v-else class="w-10 h-10 text-gray-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="font-semibold text-gray-900 truncate">{{ product.brand }} {{ product.model }}</h3>
                      <p class="text-sm text-gray-600 truncate">{{ product.name }}</p>
                      <div class="mt-2 flex items-center justify-between">
                        <span class="text-lg font-bold text-orange-600">{{ formatCurrency(product.price) }}</span>
                        <button
                          @click="addComponent(product)"
                          class="bg-orange-500 text-white px-3 py-1 rounded-lg hover:bg-orange-600 text-sm font-medium"
                        >
                          <Plus class="w-4 h-4 inline mr-1" />
                          Add
                        </button>
                      </div>
                      <div class="mt-2 flex items-center justify-between text-xs">
                        <div class="text-gray-500">
                          <span v-if="product.power_rating">{{ product.power_rating }}W</span>
                          <span v-if="product.capacity" class="ml-2">{{ product.capacity }}Ah</span>
                        </div>
                        <div :class="[
                          'font-semibold',
                          product.stock_quantity > 10 ? 'text-green-600' :
                          product.stock_quantity > 5 ? 'text-yellow-600' :
                          'text-red-600'
                        ]">
                          {{ product.stock_quantity }} in stock
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Selected Components & Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Your Custom System</h2>

            <!-- System Name -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
              <input
                v-model="systemName"
                type="text"
                class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
                placeholder="Enter system name"
              />
            </div>

            <!-- Selected Components -->
            <div class="mb-6 max-h-96 overflow-y-auto">
              <div v-if="selectedComponents.length === 0" class="text-center py-8 text-gray-500">
                <ShoppingCart class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                <p class="text-sm">No components selected</p>
                <p class="text-xs mt-1">Start building your system by adding components</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="component in selectedComponents"
                  :key="component.product_id"
                  class="border rounded-lg p-3"
                >
                  <div class="flex items-start justify-between mb-2">
                    <div class="flex-1 min-w-0">
                      <p class="font-medium text-sm text-gray-900 truncate">{{ component.name }}</p>
                      <p class="text-xs text-gray-500">{{ formatCurrency(component.price) }} each</p>
                    </div>
                    <button
                      @click="removeComponent(component.product_id)"
                      class="text-red-500 hover:text-red-700 ml-2"
                    >
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                      <button
                        @click="updateQuantity(component.product_id, -1)"
                        class="bg-gray-200 text-gray-700 w-6 h-6 rounded flex items-center justify-center hover:bg-gray-300"
                      >
                        <Minus class="w-3 h-3" />
                      </button>
                      <span class="text-sm font-medium w-8 text-center">{{ component.quantity }}</span>
                      <button
                        @click="updateQuantity(component.product_id, 1)"
                        class="bg-gray-200 text-gray-700 w-6 h-6 rounded flex items-center justify-center hover:bg-gray-300"
                      >
                        <Plus class="w-3 h-3" />
                      </button>
                    </div>
                    <span class="text-sm font-bold text-gray-900">
                      {{ formatCurrency(component.price * component.quantity) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- System Stats -->
            <div v-if="selectedComponents.length > 0" class="border-t pt-4 mb-4 space-y-2">
              <div v-if="totalPower > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Total Power:</span>
                <span class="font-semibold">{{ totalPower.toFixed(2) }}W</span>
              </div>
              <div v-if="totalCapacity > 0" class="flex justify-between text-sm">
                <span class="text-gray-600">Total Capacity:</span>
                <span class="font-semibold">{{ totalCapacity.toFixed(2) }}Ah</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Components:</span>
                <span class="font-semibold">{{ selectedComponents.length }}</span>
              </div>
            </div>

            <!-- Total Price -->
            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900">Total Price:</span>
                <span class="text-2xl font-bold text-orange-600">{{ formatCurrency(totalPrice) }}</span>
              </div>
            </div>

            <!-- Add to Cart Button -->
            <button
              @click="addToCart"
              :disabled="!hasRequiredComponents"
              :class="[
                'w-full py-3 px-4 rounded-lg font-semibold text-white transition-colors',
                hasRequiredComponents
                  ? 'bg-orange-500 hover:bg-orange-600'
                  : 'bg-gray-300 cursor-not-allowed'
              ]"
            >
              <ShoppingCart class="w-5 h-5 inline mr-2" />
              Request Quote for Custom System
            </button>

            <p v-if="!hasRequiredComponents" class="text-xs text-red-500 mt-2 text-center">
              * Solar Panel, Inverter, Battery, and Mounting are required
            </p>
            <p v-else class="text-xs text-gray-500 mt-2 text-center">
              Continue to cart to complete your quote request
            </p>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
