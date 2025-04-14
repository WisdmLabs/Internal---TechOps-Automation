// form_tracking.js

jQuery(document).ready(function ($) {
  // Function to check if the confirmation message is visible
  function checkConfirmation() {
    if ($(".gform_confirmation_message").is(":visible")) {
      console.log("form-tracking-file");
      var form_data = form_data_object; // Assuming form_data_object is passed from PHP to the script
      console.log(form_data.form_fields);
      dataLayer.push({
        event: form_data.form_title ? form_data.form_title : "form_tracking",
        ecommerce: {
          form_title: form_data.form_title,
          form_fields: form_data.form_fields,
        },
      });
    } else {
      // Confirmation message not visible yet, check again after a short delay
      setTimeout(checkConfirmation, 100); // Check again after 100 milliseconds
    }
  }

  // Start checking for the confirmation message visibility
  checkConfirmation();
});
