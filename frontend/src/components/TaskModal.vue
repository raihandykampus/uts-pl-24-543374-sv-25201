<template>
  <div class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-dialog" role="dialog" aria-modal="true">
      <div class="modal-header">
        <h3>{{ isEdit ? 'Edit Task' : 'Tambah Task Baru' }}</h3>
        <button class="modal-close" @click="$emit('close')" aria-label="Tutup">
          ✕
        </button>
      </div>

      <form @submit.prevent="handleSubmit" novalidate>
        <div class="modal-body">
          <div v-if="generalError" class="alert alert-danger" role="alert">
            {{ generalError }}
          </div>

          <!-- Title Field -->
          <div class="form-group">
            <label class="form-label" for="task-title">
              Judul Task <span class="required">*</span>
            </label>
            <input
              id="task-title"
              v-model="form.title"
              type="text"
              class="form-input"
              placeholder="Contoh: Selesaikan laporan proyek"
              :disabled="saving"
            />
            <div v-if="localTitleError || validationErrors.title" class="field-error">
              {{ localTitleError || validationErrors.title[0] }}
            </div>
          </div>

          <!-- Description Field -->
          <div class="form-group">
            <label class="form-label" for="task-description">
              Deskripsi
            </label>
            <textarea
              id="task-description"
              v-model="form.description"
              class="form-textarea"
              placeholder="Rincian atau catatan tambahan task (opsional)"
              :disabled="saving"
            ></textarea>
            <div v-if="validationErrors.description" class="field-error">
              {{ validationErrors.description[0] }}
            </div>
          </div>

          <!-- Status Field -->
          <div class="form-group">
            <label class="form-label" for="task-status">
              Status <span class="required">*</span>
            </label>
            <select
              id="task-status"
              v-model="form.status"
              class="form-select"
              required
              :disabled="saving"
            >
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
            </select>
            <div v-if="validationErrors.status" class="field-error">
              {{ validationErrors.status[0] }}
            </div>
          </div>

          <!-- Due Date Field -->
          <div class="form-group">
            <label class="form-label" for="task-due-date">
              Batas Waktu (Due Date)
            </label>
            <input
              id="task-due-date"
              v-model="form.due_date"
              type="date"
              class="form-input"
              :disabled="saving"
            />
            <div v-if="validationErrors.due_date" class="field-error">
              {{ validationErrors.due_date[0] }}
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            @click="$emit('close')"
            :disabled="saving"
          >
            Batal
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="saving"
          >
            <span v-if="saving">Menyimpan...</span>
            <span v-else>{{ isEdit ? 'Perbarui Task' : 'Simpan Task' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';

const props = defineProps({
  task: {
    type: Object,
    default: null,
  },
  saving: {
    type: Boolean,
    default: false,
  },
  validationErrors: {
    type: Object,
    default: () => ({}),
  },
  generalError: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['close', 'save']);

const isEdit = computed(() => Boolean(props.task && props.task.id));

const localTitleError = ref('');

const form = reactive({
  title: '',
  description: '',
  status: 'pending',
  due_date: '',
});

// Watch title change to clear local error
watch(
  () => form.title,
  (val) => {
    if (localTitleError.value && val && val.trim()) {
      localTitleError.value = '';
    }
  }
);

// Populate or reset form values
watch(
  () => props.task,
  (newTask) => {
    localTitleError.value = '';
    if (newTask) {
      form.title = newTask.title || '';
      form.description = newTask.description || '';
      form.status = newTask.status || 'pending';
      // Format YYYY-MM-DD for date input
      if (newTask.due_date) {
        form.due_date = newTask.due_date.substring(0, 10);
      } else {
        form.due_date = '';
      }
    } else {
      form.title = '';
      form.description = '';
      form.status = 'pending';
      form.due_date = '';
    }
  },
  { immediate: true }
);

function handleSubmit() {
  localTitleError.value = '';
  const trimmedTitle = form.title ? form.title.trim() : '';

  if (!trimmedTitle) {
    localTitleError.value = 'Judul task wajib diisi.';
    return;
  }

  emit('save', {
    title: trimmedTitle,
    description: form.description ? form.description.trim() : null,
    status: form.status,
    due_date: form.due_date ? form.due_date : null,
  });
}
</script>
