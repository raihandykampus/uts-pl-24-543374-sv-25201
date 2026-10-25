<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-dialog" role="dialog" aria-modal="true">
      <div class="modal-header">
        <h3>Detail Task</h3>
        <button class="modal-close" @click="$emit('close')" aria-label="Tutup">
          ✕
        </button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="state-box" style="padding: 1.5rem 0;">
          <p>Memuat detail task...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger" role="alert">
          {{ error }}
        </div>

        <div v-else-if="taskDetail">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <h4 style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">
              {{ taskDetail.title }}
            </h4>
            <span
              class="badge"
              :class="taskDetail.status === 'completed' ? 'badge-completed' : 'badge-pending'"
            >
              {{ taskDetail.status }}
            </span>
          </div>

          <div class="form-group">
            <span class="form-label">Deskripsi:</span>
            <div
              style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; min-height: 70px; white-space: pre-line; font-size: 0.9rem;"
            >
              {{ taskDetail.description || 'Tidak ada deskripsi.' }}
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; font-size: 0.85rem; color: #64748b;">
            <div>
              <strong style="display: block; color: #0f172a;">Jatuh Tempo:</strong>
              <span>{{ formatDate(taskDetail.due_date) }}</span>
            </div>
            <div>
              <strong style="display: block; color: #0f172a;">Dibuat Pada:</strong>
              <span>{{ formatDateTime(taskDetail.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$emit('close')">
          Tutup
        </button>
        <button
          v-if="taskDetail"
          type="button"
          class="btn btn-primary"
          @click="$emit('edit', taskDetail)"
        >
          Edit Task
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  taskDetail: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
});

defineEmits(['close', 'edit']);

function formatDate(dateStr) {
  if (!dateStr) return 'Tidak ditentukan';
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function formatDateTime(dateStr) {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
</script>
