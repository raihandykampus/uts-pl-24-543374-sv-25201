<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-dialog" role="dialog" aria-modal="true" style="max-width: 440px;">
      <div class="modal-header">
        <h3>Konfirmasi Penghapusan</h3>
        <button class="modal-close" @click="$emit('close')" aria-label="Tutup">
          ✕
        </button>
      </div>

      <div class="modal-body">
        <p style="margin-bottom: 0.75rem;">
          Apakah Anda yakin ingin menghapus task berikut?
        </p>
        <div style="background: #f1f5f9; padding: 0.75rem 1rem; border-radius: 8px; font-weight: 600; color: #0f172a;">
          "{{ task?.title }}"
        </div>
        <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.75rem;">
          Tindakan ini tidak dapat dibatalkan.
        </p>
      </div>

      <div class="modal-footer">
        <button
          type="button"
          class="btn btn-secondary"
          @click="$emit('close')"
          :disabled="deleting"
        >
          Batal
        </button>
        <button
          type="button"
          class="btn btn-danger"
          @click="$emit('confirm')"
          :disabled="deleting"
        >
          <span v-if="deleting">Menghapus...</span>
          <span v-else>Ya, Hapus</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  task: {
    type: Object,
    default: null,
  },
  deleting: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['close', 'confirm']);
</script>
