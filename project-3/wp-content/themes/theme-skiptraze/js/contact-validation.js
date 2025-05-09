function validateFirstName() {
    var firstNameInput = document.getElementById('ContactForm-first_name');
    var firstNameField = document.querySelector('.field-ContactForm-first_name');
    var errorFirstName = document.getElementById('error_first_name');
    var isValid;

    function updateValidation() {
        var fname = firstNameInput.value;
        isValid = fname.length > 2;
        firstNameField.classList.toggle('has-error', !isValid);
        firstNameField.classList.toggle('has-success', isValid);
        errorFirstName.textContent = isValid ? '' : 'Please enter your first name.';
    }

    firstNameInput.addEventListener('input', updateValidation);
    updateValidation();

    return isValid;
}

function validateLastName() {
    var lastNameInput = document.getElementById('ContactForm-last_name');
    var lastNameField = document.querySelector('.field-ContactForm-last_name');
    var errorLastName = document.getElementById('error_last_name');
    var isValid;

    function updateValidation() {
        var lname = lastNameInput.value;
        isValid = lname.length > 1;
        lastNameField.classList.toggle('has-error', !isValid);
        lastNameField.classList.toggle('has-success', isValid);
        errorLastName.textContent = isValid ? '' : 'Please enter your last name.';
    }

    lastNameInput.addEventListener('input', updateValidation);
    updateValidation();
    
    return isValid;
}

function forceText(){
    var input = jQuery(this);
    input.val(input.val().replace(/[\d]+/g,''));
}
jQuery('body').on('propertychange input', '.text-only', forceText);

function validateCompany() {
    var companyInput = document.getElementById('ContactForm-company');
    var companyField = document.querySelector('.field-ContactForm-company');
    var errorCompany = document.getElementById('error_company');
    var isValid;
    function updateValidation() {
        var company = companyInput.value.trim();
        isValid = company !== '';

        companyField.classList.toggle('has-error', !isValid);
        companyField.classList.toggle('has-success', isValid);
        errorCompany.textContent = isValid ? '' : 'Please enter your company name.';
    }

    companyInput.addEventListener('input', updateValidation);
    updateValidation();

    return isValid;
}

function validateMessage() {
    var messageInput = document.getElementById('ContactForm-message');
    var messageField = document.querySelector('.field-ContactForm-message');
    var errorMessage = document.getElementById('error_message');
    var isValid;

    function updateValidation() {
        var message = messageInput.value.trim();
        isValid = message !== '';

        messageField.classList.toggle('has-error', !isValid);
        messageField.classList.toggle('has-success', isValid);
        errorMessage.textContent = isValid ? '' : 'Please enter your message.';
    }

    messageInput.addEventListener('input', updateValidation);
    updateValidation();

    return isValid;
}

function validateForm() {
    var isFirstNameValid = validateFirstName();
    var isLastNameValid = validateLastName();
    var isCompanyValid = validateCompany();
    var isMessageValid = validateMessage();

    if (!isFirstNameValid || !isLastNameValid || !isCompanyValid || !isMessageValid) {
        
        var errorElement = document.querySelector('.has-error');

        if (errorElement) {
            errorElement.scrollIntoView({ behavior: 'smooth' });
        }
        return false;
    } else {
        // Toggle loading on submit button 
        var button_submit = document.querySelector('.submit_button');
        button_submit.classList.toggle('loading_btn');

        // Form submission code (AJAX) goes here
        var first_name = document.getElementById('ContactForm-first_name').value;
        var last_name = document.getElementById('ContactForm-last_name').value;
        var company = document.getElementById('ContactForm-company').value;
        var message = document.getElementById('ContactForm-message').value;

        jQuery.ajax({
            type: "POST",
            url: frontend_ajax_object.ajaxurl,
            data: {
                action: 'submit_contact',
                data: { first_name:first_name, last_name:last_name, company:company, message:message }
            }
        }).done(function(response) {
            resJSON = JSON.parse( response );
            console.log(resJSON.status);
            if(resJSON.status == 'success'){
                window.location.href = '';
            } else {
                console.log('Error:', resJSON.response);
            }
        })

        return true;
    }
}

jQuery(document).ready(function($) {
    
    document.getElementById('ContactForm-first_name').addEventListener('keyup', validateFirstName);
    document.getElementById('ContactForm-last_name').addEventListener('keyup', validateLastName);
    document.getElementById('ContactForm-company').addEventListener('keyup', validateCompany);
    document.getElementById('ContactForm-message').addEventListener('keyup', validateMessage);

    $('#contact-form').on('submit', function(e){
        e.preventDefault();
        validateForm();
    });
});
