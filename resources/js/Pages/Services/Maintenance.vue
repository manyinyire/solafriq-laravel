<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Settings, CheckCircle, Shield, Clock, Wrench, Phone } from 'lucide-vue-next';
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
      <!-- Hero Section -->
      <section class="relative py-20 px-4 bg-gradient-to-r from-orange-600 to-yellow-500">
        <div class="container mx-auto max-w-6xl">
          <div class="text-center text-white">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-6">
              <Settings class="h-10 w-10" />
            </div>
            <h1 class="text-5xl font-bold mb-6">Maintenance & Support Services</h1>
            <p class="text-xl max-w-3xl mx-auto leading-relaxed">
              Keep your solar system running at peak performance with our comprehensive maintenance and support services
            </p>
          </div>
        </div>
      </section>

      <!-- Main Content -->
      <section class="py-16 px-4">
        <div class="container mx-auto max-w-6xl">
          <!-- Overview -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Maximize Your Solar Investment</h2>
            <p class="text-lg text-gray-600 mb-8">
              Regular maintenance ensures your solar system operates efficiently, extends its lifespan, and protects your warranty coverage. Our expert team provides comprehensive support to keep your system performing optimally year after year.
            </p>

            <!-- Service Plans -->
            <div class="grid md:grid-cols-3 gap-6">
              <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-orange-500 transition-colors">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Basic Plan</h3>
                <p class="text-gray-600 mb-4">Essential maintenance for optimal performance</p>
                <ul class="space-y-3 mb-6">
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Annual system inspection</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Panel cleaning (once per year)</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Performance monitoring</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Email support</span>
                  </li>
                </ul>
              </div>

              <div class="border-2 border-orange-500 rounded-xl p-6 bg-orange-50 relative">
                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-orange-500 text-white px-4 py-1 rounded-full text-sm font-semibold">
                  Popular
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Standard Plan</h3>
                <p class="text-gray-600 mb-4">Comprehensive care for your system</p>
                <ul class="space-y-3 mb-6">
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Bi-annual system inspection</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Panel cleaning (twice per year)</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">24/7 performance monitoring</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Priority phone support</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Minor repairs included</span>
                  </li>
                </ul>
              </div>

              <div class="border-2 border-gray-200 rounded-xl p-6 hover:border-orange-500 transition-colors">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Premium Plan</h3>
                <p class="text-gray-600 mb-4">Complete peace of mind coverage</p>
                <ul class="space-y-3 mb-6">
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Quarterly system inspection</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Panel cleaning (quarterly)</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">Real-time monitoring & alerts</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">24/7 emergency support</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">All repairs included</span>
                  </li>
                  <li class="flex items-start">
                    <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                    <span class="text-sm">System upgrades discount</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- What We Check -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">What We Check During Maintenance</h2>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <CheckCircle class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Panel Inspection</h3>
                  <p class="text-gray-600 text-sm">Check for cracks, damage, shading issues, and optimal positioning</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Wrench class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Electrical Connections</h3>
                  <p class="text-gray-600 text-sm">Verify all wiring, connections, and junction boxes are secure</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Settings class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Inverter Performance</h3>
                  <p class="text-gray-600 text-sm">Test inverter efficiency and check for error codes or issues</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Shield class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Safety Systems</h3>
                  <p class="text-gray-600 text-sm">Verify grounding, surge protection, and safety disconnects</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Clock class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Performance Analysis</h3>
                  <p class="text-gray-600 text-sm">Review energy production data and identify optimization opportunities</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Phone class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Monitoring System</h3>
                  <p class="text-gray-600 text-sm">Ensure monitoring equipment is functioning and reporting accurately</p>
                </div>
              </div>
            </div>
          </div>

          <!-- CTA Section -->
          <div class="bg-gradient-to-r from-orange-600 to-yellow-500 rounded-2xl shadow-xl p-8 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Protect Your Investment</h2>
            <p class="text-xl mb-8">Schedule your maintenance service today and keep your system running efficiently</p>
            <a href="/contact" class="inline-block bg-white text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors">
              Schedule Maintenance
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>
