(function ($) {

    $(document).ready(function(){
        $('h1').click(function(event){
        alert(event.target.id); 
        });

    });
})(jQuery);