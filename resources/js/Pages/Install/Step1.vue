<template>
  <div class="installer-container">
    <h1>SolaFriq Installer - Step 1: Server Requirements</h1>
    <div class="requirements-list">
      <ul>
        <li v-for="(req, key) in requirements" :key="key" :class="{ 'success': req.check, 'error': !req.check }">
          {{ req.name }}
          <span>{{ req.check ? '&#10004;' : '&#10006;' }}</span>
        </li>
      </ul>
    </div>
    <div class="navigation-buttons">
      <button @click="proceed" :disabled="!allRequirementsMet">Proceed to Step 2</button>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
  props: {
    requirements: Object,
  },
  computed: {
    allRequirementsMet() {
      return Object.values(this.requirements).every(req => req.check);
    },
  },
  methods: {
    proceed() {
      router.visit(route('install.step2'));
    },
  },
};
</script>

<style scoped>
.installer-container {
  max-width: 800px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
}
.requirements-list ul {
  list-style-type: none;
  padding: 0;
}
.requirements-list li {
  padding: 10px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
}
.success {
  color: green;
}
.error {
  color: red;
}
.navigation-buttons {
  margin-top: 20px;
  text-align: right;
}
</style>
