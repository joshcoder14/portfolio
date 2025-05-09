jQuery(document).ready(function() {
	jQuery("#level-delete").hide();
	jQuery("#credit-delete").hide();
	jQuery("#download-delete").hide();
	if (jQuery('#edit-level').val() == "Choose Level") {
		jQuery('.list-shortcode').hide();
	}
	var bodyClass = jQuery('body').attr('class');
	if (bodyClass.indexOf('idc') !== -1) {
		jQuery('body').addClass('md-admin');
	}
	jQuery('.idc-attach-datepicker').datepicker({
		defaultDate: '0000-00-00',
		dateFormat : 'mm/dd/yy'
	});

	jQuery('.md_help_link').click(function() {
		var service = jQuery(this).attr('id');
		var service_id = '#' + service + '-help';
		jQuery(service_id).slideToggle('fast', function() {
			// Animation complete.
		});
	});
	// Events for Enable Credits button
	if (jQuery('#enable_credits').length > 0) {
		if (jQuery('#enable_credits').is(":checked")) {
			jQuery('#credit-value').parent('div').show();
			jQuery('#credit-settings-help').parent('div').show();
		} else {
			jQuery('#credit-value').parent('div').hide();
			jQuery('#credit-settings-help').parent('div').hide();
		}
	}
	jQuery('#enable_credits').change(function(e) {
		if (jQuery('#enable_credits').is(":checked")) {
			jQuery('#credit-value').parent('div').show();
			jQuery('#credit-settings-help').parent('div').show();
			if (jQuery('#global-currency').find('option[value="credits"]').length <= 0) {
				jQuery('#global-currency').append('<option value="credits" data-symbol="credits">'+ idc_localization_strings.virtual_currency + '</option>');
			}
			jQuery('.idc-credits-field').show();
			jQuery('.virtual-currency-selected').hide();
		} else {
			if (jQuery('#global-currency').val() == "credits") {
				jQuery('.virtual-currency-selected').show();
				jQuery('#enable_credits').attr('checked', 'checked').prop('checked',true);
				return;
			} else {
				jQuery('.virtual-currency-selected').hide();
			}
			jQuery('#credit-value').parent('div').hide();
			jQuery('#credit-settings-help').parent('div').hide();
			if (jQuery('#global-currency').val() == "credits") {
				jQuery('#global-currency').val('USD');
			}
			jQuery('#global-currency').find('option[value="credits"]').remove();
			jQuery('.idc-credits-field').hide();
		}
	});
	
	var showTerms = jQuery('input[name="show_terms"]').prop("checked");
	jQuery('input[name="show_terms"]').change(function() {
		jQuery('input[name="show_terms"]').prop("checked");
		jQuery('select[name="terms_page"], select[name="privacy_page"]').parent('div').toggle();
	});
	jQuery('select[name="export_product_choice"]').change(function() {
		allowExport();
	});
	// Checks for default product and click handlers
	if (jQuery('#enable_default_product').length > 0) {
		if (jQuery('#enable_default_product').is(":checked")) {
			jQuery('#default_product').parent('div').show();
		} else {
			jQuery('#default_product').parent('div').hide();
		}
	}
	jQuery('#enable_default_product').change(function(e) {
		if (jQuery(this).is(":checked")) {
			jQuery('#default_product').parent('div').show();
		} else {
			jQuery('#default_product').parent('div').hide();
		}
	});

	/*jQuery('input[name="export_customers"]').click(function(e) {
		e.preventDefault();
		export_customers();
	});*/
	if (jQuery('select[name="product-type"] option').length <= 1) {
		jQuery('select[name="product-type"]').parent('.form-input').hide();
	}
		
	
	function get_downloads() {
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'idmember_get_downloads'},
			success: function(res) {
				//console.log(res);
				var downloads = JSON.parse(res);
				//console.log(downloads);
				jQuery.each(downloads, function() {
					// options for level dropdown
					jQuery("#edit-download").append(jQuery("<option/>", {
						value: this.id,
						text: this.download_name
					}));
				});
				var occEnabled = jQuery(this).prop("checked");
				jQuery('input[name="enable_occ"]').removeAttr('checked').prop( 'checked', false );
				mdid_project_list();
				jQuery("#edit-download").change(function() {
					var downloadedit = parseInt(jQuery("#edit-download").val());
					jQuery('.lassign').removeAttr('checked').prop( 'checked', false );
					if (downloadedit) {
						jQuery("#download-name").val(downloads[downloadedit].download_name);
						jQuery("#download-version").val(downloads[downloadedit].version);
						if (downloads[downloadedit].enable_occ == '1') {
							jQuery('input[name="enable_occ"]').attr('checked', 'checked').prop('checked',true);
						}
						else {
							jQuery('input[name="enable_occ"]').removeAttr('checked').prop( 'checked', false );
						}
						if (downloads[downloadedit].hidden == '1') {
							jQuery('input[name="hidden"]').attr('checked', 'checked').prop('checked',true);
						}
						else {
							jQuery('input[name="hidden"]').removeAttr('checked').prop( 'checked', false );
						}
						if (downloads[downloadedit].enable_s3 == '1') {
							jQuery('input[name="enable_s3"]').attr('checked', 'checked').prop('checked',true);
						}
						else {
							jQuery('input[name="enable_s3"]').removeAttr('checked').prop( 'checked', false );
						}
						jQuery('#occ_level').val(downloads[downloadedit].occ_level);
						jQuery('#id_project').val(downloads[downloadedit].id_project);
						jQuery("#dash-position").val(downloads[downloadedit].position);
						jQuery('#licensed').val(downloads[downloadedit].licensed);
						var levels = downloads[downloadedit].levels;
						jQuery.each(levels, function(k,v) {
							//console.log(v);
							jQuery('.lassign[value="' + v + '"]').attr('checked', 'checked').prop('checked',true);
						});
						jQuery("#download-link").val(downloads[downloadedit].download_link);
						jQuery("#info-link").val(downloads[downloadedit].info_link);
						jQuery("#doc-link").val(downloads[downloadedit].doc_link);
						jQuery("#image-link").val(downloads[downloadedit].image_link);
						// If image link if not empty
						if (downloads[downloadedit].image_link !== "") {
							jQuery('#add-download-image').hide();
							jQuery('#remove-download-image').show();
							if (downloads[downloadedit].download_image_url !== undefined) {
								jQuery("#download-image-thumnail").attr("src", downloads[downloadedit].download_image_url).show();
							} else {
								jQuery("#download-image-thumnail").attr("src", downloads[downloadedit].image_link).show();
							}
						}
						else {
							jQuery('#add-download-image').show();
							jQuery('#remove-download-image').hide();
							jQuery("#download-image-thumnail").attr("src", "").hide();
						}
						jQuery("#button-text").val(downloads[downloadedit].button_text);
						jQuery("#download-submit").val(idc_localization_strings.update);
						jQuery("#download-delete").show();
					}
					else {
						jQuery("#download-name").val('');
						jQuery("#download-version").val('');
						jQuery('input[name="enable_s3"]').removeAttr('checked').prop( 'checked', false );
						jQuery('input[name="hidden"]').removeAttr('checked', 'checked').prop( 'checked', false );
						jQuery('input[name="enable_occ"]').removeAttr('checked').prop( 'checked', false );
						jQuery('#occ_level').val(0);
						jQuery('#id_project').val(0);
						jQuery("#dash-position").val('a');
						jQuery('#licensed').val('0');
						//jQuery('.lassign').removeAttr('checked').prop( 'checked', false );
						jQuery("#download-link").val('');
						jQuery("#info-link").val('');
						jQuery("#doc-link").val('');
						jQuery("#image-link").val('');
						jQuery("#button-text").val('');
						jQuery("#download-submit").val('Create');
						jQuery("#download-delete").hide();
					}
					toggle_occ_dash();
				});
				jQuery('input[name="enable_occ"]').change(function() {
					toggle_occ_dash();
				});
			}
		});
	}
	
	var creatorPerms = jQuery('select[name="creator_permissions"] option:selected').val();
	toggleCreatorPerms(creatorPerms);
	jQuery('select[name="creator_permissions"]').change(function() {
		creatorPerms = jQuery('select[name="creator_permissions"] option:selected').val();
		toggleCreatorPerms(creatorPerms);
	});
	
	function toggleCreatorPerms(creatorPerms) {
		if (creatorPerms == '2') {
			jQuery('#allowed-creator-levels').show();
		}
		else {
			jQuery('#allowed-creator-levels').hide();
		}
	}
	function allowExport() {
		var productExport = jQuery('select[name="export_product_choice"]').val();
		if (productExport > 0) {
			jQuery('input[name="export_customers"]').removeAttr('disabled').prop('disabled', false);
		}
		else {
			jQuery('input[name="export_customers"]').attr('disabled', 'disabled').prop('disabled', true);
		}
	}
	function export_customers() {
		jQuery('input[name="export_customers"]').attr('disabled', 'disabled').prop('disabled', true);
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'md_export_customers'},
			success: function(res) {
				console.log(res);
				var url = res;
				if (url !== undefined) {
					//jQuery('input[name="export_customers"]').after('&nbsp;<a href="' + url + '"><button class="button">Download File</button></a>');
					jQuery('input[name="export_customers"]').removeAttr('disabled').prop('disabled', false);
					//window.location.href = url;
				}
			}
		});
	}
	function toggle_occ_dash() {
		occEnabled = jQuery('input[name="enable_occ"]').prop("checked");
		//console.log(occEnabled);
		if (occEnabled == 'checked') {
			jQuery('#occ_level').parents('.form-input').show();
			if (jQuery('#id_project').length > 0) {
				jQuery('#id_project').parents('.form-input').show();
			}
			else {
				jQuery('#id_project').parents('.form-input').hide();
			}
		}
		else {
			jQuery('#occ_level').parents('.form-input').hide();
			jQuery('#id_project').parents('.form-input').hide();
		}
	}
	function mdid_project_list() {
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'mdid_project_list'},
			success: function(res) {
				if (res) {
					json = JSON.parse(res);
					if (json) {
						//console.log(json);
						jQuery.each(json, function() {
							if (jQuery('#id_project option[value="' + this.id + '"]').length <= 0) {
								jQuery('#id_project').append(jQuery('<option/>', {
									value: this.id,
									text: this.product_name
								}));
							}
						});
						
					}
				}
			}
		});
	}
	get_levels(idc_product_filter);
	get_downloads();
	allowExport();
	
	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idmember_get_credits'},
		success: function(res) {
			//console.log(res);
			credits = JSON.parse(res);
			jQuery.each(credits, function() {
				jQuery("#edit-credit").append(jQuery("<option/>", {
					value: this.id,
					text: this.credit_name
				}));
			});
			jQuery("#edit-credit").change(function() {
				var creditedit = parseInt(jQuery("#edit-credit").val());
				jQuery("#credit-name").val(credits[creditedit].credit_name);
				jQuery("#credit-price").val(credits[creditedit].credit_price);
				jQuery("#credit-count").val(credits[creditedit].credit_count);
				jQuery("#credit-assign").val(credits[creditedit].credit_level);
				jQuery("#credit-submit").val(idc_localization_strings.update);
				jQuery("#credit-delete").show();
			});
		}
	});
	jQuery('#memberdeck-users td.username').click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('tr').attr('id');
		if (parent) {
			id = parent.replace('user-', '');
			jQuery("#user-list, .search-box").hide();
			jQuery("#user-profile").show();
			jQuery(document).trigger('idc_member_admin_edit', id);
			jQuery.ajax({
				async: false,
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_get_profile', ID: id},
				success: function(res) {
					//console.log(res);
					json = JSON.parse(res);
					if (json.shipping_info) {
						jQuery.each(json.shipping_info, function(k, v) {
							//console.log('k: ' + k);
							//console.log('v: ' + v);
							jQuery('#user-profile .' + k).val(v);
						});
					}
					if (json.usermeta) {
						jQuery.each(json.usermeta, function(k, v) {
							//console.log('k: ' + k);
							//console.log('v: ' + v);
							jQuery('#user-profile .' + k).val(v);
						});
					}
					if (json.userdata.data) {
						jQuery.each(json.userdata.data, function(k, v) {
							//console.log('k: ' + k);
							//console.log('v: ' + v);
							jQuery('#user-profile .' + k).val(v);
						});
					}
					jQuery(document).trigger('user_profile_edit', json);
					jQuery('#confirm-edit-profile').click(function(e) {
						e.preventDefault();
						var new_userdata = {};
						var inputs = jQuery('#user-profile input');
						var error = false;
						//console.log(inputs);
						jQuery.each(jQuery(inputs), function(k,v) {
							//console.log(jQuery(this).attr('name'));
							//console.log('k: ' + k + ' = v: ' + v);
							var inputName = jQuery(this).attr('name');
							if (inputName == 'display_name' || inputName == 'user_email') {
								if (jQuery(this).val().length <= 0) {
									error = true;
								}
							}
							if (jQuery(this).attr('type') == 'checkbox') {
								if (jQuery(this).prop("checked") == 'checked') {
									new_userdata[inputName] = jQuery(this).val();
								}
							}
							else {
								new_userdata[inputName] = jQuery(this).val();
							}
						});
						new_userdata['id'] = id;
						//console.log(new_userdata);
						if (error) {
							jQuery('p.error').remove();
							jQuery('#user-profile').prepend('<p class="error">Error, missing or empty fields.</p>');
						}
						else {
							jQuery.ajax({
								async: false,
								url: md_ajaxurl,
								type: 'POST',
								data: {action: 'idmember_edit_profile', Userdata: new_userdata},
								success: function(res) {	
									//console.log(res);
									location.reload();
								}
							});
						}
					});
				}
			});
			/* Edit Subscriptions js */
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'admin_edit_subscription', user_id: id},
				success: function(res) {
					//console.log(res);
					if (res) {
						json = JSON.parse(res);
						if (!jQuery.isEmptyObject(json)) {
							jQuery('.postbox').show();
							//console.log(json);
							jQuery.each(json, function() {
								//console.log(this);
								jQuery('select[name="sub_list"]').append('<option value="' + this.id + '">' + this.plan_id + '</option>');
							});
						}
					}
				}
			});
			jQuery('select[name="sub_list"]').change(function() {
				var planID = jQuery(this).children('option:selected').val();
				if (planID !== '0') {
					var plan = jQuery(this).children('option:selected').text();
					//console.log(planID);
					jQuery('button[name="cancel_sub"]').removeAttr('disabled').prop('disabled', false).show();
				}
				else {
					jQuery('button[name="cancel_sub"]').attr('disabled', 'disabled').prop('disabled', true).hide();
				}
			});
			jQuery('button[name="cancel_sub"]').click(function(e) {
				e.preventDefault();
				jQuery('.sub_response').text('').removeClass().addClass('sub_response');
				var planID = jQuery('select[name="sub_list"]').children('option:selected').val();
				var plan = jQuery('select[name="sub_list"]').children('option:selected').text();
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idc_cancel_sub', plan_id: planID, plan: plan, user_id: id},
					success: function(res) {
						//console.log(res);
						if (res) {
							var json = JSON.parse(res);
							if (json.status == 'success') {
								jQuery('select[name="sub_list"] option:selected').remove();
								if (jQuery('select[name="sub_list"] option').size()  == 1) {
									jQuery('button[name="cancel_sub"]').attr('disabled', 'disabled').prop('disabled', true).hide();
								}
							}
							else {

							}
							jQuery('.sub_response').text(json.message).addClass(json.status);
						}
					}
				});
			});
		}
		jQuery("#cancel-edit-profile").click(function(e) {
			jQuery(".form-input").html('');
			jQuery("#user-list, .search-box").show();
			jQuery("#user-profile").hide();
			jQuery(document).trigger('idc_member_admin_cancel');
			e.preventDefault();
		});
	});
	// #devnote find better way to framework this
	if (jQuery('.idc_page_idc-users select.level-selector').length > 0) {
		var selected = jQuery('.idc_page_idc-users select.level-selector').data('selected');
		if (selected > 0) {
			jQuery('.idc_page_idc-users select.level-selector').val(selected);
		}
		jQuery('.idc_page_idc-users select.level-selector').change(function(e) {
			var val = jQuery(this).val();
			var redirect = idfStripUrlQuery(idf_current_url) + '?page=idc-users';
			if (val > 0) {
				redirect = redirect + '&level=' + val;
			}
			location.href = redirect;
		});
	}
	jQuery("#memberdeck-users td.current-levels").click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('tr').attr('id');
		if (parent) {
			id = parent.replace('user-', '');
			jQuery("#user-list, .search-box").hide();
			jQuery("#edit-user").show();

			jQuery.ajax({
				async: false,
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_get_levels'},
				success: function(res) {
					//console.log(res);
					levels = JSON.parse(res);
					//console.log(levels);
					jQuery.each(levels, function() {
						jQuery(".form-input").append(jQuery("<tr class='level" + this.id + "'><th class='check-column'><input type='checkbox' value='" + this.id + "'/></th><td><label>" + this.level_name + "</label></td></tr>"));
					});
				}
			});
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_edit_user', ID: id},
				success: function(res) {
					//console.log(res);
					var count = jQuery(".form-input input[type='checkbox']").size() - 1;
					json = JSON.parse(res);
					console.log(json);
					if (Object.keys(json.levels).length > 0) {
						for (i = 0; i <= count; i++) {
							//jQuery("input[value='" + json.levels[i] + "']").attr('checked', 'checked').prop('checked',true);
							jQuery('.form-input tr').eq(i).append('<td/><td></td>');
						}
						jQuery.each(json.levels, function(k,v) {
							jQuery("input[value='" + v + "']").attr('checked', 'checked').prop('checked',true);
						});
					}
					else {
						for (i = 0; i <= count; i++) {
							jQuery('.form-input tr').eq(i).append('<td/><td>none</td>');
						}
					}
					jQuery.each(json.levels, function(k, level_id) {
						console.log(k);
						console.log(level_id);
						console.log(json);
						if (json.lasts[k]) {
							var edate = json.lasts[k]['e_date'];
							var odate = json.lasts[k]['order_date'];
							var oid = json.lasts[k]['id'];
							if (!edate || edate == undefined || edate == '') {
								edate = 'lifetime';
							}
							else if (edate.indexOf('0000-00-00 00:00:00') !== -1) {
								edate = 'lifetime';
							}
							jQuery(".form-input tr.level" + level_id).children('td:last-child').prev().text(odate);
							jQuery(".form-input tr.level" + level_id).children('td:last-child').html('<a data-id="' + oid + '" class="edit-date" href="#">' + edate + '</a>');
						}
					});
					jQuery('.form-input').on('click', '.edit-date', function(e) {
						e.preventDefault();
						var clone = this;
						oID = jQuery(this).data('id');
						jQuery(this).replaceWith('<span id="edit-fields"><input type="text" name="edit-date" value="yyyy-mm-dd"/><p class="edit-options"><span class="trash"><a href="#" class="edit-cancel delete">cancel</a></span>&nbsp;|&nbsp;<a href="#" class="lifetime">non-expiring</a></span></p>');
						jQuery('input[name="edit-date"]').datepicker({
							defaultDate: '0000-00-00',
							dateFormat : 'yy-mm-dd',
							onClose: function() {
								var newDate = jQuery(this).val();
								jQuery('span.trash').remove();
								if (newDate !== '' || newDate !== 'yyyy-mm-dd') {
									jQuery(this).replaceWith('<a data-id="' + oID + '" class="edit-date date-edited" href="#">' + newDate + '</a>');
								}
								else {
									jQuery(this).replaceWith('<a data-id="' + oID + '" class="edit-date date-edited" href="#">0000-00-00</a>');
								}
							}
						});
						jQuery(".edit-cancel").click(function(e) {
							e.preventDefault();
							jQuery('#edit-fields').replaceWith(clone);
						});
						jQuery(".lifetime").click(function(e) {
							e.preventDefault();
							jQuery('p.edit-options').remove();
							jQuery('input[name="edit-date"]').replaceWith('<a data-id="' + id + '" class="edit-date date-edited" href="#">0000-00-00</a>');
						});
					});
				}
			});

			jQuery("#confirm-edit").click(function(e) {
				e.preventDefault();
				var new_levels = [];
				jQuery.each(levels, function() {
					new_levels.push({'level': this.id, 'value': jQuery(".form-input input[value='" + this.id + "']").prop("checked")});
				});
				var new_dates = [];
				jQuery.each(jQuery('.date-edited'), function() {
					var dateID = jQuery(this).data('id');
					var date = jQuery(this).text();
					new_dates.push({'id': dateID, 'date': date});
				});
				//console.log(new_levels);
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idmember_save_user', ID: id, Levels: new_levels, Dates: new_dates},
					success: function(res) {
						console.log(res);
						if (!res) {
							jQuery(".form-input").html('');
							jQuery("#user-list, .search-box").show();
							jQuery("#edit-user").hide();
							window.location = "?page=idc-users";
						}
					}
				});
			});
		}
		jQuery("#cancel-edit").click(function(e) {
			e.preventDefault();
			//console.log('this');
			jQuery(".form-input").html('');
			jQuery("#user-list").show();
			jQuery("#edit-user").hide();
		});
	});
	jQuery('#memberdeck-users td.current-credits').click(function(e) {
		e.preventDefault();
		var parent = jQuery(this).parent('tr').attr('id');
		if (parent) {
			id = parent.replace('user-', '');
			jQuery("#user-list, .search-box").hide();
			jQuery("#user-credits").show();

			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'idmember_credit_total', ID: id},
				success: function(res) {
					console.log(res);
					if (res == '') {
						res = 0;
					}
					jQuery('input[name="current-credits"]').val(res);
				}
			});
			jQuery('#confirm-credits').click(function(e) {
				var credits = jQuery('input[name="current-credits"]').val();
				e.preventDefault();
				jQuery.ajax({
					url: md_ajaxurl,
					type: 'POST',
					data: {action: 'idmember_save_credits', ID: id, Credits: credits},
					success: function(res) {
						//console.log(res);
						jQuery(".form-input").html('');
						jQuery("#user-list").show();
						jQuery("#user-credits").hide();
						window.location = "?page=idc-users";
					}
				});
			});
		}
		jQuery("#cancel-credits").click(function(e) {
			e.preventDefault();
			//console.log('this');
			jQuery(".form-input").html('');
			jQuery("#user-list").show();
			jQuery("#user-credits").hide();
		});
	});
	jQuery('#memberdeck-users td.order_item.order_edit a').click(function(e) {
		jQuery(document).trigger('idcOrderItemEdit', jQuery(this));
	});
	jQuery('#assign-checkbox .select').click(function(e) {
		e.preventDefault();
		jQuery('.lassign').attr('checked', 'checked').prop('checked',true);
	});
	jQuery('#assign-checkbox .clear').click(function(e) {
		e.preventDefault();
		jQuery('.lassign').removeAttr('checked').prop( 'checked', false );
	});
	// Gateway js
	jQuery.getJSON(md_currencies, function(data) {
		jQuery.each(data.currency, function() {
			jQuery('#pp-currency').append('<option value="' + this.code + '" data-symbol="' + this.symbol + '">' + this.code + '</option>');
			var selCurrency = jQuery('#pp-currency').data('selected');
			jQuery('#pp-currency').val(selCurrency);
			// if PayPal Adaptive exists in the page
			if (jQuery('#ppada_currency').length > 0) {
				jQuery('#ppada_currency').append('<option value="' + this.code + '" data-symbol="' + this.symbol + '">' + this.code + '</option>');
			}
		});
		jQuery('#pp-currency').change(function() {
			var selSymbol = jQuery(this).find(':selected').data('symbol');
			jQuery('input[name="pp-symbol"]').val(selSymbol);
		});
	});

	// Loading Global currencies json and adding where required
	jQuery.getJSON(idc_global_currencies, function(json, textStatus) {
		jQuery.each(json, function() {
			// If the selection for global currency exists in the page
			if (jQuery('#global-currency').length > 0) {
				jQuery('#global-currency').append('<option value="' + this.Currency_Code + '" data-symbol="' + this.Symbol + '">' + this.Currency_Code + '</option>');
			}
		});

		// If the selection for global currency exists in the page, append virtual currency to it also
		if (jQuery('#global-currency').length > 0) {
			var selGlobalCurrency = jQuery('#global-currency').data('selected');
			// Appending Coinbase currency Bitcoins
			jQuery('#global-currency').append('<option value="BTC" data-symbol="BTC">BTC</option>');
			if (jQuery('#enable_credits').is(":checked")) {
				jQuery('#global-currency').append('<option value="credits" data-symbol="credits">'+ idc_localization_strings.virtual_currency + '</option>');
			} else {
				if (selGlobalCurrency == "credits") {
					selGlobalCurrency = "USD";
				}
			}
			// Selecting the option saved
			jQuery('#global-currency').val(selGlobalCurrency);
			// Adding an option to use idcf settings for currency
			//jQuery('#global-currency').prepend('<option value="idcf">'+ idc_localization_strings.use_idcf_settings +'</option>');
		}
	});

	// Loading stripe currencies if it's the Gateways page
	if (jQuery('#gateway-settings').length > 0 && jQuery('#stripe_currency').length > 0) {
		jQuery.getJSON(idc_stripe_currencies, function(json, textStatus) {
			jQuery.each(json, function() {
				jQuery('#stripe_currency').append('<option value="' + this.code + '">' + this.code + '</option>');
			});
			// Selecting the currency that is stored in db
			var selStripeCurrency = jQuery('#stripe_currency').data('selected');
			jQuery('#stripe_currency').val(selStripeCurrency);
		});
	}

	/* This code ensures that only one credit card processing gateway can be active at once */
	if (jQuery('.cc-gateway-chkbox:checked').length > 0) {
		var checkbox = jQuery('.cc-gateway-chkbox:checked').get(0);
		if (jQuery(checkbox).attr('siblings') !== undefined) {
			var sibling_name = jQuery(checkbox).attr('siblings');
			var sibling = jQuery('[siblings="'+ sibling_name +'"]').get(0);

			// Disable all CC gateway checkboxes except the siblings
			jQuery('.cc-gateway-chkbox').not('.cc-gateway-chkbox[siblings="'+ sibling_name +'"]').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		} else {
			var sibling = null;
			var checkbox_id = jQuery(checkbox).attr('id');
			// Disable all CC gateway checkboxes except the siblings
			jQuery('.cc-gateway-chkbox').not('.cc-gateway-chkbox[id="'+ checkbox_id +'"]').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);	
		}
		
		// if (jQuery('#es').prop("checked") == 'checked') {
		// 	jQuery('#efd').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// 	jQuery('#eauthnet').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// }
		// else if (jQuery('#efd').prop("checked") == 'checked') {
		// 	jQuery('#es').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// 	jQuery('#esc').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// 	jQuery('#eauthnet').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// }
		// else if (jQuery('#eauthnet').prop("checked") == 'checked') {
		// 	jQuery('#es').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// 	jQuery('#efd').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
		// }
	}

	jQuery('.cc-gateway-chkbox').click(function(e) {
		var checkbox = jQuery(this);
		if (checkbox.is(':checked')) {
			// check if there are any siblings of this checkbox
			if (checkbox.attr('siblings') !== undefined) {
				var sibling_name = checkbox.attr('siblings');
				var sibling = jQuery('[siblings="'+ sibling_name +'"]').get(0);
			} else {
				var sibling = null;
			}
			// console.log('sibling_name: ', sibling_name, ', sibling: ', sibling);
			if (sibling !== null) {
				// Check that 1st sibling is checked and that current checked box is 1st sibling
				if (jQuery(sibling).is(':checked') && checkbox.attr('id') == jQuery(sibling).attr('id')) {
					// Disable all CC gateway checkboxes
					jQuery('.cc-gateway-chkbox').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
					// Enable all siblings, and check the checked box, on which mouse has clicked
					jQuery('[siblings="'+ sibling_name +'"]').removeAttr('disabled').prop('disabled', false);
					checkbox.attr('checked', 'checked').prop('checked',true);
				}
				// If 1st sibling is checked and current checked box is not 1st sibling
				else if (jQuery(sibling).is(':checked')) {
					// Disable all CC gateway checkboxes except the siblings
					jQuery('.cc-gateway-chkbox').not('.cc-gateway-chkbox[siblings="'+ sibling_name +'"]').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
					// Enable all siblings as well
					jQuery('[siblings="'+ sibling_name +'"]').removeAttr('disabled').prop('disabled', false);
					// Check the checked box and enable it
					checkbox.attr('checked', 'checked').prop('checked',true);
					// Check the 1st sibling and enable it
					jQuery(sibling).attr('checked', 'checked').prop('checked',true);
				}
			} else {
				// No siblings, Enable only current checkbox and mark it checked
				jQuery('.cc-gateway-chkbox').removeAttr('checked').prop( 'checked', false ).attr('disabled', 'disabled').prop('disabled', true);
				checkbox.attr('checked', 'checked').prop('checked',true).removeAttr('disabled').prop('disabled', false);
			}
		} else {
			// check if there are any siblings of this checkbox
			if (checkbox.attr('siblings') !== undefined) {
				var sibling_name = checkbox.attr('siblings');
				var sibling = jQuery('[siblings="'+ sibling_name +'"]').get(0);
			} else {
				var sibling = null;
			}
			if (sibling !== null && jQuery(sibling).is(':checked')) {

			} else if ((sibling !== null && !jQuery(sibling).is(':checked')) || (sibling == null)) {
				jQuery('.cc-gateway-chkbox').removeAttr('disabled').prop('disabled', false);
			}
		}
	});
	
	// PayPal checks for selecting one paypal payment method at a time
	jQuery('#epp').click(function(e) {
		if (jQuery(this).is(":checked")) {
			jQuery('#eppadap').prop('checked', false);
		}
	});
	jQuery('#eppadap').click(function(e) {
		if (jQuery(this).is(":checked")) {
			jQuery('#epp').prop('checked', false);
		}
	});
	// If it's gateway settings page
	if (jQuery('.enable_paypal').length > 0) {
		var ppMethod = jQuery('.enable_paypal:checked').attr('name');
		togglePP(ppMethod);
		toggleLiveTest(ppMethod);
		jQuery('.enable_paypal').change(function(e) {
			ppMethod = jQuery('.enable_paypal:checked').attr('name');
			togglePP(ppMethod);
			toggleLiveTest(ppMethod);
		});
		jQuery('input[name="test"]').change(function() {
			ppMethod = jQuery('.enable_paypal:checked').attr('name');
			toggleLiveTest(ppMethod);
			togglePP(ppMethod);
		});
		
		jQuery('#ppadap_max_preauth_period').keyup(function(e) {
			if (/[0-9]/.test(jQuery('#ppadap_max_preauth_period').val()) === false) {
				jQuery(this).val('');
			} else if (jQuery(this).val() > 364) {
				jQuery(this).val('364');
			}
		});
	}

	function togglePP(ppMethod) {
		if (ppMethod == undefined) {
			jQuery('.enable_paypal').removeAttr('disabled').prop('disabled', false);
		}
		else {
			jQuery('.enable_paypal:not(:checked)').attr('disabled', 'disabled').prop('disabled', true);
		}
	}

	function toggleLiveTest(ppMethod) {
		jQuery('.test-field').hide();
		jQuery('.live-field').hide();
		if (jQuery('input[name="test"]').is(':checked')) {
			if (ppMethod == 'epp' || ppMethod == undefined) {
				jQuery('.pp-standard-field.test-field').show();
			}
			else if (ppMethod == 'eppadap') {
				jQuery('.pp-adaptive-field.test-field').show();
			}
		}
		else {
			if (ppMethod == 'epp' || ppMethod == undefined) {
				jQuery('.pp-standard-field.live-field').show();
			}
			else if (ppMethod == 'eppadap') {
				jQuery('.pp-adaptive-field.live-field').show();
			}
		}
	}

	if (jQuery('#charge-screen').length > 0) {
		var filter = {
			'where': 'txn_type',
			'value': 'preauth'
		}
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'idmember_get_levels', filter: filter},
			success: function(res) {
				//console.log(res);
				if (res) {
					json = JSON.parse(res);
					jQuery.each(json, function(k,v) {
						jQuery(".md-settings-container #level-list").append(jQuery("<option/>", {
							value: this.id,
							text: this.level_name
						}));
					});
				}
			}
		});
	}
	jQuery('#btnProcessPreauth').click(function(e) {
		e.preventDefault();
		jQuery('#charge-notice').hide();
		jQuery('#charge-notice .success-count, #charge-notice .fail-count').text('');
		jQuery(this).attr('disabled', 'disabled').prop('disabled', true);
		var level = jQuery('#level-list').val();
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'md_process_preauth', Level: level},
			success: function(res) {
				//console.log(res);
				json = JSON.parse(res);
				if(typeof(json.counts)!=="undefined"){jQuery("#charge-confirm").html('<div id="charge-notice" class="updated fade below-h2" id="message"><p><span class="success-count">'+json.counts.success+'</span> Successful Transactions Processed, <span class="fail-count">'+json.counts.failures+'</span> Failed Transactions.</p><a id="close-notice" href="#">Close</a></div>')}else{jQuery("#charge-confirm").html('<div id="charge-notice" class="updated fade below-h2" id="message"><p><span class="success-count">'+json.message+'</p><a id="close-notice" href="#">Close</a></div>')}
				//jQuery("#charge-confirm").html('<div id="charge-notice" class="updated fade below-h2" id="message"><p><span class="success-count">' + json.counts.success + '</span> Successful Transactions Processed, <span class="fail-count">' + json.counts.failures + '</span> Failed Transactions.</p><a id="close-notice" href="#">Close</a></div>'); //Issue #16 to fix the FD checkout form with conditional fields to hide/show based on payment gateway seection
				jQuery('#charge-notice').show();
	    		jQuery("#close-notice").click(function(event) {
	    			if (jQuery("#charge-notice").is(":visible")) {
	    				jQuery("#charge-notice").hide();
	    			}
	    		});
				jQuery('#btnProcessPreauth').removeAttr('disabled').prop('disabled', false);
			}
		});
	});
	
	// For Media Uploader 
	var idc_download_image_frame;
	jQuery('#add-download-image').click(function(event) {
		event.preventDefault();
		// If the media frame already exists, reopen it.
		if (idc_download_image_frame) {
			// Open frame
			idc_download_image_frame.open();
			return;
		}
		// Create the media frame.
		idc_download_image_frame = wp.media.frames.idc_download_image_frame = wp.media({
			title: jQuery(this).data('uploader_title'),
			button: {
				text: jQuery(this).data('uploader_button_text'),
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
		// When an image is selected, run a callback.
		idc_download_image_frame.on('select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = idc_download_image_frame.state().get('selection').first().toJSON();
			console.log('attachment: ', attachment);
			jQuery('#image-link').val(attachment.id);
			if (attachment.sizes.thumbnail !== undefined) {
				jQuery('#download-image-thumnail').attr('src', attachment.sizes.thumbnail.url).show();
			} else {
				jQuery('#download-image-thumnail').attr('src', attachment.sizes.full.url).show();
			}
			jQuery('#add-download-image').hide();
			jQuery('#remove-download-image').show();
		});
		// Finally, open the modal
		idc_download_image_frame.open();
	});
	jQuery('#remove-download-image').click(function(e) {
		jQuery('#add-download-image').show();
		jQuery(this).hide();
		jQuery('#download-image-thumnail').attr('src', '').hide();
		jQuery('#image-link').val('');
	});

	jQuery('.pre-order-status-link').click(function(e) {
		var order_id = jQuery(this).data('order-id');
		if (order_id > 0) {
			jQuery('.preorder-status p.preorder-status-date').text(jQuery(this).data('preorder-status-date'));
			jQuery('.preorder-status p.preorder-status-error').text(jQuery(this).data('preorder-status-error'));
		}
	})
});
jQuery(document).bind('idcOrderItemEdit', function(e, object) {
	idcDiscardOrderEdit();
	var editValue = jQuery(object).text();
	var editItem = jQuery(object).parent('td');
	jQuery(object).hide();
	jQuery(object).after('<input type="text" data-item="' + jQuery(editItem).attr('id') + '" class="order_edit_field" value="' + editValue + '"/> <a href="#" class="order_edit_undo"><i class="fa fa-undo"></i></a> <a href="#' + editItem + '" class="order_edit_save"><i class="fa fa-save"></i></a>');
	jQuery('#memberdeck-users td.order_item.order_edit a.order_edit_undo').click(function(e) {
		jQuery(document).trigger('idcOrderItemUndo', editItem);
	});
	jQuery('#memberdeck-users td.order_item.order_edit a.order_edit_save').click(function(e) {
		jQuery(document).trigger('idcOrderItemSave', editItem);
	});
	jQuery('#memberdeck-users td.order_item.order_edit input.order_edit_field').keydown(function(e) {
		if (e.keyCode == '13') {
			e.preventDefault();
			jQuery(document).trigger('idcOrderItemSave', editItem);
		}
	});
});
jQuery(document).bind('idcOrderItemUndo', function(e, editItem) {
	idcDiscardOrderEdit();
});
jQuery(document).bind('idcOrderItemSave', function(e, editItem) {
	// #devnote currency code
	var updateArgs = idcCreateOrderEditObject(editItem);
	jQuery(document).trigger('idcOrderItemUpdate', updateArgs);
});
jQuery(document).bind('idcOrderItemUpdate', function(e, updateArgs) {
	console.log(updateArgs);
	jQuery.ajax({
		url: md_ajaxurl,
		type: 'POST',
		data: {action: 'idc_order_item_update', args: updateArgs},
		success: function(res) {
			if (typeof(res) !== 'undefined') {
				if (res) {
					var currencyCode = jQuery('tr[data-id="' + updateArgs.id + '"]').data('currency-code');
					jQuery('#' + updateArgs.column + '-' + updateArgs.id + ' a').text(currencyCode + updateArgs.value);
					idcDiscardOrderEdit();
				}
			}
		}
	});
});
function idcDiscardOrderEdit() {
	jQuery('#memberdeck-users td.order_item.order_edit a').show();
	jQuery('#memberdeck-users td.order_item.order_edit .order_edit_field, #memberdeck-users td.order_item.order_edit a.order_edit_save, #memberdeck-users td.order_item.order_edit a.order_edit_undo').remove();
}
function idcCreateOrderEditObject(editItem) {
	var updateArgs = {
		id: jQuery(editItem).parent('tr').data('id'),
		column: jQuery(editItem).data('column'),
		value: jQuery(editItem).find('input.order_edit_field').val()
	}
	var editType = jQuery(editItem).data('type');
	switch(editType) {
		case 'float':
			updateArgs.value = idfParseFloat(updateArgs.value);
			break;
		default:
			break;
	}
	return updateArgs;
}

/**
 * Test AWS S3 connectivity.
 */
jQuery(document).ready(function($) {
	// Attach a submit event handler to the form with the id 'aws-s3-connection-test'
	$('#aws-s3-connection-test').on('submit', function(e) {
		e.preventDefault(); // Prevent the default form submission

		// Cache the form element and the spinner element for later use
		var $form = $(this);
		var $spinner = $form.find(".spinner");
		var $successMsg = $form.find(".aws_success_msg");
		var $errorMsg = $form.find(".aws_error_msg");

		// Add the 'is-active' class to the spinner to show loading animation
		$spinner.addClass("is-active");

		// Perform an AJAX request to the server
		$.ajax({
			url: md_ajaxurl,
			type: "POST",
			dataType: 'json',
			data: {
				action: 'idc_aws_s3_connection_test',
			},
			success: function(response) {
				if (response.success) {
					// On success, display the success message and hide the error message
					$successMsg.text(response.data.message).css("display", "block");
					$errorMsg.css("display", "none");
				} else {
					// On error, display the error message and hide the success message
					$errorMsg.text(response.data.message).css("display", "block");
					$successMsg.css("display", "none");
				}
			},
			error: function(xhr, status, error) {
				// On AJAX error, display the error message and hide the success message
				$errorMsg.text(xhr.responseJSON.message).css("display", "block");
				$successMsg.css("display", "none");
			},
			complete: function() {
				// Remove the 'is-active' class from the spinner when the request is complete
				$spinner.removeClass("is-active");
			}
		});
	});
});
