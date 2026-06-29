"use strict";

$("[data-checkboxes]").each(function() {
  var me = $(this),
    group = me.data('checkboxes'),
    role = me.data('checkbox-role');

  me.change(function() {
    var all = $('[data-checkboxes="' + group + '"]:not([data-checkbox-role="dad"])'),
      checked = $('[data-checkboxes="' + group + '"]:not([data-checkbox-role="dad"]):checked'),
      dad = $('[data-checkboxes="' + group + '"][data-checkbox-role="dad"]'),
      total = all.length,
      checked_length = checked.length;

    if(role == 'dad') {
      if(me.is(':checked')) {
        all.prop('checked', true);
      }else{
        all.prop('checked', false);
      }
    }else{
      if(checked_length >= total) {
        dad.prop('checked', true);
      }else{
        dad.prop('checked', false);
      }
    }
  });
});

$("#table-tes").dataTable({
  "columnDefs": [
    { "sortable": false, "targets": [2, 3], "width": "30%" },
    // { "targets": [0, 1, 2, 5], "width": "5000px" }
    {select: true}
  ]
});

$(document).ready(function () {
  $("#table-1").css("width", '2000px');
  $("#table-1 th:nth-child(2), #table-1 td:nth-child(2)").css("width", "170px");
  $("#table-1 th:nth-child(3), #table-1 td:nth-child(3)").css("width", "150px");
  $("#table-1 th:nth-child(4), #table-1 td:nth-child(4)").css("width", "150px");
  $("#table-1 th:nth-child(5), #table-1 td:nth-child(5)").css("width", "100px");
  $("#table-1 th:nth-child(6), #table-1 td:nth-child(6)").css("width", "200px");
  $("#table-1 th:nth-child(7), #table-1 td:nth-child(7)").css("width", "150px");
  $("#table-1 th:nth-child(15), #table-1 td:nth-child(15)").css("width", "150px");
  $("#table-1 th:nth-child(16), #table-1 td:nth-child(16)").css("width", "250px");
  $("#table-1 th:nth-child(17), #table-1 td:nth-child(17)").css("width", "200px");

});

// const no_ktp = document.querySelector('#no_ktp');
// const noDataMessage = document.querySelector('.data-not-found');
const table = $('#table-temp').DataTable({
  
});


// no_ktp.addEventListener('keyup', function() {
//   const searchText = no_ktp.value;  // Ambil nilai input
//   console.log(searchText);
//   // console.log(table.search(searchText));
//   table.search(searchText).draw();  // Gunakan metode search() dari DataTable dan redraw
  
//   // Periksa jumlah hasil pencarian
//   const info = table.page.info();
//   console.log(info);
//   if (info.recordsDisplay === 0) {
//         noDataMessage.style.display = 'block';  // Tampilkan pesan
//       } else {
//         noDataMessage.style.display = 'none';  // Sembunyikan pesan
//       }
// });
// $(document).ready(function () {
//   $("#table-pegawai").css("width", '100%');
//   $("#table-pegawai th:nth-child(2), #table-pegawai td:nth-child(2)").css("width", "200px");
//   $("#table-pegawai th:nth-child(3), #table-pegawai td:nth-child(3)").css("width", "150px");
//   $("#table-pegawai th:nth-child(4), #table-pegawai td:nth-child(4)").css("width", "150px");
//   $("#table-pegawai th:nth-child(5), #table-pegawai td:nth-child(5)").css("width", "100px");
//   // $("#table-pegawai th:nth-child(6), #table-pegawai td:nth-child(6)").css("width", "200px");
//   // $("#table-pegawai th:nth-child(7), #table-pegawai td:nth-child(7)").css("width", "150px");
//   // $("#table-pegawai th:nth-child(15), #table-pegawai td:nth-child(15)").css("width", "150px");
//   // $("#table-pegawai th:nth-child(16), #table-pegawai td:nth-child(16)").css("width", "250px");
//   // $("#table-pegawai th:nth-child(17), #table-pegawai td:nth-child(17)").css("width", "200px");

// });


$("#table-2").dataTable({
  "columnDefs": [
    { "sortable": false, "targets": [0,2,3] }
  ]
});



$(document).ready(function() {
  // Inisialisasi DataTable
  var table = $('#table-pegawai').DataTable();

  // Pilih elemen input dan div pesan
  const namaInput = document.querySelector('#nama');
  const noDataMessage = document.querySelector('.data-not-found');

  // Tambahkan event listener untuk input keyup
  namaInput.addEventListener('keyup', function() {
    const searchText = namaInput.value;
    console.log('Search Text:', searchText);  // Debug log untuk pencarian

    // Update the search and redraw table
    table.search(searchText).draw();

    // Periksa jumlah hasil pencarian
    const info = table.page.info();
    console.log(table.search(searchText).draw());  // Debug log untuk hasil

    if (info.recordsDisplay === 0) {
      noDataMessage.style.display = 'block';
    } else {
      noDataMessage.style.display = 'none';
    }
  });
});

$(document).ready(function() {
  // Inisialisasi DataTable
  var tableGuru = $('#table-guru').DataTable();

  // Pilih elemen input dan div pesan
  const noKtpInput = document.querySelector('#no_ktp');
  const noDataMessage = document.querySelector('.data-not-found');

  // Tambahkan event listener untuk input keyup
  noKtpInput.addEventListener('keyup', function() {
    const searchText = noKtpInput.value;
    console.log('Search Text:', searchText);  // Debug log untuk pencarian

    // Update the search and redraw tableGuru
    tableGuru.search(searchText).draw();

    // Periksa jumlah hasil pencarian
    const info = tableGuru.page.info();
    console.log(tableGuru.search(searchText).draw());  // Debug log untuk hasil

    if (info.recordsDisplay === 0) {
      noDataMessage.style.display = 'block';
    } else {
      noDataMessage.style.display = 'none';
    }
  });
});