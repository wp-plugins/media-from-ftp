jQuery(function(){
  jQuery('.checkAll').on('change', function() {
    jQuery('.' + this.id).prop('checked', this.checked);
  });
});
