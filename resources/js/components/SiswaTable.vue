<template>
  <div class="card siswa-card border-0 shadow-sm">
    <div class="card-body p-4">
      <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
        <div>
          <p class="mb-1 siswa-kicker">Data Master</p>
          <h5 class="mb-0 fw-bold text-dark">Daftar Siswa</h5>
        </div>
        <button class="btn btn-primary px-3 siswa-add-btn" @click="tambah">
          <span class="plus-icon">+</span> Tambah Siswa
        </button>
      </div>

      <div class="row g-2 mb-4">
        <div class="col-md-7">
          <input
            v-model="search"
            @input="onSearchInput"
            placeholder="Cari nama siswa / NIS"
            class="form-control siswa-input"
          />
        </div>
        <div class="col-md-5">
          <select v-model="kelas_id" @change="onKelasChange" class="form-select siswa-input">
            <option value="">Semua Kelas</option>
            <option v-for="k in kelas" :key="k.id" :value="k.id">
              {{ k.nama_kelas }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="text-muted py-4 text-center">Loading...</div>

      <div v-else class="table-responsive siswa-table-wrap">
        
        <table class="table table-hover align-middle mb-0 siswa-table">
          <thead>
            <tr>
              <th style="width: 60px;">No</th>
              <th>NIS</th>
              <th>Nama</th>
              <th>Kelas</th>
              <th>JK</th>
              <th>No HP</th>
              <th style="width: 120px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!siswas.data?.length">
              <td colspan="7" class="text-center text-muted py-4">Data siswa belum ada</td>
            </tr>
            <tr v-for="(s, index) in siswas.data" :key="s.id">
              <td>{{ rowNumberStart + index }}</td>
              <td class="fw-semibold">{{ s.nis }}</td>
              <td class="fw-medium">{{ s.nama_siswa }}</td>
              <td>{{ s.kelas?.nama_kelas || "-" }}</td>
              <td>
                <span class="jk-badge" :class="s.jenis_kelamin === 'L' ? 'jk-l' : 'jk-p'">
                  {{ s.jenis_kelamin === "L" ? "Laki-laki" : "Perempuan" }}
                </span>
              </td>
              <td>{{ s.no_hp || "-" }}</td>
              <td class="text-center d-flex justify-content-center gap-2">
                <button
                  @click="edit(s)"
                  class="action-btn action-edit me-2"
                  title="Edit"
                  aria-label="Edit"
                >
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 20h4l10-10-4-4L4 16v4zm14.7-11.3a1 1 0 0 0 0-1.4l-2-2a1 1 0 0 0-1.4 0l-1.6 1.6 4 4 1-1.2z"/>
                  </svg>
                </button>
                <button
                  @click="hapus(s.id)"
                  class="action-btn action-delete"
                  title="Hapus"
                  aria-label="Hapus"
                >
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 6h2v9h-2V9zm4 0h2v9h-2V9zM7 9h2v9H7V9z"/>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
     

      <div v-if="siswas?.last_page > 1"
     class="d-flex justify-content-end mt-3">
        <nav aria-label="Pagination siswa">
          <ul class="pagination mb-0">
            <li class="page-item" :class="{ disabled: siswas.current_page === 1 }">
              <button
                class="page-link"
                type="button"
                :disabled="siswas.current_page === 1"
                @click="goToPage(siswas.current_page - 1)"
              >
                Previous
              </button>
            </li>
            <li
              v-for="n in pageNumbers"
              :key="n"
              class="page-item"
              :class="{ active: n === siswas.current_page }"
            >
              <button class="page-link" type="button" @click="goToPage(n)">
                {{ n }}
              </button>
            </li>
            <li class="page-item" :class="{ disabled: siswas.current_page === siswas.last_page }">
              <button
                class="page-link"
                type="button"
                :disabled="siswas.current_page === siswas.last_page"
                @click="goToPage(siswas.current_page + 1)"
              >
                Next
              </button>
            </li>
          </ul>
        </nav>
      </div>

      
  
  <button
    class="btn btn-sm btn-secondary me-1"
    :disabled="!siswas.prev_page_url"
    @click="changePage(page - 1)"
  >
    Prev
  </button>

  <button
    v-for="p in siswas.last_page"
    :key="p"
    class="btn btn-sm me-1"
    :class="p === siswas.current_page ? 'btn-primary' : 'btn-outline-primary'"
    @click="changePage(p)"
  >
    {{ p }}
  </button>

  <button
    class="btn btn-sm btn-secondary"
    :disabled="!siswas.next_page_url"
    @click="changePage(page + 1)"
  >
    Next
  </button>

</div>

      <Teleport to="body">
        <transition name="modal-fade" appear>
          <SiswaForm
            v-if="showModal"
            :siswa="selectedSiswa"
            :kelas="kelas"
            @close="showModal=false"
            @success="onSuccess"
          />
        </transition>
      </Teleport>
    </div>
 
</template>

<script>

import Swal from "sweetalert2"
import useSiswa from "../composables/useSiswa"
import SiswaForm from "./SiswaForm.vue"
import { ref, computed } from "vue"


export default {

  components: {
    SiswaForm
  },

setup(){
  const showModal = ref(false)
const selectedSiswa = ref(null)



const tambah = () => {
  selectedSiswa.value = null
  showModal.value = true
}

const edit = (siswa) => {
  selectedSiswa.value = siswa
  showModal.value = true
}

const onSuccess = () => {
  showModal.value = false
  getSiswa()
}

const {
  siswas,
  kelas,
  search,
  kelas_id,
  page,
  loading,
  getSiswa,
  deleteSiswa
} = useSiswa()

const changePage = (p) => {
  page.value = p
  getSiswa()
}

const rowNumberStart = computed(() => ((siswas.value.current_page || 1) - 1) * (siswas.value.per_page || 10) + 1)

const pageNumbers = computed(() => {
  const total = siswas.value.last_page || 1
  const current = siswas.value.current_page || 1
  const delta = 2

  const start = Math.max(1, current - delta)
  const end = Math.min(total, current + delta)
  const pages = []

  for (let i = start; i <= end; i += 1) {
    pages.push(i)
  }

  return pages
})

const goToPage = (targetPage) => {
  if (targetPage < 1 || targetPage > (siswas.value.last_page || 1)) return
  if (targetPage === page.value) return
  page.value = targetPage
  getSiswa()
}

const onSearchInput = () => {
  page.value = 1
  getSiswa()
}

const onKelasChange = () => {
  page.value = 1
  getSiswa()
}


// handle delete dengan sweetalert
const hapus = (id) => {

  Swal.fire({
    title:'Hapus?',
    icon:'warning',
    showCancelButton:true
  }).then((res)=>{

    if(res.isConfirmed){
      deleteSiswa(id)
    }

  })

}


return {
  siswas,
  kelas,
  search,
  kelas_id,
  page,
  loading,
  getSiswa,
  hapus,
  rowNumberStart,
  pageNumbers,
  goToPage,
  onSearchInput,
  onKelasChange,

  showModal,
  selectedSiswa,
  tambah,
  edit,
  onSuccess
}

}

}
</script>

<style>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
  transform: scale(0.98);
}
</style>
