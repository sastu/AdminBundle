var SelectCurrentSection = function() {
    // select the current section on the sidebar
    return {

        init: function() {
            var section = $('body').attr('current');
            var selected_span = $('<span class="selected"></span>');

            $('#section-' + section).addClass('active');
            //open multilevel block
            

            if ('widget' == section || 'calendar' == section) {
                $('#section-' + section).children('a').first().append(selected_span);
            } else {
                var parent = $('#section-' + section).parent().parent();
                //console.log(parent);
                var ul = parent.find('ul');
                
                var link = parent.children('a').first();
                var arrow = link.children('i').last();
//                console.log(link.html());
                arrow.removeClass('fa-angle-left');
                arrow.addClass('fa-angle-down');
                ul.show();
                if(ul.parent().hasClass('treeview')){
                    ul.parent().parent().show();
                    var root = ul.parent().parent().parent();
                    root.addClass('active');
                    var link = root.children('a').first();
                    var rootarrow = link.children('i').last();
                    rootarrow.removeClass('fa-angle-left');
                    rootarrow.addClass('fa-angle-down');
                }
                if(ul.parent().parent().hasClass('treeview-menu')){
                    ul.parent().parent().show();
                    var root = ul.parent().parent().parent().parent();
                    root.addClass('active');
                    var link = root.children('a').first();
                    var rootarrow = link.children('i').last();
                    rootarrow.removeClass('fa-angle-left');
                    rootarrow.addClass('fa-angle-down');
                }
            }
        }

    };
}();
