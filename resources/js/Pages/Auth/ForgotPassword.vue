<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-yellow-50">
    <div class="flex min-h-screen items-center justify-center p-8">
      <div class="max-w-md w-full space-y-8">
        <!-- Logo and header -->
        <div class="text-center">
          <div class="mx-auto mb-6 flex items-center justify-center">
            <img 
              v-if="companySettings.company_logo" 
              :src="companySettings.company_logo" 
              :alt="companySettings.company_name || 'SolaFriq'"
              class="h-16 w-auto object-contain"
            />
            <div v-else class="h-16 w-16 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
              <Sun class="h-8 w-8 text-yellow-300" />
            </div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Reset your password</h2>
          <p class="mt-2 text-gray-600">Enter your email address and we'll send you a link to reset your password</p>
        </div>

        <!-- Success message -->
        <div v-if="$page.props.flash.status" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
          <Check class="w-5 h-5 mr-2 flex-shrink-0" />
          <span>{{ $page.props.flash.status }}</span>
        </div>

        <!-- Forgot Password form -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <form class="space-y-6" @submit.prevent="submit">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email address
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="email"
                  v-model="form.email"
                  name="email"
                  type="email"
                  autocomplete="email"
                  required
                  class="block w-full pl-11 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-500 transition-colors"
                  placeholder="Enter your email"
                />
              </div>
              <div v-if="errors.email" class="text-red-600 text-sm mt-2 flex items-center">
                <AlertCircle class="w-4 h-4 mr-1" />
                {{ errors.email }}
              </div>
            </div>

            <div>
              <button
                type="submit"
                :disabled="processing"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
              >
                <span v-if="!processing">Send Reset Link</span>
                <span v-else class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Sending...
                </span>
              </button>
            </div>

            <div class="text-center">
              <Link :href="route('login')" class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors">
                Back to login
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { Mail, Sun, Check, AlertCircle } from 'lucide-vue-next'

const page = usePage()
const processing = ref(false)

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {})

const form = reactive({
  email: ''
})

const errors = ref(page.props.errors || {})

const submit = () => {
  processing.value = true

  router.post('/forgot-password', form, {
    onSuccess: () => {
      processing.value = false
      form.email = ''
    },
    onError: (err) => {
      processing.value = false
      errors.value = err
    }
  })
}
</script>
