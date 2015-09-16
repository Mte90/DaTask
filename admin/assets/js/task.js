//Based on https://wordpress.org/plugins/force-post-category-selection/
jQuery(document).ready(function () {
  jQuery('#publish').click(function (e) {
    var ids = ['taxonomy-task-area', 'taxonomy-task-difficulty', 'taxonomy-task-team', 'taxonomy-task-minute'];
    var category_selected = true;
    var ids_missing = [];
    jQuery(ids).each(function (index, value) {
      var cats = jQuery('[id^=\"' + value + '\"]').find('.selectit').find('input:checked');
      if (cats.length === 0) {
        category_selected = false;
        ids_missing.push(value);
      }
    });
    if (category_selected === false) {
      alert(dt_js_admin_vars.alert);
      setTimeout("jQuery('#ajax-loading').css('visibility', 'hidden');", 100);
      setTimeout("jQuery('#publish').removeClass('button-primary-disabled');", 100);
      jQuery(ids).each(function (index, value) {
        jQuery('[id^=\"' + value + '\"]').find('.tabs-panel').css('background', '#F96');
      });      
      e.preventDefault();
    }
  });
});