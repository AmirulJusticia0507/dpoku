    </main>

    <footer class="bg-gray-800 text-white text-center py-3">
      <p class="text-sm">&copy; 2025 - DPOku System</p>
    </footer>
  </div> <!-- /content -->
</div> <!-- /wrapper -->

<!-- Loading Overlay -->
<div id="loading" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(255,255,255,0.7); z-index:9999; text-align:center;">
  <div class="inline-block mt-32 w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin" role="status"></div>
</div>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
  // Toggle sidebar
  const sbToggle  = document.getElementById('sidebarToggle');
  const sbSidebar = document.getElementById('sidebar');
  if (sbToggle && sbSidebar) {
    sbToggle.addEventListener('click', function () {
      sbSidebar.classList.toggle('hidden');
    });
  }

  // Loading saat navigasi lewat menu
  $(document).ready(function () {
    $('#menu a').on('click', function (e) {
      e.preventDefault();
      $('#loading').fadeIn();
      var href = $(this).attr('href');
      setTimeout(function () {
        window.location.href = href;
      }, 300);
    });
  });
</script>
</body>
</html>
