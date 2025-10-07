<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  Home,
  Settings,
  Users,
  Package,
  BarChart3,
  Shield,
  Menu,
  X,
  LogOut,
  User,
  ShoppingCart,
  Zap
} from 'lucide-vue-next'

const page = usePage()
const sidebarOpen = ref(false)

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {})

const navigation = [
  { name: 'Dashboard', href: '/admin/dashboard', icon: Home },
  { name: 'Users', href: '/admin/users', icon: Users },
  { name: 'Solar Systems', href: '/admin/systems', icon: Zap },
  { name: 'Products', href: '/admin/products', icon: Package },
  { name: 'Orders', href: '/admin/orders', icon: ShoppingCart },
  { name: 'Analytics', href: '/admin/analytics', icon: BarChart3 },
  { name: 'Warranties', href: '/admin/warranties', icon: Shield },
  { name: 'Company Settings', href: '/admin/settings', icon: Settings },
]

const logout = () => {
  // Create a form and submit it as POST to logout
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/logout'
  
  // Add CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (csrfToken) {
    const csrfInput = document.createElement('input')
    csrfInput.type = 'hidden'
    csrfInput.name = '_token'
    csrfInput.value = csrfToken
    form.appendChild(csrfInput)
  }
  
  document.body.appendChild(form)
  form.submit()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile sidebar -->
    <div v-show="sidebarOpen" class="relative z-50 lg:hidden" role="dialog">
      <div class="fixed inset-0 bg-gray-900/80" @click="sidebarOpen = false"></div>
      <div class="fixed inset-y-0 left-0 z-50 w-72 bg-white px-6 pb-4 overflow-y-auto">
        <div class="flex h-16 items-center justify-between">
          <img class="h-8 w-auto" :src="companySettings.company_logo || '/images/solafriq-logo.png'" :alt="companySettings.company_name || 'SolaFriq'" />
          <button @click="sidebarOpen = false" class="rounded-md text-gray-700 hover:text-gray-900">
            <X class="h-6 w-6" />
          </button>
        </div>
        <nav class="mt-8">
          <ul class="space-y-1">
            <li v-for="item in navigation" :key="item.name">
              <Link
                :href="item.href"
                :class="[
                  $page.url === item.href
                    ? 'bg-orange-50 text-orange-700 border-r-2 border-orange-600'
                    : 'text-gray-700 hover:text-orange-600 hover:bg-orange-50',
                  'group flex gap-x-3 rounded-l-md p-3 text-sm font-semibold leading-6'
                ]"
              >
                <component :is="item.icon" :class="[
                  $page.url === item.href ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600',
                  'h-5 w-5'
                ]" />
                {{ item.name }}
              </Link>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
      <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 border-r border-gray-200">
        <div class="flex h-16 shrink-0 items-center">
          <img class="h-8 w-auto" :src="companySettings.company_logo || '/images/solafriq-logo.png'" :alt="companySettings.company_name || 'SolaFriq'" />
          <span class="ml-3 text-xl font-bold text-gray-900">Admin</span>
        </div>
        <nav class="flex flex-1 flex-col">
          <ul class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul class="space-y-1">
                <li v-for="item in navigation" :key="item.name">
                  <Link
                    :href="item.href"
                    :class="[
                      $page.url === item.href
                        ? 'bg-orange-50 text-orange-700 border-r-2 border-orange-600'
                        : 'text-gray-700 hover:text-orange-600 hover:bg-orange-50',
                      'group flex gap-x-3 rounded-l-md p-3 text-sm font-semibold leading-6'
                    ]"
                  >
                    <component :is="item.icon" :class="[
                      $page.url === item.href ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600',
                      'h-5 w-5'
                    ]" />
                    {{ item.name }}
                  </Link>
                </li>
              </ul>
            </li>
            <li class="mt-auto">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                    <User class="h-4 w-4 text-white" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {{ page.props.auth?.user?.name }}
                    </p>
                    <p class="text-xs text-gray-500">Administrator</p>
                  </div>
                  <button
                    @click="logout"
                    class="text-gray-400 hover:text-gray-600"
                    title="Logout"
                  >
                    <LogOut class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Main content -->
    <div class="lg:pl-72">
      <!-- Mobile header -->
      <div class="sticky top-0 z-40 flex h-16 items-center gap-x-4 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:hidden">
        <button @click="sidebarOpen = true" class="text-gray-700">
          <Menu class="h-6 w-6" />
        </button>
        <div class="flex flex-1 items-center justify-between">
          <h1 class="text-lg font-semibold text-gray-900">Admin Panel</h1>
          <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-600">{{ page.props.auth?.user?.name }}</span>
            <button
              @click="logout"
              class="text-gray-400 hover:text-gray-600"
              title="Logout"
            >
              <LogOut class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Page content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>