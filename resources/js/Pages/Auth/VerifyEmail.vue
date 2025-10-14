<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-yellow-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full">
        <!-- Icon -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-full mb-4 shadow-lg">
            <Mail class="w-10 h-10 text-white" />
          </div>
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Verify Your Email</h1>
          <p class="text-gray-600">We're almost there!</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <!-- Success Message -->
          <div v-if="verificationLinkSent" class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-start">
              <CheckCircle class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
              <div>
                <h3 class="font-semibold text-green-900 mb-1">Email Sent!</h3>
                <p class="text-sm text-green-700">
                  A new verification link has been sent to your email address. Please check your inbox.
                </p>
              </div>
            </div>
          </div>

          <!-- Instructions -->
          <div class="text-center mb-8">
            <p class="text-gray-700 leading-relaxed mb-4">
              Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent you.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">
                <strong>📧 Check your inbox</strong><br>
                Look for an email from SolaFriq with the subject "Verify Email Address"
              </p>
            </div>
          </div>

          <!-- Divider -->
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-white text-gray-500">Didn't receive the email?</span>
            </div>
          </div>

          <!-- Resend Button -->
          <form @submit.prevent="submit">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 disabled:from-gray-400 disabled:to-gray-500 text-white py-3 px-6 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] disabled:transform-none disabled:cursor-not-allowed flex items-center justify-center"
            >
              <RefreshCw :class="['w-5 h-5 mr-2', form.processing ? 'animate-spin' : '']" />
              {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
            </button>
          </form>

          <!-- Help Text -->
          <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              Still having trouble? 
              <a href="/contact" class="text-orange-600 hover:text-orange-700 font-medium">Contact Support</a>
            </p>
          </div>
        </div>

        <!-- Additional Tips -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
          <h4 class="font-semibold text-yellow-900 mb-2">💡 Tips:</h4>
          <ul class="text-sm text-yellow-800 space-y-1">
            <li>• Check your spam or junk folder</li>
            <li>• Make sure you entered the correct email address</li>
            <li>• Wait a few minutes before requesting another email</li>
          </ul>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Mail, CheckCircle, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
  status: String,
});

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const submit = () => {
  form.post(route('verification.send'), {
    onFinish: () => form.reset(),
  });
};
</script>