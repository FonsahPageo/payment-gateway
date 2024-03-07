jQuery(function($) {
  $('input[name="payment_gateway"]').change(function() {
    var selectedGateway = $(this).val();
    $('#payment_fields > div').hide();
    $('#' + selectedGateway + '_payment_fields').show();
  });

  // Trigger the change event to hide the payment fields initially
  $('input[name="payment_gateway"]:not(:checked)').trigger('change');
});