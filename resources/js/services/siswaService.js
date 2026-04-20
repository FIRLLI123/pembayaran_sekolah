import axios from "axios"

export default {

  getSiswa(params) {
    return axios.get('/siswa', { params })
  },

  getKelas() {
    return axios.get('/kelas-list')
  },

  deleteSiswa(id) {
    return axios.delete('/siswa/' + id)
  }

}