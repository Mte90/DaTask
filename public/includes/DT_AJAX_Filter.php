<?php

/**
 * DT_AJAX_Filter
 * Based on Simple Search Ajax of James Irving-Swift
 *
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @link      http://mte90.net
 * @copyright 2015 GPL
 */
class DT_AJAX_Filter {

	/**
	 * Initialize the class with all the hooks
	 *
	 * @since     1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wpoad-ajax-search', array( $this, 'create_filtered_section' ) );
		add_action( 'wp_ajax_nopriv_wpoad-ajax-search', array( $this, 'create_filtered_section' ) );
		add_shortcode( 'datask-search', array( $this, 'ajax_filter' ) );
	}

	/**
	 * Build the filtered element on the search results page
	 * 
	 * @since       1.7.0
	 * @param       int         $posts_per_page
	 * 
	 * @return      string      HTML for results
	 */
	public function create_filtered_section( $posts_per_page = 10 ) {
		$filters = array();
		// post data passed, so update values 
		if ( $_GET ) {
			// secure with a nonce 
			check_ajax_referer( 'filternonce' );

			// grab post data 
			$_GET_filters = isset( $_GET[ 'filters' ] ) ? explode( '&', $_GET[ 'filters' ] ) : null;

			if ( isset( $_GET[ 'postsperpage' ] ) ) {
				$posts_per_page = $_GET[ 'postsperpage' ];
			}
		}

		// counter 
		$c = 0;

		if ( isset( $_GET_filters ) && $_GET_filters[ 0 ] != "" ) { //check that the array isn't blank
			// this while loop puts the filters in a usable array 
			while ( $c < count( $_GET_filters ) ) {
				// explode string to array 
				$string = explode( '=', $_GET_filters[ $c ] );

				// check if each item is an array - or caste 
				if ( !isset( $filters[ $string[ 0 ] ] ) || !is_array( $filters[ $string[ 0 ] ] ) ) {
					$filters[ $string[ 0 ] ] = array();
				}
				// add items to array 
				array_push( $filters[ $string[ 0 ] ], $string[ 1 ] );

				// clean up empty items 
				array_filter( $filters );

				// iterate 
				$c++;
			}
		}

		// build args list 
		$args = array(
		    "post_type" => array( 'task' ), "posts_per_page" => ( int ) $posts_per_page, "tax_query" => array(), "orderby" => 'title', "order" => 'DESC', "post_status" => "publish"
		);

		// check if paging value passed, if so add to the query 
		if ( isset( $_GET[ 'paged' ] ) ) {
			$args[ 'paged' ] = $_GET[ 'paged' ];
		} else {
			$args[ 'paged' ] = 1;
		}

		if ( isset( $filters ) && !empty( $filters ) ) {
			// add all the filters to tax_query 
			foreach ( $filters as $taxonomy => $ids ) {
				if ( $taxonomy !== 'search' ) {
					foreach ( $ids as $id ) {
						array_push( $args[ 'tax_query' ], array(
						    'taxonomy' => $taxonomy,
						    'field' => 'id',
						    'terms' => $id
							)
						);
					}
				} else {
					$args[ 's' ] = $ids[ 0 ];
				}
			}
			$args[ 'tax_query' ][ 'relation' ] = 'AND';
		}

		// counter 
		$i = 0;

		// new WP_Query 
		$dt_ajax_filter_wp_query = new WP_Query();

		// parse args 
		$dt_ajax_filter_wp_query->query( $args );

		if ( $dt_ajax_filter_wp_query->have_posts() ) {
			while ( $dt_ajax_filter_wp_query->have_posts() ) {
				$dt_ajax_filter_wp_query->the_post();
				?>
				<article class="ajax-loaded">
				    <h3><?php the_title(); ?></h3>
				<?php the_post_thumbnail( array( 150, 150 ) ); ?>
				    <p><?php the_excerpt(); ?></p>
				    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php _e( "Read More" ); ?></a>
				</article>
				<?php
				// iterate 
				$i++;
			} // while loop 
		} else {
			echo "<p class='no-results'>";
			_e( "No Results found :(" );
			echo "</p>";
		}
		$this->pagination( $dt_ajax_filter_wp_query->found_posts, $posts_per_page );

		// reset global post object 
		wp_reset_query();

		// called from ajax - so needs to die 
		if ( $_GET ) {
			die();
		}
	}

	/**
	 * Buid pagination 
	 * 
	 * @since       1.4.0
	 * @return      String      HTML for pagination
	 */
	public function pagination( $total_posts, $posts_per_page ) {
		?>
		<nav class="ajax-pagination">
		    <ul class="pagination">
			<?php
			if ( $_GET ) {
				?>
				<?php
				if ( isset( $_GET[ 'paged' ] ) && $_GET[ 'paged' ] > 1 ) {
					$page_number = ( int ) $_GET[ 'paged' ];
				} else {
					$page_number = 1;
				}
				if ( isset( $_GET[ 'postsperpage' ] ) ) {
					$posts_per_page = ( int ) $_GET[ 'postsperpage' ];
				} else {
					$posts_per_page = 10;
				}
			} else {
				$page_number = 1;
			}
			$pages = ( int ) ceil( $total_posts / $posts_per_page ); // add the pages
			//Print position 1
			if ( $page_number >= 1 ) {
				if ( 1 === $page_number ) {
					$active = ' class="active"';
				} else {
					$active = '';
				}
				if ( $page_number !== 2 ) {
					?>
					<li<?php echo $active ?>><a href='#' class='pagelink-1 pagelink' rel="1">1</a></li>
					<?php
				}
				if ( $page_number > 3 ) {
					?>
					<li><a>...</a></li>
					<?php
				}
			}
			//print 3 page
			if ( $page_number - 1 !== 0 ) {
				?>
				<li class="minus"><a href="#" class="pagelink-<?php echo ($page_number - 1); ?> pagelink" rel="<?php echo ($page_number - 1); ?>"><?php echo ($page_number - 1); ?></a></li>
				<?php
			}
			if ( $page_number !== 1 ) {
				?>
				<li class="active"><a href="#" class="pagelink-<?php echo $page_number; ?> pagelink" rel="<?php echo $page_number; ?>"><?php echo $page_number; ?></a></li>
				<?php
			}
			if ( $page_number !== $pages ) {
				?>
				<li class="plus"><a href="#" class="pagelink-<?php echo ($page_number + 1); ?> pagelink" rel="<?php echo ($page_number + 1); ?>"><?php echo ($page_number + 1); ?></a></li>
				<?php
			}

			// If the current page is less than the last page minus $posts_per_page pages divided by 2 
			if ( $page_number < ( $pages - floor( $posts_per_page / 2 ) ) ) {
				?>
				<li><a>...</a></li>
				<li><a href='#' class="pagelink-<?php echo $pages; ?> pagelink" rel="<?php echo $pages; ?>"><?php echo $pages; ?></a></li>
				<?php
			}
			?>
		    </ul>
		</nav>
		<?php
	}

	/**
	 * build list of terms to filter by
	 * 
	 * @since       1.7.0
	 * @param       array   $taxonomies
	 * @param       string  
	 * @param       int     $show_count
	 * 
	 * @return      string      HTML for filter nav
	 */
	public function create_filter_nav( $filter_type = 'select', $show_count = 0 ) {
		$taxonomies = array( 'task-area', 'task-difficulty', 'task-minute' );

		$searcher = isset( $_GET[ "s" ] ) ? $_GET[ "s" ] : "";
		?>
		<div id="ajax-filters" class="ajax-filters">

		    <div class="form-group">
			<input type="text" value="<?php echo $searcher; ?>" name="searcher" id="searcher" placeholder="<?php _e( "Search" ); ?>" class="filter-selected form-control" />
		    </div>
		    <div class="form-group">
			<?php
			if ( $taxonomies && isset( $taxonomies[ 0 ] ) && $taxonomies[ 0 ] > '' ) {

				foreach ( $taxonomies as $taxonomy ) {

					$terms = get_terms( $taxonomy, array(
					    'orderby' => 'name',
					    'hide_empty' => 1
						)
					);

					if ( !isset( $terms ) || empty( $terms ) || is_wp_error( $terms ) ) {
						continue;
					}

					reset( $terms );
					$first_key = key( $terms );

					// nothing cooking in this taxonomy 
					if ( !$terms[ $first_key ] ) {
						continue;
					}

					// get tax name 
					$the_tax = get_taxonomy( $terms[ $first_key ]->taxonomy );
					$the_tax_name = $the_tax->labels->singular_name;

					// select or list items ? 
					switch ( $filter_type ) {
						// build selects for changing values 
						case "select";
							echo '<select class="filter-' . $taxonomy . ' ajax-select form-control">';
							echo "<option value=\"\" class=\"default\">-- " . $the_tax_name . " --</option>";
							foreach ( $terms as $term ) {
								echo '<option class="filter-selected" value="' . $term->term_id . '" data-tax="' . $term->term_id . '" data-slug="' . $taxonomy . '">';
								echo $term->name;
								if ( $show_count == 1 ) {
									echo ' (' . $term->count . ')';
								}
								echo '</option>';
							}
							echo '</select>';
							break;

						// build list items for changing values 
						case "list";
						default;
							echo '<div class="filter-' . $taxonomy . ' filter-selected">';
							echo '<h3>' . $the_tax_name . '</h3>';
							echo '<ul class="list-group">';
							foreach ( $terms as $term ) {
								echo '<li class="list-group-item"><input class="ajax-list" type="checkbox" data-tax="' . $term->term_id . '" data-slug="' . $taxonomy . '" /> <label>' . $term->name . '</a></label>';
								if ( $show_count == 1 ) {
									echo '<span class="badge">' . $term->count . '</span>';
								}
								echo '</li>';
							}
							echo '</ul></div>';
							break;
					}
				}
			}
			?> 
		    </div> 
		    <div class="form-group">
			<input type="submit" id="go" class="go filter btn btn-primary" value="<?php _e( "Search" ); ?>" />
			<input type="reset" id="reset" class="reset btn btn-primary" value="<?php _e( "Clear" ); ?>" />
		    </div>		    
		</div>
		<?php
	}

	/**
	 * Write the shortcode
	 * 
	 * @param       array   $atts
	 * @since       1.5
	 * @return      HTML
	 */
	function ajax_filter( $atts ) {
		$show_count = isset( $atts[ 'show_count' ] ) && $atts[ 'show_count' ] == 1 ? 1 : 0;
		$posts_per_page = isset( $atts[ 'posts_per_page' ] ) ? ( int ) $atts[ 'posts_per_page' ] : 10;
		$filter_type = isset( $atts[ 'filter_type' ] ) && !empty( $atts[ 'filter_type' ] ) ? $atts[ 'filter_type' ] : 'select';
		$this->create_filter_nav( $filter_type, $show_count );
		?>  
		<div id="ajax-content" class="r-content-wide">
		    <section id="ajax-filtered-section" data-postsperpage="<?php echo $posts_per_page ?>">
			<?php
			$this->create_filtered_section( $posts_per_page );
			?>
		    </section>
		</div>
		<?php
	}

}

new DT_AJAX_Filter();
