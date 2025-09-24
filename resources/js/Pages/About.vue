<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
  Target,
  Eye,
  Heart,
  Users,
  Award,
  Globe,
  Lightbulb,
  Shield,
  TrendingUp,
  Zap,
  Leaf,
  Building2,
  Star,
  CheckCircle,
  ArrowRight,
  MapPin,
  Calendar
} from 'lucide-vue-next';

const stats = [
  { number: '5,000+', label: 'Systems Installed', icon: Zap },
  { number: '50+', label: 'Countries Served', icon: Globe },
  { number: '98%', label: 'Customer Satisfaction', icon: Star },
  { number: '10+', label: 'Years Experience', icon: Award }
];

const values = [
  {
    icon: Leaf,
    title: 'Sustainability',
    description: 'Committed to environmental protection through renewable energy solutions that reduce carbon footprint and promote a greener future.',
    gradient: 'from-green-500 to-emerald-600'
  },
  {
    icon: Shield,
    title: 'Reliability',
    description: 'Delivering dependable solar systems with proven performance, backed by comprehensive warranties and ongoing technical support.',
    gradient: 'from-blue-500 to-blue-600'
  },
  {
    icon: Users,
    title: 'Community Focus',
    description: 'Empowering local communities with accessible solar solutions, creating jobs, and fostering energy independence across Africa.',
    gradient: 'from-purple-500 to-purple-600'
  },
  {
    icon: Lightbulb,
    title: 'Innovation',
    description: 'Pioneering cutting-edge solar technology and smart energy solutions to meet the evolving needs of modern households and businesses.',
    gradient: 'from-orange-500 to-yellow-500'
  }
];

const team = [
  {
    name: 'David Mukamuri',
    role: 'Founder & CEO',
    image: '/placeholder.svg?height=300&width=300&query=african business leader',
    bio: 'Former cruise ship engineer with 15+ years experience in renewable energy systems. Passionate about bringing sustainable energy solutions to Africa.',
    location: 'Harare, Zimbabwe'
  },
  {
    name: 'Sarah Tembo',
    role: 'Chief Technology Officer',
    image: '/placeholder.svg?height=300&width=300&query=african woman engineer',
    bio: 'Solar systems engineer with expertise in grid-tie and off-grid installations. Leading our technical innovation and quality assurance programs.',
    location: 'Cape Town, South Africa'
  },
  {
    name: 'Michael Ochieng',
    role: 'Head of Operations',
    image: '/placeholder.svg?height=300&width=300&query=african operations manager',
    bio: 'Operations specialist focused on streamlining installation processes and ensuring exceptional customer service across all markets.',
    location: 'Nairobi, Kenya'
  }
];

const milestones = [
  {
    year: '2019',
    title: 'Company Founded',
    description: 'SolaFriq was established with a mission to democratize solar energy access across Africa.',
    icon: Building2
  },
  {
    year: '2020',
    title: 'First 1,000 Installations',
    description: 'Reached our first major milestone with successful installations across 5 countries.',
    icon: Zap
  },
  {
    year: '2021',
    title: 'Diaspora Program Launch',
    description: 'Launched specialized financing program for diaspora communities working on cruise ships.',
    icon: Globe
  },
  {
    year: '2023',
    title: '5,000+ Happy Customers',
    description: 'Celebrated serving over 5,000 customers with 98% satisfaction rate across 50+ countries.',
    icon: Star
  }
];
</script>

<template>
  <MainLayout>
    <!-- Hero Section -->
    <section class="relative min-h-[80vh] flex items-center justify-center overflow-hidden bg-gradient-to-br from-gray-50 via-white to-orange-50">
      <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-72 h-72 bg-gradient-to-br from-orange-400/20 to-yellow-400/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-gradient-to-br from-blue-900/10 to-blue-800/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-br from-orange-500/5 to-yellow-500/5 rounded-full blur-3xl"></div>
      </div>

      <div class="container mx-auto px-4 pt-20 relative z-10">
        <div class="text-center space-y-8 max-w-4xl mx-auto">
          <div class="inline-flex items-center px-6 py-3 bg-orange-100 rounded-full text-orange-700 text-sm font-medium">
            <Heart class="w-4 h-4 mr-2" />
            Our Story
          </div>

          <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 leading-tight">
            Powering Africa's
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Energy Future
            </span>
          </h1>

          <p class="text-xl text-gray-600 leading-relaxed max-w-3xl mx-auto">
            Born from a vision to democratize clean energy access across Africa, SolaFriq combines cutting-edge
            solar technology with innovative financing solutions to bring sustainable power to every home and business.
          </p>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 pt-8">
            <div v-for="stat in stats" :key="stat.label" class="text-center">
              <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                <component :is="stat.icon" class="h-6 w-6 text-white" />
              </div>
              <div class="text-3xl font-bold text-gray-900">{{ stat.number }}</div>
              <div class="text-sm text-gray-600">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-16 items-center max-w-7xl mx-auto">
          <!-- Mission -->
          <div class="space-y-8">
            <div class="space-y-6">
              <div class="inline-flex items-center px-4 py-2 bg-blue-100 rounded-full text-blue-700 text-sm font-medium">
                <Target class="w-4 h-4 mr-2" />
                Our Mission
              </div>
              <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">
                Making Solar Energy
                <span class="block bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                  Accessible to All
                </span>
              </h2>
              <p class="text-lg text-gray-600 leading-relaxed">
                To provide affordable, reliable, and sustainable solar energy solutions to African communities,
                particularly supporting diaspora families working globally. We believe everyone deserves access
                to clean, dependable power that improves quality of life and drives economic growth.
              </p>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6">
                <div class="text-2xl font-bold text-blue-900 mb-2">24/7</div>
                <div class="text-blue-700 font-medium">Customer Support</div>
                <div class="text-sm text-blue-600">Always here when you need us</div>
              </div>
              <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6">
                <div class="text-2xl font-bold text-green-900 mb-2">100%</div>
                <div class="text-green-700 font-medium">Renewable Energy</div>
                <div class="text-sm text-green-600">Clean power for a better future</div>
              </div>
            </div>
          </div>

          <!-- Vision -->
          <div class="space-y-8">
            <div class="space-y-6">
              <div class="inline-flex items-center px-4 py-2 bg-orange-100 rounded-full text-orange-700 text-sm font-medium">
                <Eye class="w-4 h-4 mr-2" />
                Our Vision
              </div>
              <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">
                A Solar-Powered
                <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
                  African Continent
                </span>
              </h2>
              <p class="text-lg text-gray-600 leading-relaxed">
                We envision an Africa where every home and business is powered by clean, renewable solar energy.
                A continent where energy poverty is eliminated, economic opportunities flourish, and environmental
                sustainability drives prosperity for generations to come.
              </p>
            </div>

            <div class="space-y-4">
              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 mt-1 flex-shrink-0" />
                <div>
                  <div class="font-semibold text-gray-900">Energy Independence</div>
                  <div class="text-gray-600">Reducing reliance on grid power and fossil fuels</div>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 mt-1 flex-shrink-0" />
                <div>
                  <div class="font-semibold text-gray-900">Economic Empowerment</div>
                  <div class="text-gray-600">Creating jobs and business opportunities</div>
                </div>
              </div>
              <div class="flex items-start space-x-3">
                <CheckCircle class="h-6 w-6 text-green-500 mt-1 flex-shrink-0" />
                <div>
                  <div class="font-semibold text-gray-900">Environmental Protection</div>
                  <div class="text-gray-600">Reducing carbon footprint across the continent</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="py-24 bg-gradient-to-br from-gray-50 to-blue-50">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-white rounded-full text-gray-700 text-sm font-medium shadow-sm">
            Core Values
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            What Drives Us
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Every Day
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Our values guide every decision we make and every solution we deliver
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto">
          <div v-for="(value, index) in values" :key="index" class="group">
            <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 h-full">
              <div :class="['w-16 h-16 bg-gradient-to-br rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300', value.gradient]">
                <component :is="value.icon" class="h-8 w-8 text-white" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-4">{{ value.title }}</h3>
              <p class="text-gray-600 leading-relaxed">{{ value.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-full text-gray-700 text-sm font-medium">
            Leadership Team
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            Meet the People
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              Behind SolaFriq
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Our experienced team combines technical expertise with deep understanding of African energy needs
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <div v-for="(member, index) in team" :key="index" class="group">
            <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
              <div class="relative overflow-hidden">
                <img
                  :src="member.image"
                  :alt="member.name"
                  class="w-full h-80 object-cover group-hover:scale-110 transition-transform duration-500"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>
              <div class="p-6 space-y-4">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">{{ member.name }}</h3>
                  <p class="text-orange-600 font-medium">{{ member.role }}</p>
                  <div class="flex items-center text-sm text-gray-600 mt-1">
                    <MapPin class="h-4 w-4 mr-1" />
                    {{ member.location }}
                  </div>
                </div>
                <p class="text-gray-600 leading-relaxed">{{ member.bio }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Company Timeline -->
    <section class="py-24 bg-gradient-to-br from-gray-50 to-orange-50">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-4 mb-20">
          <div class="inline-flex items-center px-4 py-2 bg-white rounded-full text-gray-700 text-sm font-medium shadow-sm">
            <Calendar class="w-4 h-4 mr-2" />
            Company Timeline
          </div>
          <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
            Our Journey
            <span class="block bg-gradient-to-r from-orange-500 to-yellow-500 bg-clip-text text-transparent">
              So Far
            </span>
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            From humble beginnings to continental impact - here's how we've grown
          </p>
        </div>

        <div class="max-w-4xl mx-auto">
          <div class="space-y-12">
            <div v-for="(milestone, index) in milestones" :key="index" class="flex items-start space-x-6">
              <div class="flex-shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-2xl flex items-center justify-center shadow-lg">
                  <component :is="milestone.icon" class="h-8 w-8 text-white" />
                </div>
              </div>
              <div class="flex-1">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                  <div class="flex items-center space-x-4 mb-4">
                    <div class="bg-gradient-to-r from-orange-500 to-yellow-500 text-white px-4 py-1 rounded-full text-sm font-bold">
                      {{ milestone.year }}
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ milestone.title }}</h3>
                  </div>
                  <p class="text-gray-600 leading-relaxed text-lg">{{ milestone.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-r from-orange-500 to-yellow-500">
      <div class="container mx-auto px-4">
        <div class="text-center space-y-8">
          <h2 class="text-4xl lg:text-5xl font-bold text-white leading-tight">
            Ready to Join Our Mission?
          </h2>
          <p class="text-xl text-orange-100 max-w-3xl mx-auto">
            Be part of Africa's renewable energy revolution. Get your custom solar quote today.
          </p>
          <a
            href="/contact"
            class="inline-flex items-center bg-white text-orange-600 px-8 py-4 rounded-full font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105"
          >
            Get Started Today
            <ArrowRight class="ml-2 h-5 w-5" />
          </a>
        </div>
      </div>
    </section>
  </MainLayout>
</template>