<script setup>
import { ref, onMounted, watch } from 'vue';
import { CheckCircle, AlertCircle, XCircle, Info, X } from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    required: true
  },
  autoHide: {
    type: Boolean,
    default: true
  },
  duration: {
    type: Number,
    default: 5000
  }
});

const emit = defineEmits(['close']);

const visible = ref(false);
const timeoutId = ref(null);

const typeConfig = {
  success: {
    icon: CheckCircle,
    bgClass: 'bg-gradient-to-r from-green-500 to-emerald-500',
    borderClass: 'border-green-200',
    iconClass: 'text-white',
    textClass: 'text-white'
  },
  error: {
    icon: XCircle,
    bgClass: 'bg-gradient-to-r from-red-500 to-red-600',
    borderClass: 'border-red-200',
    iconClass: 'text-white',
    textClass: 'text-white'
  },
  warning: {
    icon: AlertCircle,
    bgClass: 'bg-gradient-to-r from-orange-500 to-yellow-500',
    borderClass: 'border-orange-200',
    iconClass: 'text-white',
    textClass: 'text-white'
  },
  info: {
    icon: Info,
    bgClass: 'bg-gradient-to-r from-blue-500 to-blue-600',
    borderClass: 'border-blue-200',
    iconClass: 'text-white',
    textClass: 'text-white'
  }
};

const config = typeConfig[props.type];

const close = () => {
  visible.value = false;
  if (timeoutId.value) {
    clearTimeout(timeoutId.value);
    timeoutId.value = null;
  }
  setTimeout(() => {
    emit('close');
  }, 300);
};

const startAutoHide = () => {
  if (props.autoHide && props.duration > 0) {
    timeoutId.value = setTimeout(() => {
      close();
    }, props.duration);
  }
};

watch(() => props.show, (newValue) => {
  if (newValue) {
    visible.value = true;
    startAutoHide();
  } else {
    visible.value = false;
  }
});

onMounted(() => {
  if (props.show) {
    visible.value = true;
    startAutoHide();
  }
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="transform opacity-0 translate-y-2"
    enter-to-class="transform opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-300"
    leave-from-class="transform opacity-100 translate-y-0"
    leave-to-class="transform opacity-0 translate-y-2"
  >
    <div
      v-if="visible"
      :class="[
        'fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg border overflow-hidden',
        config.bgClass,
        config.borderClass
      ]"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <component
              :is="config.icon"
              :class="['h-6 w-6', config.iconClass]"
            />
          </div>
          <div class="ml-3 flex-1">
            <h3
              v-if="title"
              :class="['text-sm font-semibold', config.textClass]"
            >
              {{ title }}
            </h3>
            <p :class="['text-sm', config.textClass, { 'mt-1': title }]">
              {{ message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="close"
              :class="[
                'rounded-md inline-flex focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white/20 p-1 hover:bg-white/20 transition-colors',
                config.textClass
              ]"
            >
              <span class="sr-only">Close</span>
              <X class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>