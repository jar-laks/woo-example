<?php

/**
 *
 * @copyright Copyright (c) 2015, Redink AS
 * @author Maksim Viter <maksim@pingbull.no>
 */
class WooFilm {
	const ARG_KEY = 'process-film-payment';

	public function __construct() {
		$this->_process_to_payment();
	}

	protected function _process_to_payment() {
		add_action( 'template_redirect', function () {
			if ( is_singular( FilmPostType::POST_TYPE_KEY ) && isset( $_GET[ static::ARG_KEY ] ) ) {
				$product_id = absint( $_GET[ static::ARG_KEY ] );
				$product    = wc_get_product( $product_id );
				if ( $product instanceof WC_Product ) {
					global $woocommerce;
					$woocommerce->cart->empty_cart();
					$order = wc_create_order( [ 'customer_id' => wp_get_current_user()->ID ] );
					$order->add_product( $product );
					$order->calculate_totals();
					update_post_meta( $order->id, '_payment_method', 'paypal' );
					update_post_meta( $order->id, '_payment_method_title', 'PayPal' );
					WC()->session->order_awaiting_payment = $order->id;
					$available_gateways                   = WC()->payment_gateways->get_available_payment_gateways();
					$result                               = $available_gateways['paypal']->process_payment( $order->id );
					if ( $result['result'] == 'success' ) {
						$result = apply_filters( 'woocommerce_payment_successful_result', $result, $order->id );
						$order->update_status( 'completed' );
						wp_redirect( $result['redirect'] );
						exit;
					}
				}

			}

		} );
	}

}
new WooFilm();


