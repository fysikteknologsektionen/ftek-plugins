<?php
/**
 * 
 * Security check. No one can access without Wordpress itself
 * 
 * */
defined('ABSPATH') or die();
if( !current_user_can('manage_vbl_files') ) die();
	
?>

<div id='vbl-documents-manager-wrapper'>

</div>

<script>

PLUGINS_URL = '<?php echo plugins_url();?>';

jQuery(document).ready(function(){
	jQuery('#vbl-documents-manager-wrapper').elfinder({
		url: ajaxurl,
		customData:{action: 'connector2'}
	});
});

</script>

<?php 

if( isset( $this->options->options['file_manager_settings']['show_url_path'] ) && !empty( $this->options->options['file_manager_settings']['show_url_path']) && $this->options->options['file_manager_settings']['show_url_path'] == 'hide' ){
	
?>
<style>
.elfinder-info-tb > tbody:nth-child(1) > tr:nth-child(2),
.elfinder-info-tb > tbody:nth-child(1) > tr:nth-child(3)
{
	display: none;
}
</style>
<?php
	
}

?>
