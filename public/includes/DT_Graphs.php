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
    add_shortcode( 'dt_task_number', array( $this, 'task_number' ) );
    add_shortcode( 'dt_task_month_activity', array( $this, 'task_month_activity' ) );
    add_shortcode( 'dt_task_daily_activity', array( $this, 'task_daily_activity' ) );
  }

  /**
   * Generate a graph of the task created
   *
   * @since    1.0.0
   * @param array $atts The attributes.
   */
  public function task_number() {
    global $wpdb;

    $postcountbymonth = $wpdb->get_results( 'select * from (select MONTH(post_date) as mo,YEAR(post_date) as ye,count(ID) as co from ' . $wpdb->posts . " where (post_status='publish' and post_type='task') group by MONTH(post_date),YEAR(post_date) order by post_date desc limit 12) a order by ye asc,mo asc" );

    $postcounts = array();
    foreach ( $postcountbymonth as $pc ) {
	$month = $pc->mo;
	if ( count( $month ) < 2 ) {
	  $month = '0' . $month;
	}
	$postcounts[] = array( 'date' => "new Date('$pc->ye-$month-01T24:00:00Z')", 'value' => $pc->co );
    }
    echo '<div id="postcreated"></div>'
    . '<script>' .
    'MG.data_graphic({
        title: "' . __( 'Show the task created in the last 12 months', DT_TEXTDOMAIN ) . '",
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
   */
  public function task_month_activity() {
    global $wpdb;

    $postcountbymonth = $wpdb->get_results( 'select * from (select MONTH(post_date) as mo,YEAR(post_date) as ye,count(ID) as co from ' . $wpdb->posts . " where (post_status='publish' and post_type='wdslp-wds-log') group by MONTH(post_date),YEAR(post_date) order by post_date desc limit 12) a order by ye asc,mo asc" );

    $postcounts = array();
    foreach ( $postcountbymonth as $pc ) {
	$month = $pc->mo;
	if ( count( $month ) < 1 ) {
	  $month = '0' . $month;
	}
	$postcounts[] = array( 'date' => "new Date('$pc->ye-$month-01T24:00:00Z')", 'value' => $pc->co );
    }
    echo '<div id="postactivity"></div>'
    . '<script>' .
    'MG.data_graphic({
        title: "' . __( 'Show the task activity in the last 12 months', DT_TEXTDOMAIN ) . '",
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
  }

  /**
   * Generate a graph of the task daily activity
   *
   * @since    1.0.0
   */
  public function task_daily_activity() {
    global $wpdb;

    $postcountbymonth = $wpdb->get_results( 'select * from (select MONTH(post_date) as mo,DAY(post_date) as day,count(ID) as co from ' . $wpdb->posts . " where (post_status='publish' and post_type='wdslp-wds-log') group by DAY(post_date),MONTH(post_date) order by post_date desc limit 12) a order by day asc,mo asc" );

    $postcounts = array();
    foreach ( $postcountbymonth as $pc ) {
	$month = $pc->mo;
	if ( count( $month ) < 1 ) {
	  $month = '0' . $month;
	}
	$day = $pc->day;
	if ( count( $day ) < 1 ) {
	  $day = '0' . $day;
	}

	$postcounts[] = array( 'date' => "new Date('" . date( 'Y' ) . '-' . $month . '-' . $day . "T24:00:00Z')", 'value' => $pc->co );
    }
    echo '<div id="postdailyactivity"></div>'
    . '<script>' .
    'MG.data_graphic({
        title: "' . __( 'Show the task daily activity in the this months', DT_TEXTDOMAIN ) . '",
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
  }

}

new DT_Graphs();
