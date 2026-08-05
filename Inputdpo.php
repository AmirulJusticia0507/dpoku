<?php
$page_title = 'Data DPO';
include 'Header.php';
include 'modals_dpo.php';
include 'koneksi.php';

// Fetch data instansi, jenis kasus, dan jenis hukuman untuk select option
$instansi = mysqli_query($koneksidpogendeng, "SELECT * FROM instansi");
$kasus = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_kasus");
$hukuman = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman");
?>

<style>
  .readonly-input { background-color: #f3f4f6; }
</style>

<div class="container mx-auto mt-4">
  <h1 class="text-2xl font-bold mb-4">Data DPO</h1>
  <!-- ✅ Button buka modal #modalInputDPO dari modals_dpo.php -->
  <button class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition mb-3" onclick="openModal('modalInputDPO')">
    <i class="fas fa-plus"></i> Tambah DPO
  </button>

  <!-- Form Pencarian -->
  <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
    <div class="md:col-span-3 text-center">
      <img src="https://via.placeholder.com/150" id="fotoDPO" class="rounded-full mx-auto mb-3" alt="Foto DPO">
      <p class="font-semibold">Foto DPO</p>
    </div>
    <div class="md:col-span-9">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Masukkan NIK</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-2 focus:ring-blue-500 outline-none" id="nik" placeholder="Masukkan NIK">
        <label class="block text-sm font-medium text-gray-700 mb-1">Masukkan Nama</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-2 focus:ring-blue-500 outline-none" id="nama" placeholder="Masukkan Nama">
        <label class="block text-sm font-medium text-gray-700 mb-1">Masukkan Nama Instansi</label>
        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-2 focus:ring-blue-500 outline-none" id="instansi">
          <option value="">-- Pilih Instansi --</option>
          <?php while($row = mysqli_fetch_assoc($instansi)) { ?>
            <option value="<?= $row['nama_instansi'] ?>"><?= $row['nama_instansi'] ?></option>
          <?php } ?>
        </select>
        <div class="flex flex-wrap gap-2 mt-2">
          <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition" id="btnCari">CARI</button>
          <button class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-4 py-2 rounded-lg transition" id="btnReset">RESET</button>
        </div>
        <!-- Tombol Generate PDF dan Download Foto Framed -->
        <div id="actionButtons" class="flex flex-wrap gap-2 mt-3 hidden">
          <a href="#" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition" id="btnDownloadPDF">Download PDF</a>
          <a href="#" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition" id="btnDownloadFramed">Download Foto Framed</a>
        </div>
      </div>
    </div>
  </div>

  <div id="hasilDPO" class="mt-3"></div>

  <!-- Tabs -->
  <div class="mt-4">
    <ul class="flex flex-wrap border-b border-gray-300 gap-1" id="tabList">
      <li><a class="tab-link inline-block px-4 py-2 text-sm font-semibold border border-b-0 border-gray-300 rounded-t-lg bg-gray-200 text-gray-700" data-tab="deskripsi">Deskripsi</a></li>
      <li><a class="tab-link inline-block px-4 py-2 text-sm font-semibold border border-b-0 border-gray-300 rounded-t-lg text-gray-500 hover:text-gray-700" data-tab="kasus">Kasus</a></li>
      <li><a class="tab-link inline-block px-4 py-2 text-sm font-semibold border border-b-0 border-gray-300 rounded-t-lg text-gray-500 hover:text-gray-700" data-tab="barang">Barang Bukti</a></li>
      <li><a class="tab-link inline-block px-4 py-2 text-sm font-semibold border border-b-0 border-gray-300 rounded-t-lg text-gray-500 hover:text-gray-700" data-tab="status">Status DPO</a></li>
    </ul>

    <div class="bg-gray-100 p-5 rounded-b-lg min-h-[300px] mt-0 border border-t-0 border-gray-300">
      <!-- Tab Deskripsi -->
      <div class="tab-pane" id="tab-deskripsi">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
          <div class="col-span-1 md:col-span-2">
            <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="NIK">
          </div>
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Nama Lengkap">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Tanggal Lahir">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Jenis Kelamin">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Nama Instansi">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Nomor HP">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Email">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Media Sosial">
          <input class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 readonly-input" readonly placeholder="Alamat">
        </div>
      </div>

      <!-- Tab Kasus -->
      <div class="tab-pane hidden" id="tab-kasus">
        <label class="block text-sm font-medium text-gray-700 mb-1">Daftar Kasus DPO:</label>
        <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg readonly-input" rows="6" readonly placeholder="Daftar Kasus DPO"></textarea>
      </div>

      <!-- Tab Barang Bukti -->
      <div class="tab-pane hidden" id="tab-barang">
        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti (PDF, Gambar, Video, Rekaman):</label>
        <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white" multiple>
      </div>

      <!-- Tab Status -->
      <div class="tab-pane hidden" id="tab-status">
        <h5 class="font-semibold mb-2">Status DPO:</h5>
        <ul class="space-y-2">
          <li><span class="inline-block bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full">BURON</span></li>
          <li><span class="inline-block bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full">TERTANGKAP</span></li>
          <li><span class="inline-block bg-gray-500 text-white text-xs font-bold px-3 py-1 rounded-full">MENINGGAL DUNIA</span></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
  // Tabs (vanilla)
  document.querySelectorAll('.tab-link').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelectorAll('.tab-link').forEach(function (l) {
        l.classList.add('text-gray-500');
        l.classList.remove('bg-gray-200', 'text-gray-700');
      });
      this.classList.remove('text-gray-500');
      this.classList.add('bg-gray-200', 'text-gray-700');

      document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.add('hidden'); });
      document.getElementById('tab-' + this.getAttribute('data-tab')).classList.remove('hidden');
    });
  });

  // Fungsi Cari DPO
  document.getElementById('btnCari').addEventListener('click', function() {
    let nik = document.getElementById('nik').value;
    let nama = document.getElementById('nama').value;
    let instansi = document.getElementById('instansi').value;

    fetch(`cari_dpo.php?nik=${nik}&nama=${nama}&instansi=${instansi}`)
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          document.getElementById('fotoDPO').src = data.foto;
          document.getElementById('hasilDPO').innerHTML = `
            <div class="overflow-x-auto bg-white rounded-xl shadow">
              <table class="w-full text-sm text-left text-gray-700">
                <tbody class="divide-y divide-gray-200">
                  <tr><th class="px-4 py-2 bg-gray-50 w-40">Nama Lengkap</th><td class="px-4 py-2">${data.nama_lengkap}</td></tr>
                  <tr><th class="px-4 py-2 bg-gray-50">Tanggal Lahir</th><td class="px-4 py-2">${data.tanggal_lahir}</td></tr>
                  <tr><th class="px-4 py-2 bg-gray-50">Jenis Kelamin</th><td class="px-4 py-2">${data.jenis_kelamin}</td></tr>
                  <tr><th class="px-4 py-2 bg-gray-50">Nama Instansi</th><td class="px-4 py-2">${data.nama_instansi}</td></tr>
                  <tr><th class="px-4 py-2 bg-gray-50">Jenis Kasus</th><td class="px-4 py-2">${data.jenis_kasus}</td></tr>
                  <tr><th class="px-4 py-2 bg-gray-50">Jenis Hukuman</th><td class="px-4 py-2">${data.jenis_hukuman}</td></tr>
                  <tr><th class="px-4 py-2 bg-gray-50">Status DPO</th><td class="px-4 py-2">${data.status_dpo}</td></tr>
                </tbody>
              </table>
            </div>`;
        } else {
          document.getElementById('fotoDPO').src = 'https://via.placeholder.com/150';
          document.getElementById('hasilDPO').innerHTML = `<div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-lg">Data tidak ditemukan!</div>`;
        }
      });
  });

  // Fungsi Reset Form
  document.getElementById('btnReset').addEventListener('click', function() {
    document.getElementById('nik').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('instansi').value = '';
    document.getElementById('fotoDPO').src = 'https://via.placeholder.com/150';
    document.getElementById('hasilDPO').innerHTML = '';
  });
</script>

<?php include 'Footer.php'; ?>
