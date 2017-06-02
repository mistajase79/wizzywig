//////////////////////////////////
// PAGE SLUG PREVIEW GENERATOR
//////////////////////////////////


$('#jstree').on("changed.jstree", function (e, data) {
  $('#page_parent').val(data.selected);
  var selectedText = $('#page_parent option:selected').text();
  if(selectedText == ""){ selectedText = "Root"; }
  $('#page_parent_text').text(selectedText);
  generateSlugPreview();
});

$('#jstree').on("loaded.jstree", function(){
    var inputelement = $('#page_parent option:selected');
    $('#jstree').jstree(true).select_node(inputelement.val());
});

$('#page_url').keyup(throttle(function(){
    //throttle function waits 1 sec after input
    generateSlugPreview();
}));
$('#page_parent').on('change', function(){
    generateSlugPreview();
});
$('.components-area').on('change', 'select', function(){
    generateSlugPreview();
});

$('#page_extraUrlsegments').on('change', 'select', function(){
    generateSlugPreview();
});

function checkURLBasedComponentsExtendSlug(pageslug){
    $('.urlbasederror').remove();
    var hasURL = 0;
    var extendSlug = "";
    var currentSlug = pageslug;

    $('.components-area select option:selected').each( function( index, element ){
        if($(this).data('urlbased') == 1){ hasURL++; }
        extendSlug = $(this).data('slug');
    });

    if(hasURL>1){
        $('.components-area select option:selected').each( function( index, element ){
            if($(this).data('urlbased') == 1){
                var htmlerror  ='<span class="has-error urlbasederror"><span class="help-block">';
                    htmlerror +='<span class="glyphicon glyphicon-exclamation-sign"></span>Only 1 URL Based Component Allowed';
                    htmlerror +='</span></span>';
                $(this).closest('div').append(htmlerror);
            }
        });
    }

    //clear existing slug
    if(hasURL == 1){

        //currentSlug = currentSlug.replace(/\/{.*?\}/, '');
        //$('#slug_preview').html(currentSlug);

        $('.components-area select option:selected').each( function( index, element ){
            if($(this).data('urlbased') == 1){ extendSlug = $(this).data('slug');}
        });
        if (currentSlug.indexOf(extendSlug) < 0){
            currentSlug = currentSlug+'/'+extendSlug;
            $('#slug_preview').html(currentSlug.replace('//', '/'));
        }
    }
}

function generateSlugPreview(){
    var pagetitle = $('#page_url').val();
    var parentId = $('#page_parent').val();

    var extraArray = [];
    $('#page_extraUrlsegments select option:selected').each(function(index) {
        extraArray[index] = $(this).val();
    });

    $('#slug_preview_loader').show();

        $.ajax({
            type: "POST",
            url : '/control/page/ajax-slug-preview',
            data: { pagetitle: pagetitle, parentId: parentId, extraArray:extraArray},
            success: function(data){
                $('#slug_preview_loader').hide();
                $('#slug_preview').html('/'+data);
                checkURLBasedComponentsExtendSlug('/'+data);

            }
        });

}


$('#page_template').on('change', function(){

    var pageID = $('#pageID').val();
    var templateID = $(this).val();

    $('#template_components_loader').show();
    $('#template_html_loader').show();
    $('.components-area').html('');
    $('.html-area').html('');

        $.ajax({
            type: "POST",
            url : '/control/page/ajax/fetch-template',
            data: { page: pageID, template: templateID},
            dataType: 'json',
            success: function(data){
                $('#template_components_loader').hide();
                $('#template_html_loader').hide();
                if(data.status == 'success'){
                    $('.components-area').html(data.comTemplate);
                    $('.html-area').html(data.htmlTemplate);
                    generateSlugPreview();
                }else{
                    $('.components-area').html(ajaxError(data.message));
                }
            }
        });
    //generateSlugPreview();
});


////////////////////////////////
// AJAX TRANSLATABLE SLUGS
////////////////////////////////
$('#page_translation_url').keyup(throttle(function(){
    //throttle function waits 1 sec after input
    generateTranslatableSlugPreview();
}));

function generateTranslatableSlugPreview(){
    var pagetitle = $('#page_translation_title').val();
    var pageurl = $('#page_translation_url').val();
    var parentId = $('#page_parent').val();
    var locale = $('#page_translation_translatableLocale').val();

    var extraArray = [];
    $('.page_extraUrlsegments').each(function(index) {
        extraArray[index] = $(this).val();
    });

    $('#slug_preview_loader').show();

        $.ajax({
            type: "POST",
            url : '/control/page/ajax-translatable-slug-preview',
            data: { pageurl: pagetitle, pageurl: pageurl, parentId: parentId, locale:locale, extraArray:extraArray},
            success: function(data){
                $('#slug_preview_loader').hide();
                $('#slug_preview').html('/'+data);
                checkURLBasedComponentsExtendSlugTranslation('/'+data);

            }
        });

}


function checkURLBasedComponentsExtendSlugTranslation(pageslug){

    var hasURL = 0;
    var extendSlug = "";
    //var currentSlug = $('#slug_preview').html();
    var currentSlug = pageslug;

    $('.selected-component').each( function( index, element ){
        if($(this).data('urlbased') == 1){ extendSlug = $(this).data('slug');}
    });

    if (currentSlug.indexOf(extendSlug) < 0){
        currentSlug = currentSlug+'/'+extendSlug;
        $('#slug_preview').html(currentSlug.replace('//', '/'));
    }

}



////////////////////////////////
// EXTRA URL SEGMENTS (advancedArea)
////////////////////////////////

var segmentCount = $('#page_extraUrlsegments').attr('data-segmentcount');

$('#showAdvancedButton').on('click', function(){
    $('#advancedArea').show();
});

$(document).on('click', '.removeExtraurl', function(){
    $(this).parent().remove();
    generateSlugPreview();
});

$('#insertUrlSegment').click(function(e) {
    e.preventDefault();
    var emailList = $('#extraUrlsegments-fields-list');
    var newWidget = $('#extraUrlsegments-fields-list').attr('data-prototype');
    var buttonHtml = "&nbsp;<a class='removeExtraurl'>Remove</a>";
    newWidget = newWidget.replace(/__name__/g, segmentCount);
    var newLi = $('<li>'+newWidget+'</li>');
    newLi.appendTo(emailList);
    $('#page_extraUrlsegments_'+segmentCount+' .form-group').append(buttonHtml);
    segmentCount++;
});
