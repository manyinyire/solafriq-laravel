<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ShoppingCart, Star, Package, Filter, Zap, Shield, Award, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
  category: String,
  products: Array,
});

const sortBy = ref('name');
const addingToCart = ref({});

const sortedProducts = computed(() => {
  if (!props.products) return [];
  
  let sorted = [...props.products];
  
  switch (sortBy.value) {
    case 'price-low':
      sorted.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
      break;
    case 'price-high':
      sorted.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
      break;
    case 'name':
    default:
      sorted.sort((a, b) => a.name.localeCompare(b.name));
      break;
  }
  
  return sorted;
});

const addToCart = (product) => {
  addingToCart.value[product.id] = true;
  
  router.post('/cart/add', {
    product_id: product.id,
    quantity: 1,
    type: 'product'
  }, {
    preserveScroll: true,
    onSuccess: () => {
      addingToCart.value[product.id] = false;
    },
    onError: () => {
      addingToCart.value[product.id] = false;
    }
  });
};

const getProductGradient = (index) => {
  const gradients = [
    'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
    'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
    'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
  ];
  return gradients[index % gradients.length];
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
      <!-- Header Section -->
      <section class="relative py-16 px-4 bg-gradient-to-r from-orange-600 to-yellow-500">
        <div class="container mx-auto max-w-7xl">
          <div class="text-center text-white">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
              <Package class="h-8 w-8" />
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4 capitalize">{{ category === 'all' ? 'All' : category }} Products</h1>
            <p class="text-xl max-w-2xl mx-auto">
              Browse our selection of high-quality {{ category === 'all' ? '' : category }} products
            </p>
          </div>
        </div>
      </section>

      <!-- Products Section -->
      <section class="py-16 px-4 bg-gradient-to-br from-gray-50 via-white to-orange-50">
        <div class="container mx-auto max-w-7xl">
          <!-- Header -->
          <div class="text-center mb-12">
            <div class="inline-block bg-gradient-to-r from-orange-500 to-yellow-500 text-white px-6 py-2 text-sm font-medium rounded-full mb-6">
              <Award class="w-4 h-4 mr-2 inline-block" />
              Premium {{ category === 'all' ? '' : category }} Products
            </div>
            <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
              Browse Our
              <span class="block bg-gradient-to-r from-orange-500 via-yellow-500 to-green-500 bg-clip-text text-transparent capitalize">
                {{ category === 'all' ? 'Complete' : category }} Collection
              </span>
            </h2>
          </div>

          <!-- Controls Bar -->
          <div class="flex justify-center mb-12">
            <div class="bg-white rounded-2xl p-2 shadow-lg border border-gray-200">
              <div class="flex items-center space-x-2">
                <Filter class="h-5 w-5 text-gray-500 ml-2" />
                <select v-model="sortBy" class="border-0 bg-transparent px-4 py-2 focus:ring-0 font-medium text-gray-700">
                  <option value="name">Sort by Name</option>
                  <option value="price-low">Price: Low to High</option>
                  <option value="price-high">Price: High to Low</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-if="sortedProducts.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div 
              v-for="(product, index) in sortedProducts" 
              :key="product.id" 
              class="group hover:shadow-2xl transition-all duration-500 border-0 bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden transform hover:-translate-y-2"
            >
              <div class="p-0">
                <div class="relative overflow-hidden">
                  <div class="h-64 bg-gradient-to-br relative" :style="{ background: getProductGradient(index) }">
                    <div class="absolute inset-0 bg-black/10"></div>

                    <!-- Product Image or Icon -->
                    <div class="absolute inset-0 flex items-center justify-center">
                      <img 
                        v-if="product.image_url" 
                        :src="product.image_url" 
                        :alt="product.name"
                        class="w-full h-full object-cover opacity-90"
                      />
                      <Package v-else class="h-24 w-24 text-white/80" />
                    </div>

                    <!-- Badges -->
                    <div class="absolute top-4 left-4 flex flex-col space-y-2">
                      <span v-if="product.stock_quantity > 0 && product.stock_quantity < 10" class="inline-block bg-yellow-500 text-yellow-900 shadow-lg px-2 py-1 rounded-full text-xs font-bold">
                        <Star class="w-3 h-3 mr-1 inline-block" />
                        Low Stock
                      </span>
                      <span v-if="!product.is_active || product.stock_quantity === 0" class="inline-block bg-red-500 text-white shadow-lg px-2 py-1 rounded-full text-xs font-bold">
                        Out of Stock
                      </span>
                    </div>

                    <!-- Product Info Overlay -->
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                      <h3 class="text-2xl font-bold text-white mb-2">{{ product.name }}</h3>
                      <div class="flex items-center space-x-4 text-white/90">
                        <div v-if="product.power_rating" class="flex items-center space-x-1">
                          <Zap class="w-4 h-4" />
                          <span class="text-sm">{{ product.power_rating }}W</span>
                        </div>
                        <div v-if="product.capacity" class="flex items-center space-x-1">
                          <Shield class="w-4 h-4" />
                          <span class="text-sm">{{ product.capacity }}Ah</span>
                        </div>
                        <div v-if="product.brand" class="flex items-center space-x-1">
                          <Award class="w-4 h-4" />
                          <span class="text-sm">{{ product.brand }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="p-6 space-y-4">
                  <p v-if="product.description" class="text-gray-600 line-clamp-2 leading-relaxed">{{ product.description }}</p>

                  <div class="flex flex-wrap gap-2">
                    <span class="inline-block text-green-600 border-green-200 bg-green-50 px-2 py-1 rounded-full text-xs font-bold">
                      <CheckCircle class="w-3 h-3 mr-1 inline-block" />
                      Quality Assured
                    </span>
                    <span v-if="product.stock_quantity > 10" class="inline-block text-blue-600 border-blue-200 bg-blue-50 px-2 py-1 rounded-full text-xs font-bold">
                      <Shield class="w-3 h-3 mr-1 inline-block" />
                      In Stock
                    </span>
                  </div>

                  <div class="space-y-2">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-2">
                        <span class="text-3xl font-bold text-gray-900">${{ parseFloat(product.price).toFixed(2) }}</span>
                      </div>
                    </div>
                    <p class="text-sm text-gray-600">
                      per <span class="font-semibold">{{ product.unit || 'unit' }}</span>
                    </p>
                  </div>

                  <div class="pt-2">
                    <button 
                      @click="addToCart(product)"
                      :disabled="!product.is_active || product.stock_quantity === 0 || addingToCart[product.id]"
                      class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 disabled:from-gray-300 disabled:to-gray-400 text-white py-3 rounded-full font-semibold transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl"
                    >
                      <ShoppingCart class="h-5 w-5" />
                      <span v-if="addingToCart[product.id]">Adding...</span>
                      <span v-else-if="!product.is_active || product.stock_quantity === 0">Out of Stock</span>
                      <span v-else>Add to Cart</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16">
            <Package class="h-24 w-24 text-gray-300 mx-auto mb-4" />
            <h3 class="text-2xl font-semibold text-gray-900 mb-2">No Products Found</h3>
            <p class="text-gray-600 mb-8">There are currently no products in this category.</p>
            <a href="/" class="inline-block bg-gradient-to-r from-orange-500 to-yellow-500 text-white px-8 py-3 rounded-full font-semibold hover:from-orange-600 hover:to-yellow-600 transition-all">
              Browse All Products
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>
