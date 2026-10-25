<template>
  <header class="navbar">
    <div class="navbar-inner">
      <div class="brand">
        <div class="brand-badge">✓</div>
        <div class="brand-text">
          <h1>Task Tracker</h1>
        </div>
      </div>

      <div v-if="user" class="user-nav">
        <div class="user-badge" :title="user.email">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>{{ user.name || user.email }}</span>
        </div>

        <button
          @click="$emit('logout')"
          class="btn btn-secondary btn-sm"
          :disabled="isLoggingOut"
        >
          <span v-if="isLoggingOut">Keluar...</span>
          <span v-else>Logout</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps({
  user: {
    type: Object,
    default: null,
  },
  isLoggingOut: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['logout']);
</script>
