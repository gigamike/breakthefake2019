var $j = jQuery.noConflict();
$j(document).ready(function(){
 $j(document).on('click', 'a[href="#ex1"]', function(e) {
   $j("#tabs").tabs();
   event.preventDefault();
   $j(this).modal({
     fadeDuration: 1000,
     fadeDelay: 0.50
   });
 });
});
