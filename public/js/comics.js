$(document).ready(function() {
  $.fn.DataTable.ext.pager.numbers_length = 5;

  $('#comicsPhyTable').DataTable( {
    "processing": true,
    "serverSide": true,
    "ajax": {
      "url": "comics/show",
      "type": "POST"
      },
    "columns": [
      { "data": "cover" },
      { "data": "title" },
      { "data": "publisher" },
      { "data": "year" },
      { "data": "price" }
    ],
    "lengthMenu": [ [10, 25, 50, 100], [10, 25, 50, 100] ],
    "order": [[ 1, "asc" ]],
    "pagingType": "simple_numbers",
    "columnDefs": [
      { 
        "orderable": false, 
        "targets": 0 ,
        "data": "cover",
        "render": function ( data, type, row, meta ) {
          return '<img height="97" width="63" src="/images/covers_comics/'+data+'">';
        }
      },
      { 
        "targets": 4 ,
        "data": "price",
        "render": function ( data, type, row, meta ) {
          if(data === null) {
            return '-';
          } else {
            return data + ' €';
          }
        }
      }
    ]
  } );

  $("#comicsPhyTable_wrapper").css("padding", "0px");

  var table = $('#comicsPhyTable').DataTable();

  $('#comicsPhyTable tbody').on('click', 'tr td:first-child', function () {
    var data = table.row(this).data();
    document.getElementById("detailsTitle").innerHTML = data['title'];
    document.getElementById("detailsPlot").innerHTML = data['description'];
    $("#comicNr").val(data['comicID']);
    $("#comicCover").attr("src", "https://media.leepeuker.de/images/covers_comics/" + data['cover']);
    $("#deleteComicID").val(data['id']);
    $('#comicDetails').modal();
    $('.nav-tabs a[href="#cover"]').tab('show');
  } );


  $('#comicsPhyTable tbody').on('click', 'tr td:not(:first-child)', function () {
    var data = table.row(this).data();
    document.getElementById("detailsTitle").innerHTML = data['title'];
    document.getElementById("detailsPlot").innerHTML = data['description'];
    $("#comicNr").val(data['comicID']);
    $("#comicCover").attr("src", "https://media.leepeuker.de/images/covers_comics/" + data['cover']);
    $("#deleteComicID").val(data['id']);
    $('#comicDetails').modal();
    $('.nav-tabs a[href="#comicInfo"]').tab('show');
  } );
} );

$("#deleteComic").click(function() {
  $.post("pointer/to_action", { action: "deleteComic", id: $("#comicNr").val() } );
} );
