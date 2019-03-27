var selectors = '#test_form_hours, #test_form_minutes';

// $selectors.change(function() {
$(document).on('change', selectors, function() {
  var $triggered = $(this);

  var $form = $triggered.closest('form');
  var data = {};

  $(selectors.split(',')).each(function(key, value) {
    $value = $($.trim(value));
    data[$value.attr('name')] = $value.is(':checked')
  });

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
    }
  });

});
