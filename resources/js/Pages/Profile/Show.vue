<template>
  <ClientLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-3xl font-bold mb-6">My Profile</h1>
      <div class="bg-white shadow-md rounded-lg p-6">
        <form @submit.prevent="updateProfile">
          <div class="space-y-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
              <input type="text" id="name" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
            </div>
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
              <input type="email" id="email" v-model="form.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
              <input type="password" id="password" v-model="form.password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
              <input type="password" id="password_confirmation" v-model="form.password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
            </div>
          </div>
          <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:border-orange-900 focus:ring ring-orange-300 disabled:opacity-25 transition ease-in-out duration-150">
              Update Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const form = useForm({
  name: page.props.auth.user.name,
  email: page.props.auth.user.email,
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  form.put(route('profile.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>