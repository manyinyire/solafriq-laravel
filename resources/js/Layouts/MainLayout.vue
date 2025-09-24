<script setup>
import { Link } from '@inertiajs/vue3';
import Footer from '@/Components/Footer.vue';
import ModernHeader from '@/Components/ModernHeader.vue';
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-orange-50">
    <ModernHeader />

    <!-- Page Content -->
    <main>
      <slot />
    </main>

    <Footer />
  </div>
</template>
