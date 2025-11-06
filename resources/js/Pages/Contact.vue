<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Mail, Phone, MapPin, Send, MessageSquare, Clock, CheckCircle } from 'lucide-vue-next';

const page = usePage();

// Get company settings from shared data
const companySettings = computed(() => page.props.companySettings || {});

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
  projectType: 'residential'
});

const isSubmitting = ref(false);
const isSubmitted = ref(false);

const submitForm = async () => {
  isSubmitting.value = true;

  // Simulate form submission
  setTimeout(() => {
    isSubmitting.value = false;
    isSubmitted.value = true;

    // Reset form after 3 seconds
    setTimeout(() => {
      isSubmitted.value = false;
      form.value = {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
        projectType: 'residential'
      };
    }, 3000);
  }, 1500);
};

const contactInfo = computed(() => [
  {
    icon: Mail,
    title: "Email Us",
    content: companySettings.value.company_email || "info@solafriq.com",
    description: "Send us your questions anytime",
    gradient: "from-blue-500 to-blue-600"
  },
  {
    icon: Phone,
    title: "Call Us",
    content: companySettings.value.company_phone || "+1 (555) 123-4567",
    description: "Mon-Fri 9AM-6PM EST",
    gradient: "from-green-500 to-green-600"
  },
  {
    icon: MapPin,
    title: "Visit Us",
    content: companySettings.value.company_address || "123 Solar Street, Energy City",
    description: "Schedule an appointment",
    gradient: "from-orange-500 to-orange-600"
  }
]);
</script>

<template>
  <MainLayout>
    <!-- Hero Section -->
    <section class="relative min-h-[60vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-gray-50 via-white to-orange-50">
      <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-72 h-72 bg-gradient-to-br from-orange-400/20 to-yellow-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-br from-blue-900/10 to-blue-800/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
      </div>

      <div class="container mx-auto px-4 pt-20 relative z-10">
        <div class="text-center space-y-6">
          <div class="inline-flex items-center px-6 py-3 bg-orange-100 rounded-full text-orange-700 text-sm font-medium">
            <MessageSquare class="w-4 h-4 mr-2" />
            Get In Touch
          </div>

          <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
            Request Your
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Solar Quote
            </span>
          </h1>

          <p class="text-xl text-gray-600 leading-relaxed max-w-3xl mx-auto">
            Ready to transform your energy future? Get a personalized quote for your solar system and start saving today.
            Our experts are here to help you every step of the way.
          </p>
        </div>
      </div>
    </section>

    <!-- Contact Form & Info Section -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-16 max-w-7xl mx-auto">
          <!-- Contact Form -->
          <div class="order-2 lg:order-1">
            <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100">
              <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Get Your Custom Quote</h2>
                <p class="text-gray-600">Fill out the form below and we'll get back to you within 24 hours with a personalized solar solution.</p>
              </div>

              <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                      placeholder="Your full name"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                      placeholder="your@email.com"
                    />
                  </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input
                      v-model="form.phone"
                      type="tel"
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                      placeholder="+1 (555) 123-4567"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Project Type</label>
                    <select
                      v-model="form.projectType"
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                    >
                      <option value="residential">Residential</option>
                      <option value="commercial">Commercial</option>
                      <option value="industrial">Industrial</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                  <input
                    v-model="form.subject"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                    placeholder="Solar system inquiry"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                  <textarea
                    v-model="form.message"
                    required
                    rows="5"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 resize-none"
                    placeholder="Tell us about your energy needs, property size, and any specific requirements..."
                  ></textarea>
                </div>

                <button
                  type="submit"
                  :disabled="isSubmitting || isSubmitted"
                  :class="[
                    'w-full py-4 px-6 rounded-xl font-semibold text-white transition-all duration-300 transform',
                    isSubmitted
                      ? 'bg-green-500 hover:bg-green-600'
                      : 'bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 hover:scale-105'
                  ]"
                >
                  <div v-if="isSubmitting" class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent mr-3"></div>
                    Sending Message...
                  </div>
                  <div v-else-if="isSubmitted" class="flex items-center justify-center">
                    <CheckCircle class="w-5 h-5 mr-2" />
                    Message Sent Successfully!
                  </div>
                  <div v-else class="flex items-center justify-center">
                    <Send class="w-5 h-5 mr-2" />
                    Send Message & Get Quote
                  </div>
                </button>
              </form>
            </div>
          </div>

          <!-- Contact Information -->
          <div class="order-1 lg:order-2 space-y-8">
            <div>
              <h2 class="text-3xl font-bold text-gray-900 mb-6">Let's Start Your Solar Journey</h2>
              <p class="text-lg text-gray-600 leading-relaxed mb-8">
                Our solar experts are ready to help you design the perfect energy solution for your needs.
                We provide comprehensive consultations, custom system designs, and ongoing support.
              </p>
            </div>

            <div class="space-y-6">
              <div v-for="(info, index) in contactInfo" :key="index" class="group">
                <div class="flex items-start space-x-4 p-6 rounded-2xl hover:bg-gray-50 transition-all duration-300">
                  <div :class="['w-12 h-12 bg-gradient-to-br rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300', info.gradient]">
                    <component :is="info.icon" class="h-6 w-6 text-white" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ info.title }}</h3>
                    <p class="text-orange-600 font-medium">{{ info.content }}</p>
                    <p class="text-sm text-gray-600">{{ info.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Response Time -->
            <div class="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-2xl p-6 border border-orange-100">
              <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-full flex items-center justify-center">
                  <Clock class="h-5 w-5 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Quick Response Guarantee</h3>
              </div>
              <p class="text-gray-600">
                We respond to all inquiries within 4 business hours. For urgent matters,
                please call us directly at the number above.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-24 bg-gradient-to-br from-gray-50 to-blue-50">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-16">
          <div class="inline-flex items-center px-4 py-2 bg-white rounded-full text-gray-700 text-sm font-medium shadow-sm">
            Frequently Asked Questions
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            Got Questions?
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              We've Got Answers
            </span>
          </h2>
        </div>

        <div class="max-w-4xl mx-auto space-y-6">
          <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">How long does installation take?</h3>
            <p class="text-gray-600">Most residential installations are completed within 1-3 days, depending on system size and complexity.</p>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Do you offer financing options?</h3>
            <p class="text-gray-600">Yes! We offer flexible payment plans and financing options. Contact us to discuss payment arrangements that work for you.</p>
          </div>

          <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">What warranty do you provide?</h3>
            <p class="text-gray-600">All our solar systems come with a comprehensive 25-year warranty covering panels, inverters, and installation.</p>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>