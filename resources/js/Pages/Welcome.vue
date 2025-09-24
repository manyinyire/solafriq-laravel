<script setup>
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  ArrowRight,
  Play,
  Sparkles,
  Zap,
  Globe,
  Lightbulb,
  Monitor,
  Home,
  Building2,
  Star,
  Shield,
  Leaf,
  Award,
  Users,
  TrendingUp,
  Sun,
  Battery,
  CheckCircle,
  AlertCircle,
  ShoppingCart,
  Quote,
  Facebook,
  Twitter,
  Instagram,
  Linkedin,
  Mail,
  Phone,
  MapPin
} from 'lucide-vue-next';

// Props from Laravel
const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  solarSystems: Array
});

// Modern Solutions Section Data
const loading = ref(false);
const activeFilter = ref("all");

const filters = [
    { id: "all", label: "All Systems", icon: Zap },
    { id: "residential", label: "Residential", icon: Users },
    { id: "commercial", label: "Commercial", icon: TrendingUp },
    { id: "popular", label: "Most Popular", icon: Star },
  ];

const filteredSystems = computed(() => {
    if (!props.solarSystems) return [];
    return props.solarSystems.filter((system) => {
        if (activeFilter.value === "all") return true;
        if (activeFilter.value === "popular") return system.isPopular;
        if (activeFilter.value === "residential") return system.capacity <= 5;
        if (activeFilter.value === "commercial") return system.capacity > 5;
        return true;
    });
});

// Solar System Builder Data
const config = ref({
    panels: 4,
    panelWattage: 450,
    batteries: 2,
    batteryVoltage: 24,
    batteryCapacity: 200,
    inverterSize: 3,
  });

const analysis = computed(() => {
    const totalSolarPower = config.value.panels * config.value.panelWattage;
    const totalStorage = (config.value.batteries * config.value.batteryCapacity * config.value.batteryVoltage) / 1000;
    const dailyOutput = (totalSolarPower * 5) / 1000; // Assuming 5 hours of peak sun

    const panelCost = config.value.panels * (config.value.panelWattage * 0.8);
    const batteryCost = config.value.batteries * (config.value.batteryCapacity * config.value.batteryVoltage * 0.5);
    const inverterCost = config.value.inverterSize * 200;
    const installationCost = 1500;
    const estimatedPrice = panelCost + batteryCost + inverterCost + installationCost;

    const backupHours = (totalStorage * 1000) / (totalSolarPower * 0.7);
    const backupTime =
      backupHours < 12
        ? "8-12 hours"
        : backupHours < 24
          ? "12-24 hours"
          : backupHours < 48
            ? "24-36 hours"
            : "36+ hours";

    const canPower = [];
    if (totalSolarPower >= 1000) canPower.push("Lights, TV, Fans, Computer, Refrigerator");
    if (totalSolarPower >= 1500) canPower.push("Small Air Conditioner, Water Pump");
    if (totalSolarPower >= 2500) canPower.push("Multiple Air Conditioners, Heavy Appliances");

    const recommendations = [];
    if (config.value.inverterSize * 1000 > totalSolarPower * 1.2) {
      recommendations.push("Consider adding more solar panels to maximize inverter utilization");
    }
    if (totalStorage < dailyOutput) {
      recommendations.push("Increase battery capacity for better energy storage");
    }
    if (config.value.panels < 6 && totalSolarPower < 2000) {
      recommendations.push("Add more panels for increased energy generation");
    }

    const viability =
      totalSolarPower >= 1000 && totalStorage >= 5
        ? "ready"
        : totalSolarPower >= 500
          ? "needs-adjustment"
          : "insufficient";

    return {
      totalSolarPower,
      totalStorage,
      dailyOutput,
      estimatedPrice,
      backupTime,
      canPower,
      recommendations,
      viability,
    };
});

// Testimonials Section Data
const testimonials = [
    {
      name: "Kwame Asante",
      location: "Accra, Ghana",
      role: "Cruise Ship Engineer",
      content:
        "The installment plan was a game-changer for my family. Working on cruise ships means irregular income, but SolaFriq made it possible for us to have reliable power at home.",
      rating: 5,
      image: "/placeholder.svg?height=80&width=80&query=professional african man smiling",
    },
    {
      name: "Amina Hassan",
      location: "Lagos, Nigeria",
      role: "Restaurant Owner",
      content:
        "Our 3KVA system powers the entire restaurant efficiently. The installation was seamless and the ongoing support has been exceptional. Highly recommend!",
      rating: 5,
      image: "/placeholder.svg?height=80&width=80&query=professional african woman entrepreneur",
    },
    {
      name: "David Mwangi",
      location: "Nairobi, Kenya",
      role: "Tech Consultant",
      content:
        "The custom builder tool is incredible! It helped me design the perfect system for my home office with real-time compatibility checks. Brilliant technology.",
      rating: 5,
      image: "/placeholder.svg?height=80&width=80&query=african tech professional working",
    },
  ];

</script>

<template>
  <MainLayout>
    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-gray-50 via-white to-orange-50">
      <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-72 h-72 bg-gradient-to-br from-orange-400/20 to-yellow-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-br from-blue-900/10 to-blue-800/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-br from-orange-500/5 to-yellow-500/5 rounded-full blur-3xl"></div>
      </div>

      <div class="container mx-auto px-4 pt-20 relative z-10">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <div class="space-y-8">
            <div class="inline-flex items-center px-4 py-2 bg-orange-100 rounded-full text-orange-700 text-sm font-medium">
              <Sparkles class="w-4 h-4 mr-2" />
              Powering Africa's Future
            </div>

            <div class="space-y-6">
              <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 leading-tight">
                Solar Energy
                <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
                  Made Simple
                </span>
              </h1>
              <p class="text-xl text-gray-600 leading-relaxed max-w-lg">
                Revolutionary solar e-commerce platform with custom packages, real-time recommendations, and flexible
                payment plans for diaspora clients.
              </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/products" class="inline-flex items-center bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-8 py-4 rounded-full font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                    Explore Packages
                    <ArrowRight class="ml-2 h-5 w-5" />
                </a>
                <a href="/contact" class="inline-flex items-center border-2 border-gray-200 hover:border-orange-500 px-8 py-4 rounded-full font-semibold transition-all duration-300">
                    <Play class="mr-2 h-5 w-5" />
                    Book a Consultation
                </a>
            </div>

            <div class="flex items-center space-x-8 pt-8">
            </div>
          </div>

          <div class="relative">
            <div class="relative z-10">
              <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100">
                <div class="grid grid-cols-2 gap-6">
                  <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold">5,000+</div>
                    <div class="text-orange-100">Systems Installed</div>
                  </div>
                  <div class="bg-gradient-to-br from-blue-900 to-blue-800 rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold">98%</div>
                    <div class="text-blue-100">Satisfaction Rate</div>
                  </div>
                  <div class="bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold">24/7</div>
                    <div class="text-yellow-100">Expert Support</div>
                  </div>
                  <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white">
                    <div class="text-3xl font-bold">50+</div>
                    <div class="text-green-100">Countries</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-br from-orange-400 to-yellow-400 rounded-full opacity-20 animate-bounce"></div>
            <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full opacity-20 animate-pulse"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modern Solutions Section -->
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
              <button v-for="filter in filters" :key="filter.id" @click="activeFilter = filter.id" :class="['flex items-center space-x-2 px-6 py-3 rounded-xl font-medium transition-all duration-300', activeFilter === filter.id ? 'bg-gradient-to-r from-orange-500 to-yellow-500 text-white shadow-lg' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
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
          <div v-for="(system, index) in filteredSystems" :key="system.id" class="group hover:shadow-2xl transition-all duration-500 border-0 bg-white/80 backdrop-blur-sm rounded-2xl overflow-hidden transform hover:-translate-y-2" :style="{ animationDelay: `${index * 100}ms` }">
              <div class="p-0">
                <div class="relative overflow-hidden">
                  <div class="h-64 bg-gradient-to-br relative" :style="{ background: system.gradient_colors || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }">
                    <div class="absolute inset-0 bg-black/10"></div>

                    <div class="absolute top-4 left-4 flex flex-col space-y-2">
                      <span v-if="system.isPopular" class="inline-block bg-yellow-500 text-yellow-900 border-yellow-400 shadow-lg px-2 py-1 rounded-full text-xs font-bold">
                        <Star class="w-3 h-3 mr-1 inline-block" />
                        Most Popular
                      </span>
                      <span v-if="system.originalPrice && system.originalPrice > system.price" class="inline-block bg-red-500 text-white shadow-lg px-2 py-1 rounded-full text-xs font-bold">
                        Save ${{ (system.originalPrice - system.price).toLocaleString() }}
                      </span>
                    </div>

                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                      <h3 class="text-2xl font-bold text-white mb-2">{{ system.name }}</h3>
                      <div class="flex items-center space-x-4 text-white/90">
                        <div class="flex items-center space-x-1">
                          <Zap class="w-4 h-4" />
                          <span class="text-sm">{{ system.capacity }}kW</span>
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
                  <p class="text-gray-600 line-clamp-2 leading-relaxed">{{ system.shortDescription }}</p>

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
                        <span class="text-3xl font-bold text-gray-900">${{ system.price.toLocaleString() }}</span>
                        <span v-if="system.originalPrice && system.originalPrice > system.price" class="text-lg text-gray-500 line-through">
                          ${{ system.originalPrice.toLocaleString() }}
                        </span>
                      </div>
                    </div>
                    <p v-if="system.installmentPrice" class="text-sm text-gray-600">
                      or <span class="font-semibold text-orange-600">${{ system.installmentPrice }}/month</span> for {{ system.installmentMonths }} months
                    </p>
                  </div>

                  <div class="flex space-x-3 pt-2">
                    <a :href="`/systems/${system.id}`" class="flex-1">
                      <button class="w-full group-hover:border-orange-500 group-hover:text-orange-600 transition-all duration-300 border-2 border-gray-200 px-4 py-2 rounded-full font-semibold">
                        Learn More
                        <ArrowRight class="w-4 h-4 ml-2 inline-block group-hover:translate-x-1 transition-transform" />
                      </button>
                    </a>
                    <!-- AddToCartButton component would be here -->
                  </div>
                </div>
              </div>
          </div>
        </div>

        <div v-if="filteredSystems.length === 0 && !loading" class="text-center py-16">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <Leaf class="w-12 h-12 text-gray-400" />
            </div>
            <h3 class="text-2xl font-semibold text-gray-600 mb-3">No Systems Found</h3>
            <p class="text-gray-500 mb-6">Try adjusting your filter or check back later for new solutions.</p>
            <button @click="activeFilter = 'all'" class="border-2 border-gray-200 px-4 py-2 rounded-full font-semibold">View All Systems</button>
        </div>

        <div class="text-center">
          <div class="bg-gradient-to-r from-orange-500 to-yellow-500 rounded-3xl p-8 text-white shadow-2xl">
            <h3 class="text-3xl font-bold mb-4">Ready to Go Solar?</h3>
            <p class="text-xl mb-6 opacity-90">
              Join thousands of satisfied customers who have made the switch to clean energy
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <a href="/products" class="inline-flex items-center bg-white text-orange-600 hover:bg-gray-100 shadow-lg px-6 py-3 rounded-full font-semibold">
                <Zap class="w-5 h-5 mr-2" />
                View All Solutions
              </a>
              <a href="/contact" class="inline-flex items-center border-white text-white hover:bg-white hover:text-orange-600 border-2 px-6 py-3 rounded-full font-semibold">
                Get Free Consultation
                <ArrowRight class="w-5 h-5 ml-2" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Solar System Builder -->
    <section class="py-20 bg-gradient-to-br from-blue-50 via-white to-green-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-12">
          <span class="inline-block mb-4 bg-gradient-to-r from-orange-500 to-yellow-500 text-white px-4 py-2 rounded-full text-sm font-bold">Interactive Builder</span>
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Build Your Perfect Solar System</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Customize your solar solution with our intelligent system builder. Get real-time analysis and pricing as you
            design.
          </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8 max-w-7xl mx-auto">
          <div class="space-y-6">
            <div class="border-0 shadow-xl rounded-2xl overflow-hidden">
              <div class="bg-gradient-to-r from-orange-500 to-yellow-500 text-white p-4">
                <h3 class="flex items-center space-x-2 font-bold">
                  <Sun class="h-6 w-6" />
                  <span>Solar Panel Configuration</span>
                </h3>
              </div>
              <div class="p-6 space-y-6">
                <div>
                  <div class="flex justify-between items-center mb-4">
                    <label class="text-sm font-medium text-gray-700">Number of Panels: {{ config.panels }}</label>
                    <span class="inline-block text-orange-600 border-orange-200 px-2 py-1 rounded-full text-xs font-bold">
                      Total: {{ analysis.totalSolarPower }}W
                    </span>
                  </div>
                  <input type="range" v-model.number="config.panels" min="1" max="24" step="1" class="w-full" />
                  <div class="flex justify-between text-xs text-gray-500 mt-2">
                    <span>1</span>
                    <span>12</span>
                    <span>24</span>
                  </div>
                </div>

                <div>
                  <label class="text-sm font-medium text-gray-700 mb-2 block">Panel Wattage</label>
                  <select v-model.number="config.panelWattage" class="w-full border-gray-300 rounded-md shadow-sm">
                      <option value="300">300W</option>
                      <option value="400">400W</option>
                      <option value="450">450W</option>
                      <option value="500">500W</option>
                  </select>
                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                  <div class="flex items-center space-x-2 text-orange-800">
                    <Sun class="h-4 w-4" />
                    <span class="text-sm font-medium">
                      Recommended for: {{ analysis.totalSolarPower < 1500 ? "Small Home" : analysis.totalSolarPower < 3000 ? "Medium Home" : "Large Home/Business" }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="border-0 shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-blue-500 text-white p-4">
                    <h3 class="flex items-center space-x-2 font-bold">
                        <Battery class="h-6 w-6" />
                        <span>Battery Configuration</span>
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <label class="text-sm font-medium text-gray-700">Number of Batteries: {{ config.batteries }}</label>
                            <span class="inline-block text-green-600 border-green-200 px-2 py-1 rounded-full text-xs font-bold">
                                Total: {{ analysis.totalStorage.toFixed(1) }} kWh
                            </span>
                        </div>
                        <input type="range" v-model.number="config.batteries" min="1" max="16" step="1" class="w-full" />
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span>1</span>
                            <span>8</span>
                            <span>16</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-2 block">Battery Voltage</label>
                            <select v-model.number="config.batteryVoltage" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="12">12V</option>
                                <option value="24">24V</option>
                                <option value="48">48V</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-2 block">Capacity (Ah)</label>
                            <select v-model.number="config.batteryCapacity" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="100">100Ah</option>
                                <option value="150">150Ah</option>
                                <option value="200">200Ah</option>
                                <option value="250">250Ah</option>
                            </select>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="text-sm text-blue-800">
                            <strong>Estimated backup time:</strong> {{ analysis.backupTime }} (with moderate usage)
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-0 shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white p-4">
                    <h3 class="flex items-center space-x-2 font-bold">
                        <Zap class="h-6 w-6" />
                        <span>Inverter Selection</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-2 block">Inverter Size (kVA)</label>
                        <select v-model.number="config.inverterSize" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="1">1 kVA</option>
                            <option value="3">3 kVA</option>
                            <option value="5">5 kVA</option>
                            <option value="10">10 kVA</option>
                        </select>
                    </div>
                </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="border-0 shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 text-white p-4">
                    <h3 class="flex items-center space-x-2 font-bold">
                        <CheckCircle class="h-6 w-6" />
                        <span>System Analysis</span>
                    </h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-orange-50 rounded-xl">
                            <Sun class="h-8 w-8 text-orange-500 mx-auto mb-2" />
                            <div class="text-2xl font-bold text-gray-900">{{ analysis.totalSolarPower }}W</div>
                            <div class="text-sm text-gray-600">Solar Power</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-xl">
                            <Battery class="h-8 w-8 text-green-500 mx-auto mb-2" />
                            <div class="text-2xl font-bold text-gray-900">{{ analysis.totalStorage.toFixed(1) }} kWh</div>
                            <div class="text-sm text-gray-600">Storage</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-xl">
                            <Zap class="h-8 w-8 text-blue-500 mx-auto mb-2" />
                            <div class="text-2xl font-bold text-gray-900">{{ analysis.dailyOutput.toFixed(1) }} kWh</div>
                            <div class="text-sm text-gray-600">Daily Output</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">This System Can Power:</h3>
                        <div class="space-y-2">
                            <div v-for="(item, index) in analysis.canPower" :key="index" class="flex items-center space-x-2">
                                <CheckCircle class="h-4 w-4 text-green-500 flex-shrink-0" />
                                <span class="text-sm text-gray-700">{{ item }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="analysis.recommendations.length > 0">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <AlertCircle class="h-4 w-4 text-yellow-500 mr-2" />
                            Recommendations:
                        </h3>
                        <div class="space-y-2">
                            <div v-for="(rec, index) in analysis.recommendations" :key="index" class="text-sm text-gray-700 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                {{ rec }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-0 shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-blue-600 text-white p-4">
                    <h3 class="font-bold">Your Custom System</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900 mb-2">
                            ${{ analysis.estimatedPrice.toLocaleString() }}
                        </div>
                        <div class="text-sm text-gray-600">Estimated price including installation</div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Components:</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm">
                                        {{ config.panels }} Solar Panels ({{ config.panelWattage }}W each, {{ analysis.totalSolarPower }}W total)
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm">
                                        {{ config.batteries }} Batteries ({{ config.batteryCapacity }}Ah, {{ config.batteryVoltage }}V each)
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm">{{ config.inverterSize }}kVA Inverter</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm">Charge Controller, Mounting & Wiring</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm">Installation & Setup</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                        <span class="font-semibold text-green-800">System Viability:</span>
                        <span :class="['px-2 py-1 rounded-full text-xs font-bold text-white', analysis.viability === 'ready' ? 'bg-green-500' : analysis.viability === 'needs-adjustment' ? 'bg-yellow-500' : 'bg-red-500']">
                            {{ analysis.viability === 'ready' ? "Ready to Order" : analysis.viability === 'needs-adjustment' ? "Needs Adjustment" : "Insufficient Power" }}
                        </span>
                    </div>

                    <button :disabled="analysis.viability === 'insufficient'" class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300">
                        <ShoppingCart class="h-5 w-5 mr-2 inline-block" />
                        Add Custom System to Cart
                    </button>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-24 bg-gradient-to-br from-gray-50 to-blue-50">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-white rounded-full text-gray-700 text-sm font-medium shadow-sm">
            Customer Stories
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            Trusted by Thousands
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Across Africa
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Real stories from satisfied customers who've transformed their energy future with SolaFriq.
          </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
          <div v-for="(testimonial, index) in testimonials" :key="index" class="border-0 shadow-xl hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 rounded-2xl overflow-hidden bg-white">
            <div class="p-8">
              <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-1">
                  <Star v-for="i in testimonial.rating" :key="i" class="h-5 w-5 fill-yellow-400 text-yellow-400" />
                </div>
                <Quote class="h-8 w-8 text-orange-500 opacity-50" />
              </div>

              <p class="text-gray-700 leading-relaxed mb-8 text-lg italic">"{{ testimonial.content }}"</p>

              <div class="flex items-center space-x-4">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-orange-400 to-yellow-400 p-0.5">
                  <img :src="testimonial.image" :alt="testimonial.name" class="w-full h-full rounded-full object-cover bg-white" />
                </div>
                <div>
                  <div class="font-bold text-gray-900 text-lg">{{ testimonial.name }}</div>
                  <div class="text-orange-600 font-medium">{{ testimonial.role }}</div>
                  <div class="text-gray-600 text-sm">{{ testimonial.location }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>



  </MainLayout>
</template>