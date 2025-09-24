<template>
  <div class="installer-container">
    <h1>SolaFriq Installer - Step 3: Database Migration</h1>
    <p>Click the button below to run the database migrations and seed the database with initial data.</p>
    <div v-if="form.errors.migration" class="error-message">{{ form.errors.migration }}</div>
    <div class="navigation-buttons">
      <button @click="submit" :disabled="form.processing">Run Migrations and Seed</button>
    </div>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  setup() {
    const form = useForm({});

    const submit = () => {
      form.post(route('install.processStep3'));
    };

    return { form, submit };
  },
};
</script>

<style scoped>
/* Add your styles here */
.error-message {
  color: red;
  margin-bottom: 15px;
}
</style>
