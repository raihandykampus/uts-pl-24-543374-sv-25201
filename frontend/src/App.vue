<template>
  <div class="app-container">
    <Navbar
      :user="user"
      :is-logging-out="isLoggingOut"
      @logout="handleLogout"
    />

    <main class="main-content">
      <!-- Global Toast Alert -->
      <div
        v-if="toast.message"
        class="alert"
        :class="toast.type === 'error' ? 'alert-danger' : 'alert-success'"
        role="status"
        style="animation: fadeIn 0.2s ease;"
      >
        <span>{{ toast.message }}</span>
      </div>

      <!-- View 1: Unauthenticated (Login Screen) -->
      <LoginCard
        v-if="!user"
        :loading="loginLoading"
        :error-message="loginError"
        @submit="handleLogin"
      />

      <!-- View 2: Authenticated (Task Management Dashboard) -->
      <div v-else class="card">
        <div class="card-header">
          <div>
            <h2 style="font-size: 1.25rem; font-weight: 700;">Daftar Task</h2>
            <p style="font-size: 0.825rem; color: #64748b;">
              Kelola tugas dan lacak status pengerjaan secara terorganisir
            </p>
          </div>

          <button
            class="btn btn-primary btn-sm"
            @click="openCreateModal"
          >
            + Tambah Task
          </button>
        </div>

        <div class="card-body">
          <!-- Loading State -->
          <div v-if="loading" class="state-box">
            <div class="state-icon">⏳</div>
            <h3>Memuat Data Task...</h3>
            <p>Menghubungkan ke backend REST API melalui reverse proxy</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="state-box">
            <div class="state-icon">⚠️</div>
            <h3>Gagal Memuat Task</h3>
            <p>{{ error }}</p>
            <button class="btn btn-secondary btn-sm" @click="fetchTasks">
              Coba Lagi
            </button>
          </div>

          <!-- Empty State -->
          <div v-else-if="tasks.length === 0" class="state-box">
            <div class="state-icon">📝</div>
            <h3>Belum Ada Task</h3>
            <p>Daftar task masih kosong. Klik tombol di bawah untuk membuat task pertama Anda.</p>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
              + Buat Task Pertama
            </button>
          </div>

          <!-- Task Items List -->
          <div v-else class="tasks-grid">
            <div
              v-for="task in tasks"
              :key="task.id"
              class="task-item"
              :class="{ 'is-completed': task.status === 'completed' }"
            >
              <div class="task-main">
                <div class="task-header-row">
                  <h3 class="task-title">{{ task.title }}</h3>

                  <button
                    class="badge"
                    :class="task.status === 'completed' ? 'badge-completed' : 'badge-pending'"
                    :title="'Klik untuk mengubah ke ' + (task.status === 'completed' ? 'pending' : 'completed')"
                    style="cursor: pointer; border: none;"
                    @click="handleToggleStatus(task)"
                  >
                    {{ task.status }}
                  </button>
                </div>

                <p v-if="task.description" class="task-description">
                  {{ task.description }}
                </p>

                <div class="task-meta">
                  <span v-if="task.due_date" class="badge badge-date">
                    📅 {{ formatDate(task.due_date) }}
                  </span>
                  <span>ID: #{{ task.id }}</span>
                  <span>Dibuat: {{ formatDate(task.created_at) }}</span>
                </div>
              </div>

              <div class="task-actions">
                <button
                  class="btn btn-secondary btn-sm"
                  @click="openDetailModal(task.id)"
                  title="Lihat detail lengkap task"
                >
                  Detail
                </button>
                <button
                  class="btn btn-secondary btn-sm"
                  @click="openEditModal(task)"
                  title="Edit task"
                >
                  Edit
                </button>
                <button
                  class="btn btn-danger btn-sm"
                  @click="openDeleteConfirm(task)"
                  title="Hapus task"
                >
                  Hapus
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Create / Edit Modal -->
    <TaskModal
      v-if="modalMode === 'create' || modalMode === 'edit'"
      :task="selectedTask"
      :saving="modalSaving"
      :validation-errors="validationErrors"
      :general-error="modalError"
      @close="closeModal"
      @save="handleSaveTask"
    />

    <!-- Task Detail Modal -->
    <TaskDetailModal
      v-if="modalMode === 'detail'"
      :task-detail="detailTask"
      :loading="detailLoading"
      :error="detailError"
      @close="closeModal"
      @edit="transitionToEdit"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-if="taskToDelete"
      :task="taskToDelete"
      :deleting="isDeleting"
      @close="taskToDelete = null"
      @confirm="handleConfirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import Navbar from './components/Navbar.vue';
import LoginCard from './components/LoginCard.vue';
import TaskModal from './components/TaskModal.vue';
import TaskDetailModal from './components/TaskDetailModal.vue';
import ConfirmModal from './components/ConfirmModal.vue';
import api from './api';

// Auth State
const user = ref(api.getUserData());
const loginLoading = ref(false);
const loginError = ref('');
const isLoggingOut = ref(false);

// Task List State
const tasks = ref([]);
const loading = ref(false);
const error = ref('');

// Modal States
const modalMode = ref(null); // 'create' | 'edit' | 'detail' | null
const selectedTask = ref(null);
const modalSaving = ref(false);
const modalError = ref('');
const validationErrors = ref({});

// Detail View State
const detailTask = ref(null);
const detailLoading = ref(false);
const detailError = ref('');

// Delete Confirmation State
const taskToDelete = ref(null);
const isDeleting = ref(false);

// Notification Toast State
const toast = reactive({
  message: '',
  type: 'success',
  timeoutId: null,
});

function showToast(message, type = 'success') {
  if (toast.timeoutId) clearTimeout(toast.timeoutId);
  toast.message = message;
  toast.type = type;
  toast.timeoutId = setTimeout(() => {
    toast.message = '';
  }, 4000);
}

// Lifecycle: init auth and tasks
onMounted(async () => {
  window.addEventListener('auth:unauthorized', onUnauthorized);

  if (api.isAuthenticated()) {
    try {
      const userData = await api.getUser();
      user.value = userData.user || userData;
      await fetchTasks();
    } catch {
      handleSessionExpired();
    }
  }
});

onUnmounted(() => {
  window.removeEventListener('auth:unauthorized', onUnauthorized);
});

function onUnauthorized() {
  handleSessionExpired('Sesi Anda telah berakhir atau token tidak valid. Silakan masuk kembali.');
}

function handleSessionExpired(msg = '') {
  api.clearAuth();
  user.value = null;
  tasks.value = [];
  closeModal();
  taskToDelete.value = null;
  if (msg) showToast(msg, 'error');
}

// Authentication Actions
async function handleLogin({ email, password }) {
  loginLoading.value = true;
  loginError.value = '';

  try {
    const data = await api.login(email, password);
    user.value = data.user;
    showToast('Login berhasil! Selamat datang.');
    await fetchTasks();
  } catch (err) {
    if (err.response?.status === 401) {
      loginError.value = 'Email atau password yang Anda masukkan salah.';
    } else if (err.response?.status === 422) {
      const errors = err.response.data?.errors;
      loginError.value = errors ? Object.values(errors).flat().join(', ') : 'Input tidak valid.';
    } else {
      loginError.value = err.message || 'Terjadi kesalahan saat menghubungi server.';
    }
  } finally {
    loginLoading.value = false;
  }
}

async function handleLogout() {
  isLoggingOut.value = true;
  try {
    await api.logout();
  } catch {
    // Continue cleanup even if server responded with error
  } finally {
    user.value = null;
    tasks.value = [];
    isLoggingOut.value = false;
    showToast('Anda telah keluar dari aplikasi.');
  }
}

// Tasks Actions
async function fetchTasks() {
  loading.value = true;
  error.value = '';

  try {
    const res = await api.getTasks();
    tasks.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Gagal memuat task.';
  } finally {
    loading.value = false;
  }
}

// Create & Edit Modal
function openCreateModal() {
  selectedTask.value = null;
  validationErrors.value = {};
  modalError.value = '';
  modalMode.value = 'create';
}

function openEditModal(task) {
  selectedTask.value = { ...task };
  validationErrors.value = {};
  modalError.value = '';
  modalMode.value = 'edit';
}

function closeModal() {
  modalMode.value = null;
  selectedTask.value = null;
  detailTask.value = null;
  validationErrors.value = {};
  modalError.value = '';
}

async function handleSaveTask(payload) {
  modalSaving.value = true;
  modalError.value = '';
  validationErrors.value = {};

  try {
    if (modalMode.value === 'create') {
      await api.createTask(payload);
      showToast('Task berhasil ditambahkan.');
    } else {
      await api.updateTask(selectedTask.value.id, payload);
      showToast('Task berhasil diperbarui.');
    }
    closeModal();
    await fetchTasks();
  } catch (err) {
    if (err.response?.status === 422) {
      validationErrors.value = err.response.data?.errors || {};
      modalError.value = 'Harap periksa kembali input yang Anda masukkan.';
    } else {
      modalError.value = err.response?.data?.message || err.message || 'Gagal menyimpan task.';
    }
  } finally {
    modalSaving.value = false;
  }
}

// Quick Status Toggle
async function handleToggleStatus(task) {
  const newStatus = task.status === 'completed' ? 'pending' : 'completed';
  try {
    await api.updateTask(task.id, { status: newStatus });
    task.status = newStatus;
    showToast(`Status task #${task.id} diubah ke ${newStatus}.`);
  } catch (err) {
    showToast('Gagal mengubah status task.', 'error');
  }
}

// Detail View
async function openDetailModal(taskId) {
  modalMode.value = 'detail';
  detailLoading.value = true;
  detailError.value = '';
  detailTask.value = null;

  try {
    const res = await api.getTask(taskId);
    detailTask.value = res.data;
  } catch (err) {
    detailError.value = err.response?.data?.message || 'Gagal memuat detail task.';
  } finally {
    detailLoading.value = false;
  }
}

function transitionToEdit(task) {
  closeModal();
  openEditModal(task);
}

// Delete Confirmation
function openDeleteConfirm(task) {
  taskToDelete.value = task;
}

async function handleConfirmDelete() {
  if (!taskToDelete.value) return;

  isDeleting.value = true;
  try {
    await api.deleteTask(taskToDelete.value.id);
    showToast('Task berhasil dihapus.');
    taskToDelete.value = null;
    await fetchTasks();
  } catch (err) {
    showToast(err.response?.data?.message || 'Gagal menghapus task.', 'error');
  } finally {
    isDeleting.value = false;
  }
}

// Helper Formatters
function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
}
</script>
