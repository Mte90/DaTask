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
    add_shortcode( 'dt_task_number', array( $this, 'dt_task_number' ) );
  }

  /**
   * Generate a graph of the task created
   *
   * @since    1.0.0
   * @param array $atts The attributes.
   */
  public function dt_task_number( $atts ) {
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
    echo '<div id="postmonth"></div>'
    . '<script>' .
    'MG.data_graphic({
        title: "Show the task created in the last 12 months",
        data: ' . str_replace( ')"', ')', str_replace( '"new', 'new', wp_json_encode( $postcounts, JSON_NUMERIC_CHECK ) ) ) . ',
        width: 800,
        height: 400,
        interpolate: d3.curveLinear,
        xax_count: 4,
        right: 40,
        target: "#postmonth",
	  mouseover: function(d, i) {
		var df = d3.timeFormat("%d/%m/%Y");
		var date = df(d.date);
            d3.select("#postmonth svg .mg-active-datapoint").text(date + " " + d.value + " Tasks");
        }
    });'
    . '</script>';
  }

}

new DT_Graphs();
