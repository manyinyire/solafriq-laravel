<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-yellow-50">
    <div class="flex min-h-screen">
      <!-- Left side - Solar themed illustration -->
      <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 relative overflow-hidden">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
        <!-- Solar panels illustration -->
        <div class="relative z-10 flex flex-col justify-center p-12 text-white">
          <div class="mb-8">
            <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mb-6 shadow-lg">
              <Sun class="w-8 h-8 text-yellow-800" />
            </div>
            <h1 class="text-4xl font-bold mb-4">Welcome to {{ companySettings.company_name || 'SolaFriq' }}</h1>
            <p class="text-xl text-blue-200 leading-relaxed font-medium">
              Your comprehensive solar energy management platform. Monitor, manage, and optimize your solar installations with ease.
            </p>
          </div>

          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center">
                <Check class="w-5 h-5 text-green-800" />
              </div>
              <span class="text-blue-200 font-medium">Real-time energy monitoring</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center">
                <Check class="w-5 h-5 text-green-800" />
              </div>
              <span class="text-blue-200 font-medium">Smart analytics & insights</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center">
                <Check class="w-5 h-5 text-green-800" />
              </div>
              <span class="text-blue-200 font-medium">Warranty & maintenance tracking</span>
            </div>
          </div>
        </div>

        <!-- Decorative elements -->
        <div class="absolute top-1/4 right-1/4 w-32 h-32 bg-yellow-400 bg-opacity-20 rounded-full blur-xl"></div>
        <div class="absolute bottom-1/3 left-1/4 w-24 h-24 bg-green-400 bg-opacity-20 rounded-full blur-xl"></div>
      </div>

      <!-- Right side - Login form -->
      <div class="flex-1 flex items-center justify-center p-8">
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
            <h2 class="text-3xl font-bold text-gray-900">Sign in to {{ companySettings.company_name || 'SolaFriq' }}</h2>
            <p class="mt-2 text-gray-600">Access your solar energy dashboard</p>
          </div>

          <!-- Login form -->
          <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <form class="space-y-6" @submit.prevent="submit">
              <div class="space-y-5">
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
                  <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <Lock class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                      id="password"
                      v-model="form.password"
                      name="password"
                      type="password"
                      autocomplete="current-password"
                      required
                      class="block w-full pl-11 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-500 transition-colors"
                      placeholder="Enter your password"
                    />
                  </div>
                  <div v-if="errors.password" class="text-red-600 text-sm mt-2 flex items-center">
                    <AlertCircle class="w-4 h-4 mr-1" />
                    {{ errors.password }}
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <input
                    id="remember"
                    v-model="form.remember"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="remember" class="ml-2 block text-sm text-gray-700">
                    Remember me
                  </label>
                </div>

                <div class="text-sm">
                  <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                    Forgot password?
                  </a>
                </div>
              </div>

              <div>
                <button
                  type="submit"
                  :disabled="processing"
                  class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                  <span v-if="!processing" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <Lock class="h-5 w-5 text-blue-300 group-hover:text-blue-200" />
                  </span>
                  <span v-if="processing" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="animate-spin h-5 w-5 text-blue-300" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  {{ processing ? 'Signing in...' : 'Sign in to Dashboard' }}
                </button>
              </div>

              <div class="text-center pt-4">
                <p class="text-sm text-gray-600">
                  Don't have an account?
                  <Link href="/register" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                    Create account
                  </Link>
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { Lock, Mail, Sun, Check, AlertCircle } from 'lucide-vue-next'

const page = usePage()
const processing = ref(false)

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {})

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const errors = ref(page.props.errors || {})

const submit = () => {
  processing.value = true

  router.post('/login', form, {
    onSuccess: () => {
      processing.value = false
    },
    onError: (errors) => {
      processing.value = false
      errors.value = errors
    }
  })
}
</script>