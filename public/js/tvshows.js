$(document).ready(function() {
    $.fn.DataTable.ext.pager.numbers_length = 5;
  
    $('#tvshowTable').DataTable( {
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": "tvshows/show",
        "type": "POST"
        },
      "columns": [
        { "data": "cover" },
        { "data": "title" },
        { "data": "premiered" },
        { "data": "rating" },
        { "data": "added" }
      ],
      "lengthMenu": [ [10, 25, 50, 100], [10, 25, 50, 100] ],
      "order": [[ 1, "asc" ]],
      "pagingType": "simple_numbers",
      "order": [[ 4, "desc" ]],
      "columnDefs": [
        { 
            "targets": 0 ,
            "orderable": false, 
            "data": "cover",
            "render": function ( data, type, row, meta ) {
                return '<img height="97" width="63" src="/images/covers_tvshows/'+data+'">';
            }
        }
      ]
    } );
    
    $("#tvshowTable_wrapper").css("padding", "0px");
  
    // Modal #################################################################
  
    var table = $('#tvshowTable').DataTable();
  
    $('#tvshowTable tbody').on('click', 'tr td:first-child', function () {
        var data = table.row(this).data();
        getModalData(data);
        $('#tvshowDetails').modal();
        $('.nav-tabs a[href="#cover"]').tab('show');
    } );
  
    $('#tvshowTable tbody').on('click', 'tr td:not(:first-child)', function () {
        var data = table.row(this).data();
        getModalData(data);
        $('#tvshowDetails').modal();
        $('.nav-tabs a[href="#tvshowInfo"]').tab('show');
    } );
  
    $("#downloadTab").click(function(){
        $('.nav-tabs a[href="#episodesInfo"]').tab('show');
    } );
  
    $('.nav-tabs a').on('hidden.bs.tab', function (e) {
        $("#episodesInfo").empty();
    });

    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        var current_tab = e.target;
        var tvshowID = document.getElementById("tvshowID").value;

        if(current_tab == 'https://media.leepeuker.de/tvshows#episodesInfo') {
            $(".modal-body").css("padding", "15px 15px 0px 15px");
            $("#downloadTab").hide();
        
            $.ajax({
                type: "POST",
                url: "tvshows/episodes/" + tvshowID,
                success: function(result) {
                    $("#episodesInfo").html(result);
                },
                error: function(result) {
                    alert('Sorry, something seems to be broken! I will fix it as soon as I can.');
                }
            });
        }
    
        if(current_tab == 'https://media.leepeuker.de/tvshows#cover') {
            $(".modal-body").css("padding", "15px 15px 10px 15px");
            $("#downloadTab").show();
        }
        if(current_tab == 'https://media.leepeuker.de/tvshows#tvshowInfo') {
            $("#downloadTab").show();
            $(".modal-body").css("padding", "15px 15px 0px 15px");
        }
    });
  
    $('#tvshowsDetails').on('hide.bs.modal', function(){
        $('#trailer').attr('src', '');
    });
  });
  
  function getModalData(data) {
    document.getElementById("detailsTitle").innerHTML = data['title'];
    document.getElementById("tvshowID").value = data['id'];
    // Cover
    $("#coverImage").attr("src", "/images/covers_tvshows/" + data['cover']);
    // Movie Informations
    document.getElementById("detailsStudio").innerHTML = data['studio'];
    document.getElementById("detailsPlot").innerHTML = data['plot'];
    document.getElementById("detailsGenre").innerHTML = data['genre'];
  }
  