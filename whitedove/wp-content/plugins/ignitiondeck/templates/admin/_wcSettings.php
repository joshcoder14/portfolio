<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td><strong><?php esc_html_e('WooCommerce Checkout Page', 'idf'); ?></strong>
</tr>
<tr>
	<td>
		<select name="idf_wc_checkout_url">
			<option value="get_cart_url" <?php echo ($idf_wc_checkout_url == 'get_cart_url' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Cart URL', 'idf'); ?></option>
			<option value="get_checkout_url" <?php echo ($idf_wc_checkout_url == 'get_checkout_url' ? 'selected="selected"' : ''); ?>><?php esc_html_e('Checkout URL', 'idf'); ?></option>
		</select>
	</td>
</tr>