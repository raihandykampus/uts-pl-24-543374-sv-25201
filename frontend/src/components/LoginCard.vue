<template>
  <div class="auth-wrapper">
    <div class="card">
      <div class="card-body">
        <div class="auth-header">
          <h2>Masuk ke Akun</h2>
          <p>Masukkan kredensial untuk mengelola daftar task Anda</p>
        </div>

        <div class="demo-account-hint">
          <strong>Akun Pengujian (Seeder):</strong><br />
          Email: <code>test@example.com</code><br />
          Password: <code>password</code>
        </div>

        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <div>{{ errorMessage }}</div>
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label class="form-label" for="login-email">
              Email <span class="required">*</span>
            </label>
            <input
              id="login-email"
              v-model="email"
              type="email"
              class="form-input"
              placeholder="nama@example.com"
              required
              autocomplete="email"
              :disabled="loading"
            />
          </div>

          <div class="form-group">
            <label class="form-label" for="login-password">
              Password <span class="required">*</span>
            </label>
            <input
              id="login-password"
              v-model="password"
              type="password"
              class="form-input"
              placeholder="••••••••"
              required
              autocomplete="current-password"
              :disabled="loading"
            />
          </div>

          <button
            type="submit"
            class="btn btn-primary btn-block"
            :disabled="loading"
          >
            <span v-if="loading">Memproses login...</span>
            <span v-else>Masuk ke Aplikasi</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['submit']);

const email = ref('test@example.com');
const password = ref('password');

function handleSubmit() {
  emit('submit', {
    email: email.value.trim(),
    password: password.value,
  });
}
</script>
