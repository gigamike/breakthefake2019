$(document).ready(function(){
  $("#tabs").tabs();
  $('a[href="#ex1"]').click(function(event) {
   event.preventDefault();
   $(this).modal({
     fadeDuration: 1000,
     fadeDelay: 0.50
   });
 });
});
