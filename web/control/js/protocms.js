// general CMS functionality

$(document).ready(function() {
  $('.datatable').dataTable({
  "lengthMenu":  [50, 100, 250, "All"],
  "lengthChange":  false
  });


    $("#jstree").jstree({
      "core" : {
        "multiple" : false
      }
    });

    $('#jstree').hide();
    $('#jstree').removeClass('hidden');
    $('#jstree').slideDown();


    $('[data-fieldtype="redactor"]').redactor({
        plugins: ['table', 'codemirror', 'imagemanager','alignment'],
        imageUpload: '/control/redactor-upload',
        imageResizable: true,
        imagePosition: true,
        codemirror: {
            lineNumbers: true,
            mode: 'xml',
            indentUnit: 4,
            theme: 'rubyblue',
            htmlMode: true,

        }
    });

    $('select[multiple="multiple"]').multiSelect();
    $('.datetimepicker').datetimepicker({format : 'DD-MM-YYYY HH:mm:ss'});

});



$('.datepicker').datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
});


function formatState (state) {
  if (!state.id) { return state.text; }
  var $state = $(
    '<span><img src="/control/flags/png/' + state.element.value.toLowerCase() + '.png" class="img-flag" /> ' + state.text + '</span>'
  );
  return $state;
};

$( "select[name*='translatableLocale']" ).select2({
  templateResult: formatState
});

//refreshes current page if user changes perpage (widget)
$('#perpage').on('change', function(){
  location.href= $(location).attr('pathname')+'?perpage='+$(this).val();
});

//submit search on button press (widget)
$('#searcbox_submit').on('click', function(){
    $('#searchform').submit();
})

// cms sidebar search
$('form.sidebar-form div input').on('keyup', function(){

    var inputted = $(this).val();
    if(inputted.lenth < 1){
        $('ul.sidebar-menu li').show();
    }else{
        $('ul.sidebar-menu li').each(function(index) {
            if( $(this).attr('class') != 'header'){
                $(this).hide();
                var text =  $(this).find('a span').text();
                if (text.toLowerCase().indexOf(inputted) >= 0){
                    $(this).show();
                }
            }
        })
    }

})

// checks/unchecks all checkboxes
$(".checkbox-toggle").click(function () {
    var clicks = $(this).data('clicks');
    if (clicks) {
    //Uncheck all checkboxes
        $("input[type='checkbox']").prop( "checked", false );
        $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
      } else {
        //Check all checkboxes
        $("input[type='checkbox']").prop( "checked", true );
        $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
      }
      $(this).data("clicks", !clicks);
});

// simple form poster
function postForm(formID){
    $(formID).submit();
}

function ajaxError(str){
    var html ='';
    html +='<div class="alert alert-danger">';
    html +='<ul class="list-unstyled">';
    html +='        <li><span style="padding:0px" class="glyphicon glyphicon-exclamation-sign"></span> '+str+'</li>';
    html +='</ul>';
    return html;
}

//throttle function waits 1 sec after input
//to prevent multiple ajax calls on keyUp events
function throttle(f, delay){
    var timer = null;
    return function(){
        var context = this, args = arguments;
        clearTimeout(timer);
        timer = window.setTimeout(function(){
            f.apply(context, args);
        },
        delay || 1000);
    };
}
