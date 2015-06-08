<?php
/**
 * @package   Wp-Oneanddone
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2014 GPL
 */

function complete_task() {
	check_ajax_referer( 'wo-task-nonce', $_GET['nonce'] );
	
	wp_die();
} 
add_action( 'wp_ajax_nopriv_complete_task', 'complete_task' );

?>

