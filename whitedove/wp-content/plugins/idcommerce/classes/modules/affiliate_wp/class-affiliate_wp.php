<?php

/**
 * Class ID_Affiliate_WP
 * Extends AffiliateWP functionalities for IgnitionDeck Commerce.
 */
class ID_Affiliate_WP {

	/**
	 * ID_Affiliate_WP constructor.
	 */
	public function __construct() {
		add_filter( 'affwp_extended_integrations', array( $this, 'register_integration' ) );
	}

	/**
	 * Registers the integration with AffiliateWP.
	 *
	 * @param array $integrations Existing integrations.
	 * @return array Modified integrations.
	 */
	public function register_integration( $integrations ) {
		// Register the integration.
		$integrations['idcommerce'] = array(
			'name'     => __( 'IgnitionDeck Commerce', 'memberdeck' ),
			'class'    => '\ID_Affiliate_WP_Extend',
			'file'     => plugin_dir_path( __FILE__ ) . 'class-affiliate_wp_extend.php', // Correct file path.
			'enabled'  => true,
			'supports' => array( 'sales_reporting' ), // Enable sales reporting.
		);

		return $integrations;
	}
}

new ID_Affiliate_WP();
