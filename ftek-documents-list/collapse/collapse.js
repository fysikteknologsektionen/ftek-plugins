/* For collapsing sections of documents */

jQuery(document).ready(function() {
  jQuery('.collapsible').click(function() {
    jQuery(this).toggleClass('expanded');
    jQuery(this).next().slideToggle('fast');
  })
});

