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
    add_action( 'wp_ajax_dt-ajax-search', array( $this, 'create_filtered_section' ) );
    add_action( 'wp_ajax_nopriv_dt-ajax-search', array( $this, 'create_filtered_section' ) );
    add_shortcode( 'datask-search', array( $this, 'ajax_filter' ) );
  }

  /**
   * Build the filtered element on the search results page
   *
   * @since       1.0.0
   * @param       integer $posts_per_page
   */
  public function create_filtered_section( $posts_per_page = 10 ) {
    $filters = array();
    // Post data passed, so update values
    if ( $_GET ) {
	// Secure with a nonce
	check_ajax_referer( 'filternonce' );

	// Grab post data
	$getfilters = isset( $_GET[ 'filters' ] ) ? explode( '&', $_GET[ 'filters' ] ) : null;

	if ( isset( $_GET[ 'postsperpage' ] ) ) {
	  $posts_per_page = ( int ) $_GET[ 'postsperpage' ];
	}
    }

    // Counter
    $c = 0;

    if ( isset( $getfilters ) && $getfilters[ 0 ] !== '' ) { // Check that the array isn't blank
	// This while loop puts the filters in a usable array
	while ( $c < count( $getfilters ) ) {
	  // Explode string to array
	  $string = explode( '=', $getfilters[ $c ] );

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
    $args = array( 'post_type' => 'task', 'posts_per_page' => ( int ) $posts_per_page, 'tax_query' => array(), 'orderby' => 'date', 'order' => 'DESC', 'post_status' => 'publish' );

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
    $ajax_post_filtered = new WP_Query();

    // Parse args
    $ajax_post_filtered->query( $args );
    if ( $ajax_post_filtered->have_posts() ) {
	while ( $ajax_post_filtered->have_posts() ) {
	  $ajax_post_filtered->the_post();
	  ?>
	  <article class="ajax-loaded">
	      <h4><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
	      <p><?php the_excerpt(); ?></p>
	      <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php _e( 'Read More', DT_TEXTDOMAIN ); ?></a>
	  </article>
	  <?php
	  $i++;
	}
	$this->pagination( $ajax_post_filtered->found_posts, $posts_per_page );
    } else {
	echo "<p class='no-results'>";
	_e( 'No Results found :(', DT_TEXTDOMAIN );
	echo '</p>';
    }

    // Reset global post object
    wp_reset_postdata();

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
   * @param string  $filter_type List or select.
   * @param integer $show_count Show the post.
   *
   */
  public function create_filter_nav( $filter_type = 'select', $show_count = 0, $taxonomies = null ) {
    $searcher = isset( $_GET[ 's' ] ) ? wp_unslash( $_GET[ 's' ] ) : '';
    ?>
    <div id="ajax-filters" class="ajax-filters">
        <div class="form-group">
    	  <input type="text" value="<?php echo $searcher; ?>" name="searcher" id="searcher" placeholder="<?php _e( 'Search' ); ?>" class="filter-selected form-control" />
        </div>
        <div class="form-inline">
    	  <div class="form-group" style="width: 100%;">
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
			    case 'select';
				echo '<select class="filter-' . $taxonomy . ' ajax-select form-control">';
				echo '<option value="" class="default">' . $the_tax_name . '</option>';
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
			    case 'list';
			    default;
				echo '<div class="filter-' . $taxonomy . ' filter-selected">';
				echo '<h4>' . $the_tax_name . '</h4>';
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
			_e( 'There are no tasks!', DT_TEXTDOMAIN );
		    } else {
			?>
			<div class="search-form-button" style="float: right;">
			    <input type="submit" id="go" class="go filter btn btn-info" value="<?php _e( 'Search' ); ?>" />
			    <input type="reset" id="reset" class="reset btn btn-info" value="<?php _e( 'Clear' ); ?>" />
			</div>
			<?php
		    }
		    ?>
    	  </div>
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
    $show_count = isset( $atts[ 'show_count' ] ) && $atts[ 'show_count' ] === 1 ? 1 : 0;
    $posts_per_page = isset( $atts[ 'posts_per_page' ] ) ? ( int ) $atts[ 'posts_per_page' ] : 10;
    $filter_type = isset( $atts[ 'filter_type' ] ) && !empty( $atts[ 'filter_type' ] ) ? $atts[ 'filter_type' ] : 'select';
    if ( !isset( $atts[ 'taxonomies' ] ) ) {
	$taxonomies = array( 'task-team', 'task-area', 'task-difficulty', 'task-minute' );
    } else {
	if ( !strpos( ',', $atts[ 'taxonomies' ] ) ) {
	  $taxonomies = array( $atts[ 'taxonomies' ] );
	}
	$taxonomies = explode( ',', str_replace( ' ', '', $atts[ 'taxonomies' ] ) );
    }
    ob_start();
    $this->create_filter_nav( $filter_type, $show_count, $taxonomies );
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
