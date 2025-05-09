<div class="wrap memberdeck">
	<div class="icon32" id="icon-md"></div><h2 class="title"><?php _e('Orders', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div style="clear: both;"><br/></div>
	<form id="order-filter" action="" method="get">
		<p class="search-box">
			<label class="screen-reader-text" for="post-search-input"><?php _e('Search Transaction ID\'s', 'memberdeck'); ?></label>
			<input type="search" id="post-search-input" name="s" value="<?php echo (isset($_GET['s']) ? $_GET['s'] : ''); ?>"/>
			<input type="hidden" name="page" value="idc-orders"/>
			<input type="submit" name="" id="search-submit" class="button" value="<?php _e('Search Transaction ID\'s', 'memberdeck'); ?>">
		</p>
		<div id="order-list">
			<div class="tablenav top">
				<div class="tablenav-pages"><span class="displaying-num"><?php echo $pages; ?> <?php _e('items', 'memberdeck'); ?></span>
					<?php if (isset($page) && $page > 1) { ?>
					<span class="pagination-links"><a class="first-page" title="<?php _e('Go to the first page', 'memberdeck'); ?>" href="admin.php?<?php echo (isset($query_first) ? $query_first : ''); ?>">«</a>
					<a class="prev-page" title="<?php _e('Go to the previous page', 'memberdeck'); ?>" href="admin.php?<?php echo (isset($query_prev) ? $query_prev : ''); ?>">‹</a>
					<?php } ?>
					<span class="paging-input"><input class="current-page" title="<?php _e('Current page', 'memberdeck'); ?>" type="text" name="p" value="<?php echo $page; ?>" size="1"> of <span class="total-pages"><?php echo $pages; ?></span></span>
					<?php if (isset($page) && $page < $pages) { ?>
					<a class="next-page" title="Go to the next page" href="admin.php?<?php echo (isset($query_next) ? $query_next : ''); ?>">›</a>
					<a class="last-page" title="Go to the last page" href="admin.php?<?php echo (isset($query_last) ? $query_last : ''); ?>">»</a></span>
					<?php } ?>
				</div>
			</div>
			<table id="memberdeck-users" class="wp-list-table widefat fixed pages" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column">
							&nbsp;<!--<input id="cb-select-all-1" type="checkbox"/>-->
						</th>
						<th scope="col" id="order_id" class="manage-column sortable desc small">
							<a href="#">
								<span><?php _e('Order ID', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="order_date" class="manage-column sortable desc medium">
							<a href="#">
								<span><?php _e('Order Date', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="user_id" class="manage-column sortable desc small">
							<a href="#">
								<span><?php _e('User ID', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="customer" class="manage-column sortable desc medium">
							<a href="#">
								<span><?php _e('User Email', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="product" class="manage-column sortable desc medium">
							<a href="#">
								<span><?php _e('Product', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="price" class="manage-column sortable desc small">
							<a href="#">
								<span><?php _e('Price', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="txn_id" class="manage-column sortable desc medium">
							<a href="#">
								<span><?php _e('Transaction ID', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" id="order_status" class="manage-column sortable desc small">
							<a href="#">
								<span><?php _e('Order Status', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column column-cb check-column">
							&nbsp;<!--<input id="cb-select-all-1" type="checkbox"/>-->
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Order ID', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Order Date', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('User ID', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('User Email', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Product', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Price', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Transaction ID', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>
						<th scope="col" class="manage-column sortable desc">
							<a href="#">
								<span><?php _e('Order Status', 'memberdeck'); ?></span>
								<!--<span class="sorting-indicator"></span>-->
							</a>
						</th>						 
					</tr>
				</tfoot>
				<tbody id="the-list">
					<?php
						// start the loop
						$i = 0;
						foreach ($orders as $order) {
							if ($i % 2 == 0) {
								$alt = 'alternate';
							}
							else {
								$alt = '';
							}
							if (!empty($order->user_id) && $order->user_id > 0) {
								$user = get_user_by('id', $order->user_id);
								$email = (isset($user->user_email) ? $user->user_email : '');
							}
							else {
								$guest_meta = ID_Member_Order::get_order_meta($order->id, 'guest_data', true);
								$email = (!empty($guest_meta['email']) ? $guest_meta['email'] : '');
							}
							if (!empty($order->level_id)) {
								$level = ID_Member_Level::get_level($order->level_id);
								if (!empty($level)) {
									$level_display = $level->level_name;
								}
								else {
									$level_display = __('N/A', 'memberdeck');
								}
							}
							$gateway_info = ID_Member_Order::get_order_meta($order->id, 'gateway_info', true);
							if ($order->transaction_id == 'pre') {
								$preorder_meta = ID_Member_Order::get_order_meta($order->id, 'preauth_error', true);
							}
							if (!empty($gateway_info) && $gateway_info['gateway'] == 'credit') {
								$price = (isset($level) ? $level->credit_value : '0');
							} else {
								$price = $order->price;
							}
							
							$date = new DateTime($order->order_date, $tz);
							#redundant because orders are stored in default tz
							$date->setTimezone($tz);

							echo '<tr data-id="'.$order->id.'" data-currency-code="'.(!empty($gateway_info['currency_code']) ? idc_currency_to_symbol($gateway_info['currency_code']) : '').'" id="user-'.$order->id.'" class="order-'.$order->id.' hentry '.$alt.'">';
								echo '<th scope="row" class="check-column">&nbsp;</th>';
								echo '<td id="order_id-'.$order->id.'" class="order_item order_id column-cb" data-column="id" data-type="int">'.'100'.$order->id.'
										<div class="row-actions">
											<span class="view" style="display: none;"><a href="?page=idc-view-order&action=idc_view_order&order_id='.$order->id.'">'.__('View', 'memberdeck').'</a> |</span>
											<span class="edit" style="display: none;"><a href="?page=idc-edit-order&action=idc_edit_order&order_id='.$order->id.'">'.__('Edit', 'memberdeck').'</a> |</span>
											<span class="trash"><a id="idc-delete-order" href="?page=idc-orders&action=idc_delete_order&order_id='.$order->id.'">'.__('Delete', 'memberdeck').'</a></span>
										</div>
									</td>';
								echo '<td id="order_date-'.$order->id.'" class="order_item order_edit order_date" data-column="order_date" data-type="date"><a title="'.__('Click to edit', 'memberdeck').'" href="#">'.$date->format('Y-m-d H:i:s').'</a></td>';
								echo '<td id="user_id-'.$order->id.'" class="order_item order_edit customer" data-type="int">'.(!empty($order->user_id) ? $order->user_id : '').'</td>';
								echo '<td id="user_email-'.$order->id.'" class="order_item customer" data-type="text"><a href="'.md_get_admin_order_list_url().'&user_id='.$order->user_id.'">'.$email.'</a></td>';
								echo '<td id="product-'.$order->id.'" class="order_item product" data-type="text">'.apply_filters('idc_level_name', $level_display).'</td>';
								echo '<td id="price-'.$order->id.'" class="order_item order_edit price" data-column="price" data-type="float"><a title="'.__('Click to edit', 'memberdeck').'" href="#">'.apply_filters('idc_order_price', $price, $order->id, $gateway_info).'</a></td>';
								// if a preorder fails, we link the transaction ID to a detail popup
								echo '<td id="txn_id-'.$order->id.'" class="order_item txn_id" data-type="text">'.($order->transaction_id == 'pre' && !empty($preorder_meta) ? '<a class="pre-order-status-link openLBGlobal" href=".idc-preorder-status-box" data-order-id="'.$order->id.'" data-preorder-status-date="'.(isset($preorder_meta) ? date(idf_date_format(), $preorder_meta['date']) : '').'" data-preorder-status-message="'.(isset($preorder_meta) ? ($preorder_meta['error']) : '').'">' : '').$order->transaction_id.($order->transaction_id == 'pre' ? '</a>' : '').'</td>';
								echo '<td id="order_status-'.$order->id.'" class="order_item order_status">'.$order->status.'</td>';
							echo '</tr>';
							$i++;
						}
					?>
				</tbody>
			</table>
			<div class="tablenav bottom">
			</div>
		</div>
	</form>
	<div id="user-profile" class="postbox-container" style="width:95%; margin-right: 5%; display: none">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('User Profile', 'memberdeck'); ?></span></h3>
					<div class="inside">
						<div class="memberdeck">
							<form id="user-profile">
								<div class="form-row quarter">
									<label for="nicename"><?php _e('Display Name', 'memberdeck'); ?></label>
									<input type="text" size="20" class="display_name" name="display_name" value=""/>
								</div>
								<div class="form-row quarter">
									<label for="first-name"><?php _e('First Name', 'memberdeck'); ?></label>
									<input type="text" size="20" class="first_name" name="first_name" value=""/>
								</div>
								<div class="form-row half">
									<label for="last-name"><?php _e('Last Name', 'memberdeck'); ?></label>
									<input type="text" size="20" class="last_name" name="last_name" value=""/>
								</div>
								<div class="form-row half">
									<label for="user_email"><?php _e('Email Address', 'memberdeck'); ?></label>
									<input type="email" size="20" class="user_email" name="user_email" value=""/>
								</div>
								<div class="form-row half">
									<label for="url"><?php _e('Website URL', 'memberdeck'); ?></label>
									<input type="url" size="20" class="user_url" name="user_url" value=""/>
								</div>
								<div class="form-row half">
									<label for="description"><?php _e('Bio', 'memberdeck'); ?></label>
									<textarea row="10" class="description" name="description"></textarea>
								</div>
								<div class="form-row half">
									<label for="twitter"><?php _e('Twitter URL', 'memberdeck'); ?></label>
									<input type="twitter" size="20" class="twitter" name="twitter" value=""/>
									<label for="facebook"><?php _e('Facebook URL', 'memberdeck'); ?></label>
									<input type="facebook" size="20" class="facebook" name="facebook" value=""/>
									<label for="google"><?php _e('Google URL', 'memberdeck'); ?></label>
									<input type="google" size="20" class="google" name="google" value=""/>
								</div>
								<div class="form-row">
									<label for="address"><?php _e('Address Line 1', 'memberdeck'); ?></label>
									<input type="text" size="20" class="address" name="address" value=""/>
								</div>
								<div class="form-row">
									<label for="address_two"><?php _e('Address Line 2', 'memberdeck'); ?></label>
									<input type="text" size="20" class="address_two" name="address_two" value=""/>
								</div>
								<div class="form-row half">
									<label for="city"><?php _e('City', 'memberdeck'); ?></label>
									<input type="text" size="20" class="city" name="city" value=""/>
								</div>
								<div class="form-row half">
									<label for="state"><?php _e('State', 'memberdeck'); ?></label>
									<input type="text" size="20" class="state" name="state" value=""/>
								</div>
								<div class="form-row half">
									<label for="zip"><?php _e('Postal Code', 'memberdeck'); ?></label>
									<input type="text" size="20" class="zip" name="zip" value=""/>
								</div>
								<div class="form-row half">
									<label for="country"><?php _e('Country', 'memberdeck'); ?></label>
									<input type="text" size="20" class="country" name="country" value=""/>
								</div>
								<div class="submit">
									<button class="button button-primary" id="confirm-edit-profile"><?php _e('Save', 'memberdeck'); ?></button>
									<button class="button" id="cancel-edit-profile"><?php _e('Cancel', 'memberdeck'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="edit-user" style="display: none;">
		<form id="update-user">
			<h3><?php _e('Manage Access Levels', 'memberdeck'); ?></h3>
			<p><?php _e('Check or uncheck to grant or remove access to each level. Expiration will be automatically determined based on level settings. Click expiration date to customize', 'memberdeck'); ?>.</p>
			<table class="wp-list-table widefat fixed pages" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"/></th>
						<th><?php _e('Level Name', 'memberdeck'); ?></th>
						<th><?php _e('Order Date', 'memberdeck'); ?></th>
						<th><?php _e('Expiration Date', 'memberdeck'); ?></th>
					</tr>
				</thead>
				<tbody class="form-input">
				</tbody>
			</table>
			<div class="submit">
				<button class="button button-primary" id="confirm-edit"><?php _e('Save', 'memberdeck'); ?></button>
				<button class="button" id="cancel-edit"><?php _e('Cancel', 'memberdeck'); ?></button>
			</div>
		</form>
	</div>
</div>