// var $triggered = $('#test_form_days');
// var $selectors = $('#test_form_days, #test_form_hours, #test_form_munites');

// $(document).on('click', '.deletelanguage', function(){
//   alert("success");
// });

var selectors = '#test_form_days, #test_form_hours, #test_form_minutes';

// $selectors.change(function() {
$(document).on('change', selectors, function() {
  var $triggered = $(this);

  // console.log($triggered.attr('id'));

  var $form = $triggered.closest('form');
  var data = {};

  //TODO: populate with all the checkboxes.
  // data[$triggered.attr('name')] = $triggered.is(':checked') ? 'true' : '__false';

  $(selectors.split(',')).each(function(key, value) {
    $value = $($.trim(value));
    // console.log($value);
    // data[$value.attr('name')] = $value.is(':checked') ? 'true' : '__false';
    // data[$value.attr('name')] = $value.is(':checked') ? true : 'asd';
    data[$value.attr('name')] = $value.is(':checked')
  });

  var $target_selector = $triggered.nextAll('input');
  // var $target_selector = $triggered.nextAll('input').first()
  // console.log($target_selector);

  $.ajax({
    url : $form.attr('action'),
    type: $form.attr('method'),
    data : data,
    success: function(html) {
      console.log(html);

      // $target_selector.replaceWith(
      //   $(html).find('#' + $target_selector.attr('id'))
      // );

      $target_selector.each(function (key, value) {
        $value = $(value);
        $value.replaceWith(
          $(html).find('#' + $value.attr('id'))
        );
      });


    }
  });
});
