import { ref, onMounted } from "vue"
import siswaService from "../services/siswaService"

export default function useSiswa() {

  // STATE
  const siswas = ref({ data: [] })
  const kelas = ref([])

  const search = ref("")
  const kelas_id = ref("")
  const page = ref(1)

  const loading = ref(false)


  // GET SISWA
  const getSiswa = async () => {

    loading.value = true

    let res = await siswaService.getSiswa({
      search: search.value,
      kelas_id: kelas_id.value,
      page: page.value
    })

    siswas.value = res.data

    loading.value = false
  }


  // GET KELAS
  const getKelas = async () => {

    let res = await siswaService.getKelas()

    kelas.value = res.data
  }


  // DELETE
  const deleteSiswa = async (id) => {

    await siswaService.deleteSiswa(id)

    getSiswa()
  }


  // LIFECYCLE
  onMounted(() => {
    getSiswa()
    getKelas()
  })


  return {
    siswas,
    kelas,
    search,
    kelas_id,
    page,
    loading,
    getSiswa,
    deleteSiswa
  }

}