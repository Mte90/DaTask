<?php

/**
 *
 * the core class of the plugin
 * @author James Irving-Swift
 *
 *
 */
class Q_AJAX_Filter_Core {

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
					$args[ 's' ] = array();
					array_push( $args[ 's' ], implode( ',', $ids ) );
				}
			}
			$args[ 'tax_query' ][ 'relation' ] = 'AND';
		}

		// counter 
		$i = 0;

		// new WP_Query 
		$q_ajax_filter_wp_query = new WP_Query();

		// parse args 
		$q_ajax_filter_wp_query->query( $args );

//chiarire perchè while non và
		if ( $q_ajax_filter_wp_query->have_posts() ) {
			while ( $q_ajax_filter_wp_query->have_posts() ) {
				$q_ajax_filter_wp_query->the_post();
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
		$this->pagination( $q_ajax_filter_wp_query->found_posts, $posts_per_page );

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
		<nav class="pagination">
		    <?php
		    if ( $_GET ) {
			    ?>
			    <div class="prevPage"><a class="paginationNav" rel="prev" href="#">&lsaquo; <?php _e( "Back" ); ?></a></div>
			    <?php
			    if ( isset( $_GET[ 'paged' ] ) && $_GET[ 'paged' ] > 1 ) {
				    $page_number = $_GET[ 'paged' ];
			    } else {
				    $page_number = 1;
			    }
			    if ( isset( $_GET[ 'postsperpage' ] ) ) {
				    $posts_per_page = $_GET[ 'postsperpage' ];
			    } else {
				    $posts_per_page = 10;
			    }
		    } else {
			    $page_number = 1;
		    }
		    ?>
		    <div class="af-pages">
			<?php
			//$offset = ( $page_number * $posts_per_page ) - $posts_per_page; // what row to start
			echo $total_posts . '/' . $posts_per_page;
			$pages = ceil( $total_posts / $posts_per_page ); // add the pages
			// check things out 
			if ( $page_number < $posts_per_page ) {
				$sp = 1;
			} elseif ( $page_number >= ($pages - floor( $posts_per_page / 2 )) ) {
				$sp = $pages - $posts_per_page + 1;
			} elseif ( $page_number >= $posts_per_page ) {
				$sp = $page_number - floor( $posts_per_page / 2 );
			}

			// If the current page >= $posts_per_page then show link to 1st page
			if ( $page_number >= $posts_per_page ) {
				?>
				<a href='#' class='pagelink-1 pagelink' rel="1">1</a>..
				<?php
			}

			//Loop though max number of pages shown and show links either side equal to $posts_per_page / 2 -->
			for ( $i = $sp; $i <= ($sp + $posts_per_page - 1); $i++ ) {

				if ( $i > $pages ) {
					continue;
				}

				// current 
				if ( $page_number == $i ) {
					?>
					<a href="#" class="pagelink-<?php echo $i; ?> pagelink current" rel="<?php echo $i; ?>"><?php echo $i; ?></a>

					<?php
					// normal 
				} else {
					?>

					<a href='#' class="pagelink-<?php echo $i; ?> pagelink" rel="<?php echo $i; ?>"><?php echo $i; ?></a>

					<?php
				}
			}

			// If the current page is less than the last page minus $posts_per_page pages divided by 2 
			if ( $page_number < ( $pages - floor( $posts_per_page / 2 ) ) ) {
				?>
				..<a href='#' class="pagelink-<?php echo $pages; ?> pagelink" rel="<?php echo $pages; ?>"><?php echo $pages; ?></a>
				<?php
			}
			?>
		    </div>
		    <?php
		    // check if we need to print pagination 
		    if ( ( $posts_per_page * $page_number ) < $total_posts && $posts_per_page < $total_posts ) {
			    ?>
			    <div class="nextPage"><a class="paginationNav" rel="next" href="#"><?php _e( "Next" ); ?> &rsaquo;</a></div>
			    <?php
		    } // pagination check  
		    ?>
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

							echo "<select class=\"filter-$taxonomy ajax-select form-control\">";
							echo "<option value=\"\" class=\"default\">-- " . $the_tax_name . " --</option>";

							#wp_die(pr($terms));
							foreach ( $terms as $term ) {

								echo "<option value=\"$taxonomy={$term->term_id}\" data-tax=\"$taxonomy={$term->term_id}\" class=\"filter-selected\">";

								echo "{$term->name}";

								if ( $show_count == 1 ) {
									echo " ({$term->count})";
								}

								echo "</option>";
							}

							echo "</select>";

							break;

						// build list items for changing values 
						case "list";

						default;

							foreach ( $terms as $term ) {

								echo "<div class=\"ajaxFilterItem form-control filter-selected\">";

								echo "<input type=\"checkbox\" data-tax=\"$taxonomy={$term->term_id}\" /><label>{$term->name}</a></label>";
								if ( $show_count == 1 ) {
									echo " ({$term->count})";
								}
								echo "</div>";
							}

							break;
					} // switch 
				} // loop  
			} // taxs set 
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
	 * Caste Array to Object
	 * 
	 * @param type $array
	 * @return \stdClass|boolean
	 */
	public function array_to_object( $array ) {

		if ( !is_array( $array ) ) {
			return $array;
		}

		$object = new stdClass();
		if ( is_array( $array ) && count( $array ) > 0 ) {
			foreach ( $array as $name => $value ) {
				$name = strtolower( trim( $name ) );
				if ( !empty( $name ) ) {
					$object->$name = $this->array_to_object( $value );
				}
			}
			return $object;
		} else {
			return false;
		}
	}

}
