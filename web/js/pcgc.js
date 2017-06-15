/* ------------------ */
//  PCGC JS FILE
/* ------------------ */

function gotoLink($url){
    // used for onclicks - similar behavior as clicking on a link except googlebots wont follow
    window.location.href = $url;
}

$('#social-pop-out-trigger').on('click', function(){
  $('.social-pop-out-box').slideToggle();
  if( $('.twitter-pop-out-box').is(":visible")){
    $('.twitter-pop-out-box').slideToggle();
  }
})

$('#twitter-pop-out-trigger').on('click', function(){
  $('.twitter-pop-out-box').slideToggle();

  if($('.social-pop-out-box').is(":visible")){
    $('.social-pop-out-box').slideToggle();
  }
})

$('.search-button').on('click', function(){
  $('.search-container').slideToggle();
})
