<?php include 'Header.php'; ?>
<?php include 'Sidebar.php'; ?>
<?php include 'modals_dpo.php'; ?>
<?php include 'assets.php'; ?>
<?php 
// Koneksi database
include 'koneksi.php';

// Fetch data instansi, jenis kasus, dan jenis hukuman untuk select option
$instansi = mysqli_query($koneksidpogendeng, "SELECT * FROM instansi");
$kasus = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_kasus");
$hukuman = mysqli_query($koneksidpogendeng, "SELECT * FROM jenis_hukuman");
?>


  <style>
    .dynamic-card {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      border-radius: 15px;
      padding: 10px;
      margin-bottom: 15px;
      text-align: center;
    }

    .btn-dynamic {
      background-color: #ff6f61;
      color: white;
      border-radius: 10px;
      margin-bottom: 10px;
    }

    .btn-dynamic:hover {
      background-color: #e04e3b;
    }

    .tab-content {
      background-color: #f4f6f9;
      padding: 20px;
      border-radius: 10px;
      min-height: 300px;
    }

    .readonly-input {
      background-color: #e9ecef;
    }
  </style>
<style>
  html, body { height: 100%; }
  .wrapper { min-height: 100%; display: flex; flex-direction: column; }
  .content-wrapper { flex: 1; }
  .readonly-input { background-color: #f8f9fa; }
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <div class="container mt-4">
    <h1 class="mb-4">Data DPO</h1>
    <!-- ✅ Button trigger modal ke ID #modalInputDPO dari modals_dpo.php -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalInputDPO">
      <i class="fas fa-plus"></i> Tambah DPO
    </button>

    <!-- Form Pencarian -->
    <div class="row mb-4">
      <div class="col-md-3 text-center">
        <img src="https://via.placeholder.com/150" id="fotoDPO" class="img-fluid rounded-circle mb-3" alt="Foto DPO">
        <p><b>Foto DPO</b></p>
      </div>
      <div class="col-md-9">
        <div class="form-group">
          <label>Masukkan NIK</label>
          <input type="text" class="form-control mb-2" id="nik" placeholder="Masukkan NIK">
          <label>Masukkan Nama</label>
          <input type="text" class="form-control mb-2" id="nama" placeholder="Masukkan Nama">
          <label>Masukkan Nama Instansi</label>
          <select class="form-control mb-2" id="instansi">
            <option value="">-- Pilih Instansi --</option>
            <?php while($row = mysqli_fetch_assoc($instansi)) { ?>
              <option value="<?= $row['nama_instansi'] ?>"><?= $row['nama_instansi'] ?></option>
            <?php } ?>
          </select>
          <button class="btn btn-primary" id="btnCari">CARI</button>
          <button class="btn btn-warning" id="btnReset">RESET</button>
          <!-- Tombol Generate PDF dan Download Foto Framed -->
          <div id="actionButtons" class="mt-3" style="display:none;">
            <a href="#" target="_blank" class="btn btn-success" id="btnDownloadPDF">Download PDF</a>
            <a href="#" target="_blank" class="btn btn-danger" id="btnDownloadFramed">Download Foto Framed</a>
          </div>

        </div>
      </div>
    </div>

    <div id="hasilDPO" class="mt-3"></div>

    <!-- Tabs -->
    <div class="mt-4">
      <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#deskripsi">Deskripsi</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#kasus">Kasus</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#barang">Barang Bukti</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#status">Status DPO</a></li>
      </ul>

      <div class="tab-content mt-3">
        <!-- Tab Deskripsi -->
        <div class="tab-pane fade show active" id="deskripsi">
          <div class="row">
            <div class="col-md-6">
              <input class="form-control readonly-input mb-2" readonly placeholder="NIK">
              <input class="form-control readonly-input mb-2" readonly placeholder="Nama Lengkap">
              <input class="form-control readonly-input mb-2" readonly placeholder="Tanggal Lahir">
              <input class="form-control readonly-input mb-2" readonly placeholder="Jenis Kelamin">
              <input class="form-control readonly-input mb-2" readonly placeholder="Nama Instansi">
            </div>
            <div class="col-md-6">
              <input class="form-control readonly-input mb-2" readonly placeholder="Nomor HP">
              <input class="form-control readonly-input mb-2" readonly placeholder="Email">
              <input class="form-control readonly-input mb-2" readonly placeholder="Media Sosial">
              <input class="form-control readonly-input mb-2" readonly placeholder="Alamat">
            </div>
          </div>
        </div>

        <!-- Tab Kasus -->
        <div class="tab-pane fade" id="kasus">
          <label>Daftar Kasus DPO:</label>
          <textarea class="form-control readonly-input" rows="6" readonly placeholder="Daftar Kasus DPO"></textarea>
        </div>

        <!-- Tab Barang Bukti -->
        <div class="tab-pane fade" id="barang">
          <label>Upload Bukti (PDF, Gambar, Video, Rekaman):</label>
          <input type="file" class="form-control" multiple>
        </div>

        <!-- Tab Status -->
        <div class="tab-pane fade" id="status">
          <h5>Status DPO:</h5>
          <ul>
            <li><span class="badge bg-danger">BURON</span></li>
            <li><span class="badge bg-success">TERTANGKAP</span></li>
            <li><span class="badge bg-secondary">MENINGGAL DUNIA</span></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Modal Tambah DPO -->
    <div class="modal fade" id="modalTambahDPO" tabindex="-1" aria-labelledby="modalTambahDPOLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form action="proses_tambah_dpo.php" method="POST" enctype="multipart/form-data" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Data DPO</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>NIK</label>
              <input type="text" name="nik" class="form-control" >
            </div>
            <div class="mb-3">
              <label>Nama Lengkap</label>
              <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Instansi</label>
              <select name="instansi" class="form-control" required>
                <option value="">-- Pilih Instansi --</option>
                <?php mysqli_data_seek($instansi, 0); while($row = mysqli_fetch_assoc($instansi)) { ?>
                  <option value="<?= $row['nama_instansi'] ?>"><?= $row['nama_instansi'] ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Jenis Kasus</label>
              <select name="jenis_kasus" class="form-control" required>
                <option value="">-- Pilih Kasus --</option>
                <?php while($row = mysqli_fetch_assoc($kasus)) { ?>
                  <option value="<?= $row['nama_kasus'] ?>"><?= $row['nama_kasus'] ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Jenis Hukuman</label>
              <select name="jenis_hukuman" class="form-control" required>
                <option value="">-- Pilih Hukuman --</option>
                <?php while($row = mysqli_fetch_assoc($hukuman)) { ?>
                  <option value="<?= $row['nama_hukuman'] ?>"><?= $row['nama_hukuman'] ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Upload Foto DPO</label>
              <input type="file" name="foto" class="form-control" accept="image/*" >
            </div>
            <div class="mb-3">
              <label>Deskripsi Kasus</label>
              <textarea name="deskripsi" class="form-control" rows="4" ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Simpan</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
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
              <table class="table table-bordered">
                <tr><th>Nama Lengkap</th><td>${data.nama_lengkap}</td></tr>
                <tr><th>Tanggal Lahir</th><td>${data.tanggal_lahir}</td></tr>
                <tr><th>Jenis Kelamin</th><td>${data.jenis_kelamin}</td></tr>
                <tr><th>Nama Instansi</th><td>${data.nama_instansi}</td></tr>
                <tr><th>Jenis Kasus</th><td>${data.jenis_kasus}</td></tr>
                <tr><th>Jenis Hukuman</th><td>${data.jenis_hukuman}</td></tr>
                <tr><th>Status DPO</th><td>${data.status_dpo}</td></tr>
              </table>`;
          } else {
            document.getElementById('fotoDPO').src = 'https://via.placeholder.com/150';
            document.getElementById('hasilDPO').innerHTML = `<div class='alert alert-danger'>Data tidak ditemukan!</div>`;
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
