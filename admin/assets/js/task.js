//Based on https://wordpress.org/plugins/force-post-category-selection/
jQuery(document).ready(function () {
  jQuery('#publish').click(function () {
    var cats = jQuery('[id^=\"taxonomy\"]').find('.selectit').find('input');
    if (cats.length) {
      category_selected = false;
      for (counter = 0; counter < cats.length; counter++) {
        if (cats.get(counter).checked === true) {
          category_selected = true;
          break;
        }
      }
      if (category_selected === false) {
        alert('You have not selected any category for the post. Kindly select post category.');
        setTimeout("jQuery('#ajax-loading').css('visibility', 'hidden');", 100);
        jQuery('[id^=\"taxonomy\"]').find('.tabs-panel').css('background', '#F96');
        setTimeout("jQuery('#publish').removeClass('button-primary-disabled');", 100);
        return false;
      }
    }
  });
});