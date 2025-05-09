jQuery(document).ready(function() {
	// Bridge js
	jQuery(".mdid-project-grid:odd").addClass('odd');
	jQuery("#master-select-all").click(function() {
		jQuery(".level-select").attr('checked', 'checked').prop('checked', true);
	});
	jQuery("#master-clear-all").click(function() {
		jQuery(".level-select").removeAttr('checked').prop('checked',false);
	});
	jQuery(".mdid-project-grid .select-all").click(function() {
		var projectID = jQuery(this).parents('.mdid-project-grid').data('projectid');
		jQuery('.select-' + projectID).attr('checked', 'checked').prop('checked', true);
	});
	jQuery(".mdid-project-grid .clear-all").click(function() {
		var projectID = jQuery(this).parents('.mdid-project-grid').data('projectid');
		jQuery('.select-' + projectID).removeAttr('checked').prop('checked',false);
	});
	var levelID = jQuery('#edit-level').val();
	jQuery('input[name="edit-level-filter"]').change(function() {
		var options = jQuery('select[name="edit-level"] option');
		var filter = jQuery(this).val();
		filter = filter.toLowerCase();
		console.log(filter);
		
		jQuery.each(jQuery(options), function(k, el) {
			var optionText = jQuery(this).text();
			optionText = optionText.toLowerCase();
			if (filter === '') {
				jQuery(options).show();
			}
			else {
				if (optionText.includes(filter)) {
					jQuery(this).show();
				}
				else {
					jQuery(this).hide();
				}
			}
		});
	});
	jQuery('.level-select').change(function() {
		if (jQuery(this).prop('checked') == true) {
			jQuery(this).removeClass('pending');
		}
		else {
			jQuery(this).addClass('pending');
		}
	});
	jQuery("#edit-level").change(function() {
		levelID = jQuery(this).val();
		if (levelID == idc_localization_strings.choose_product || levelID == 0) {
			jQuery("#save-assignments").attr('disabled', 'disabled').prop('disabled', true);
		}
		else {
			jQuery("#save-assignments").removeAttr('disabled').prop('disabled', false);
		}
		jQuery('.level-select').removeAttr('checked').prop('checked',false);
		jQuery.each(jQuery('.level-select'), function() {
			if (jQuery(this).data('owner') > 0) {
				if (jQuery(this).data('owner') == levelID) {
					jQuery(this).removeAttr('disabled').prop('disabled', false);
				}
				else {
					jQuery(this).attr('disabled', 'disabled').prop('disabled', true);
				}
			}
		});
		jQuery.ajax({
			url: md_ajaxurl,
			type: 'POST',
			data: {action: 'mdid_get_assignments', Level: levelID},
			success: function(res) {
				//console.log(res);
				var json = JSON.parse(res);
				jQuery.each(json, function(k, v) {
					var project = this.project;
					var levels = this.levels;
					var selected = jQuery('.mdid-project-grid[data-projectid="' + project + '"]');
					jQuery.each(levels, function() {
						var levelBox = jQuery('.select-' + project + '[data-level="' + this + '"]');
						jQuery(levelBox).attr('checked', 'checked').prop('checked', true);
					});
				});
			}
		});
	});
	jQuery("#save-assignments").click(function(e) {
		e.preventDefault();
		var levelID = jQuery('#edit-level').val();
		var parent = jQuery('.mdid-project-grid').has('input.level-select:checked');
		var assignments = {'projects': {}, 'level': levelID};
		jQuery.each(parent, function(x, y) {
			var projectID = jQuery(this).data('projectid');
			assignments.projects[x] = {};
			assignments.projects[x].id = projectID;
			assignments.projects[x].levels = {};
			jQuery(this).find('.level-select').each(function(k, v) {
				var level = jQuery(this).data('level');
				if (jQuery(this).prop('checked') == true) {
					jQuery(this).data('owner', levelID);
					assignments.projects[x].levels[k] = level;
				}
				else {
					if (jQuery(this).data('owner') == levelID) {
						jQuery(this).removeData('owner');
					}
				}
			});
		});
		jQuery.each(jQuery('.pending'), function() {
			if (jQuery(this).data('owner') == levelID) {
				jQuery(this).removeData('owner');
				jQuery(this).removeClass('pending');
			}
		});
		//console.log(assignments);
		var assignmentLength = Object.keys(assignments).length;
		if (assignmentLength > 0) {
			jQuery.ajax({
				url: md_ajaxurl,
				type: 'POST',
				data: {action: 'mdid_save_assignments', Assignments: assignments},
				success: function(res) {
					//console.log(res);
					location.reload();
				}
			});
		}
	});
});