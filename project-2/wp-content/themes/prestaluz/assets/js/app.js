jQuery(document).ready(function ($) {
  "use strict";

  const $window = $(window);

  // Header
  $window.scroll(function(){
    $("header").toggleClass("sticky-header", $(this).scrollTop() > 50);
  });

  $("header").toggleClass("sticky-header", $(this).scrollTop() > 50);

  // Header menu click
  $('.navigation .menu-item a').on('click', function (e) {

    // Remove the 'active' class from all menu links
    $('.navigation a').removeClass('active');

    // Add the 'active' class to the clicked menu link
    $(this).find('a').addClass('active');
  });

  // Cache navigation menu links
  var menuLinks = $('.navigation a');

  // Function to highlight the menu link based on the visible section
  function highlightMenu() {
    var scrollPosition = $(document).scrollTop(); // Get current scroll position
    var windowHeight = $(window).height();

    // Remove active class from all links by default
    menuLinks.removeClass('active');

    // Loop through sections to find the current one in view
    $('section[id]').each(function () {
      var sectionId = $(this).attr('id'); // Section ID
      var sectionOffset = $(this).offset().top; // Section offset from top
      var sectionHeight = $(this).outerHeight(); // Section height

      // Check if the section is in the viewport
      if (
        scrollPosition >= sectionOffset - windowHeight / 3 &&
        scrollPosition < sectionOffset + sectionHeight - windowHeight / 3
      ) {
        // Add active class to corresponding link
        $('.navigation a[href="#' + sectionId + '"]').addClass('active');
      }
    });
  }

  // Run the highlightMenu function on scroll and page load
  $(window).on('scroll', highlightMenu);
  highlightMenu();

  // Menu scroll to designated section
  $('a[href^="#"]').on('click', function(event) {
    event.preventDefault();

    const targetId = this.getAttribute('href');
    const offset = 100;
    const target = $(targetId);

    if (target.length) {
      // If the section exists, scroll to it
      $('html, body').animate({
        scrollTop: target.offset().top - offset
      }, 800); // Adjust 800 to control the animation speed (in milliseconds)
    } else {
      // If the section doesn't exist, always redirect to the homepage with the section ID
      window.location.href = `${frontend.url}/${targetId}`;
    }

    $(".mobile_menu_container").removeClass("open");
    $("#open_menu").text("Menu");
  });
  
  const hash = window.location.hash;
  const offset = 100;

  if (hash) {
    const target = $(hash);
    if (target.length) {
      $('html, body').animate({
        scrollTop: target.offset().top - offset
      }, 800);
    }
  }

  // Elements to animate
  const elementsToAnimate = $('.heading, .subtext, .list, .desc, .action_btn, .keywords_content, .wrapper_image, .card-item, .help_text, .item-pergunta, .list, .footer_logo, .footer_contact, .footer_information, .policies, .card_icon_mobile, .beneficiary_details, .payment_form, .help_text, .congratulation_content, .form-buttons-container, .content, .forgot_and_create_account, .offer_card, .content_item, .offer_btn, .offer_image, .contact_card, .content_step-3, .email_icon, .continue_button');

  //Add css opacity to 0 for elements to animate
  elementsToAnimate.css('opacity', '0');

  // Intersection Observer for fadeUp animation
  const fadeUpObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        // Animate the element and add transition delay on the opacity
        $(entry.target).addClass('fadeInUp').css({ opacity: 1}, 1000);
        fadeUpObserver.unobserve(entry.target); // Stop observing once animated
      }
    });
  });

  // Observe each element to animate
  elementsToAnimate.each(function() {
    fadeUpObserver.observe(this);
  });

  // Debounced scroll event listener for "top" button
  let debounceTimeout;
  $window.on('scroll', function() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(function() {
      if ($window.scrollTop() > 10) {
        $('.top').fadeIn();
      } else {
        $('.top').fadeOut();
      }
    }, 100);
  });

  // Smooth scroll to top when "top" button is clicked
  $('.top').on('click', function(e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 'slow');
  });

  // Trigger the scroll event on load to initiate animations on elements already in view
  $window.trigger('scroll');

  $(".js-show-pergunta").on("click", function () {
    var isActive = $(this).hasClass("active");
    $(this).parents(".all").find(".item-pergunta").removeClass("active");
    if (!isActive) {
      $(this).addClass("active");
    }
  });

  // Mobile menu
  $(".menu_btn").on("click", function() {
    $(".mobile_menu_container").toggleClass("open");
  
    // Check if the container has the 'open' class and add/remove overflow hidden
    $("body").css("overflow", $(".mobile_menu_container").hasClass("open") ? "hidden" : "auto");
    
    // Update the button text
    $("#open_menu").text($(".mobile_menu_container").hasClass("open") ? "Close" : "Menu");
  });
  
  // Close the menu and reset overflow when a menu item is clicked
  $(".mobile_menu_container ul li").on("click", function() {
    $(".mobile_menu_container").removeClass("open");
    $("body").css("overflow", "auto");
    $("#open_menu").text("Menu"); // Reset the button text
  });

  // Application form
  var currentStep = 1;
  var totalSteps = 5;

  function updateStep() {
    // Hide all steps
    for (var i = 1; i <= totalSteps; i++) {
      $('#step-' + i).removeClass('show');
      $('.form_step-' + i).removeClass('active visited');
    }

    // Show the current step
    $('#step-' + currentStep).addClass('show');
    $('.form_step-' + currentStep).addClass('active');

    // Add the 'visited' class to the previous steps
    for (var i = 1; i < currentStep; i++) {
      $('.form_step-' + i).addClass('visited');
    }

    // Hide "Previous" button on step 1 and show it on others
    if (currentStep == 1) {
      $('#previous-button-step-1').hide();
    } else {
      $('#previous-button-step-1').show();
    }

    // Update the class on the form_steps container
    $('.form_steps').removeClass(function(index, className) {
      return (className.match(/form_step-\d+/g) || []).join(' ');
    }).addClass('form_step-' + currentStep);

    // Check if user has moved to step 2
    if (currentStep == 2) {
      // Remove fadeInUp class from form-buttons-container on step 1
      // 
      $('.form-buttons-container').removeClass('fadeInUp');
      
      // Add fadeInUp class to form-buttons-container on step 2
      setTimeout(function() {
        $('.form-buttons-container').addClass('fadeInUp');
      }, 200); // Optional: Add delay to ensure animation happens smoothly
    } else if (currentStep == 1) {
      // Remove fadeInUp class from form-buttons-container when moving back to step 1
      $('.form-buttons-container').removeClass('fadeInUp');
      
      // Optionally, add fadeInUp class to form-buttons-container again when back to step 1
      setTimeout(function() {
        $('.form-buttons-container').addClass('fadeInUp');
      }, 200); // Optional: Add delay for smooth animation
    }
  }

  // Next button functionality
  $('#submit-button-step-1').click(function() {
    if (currentStep < totalSteps) {
      currentStep++;
      updateStep();
    }
  });

  // Previous button functionality
  $('#previous-button-step-1').click(function() {
    if (currentStep > 1) {
      currentStep--;
      updateStep();
    }
  });

  // Step navigation click: Fix Me: Add validation to prevent user to click on other steps if steps has not been completed/visited
  $('.steps_wrapper .steps').click(function() {
    var step = $(this).data('step');
    if (step && step !== currentStep) {
      currentStep = step;
      updateStep();
    }
  });

  $('#button-step-1').click(function(e) {
    e.preventDefault();
    currentStep = 1;
    updateStep();
  });

  $('#button-step-2').click(function(e) {
    e.preventDefault();
    currentStep = 2;
    updateStep();
  });

  $('#button-step-4').click(function(e) {
    e.preventDefault();
    currentStep = 4;
    updateStep();
  });

  // Initial setup
  updateStep();

  // Step 2: 

  $('#amount_loan').change(function() {
    var amount = $(this).val();

    if (amount != 0) {
      // Directly set the amount as the total to return
      $('.total-amount .amount').text(amount + '€');
    } else {
      // Reset the value to '?' if no valid selection
      $('.total-amount .amount').text('?');
    }
  });

  // rotate arrow on click - Step 2
  $('#amount_loan').on('click', function() {
    $(this).toggleClass('rotate');
  });

  $('#date_return').on('click', function() {
    $(this).toggleClass('rotate');
  });

  $('#procedure').on('click', function() {
    $(this).toggleClass('rotate');
  });

  $(document).on('click', function(event) {
    if (!$(event.target).is('#amount_loan, #date_return, #procedure')) {
      $('#amount_loan, #date_return, #procedure').removeClass('rotate');
    }
  });

  // Get the select element
  const dateReturnSelect = document.getElementById('date_return');
  const procedureSelect = document.getElementById('procedure');
  const amountLoanSelect = document.getElementById('amount_loan');

  function updateSelectColor(selectElement) {
    if (!selectElement) {
      console.warn('Element not found:', selectElement);
      return;
    }
    
    const value = selectElement.value;
    selectElement.style.color = value === '0' ? 'rgba(54, 53, 49, 0.5)' : 'rgba(54, 53, 49, 1)';
  }

  // Update colors based on initial values
  if (dateReturnSelect) updateSelectColor(dateReturnSelect);
  if (procedureSelect) updateSelectColor(procedureSelect);
  if (amountLoanSelect) updateSelectColor(amountLoanSelect);

  // Add event listeners if you want to update the colors dynamically when values change
  if (dateReturnSelect) dateReturnSelect.addEventListener('change', () => updateSelectColor(dateReturnSelect));
  if (procedureSelect) procedureSelect.addEventListener('change', () => updateSelectColor(procedureSelect));
  if (amountLoanSelect) amountLoanSelect.addEventListener('change', () => updateSelectColor(amountLoanSelect));


  // Step 3: Verification Code Input (Limit to 4 characters, replace asterisk)
  const $verificationInput = $("#verification_code");

  // Set default value to 4 asterisks
  $verificationInput.val("****");

  // Limit input to 4 characters and replace asterisk on input or delete
  $verificationInput.on("input", function () {
    let inputValue = $verificationInput.val();

    // Allow only numbers and limit input to 4 characters
    inputValue = inputValue.replace(/[^0-9]/g, "").slice(0, 4);

    // Update input value with asterisks, replacing only inputted characters
    let maskedValue = "****";
    for (let i = 0; i < inputValue.length; i++) {
      maskedValue = maskedValue.substring(0, i) + inputValue[i] + maskedValue.substring(i + 1);
    }

    // Set the input field value to the masked value
    $verificationInput.val(maskedValue);

    // Check if all 4 digits are entered, then proceed to next step
    if (inputValue.length === 4) {
      setTimeout(function () {
        $('#submit-button-step-1').click(); // Trigger the next step after entering the code
      }, 500); // Optional: Add a delay to simulate user action
    }
  });

  // Prevent default form submission (if any) on Enter key press
  $verificationInput.on("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault(); // Prevent form submission when Enter is pressed
    }
  });

  // Focus management to ensure the cursor stays at the end of the input
  $verificationInput.on('focus', function() {
    $(this)[0].setSelectionRange(4, 4); // Keep the cursor at the end
  });

  // Handle left-to-right deletion logic
  $verificationInput.on("keydown", function(e) {
    // Detect the backspace (delete key) event
    if (e.key === "Backspace") {
      let currentValue = $verificationInput.val();
      
      // Check if there is a number in the code and replace it with an asterisk
      if (currentValue.includes("****")) {
        let updatedValue = currentValue.split("").map(c => (c === "*" ? "*" : "")).join("");
        $verificationInput.val(updatedValue);
      }
    }
  });

  $(".offer_items .container").on("click", function() {
    $(this).find('a.arrow')[0].click();
  });
  

  // Checkbox Policy - check all
  // Make labels clickable by binding the click event to the labels
  $('.form-checkbox-label').click(function() {
    // Find the checkbox associated with the label
    var checkbox = $(this).prev('input[type="checkbox"]');
    checkbox.prop('checked', !checkbox.prop('checked'));
    // Trigger change event to handle "select all" logic
    checkbox.change();
  });

  // When "Select/Deselect All" is clicked
  $('#LeadForm-select_all_policies').change(function() {
    var isChecked = $(this).prop('checked');
    // Check/uncheck all checkboxes
    $('input[type="checkbox"]').prop('checked', isChecked);
  });

  // When any individual checkbox is changed
  $('input[type="checkbox"]:not(#LeadForm-select_all_policies)').change(function() {
    var allChecked = true;
    // Check if all checkboxes are checked
    $('input[type="checkbox"]:not(#LeadForm-select_all_policies)').each(function() {
      if (!$(this).prop('checked')) {
        allChecked = false;
      }
    });
    // If not all checkboxes are checked, uncheck the "Select/Deselect All"
    $('#LeadForm-select_all_policies').prop('checked', allChecked);
  });

  // Listen for changes to the select element
  
  $('#procedure').change(function() {
    // Get the new value
    let selectedValue = $(this).val();
    let selectedText = $('#procedure option:selected').text();

    // Update the output
    $('#output').text('Selected Value: ' + selectedValue + ' (' + selectedText + ')');

    console.log(selectedText);

    if(selectedText == 'Reembolso' || selectedText == 'Cancelar'){
      $('.accordion').removeClass('hide-faq');
      if(selectedText == 'Reembolso'){
        $('.cancel-faq').hide('cancel-faq')
        $('.reimburse-faq').show('reimburse-faq')
      }
      if(selectedText == 'Cancelar'){
        $('.reimburse-faq').hide('show-faq')
        $('.cancel-faq').show('cancel-faq')
      }
    }else{
      $('.accordion').addClass('hide-faq');
    }
  });

  const infoWrapper = document.querySelector('.info_wrapper');
  const customScrollbar = document.querySelector('.custom_scrollbar');
  
  if (infoWrapper && customScrollbar) {
    infoWrapper.addEventListener('scroll', () => {
      const scrollWidth = infoWrapper.scrollWidth - infoWrapper.clientWidth; // Total scrollable width
      const scrollPosition = infoWrapper.scrollLeft; // Current scroll position
      
      // Calculate the percentage of scroll position
      const scrollPercentage = (scrollPosition / scrollWidth) * 100;
      
      // Calculate the maximum left position for the custom scrollbar
      const maxLeftPosition = 100 - (customScrollbar.offsetWidth / infoWrapper.clientWidth) * 100;
      
      // Set the left position of the custom scrollbar, ensuring it doesn't overflow
      const newLeftPosition = Math.min(scrollPercentage, maxLeftPosition);
      
      customScrollbar.style.left = `${newLeftPosition}%`;
    });
  }
});