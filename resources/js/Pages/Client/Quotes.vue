<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { FileText, Eye, Calendar, DollarSign, Clock } from 'lucide-vue-next';

const props = defineProps({
  quotes: Object,
});

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    sent: 'bg-blue-100 text-blue-800 border-blue-200',
    accepted: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
    expired: 'bg-gray-100 text-gray-800 border-gray-200',
  };
  return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="container mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            <FileText class="w-8 h-8 mr-3 text-orange-500" />
            My Quotes
          </h1>
          <p class="text-gray-600 mt-2">View and manage your quote requests</p>
        </div>

        <!-- Quotes List -->
        <div v-if="quotes.data && quotes.data.length > 0" class="space-y-4">
          <div v-for="quote in quotes.data" :key="quote.id" class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow">
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ quote.quote_number }}</h3>
                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold border', getStatusColor(quote.status)]">
                      {{ quote.status.charAt(0).toUpperCase() + quote.status.slice(1) }}
                    </span>
                  </div>
                  
                  <div class="grid md:grid-cols-3 gap-4 mt-4">
                    <div class="flex items-center text-sm text-gray-600">
                      <Calendar class="w-4 h-4 mr-2 text-gray-400" />
                      <span>{{ new Date(quote.created_at).toLocaleDateString() }}</span>
                    </div>
                    
                    <div class="flex items-center text-sm text-gray-600">
                      <DollarSign class="w-4 h-4 mr-2 text-gray-400" />
                      <span class="font-semibold text-gray-900">${{ parseFloat(quote.total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                    
                    <div v-if="quote.valid_until" class="flex items-center text-sm text-gray-600">
                      <Clock class="w-4 h-4 mr-2 text-gray-400" />
                      <span>Valid until {{ new Date(quote.valid_until).toLocaleDateString() }}</span>
                    </div>
                  </div>
                  
                  <div v-if="quote.items && quote.items.length > 0" class="mt-3">
                    <p class="text-sm text-gray-600">{{ quote.items.length }} item{{ quote.items.length !== 1 ? 's' : '' }}</p>
                  </div>
                </div>
                
                <div class="ml-4">
                  <Link :href="`/quotes/${quote.id}`" class="inline-flex items-center bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium text-sm">
                    <Eye class="w-4 h-4 mr-2" />
                    View Details
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-xl shadow-sm border p-12 text-center">
          <FileText class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No quotes yet</h3>
          <p class="text-gray-600 mb-6">You haven't requested any quotes yet. Start shopping to create your first quote!</p>
          <Link href="/" class="inline-flex items-center bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white px-6 py-3 rounded-lg font-semibold">
            Browse Products
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="quotes.data && quotes.data.length > 0" class="mt-6 flex items-center justify-between bg-white rounded-lg border p-4">
          <div class="text-sm text-gray-700">
            Showing {{ quotes.from }} to {{ quotes.to }} of {{ quotes.total }} quotes
          </div>
          <div class="flex space-x-2">
            <a
              v-for="link in quotes.links"
              :key="link.label"
              :href="link.url"
              v-html="link.label"
              :class="[
                'px-3 py-2 text-sm rounded-lg',
                link.active
                  ? 'bg-orange-500 text-white font-semibold'
                  : link.url
                  ? 'bg-white text-gray-700 hover:bg-gray-100 border'
                  : 'bg-gray-100 text-gray-400 cursor-not-allowed'
              ]"
            ></a>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
