var TableManaged = {

    // main function to initiate the module
    init: function (id, url, not_sortable_cols, columns, languageUrl) {
        if (!jQuery().dataTable) {
            return;
        }

        $(id).dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": url,
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": not_sortable_cols
            }],
            "aoColumns": columns,
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sUrl": languageUrl
            },
            "aaSorting": [[ 0, "desc" ]] // Sort by first column descending
        });
    }
}