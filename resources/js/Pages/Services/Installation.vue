<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Wrench, CheckCircle, Clock, Shield, Users, Award } from 'lucide-vue-next';
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
      <!-- Hero Section -->
      <section class="relative py-20 px-4 bg-gradient-to-r from-orange-600 to-yellow-500">
        <div class="container mx-auto max-w-6xl">
          <div class="text-center text-white">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-6">
              <Wrench class="h-10 w-10" />
            </div>
            <h1 class="text-5xl font-bold mb-6">Professional Installation Services</h1>
            <p class="text-xl max-w-3xl mx-auto leading-relaxed">
              Expert installation by certified technicians ensuring your solar system performs at its best from day one
            </p>
          </div>
        </div>
      </section>

      <!-- Main Content -->
      <section class="py-16 px-4">
        <div class="container mx-auto max-w-6xl">
          <!-- Overview -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Why Choose Our Installation Service?</h2>
            <p class="text-lg text-gray-600 mb-8">
              Our installation team brings years of experience and expertise to every project. We ensure that your solar system is installed safely, efficiently, and in compliance with all local regulations and standards.
            </p>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Users class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Certified Technicians</h3>
                  <p class="text-gray-600 text-sm">All our installers are certified and regularly trained on the latest installation techniques</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Clock class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Quick Installation</h3>
                  <p class="text-gray-600 text-sm">Most residential installations completed within 1-3 days with minimal disruption</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Shield class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Safety First</h3>
                  <p class="text-gray-600 text-sm">Strict adherence to safety protocols and building codes for your protection</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Award class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Quality Guarantee</h3>
                  <p class="text-gray-600 text-sm">Workmanship warranty on all installations for your peace of mind</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <CheckCircle class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Complete Service</h3>
                  <p class="text-gray-600 text-sm">From site assessment to final testing and commissioning</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Wrench class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Professional Equipment</h3>
                  <p class="text-gray-600 text-sm">State-of-the-art tools and equipment for precise installation</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Installation Process -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Our Installation Process</h2>
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">1</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Site Assessment</h3>
                  <p class="text-gray-600">Comprehensive evaluation of your property to determine optimal panel placement and system configuration</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">2</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">System Design</h3>
                  <p class="text-gray-600">Custom design tailored to your energy needs and property specifications</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">3</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Permits & Approvals</h3>
                  <p class="text-gray-600">We handle all necessary permits and regulatory approvals on your behalf</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">4</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Installation</h3>
                  <p class="text-gray-600">Professional installation of panels, inverters, batteries, and all system components</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">5</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Testing & Commissioning</h3>
                  <p class="text-gray-600">Thorough testing to ensure optimal performance and safety compliance</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">6</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Training & Handover</h3>
                  <p class="text-gray-600">Complete system walkthrough and training on operation and monitoring</p>
                </div>
              </div>
            </div>
          </div>

          <!-- CTA Section -->
          <div class="bg-gradient-to-r from-orange-600 to-yellow-500 rounded-2xl shadow-xl p-8 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
            <p class="text-xl mb-8">Contact us today for a free consultation and installation quote</p>
            <a href="/contact" class="inline-block bg-white text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors">
              Request Installation Quote
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>
