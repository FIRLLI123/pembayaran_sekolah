<template>
  <div class="siswa-modal-overlay" tabindex="-1" @click.self="close">
    <div class="modal-dialog modal-dialog-centered siswa-modal-dialog">
      <div class="modal-content border-0 shadow">

        <!-- HEADER -->
        <div class="modal-header border-0 pb-1">
          <h5 class="modal-title fw-bold">
            {{ isEdit ? "Edit" : "Tambah" }} Siswa
          </h5>
          <button class="btn-close" @click="close">
  <i class="fas fa-times"></i>
</button>
        </div>

        <!-- BODY -->
        <div class="modal-body pt-2">

          <!-- NIS -->
          <div class="mb-3">
            <label class="form-label text-muted mb-1">NIS</label>
            <input
              v-model="form.nis"
              type="text"
              placeholder="Masukkan NIS"
              class="form-control siswa-field"
            >
          </div>

          <!-- NAMA -->
          <div class="mb-3">
            <label class="form-label text-muted mb-1">Nama Siswa</label>
            <input
              v-model="form.nama_siswa"
              type="text"
              placeholder="Masukkan nama siswa"
              class="form-control siswa-field"
            >
          </div>

          <!-- KELAS -->
<div class="mb-4">
  <label class="form-label fw-semibold">Kelas</label>
  <select
    v-model="form.kelas_id"
    class="form-select siswa-field custom-select"
  >
    <option disabled value="">-- Pilih Kelas --</option>
    <option v-for="k in kelas" :key="k.id" :value="k.id">
      {{ k.nama_kelas }}
    </option>
  </select>
</div>

<!-- JENIS KELAMIN -->
<div class="mb-4">
  <label class="form-label fw-semibold">Jenis Kelamin</label>
  <select
    v-model="form.jenis_kelamin"
    class="form-select siswa-field custom-select"
  >
    <option disabled value="">-- Pilih Jenis Kelamin --</option>
    <option value="L">Laki-laki</option>
    <option value="P">Perempuan</option>
  </select>
</div>

          <!-- NO HP -->
          <div class="mb-1">
            <label class="form-label text-muted mb-1">No HP</label>
            <input
              v-model="form.no_hp"
              type="text"
              placeholder="08xxxxxxxxxx"
              class="form-control siswa-field"
            >
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer border-0 pt-2">
          <button
            class="btn btn-light border"
            @click="close"
            :disabled="loading"
          >
            Batal
          </button>

          <button
            class="btn btn-primary px-3"
            @click="submit"
            :disabled="loading"
          >
            <span v-if="loading">Menyimpan...</span>
            <span v-else>Simpan</span>
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios"

export default {

  props: {
    siswa: Object,
    kelas: Array
  },

  emits: ["close", "success"],

  data() {
    return {

      // state form
      form: {
        nis: "",
        nama_siswa: "",
        kelas_id: "",
        jenis_kelamin: "",
        no_hp: ""
      },

      isEdit: false,
      loading: false

    }
  },

  mounted() {
    document.body.classList.add("modal-open")

    // isi form jika edit
    if (this.siswa) {
      this.form = { ...this.siswa }
      this.isEdit = true
    }
  },

  beforeUnmount() {
    document.body.classList.remove("modal-open")
  },

  methods: {

    // tutup modal
    close() {
      this.$emit("close")
    },

    // submit form
    async submit() {

      try {
        this.loading = true

        if (this.isEdit) {
          await axios.put(`/siswa/${this.form.id}`, this.form)
        } else {
          await axios.post("/siswa", this.form)
        }

        this.$emit("success")

      } catch (error) {
        console.error(error)
        alert("Terjadi kesalahan")
      } finally {
        this.loading = false
      }

    }

  }

}
</script>

<style scoped>
.siswa-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(2px);
}

.siswa-modal-dialog {
  width: 100%;
  max-width: 560px;
}

.modal-content {
  border-radius: 14px;
}


.siswa-field {
  border-radius: 10px;
  padding: 10px 12px;
  min-height: 44px;
  line-height: 1.4;
  font-size: 14px;
  transition: all 0.2s ease;
}

.siswa-field:focus {
  border-color: #4e73df;
  box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.25);
}

.custom-select {
  cursor: pointer;
  background-position: right 0.75rem center;
}
</style>
