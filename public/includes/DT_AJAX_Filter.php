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
	 * @since       1.0.0
	 * @param       integer $posts_per_page
	 * 
	 */
	public function create_filtered_section( $posts_per_page = 10 ) {
		$plugin = DaTask::get_instance();
		$filters = array();
		// Post data passed, so update values 
		if ( $_GET ) {
			// Secure with a nonce 
			check_ajax_referer( 'filternonce' );

			// Grab post data 
			$_GET_filters = isset( $_GET[ 'filters' ] ) ? explode( '&', $_GET[ 'filters' ] ) : null;

			if ( isset( $_GET[ 'postsperpage' ] ) ) {
				$posts_per_page = $_GET[ 'postsperpage' ];
			}
		}

		// Counter 
		$c = 0;

		if ( isset( $_GET_filters ) && $_GET_filters[ 0 ] != "" ) { // Check that the array isn't blank
			// This while loop puts the filters in a usable array 
			while ( $c < count( $_GET_filters ) ) {
				// Explode string to array 
				$string = explode( '=', $_GET_filters[ $c ] );

				// Check if each item is an array - or caste 
				if ( !isset( $filters[ $string[ 0 ] ] ) || !is_array( $filters[ $string[ 0 ] ] ) ) {
					$filters[ $string[ 0 ] ] = array();
				}
				// Add items to array 
				array_push( $filters[ $string[ 0 ] ], $string[ 1 ] );

				// Clean up empty items 
				array_filter( $filters );

				// Iterate 
				$c++;
			}
		}

		// Build args list 
		$args = array(
		    "post_type" => array( 'task' ), "posts_per_page" => ( int ) $posts_per_page, "tax_query" => array(), "orderby" => 'date', "order" => 'DESC', "post_status" => "publish"
		);

		// Check if paging value passed, if so add to the query 
		if ( isset( $_GET[ 'paged' ] ) ) {
			$args[ 'paged' ] = $_GET[ 'paged' ];
		} else {
			$args[ 'paged' ] = 1;
		}

		if ( isset( $filters ) && !empty( $filters ) ) {
			// Add all the filters to tax_query 
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

		// Counter 
		$i = 0;

		// New WP_Query 
		$dt_ajax_filter_wp_query = new WP_Query();

		// Parse args 
		$dt_ajax_filter_wp_query->query( $args );

		if ( $dt_ajax_filter_wp_query->have_posts() ) {
			while ( $dt_ajax_filter_wp_query->have_posts() ) {
				$dt_ajax_filter_wp_query->the_post();
				?>
				<article class="ajax-loaded">
				    <h3><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
				    <p><?php the_excerpt(); ?></p>
				    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php _e( "Read More", $plugin->get_plugin_slug() ); ?></a>
				</article>
				<?php
				$i++;
			}
			$this->pagination( $dt_ajax_filter_wp_query->found_posts, $posts_per_page );
		} else {
			echo "<p class='no-results'>";
			_e( "No Results found :(", $plugin->get_plugin_slug() );
			echo "</p>";
		}

		// Reset global post object 
		wp_reset_query();

		// Called from ajax - so needs to die 
		if ( $_GET ) {
			die();
		}
	}

	/**
	 * Buid pagination 
	 * 
	 * @since 1.0.0
	 * @param integer $total_posts Post totali.
	 * @param integer $posts_per_page Post per pagina.
	 * 
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
			$pages = ( int ) ceil( $total_posts / $posts_per_page ); // Add the pages
			// Print position 1
			if ( $page_number >= 1 ) {
				if ( 1 === $page_number ) {
					$active = ' active';
				} else {
					$active = '';
				}
				if ( $page_number !== 2 ) {
					?>
					<li class="page-item<?php echo $active ?>"><a href='#' class='page-link pagelink-1 pagelink' rel="1">1</a></li>
					<?php
				}
				if ( $page_number > 3 ) {
					?>
					<li><a>...</a></li>
					<?php
				}
			}
			// Print 3 page
			if ( $page_number - 1 !== 0 ) {
				?>
				<li class="minus page-item"><a href="#" class="page-link pagelink-<?php echo ($page_number - 1); ?> pagelink" rel="<?php echo ($page_number - 1); ?>"><?php echo ($page_number - 1); ?></a></li>
				<?php
			}
			if ( $page_number !== 1 ) {
				?>
				<li class="active page-item"><a href="#" class="page-link pagelink-<?php echo $page_number; ?> pagelink" rel="<?php echo $page_number; ?>"><?php echo $page_number; ?></a></li>
				<?php
			}
			if ( $page_number !== $pages ) {
				?>
				<li class="plus page-item"><a href="#" class="page-link pagelink-<?php echo ($page_number + 1); ?> pagelink" rel="<?php echo ($page_number + 1); ?>"><?php echo ($page_number + 1); ?></a></li>
				<?php
			}

			// If the current page is less than the last page minus $posts_per_page pages divided by 2 
			if ( $page_number < ( $pages - floor( $posts_per_page / 2 ) ) ) {
				?>
				<li class="page-item"><a href='#' class="page-link">...</a></li>
				<li class="page-item"><a href='#' class="page-link pagelink-<?php echo $pages; ?> pagelink" rel="<?php echo $pages; ?>"><?php echo $pages; ?></a></li>
				<?php
			}
			?>
		    </ul>
		</nav>
		<?php
	}

	/**
	 * Build list of terms to filter by
	 * 
	 * @since 1.0.0
	 * @param string $filter_type List or select.
	 * @param integer $show_count Show the post.
	 * 
	 */
	public function create_filter_nav( $filter_type = 'select', $show_count = 0 ) {
		$taxonomies = array( 'task-team', 'task-area', 'task-difficulty', 'task-minute' );
		$plugin = DaTask::get_instance();
		$searcher = isset( $_GET[ "s" ] ) ? $_GET[ "s" ] : "";
		?>
		<div id="ajax-filters" class="ajax-filters">
		    <div class="form-group">
			<input type="text" value="<?php echo $searcher; ?>" name="searcher" id="searcher" placeholder="<?php _e( "Search" ); ?>" class="filter-selected form-control" />
		    </div>
		    <div class="form-group">
			<?php
			if ( $taxonomies && isset( $taxonomies[ 0 ] ) && $taxonomies[ 0 ] > '' ) {
				$emptytask = 0;
				foreach ( $taxonomies as $taxonomy ) {

					$terms = get_terms( $taxonomy, array(
					    'orderby' => 'name',
					    'order' => 'ASC',
					    'hide_empty' => 1
						)
					);

					if ( !isset( $terms ) || empty( $terms ) || is_wp_error( $terms ) ) {
						$emptytask++;
						continue;
					}

					reset( $terms );
					$first_key = key( $terms );

					// Nothing cooking in this taxonomy 
					if ( !$terms[ $first_key ] ) {
						continue;
					}

					// Get tax name 
					$the_tax = get_taxonomy( $terms[ $first_key ]->taxonomy );
					$the_tax_name = $the_tax->labels->singular_name;

					// Select or list items ? 
					switch ( $filter_type ) {
						// Build selects for changing values 
						case "select";
							echo '<select class="filter-' . $taxonomy . ' ajax-select form-control">';
							echo "<option value=\"\" class=\"default\">" . $the_tax_name . "</option>";
							foreach ( $terms as $term ) {
								echo '<option class="filter-selected" value="' . $term->term_id . '" data-tax="' . $term->term_id . '" data-slug="' . $taxonomy . '"> - ';
								echo $term->name;
								if ( $show_count == 1 ) {
									echo ' (' . $term->count . ')';
								}
								echo '</option>';
							}
							echo '</select>';
							break;

						// Build list items for changing values 
						case "list";
						default;
							echo '<div class="filter-' . $taxonomy . ' filter-selected">';
							echo '<h3>' . $the_tax_name . '</h3>';
							echo '<ul class="list-group">';
							foreach ( $terms as $term ) {
								echo '<li class="list-group-item"><input class="ajax-list" type="checkbox" data-tax="' . $term->term_id . '" data-slug="' . $taxonomy . '" /><label>' . $term->name . '</a></label>';
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
			if ( $emptytask === 4 ) {
				_e( 'There are no tasks!', $plugin->get_plugin_slug() );
			} else {
				?>
				<div class="search-form-button">
				    <input type="submit" id="go" class="go filter btn btn-info" value="<?php _e( "Search" ); ?>" />
				    <input type="reset" id="reset" class="reset btn btn-info" value="<?php _e( "Clear" ); ?>" />
				</div>
				<?php
			}
			?>
		    </div> 	    
		</div>
		<?php
	}

	/**
	 * Write the shortcode
	 * 
	 * @since       1.0.0
	 * @param array $atts The values from the shortcode.
	 * @return string THe HTML code
	 */
	function ajax_filter( $atts ) {
		$show_count = isset( $atts[ 'show_count' ] ) && $atts[ 'show_count' ] == 1 ? 1 : 0;
		$posts_per_page = isset( $atts[ 'posts_per_page' ] ) ? ( int ) $atts[ 'posts_per_page' ] : 10;
		$filter_type = isset( $atts[ 'filter_type' ] ) && !empty( $atts[ 'filter_type' ] ) ? $atts[ 'filter_type' ] : 'select';
		ob_start();
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
		$output = ob_get_contents();
		ob_clean();
		return $output;
	}

}

new DT_AJAX_Filter();
