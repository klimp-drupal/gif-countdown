// var selectors = '#test_form_hours, #test_form_minutes, #test_form_seconds';
var selectors = '#test_form_checkboxes_hours, #test_form_checkboxes_minutes, #test_form_checkboxes_seconds';

// We use on to bind the event to the elements rendered by ajax responses.
$(document).on('change', selectors, function() {
  var $triggered = $(this);
  var $form = $triggered.closest('form');

  var data = {};
  $(selectors.split(',')).each(function(key, value) {
    $value = $($.trim(value));
    data[$value.attr('name')] = $value.is(':checked')
  });

  // data[$('#test_form_date').attr('name')] = $('#test_form_date').val();
  // data[$('#test_form_timezone').attr('name')] = $('#test_form_timezone').val();

  var $target_selectors = $triggered.nextAll('input');

  $.ajax({
    url : $form.attr('action'),
    type: $form.attr('method'),
    data : data,

    success: function(html) {

      $target_selectors.each(function (key, value) {
        $value = $(value);
        $value.replaceWith(
          $(html).find('#' + $value.attr('id'))
        );
      });

      console.log(html);
      $('#test_form_date').replaceWith(
        $(html).find('#test_form_date')
      );

    }

  });

});
