<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { DollarSign, CheckCircle, CreditCard, Calendar, TrendingUp, Shield } from 'lucide-vue-next';
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
      <!-- Hero Section -->
      <section class="relative py-20 px-4 bg-gradient-to-r from-orange-600 to-yellow-500">
        <div class="container mx-auto max-w-6xl">
          <div class="text-center text-white">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-6">
              <DollarSign class="h-10 w-10" />
            </div>
            <h1 class="text-5xl font-bold mb-6">Flexible Financing Options</h1>
            <p class="text-xl max-w-3xl mx-auto leading-relaxed">
              Make solar energy affordable with our flexible payment plans and financing solutions
            </p>
          </div>
        </div>
      </section>

      <!-- Main Content -->
      <section class="py-16 px-4">
        <div class="container mx-auto max-w-6xl">
          <!-- Overview -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Affordable Solar for Everyone</h2>
            <p class="text-lg text-gray-600 mb-8">
              Don't let upfront costs prevent you from enjoying the benefits of solar energy. We offer multiple financing options to make solar accessible and affordable for homes and businesses of all sizes.
            </p>
          </div>

          <!-- Payment Options -->
          <div class="grid md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-transparent hover:border-orange-500 transition-colors">
              <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center mb-6">
                <CreditCard class="h-8 w-8 text-orange-600" />
              </div>
              <h3 class="text-2xl font-bold text-gray-900 mb-4">Cash Purchase</h3>
              <p class="text-gray-600 mb-6">Pay in full and enjoy immediate savings with no interest charges</p>
              <ul class="space-y-3">
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Lowest total cost</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Immediate ROI</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">No monthly payments</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Special discounts available</span>
                </li>
              </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-orange-500 relative">
              <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-orange-500 text-white px-4 py-1 rounded-full text-sm font-semibold">
                Most Popular
              </div>
              <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center mb-6">
                <Calendar class="h-8 w-8 text-orange-600" />
              </div>
              <h3 class="text-2xl font-bold text-gray-900 mb-4">Installment Plans</h3>
              <p class="text-gray-600 mb-6">Spread payments over 6-24 months with flexible terms</p>
              <ul class="space-y-3">
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">No credit check required</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Low monthly payments</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Quick approval process</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Early payment options</span>
                </li>
              </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 border-2 border-transparent hover:border-orange-500 transition-colors">
              <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center mb-6">
                <TrendingUp class="h-8 w-8 text-orange-600" />
              </div>
              <h3 class="text-2xl font-bold text-gray-900 mb-4">Solar Loans</h3>
              <p class="text-gray-600 mb-6">Long-term financing with competitive interest rates</p>
              <ul class="space-y-3">
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Terms up to 10 years</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Competitive rates</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Preserve cash flow</span>
                </li>
                <li class="flex items-start">
                  <CheckCircle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" />
                  <span class="text-sm">Tax benefits available</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Benefits Section -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Why Finance Your Solar System?</h2>
            <div class="grid md:grid-cols-2 gap-8">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <DollarSign class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Start Saving Immediately</h3>
                  <p class="text-gray-600 text-sm">Begin reducing your electricity bills right away, even while making payments</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <Shield class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Protect Against Rate Increases</h3>
                  <p class="text-gray-600 text-sm">Lock in your energy costs and avoid future utility rate hikes</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <TrendingUp class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Increase Property Value</h3>
                  <p class="text-gray-600 text-sm">Solar installations can increase your property value by up to 4%</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <CheckCircle class="h-6 w-6 text-orange-600" />
                  </div>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Flexible Terms</h3>
                  <p class="text-gray-600 text-sm">Choose payment terms that fit your budget and financial goals</p>
                </div>
              </div>
            </div>
          </div>

          <!-- How It Works -->
          <div class="bg-white rounded-2xl shadow-xl p-8 mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">How Our Financing Works</h2>
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">1</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Get a Quote</h3>
                  <p class="text-gray-600">Request a free consultation and receive a detailed system proposal with pricing</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">2</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Choose Your Plan</h3>
                  <p class="text-gray-600">Select the financing option that best fits your budget and preferences</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">3</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Quick Approval</h3>
                  <p class="text-gray-600">Most financing applications are approved within 24-48 hours</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">4</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Installation</h3>
                  <p class="text-gray-600">We schedule and complete your installation at your convenience</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-full flex items-center justify-center font-bold">5</div>
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">Start Saving</h3>
                  <p class="text-gray-600">Begin enjoying lower energy bills while making affordable monthly payments</p>
                </div>
              </div>
            </div>
          </div>

          <!-- CTA Section -->
          <div class="bg-gradient-to-r from-orange-600 to-yellow-500 rounded-2xl shadow-xl p-8 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Explore Your Options?</h2>
            <p class="text-xl mb-8">Contact us today to discuss financing solutions that work for you</p>
            <a href="/contact" class="inline-block bg-white text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition-colors">
              Get Financing Information
            </a>
          </div>
        </div>
      </section>
    </div>
  </MainLayout>
</template>
