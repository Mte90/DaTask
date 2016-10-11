<?php

/**
 * Graph in DaTask
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2016 GPL
 */
class DT_Graphs {

  /**
   * Initialize the class with all the hooks
   *
   * @since     1.0.0
   */
  public function __construct() {
    add_shortcode( 'datask-task-number', array( $this, 'task_number' ) );
    add_shortcode( 'datask-task-month-activity', array( $this, 'task_month_activity' ) );
    add_shortcode( 'datask-task-daily-activity', array( $this, 'task_daily_activity' ) );
  }

  /**
   * Generate a graph of the task created
   *
   * @since    1.0.0
   */
  public function task_number() {
    $args = array(
	  'post_type' => 'task',
	  'posts_per_page' => -1,
	  'date_query' => array(
		array(
		    'after' => '12 month ago',
		),
	  ),
    );
    $query = new WP_Query( $args );

    $postquery = array();
    foreach ( $query->posts as $post ) {
	$date = explode( ' ', $post->post_date );
	if ( !isset( $postquery[ $date[ 0 ] ] ) ) {
	  $postquery[ $date[ 0 ] ] = 0;
	}
	$postquery[ $date[ 0 ] ] += 1;
    }

    foreach ( $postquery as $key => $count ) {
	$postcounts[] = array( 'date' => "new Date('" . $key . "T24:00:00Z')", 'value' => $count );
    }
    echo '<div id="postcreated"></div>'
    . '<script>' .
    'MG.data_graphic({
        title: "' . sprintf( __( 'The %s tasks created in the last 12 months', DT_TEXTDOMAIN ), count( $query->posts ) ) . '",
        data: ' . str_replace( ')"', ')', str_replace( '"new', 'new', wp_json_encode( $postcounts, JSON_NUMERIC_CHECK ) ) ) . ',
        width: 800,
        height: 400,
        interpolate: d3.curveLinear,
        xax_count: 4,
        right: 40,
        target: "#postcreated",
	  mouseover: function(d, i) {
		var df = d3.timeFormat("%d/%m/%Y");
		var date = df(d.date);
            d3.select("#postcreated svg .mg-active-datapoint").text(date + " " + d.value + " Tasks");
        }
    });'
    . '</script>';
  }

  /**
   * Generate a graph of the task monthly activity
   *
   * @since    1.0.0
   * @param	   array $atts The attribute.
   */
  public function task_month_activity( $atts ) {
    $args = array(
	  'post_type' => 'datask-log',
	  'posts_per_page' => -1,
	  'tax_query' => array(
		array(
		    'taxonomy' => 'wds_log_type',
		    'field' => 'slug',
		    'terms' => array( 'error','pending' ),
		    'operator' => 'NOT',
		),
	  ),
	  'date_query' => array(
		array(
		    'after' => '12 month ago',
		),
	  ),
    );
    $query = new WP_Query( $args );

    $postquery = $postid = array();
    foreach ( $query->posts as $post ) {
	$date = explode( ' ', $post->post_date );
	if ( !isset( $postquery[ $date[ 0 ] ] ) ) {
	  $postquery[ $date[ 0 ] ] = 0;
	}
	$postquery[ $date[ 0 ] ] += 1;

	if ( isset( $atts[ 'list' ] ) && $atts[ 'list' ] ) {
	  $id_task = get_post_meta( $post->ID, DT_TEXTDOMAIN . '_id', true );
	  if ( !isset( $postid[ $id_task ] ) ) {
	    $postid[ $id_task ] = 0;
	  }
	  $postid[ $id_task ] += 1;
	}
    }

    foreach ( $postquery as $key => $count ) {
	$postcounts[] = array( 'date' => "new Date('" . $key . "T24:00:00Z')", 'value' => $count );
    }
    echo '<div id="postactivity"></div>'
    . '<script>' .
    'MG.data_graphic({
        title: "' . sprintf( __( 'The %s tasks activity of last 12 months', DT_TEXTDOMAIN ), count( $query->posts ) ) . '",
        data: ' . str_replace( ')"', ')', str_replace( '"new', 'new', wp_json_encode( $postcounts, JSON_NUMERIC_CHECK ) ) ) . ',
        width: 800,
        height: 400,
        interpolate: d3.curveLinear,
        xax_count: 4,
        right: 40,
        target: "#postactivity",
	  mouseover: function(d, i) {
		var df = d3.timeFormat("%d/%m/%Y");
		var date = df(d.date);
            d3.select("#postactivity svg .mg-active-datapoint").text(date + " " + d.value + " done");
        }
    });'
    . '</script>';
    if ( isset( $atts[ 'list' ] ) && $atts[ 'list' ] ) {
	echo '<ul>';
	foreach ( $postid as $key => $count ) {
	  echo '<li><a href="' . get_permalink( $key ) . '" target="_blank">' . get_the_title( $key ) . '</a> ' . $count . ' ' . __( 'times', DT_TEXTDOMAIN ) . '</li>';
	}
	echo '</ul>';
    }
  }

  /**
   * Generate a graph of the task daily activity
   *
   * @since    1.0.0
   * @param	   array $atts The attribute.
   */
  public function task_daily_activity( $atts ) {
    $args = array(
	  'post_type' => 'datask-log',
	  'posts_per_page' => -1,
	  'tax_query' => array(
		array(
		    'taxonomy' => 'wds_log_type',
		    'field' => 'slug',
		    'terms' => array( 'error', 'pending' ),
		    'operator' => 'NOT',
		),
	  ),
	  'date_query' => array(
		array(
		    'after' => '1 month ago',
		),
	  ),
    );
    $query = new WP_Query( $args );

    $postquery = $postid = array();
    foreach ( $query->posts as $post ) {
	$date = explode( ' ', $post->post_date );
	if ( !isset( $postquery[ $date[ 0 ] ] ) ) {
	  $postquery[ $date[ 0 ] ] = 0;
	}
	$postquery[ $date[ 0 ] ] += 1;

	if ( isset( $atts[ 'list' ] ) && $atts[ 'list' ] ) {
	  $id_task = get_post_meta( $post->ID, DT_TEXTDOMAIN . '_id', true );
	  if ( !isset( $postid[ $id_task ] ) ) {
	    $postid[ $id_task ] = 0;
	  }
	  $postid[ $id_task ] += 1;
	}
    }

    foreach ( $postquery as $key => $count ) {
	$postcounts[] = array( 'date' => "new Date('" . $key . "T24:00:00Z')", 'value' => $count );
    }
    echo '<div id="postdailyactivity"></div><script>' .
    'MG.data_graphic({
        title: "' . sprintf( __( 'The %s tasks daily activity of this months', DT_TEXTDOMAIN ), count( $query->posts ) ) . '",
        data: ' . str_replace( ')"', ')', str_replace( '"new', 'new', wp_json_encode( $postcounts, JSON_NUMERIC_CHECK ) ) ) . ',
        width: 800,
        height: 400,
        interpolate: d3.curveLinear,
        xax_count: 4,
        right: 40,
        target: "#postdailyactivity",
	  mouseover: function(d, i) {
		var df = d3.timeFormat("%d/%m/%Y");
		var date = df(d.date);
            d3.select("#postdailyactivity svg .mg-active-datapoint").text(date + " " + d.value + " done");
        }
    });'
    . '</script>';
    if ( isset( $atts[ 'list' ] ) && $atts[ 'list' ] ) {
	echo '<ul>';
	foreach ( $postid as $key => $count ) {
	  echo '<li><a href="' . get_permalink( $key ) . '" target="_blank">' . get_the_title( $key ) . '</a> ' . $count . ' ' . __( 'times', DT_TEXTDOMAIN ) . '</li>';
	}
	echo '</ul>';
    }
  }

}

new DT_Graphs();
