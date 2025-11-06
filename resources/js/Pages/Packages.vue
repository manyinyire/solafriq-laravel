<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  ArrowRight,
  Zap,
  Star,
  Shield,
  Leaf,
  Award,
  Package
} from 'lucide-vue-next';

const props = defineProps({
  solarSystems: Array,
});

const loading = ref(false);
const activeFilter = ref("all");

const filters = [
  { id: "all", label: "All Packages", icon: Package },
  { id: "popular", label: "Most Popular", icon: Star },
];

const filteredSystems = computed(() => {
  if (!props.solarSystems) return [];
  return props.solarSystems.filter((system) => {
    if (activeFilter.value === "all") return true;
    if (activeFilter.value === "popular") return system.is_popular;
    return true;
  });
});
</script>

<template>
  <MainLayout>
    <Head title="Solar Packages" />

    <!-- Header Section -->
    <section class="relative py-16 px-4 bg-gradient-to-r from-orange-600 to-yellow-500">
      <div class="container mx-auto max-w-7xl">
        <div class="text-center text-white">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
            <Package class="h-8 w-8" />
          </div>
          <h1 class="text-4xl md:text-5xl font-bold mb-4">Solar Packages</h1>
          <p class="text-xl max-w-2xl mx-auto">
            Pre-configured solar solutions designed to meet your energy needs
          </p>
        </div>
      </div>
    </section>

    <!-- Packages Section -->
    <section class="py-24 bg-gradient-to-br from-gray-50 via-white to-blue-50 relative overflow-hidden">
      <div class="absolute inset-0 opacity-5">
        <div class="absolute top-20 left-10 w-72 h-72 bg-orange-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500 rounded-full blur-3xl"></div>
      </div>

      <div class="container mx-auto px-4 relative">
        <div class="text-center mb-16">
          <div class="inline-block bg-gradient-to-r from-orange-500 to-yellow-500 text-white px-6 py-2 text-sm font-medium rounded-full mb-6">
            <Award class="w-4 h-4 mr-2 inline-block" />
            Premium Solar Solutions
          </div>
          <h2 class="text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
            Power Your Future with
            <span class="block bg-gradient-to-r from-orange-500 via-yellow-500 to-green-500 bg-clip-text text-transparent">
              Clean Energy
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
            Discover our comprehensive range of solar solutions designed to meet every energy need. From residential
            homes to commercial enterprises, we have the perfect system for you.
          </p>
        </div>

        <div class="flex justify-center mb-12">
          <div class="bg-white rounded-2xl p-2 shadow-lg border border-gray-200">
            <div class="flex space-x-2">
              <button 
                v-for="filter in filters" 
                :key="filter.id" 
                @click="activeFilter = filter.id" 
                :class="[
                  'flex items-center space-x-2 px-6 py-3 rounded-xl font-medium transition-all duration-300', 
                  activeFilter === filter.id 
                    ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white shadow-lg' 
                    : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'
                ]"
              >
                <component :is="filter.icon" class="w-4 h-4" />
                <span>{{ filter.label }}</span>
              </button>
            </div>
          </div>
        </div>

        <div v-if="loading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="i in 3" :key="i" class="animate-pulse border-0 shadow-lg rounded-2xl overflow-hidden">
            <div class="h-64 bg-gray-200"></div>
            <div class="p-6 space-y-4">
              <div class="h-6 bg-gray-200 rounded"></div>
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-8 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>

        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
          <div 
            v-for="(system, index) in filteredSystems" 
            :key="system?.id || index" 
            class="group hover:shadow-2xl transition-all duration-500 border-0 bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden transform hover:-translate-y-2"
          >
            <div class="p-0">
              <div class="relative overflow-hidden">
                <div 
                  class="h-64 bg-gradient-to-br relative" 
                  :style="{ background: system?.gradient_colors || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
                >
                  <div class="absolute inset-0 bg-black/10"></div>

                  <div class="absolute top-4 left-4 flex flex-col space-y-2">
                    <span 
                      v-if="system?.is_popular" 
                      class="inline-block bg-yellow-500 text-yellow-900 border-yellow-400 shadow-lg px-2 py-1 rounded-full text-xs font-bold"
                    >
                      <Star class="w-3 h-3 mr-1 inline-block" />
                      Most Popular
                    </span>
                    <span 
                      v-if="system?.original_price && system.original_price > system.price" 
                      class="inline-block bg-red-500 text-white shadow-lg px-2 py-1 rounded-full text-xs font-bold"
                    >
                      Save ${{ (system.original_price - system.price).toLocaleString() }}
                    </span>
                  </div>

                  <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                    <h3 class="text-2xl font-bold text-white mb-2">{{ system?.name || 'Solar System' }}</h3>
                    <div class="flex items-center space-x-4 text-white/90">
                      <div class="flex items-center space-x-1">
                        <Zap class="w-4 h-4" />
                        <span class="text-sm">{{ system?.capacity || 0 }}kW</span>
                      </div>
                      <div class="flex items-center space-x-1">
                        <Shield class="w-4 h-4" />
                        <span class="text-sm">25yr Warranty</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-6 space-y-4">
                <p class="text-gray-600 line-clamp-2 leading-relaxed">
                  {{ system?.short_description || system?.description || 'High-quality solar system' }}
                </p>

                <div class="flex flex-wrap gap-2">
                  <span class="inline-block text-green-600 border-green-200 bg-green-50 px-2 py-1 rounded-full text-xs font-bold">
                    <Leaf class="w-3 h-3 mr-1 inline-block" />
                    Eco-Friendly
                  </span>
                  <span class="inline-block text-blue-600 border-blue-200 bg-blue-50 px-2 py-1 rounded-full text-xs font-bold">
                    <Shield class="w-3 h-3 mr-1 inline-block" />
                    Reliable
                  </span>
                </div>

                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                      <span class="text-3xl font-bold text-gray-900">
                        ${{ system?.price ? parseFloat(system.price).toLocaleString() : '0' }}
                      </span>
                      <span 
                        v-if="system?.original_price && system.original_price > system.price" 
                        class="text-lg text-gray-500 line-through"
                      >
                        ${{ parseFloat(system.original_price).toLocaleString() }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex space-x-3 pt-2">
                  <Link :href="`/systems/${system?.id}`" class="flex-1">
                    <button class="w-full group-hover:border-orange-500 group-hover:text-orange-600 transition-all duration-300 border-2 border-gray-200 px-4 py-2 rounded-full font-semibold">
                      Learn More
                      <ArrowRight class="w-4 h-4 ml-2 inline-block group-hover:translate-x-1 transition-transform" />
                    </button>
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="filteredSystems.length === 0 && !loading" class="text-center py-16">
          <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <Leaf class="w-12 h-12 text-gray-400" />
          </div>
          <h3 class="text-2xl font-semibold text-gray-600 mb-3">No Packages Found</h3>
          <p class="text-gray-500 mb-6">Try adjusting your filter or check back later for new solutions.</p>
          <button 
            @click="activeFilter = 'all'" 
            class="border-2 border-gray-200 px-4 py-2 rounded-full font-semibold"
          >
            View All Packages
          </button>
        </div>
      </div>
    </section>
  </MainLayout>
</template>

