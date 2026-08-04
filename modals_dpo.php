<?php
include 'koneksi.php';
// Fetch data dari database
$instansi_query = mysqli_query($koneksidpogendeng, "SELECT * FROM instansi");
$instansis = mysqli_fetch_all($instansi_query, MYSQLI_ASSOC);

$jeniskasus_query = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_kasus");
$jeniskasus = mysqli_fetch_all($jeniskasus_query, MYSQLI_ASSOC);

$jenishukuman_query = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman");
$jenishukuman = mysqli_fetch_all($jenishukuman_query, MYSQLI_ASSOC);
?>


<!-- Modal Input DPO Lengkap -->
<div class="modal fade" id="modalInputDPO" tabindex="-1" aria-labelledby="modalInputDPOLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Input Data DPO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formInputDPO" action="proses_inputdpo.php" method="POST" enctype="multipart/form-data">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label>NIK</label>
              <input type="text" class="form-control" name="nik" required>
            </div>

            <div class="col-md-6 mb-3">
              <label>Nama Lengkap</label>
              <input type="text" class="form-control" name="nama_lengkap" required>
            </div>

            <div class="col-md-6 mb-3">
              <label>Tanggal Lahir</label>
              <input type="date" class="form-control" name="tanggal_lahir">
            </div>

            <div class="col-md-6 mb-3">
              <label>Jenis Kelamin</label>
              <select class="form-control" name="jenis_kelamin">
                <option value="">-- Pilih --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>Instansi</label>
              <select class="form-control" name="instansi_id">
                <option value="">-- Pilih Instansi --</option>
                <?php foreach($instansis as $instansi): ?>
                  <option value="<?= $instansi['id'] ?>"><?= htmlspecialchars($instansi['nama_instansi']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>Jenis Kasus</label>
              <select class="form-control" name="jenis_kasus_id">
                <option value="">-- Pilih Kasus --</option>
                <?php foreach($jeniskasus as $kasus): ?>
                  <option value="<?= $kasus['id'] ?>"><?= htmlspecialchars($kasus['jenis_kasus']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>Jenis Hukuman</label>
              <select class="form-control" name="jenis_hukuman_id">
                <option value="">-- Pilih Hukuman --</option>
                <?php foreach($jenishukuman as $hukuman): ?>
                  <option value="<?= $hukuman['id'] ?>"><?= htmlspecialchars($hukuman['jenis_hukuman']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>No HP</label>
              <input type="text" class="form-control" name="no_hp">
            </div>

            <div class="col-md-6 mb-3">
              <label>Email</label>
              <input type="email" class="form-control" name="email">
            </div>

            <div class="col-md-6 mb-3">
              <label>Media Sosial</label>
              <input type="text" class="form-control" name="media_sosial">
            </div>

            <div class="col-md-12 mb-3">
              <label>Alamat</label>
              <textarea class="form-control" name="alamat"></textarea>
            </div>

            <div class="col-md-6 mb-3">
              <label>Status DPO</label>
              <select class="form-control" name="status_dpo">
                <option value="">-- Pilih Status --</option>
                <option value="Aktif">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label>Upload Foto DPO</label>
              <input type="file" class="form-control" name="foto">
            </div>

          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" form="formInputDPO" class="btn btn-primary">Simpan</button>
      </div>

    </div>
  </div>
</div>
