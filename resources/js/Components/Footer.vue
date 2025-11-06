<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Facebook, Twitter, Instagram, Linkedin, Mail, Phone, MapPin, ArrowRight } from 'lucide-vue-next';

const page = usePage();

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {});

const socialLinks = [Facebook, Twitter, Instagram, Linkedin];

const solutions = [
  { name: "Solar Packages", href: "/packages" },
  { name: "Custom Builder", href: "/builder" },
  { name: "Installation", href: "/installation" },
  { name: "Maintenance", href: "/maintenance" },
  { name: "Support", href: "/support" },
];

const services = [
  { name: "Free Consultation", href: "/consultation" },
  { name: "Financing Options", href: "/financing" },
  { name: "Gift Solar", href: "/gift" },
  { name: "Client Dashboard", href: "/dashboard" },
  { name: "Warranty", href: "/warranty" },
  { name: "Training", href: "/training" },
];

const contactInfo = computed(() => [
  { icon: Mail, text: companySettings.value.company_email || "hello@solafriq.com" },
  { icon: Phone, text: companySettings.value.company_phone || "+1 800 123 4567" },
  { icon: MapPin, text: companySettings.value.company_address || "New York, USA" },
]);

const footerLinks = ["Privacy Policy", "Terms of Service", "Cookie Policy"];
</script>

<template>
  <footer class="bg-gradient-to-br from-gray-900 to-blue-900 text-white">
    <div class="container mx-auto px-4 py-20">
      <div class="grid lg:grid-cols-4 gap-12">
        <!-- Company Info -->
        <div class="space-y-6">
          <img
            :src="companySettings.company_logo || '/images/solafriq-logo.svg'"
            :alt="companySettings.company_name || 'SolaFriq'"
            class="h-9 w-auto brightness-0 invert"
          />
          <p class="text-gray-300 leading-relaxed">
            Revolutionizing Africa's energy landscape with innovative solar solutions, flexible financing, and
            world-class support.
          </p>
          <div class="flex space-x-4">
            <button
              v-for="(Icon, index) in socialLinks"
              :key="index"
              class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-500/10 rounded-full transition-all duration-300"
            >
              <component :is="Icon" class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="space-y-6">
          <h3 class="text-xl font-bold text-white">Solutions</h3>
          <ul class="space-y-3">
            <li v-for="item in solutions" :key="item.name">
              <Link
                :href="item.href"
                class="text-gray-300 hover:text-orange-500 transition-colors duration-200 flex items-center group"
              >
                <ArrowRight class="h-4 w-4 mr-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                {{ item.name }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Services -->
        <div class="space-y-6">
          <h3 class="text-xl font-bold text-white">Services</h3>
          <ul class="space-y-3">
            <li v-for="item in services" :key="item.name">
              <Link
                :href="item.href"
                class="text-gray-300 hover:text-orange-500 transition-colors duration-200 flex items-center group"
              >
                <ArrowRight class="h-4 w-4 mr-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                {{ item.name }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Contact & Newsletter -->
        <div class="space-y-6">
          <h3 class="text-xl font-bold text-white">Get in Touch</h3>
          <div class="space-y-4">
            <div v-for="(item, index) in contactInfo" :key="index" class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-orange-500/20 rounded-full flex items-center justify-center">
                <component :is="item.icon" class="h-5 w-5 text-orange-500" />
              </div>
              <span class="text-gray-300">{{ item.text }}</span>
            </div>
          </div>

          <div class="space-y-4">
            <p class="text-gray-300 font-medium">Stay Updated</p>
            <div class="flex space-x-2">
              <input
                placeholder="Enter your email"
                class="bg-white/10 border-white/20 text-white placeholder:text-gray-400 rounded-full w-full px-4 py-2"
              />
              <button class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-full px-6">
                Subscribe
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="border-t border-white/10 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-400">© 2024 SolaFriq. All rights reserved.</p>
        <div class="flex space-x-8 mt-4 md:mt-0">
          <Link v-for="item in footerLinks" :key="item" href="#" class="text-gray-300 hover:text-orange-500 transition-colors duration-200">
            {{ item }}
          </Link>
        </div>
      </div>
    </div>
  </footer>
</template>
