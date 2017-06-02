//////////////////////////////////
// MENU BUILDER FUNCTIONS
//////////////////////////////////

$(document).ready(function() {
	var ns = $('ol.sortable').nestedSortable({
		forcePlaceholderSize: true,
		excludeRoot: true,
		handle: 'div',
		items: 'li',
		opacity: .6,
		placeholder: 'placeholder',
		revert: 250,
		tabSize: 25,
		tolerance: 'pointer',
		toleranceElement: '> div',
		isTree: true,
		expandOnHover: 700,
		startCollapsed: false
	});

});

$('.menu-pagelist button').on('click', function(e){
	e.preventDefault();
	var pageId = $(this).attr('data-pageId');
	var navtitle = $(this).attr('data-title');
	var slug = $(this).attr('data-slug');
	var currentItems = $('.menu-activelist ol li').length;
	//console.log('currentItems = '+currentItems);
	var html = $("#item-template").html();
	html = html.replace( /__num__/g, (currentItems+1) );
	html = html.replace( /__pageId__/g, pageId );
	html = html.replace( /__navtitle__/g, navtitle ) ;
	html = html.replace( /__slug__/g, slug ) ;
    html = html.replace( /__nameoverride__/g, navtitle ) ;
	//console.log(html);
	$('#nestedMenu').append(html);
})


$('.expandEditor').attr('title','Click to show/hide item editor');
$('.disclose').attr('title','Click to show/hide children');
$('.deleteMenu').attr('title', 'Click to delete item.');

$('.menu-activelist').on('click', '.disclose', function(e){
	e.preventDefault(e);
	$(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
	$(this).find('span').toggleClass('glyphicon-plus').toggleClass('glyphicon-minus');
});

$('.menu-activelist').on('click', '.expandEditor, .itemTitle', function(e){
	e.preventDefault();
	$('ol.sortable').nestedSortable('refresh');
	var id = $(this).attr('data-id');
	$('#menuEdit'+id).slideToggle();
	$(this).find('span').toggleClass('glyphicon-triangle-top').toggleClass('glyphicon-triangle-bottom');
});

$('.menu-activelist').on('click', '.deleteMenu', function(e){
	e.preventDefault(e);
	$('ol.sortable').nestedSortable('refresh');
	var id = $(this).attr('data-id');
	$('#menuItem_'+id).remove();
});

$('#serialize').click(function(e){
	serialized = $('ol.sortable').nestedSortable('serialize');
	$('#serializeOutput').text(serialized+'\n\n');
})

$('#toHierarchy').click(function(e){
	hiered = $('ol.sortable').nestedSortable('toHierarchy', {startDepthCount: 0});
	hiered = dump(hiered);
	(typeof($('#toHierarchyOutput')[0].textContent) != 'undefined') ?
	$('#toHierarchyOutput')[0].textContent = hiered : $('#toHierarchyOutput')[0].innerText = hiered;
})

$('#parsemenu').click(function(e){
	e.preventDefault();
	arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});
	arraied_output = dump(arraied);
	var menuData = JSON.stringify(arraied);
	console.log(menuData);
	$('#menu_menu_json').val(menuData);
	$('#menuform').submit();


});


function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Strings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}
