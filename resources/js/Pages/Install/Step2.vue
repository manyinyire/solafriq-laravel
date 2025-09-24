<template>
  <div class="installer-container">
    <h1>SolaFriq Installer - Step 2: Database Configuration</h1>
    <form @submit.prevent="submit">
      <div class="form-group">
        <label for="db_host">Database Host</label>
        <input id="db_host" v-model="form.db_host" type="text" required>
      </div>
      <div class="form-group">
        <label for="db_port">Database Port</label>
        <input id="db_port" v-model="form.db_port" type="text" required>
      </div>
      <div class="form-group">
        <label for="db_database">Database Name</label>
        <input id="db_database" v-model="form.db_database" type="text" required>
      </div>
      <div class="form-group">
        <label for="db_username">Database Username</label>
        <input id="db_username" v-model="form.db_username" type="text" required>
      </div>
      <div class="form-group">
        <label for="db_password">Database Password</label>
        <input id="db_password" v-model="form.db_password" type="password">
      </div>
      <div v-if="form.errors.db_connection" class="error-message">{{ form.errors.db_connection }}</div>
      <div class="navigation-buttons">
        <button type="submit" :disabled="form.processing">Proceed to Step 3</button>
      </div>
    </form>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  setup() {
    const form = useForm({
      db_host: '127.0.0.1',
      db_port: '3306',
      db_database: 'solafriq',
      db_username: 'root',
      db_password: '',
    });

    const submit = () => {
      form.post(route('install.processStep2'));
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
