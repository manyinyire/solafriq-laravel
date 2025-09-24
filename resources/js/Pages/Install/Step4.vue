<template>
  <div class="installer-container">
    <h1>SolaFriq Installer - Step 4: Create Admin User</h1>
    <form @submit.prevent="submit">
      <div class="form-group">
        <label for="name">Name</label>
        <input id="name" v-model="form.name" type="text" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" v-model="form.email" type="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" v-model="form.password" type="password" required>
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required>
      </div>
      <div v-if="form.errors.email" class="error-message">{{ form.errors.email }}</div>
      <div v-if="form.errors.password" class="error-message">{{ form.errors.password }}</div>
      <div class="navigation-buttons">
        <button type="submit" :disabled="form.processing">Create Admin and Finish</button>
      </div>
    </form>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  setup() {
    const form = useForm({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    });

    const submit = () => {
      form.post(route('install.processStep4'));
    };

    return { form, submit };
  },
};
</script>

<style scoped>
/* Add your styles here */
.form-group {
  margin-bottom: 15px;
}
.error-message {
  color: red;
  margin-bottom: 15px;
}
</style>
