<script setup>
import { Head } from '@inertiajs/vue3'
import ClientLayout from '@/Layouts/ClientLayout.vue'
import { Wrench, Mail, Phone, MessageCircle } from 'lucide-vue-next'
</script>

<template>
  <ClientLayout>
    <Head title="Support" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Support Center</h1>
            <p class="text-gray-600 mt-1">Get help with your solar system</p>
          </div>
          <Wrench class="h-12 w-12 text-orange-500" />
        </div>
      </div>

      <!-- Coming Soon -->
      <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="max-w-md mx-auto">
          <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <Wrench class="h-12 w-12 text-orange-600" />
          </div>
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Coming Soon</h2>
          <p class="text-lg text-gray-600 mb-8">
            We're building an amazing support experience for you. In the meantime, feel free to reach out to us directly.
          </p>

          <!-- Contact Options -->
          <div class="space-y-4 text-left">
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
              <Mail class="h-6 w-6 text-orange-600" />
              <div>
                <p class="font-medium text-gray-900">Email Support</p>
                <a href="mailto:support@solafriq.com" class="text-orange-600 hover:text-orange-700">
                  support@solafriq.com
                </a>
              </div>
            </div>

            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
              <Phone class="h-6 w-6 text-orange-600" />
              <div>
                <p class="font-medium text-gray-900">Phone Support</p>
                <a href="tel:+1234567890" class="text-orange-600 hover:text-orange-700">
                  +1 (234) 567-890
                </a>
              </div>
            </div>

            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
              <MessageCircle class="h-6 w-6 text-orange-600" />
              <div>
                <p class="font-medium text-gray-900">Live Chat</p>
                <p class="text-gray-600">Available Monday - Friday, 9AM - 5PM</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>
