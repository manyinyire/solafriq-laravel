<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, ShoppingCart, User, LogOut, Settings, Sun, Shield, Phone, Mail, ChevronDown, FileText } from 'lucide-vue-next';

const page = usePage();

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {});

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
                <div class="absolute top-full left-0 w-[500px] bg-white shadow-lg rounded-lg p-6 hidden group-hover:grid gap-3 z-50">
                  <Link v-for="subItem in item.items" :key="subItem.title" :href="subItem.href" class="block select-none space-y-1 rounded-lg p-3 leading-none no-underline outline-none transition-colors hover:bg-orange-50 hover:text-orange-600 focus:bg-orange-50 focus:text-orange-600">
                    <div class="text-sm font-medium leading-none">{{ subItem.title }}</div>
                    <p class="line-clamp-2 text-sm leading-snug text-gray-500">
                      {{ subItem.description }}
                    </p>
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

            <!-- Mobile Menu -->
            <button class="lg:hidden hover:bg-orange-50 hover:text-orange-600 p-2 rounded-full">
              <Menu class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </header>
  </div>
</template>
