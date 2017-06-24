//////////////////////////////////
// IMAGE MANAGER FUNCTIONS
//////////////////////////////////

$( document ).ready(function() {

    $('.imagemanager_modal_open').each( function(index, element){
        var element_id = $(this).attr('data-id');
        element_id = element_id + '_'+index;
        $(this).attr('data-id', element_id);

    })

    $('.imagemanager_formwidget').each( function(index, element){
        var element_id = $(this).attr('data-widgetid');
        element_id = element_id + '_'+index;

        //console.log('img_imagefield = ' + element_id);

        $(this).attr('data-widgetid', element_id);
        $(this).find('.img_imagefield').attr('id', element_id)
        $(this).find('.hidden_imagefield').attr('id', element_id)
    })

});


$(document).on('click', '.imagemanager_modal_open', function(){
    $("#imagemanager_modal").modal();

    var path = $(this).attr('data-imagepath');
    $('#imagemanager-pathtext').html(path);
    $('#image_manager_filePath').val(path+'/');
    $('#imagemanager-message').text('');
    refreshBrowser(path);
    var element_id = $(this).attr('data-id');
    //console.log(element_id);
    //console.log('confirm-image = ' + element_id);
    $('#confirm-image').attr('data-input',element_id);
});

function truncateString(str, length) {
     return str.length > length ? str.substring(0, length - 3) + '...' : str
  }

function refreshBrowser(path){

    $('#folder-content').html('<p id="imagemanager-ajaxloader"><img src="/control/ajax-loader-med.gif" alt="Loading..." /></p>');

    $.ajax({
         url: '/control/imagemanager-open',
         type: "POST",
         dataType:'json',
         data: ({path: path}),
         success: function(response){
             if(response.status =='error'){
                 $('#imagemanager-message').text('Status: ' + response.message);
                 $('#folder-content').html('<p>No files found</p>');
             }else{
                 var folderContents = "";
                 $.each(response.data, function( key, file ) {
                    //console.log(file);
                    folderContents += "<div class='imagemanager-image' data-selected='false' data-filename='"+file.name+"' ><img src='"+file.icon+"' width='105px' height='105px' /><br/><span>"+truncateString(file.name, 18)+"</span></div>";
                 });

                 folderContents += "<br class='clear'><p>&nbsp;</p>";
                 $('#folder-content').html(folderContents);
                 $('#uploadfileInput').removeAttr('disabled');
             }

            var ie_version =  getIEVersion();
            if(ie_version.major > 0 && ie_version.major <10){
                $('#imagemanager-message').text('Your version of IE is not compatable with ImageManager - update IE or try a different browser');
            }
         }
    });

}

$(document).on('click', '#refreshfilebrowser', function(){
    var path = $('#imagemanager-pathtext').html();
    refreshBrowser(path);
})

$(document).on('click', '#openfilebrowser', function(){
    $('#uploadfileInput').trigger('click');
})


$(document).on('click', '.imagemanager-image', function(){
    var selected = $(this).attr('data-filename');
    //console.log(selected);
    $('.imagemanager-image').attr('data-selected', 'false');

    $(this).attr('data-selected', 'true');
    $('#selected-image').val(selected);
})


$(document).on('click', '#confirm-image', function(){
    var input_id = $(this).attr('data-input');

    //console.log('#'+input_id + '= '+$('#selected-image').val());
    var path = $('#'+input_id).parent().find('img').attr('data-imagepath');
    $('#'+input_id).parent().find('img').attr('src', path+'/tn_'+$('#selected-image').val());
    $('#'+input_id).parent().find('span').text($('#selected-image').val());
    $('#'+input_id).parent().find('.hidden_imagefield').val($('#selected-image').val());
    $("#imagemanager_modal").modal('hide');
})


$(document).on('change', '#uploadfileInput', function(){

      $('#imagemanager-uploadloader').show();
      $('#imagemanager-message').text('');

      var formData = new FormData($('#uploadfileform')[0]);
       $.ajax({
           url: '/control/imagemanager-upload',
           type: 'POST',
           data: formData,
           async: false,
           cache: false,
           contentType: false,
           enctype: 'multipart/form-data',
           dataType:'json',
           processData: false,
           async: true,
           success: function (response) {
               $('#imagemanager-uploadloader').hide();
               var folderContents = "";
               if(response.status == 'error'){
                   $.each(response.errors, function( key, error ) {
                       $('#imagemanager-message').text('Error: ' + error);
                    });
               }else{
                    $('.imagemanager-image').each(function() {
                      $( this ).attr('data-selected', 'false' );
                    });
                    folderContents += "<div class='imagemanager-image' data-selected='true' data-filename='"+response.data.name+"'><img src='"+response.data.icon+"' width='105px' height='105px' /><br/><span>"+truncateString(response.data.name, 18)+"</span></div>";
                    $('#folder-content').prepend(folderContents);
                    $('#folder-content').animate({scrollTop: 0}, 200);
                    $('#selected-image').val(response.data.name);
               }

               //clear file input - had to do thing this way due to IE
               var input = $("#uploadfileInput");
               input.replaceWith(input.val('').clone(true));
           }
       });
       return false;
})


function getIEVersion(){
    var agent = navigator.userAgent;
    var reg = /MSIE\s?(\d+)(?:\.(\d+))?/i;
    var matches = agent.match(reg);
    if (matches != null) {
        return { major: matches[1], minor: matches[2] };
    }
    return { major: "-1", minor: "-1" };
}
