jQuery(document).ready(function ($) {
  "use strict";
  
  // Company on fucos
  $("#ContactForm-company").on("focus", function() {
    $(".text-right").addClass("hide-content");
  });
  $("#ContactForm-company").on("blur", function() {       
      $(".text-right").removeClass("hide-content");
  });

  // Message calculator and Limits characters
  var maxCharacters = 300;
                
  $("#ContactForm-message").on("input", function() {
    var text = $(this).val();
    var remainingCharacters = maxCharacters - text.length;
    
    if (remainingCharacters < 0) {
        // Truncate the input to the maximum allowed characters
        $(this).val(text.substr(0, maxCharacters));
        remainingCharacters = 0;
    }
    
    $(".text-counter").text(remainingCharacters + "/" + maxCharacters);
    
    // Disable input when the character limit is reached
    if (remainingCharacters <= 0) {
        $(this).attr("maxlength", maxCharacters);
    } else {
        $(this).removeAttr("maxlength");
    }
  });


  // Filter Accordion List
  $('.accord-list .accord-item ').click(function() {
      $('.accord-list .accord-item').removeClass('active');
      $(this).addClass('active');

      if ($('.collect-accord').hasClass('active')) {
        $('.collection').addClass('show-accordion');
        $('.data').removeClass('show-accordion');
        $('.data-accord').removeClass('active');
      } else if ($('.data-accord').hasClass('active')) {
        $('.data').addClass('show-accordion');
        $('.collection').removeClass('show-accordion');
        $('.collect-accord').removeClass('active');
      }
  });
})