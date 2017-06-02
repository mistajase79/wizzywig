//////////////////////////////////
// SUMMERNOTE FUNCTIONS
//////////////////////////////////
$("[data-fieldtype='summernote']").summernote({
    toolbar: [
      // [groupName, [list of button]]
      ['cleaner',['cleaner']],
      ['style', ['style','bold', 'italic', 'underline', 'clear']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['insert', ['picture', 'video']],
      ['other', ['codeview', 'undo', 'redo']],
  ],
    cleaner:{
          notTime: 2400, // Time to display Notifications.
          action: 'both', // both|button|paste 'button' only cleans via toolbar button, 'paste' only clean when pasting content, both does both options.
          newline: '<br>', // Summernote's default is to use '<p><br></p>'
          notStyle: 'position:absolute;top:0;left:0;right:0', // Position of Notification
          icon: '<i class="note-icon">[Your Button]</i>',
          keepHtml: false, // Remove all Html formats
          keepClasses: false, // Remove Classes
          badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'], // Remove full tags with contents
          badAttributes: ['style', 'start'] // Remove attributes from remaining tags
    },
  height: 250,                 // set editor height
  minHeight: null,             // set minimum height of editor
  maxHeight: null,             // set maximum height of editor
  focus: true                  // set focus to editable area after initializing summernote
});
