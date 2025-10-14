<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { FileText, Download, CheckCircle, XCircle, Calendar, User, Mail, Phone, MapPin, Package, DollarSign, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
  quote: Object,
});

const accepting = ref(false);
const rejecting = ref(false);
const showRejectModal = ref(false);
const rejectionReason = ref('');

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

const canAccept = () => {
  return props.quote.status === 'sent' && !isExpired();
};

const isExpired = () => {
  if (!props.quote.valid_until) return false;
  return new Date(props.quote.valid_until) < new Date();
};

const acceptQuote = () => {
  if (confirm('Are you sure you want to accept this quote? This will create an order and you will receive an invoice.')) {
    accepting.value = true;
    router.post(`/quotes/${props.quote.id}/accept`, {}, {
      preserveScroll: true,
      onFinish: () => {
        accepting.value = false;
      },
    });
  }
};

const rejectQuote = () => {
  rejecting.value = true;
  router.post(`/quotes/${props.quote.id}/reject`, {
    reason: rejectionReason.value,
  }, {
    preserveScroll: true,
    onFinish: () => {
      rejecting.value = false;
      showRejectModal.value = false;
      rejectionReason.value = '';
    },
  });
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="container mx-auto px-4 max-w-5xl">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <FileText class="w-8 h-8 mr-3 text-orange-500" />
                Quote {{ quote.quote_number }}
              </h1>
              <p class="text-gray-600 mt-2">Review your quote details</p>
            </div>
            <div class="flex items-center space-x-3">
              <span :class="['px-4 py-2 rounded-full text-sm font-semibold border', getStatusColor(quote.status)]">
                {{ quote.status.charAt(0).toUpperCase() + quote.status.slice(1) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Expiry Warning -->
        <div v-if="isExpired()" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-start">
          <AlertCircle class="w-5 h-5 text-red-600 mr-3 mt-0.5" />
          <div>
            <h3 class="font-semibold text-red-900">Quote Expired</h3>
            <p class="text-red-700 text-sm">This quote expired on {{ new Date(quote.valid_until).toLocaleDateString() }}. Please contact us for a new quote.</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div v-if="canAccept()" class="bg-gradient-to-r from-orange-500 to-yellow-500 rounded-xl p-6 mb-6 text-white">
          <h3 class="text-xl font-bold mb-2">Ready to proceed?</h3>
          <p class="mb-4 opacity-90">Accept this quote to create your order and receive an invoice.</p>
          <div class="flex items-center space-x-3">
            <button
              @click="acceptQuote"
              :disabled="accepting"
              class="flex items-center bg-white text-orange-600 hover:bg-gray-100 disabled:bg-gray-300 px-6 py-3 rounded-lg font-semibold shadow-lg"
            >
              <CheckCircle class="w-5 h-5 mr-2" />
              {{ accepting ? 'Processing...' : 'Accept Quote' }}
            </button>
            <button
              @click="showRejectModal = true"
              class="flex items-center bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-lg font-semibold"
            >
              <XCircle class="w-5 h-5 mr-2" />
              Decline
            </button>
          </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Your Information</h2>
              
              <div class="space-y-3">
                <div class="flex items-start">
                  <User class="w-5 h-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-sm text-gray-500">Name</div>
                    <div class="font-medium text-gray-900">{{ quote.customer_name }}</div>
                  </div>
                </div>
                <div class="flex items-start">
                  <Mail class="w-5 h-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="font-medium text-gray-900">{{ quote.customer_email }}</div>
                  </div>
                </div>
                <div v-if="quote.customer_phone" class="flex items-start">
                  <Phone class="w-5 h-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-sm text-gray-500">Phone</div>
                    <div class="font-medium text-gray-900">{{ quote.customer_phone }}</div>
                  </div>
                </div>
                <div v-if="quote.customer_address" class="flex items-start">
                  <MapPin class="w-5 h-5 text-gray-400 mr-3 mt-0.5" />
                  <div>
                    <div class="text-sm text-gray-500">Address</div>
                    <div class="font-medium text-gray-900">{{ quote.customer_address }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quote Items -->
            <div class="bg-white rounded-xl shadow-sm border p-6">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Quote Items</h2>
              
              <div class="space-y-4">
                <div v-for="item in quote.items" :key="item.id" class="border-b pb-4 last:border-b-0 last:pb-0">
                  <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Package class="w-6 h-6 text-white" />
                      </div>
                      <div>
                        <h3 class="font-semibold text-gray-900">{{ item.item_name }}</h3>
                        <p v-if="item.item_description" class="text-sm text-gray-600 mt-1">{{ item.item_description }}</p>
                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                          <span>Quantity: {{ item.quantity }}</span>
                          <span>•</span>
                          <span>Unit Price: ${{ parseFloat(item.unit_price).toFixed(2) }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="font-bold text-lg text-gray-900">${{ parseFloat(item.total_price).toFixed(2) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Terms & Conditions -->
            <div v-if="quote.terms_and_conditions" class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
              <h3 class="font-bold text-yellow-900 mb-3">Terms & Conditions</h3>
              <div class="text-yellow-800 text-sm whitespace-pre-line">{{ quote.terms_and_conditions }}</div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Quote Summary -->
            <div class="bg-white rounded-xl shadow-sm border p-6 sticky top-8">
              <h2 class="text-xl font-bold text-gray-900 mb-4">Quote Summary</h2>
              
              <div class="space-y-3 mb-6">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="font-medium">${{ parseFloat(quote.subtotal).toFixed(2) }}</span>
                </div>
                
                <div v-if="quote.tax > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Tax:</span>
                  <span class="font-medium">${{ parseFloat(quote.tax).toFixed(2) }}</span>
                </div>
                
                <div v-if="quote.discount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Discount:</span>
                  <span class="font-medium text-green-600">-${{ parseFloat(quote.discount).toFixed(2) }}</span>
                </div>
                
                <div class="border-t pt-3 flex justify-between">
                  <span class="font-bold text-gray-900">Total:</span>
                  <span class="font-bold text-2xl text-gray-900">${{ parseFloat(quote.total).toFixed(2) }}</span>
                </div>
              </div>

              <a
                :href="`/admin/quotes/${quote.id}/pdf`"
                target="_blank"
                class="flex items-center justify-center w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold mb-3"
              >
                <Download class="w-5 h-5 mr-2" />
                Download PDF
              </a>

              <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center">
                  <Calendar class="w-4 h-4 mr-2" />
                  <span>Quote Date: {{ new Date(quote.created_at).toLocaleDateString() }}</span>
                </div>
                <div v-if="quote.valid_until" class="flex items-center">
                  <Calendar class="w-4 h-4 mr-2" />
                  <span>Valid Until: {{ new Date(quote.valid_until).toLocaleDateString() }}</span>
                </div>
              </div>
            </div>

            <!-- Help Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
              <h3 class="font-bold text-blue-900 mb-2">Need Help?</h3>
              <p class="text-blue-800 text-sm mb-3">Have questions about this quote? Contact our team.</p>
              <a href="/contact" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Contact Support →</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Decline Quote</h3>
        <p class="text-gray-600 mb-4">Please let us know why you're declining this quote (optional):</p>
        
        <textarea
          v-model="rejectionReason"
          rows="4"
          placeholder="Enter your reason..."
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 mb-4"
        ></textarea>

        <div class="flex items-center space-x-3">
          <button
            @click="rejectQuote"
            :disabled="rejecting"
            class="flex-1 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg font-semibold"
          >
            {{ rejecting ? 'Processing...' : 'Confirm Decline' }}
          </button>
          <button
            @click="showRejectModal = false"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
