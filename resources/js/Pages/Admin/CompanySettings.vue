<script setup>
import { ref, onMounted } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import {
  Save,
  Upload,
  RotateCcw,
  Download,
  Upload as UploadIcon,
  Building,
  Mail,
  Phone,
  MapPin,
  DollarSign,
  Image,
  AlertCircle,
  Check,
  Loader2
} from 'lucide-vue-next'

const page = usePage()
const settings = ref({})
const loading = ref(false)
const saving = ref(false)
const message = ref('')
const errors = ref({})

// Form data
const formData = ref({
  company_name: '',
  company_email: '',
  company_phone: '',
  company_address: '',
  company_logo: null,
  default_currency: '',
  currency_symbol: '',
  tax_rate: '',
  installation_fee: '',
  warranty_period_months: ''
})

const logoPreview = ref('')
const logoFile = ref(null)

onMounted(async () => {
  await loadSettings()
})

const loadSettings = async () => {
  loading.value = true
  try {
    const response = await fetch('/admin/settings/data', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      settings.value = data.data

      // Populate form data
      Object.keys(formData.value).forEach(key => {
        if (settings.value[key] && settings.value[key].value !== undefined) {
          formData.value[key] = settings.value[key].value
        }
      })

      // Set logo preview if exists
      if (formData.value.company_logo) {
        logoPreview.value = formData.value.company_logo
      }
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
  } finally {
    loading.value = false
  }
}

const handleLogoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    logoFile.value = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const saveSettings = async () => {
  saving.value = true
  errors.value = {}
  message.value = ''

  try {
    const formDataObj = new FormData()

    // Add method spoofing for Laravel
    formDataObj.append('_method', 'PUT')

    // Add all form fields
    Object.keys(formData.value).forEach(key => {
      if (key !== 'company_logo' && formData.value[key] !== null && formData.value[key] !== '') {
        formDataObj.append(key, formData.value[key])
      }
    })

    // Add logo file if selected
    if (logoFile.value) {
      formDataObj.append('company_logo', logoFile.value)
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch('/admin/settings/data', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin',
      body: formDataObj
    })

    const data = await response.json()

    if (data.success) {
      message.value = 'Settings updated successfully!'
      
      // Update form with fresh data from server
      if (data.data) {
        Object.keys(formData.value).forEach(key => {
          if (data.data[key] && data.data[key].value !== undefined) {
            formData.value[key] = data.data[key].value
          }
        })
        
        // Update logo preview
        if (data.data.company_logo && data.data.company_logo.value) {
          logoPreview.value = data.data.company_logo.value
        }
      }
      
      // Force page reload after 1 second to refresh all cached data
      setTimeout(() => {
        window.location.reload()
      }, 1000)
    } else {
      if (data.errors) {
        errors.value = data.errors
      } else {
        message.value = data.message || 'Failed to update settings'
      }
    }
  } catch (error) {
    console.error('Failed to save settings:', error)
    message.value = 'An error occurred while saving settings'
  } finally {
    saving.value = false
  }
}

const resetToDefaults = async () => {
  if (!confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
    return
  }

  loading.value = true
  try {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch('/admin/settings/reset', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      message.value = 'Settings reset to defaults successfully!'
      await loadSettings()
    } else {
      message.value = data.message || 'Failed to reset settings'
    }
  } catch (error) {
    console.error('Failed to reset settings:', error)
    message.value = 'An error occurred while resetting settings'
  } finally {
    loading.value = false
  }
}

const exportSettings = async () => {
  try {
    const response = await fetch('/admin/settings/export', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })

    const data = await response.json()
    if (data.success) {
      // Download JSON file
      const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' })
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = data.filename || 'company-settings.json'
      a.click()
      window.URL.revokeObjectURL(url)
    }
  } catch (error) {
    console.error('Failed to export settings:', error)
  }
}
</script>

<template>
  <AdminLayout>
    <Head title="Company Settings" />

    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Company Settings</h1>
            <p class="text-gray-600 mt-1">Manage your company information and system configuration</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="exportSettings"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            >
              <Download class="w-4 h-4 mr-2" />
              Export
            </button>
            <button
              @click="resetToDefaults"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50"
            >
              <RotateCcw class="w-4 h-4 mr-2" />
              Reset to Defaults
            </button>
          </div>
        </div>
      </div>

      <!-- Alert Messages -->
      <div v-if="message" class="rounded-md p-4" :class="message.includes('success') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
        <div class="flex">
          <component :is="message.includes('success') ? Check : AlertCircle" class="w-5 h-5 mr-3 mt-0.5" />
          <div class="text-sm">{{ message }}</div>
        </div>
      </div>

      <!-- Settings Form -->
      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Company Information -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
              <Building class="w-5 h-5 text-gray-400 mr-3" />
              <h2 class="text-lg font-medium text-gray-900">Company Information</h2>
            </div>
          </div>
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Company Name -->
              <div>
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                  Company Name
                </label>
                <input
                  id="company_name"
                  v-model="formData.company_name"
                  type="text"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.company_name ? 'border-red-300' : ''"
                />
                <p v-if="errors.company_name" class="mt-1 text-sm text-red-600">
                  {{ errors.company_name[0] }}
                </p>
              </div>

              <!-- Company Email -->
              <div>
                <label for="company_email" class="block text-sm font-medium text-gray-700 mb-2">
                  <Mail class="w-4 h-4 inline mr-1" />
                  Company Email
                </label>
                <input
                  id="company_email"
                  v-model="formData.company_email"
                  type="email"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.company_email ? 'border-red-300' : ''"
                />
                <p v-if="errors.company_email" class="mt-1 text-sm text-red-600">
                  {{ errors.company_email[0] }}
                </p>
              </div>

              <!-- Company Phone -->
              <div>
                <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-2">
                  <Phone class="w-4 h-4 inline mr-1" />
                  Company Phone
                </label>
                <input
                  id="company_phone"
                  v-model="formData.company_phone"
                  type="text"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.company_phone ? 'border-red-300' : ''"
                />
                <p v-if="errors.company_phone" class="mt-1 text-sm text-red-600">
                  {{ errors.company_phone[0] }}
                </p>
              </div>

              <!-- Company Address -->
              <div>
                <label for="company_address" class="block text-sm font-medium text-gray-700 mb-2">
                  <MapPin class="w-4 h-4 inline mr-1" />
                  Company Address
                </label>
                <textarea
                  id="company_address"
                  v-model="formData.company_address"
                  rows="3"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.company_address ? 'border-red-300' : ''"
                ></textarea>
                <p v-if="errors.company_address" class="mt-1 text-sm text-red-600">
                  {{ errors.company_address[0] }}
                </p>
              </div>
            </div>

            <!-- Company Logo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                <Image class="w-4 h-4 inline mr-1" />
                Company Logo
              </label>
              <div class="flex items-center space-x-6">
                <!-- Current Logo Preview -->
                <div v-if="logoPreview" class="flex-shrink-0">
                  <img :src="logoPreview" alt="Logo Preview" class="h-20 w-20 object-contain border border-gray-300 rounded-md p-2 bg-white" />
                </div>
                <div v-else class="flex-shrink-0">
                  <div class="h-20 w-20 border-2 border-dashed border-gray-300 rounded-md flex items-center justify-center">
                    <Image class="h-8 w-8 text-gray-400" />
                  </div>
                </div>

                <!-- File Input -->
                <div class="flex-1">
                  <input
                    type="file"
                    accept="image/*"
                    @change="handleLogoChange"
                    class="hidden"
                    ref="logoInput"
                  />
                  <button
                    type="button"
                    @click="$refs.logoInput.click()"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                  >
                    <UploadIcon class="w-4 h-4 mr-2" />
                    Upload Logo
                  </button>
                  <p class="mt-2 text-sm text-gray-500">
                    PNG, JPG, SVG up to 2MB. Recommended size: 200x200px
                  </p>
                </div>
              </div>
              <p v-if="errors.company_logo" class="mt-1 text-sm text-red-600">
                {{ errors.company_logo[0] }}
              </p>
            </div>
          </div>
        </div>

        <!-- Currency & Pricing -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
              <DollarSign class="w-5 h-5 text-gray-400 mr-3" />
              <h2 class="text-lg font-medium text-gray-900">Currency & Pricing</h2>
            </div>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Default Currency -->
              <div>
                <label for="default_currency" class="block text-sm font-medium text-gray-700 mb-2">
                  Default Currency
                </label>
                <select
                  id="default_currency"
                  v-model="formData.default_currency"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.default_currency ? 'border-red-300' : ''"
                >
                  <option value="USD">USD - US Dollar</option>
                  <option value="EUR">EUR - Euro</option>
                  <option value="GBP">GBP - British Pound</option>
                  <option value="CAD">CAD - Canadian Dollar</option>
                  <option value="AUD">AUD - Australian Dollar</option>
                </select>
                <p v-if="errors.default_currency" class="mt-1 text-sm text-red-600">
                  {{ errors.default_currency[0] }}
                </p>
              </div>

              <!-- Currency Symbol -->
              <div>
                <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-2">
                  Currency Symbol
                </label>
                <input
                  id="currency_symbol"
                  v-model="formData.currency_symbol"
                  type="text"
                  maxlength="5"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.currency_symbol ? 'border-red-300' : ''"
                />
                <p v-if="errors.currency_symbol" class="mt-1 text-sm text-red-600">
                  {{ errors.currency_symbol[0] }}
                </p>
              </div>

              <!-- Tax Rate -->
              <div>
                <label for="tax_rate" class="block text-sm font-medium text-gray-700 mb-2">
                  Tax Rate (%)
                </label>
                <input
                  id="tax_rate"
                  v-model="formData.tax_rate"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.tax_rate ? 'border-red-300' : ''"
                />
                <p v-if="errors.tax_rate" class="mt-1 text-sm text-red-600">
                  {{ errors.tax_rate[0] }}
                </p>
              </div>

              <!-- Installation Fee -->
              <div>
                <label for="installation_fee" class="block text-sm font-medium text-gray-700 mb-2">
                  Default Installation Fee
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">{{ formData.currency_symbol || '$' }}</span>
                  </div>
                  <input
                    id="installation_fee"
                    v-model="formData.installation_fee"
                    type="number"
                    step="0.01"
                    min="0"
                    class="block w-full pl-7 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                    :class="errors.installation_fee ? 'border-red-300' : ''"
                  />
                </div>
                <p v-if="errors.installation_fee" class="mt-1 text-sm text-red-600">
                  {{ errors.installation_fee[0] }}
                </p>
              </div>

              <!-- Warranty Period -->
              <div>
                <label for="warranty_period_months" class="block text-sm font-medium text-gray-700 mb-2">
                  Default Warranty (months)
                </label>
                <input
                  id="warranty_period_months"
                  v-model="formData.warranty_period_months"
                  type="number"
                  min="1"
                  max="120"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                  :class="errors.warranty_period_months ? 'border-red-300' : ''"
                />
                <p v-if="errors.warranty_period_months" class="mt-1 text-sm text-red-600">
                  {{ errors.warranty_period_months[0] }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end bg-white rounded-lg shadow p-6">
          <button
            type="submit"
            :disabled="saving"
            class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50"
          >
            <component :is="saving ? Loader2 : Save" class="w-4 h-4 mr-2" :class="{ 'animate-spin': saving }" />
            {{ saving ? 'Saving...' : 'Save Settings' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>