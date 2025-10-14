<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Lightbulb, CheckCircle, Users, FileText, Calculator, Phone } from 'lucide-vue-next';
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
      <!-- Hero Section -->
      <section class="relative py-20 px-4 bg-gradient-to-r from-orange-600 to-yellow-500">
        <div class="container mx-auto max-w-6xl">
          <div class="text-center text-white">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-6">
              <Lightbulb class="h-10 w-10" />
            </div>
            <h1 class="text-5xl font-bold mb-6">Free Solar Consultation</h1>
            <p class="text-xl max-w-3xl mx-auto leading-relaxed">
              Expert guidance to help you make informed decisions about your solar energy investment
            </p>
          </div>
        </div>
      </section>

      <!-- Main Content -->
      <section class="py-16 px-4">
        <div class="container mx-auto max-w-6xl">
          <!-- Overview -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">What to Expect</h2>
            <p class="text-lg text-gray-600 mb-8">
              Our free consultation service provides you with all the information you need to make the right choice for your solar energy needs. We'll assess your property, analyze your energy consumption, and recommend the best solution for your budget and goals.
            </p>

            <!-- Consultation Process -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Phone class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Initial Contact</h3>
                  <p class="text-gray-600 text-sm">Schedule a convenient time for your consultation via phone, email, or online form</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <FileText class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Energy Assessment</h3>
                  <p class="text-gray-600 text-sm">Review your current energy bills and consumption patterns</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Users class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Site Visit</h3>
                  <p class="text-gray-600 text-sm">On-site evaluation of your property, roof condition, and sun exposure</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Calculator class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Custom Proposal</h3>
                  <p class="text-gray-600 text-sm">Detailed system design with cost estimates and savings projections</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <CheckCircle class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Financial Options</h3>
                  <p class="text-gray-600 text-sm">Explore financing, payment plans, and available incentives</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Lightbulb class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Expert Advice</h3>
                  <p class="text-gray-600 text-sm">Answer all your questions and address any concerns</p>
                </div>
              </div>
            </div>
          </div>

          <!-- What We Cover -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Topics We'll Cover</h2>
            <div class="space-y-4">
              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">System Sizing & Design</h3>
                  <p class="text-gray-600">Determine the optimal system size based on your energy needs and available space</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Cost & Savings Analysis</h3>
                  <p class="text-gray-600">Detailed breakdown of installation costs, potential savings, and return on investment</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Technology Options</h3>
                  <p class="text-gray-600">Compare different panel types, inverters, and battery storage solutions</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Installation Timeline</h3>
                  <p class="text-gray-600">Understand the installation process and expected completion timeframe</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Maintenance Requirements</h3>
                  <p class="text-gray-600">Learn about ongoing maintenance needs and warranty coverage</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Permits & Regulations</h3>
                  <p class="text-gray-600">Overview of required permits, inspections, and local regulations</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Financing & Incentives</h3>
                  <p class="text-gray-600">Explore payment options, tax credits, and available rebates</p>
                </div>
              </div>

              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 class="font-semibold text-gray-900 mb-1">Environmental Impact</h3>
                  <p class="text-gray-600">Calculate your carbon footprint reduction and environmental benefits</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Why Choose Us -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Why Choose Our Consultation?</h2>
            <div class="grid md:grid-cols-2 gap-8">
              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">No Obligation</h3>
                <p class="text-gray-600">Our consultation is completely free with no pressure to purchase. We're here to educate and help you make the best decision for your situation.</p>
              </div>

              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Expert Knowledge</h3>
                <p class="text-gray-600">Our consultants have years of experience in solar energy and stay current with the latest technology and industry trends.</p>
              </div>

              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Personalized Solutions</h3>
                <p class="text-gray-600">We don't believe in one-size-fits-all. Every recommendation is tailored to your specific needs, budget, and goals.</p>
              </div>

              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Transparent Pricing</h3>
                <p class="text-gray-600">Clear, detailed quotes with no hidden fees. You'll know exactly what you're getting and what it costs.</p>
              </div>
            </div>
          </div>

          <!-- CTA Section -->
          <div class="bg-gradient-to-r from-orange-600 to-yellow-500 rounded-2xl shadow-xl p-8 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Go Solar?</h2>
            <p class="text-xl mb-8">Schedule your free consultation today and take the first step towards energy independence</p>
            <a href="/contact" class="inline-block bg-white text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors">
              Schedule Free Consultation
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>
