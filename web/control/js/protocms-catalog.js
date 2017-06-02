//////////////////////////////////
// CATALOG BUNDLE FUNCTIONS
//////////////////////////////////
$('#jstree').on("changed.jstree", function (e, data) {
  $('#catalog_categories_parent').val(data.selected);
  var selectedText = $('#catalog_categories_parent option:selected').text();
  if(selectedText == ""){ selectedText = "Root"; }
  $('#category_text').text(selectedText);
});

$('#jstree').on("loaded.jstree", function(){
    var inputelement = $('#catalog_categories_parent option:selected');
    $('#jstree').jstree(true).select_node(inputelement.val());
});
