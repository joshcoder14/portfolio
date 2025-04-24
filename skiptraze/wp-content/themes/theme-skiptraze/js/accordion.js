jQuery(document).ready(function ($) {
    "use strict";
  
    $(".accordion-header").click(function(){
        // self clicking close
        if($(this).next(".accordion-body").hasClass("active")){
          $(this).next(".accordion-body").removeClass("active").slideUp();
          $(this).children(".arrow-up").removeClass("down").addClass("up");
        }
        else{
          $(".accordion-card .accordion-body").removeClass("active").slideUp();
          $(".accordion-card .accordion-header .arrow-up").removeClass("down").addClass("up");
          $(this).next(".accordion-body").addClass("active").slideDown();
          $(this).children(".arrow-up").removeClass("up").addClass("down");
        }
    });
});