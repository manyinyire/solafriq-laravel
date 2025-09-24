<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  Zap,
  Home,
  Building2,
  Factory,
  Settings,
  Shield,
  Wrench,
  Clock,
  CheckCircle,
  ArrowRight,
  Sun,
  Battery,
  Monitor,
  Lightbulb,
  Phone,
  Users,
  Award,
  TrendingUp,
  Leaf,
  DollarSign,
  Calendar,
  FileText,
  Star,
  Target
} from 'lucide-vue-next';

const services = [
  {
    id: 'residential',
    icon: Home,
    title: 'Residential Solar Systems',
    shortDesc: 'Complete solar solutions for homes and apartments',
    description: 'Transform your home into an energy-independent powerhouse with our residential solar systems. From small apartments to large family homes, we design and install custom solutions that meet your specific energy needs while reducing your electricity bills.',
    features: [
      'Custom system design based on energy consumption',
      'High-efficiency solar panels with 25-year warranty',
      'Smart inverters with monitoring capabilities',
      'Battery backup options for uninterrupted power',
      'Professional installation by certified technicians',
      'Ongoing maintenance and support services'
    ],
    gradient: 'from-blue-500 to-blue-600',
    pricing: 'Starting from $2,500',
    duration: '1-2 days installation'
  },
  {
    id: 'commercial',
    icon: Building2,
    title: 'Commercial Solar Solutions',
    shortDesc: 'Scalable solar systems for businesses and offices',
    description: 'Power your business with reliable, cost-effective solar energy. Our commercial solutions are designed to reduce operational costs while demonstrating your commitment to sustainability. Perfect for offices, retail stores, and small to medium enterprises.',
    features: [
      'Load analysis and energy audit services',
      'Scalable systems from 5kW to 100kW+',
      'Grid-tie and hybrid system options',
      'Energy management and monitoring systems',
      'Tax incentive consultation and support',
      'Maintenance contracts with guaranteed performance'
    ],
    gradient: 'from-green-500 to-emerald-600',
    pricing: 'Starting from $8,000',
    duration: '3-5 days installation'
  },
  {
    id: 'industrial',
    icon: Factory,
    title: 'Industrial Solar Systems',
    shortDesc: 'Large-scale solar installations for manufacturing and industry',
    description: 'Reduce energy costs and environmental impact with industrial-scale solar installations. Our team specializes in complex projects for manufacturing facilities, warehouses, and large commercial complexes.',
    features: [
      'Comprehensive feasibility studies',
      'Megawatt-scale system design and installation',
      'Integration with existing electrical infrastructure',
      'Advanced monitoring and control systems',
      'Performance guarantees and insurance coverage',
      'Dedicated project management and support'
    ],
    gradient: 'from-purple-500 to-purple-600',
    pricing: 'Custom pricing available',
    duration: '2-4 weeks installation'
  },
  {
    id: 'maintenance',
    icon: Settings,
    title: 'Maintenance & Support',
    shortDesc: 'Keep your solar system running at peak performance',
    description: 'Maximize your solar investment with our comprehensive maintenance and support services. Regular maintenance ensures optimal performance, extends system life, and protects your warranty coverage.',
    features: [
      'Scheduled cleaning and inspection services',
      'Performance monitoring and diagnostics',
      'Component replacement and repairs',
      '24/7 technical support hotline',
      'System upgrades and expansions',
      'Insurance claim assistance when needed'
    ],
    gradient: 'from-orange-500 to-yellow-500',
    pricing: 'From $200/year',
    duration: 'Ongoing service'
  }
];

const additionalServices = [
  {
    icon: FileText,
    title: 'Energy Audits',
    description: 'Comprehensive analysis of your energy consumption patterns to optimize solar system design.',
    gradient: 'from-cyan-500 to-blue-500'
  },
  {
    icon: DollarSign,
    title: 'Financing Solutions',
    description: 'Flexible payment plans and financing options to make solar energy accessible to everyone.',
    gradient: 'from-green-500 to-teal-500'
  },
  {
    icon: Shield,
    title: 'Insurance Services',
    description: 'Comprehensive insurance coverage for your solar investment with competitive rates.',
    gradient: 'from-indigo-500 to-purple-500'
  },
  {
    icon: Monitor,
    title: 'System Monitoring',
    description: 'Real-time monitoring and alerts to ensure your system is always performing optimally.',
    gradient: 'from-pink-500 to-rose-500'
  },
  {
    icon: Wrench,
    title: 'Emergency Repairs',
    description: '24/7 emergency repair services to minimize downtime and protect your energy supply.',
    gradient: 'from-red-500 to-orange-500'
  },
  {
    icon: TrendingUp,
    title: 'System Upgrades',
    description: 'Expand or upgrade your existing solar system as your energy needs grow over time.',
    gradient: 'from-violet-500 to-purple-500'
  }
];

const processSteps = [
  {
    step: '01',
    title: 'Consultation & Assessment',
    description: 'Initial consultation to understand your energy needs, site assessment, and custom system design.',
    icon: Target,
    duration: '1-2 days'
  },
  {
    step: '02',
    title: 'Custom Proposal',
    description: 'Detailed proposal with system specifications, pricing, financing options, and timeline.',
    icon: FileText,
    duration: '2-3 days'
  },
  {
    step: '03',
    title: 'Installation Planning',
    description: 'Permit acquisition, equipment procurement, and installation scheduling with our certified team.',
    icon: Calendar,
    duration: '1-2 weeks'
  },
  {
    step: '04',
    title: 'Professional Installation',
    description: 'Expert installation by certified technicians with minimal disruption to your daily operations.',
    icon: Wrench,
    duration: '1-5 days'
  },
  {
    step: '05',
    title: 'Testing & Commissioning',
    description: 'Comprehensive testing, system commissioning, and training on system operation and monitoring.',
    icon: CheckCircle,
    duration: '1 day'
  },
  {
    step: '06',
    title: 'Ongoing Support',
    description: 'Continuous monitoring, maintenance services, and customer support throughout the system lifetime.',
    icon: Phone,
    duration: 'Ongoing'
  }
];

const selectedService = ref(null);

const selectService = (service) => {
  selectedService.value = selectedService.value === service.id ? null : service.id;
};
</script>

<template>
  <MainLayout>
    <!-- Hero Section -->
    <section class="relative min-h-[80vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-gray-50 via-white to-blue-50">
      <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-72 h-72 bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-br from-orange-400/10 to-yellow-400/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-br from-blue-500/5 to-purple-500/5 rounded-full blur-3xl"></div>
      </div>

      <div class="container mx-auto px-4 pt-20 relative z-10">
        <div class="text-center space-y-8 max-w-4xl mx-auto">
          <div class="inline-flex items-center px-6 py-3 bg-blue-100 rounded-full text-blue-700 text-sm font-medium">
            <Zap class="w-4 h-4 mr-2" />
            Solar Services
          </div>

          <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 leading-tight">
            Complete Solar
            <span class="block bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Energy Solutions
            </span>
          </h1>

          <p class="text-xl text-gray-600 leading-relaxed max-w-3xl mx-auto">
            From initial consultation to ongoing maintenance, we provide comprehensive solar services
            designed to meet your unique energy needs and maximize your return on investment.
          </p>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 pt-8">
            <div class="text-center">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <Award class="h-6 w-6 text-white" />
              </div>
              <div class="text-2xl font-bold text-gray-900">Certified</div>
              <div class="text-sm text-gray-600">Installers</div>
            </div>
            <div class="text-center">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <Shield class="h-6 w-6 text-white" />
              </div>
              <div class="text-2xl font-bold text-gray-900">25 Year</div>
              <div class="text-sm text-gray-600">Warranty</div>
            </div>
            <div class="text-center">
              <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <Clock class="h-6 w-6 text-white" />
              </div>
              <div class="text-2xl font-bold text-gray-900">24/7</div>
              <div class="text-sm text-gray-600">Support</div>
            </div>
            <div class="text-center">
              <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <Star class="h-6 w-6 text-white" />
              </div>
              <div class="text-2xl font-bold text-gray-900">98%</div>
              <div class="text-sm text-gray-600">Satisfaction</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Services Section -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full text-gray-700 text-sm font-medium">
            Core Services
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            Comprehensive Solar
            <span class="block bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Service Portfolio
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            From residential rooftops to industrial installations, we deliver tailored solar solutions for every need
          </p>
        </div>

        <div class="space-y-8 max-w-6xl mx-auto">
          <div v-for="(service, index) in services" :key="service.id" :id="service.id" class="group scroll-mt-20">
            <div class="bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 overflow-hidden">
              <div class="p-8">
                <div class="flex flex-col lg:flex-row items-start space-y-6 lg:space-y-0 lg:space-x-8">
                  <!-- Service Icon & Basic Info -->
                  <div class="flex-shrink-0">
                    <div :class="['w-20 h-20 bg-gradient-to-br rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300', service.gradient]">
                      <component :is="service.icon" class="h-10 w-10 text-white" />
                    </div>
                    <div class="space-y-2">
                      <div class="text-sm text-gray-500">Starting Price</div>
                      <div class="text-lg font-bold text-gray-900">{{ service.pricing }}</div>
                      <div class="text-sm text-gray-500">{{ service.duration }}</div>
                    </div>
                  </div>

                  <!-- Service Content -->
                  <div class="flex-1 space-y-6">
                    <div>
                      <h3 class="text-3xl font-bold text-gray-900 mb-3">{{ service.title }}</h3>
                      <p class="text-lg text-gray-600 leading-relaxed">{{ service.description }}</p>
                    </div>

                    <!-- Service Features -->
                    <div>
                      <button
                        @click="selectService(service)"
                        class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium transition-colors duration-200 mb-4"
                      >
                        {{ selectedService === service.id ? 'Hide' : 'View' }} Features
                        <ArrowRight :class="['ml-2 h-4 w-4 transition-transform duration-200', selectedService === service.id ? 'rotate-90' : '']" />
                      </button>

                      <div v-if="selectedService === service.id" class="grid md:grid-cols-2 gap-4 bg-gray-50 rounded-2xl p-6">
                        <div v-for="(feature, fIndex) in service.features" :key="fIndex" class="flex items-start space-x-3">
                          <CheckCircle class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                          <span class="text-gray-700">{{ feature }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- CTA -->
                    <div class="flex flex-col sm:flex-row gap-4">
                      <a
                        href="/contact"
                        :class="['inline-flex items-center bg-gradient-to-r text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105', service.gradient]"
                      >
                        Get Quote
                        <ArrowRight class="ml-2 h-4 w-4" />
                      </a>
                      <button class="inline-flex items-center border-2 border-gray-200 hover:border-blue-500 text-gray-700 hover:text-blue-600 px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                        <Phone class="mr-2 h-4 w-4" />
                        Call for Details
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Additional Services Section -->
    <section id="additional-services" class="py-24 bg-gradient-to-br from-gray-50 to-blue-50 scroll-mt-20">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-white rounded-full text-gray-700 text-sm font-medium shadow-sm">
            Additional Services
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            Supporting Your
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Solar Journey
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Beyond installation, we offer comprehensive support services to maximize your solar investment
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
          <div v-for="(service, index) in additionalServices" :key="index" class="group">
            <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 h-full">
              <div :class="['w-16 h-16 bg-gradient-to-br rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300', service.gradient]">
                <component :is="service.icon" class="h-8 w-8 text-white" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-4">{{ service.title }}</h3>
              <p class="text-gray-600 leading-relaxed">{{ service.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Process Section -->
    <section id="process" class="py-24 bg-white scroll-mt-20">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full text-gray-700 text-sm font-medium">
            Our Process
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            How We
            <span class="block bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Work Together
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            A streamlined process from initial consultation to ongoing support
          </p>
        </div>

        <div class="max-w-5xl mx-auto">
          <div class="space-y-12">
            <div v-for="(step, index) in processSteps" :key="index" class="flex items-start space-x-6">
              <div class="flex-shrink-0 relative">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg relative z-10">
                  <component :is="step.icon" class="h-8 w-8 text-white" />
                </div>
                <div v-if="index < processSteps.length - 1" class="absolute top-20 left-8 w-0.5 h-16 bg-gradient-to-b from-blue-500 to-purple-500 opacity-30"></div>
              </div>
              <div class="flex-1">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                  <div class="flex items-start justify-between mb-4">
                    <div>
                      <div class="text-blue-600 font-bold text-lg mb-2">Step {{ step.step }}</div>
                      <h3 class="text-2xl font-bold text-gray-900">{{ step.title }}</h3>
                    </div>
                    <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                      {{ step.duration }}
                    </div>
                  </div>
                  <p class="text-gray-600 leading-relaxed text-lg">{{ step.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-blue-600 to-purple-600">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-8">
          <h2 class="text-4xl lg:text-5xl font-bold text-white leading-tight">
            Ready to Go Solar?
          </h2>
          <p class="text-xl text-blue-100 max-w-3xl mx-auto">
            Join thousands of satisfied customers who have made the switch to clean, renewable energy.
            Get your free consultation and custom quote today.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a
              href="/contact"
              class="inline-flex items-center bg-white text-blue-600 px-8 py-4 rounded-full font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105"
            >
              Get Free Consultation
              <ArrowRight class="ml-2 h-5 w-5" />
            </a>
            <button class="inline-flex items-center border-2 border-white text-white hover:bg-white hover:text-blue-600 px-8 py-4 rounded-full font-semibold transition-all duration-300">
              <Phone class="mr-2 h-5 w-5" />
              Call: +1 (555) 123-4567
            </button>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>