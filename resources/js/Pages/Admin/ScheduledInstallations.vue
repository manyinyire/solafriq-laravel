<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Package, User, MapPin, Clock } from 'lucide-vue-next';
import axios from 'axios';

const installations = ref([]);
const loading = ref(true);
const error = ref(null);
const fromDate = ref('');
const toDate = ref('');

const fetchInstallations = async () => {
  loading.value = true;
  try {
    const params = {};
    if (fromDate.value) params.from_date = fromDate.value;
    if (toDate.value) params.to_date = toDate.value;

    const response = await axios.get('/admin/installations-data', { params });
    installations.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to load scheduled installations.';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchInstallations);

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatShortDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const isToday = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();
  return date.toDateString() === today.toDateString();
};

const isUpcoming = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();
  const diffDays = Math.ceil((date - today) / (1000 * 60 * 60 * 24));
  return diffDays > 0 && diffDays <= 7;
};

const filterInstallations = () => {
  fetchInstallations();
};

const clearFilters = () => {
  fromDate.value = '';
  toDate.value = '';
  fetchInstallations();
};
</script>

<template>
  <Head title="Scheduled Installations" />
  <AdminLayout>
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
          <Calendar class="w-8 h-8 mr-3 text-orange-500" />
          Scheduled Installations
        </h1>
        <p class="text-gray-600 mt-2">Manage and track upcoming solar system installations</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Filter by Date Range</h2>
        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input 
              type="date" 
              v-model="fromDate" 
              class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
            <input 
              type="date" 
              v-model="toDate" 
              class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
            >
          </div>
          <div class="flex items-end space-x-2">
            <button 
              @click="filterInstallations" 
              class="flex-1 bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-600"
            >
              Apply Filter
            </button>
            <button 
              @click="clearFilters" 
              class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300"
            >
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-16">
        <p class="text-gray-600">Loading scheduled installations...</p>
      </div>

      <!-- Error State -->
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">{{ error }}</span>
      </div>

      <!-- Installations List -->
      <div v-if="!loading && !error">
        <div v-if="installations.length === 0" class="bg-white rounded-lg shadow-md p-12 text-center">
          <Calendar class="w-16 h-16 mx-auto text-gray-400 mb-4" />
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No Scheduled Installations</h3>
          <p class="text-gray-500">There are no installations scheduled at this time.</p>
        </div>

        <div v-else class="grid gap-6">
          <div 
            v-for="installation in installations" 
            :key="installation.id"
            class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
          >
            <div class="p-6">
              <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                <div class="flex-1">
                  <div class="flex items-center mb-2">
                    <Link 
                      :href="`/admin/orders/${installation.id}`"
                      class="text-xl font-bold text-gray-900 hover:text-orange-500"
                    >
                      Order #{{ installation.id }}
                    </Link>
                    <span 
                      v-if="isToday(installation.installation_date)"
                      class="ml-3 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
                    >
                      TODAY
                    </span>
                    <span 
                      v-else-if="isUpcoming(installation.installation_date)"
                      class="ml-3 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800"
                    >
                      UPCOMING
                    </span>
                  </div>
                  <div class="flex items-center text-gray-600">
                    <Clock class="w-4 h-4 mr-2" />
                    <span class="font-medium">{{ formatDate(installation.installation_date) }}</span>
                  </div>
                </div>
                <div class="mt-4 md:mt-0">
                  <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ installation.status }}
                  </span>
                </div>
              </div>

              <div class="grid md:grid-cols-2 gap-6 mb-4">
                <!-- Customer Info -->
                <div class="flex items-start">
                  <User class="w-5 h-5 text-gray-400 mr-3 mt-1" />
                  <div>
                    <p class="text-sm text-gray-500">Customer</p>
                    <p class="font-semibold text-gray-900">{{ installation.customer_name }}</p>
                    <p class="text-sm text-gray-600">{{ installation.customer_email }}</p>
                    <p class="text-sm text-gray-600">{{ installation.customer_phone }}</p>
                  </div>
                </div>

                <!-- Address -->
                <div class="flex items-start">
                  <MapPin class="w-5 h-5 text-gray-400 mr-3 mt-1" />
                  <div>
                    <p class="text-sm text-gray-500">Installation Address</p>
                    <p class="font-semibold text-gray-900">{{ installation.customer_address || 'Not provided' }}</p>
                  </div>
                </div>
              </div>

              <!-- Order Items -->
              <div class="border-t pt-4">
                <div class="flex items-center mb-3">
                  <Package class="w-5 h-5 text-gray-400 mr-2" />
                  <p class="text-sm font-medium text-gray-700">Items to Install</p>
                </div>
                <div class="space-y-2">
                  <div 
                    v-for="item in installation.items" 
                    :key="item.id"
                    class="flex justify-between items-center bg-gray-50 p-3 rounded-lg"
                  >
                    <div>
                      <p class="font-medium text-gray-900">{{ item.name }}</p>
                      <p class="text-sm text-gray-600">{{ item.description }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm text-gray-600">Qty: {{ item.quantity }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="border-t pt-4 mt-4 flex space-x-3">
                <Link 
                  :href="`/admin/orders/${installation.id}`"
                  class="flex-1 bg-orange-500 text-white py-2 px-4 rounded-lg hover:bg-orange-600 text-center"
                >
                  View Details
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
