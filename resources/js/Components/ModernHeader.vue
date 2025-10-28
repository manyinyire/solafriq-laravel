<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, ShoppingCart, User, LogOut, Settings, Sun, Shield, Phone, Mail, ChevronDown, FileText, X } from 'lucide-vue-next';

const page = usePage();

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {});

// Mobile menu state
const mobileMenuOpen = ref(false);

// Toggle mobile menu
const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Close mobile menu
const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
};

const logout = async () => {
  try {
    // First, try to get a fresh CSRF token
    const response = await fetch('/sanctum/csrf-cookie', {
      method: 'GET',
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      // Get the updated CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      
      if (!csrfToken) {
        console.error('CSRF token not found')
        alert('Unable to logout. Please refresh the page and try again.')
        return
      }
      
      // Create a form and submit it as POST to logout
      const form = document.createElement('form')
      form.method = 'POST'
      form.action = '/logout'
      
      // Add CSRF token
      const csrfInput = document.createElement('input')
      csrfInput.type = 'hidden'
      csrfInput.name = '_token'
      csrfInput.value = csrfToken
      form.appendChild(csrfInput)
      
      document.body.appendChild(form)
      form.submit()
    } else {
      // Fallback to direct form submission
      const form = document.createElement('form')
      form.method = 'POST'
      form.action = '/logout'
      
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
  } catch (error) {
    console.error('Logout error:', error)
    // Fallback to direct form submission
    const form = document.createElement('form')
    form.method = 'POST'
    form.action = '/logout'
    
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
}

const isScrolled = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Get solar systems and product categories from shared data
const solarSystems = computed(() => page.props.solarSystems || []);
const productCategories = computed(() => page.props.productCategories || []);

const navItems = computed(() => {
  // Build Solutions menu with dynamic solar systems
  const solutionsItems = [
    ...solarSystems.value,
    { title: "Custom Builder", href: "/custom-builder", description: "Build your perfect solar system" },
  ];

  // Build Products menu with dynamic categories
  const productsItems = productCategories.value.length > 0 
    ? productCategories.value 
    : [
        { title: "All Products", href: "/products", description: "Browse all products" },
      ];

  return [
    {
      title: "Solutions",
      items: solutionsItems,
    },
    {
      title: "Products",
      items: productsItems,
    },
    {
      title: "Services",
      items: [
        { title: "Installation", href: "/services/installation", description: "Professional installation services" },
        { title: "Maintenance", href: "/services/maintenance", description: "Ongoing support and maintenance" },
        { title: "Consultation", href: "/services/consultation", description: "Free solar consultation" },
        { title: "Financing", href: "/services/financing", description: "Flexible payment options" },
      ],
    },
  ];
});

</script>

<template>
  <div>
    <!-- Top Bar -->
    <div class="bg-gradient-to-r from-orange-600 to-yellow-500 text-white py-2 px-4">
      <div class="container mx-auto flex justify-between items-center text-sm">
        <div class="flex items-center space-x-6">
          <div class="flex items-center space-x-2">
            <Phone class="h-4 w-4" />
            <span>{{ companySettings.company_phone || '+263 77 123 4567' }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <Mail class="h-4 w-4" />
            <span>{{ companySettings.company_email || 'info@solafriq.com' }}</span>
          </div>
        </div>
        <div class="hidden md:flex items-center space-x-4">
          <span class="bg-white/20 text-white border-white/30 px-3 py-1 rounded-full text-xs">
            Free Installation Quote
          </span>
        </div>
      </div>
    </div>

    <!-- Main Header -->
    <header
      :class="['sticky top-0 z-50 transition-all duration-300', { 'bg-white/95 backdrop-blur-md shadow-lg border-b border-gray-200': isScrolled, 'bg-white shadow-sm': !isScrolled }]"
    >
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <Link href="/" class="flex items-center group">
            <img 
              :src="companySettings.company_logo || '/images/solafriq-logo.svg'" 
              :alt="companySettings.company_name || 'SolaFriq'"
              class="h-12 w-auto object-contain transition-all duration-300 group-hover:scale-105"
            />
          </Link>

          <!-- Desktop Navigation -->
          <nav class="hidden lg:flex">
            <ul class="flex space-x-2">
              <li v-for="item in navItems" :key="item.title" class="relative group">
                <button class="bg-transparent hover:bg-orange-50 hover:text-orange-600 transition-colors px-4 py-2 rounded-md text-sm font-medium flex items-center">
                  {{ item.title }}
                  <ChevronDown class="h-4 w-4 ml-1" />
                </button>
                <div class="absolute top-full left-0 w-[250px] bg-white shadow-lg rounded-lg p-3 hidden group-hover:grid gap-1 z-50">
                  <Link v-for="subItem in item.items" :key="subItem.title" :href="subItem.href" class="block select-none rounded-lg px-3 py-2 leading-none no-underline outline-none transition-colors hover:bg-orange-50 hover:text-orange-600 focus:bg-orange-50 focus:text-orange-600">
                    <div class="text-sm font-medium">{{ subItem.title }}</div>
                  </Link>
                </div>
              </li>
              <li>
                <Link href="/about" class="group inline-flex h-10 w-max items-center justify-center rounded-md bg-transparent px-4 py-2 text-sm font-medium transition-colors hover:bg-orange-50 hover:text-orange-600 focus:bg-orange-50 focus:text-orange-600 focus:outline-none disabled:pointer-events-none disabled:opacity-50">
                  About
                </Link>
              </li>
              <li>
                <Link href="/contact" class="group inline-flex h-10 w-max items-center justify-center rounded-md bg-transparent px-4 py-2 text-sm font-medium transition-colors hover:bg-orange-50 hover:text-orange-600 focus:bg-orange-50 focus:text-orange-600 focus:outline-none disabled:pointer-events-none disabled:opacity-50">
                  Contact
                </Link>
              </li>
            </ul>
          </nav>

          <!-- Right Side Actions -->
          <div class="flex items-center space-x-4">
            <!-- Shopping Cart -->
            <Link href="/cart" class="relative hover:bg-orange-50 hover:text-orange-600 p-2 rounded-full transition-colors">
              <ShoppingCart class="h-5 w-5" />
              <span
                v-if="$page.props.cart.item_count > 0"
                class="absolute -top-2 -right-2 h-5 w-5 rounded-full p-0 flex items-center justify-center bg-gradient-to-r from-orange-500 to-yellow-500 text-white text-xs font-medium shadow-lg animate-pulse"
              >
                {{ $page.props.cart.item_count }}
              </span>
            </Link>

            <!-- User Menu -->
            <div v-if="$page.props.auth.user" class="relative group">
                <button class="flex items-center space-x-2 hover:bg-orange-50 hover:text-orange-600 p-1 rounded-full">
                    <div class="h-8 w-8 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-full flex items-center justify-center">
                        <User class="h-4 w-4 text-white" />
                    </div>
                    <span class="hidden md:block">{{ $page.props.auth.user.name }}</span>
                    <ChevronDown class="h-4 w-4" />
                </button>
                
                <!-- Dropdown Menu -->
                <div class="absolute right-0 top-full mt-2 w-48 bg-white shadow-lg rounded-lg border border-gray-200 hidden group-hover:block z-50">
                    <div class="py-2">
                        <Link :href="$page.props.auth.user.is_admin ? '/admin/dashboard' : '/dashboard'" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                            <User class="h-4 w-4 mr-3" />
                            Dashboard
                        </Link>
                        <Link v-if="!$page.props.auth.user.is_admin" href="/client/quotes" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                            <FileText class="h-4 w-4 mr-3" />
                            My Quotes
                        </Link>
                        <Link href="/profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">
                            <Settings class="h-4 w-4 mr-3" />
                            Profile Settings
                        </Link>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button @click="logout" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600">
                            <LogOut class="h-4 w-4 mr-3" />
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="flex items-center space-x-2">
                <Link href="/login">
                    <button class="hover:bg-orange-50 hover:text-orange-600 px-3 py-2 rounded-md text-sm font-medium">
                        Sign In
                    </button>
                </Link>
                <Link href="/register">
                    <button class="bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white shadow-lg hover:shadow-orange-300/50 px-4 py-2 rounded-full text-sm font-medium">
                        Get Started
                    </button>
                </Link>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="toggleMobileMenu" class="lg:hidden hover:bg-orange-50 hover:text-orange-600 p-2 rounded-full">
              <Menu class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div v-show="mobileMenuOpen" class="lg:hidden fixed inset-0 z-50" role="dialog">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-900/80" @click="closeMobileMenu"></div>
      
      <!-- Mobile menu panel -->
      <div class="fixed inset-y-0 right-0 z-50 w-80 bg-white px-6 pb-4 overflow-y-auto">
        <div class="flex h-16 items-center justify-between">
          <img class="h-8 w-auto" :src="companySettings.company_logo || '/images/solafriq-logo.svg'" :alt="companySettings.company_name || 'SolaFriq'" />
          <button @click="closeMobileMenu" class="rounded-md text-gray-700 hover:text-gray-900">
            <X class="h-6 w-6" />
          </button>
        </div>
        
        <!-- Navigation Links -->
        <nav class="mt-8">
          <ul class="space-y-4">
            <li>
              <Link href="/" @click="closeMobileMenu" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-md">
                Home
              </Link>
            </li>
            <li>
              <Link href="/about" @click="closeMobileMenu" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-md">
                About
              </Link>
            </li>
            <li>
              <Link href="/products" @click="closeMobileMenu" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-md">
                Products
              </Link>
            </li>
            <li>
              <Link href="/custom-builder" @click="closeMobileMenu" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-md">
                Custom Builder
              </Link>
            </li>
            <li>
              <Link href="/quotes" @click="closeMobileMenu" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-md">
                Get Quote
              </Link>
            </li>
            <li>
              <Link href="/contact" @click="closeMobileMenu" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-md">
                Contact
              </Link>
            </li>
          </ul>
        </nav>
        
        <!-- Action Buttons -->
        <div class="mt-8 space-y-4">
          <Link href="/login" @click="closeMobileMenu" class="block w-full text-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-orange-600 border border-gray-300 rounded-md hover:border-orange-300">
            Sign In
          </Link>
          <Link href="/register" @click="closeMobileMenu" class="block w-full text-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 rounded-md">
            Get Started
          </Link>
        </div>
        
        <!-- Contact Info -->
        <div class="mt-8 pt-8 border-t border-gray-200">
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <Phone class="h-4 w-4 mr-2" />
              {{ companySettings.company_phone || '+1-XXX-XXX-XXXX' }}
            </div>
            <div class="flex items-center text-sm text-gray-600">
              <Mail class="h-4 w-4 mr-2" />
              {{ companySettings.company_email || 'info@solafriq.com' }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
