/* ------------------------------------------ */
//  PCGC JS REDACTOR INLINE EDITOR FUNCTIONS
/* ------------------------------------------ */

//Secondary Redactor Close Button
$(document).on('click','#redactor-modal-close2', function(){
    $('#redactor-modal-close').trigger('click');
})

$(function()
{

    $(".inlineEditor").each(function(){

        var elementId   = $(this).attr('id');
        var entitynamespace = $('#'+elementId).attr('data-entitynamespace');
        var field       = $('#'+elementId).attr('data-entityfield');
        var id          = $('#'+elementId).attr('data-id');
        var locale      = $('#'+elementId).attr('data-locale');
        var editabletext = $('#inlineEditor-message-'+elementId).html();

        // remove toolbar on string inputs only (no html allowed);
        var fulleditor   = $('#'+elementId).attr('data-fulleditor');
        if(fulleditor == 1){

            //SHOW FULL EDITOR (HTML) including toolbar
            $('#'+elementId).redactor({
                // air: true,
                toolbar: true,
                clickToEdit: true,
                buttons: ['format', 'bold', 'italic'],
                plugins: ['table','cleaner','source','alignment'],
                codemirror: {
                    lineNumbers: true,
                    mode: 'xml',
                    indentUnit: 4,
                    theme: 'rubyblue',
                    htmlMode: true,
                },
                clickToSave: '#btn-save-'+elementId,
                callbacks: {
                    focus: function(){
                        showFocus(elementId, editabletext);
                    },
                    save: function(html){
                        var data = {entitynamespace:entitynamespace, field:field, id:id, locale:locale, data:html};
                        sendInlineEditorAjax(elementId, data);
                        //this.source.hide()
                    }
                }
            });

        }else{
            //SHOW SINGLE LINE EDITOR (NO HTML)
            $('#'+elementId).redactor({
                // air: true,
                toolbar: false,
                pasteInlineTags: [],
                pasteBlockTags: [],
                pastePlainText: true,
                pasteImages: false,
                pasteLinks: false,
                clickToEdit: true,
                deniedTags: ['br', 'p', 'a'],
                formatting: [],
                enterKey: false,
                pastePlainText: true,
                linebreaks: false,
                paragraphize: false,
                buttons: [],
                plugins: [],
                clickToSave: '#btn-save-'+elementId,
                callbacks: {
                    focus: function(){
                        showFocus(elementId, editabletext);
                    },
                    save: function(html){
                        var plainText = this.clean.getPlainText(html)
                        var data = {entitynamespace:entitynamespace, field:field, id:id, locale:locale, data:plainText, currentUrl:window.location.pathname};

                        sendInlineEditorAjax(elementId, data, this);

                    },
                }
            });
        }

    })

});

function setCaretPosition(elemId, caretPos) {
    var elem = document.getElementById(elemId);

    if(elem != null) {
        if(elem.createTextRange) {
            var range = elem.createTextRange();
            range.move('character', caretPos);
            range.select();
        }
        else {
            if(elem.selectionStart) {
                elem.focus();
                elem.setSelectionRange(caretPos, caretPos);
            }
            else
                elem.focus();
        }
    }
}

function showFocus(elementId, editabletext){
    $('#inlineEditor-message-'+elementId).css('background-color','#367fa9').html(editabletext);
    $('#inlineEditor-message-'+elementId).show();
}

function sendInlineEditorAjax(elementId, data, redactor){
    $('#inlineEditor-message-'+elementId).html('<img style="padding:2px 10px 2px 10px;" height="14px;" src="/control/ajax-loader-imagemanager.gif"  />');
    $.ajax({
        url : "/control/page/ajax-inline-editor-update",
        type: "POST",
        data : data,
        dataType: 'json',
        success: function(data, textStatus, jqXHR){
            if(data.status == 'success'){
                $('#inlineEditor-message-'+elementId).css('background-color','darkgreen').html('Saved');
            }
            if(data.status == 'notice'){
                redactor.modal.load('name', 'title', 600);
                redactor.modal.setTitle(data.title);
                redactor.modal.show();
                $('#redactor-modal-body').html(data.message);
                $('#inlineEditor-message-'+elementId).css('background-color','darkgreen').html('Saved');
            }
            if(data.status == 'error'){
                $('#inlineEditor-message-'+elementId).css('background-color','darkred').html('Error');
            }

			$('#'+elementId).html(data.returnedContent)
        },
        error: function (jqXHR, textStatus, errorThrown){
            $('#inlineEditor-message-'+elementId).css('background-color','darkred').html('Error');
        }
    });
}
