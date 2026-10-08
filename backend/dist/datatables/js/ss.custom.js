// Apply export borders centrally, including reports with their own button settings.
(function ($) {
    var buttons = $.fn.dataTable.ext.buttons;
    function wrapExport(name, customizeExport) {
        var button = buttons[name];
        if (!button || typeof button.action !== 'function') return;
        var originalAction = button.action;
        button.action = function (event, dt, node, config) {
            var exportConfig = $.extend({}, config);
            var originalCustomize = config.customize;
            exportConfig.customize = function () {
                if (typeof originalCustomize === 'function') {
                    originalCustomize.apply(this, arguments);
                }
                customizeExport(arguments[0]);
            };
            return originalAction.call(this, event, dt, node, exportConfig);
        };
    }
    wrapExport('print', function (win) {
        $(win.document.head).append('<style>body.dt-print-view table{border-collapse:collapse!important;}body.dt-print-view table thead{display:table-header-group;}body.dt-print-view table thead th{border-top:1px solid #000!important;}</style>');
    });
    wrapExport('pdfHtml5', function (doc) {
        function visit(items) {
            (items || []).forEach(function (item) {
                if (!item || typeof item !== 'object') return;
                if (item.table) {
                    var previous = item.layout;
                    var layout = typeof previous === 'object' && previous ? $.extend({}, previous) : {};
                    var width = layout.hLineWidth;
                    var color = layout.hLineColor;
                    layout.hLineWidth = function (i, table) {
                        if (i === 0) return 1;
                        if (typeof width === 'function') return width(i, table);
                        if (previous === 'noBorders') return 0;
                        if (previous === 'headerLineOnly') return i === table.table.headerRows ? 2 : 0;
                        if (previous === 'lightHorizontalLines') return i === table.table.body.length ? 0 : (i === table.table.headerRows ? 2 : 1);
                        return 1;
                    };
                    layout.hLineColor = function (i, table) {
                        if (i === 0) return '#000000';
                        if (typeof color === 'function') return color(i, table);
                        return previous === 'lightHorizontalLines' && i !== table.table.headerRows ? '#aaa' : '#000000';
                    };
                    if (typeof previous === 'string') {
                        layout.vLineWidth = function () { return 0; };
                    }
                    item.layout = layout;
                }
                if (item.stack) visit(item.stack);
                if (item.columns) visit(item.columns);
            });
        }
        visit(doc.content);
    });
})(jQuery);

$(document).ready(function () {
    $('.example').each(function () {
        var $tbl = $(this);
        // Per-table export heading: prefer the table's own data-export-title
        // (same convention as initDatatable); fall back to the first .download_label
        // so every existing page keeps its current behaviour.
        var exportTitle = $tbl.data('exportTitle') || $('.download_label').html();
        var blackExportBorder = $tbl.attr('data-export-black-border') === 'true';
        $tbl.DataTable({
            "aaSorting": [],
            rowReorder: {
            selector: 'td:nth-child(2)'
            },
            //responsive: 'false',
            dom: '<"dt-toolbar"f<"dt-toolbar-right"lB>>rtip',
            buttons: [

                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    title: exportTitle,
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                   
                    title: exportTitle,
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    title: exportTitle,
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'PDF',
                    title: exportTitle,
                    customize: function (doc) {
                        if (!blackExportBorder) return;
                        doc.content.forEach(function (item) {
                            if (!item.table) return;
                            item.layout = {
                                hLineWidth: function () { return 0.75; },
                                vLineWidth: function () { return 0.75; },
                                hLineColor: function () { return '#000000'; },
                                vLineColor: function () { return '#000000'; },
                                paddingLeft: function () { return 8; },
                                paddingRight: function () { return 8; },
                                paddingTop: function () { return 6; },
                                paddingBottom: function () { return 6; }
                            };
                        });
                    },
                    exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: exportTitle,
                 customize: function ( win ) {

                    if (blackExportBorder) {
                        $(win.document.head).append('<style>body.dt-print-view table{border-collapse:collapse!important;}body.dt-print-view table thead{display:table-header-group;}body.dt-print-view table thead th{border-top:1px solid #000!important;}</style>');
                    }

                    $(win.document.body).find('th').addClass('display').css('text-align', 'left');
                    $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                    $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                    // Only strip the H1 when it's the noisy document.title fallback (no real
                    // export title set). A real heading comes from data-export-title (exportTitle)
                    // or a .download_label — keep and centre it in either case.
                    if (!$.trim(exportTitle || '') && !$.trim($('.download_label').text())) {
                        $(win.document.body).find('h1').remove();
                    } else {
                        $(win.document.body).find('h1').css('text-align', 'center');
                    }
                },
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"],
                    format: {
                        body: function(data, row, column, node) {
                            // exportData strips HTML so checkboxes become empty — read DOM state directly.
                            var $cb = $(node).find('input[type="checkbox"]');
                            if ($cb.length) {
                                return $cb.prop('checked') ? 'Yes' : 'No';
                            }
                            return data;
                        }
                    }
                  }
                }
            ],
            "language": {
                sLengthMenu: "_MENU_"
            }
        });
    });
});


/*--dropify--*/
$(document).ready(function(){
                // Basic
                $('.filestyle').dropify();

                // Translated
                $('.dropify-fr').dropify({
                    messages: {
                        default: 'Glissez-déposez un fichier ici ou cliquez',
                        replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                        remove:  'Supprimer',
                        error:   'Désolé, le fichier trop volumineux'
                    }
                });

                // Used events
                var drEvent = $('#input-file-events').dropify();

                drEvent.on('dropify.beforeClear', function(event, element){
                    return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
                });

                drEvent.on('dropify.afterClear', function(event, element){
                    alert('File deleted');
                });

                drEvent.on('dropify.errors', function(event, element){
                    console.log('Has Errors');
                });

                var drDestroy = $('#input-file-to-destroy').dropify();
                drDestroy = drDestroy.data('filestyle')
                $('#toggleDropify').on('click', function(e){
                    e.preventDefault();
                    if (drDestroy.isDropified()) {
                        drDestroy.destroy();
                    } else {
                        drDestroy.init();
                    }
                })
            });
/*--end dropify--*/

/*--nprogress--*/
 $('body').show();
    $('.version').text(NProgress.version);
    NProgress.start();
    setTimeout(function() { NProgress.done(); $('.fade').removeClass('out'); }, 1000);
/*--nprogress--*/    
// _selector, // selector  class of table
// _url, // url is url of controller where data to be fetch
// params={}, is parameter of post method
// rm_export_btn=[], // var rm_export_btn = ["btn-pdf"] //"btn-copy","btn-excel","btn-csv","btn-pdf","btn-print" // btn-all
// pageLength=100, //per page data
// aoColumnDefs=[{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}],
// searching=true,
// aaSorting=[],
// dataSrc="data" it is array source of data


   function initDatatable(_selector,_url,params={},rm_export_btn=[],pageLength=100,aoColumnDefs=[{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}],searching=true,aaSorting=[],dataSrc="data"){
        if ($.fn.DataTable.isDataTable('.'+_selector)) { // if exist datatable it will destrory first
         $('.'+_selector).DataTable().destroy();
       }
        // Resolve the export heading once so the print customize can decide whether
        // to keep the <h1> based on the REAL title, not on .download_label existence.
        var exportTitle = $('.'+_selector).data("exportTitle");
        var table = $('.'+_selector)
    .on( 'preInit.dt', function (e, settings ) {

     var api = new $.fn.dataTable.Api( settings );
     $.each(rm_export_btn, function(key, expt_select) {
     if(expt_select === "btn-all"){
       api.buttons().remove();

     }else{
       api.buttons('.'+expt_select).remove();

     }
    });

    }).DataTable({
        // "scrollX": true,
        dom: '<"dt-toolbar"f<"dt-toolbar-right"lB>>r<t>ip',
    
         lengthMenu: [[100, -1], [100, "All"]],
       
          buttons: [
            {
                extend:    'copy',
                text:      '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                 className: "btn-copy",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'excel',
                text:      '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                     className: "btn-excel",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'csv',
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                className: "btn-csv",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'pdf',
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: "btn-pdf",
                title: exportTitle,
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  },

            },
            {
                extend:    'print',
                text:      '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                className: "btn-print",
                title: exportTitle,
                customize: function ( win ) {

                    $(win.document.body).find('th').addClass('display').css('text-align', 'left');
                    $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                     $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                    // Only strip the H1 when it's the noisy document.title fallback (no real
                    // export title set). A real heading comes from data-export-title (exportTitle)
                    // or a .download_label — keep and centre it in either case.
                    if (!$.trim(exportTitle || '') && !$.trim($('.download_label').text())) {
                        $(win.document.body).find('h1').remove();
                    } else {
                        $(win.document.body).find('h1').css('text-align', 'center');
                    }
                },
                exportOptions: {
                    columns: ["thead th:not(.noExport)"],
                    format: {
                        body: function(data, row, column, node) {
                            // exportData strips HTML so checkboxes become empty — read DOM state directly.
                            var $cb = $(node).find('input[type="checkbox"]');
                            if ($cb.length) {
                                return $cb.prop('checked') ? 'Yes' : 'No';
                            }
                            return data;
                        }
                    }
                  }

            }
        ],
      
         // "scrollY":        "320px",
         
           "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> ',
             sLengthMenu: "_MENU_"
        },
        "pageLength": pageLength,
        "searching": searching,
        "aaSorting": aaSorting, // default sorting [ [0,'asc'], [1,'asc'] ]
        "autoWidth": false,
        "aoColumnDefs": aoColumnDefs, //disable sorting { "bSortable": false, "aTargets": [ 1,2 ] }
        "processing": true,
        "serverSide": true,
        
        "ajax":{
        "url": baseurl+_url,
        "dataSrc": dataSrc,
        "type": "POST",
        'data': params,
     },
        "drawCallback": function() {
            this.api().table().container().querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                var existing = bootstrap.Tooltip.getInstance(el);
                if (existing) { existing.dispose(); }
                new bootstrap.Tooltip(el);
            });
        }

    });
    return table;
    }

   function emptyDatatable(_selector,dataSrc="data"){

        $('.'+_selector).DataTable({
        "searching": false,
        "processing": true,
        "paging":   false,
        "ordering": false,
        "info":     true,
        "ajax": {
            "url": base_url+'backend/json-files/datatable_empty.json',
            "dataSrc": dataSrc
        }
    });
    }