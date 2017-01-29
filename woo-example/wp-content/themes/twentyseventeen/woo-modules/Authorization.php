<?php

/**
 *
 * @copyright Copyright (c) 2015, Redink AS
 * @author Maksim Viter <maksim@pingbull.no>
 */
class Authorization {
	const REG_FIELD_KEY = 'customer_skype';

	public function __construct() {
		$this->_add_login_redirect();
		$this->_add_logout_redirect();
		$this->_add_registration_redirect();
		$this->_add_registration_field();
		$this->_add_account_field();
		$this->_add_admin_user_field();
	}

	protected function _add_logout_redirect() {


		add_action( 'wp_logout', function () {
			wp_safe_redirect( home_url() );
			exit;
		} );
	}

	protected function _add_login_redirect() {
		add_filter( 'login_redirect', function () {
			return home_url();
		} );
	}

	protected function _add_registration_redirect() {
		add_filter( 'woocommerce_registration_redirect', function () {
			return get_post_type_archive_link( FilmPostType::POST_TYPE_KEY );
		} );

	}

	protected function _add_registration_field() {
		add_action( 'woocommerce_register_form', [ $this, 'add_registration_form_filed' ] );
		add_action( 'woocommerce_register_post', [ $this, 'add_registration_form_filed_validation' ], 10, 3 );
		add_action( 'woocommerce_created_customer', [ $this, 'save_registration_form_filed' ] );
		/*add_action( 'register_form', [ $this, 'add_registration_form_filed' ] );
		add_filter( 'registration_errors', [ $this, 'add_registration_form_filed_validation' ], 10, 3 );
		add_action( 'user_register', [ $this, 'save_registration_form_filed' ] );*/
	}

	protected function _add_account_field() {
		add_action( 'woocommerce_edit_account_form', [ $this, 'add_account_field' ] );
		add_action( 'woocommerce_save_account_details', [ $this, 'save_registration_form_filed' ] );

	}

	protected function _add_admin_user_field() {
		add_action( 'show_user_profile', [ $this, 'add_admin_user_field' ] );
		add_action( 'edit_user_profile', [ $this, 'add_admin_user_field' ] );
		add_action( 'personal_options_update', [ $this, 'save_admin_user_field' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_admin_user_field' ] );

	}

	public function save_admin_user_field( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		update_user_meta( $user_id, static::REG_FIELD_KEY, sanitize_text_field( $_POST[ static::REG_FIELD_KEY ] ) );

	}

	public function add_admin_user_field( $user ) {
		$value = get_user_meta( $user->ID, static::REG_FIELD_KEY, true );

		?>
		<h3>Extra profile information</h3>

		<table class="form-table">

			<tr>
				<th><label for="<?= static::REG_FIELD_KEY ?>"><?php _e( 'Skype', 'twentyseventeen' ); ?></label></th>

				<td>
					<input type="text" name="<?= static::REG_FIELD_KEY ?>" id="<?= static::REG_FIELD_KEY ?>"
					       value="<?= esc_attr( $value ); ?>"
					       class="regular-text"/><br/>
					<span class="description">Please enter your Skype username.</span>
				</td>
			</tr>

		</table>
		<?php
	}

	public function add_account_field() {
		$user_id = wp_get_current_user()->ID;
		$value   = get_user_meta( $user_id, static::REG_FIELD_KEY, true );
		?>
		<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			<label for="account_<?= static::REG_FIELD_KEY ?>"><?php _e( 'Skype', 'twentyseventeen' ); ?><span
					class="required">*</span></label>
			<input type="text" class="woocommerce-Input input-text" name="<?= static::REG_FIELD_KEY ?>"
			       id="account_<?= static::REG_FIELD_KEY ?>" value="<?= $value ?>">
		</p>

		<?php
	}

	public function add_registration_form_filed() {
		?>

		<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">

			<label for="reg_<?= static::REG_FIELD_KEY ?>"><?php _e( 'Skype', 'twentyseventeen' ); ?><span
					class="required">*</span></label>

			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
			       name="<?= static::REG_FIELD_KEY ?>" id="reg_<?= static::REG_FIELD_KEY ?>"
			       value="<?php if ( ! empty( $_POST[ static::REG_FIELD_KEY ] ) ) {
				       esc_attr_e( $_POST[ static::REG_FIELD_KEY ] );
			       } ?>"/>

		</p>
		<?php
	}

	public function add_registration_form_filed_validation( $username, $email, $validation_errors ) {
		if ( isset( $_POST[ static::REG_FIELD_KEY ] ) && empty( $_POST[ static::REG_FIELD_KEY ] ) ) {
			$validation_errors->add( static::REG_FIELD_KEY . '_error', __( '<strong>Error</strong>: Skype is required.', 'twentyseventeen' ) );
		}
	}

	public function save_registration_form_filed( $customer_id ) {
		if ( isset( $_POST[ static::REG_FIELD_KEY ] ) ) {
			update_user_meta( $customer_id, static::REG_FIELD_KEY, sanitize_text_field( $_POST[ static::REG_FIELD_KEY ] ) );
		}
	}


}

new Authorization();

