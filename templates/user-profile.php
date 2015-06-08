<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$plugin = Wp_Oneanddone::get_instance();
get_header();
?>
<div id="content-main" class="main" role="main">
	<?php 
		get_tasks_completed();
	?>
</div>
<?php
get_footer();
