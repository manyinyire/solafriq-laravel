<script setup>
import { Head } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import { CreditCard } from 'lucide-vue-next'
</script>

<template>
  <ClientLayout>
    <Head title="Installments" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Installment Plans</h1>
            <p class="text-gray-600 mt-1">Manage your payment plans</p>
          </div>
          <CreditCard class="h-12 w-12 text-orange-500" />
        </div>
      </div>

      <!-- Coming Soon -->
      <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="max-w-md mx-auto">
          <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <CreditCard class="h-12 w-12 text-orange-600" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Coming Soon</h2>
          <p class="text-lg text-gray-600 mb-8">
            Flexible payment plans are on the way! Soon you'll be able to manage your installments and make payments directly from this page.
          </p>
          <p class="text-sm text-gray-500">
            For now, please contact our sales team to set up a payment plan.
          </p>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
