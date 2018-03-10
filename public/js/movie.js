$(document).ready(function() {
    $.fn.DataTable.ext.pager.numbers_length = 5;
    
    $('#movieTable').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/movies/show",
            "type": "POST"
        },
        "columns": [
            { "data": "cover" }, 
            { "data": "title" },
            { "data": "premiered" },
            { "data": "rating" },
            { "data": "dateAdded" }
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
                    return '<img height="97" width="63" src="/images/covers_movies/'+data+'">';
                }
            }
        ]
    } ); 
    
    $("#movieTable_wrapper").css("padding", "0px");

	var table = $('#movieTable').DataTable();

    $('#movieTable tbody').on('click', 'tr td:first-child', function () {
        var data = table.row(this).data();
        getModalData(data);
        $('#movieDetails').modal();
        $('.nav-tabs a[href="#movieInfo"]').trigger('click');
        $('.nav-tabs a[href="#cover"]').trigger('click');
    } );

    $('#movieTable tbody').on('click', 'tr td:not(:first-child)', function () {
        var data = table.row(this).data();
        getModalData(data);
        $('#movieDetails').modal();
        $('.nav-tabs a[href="#movieInfo"]').trigger('click');
    } );

    $('#movieDetails').on('hide.bs.modal', function(){
        $('#trailer').attr('src', '');
    });
} );

function getModalData(data) {
    document.getElementById("detailsTitle").innerHTML = data['title'] + ' (' + data['premiered'] + ')';
    // Cover
    $("#coverImage").attr("src", "/images/covers_movies/" + data['cover']);
    // Movie Informations
    document.getElementById("detailsDirector").innerHTML = data['director'];
    document.getElementById("detailsStudio").innerHTML = data['studio'];
    document.getElementById("detailsPlot").innerHTML = data['plot'];
    document.getElementById("detailsGenre").innerHTML = data['genre'];
    document.getElementById("detailsRuntime").innerHTML = data['runtime'] + ' min';
    $("#trailer").attr("src", "//www.youtube.com/embed/" + data['trailer']);
    // File Informations
    document.getElementById("detailsFilename").innerHTML = data['fileName'];
    document.getElementById("detailsDateAdded").innerHTML = data['dateAdded'];
    document.getElementById("detailsFileSize").innerHTML = data['fileSize'] + ' GB';
    // Download Button
    $("#downloadLink").attr("href", "movies/download/" + data['id']);
}