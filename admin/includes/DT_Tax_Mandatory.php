<?php
/**
 * @package   DaTask
 * @author    Mte90 <mte90net@gmail.com>
 * @license   GPL-2.0+
 * @since     1.1.0
 * @link      http://mte90.net
 * @copyright 2017 GPL
 */

/**
 * This class generate the list for the statistics
 */
class DT_Tax_Mandatory {

	/**
	 * Initialize the class with all the hooks
	 */
	public function __construct() {
		add_action( 'manage_users_columns', array( $this, 'add_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'btn_tax_assign' ), 10, 3 );
		add_action( 'admin_head', array( $this, 'append_resource_modal' ) );
		add_action( 'wp_ajax_find_datask_tax', array( $this, 'wp_ajax_find_tax' ) );
		add_action( 'wp_ajax_add_datask_tax', array( $this, 'wp_ajax_add_tax' ) );
		add_action( 'admin_footer', array( $this, 'append_modal' ) );
	}

	/**
	 * Add column in the users list
	 *
	 * @param array $column_headers List of column.
	 * @return array
	 */
	public function add_column( $column_headers ) {
		$column_headers[ 'datask_tax' ] = __( 'DaTask Actions', DT_TEXTDOMAIN );
		return $column_headers;
	}

	/**
	 * Assign tax to users
	 *
	 * @param string $value The button to show.
	 * @param string $column_name The id of the column.
	 * @param string $user_id The user ID.
	 */
	public function btn_tax_assign( $value, $column_name, $user_id ) {
		if ( 'datask_tax' === $column_name ) {
			$value = '<a href="#" class="button modal-datask-assign" data-user-id="' . $user_id . '">' . __( 'Assign Task Category', DT_TEXTDOMAIN ) . '</a>';
		}
		return $value;
	}

	/**
	 * Append the modal
	 */
	public function append_resource_modal() {
		$screen = get_current_screen();
		if ( $screen->base === 'users' ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-backbone' );
			wp_enqueue_script( DT_TEXTDOMAIN . '-modal-user', plugins_url( '/assets/js/modal-assign.js', dirname( __FILE__ ) ), array( 'jquery', 'wp-backbone' ), DT_VERSION );
		}
	}

	/**
	 * 
	 * Based on find_posts
	 * 
	 * @param type $found_action
	 */
	public function append_modal( $found_action = '' ) {
		?>
		<style>
			#find-datask-tax-close {
				width: 36px;
				height: 36px;
				position: absolute;
				top: 0px;
				right: 0px;
				cursor: pointer;
				text-align: center;
				color: #666;
			}
			#find-datask-tax-close::before {
				font: 400 20px/36px dashicons;
				vertical-align: top;
				content: "";
			}
			#find-datask-tax-close:hover {
				color: #00A0D2;
			}
		</style>
		<div id="find-datask-tax" class="find-box" style="display: none;">
			<div id="find-datask-tax-head" class="find-box-head">
				<?php _e( 'Task' ); ?>
				<div id="find-datask-tax-close"></div>
			</div>
			<div class="find-box-inside">
				<div class="find-box-search">
					<?php if ( $found_action ) { ?>
						<input type="hidden" name="found_action" value="<?php echo esc_attr( $found_action ); ?>" />
					<?php } ?>
					<input type="hidden" name="affected" id="affected" value="" />
					<?php wp_nonce_field( '#find-datask-tax', '_ajax_nonce', false ); ?>
					<label class="screen-reader-text" for="#find-datask-tax-input"><?php _e( 'Search' ); ?></label>
					<input type="text" id="find-datask-tax-input" name="ps" value="" autocomplete="off" />
					<span class="spinner"></span>
					<input type="button" id="find-datask-tax-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
					<div class="clear"></div>
				</div>
				<div id="find-datask-tax-response"></div>
			</div>
			<div class="find-box-buttons">
				<?php submit_button( __( 'Select' ), 'button-primary alignright', 'find-datask-tax-submit', false ); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Ajax handler for querying posts for the Find Users modal.
	 *
	 * @see window.findPosts
	 *
	 * @since 3.1.0
	 */
	public function wp_ajax_find_tax() {
		$plugin = DaTask::get_instance();
		$taxs = get_terms( 'task-team', array(
			'orderby' => 'count',
			'hide_empty' => 0,
			'name__like' => wp_unslash( $_POST[ 'ps' ] )
				) );
		$user_taxs = explode( ', ', get_user_meta( wp_unslash( $_POST[ 'user' ] ), $plugin->get_fields( 'category_to_do' ), true ) );

		if ( !$taxs ) {
			wp_send_json_error( __( 'No items found.' ) );
		}

		$html = '<table class="widefat"><thead><tr><th class="found-radio"><br /></th><th>' . __( 'Name' ) . '</th></tr></thead><tbody>';
		$alt = '';
		foreach ( $taxs as $tax ) {
			$checked = '';
			foreach ( $user_taxs as $key => $user_tax ) {
				if ( $user_tax === $tax->slug ) {
					$checked = ' checked="checked"';
					unset( $user_taxs[ $key ] );
				}
			}
			$alt = ( 'alternate' == $alt ) ? '' : 'alternate';

			$html .= '<tr class="' . trim( 'found-tax-task ' . $alt ) . '"><td class="found-checkbox"><input type="checkbox" id="found-' . $tax->slug . '" name="found_tax_task" value="' . esc_attr( $tax->slug ) . '"' . $checked . '></td>';
			$html .= '<td><label for="found-' . $tax->slug . '">' . esc_html( $tax->name ) . '</label></td></tr>' . "\n\n";
		}

		$html .= '</tbody></table>';

		wp_send_json_success( $html );
	}

	/**
	 * Add taxonomy to the user
	 */
	public function wp_ajax_add_tax() {
		$plugin = DaTask::get_instance();
		$user = wp_unslash( $_POST[ 'user' ] );
		$taxs = wp_unslash( $_POST[ 'taxs' ] );
		update_user_meta( $user, $plugin->get_fields( 'category_to_do' ), $taxs );

		wp_send_json_success();
	}

}

new DT_Tax_Mandatory();
