<?php

/**
 * Class ID_Affiliate_WP_Extend
 * Extends the AffiliateWP functionalities for IgnitionDeck Commerce.
 */
class ID_Affiliate_WP_Extend extends Affiliate_WP_Base {

	/**
	 * The context for referrals. This refers to the integration that is being used.
	 *
	 * @var string
	 */
	public $context = 'idcommerce';

	/**
	 * Checks if the plugin is active.
	 *
	 * @return bool True if IgnitionDeck Commerce is active, false otherwise.
	 */
	public function plugin_is_active() {
		// Check if IgnitionDeck Commerce plugin is active
		return defined( 'IDC_PATH' );
	}

	/**
	 * Gets things started
	 *
	 * @return  void
	 */
	public function init() {
		add_action( 'memberdeck_payment_success', array( $this, 'idc_add_pending_referral' ), 10, 5 );
		add_action( 'idc_before_order_delete', array( $this, 'idc_revoke_referral' ) );
	}

	/**
	 * Adds a pending referral for an order.
	 *
	 * @param int    $user_id     The user ID.
	 * @param int    $order_id    The order ID.
	 * @param string $payment_key The payment key.
	 * @param array  $fields      The form fields submitted.
	 * @param string $source      The source of the payment.
	 */
	public function idc_add_pending_referral( $user_id, $order_id, $payment_key = '', $fields = null, $source = '' ) {
		$this->log( 'IgnitionDeck Commerce Pending Referral Executed' );

		if ( empty( $order_id ) || empty( $user_id ) || ! $this->was_referred() ) {
			$this->log( 'IgnitionDeck Commerce missing required referral data' );

			if ( empty( $order_id ) ) {
				$this->log( 'IgnitionDeck Commerce: Empty order ID' );
			}

			if ( empty( $user_id ) ) {
				$this->log( 'IgnitionDeck Commerce: Empty user ID' );
			}

			if ( ! $this->was_referred() ) {
				$this->log( 'IgnitionDeck Commerce: Is not a referral. Referral not created.' );
			}

			return;
		}

		$order     = new ID_Member_Order( $order_id );
		$the_order = $order->get_order();

		if ( empty( $the_order ) ) {
			$this->log( 'IgnitionDeck Commerce: Could not retrieve order data.' );
			return; // No order data, cannot apply referral.
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! empty( $user->user_email ) && $this->is_affiliate_email( $user->user_email ) ) {
			$this->log( 'IgnitionDeck Commerce: Referral not created because affiliate\'s own account was used.' );
			return; // Self-referral, no credit applied.
		}

		$level = ID_Member_Level::get_level( $the_order->level_id );

		if ( empty( $level ) ) {
			$this->log( 'IgnitionDeck Commerce: Could not retrieve product data.' );
			return; // Cannot retrieve level data.
		}

		$referral_total = $this->calculate_referral_amount( $the_order->price, $the_order->transaction_id );

		if ( isset( $_COOKIE['affwp_ref'] ) || isset( $_COOKIE['wp-affwp_ref'] ) ) {
			// Choose the cookie that has a value.
			$affiliate_id = isset( $_COOKIE['affwp_ref'] ) ? sanitize_text_field( $_COOKIE['affwp_ref'] ) : sanitize_text_field( $_COOKIE['wp-affwp_ref'] );

			// Insert pending referral.
			$this->insert_pending_referral( $referral_total, $the_order->transaction_id, $level->level_name, array(), array( 'affiliate_id' => $affiliate_id ) );

			// Log the affiliate ID.
			$this->log( 'IgnitionDeck Commerce: Referral Created #' . esc_html( $affiliate_id ) );
		}
	}

	/**
	 * Revokes the referral for a given order.
	 *
	 * @param int $order_id The order ID.
	 */
	public function idc_revoke_referral( $order_id ) {
		$order     = new ID_Member_Order( $order_id );
		$the_order = $order->get_order();

		if ( empty( $the_order ) ) {
			$this->log( 'Could not retrieve order data. Referral for order: #' . absint( $order_id ) . ' could not be revoked.' );
			return; // No order data, cannot revoke referral.
		}

		$this->reject_referral( $the_order->transaction_id );
	}
}