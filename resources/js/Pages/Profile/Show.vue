<template>
  <ClientLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-3xl font-bold mb-6">My Profile</h1>
      <div v-if="$page.props.flash.status" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ $page.props.flash.status }}</span>
      </div>
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
              <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number</label>
              <input type="text" id="phone_number" v-model="form.phone_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
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

      <!-- Danger Zone - Delete Account -->
      <div class="bg-white shadow-md rounded-lg p-6 mt-6 border-2 border-red-200">
        <h2 class="text-xl font-bold text-red-600 mb-4">Danger Zone</h2>
        <p class="text-gray-600 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
        
        <button 
          @click="showDeleteModal = true" 
          type="button" 
          class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150"
        >
          Delete Account
        </button>
      </div>

      <!-- Delete Account Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm Account Deletion</h3>
          <p class="text-gray-600 mb-4">Are you sure you want to delete your account? This action cannot be undone.</p>
          
          <form @submit.prevent="deleteAccount">
            <div class="mb-4">
              <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">
                Enter your password to confirm
              </label>
              <input 
                type="password" 
                id="delete_password" 
                v-model="deleteForm.password" 
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                required
              >
              <div v-if="deleteErrors.password" class="text-red-600 text-sm mt-2">
                {{ deleteErrors.password }}
              </div>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="showDeleteModal = false; deleteForm.password = ''; deleteErrors = {}" 
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
              >
                Delete My Account
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </ClientLayout>
</template>

<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const page = usePage();

const form = useForm({
  name: page.props.auth.user.name,
  email: page.props.auth.user.email,
  phone_number: page.props.auth.user.phone_number,
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  form.put(route('profile.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};

// Account deletion
const showDeleteModal = ref(false);
const deleteForm = reactive({
  password: ''
});
const deleteErrors = ref({});

const deleteAccount = () => {
  router.delete(route('profile.delete'), {
    data: deleteForm,
    onSuccess: () => {
      showDeleteModal.value = false;
    },
    onError: (errors) => {
      deleteErrors.value = errors;
    }
  });
};
</script>
