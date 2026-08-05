<?php
include 'Koneksi.php';
// Fetch data dari database
$instansi_query = $koneksidpogendeng->query("SELECT * FROM instansi");
$instansis = $instansi_query->fetchAll();

$jeniskasus_query = $koneksidpogendeng->query("SELECT * FROM jenis_kasus");
$jeniskasus = $jeniskasus_query->fetchAll();

$jenishukuman_query = $koneksidpogendeng->query("SELECT * FROM jenis_hukuman");
$jenishukuman = $jenishukuman_query->fetchAll();
?>

<!-- Modal Input DPO Lengkap -->
<div id="modalInputDPO" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal('modalInputDPO')"></div>
  <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-blue-600 text-white rounded-t-xl">
      <h5 class="font-semibold">Input Data DPO</h5>
      <button type="button" class="text-white hover:text-gray-200 text-xl leading-none" onclick="closeModal('modalInputDPO')">&times;</button>
    </div>

    <form id="formInputDPO" action="proses_inputdpo.php" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="nik" required maxlength="16" pattern="[0-9]{16}">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="nama_lengkap" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
          <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="tanggal_lahir">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
          <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="jenis_kelamin">
            <option value="">-- Pilih --</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Instansi</label>
          <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="instansi_id">
            <option value="">-- Pilih Instansi --</option>
            <?php foreach($instansis as $instansi): ?>
              <option value="<?= $instansi['id'] ?>"><?= htmlspecialchars($instansi['nama_instansi']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kasus</label>
          <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="jenis_kasus_id">
            <option value="">-- Pilih Kasus --</option>
            <?php foreach($jeniskasus as $kasus): ?>
              <option value="<?= $kasus['id'] ?>"><?= htmlspecialchars($kasus['jenis_kasus']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Hukuman</label>
          <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="jenis_hukuman_id">
            <option value="">-- Pilih Hukuman --</option>
            <?php foreach($jenishukuman as $hukuman): ?>
              <option value="<?= $hukuman['id'] ?>"><?= htmlspecialchars($hukuman['jenis_hukuman']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="no_hp">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="email">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Media Sosial</label>
          <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="media_sosial">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
          <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="alamat"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status DPO</label>
          <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="status_dpo">
            <option value="">-- Pilih Status --</option>
            <option value="BURON" selected>BURON</option>
            <option value="TERTANGKAP">TERTANGKAP</option>
            <option value="MENINGGAL DUNIA">MENINGGAL DUNIA</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Upload Foto DPO</label>
          <input type="file" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" name="foto">
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg transition" onclick="closeModal('modalInputDPO')">Tutup</button>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id)   { document.getElementById(id).classList.remove('hidden'); }
  function closeModal(id)  { document.getElementById(id).classList.add('hidden'); }
</script>
