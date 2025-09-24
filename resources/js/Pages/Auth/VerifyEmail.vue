<template>
  <div>
    <h1>Email Verification</h1>
    <p>Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</p>
    <div v-if="verificationLinkSent">
      <p>A new verification link has been sent to the email address you provided during registration.</p>
    </div>
    <form @submit.prevent="submit">
      <button type="submit" :disabled="form.processing">Resend Verification Email</button>
    </form>
  </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
  setup() {
    const form = useForm({});

    const submit = () => {
      form.post(route('verification.send'), {
        onFinish: () => form.reset(),
      });
    };

    return { form, submit };
  },
  props: {
    status: String,
  },
  computed: {
    verificationLinkSent() {
      return this.status === 'verification-link-sent';
    },
  },
};
</script>