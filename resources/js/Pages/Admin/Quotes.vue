<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileText, Search, Filter, Eye, Send, Download, Trash2, Calendar, User, Mail, DollarSign } from 'lucide-vue-next';

const props = defineProps({
  quotes: Object,
  filters: Object,
});

const searchQuery = ref(props.filters.search);
const statusFilter = ref(props.filters.status);

const statusOptions = [
  { value: 'all', label: 'All Statuses', color: 'gray' },
  { value: 'pending', label: 'Pending', color: 'yellow' },
  { value: 'sent', label: 'Sent', color: 'blue' },
  { value: 'accepted', label: 'Accepted', color: 'green' },
  { value: 'rejected', label: 'Rejected', color: 'red' },
  { value: 'expired', label: 'Expired', color: 'gray' },
];

const getStatusColor = (status) => {
  const option = statusOptions.find(opt => opt.value === status);
  return option ? option.color : 'gray';
};

const applyFilters = () => {
  router.get('/admin/quotes', {
    status: statusFilter.value,
    search: searchQuery.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const viewQuote = (quoteId) => {
  router.visit(`/admin/quotes/${quoteId}`);
};

const deleteQuote = (quoteId) => {
  if (confirm('Are you sure you want to delete this quote?')) {
    router.delete(`/admin/quotes/${quoteId}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center">
          <FileText class="w-8 h-8 mr-3 text-orange-500" />
          Quote Requests
        </h1>
        <p class="text-gray-600 mt-2">Manage customer quote requests and send detailed quotes</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <div class="grid md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                v-model="searchQuery"
                @keyup.enter="applyFilters"
                type="text"
                placeholder="Search by quote number, customer name, or email..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
            >
              <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <!-- Apply Button -->
          <div class="flex items-end">
            <button
              @click="applyFilters"
              class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white px-6 py-2 rounded-lg font-semibold flex items-center justify-center"
            >
              <Filter class="w-4 h-4 mr-2" />
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Quotes Table -->
      <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quote #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Until</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="quote in quotes.data" :key="quote.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <FileText class="h-5 w-5 text-gray-400 mr-2" />
                    <span class="font-medium text-gray-900">{{ quote.quote_number }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-start">
                    <User class="h-4 w-4 text-gray-400 mr-2 mt-0.5" />
                    <div>
                      <div class="font-medium text-gray-900">{{ quote.customer_name }}</div>
                      <div class="text-sm text-gray-500 flex items-center">
                        <Mail class="h-3 w-3 mr-1" />
                        {{ quote.customer_email }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center text-sm text-gray-900">
                    <Calendar class="h-4 w-4 text-gray-400 mr-2" />
                    {{ new Date(quote.created_at).toLocaleDateString() }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center font-semibold text-gray-900">
                    <DollarSign class="h-4 w-4 text-green-600 mr-1" />
                    {{ parseFloat(quote.total).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                      getStatusColor(quote.status) === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '',
                      getStatusColor(quote.status) === 'blue' ? 'bg-blue-100 text-blue-800' : '',
                      getStatusColor(quote.status) === 'green' ? 'bg-green-100 text-green-800' : '',
                      getStatusColor(quote.status) === 'red' ? 'bg-red-100 text-red-800' : '',
                      getStatusColor(quote.status) === 'gray' ? 'bg-gray-100 text-gray-800' : '',
                    ]"
                  >
                    {{ quote.status.charAt(0).toUpperCase() + quote.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ quote.valid_until ? new Date(quote.valid_until).toLocaleDateString() : 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="viewQuote(quote.id)"
                      class="text-blue-600 hover:text-blue-900 p-2 hover:bg-blue-50 rounded-lg transition-colors"
                      title="View Details"
                    >
                      <Eye class="h-5 w-5" />
                    </button>
                    <a
                      :href="`/admin/quotes/${quote.id}/pdf`"
                      target="_blank"
                      class="text-green-600 hover:text-green-900 p-2 hover:bg-green-50 rounded-lg transition-colors"
                      title="Download PDF"
                    >
                      <Download class="h-5 w-5" />
                    </a>
                    <button
                      v-if="quote.status !== 'accepted'"
                      @click="deleteQuote(quote.id)"
                      class="text-red-600 hover:text-red-900 p-2 hover:bg-red-50 rounded-lg transition-colors"
                      title="Delete"
                    >
                      <Trash2 class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!quotes.data || quotes.data.length === 0" class="text-center py-12">
          <FileText class="h-16 w-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No quotes found</h3>
          <p class="text-gray-500">No quote requests match your current filters.</p>
        </div>

        <!-- Pagination -->
        <div v-if="quotes.data && quotes.data.length > 0" class="bg-gray-50 px-6 py-4 border-t">
          <div class="flex items-center justify-between">
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
    </div>
  </AdminLayout>
</template>
