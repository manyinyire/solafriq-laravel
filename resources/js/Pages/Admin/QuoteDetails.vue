<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { FileText, Send, Download, Edit, Save, X, Plus, Trash2, User, Mail, Phone, MapPin, Calendar, DollarSign, Package, CheckCircle } from 'lucide-vue-next';

const props = defineProps({
  quote: Object,
});

const editing = ref(false);
const sending = ref(false);

const form = ref({
  customer_name: props.quote.customer_name,
  customer_email: props.quote.customer_email,
  customer_phone: props.quote.customer_phone,
  customer_address: props.quote.customer_address,
  subtotal: parseFloat(props.quote.subtotal),
  tax: parseFloat(props.quote.tax),
  discount: parseFloat(props.quote.discount),
  admin_notes: props.quote.admin_notes || '',
  terms_and_conditions: props.quote.terms_and_conditions || '',
  valid_until: props.quote.valid_until,
});

const items = ref(props.quote.items.map(item => ({
  id: item.id,
  item_name: item.item_name,
  item_description: item.item_description,
  quantity: item.quantity,
  unit_price: parseFloat(item.unit_price),
  total_price: parseFloat(item.total_price),
})));

const total = computed(() => {
  return form.value.subtotal + form.value.tax - form.value.discount;
});

const canEdit = computed(() => {
  return ['pending', 'sent'].includes(props.quote.status);
});

const canSend = computed(() => {
  return props.quote.status === 'pending';
});

const canAccept = computed(() => {
  return props.quote.status === 'sent' || props.quote.status === 'pending';
});

const accepting = ref(false);

const acceptQuoteOnBehalf = () => {
  if (confirm('Accept this quote and create an order for the customer?')) {
    accepting.value = true;
    router.post(`/admin/quotes/${props.quote.id}/accept`, {}, {
      preserveScroll: true,
      onFinish: () => {
        accepting.value = false;
      },
    });
  }
};

const updateQuote = () => {
  router.put(`/admin/quotes/${props.quote.id}`, form.value, {
    preserveScroll: true,
    onSuccess: () => {
      editing.value = false;
    },
  });
};

const updateItems = () => {
  router.put(`/admin/quotes/${props.quote.id}/items`, {
    items: items.value,
  }, {
    preserveScroll: true,
  });
};

const sendQuote = () => {
  if (confirm('Are you sure you want to send this quote to the customer?')) {
    sending.value = true;
    router.post(`/admin/quotes/${props.quote.id}/send`, {}, {
      preserveScroll: true,
      onFinish: () => {
        sending.value = false;
      },
    });
  }
};

const recalculateSubtotal = () => {
  form.value.subtotal = items.value.reduce((sum, item) => sum + item.total_price, 0);
};

const updateItemTotal = (item) => {
  item.total_price = item.quantity * item.unit_price;
  recalculateSubtotal();
};

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800',
    sent: 'bg-blue-100 text-blue-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    expired: 'bg-gray-100 text-gray-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
  <AdminLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
              <FileText class="w-8 h-8 mr-3 text-orange-500" />
              Quote {{ quote.quote_number }}
            </h1>
            <p class="text-gray-600 mt-2">Review and manage quote details</p>
          </div>
          <div class="flex items-center space-x-3">
            <span :class="['px-4 py-2 rounded-full text-sm font-semibold', getStatusColor(quote.status)]">
              {{ quote.status.charAt(0).toUpperCase() + quote.status.slice(1) }}
            </span>
            <a
              :href="`/admin/quotes/${quote.id}/pdf`"
              target="_blank"
              class="flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold"
            >
              <Download class="w-4 h-4 mr-2" />
              Download PDF
            </a>
            <button
              v-if="canAccept"
              @click="acceptQuoteOnBehalf"
              :disabled="accepting"
              class="flex items-center bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg font-semibold"
            >
              <CheckCircle class="w-4 h-4 mr-2" />
              {{ accepting ? 'Processing...' : 'Accept on Behalf' }}
            </button>
            <button
              v-if="canSend"
              @click="sendQuote"
              :disabled="sending"
              class="flex items-center bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 disabled:from-gray-400 disabled:to-gray-500 text-white px-4 py-2 rounded-lg font-semibold"
            >
              <Send class="w-4 h-4 mr-2" />
              {{ sending ? 'Sending...' : 'Send to Client' }}
            </button>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Customer Information -->
          <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Customer Information</h2>
              <button
                v-if="canEdit && !editing"
                @click="editing = true"
                class="flex items-center text-orange-600 hover:text-orange-700 font-medium"
              >
                <Edit class="w-4 h-4 mr-2" />
                Edit
              </button>
              <div v-if="editing" class="flex items-center space-x-2">
                <button
                  @click="updateQuote"
                  class="flex items-center bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm"
                >
                  <Save class="w-4 h-4 mr-1" />
                  Save
                </button>
                <button
                  @click="editing = false"
                  class="flex items-center bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg text-sm"
                >
                  <X class="w-4 h-4 mr-1" />
                  Cancel
                </button>
              </div>
            </div>

            <div v-if="!editing" class="space-y-3">
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

            <div v-else class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input v-model="form.customer_name" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input v-model="form.customer_email" type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input v-model="form.customer_phone" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea v-model="form.customer_address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"></textarea>
              </div>
            </div>
          </div>

          <!-- Quote Items -->
          <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Quote Items</h2>
              <button
                v-if="canEdit"
                @click="updateItems"
                class="flex items-center bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-sm"
              >
                <Save class="w-4 h-4 mr-1" />
                Save Items
              </button>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="item in items" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="font-medium text-gray-900">{{ item.item_name }}</div>
                      <div v-if="item.item_description" class="text-sm text-gray-500">{{ item.item_description }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                      <input
                        v-if="canEdit"
                        v-model.number="item.quantity"
                        @input="updateItemTotal(item)"
                        type="number"
                        min="1"
                        class="w-20 px-2 py-1 text-center border border-gray-300 rounded focus:ring-2 focus:ring-orange-500"
                      />
                      <span v-else>{{ item.quantity }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <input
                        v-if="canEdit"
                        v-model.number="item.unit_price"
                        @input="updateItemTotal(item)"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-32 px-2 py-1 text-right border border-gray-300 rounded focus:ring-2 focus:ring-orange-500"
                      />
                      <span v-else>${{ item.unit_price.toFixed(2) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold">${{ item.total_price.toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Admin Notes -->
          <div v-if="editing" class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Notes (Internal)</h2>
            <textarea
              v-model="form.admin_notes"
              rows="4"
              placeholder="Internal notes (not visible to customer)"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
            ></textarea>
          </div>

          <!-- Terms & Conditions -->
          <div v-if="editing" class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Terms & Conditions</h2>
            <textarea
              v-model="form.terms_and_conditions"
              rows="6"
              placeholder="Enter terms and conditions for this quote"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
            ></textarea>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quote Summary -->
          <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Quote Summary</h2>
            
            <div class="space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal:</span>
                <span v-if="!editing" class="font-medium">${{ parseFloat(quote.subtotal).toFixed(2) }}</span>
                <input
                  v-else
                  v-model.number="form.subtotal"
                  type="number"
                  step="0.01"
                  class="w-32 px-2 py-1 text-right border border-gray-300 rounded focus:ring-2 focus:ring-orange-500"
                  readonly
                />
              </div>
              
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax:</span>
                <span v-if="!editing" class="font-medium">${{ parseFloat(quote.tax).toFixed(2) }}</span>
                <input
                  v-else
                  v-model.number="form.tax"
                  type="number"
                  step="0.01"
                  class="w-32 px-2 py-1 text-right border border-gray-300 rounded focus:ring-2 focus:ring-orange-500"
                />
              </div>
              
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Discount:</span>
                <span v-if="!editing" class="font-medium text-red-600">-${{ parseFloat(quote.discount).toFixed(2) }}</span>
                <input
                  v-else
                  v-model.number="form.discount"
                  type="number"
                  step="0.01"
                  class="w-32 px-2 py-1 text-right border border-gray-300 rounded focus:ring-2 focus:ring-orange-500"
                />
              </div>
              
              <div class="border-t pt-3 flex justify-between">
                <span class="font-bold text-gray-900">Total:</span>
                <span class="font-bold text-xl text-gray-900">${{ total.toFixed(2) }}</span>
              </div>
            </div>

            <div v-if="editing" class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
              <input
                v-model="form.valid_until"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
              />
            </div>
          </div>

          <!-- Quote Details -->
          <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Quote Details</h2>
            
            <div class="space-y-3 text-sm">
              <div>
                <div class="text-gray-500">Created</div>
                <div class="font-medium">{{ new Date(quote.created_at).toLocaleString() }}</div>
              </div>
              
              <div v-if="quote.sent_at">
                <div class="text-gray-500">Sent</div>
                <div class="font-medium">{{ new Date(quote.sent_at).toLocaleString() }}</div>
              </div>
              
              <div v-if="quote.accepted_at">
                <div class="text-gray-500">Accepted</div>
                <div class="font-medium">{{ new Date(quote.accepted_at).toLocaleString() }}</div>
              </div>
              
              <div v-if="quote.valid_until">
                <div class="text-gray-500">Valid Until</div>
                <div class="font-medium">{{ new Date(quote.valid_until).toLocaleDateString() }}</div>
              </div>
            </div>
          </div>

          <!-- Customer Notes -->
          <div v-if="quote.notes" class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h3 class="font-bold text-blue-900 mb-2">Customer Notes</h3>
            <p class="text-blue-800 text-sm">{{ quote.notes }}</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
