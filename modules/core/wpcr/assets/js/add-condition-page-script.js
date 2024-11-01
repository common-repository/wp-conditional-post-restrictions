//Main variables

var row_template = "";

jQuery(document).ready(function ($) {
	
        if($('.vegacorp-or-groups').data('settings_link')){
            jQuery('.page-title-action').after('<a href="' + $('.vegacorp-or-groups').data('settings_link') + '" class="page-title-action">' + vegacorp_conditions_obj.text.go_to_settings + '</a>');
        }

	init_selects_2($(".condition"));
	change_input_place_holder();

	//Initializing events
	$(".btn-add-group").click(function () {
		var $groups_container = $(this).parent().find('.vegacorp-or-groups'); 
                var conditions_name   = $groups_container.data('conditions_name');  
		$groups_container.append($('#' + conditions_name + '-group-template').html());
		rename_groups($groups_container.children());
		init_selects_2($(".condition"));
	});

	$("body").on("click", ".btn-remove-group", function () {

		var $groups_container = $(this).closest('.vegacorp-or-groups'); 
		var $group = $(this).closest(".or_group_table");
		var number_of_groups = $groups_container.children().length;

		if (number_of_groups === 1){
			return false;
                }

		$group.remove();
		rename_groups($groups_container.children());

	});

	$("body").on("click", ".btn-add-condition", function () {

		var $group_index    = $(this).closest("table").index();

                var conditions_name = $(this).closest('.vegacorp-or-groups').data('conditions_name');  
        
		var $condition_row_template = $($('#' + conditions_name + '-row-template').html());

		$condition_row_template.insertAfter($(this).parent().parent());
		var $group_conditions_container = $(this).closest(".conditions");
		rename_conditions($group_conditions_container.children(), $group_index);

		var countAndConditions = $group_conditions_container.children().length;
		if (countAndConditions > 3 && !jQuery('.many-and-conditions-warning').length) {
			$group_conditions_container.append('<tr class="condition many-and-conditions-warning"><td colspan="6">' + vegacorp_conditions_obj.text.too_many_and_conditions + '</td></tr>');
		}

		init_selects_2($(this).closest('.vegacorp-or-groups').find(".condition"));

	});

	$("body").on("click", ".btn-remove-condition", function () {

		var $group_index          = $(this).closest("table").index();
		
		var $conditions_container = $(this).closest(".conditions");
		var number_of_conditions  = $conditions_container.children().length;
                var too_many_and_conditions_warning_is_present = $conditions_container.find('.many-and-conditions-warning').length > 0; 
 
                if (number_of_conditions === 2 && too_many_and_conditions_warning_is_present) {
			return false;
		}

		if (number_of_conditions === 1) {
			return false;
		}

		$(this).parent().parent().remove();

		rename_conditions($conditions_container.children(), $group_index);

	});

	$("body").on("change", ".condition-input-modifier", function () {

                var $groups_container = $(this).closest('.vegacorp-or-groups');
                var conditions        = $groups_container.data('conditions');  
		var condition_select  = $(this);
		var condition_key     = condition_select.val();
		var $condition_row    = condition_select.closest("tr");
		$condition_row.find(".condition-operators-selection").empty().append(conditions[condition_key]["html_operators"]);
               
		var $input        = $(conditions[condition_key]["html_input"]);

                var $input_column = $condition_row.find(".input-column");
                $input_column.data('conditions_types_list_being_used_as_input', false);
		$input_column.empty().append($input);

		rename_groups($groups_container.children());
		init_selects_2($groups_container.find(".condition"));

	});

	$("body").on("change", ".condition-operators-selection", function () {

		change_input_place_holder();

	});

	function change_input_place_holder() {

		$(".input-column").each(function () {

                        var conditions     = $(this).closest('.vegacorp-or-groups').data('conditions');
                        var selected_type  = $(this).closest('tr').find('.condition-input-modifier').val();
			var $input_column  = $(this);
			               			
			var $condition_row = $input_column.closest("tr");

			var selected_operator = $condition_row.find(".condition-operators-selection").val();
                     
			if (selected_operator == "equal_to_field" || selected_operator == "not_equal_to_field") {                            
                     
                                if($input_column.data('conditions_types_list_being_used_as_input')){ return; }
                     
				var $fieldsSelect = $input_column.parent().find('.type-selection select').clone();
				
				$fieldsSelect.attr('class', 'vegacorp-fields-value');
                                $fieldsSelect.find('option').toArray().forEach( option => $(option).attr('selected', $(option).val() === $input_column.data('saved-value')) );
                       			                                     
				$input_column.empty();
                       
				$input_column.append($fieldsSelect);
                                
                                $input_column.data('conditions_types_list_being_used_as_input', true);
                 
                                rename_groups($(this).closest('.vegacorp-or-groups').children());
                                
			} else if ($input_column.data('conditions_types_list_being_used_as_input')) {
                                
				$input_column.empty();
			
                                $input_column.append(conditions[selected_type].html_input); 
                                
                                rename_groups($(this).closest('.vegacorp-or-groups').children());
                              
				if ($input_column.find('select.vegacorp-select-2').length) {                                        
					init_selects_2($input_column.parent());
				}
                                
                                $input_column.data('conditions_types_list_being_used_as_input', false);
                                
			}
                        
                        var $input = $input_column.children().first();
                     
                        if(conditions[selected_type].operators_data && conditions[selected_type].operators_data[selected_operator] && conditions[selected_type].operators_data[selected_operator].placeholder){
                            $input.attr('placeholder', conditions[selected_type].operators_data[selected_operator].placeholder);
                        }else{
                            if($input.attr('type')){
                                $input.attr('placeholder', '');
                            }                    
                        }
                       
		});

	}

	function rename_conditions($conditions, group_index) {
               
                var conditions_name = $conditions.closest('.vegacorp-or-groups').data('conditions_name');
            
		$conditions.each(function (i) {
                       
			var $current_conditon_row = $(this);
			$current_conditon_row.find(".condition-name").empty().append("condition " + (i + 1));

			var current_condition_name_and_id_default_text = conditions_name + "[" + group_index + "][conditions][" + i + "]";

			var type_selection_id_and_name = current_condition_name_and_id_default_text + "[type]";
			$current_conditon_row.find(".type-selection select").attr("name", type_selection_id_and_name);

			var operator_selection_id_and_name = current_condition_name_and_id_default_text + "[operator]";
			$current_conditon_row.find(".operator-selection select").attr("name", operator_selection_id_and_name);
			
                        var user_data_id_and_name = current_condition_name_and_id_default_text + "[data]";
                        var $condition_input = $current_conditon_row.find(".input-column :first-child").first();
                       
                        var array_selector = $condition_input.attr('multiple') ? '[]' : '';
			$condition_input.attr("name", user_data_id_and_name + array_selector);

		});
	}

	function rename_groups($groups) {

		$groups.each(function (i) {

			var $current_group = $(this);
			$current_group.find(".group-text").empty().append(vegacorp_conditions_obj.text.group_text + ' ' + (i + 1));
			rename_conditions($current_group.find(".conditions").children(), $current_group.index());

		});
	}

	function get_condition_select_2_ajax_object($condition_row) {

                var conditions = $condition_row.closest('.vegacorp-or-groups').data('conditions');
		var condition_key = $condition_row.find(".condition-input-modifier").val();
		var $condition_select = $condition_row.find(".vegacorp-select-2");

		if (!conditions[condition_key].request_options_with_ajax) {

			return {dropdownAutoWidth: true}
		}

		var select2_obj = {

			ajax: {
				url: vegacorp_conditions_obj.ajax.url,
				dataType: "json",
				method: "POST",
				delay: 1000,
				data: function (params) {
					return{
						q: params.term,
						condition_ajax_args: conditions[condition_key].condition_ajax_args,						
						ajax_request_nonce: vegacorp_conditions_obj.ajax.ajax_request_nonce,						
						action: conditions[condition_key].ajax_method
					}
				},
				processResults: function (response) {
					// Tranforms the top-level key of the response object from 'items' to 'results'                        					
					var resultsArray = [];

					response.data.posts.forEach(function (currentPost) {
						resultsArray.push({id: currentPost.value, text: currentPost.text});
					});

					return {results: resultsArray};

				}
			},
			minimumInputLength: 1,
			dropdownAutoWidth: true,
			height: '22px'

		}

		var select2_obj = jQuery.extend({}, select2_obj);
		return select2_obj;

	}

	function init_selects_2($conditions_rows) {

		$conditions_rows.each(function () {

                        var conditions   = $(this).closest('.vegacorp-or-groups').data('conditions');
			var $current_row = $(this);
			var $select      = $current_row.find(".input-column select");
			if (!$select.length) {
				return true;
			}
			var condition_key = $current_row.find(".condition-input-modifier").val();
			var condition     = conditions[condition_key];

			if (!condition.is_select_2 || $select.hasClass('vegacorp-fields-value')) {
				return true;
			}

			if (!$select.data("vegacorp-already-init")) {
				var select_2_ajax_obj = get_condition_select_2_ajax_object($current_row)
				$select.select2(select_2_ajax_obj);
				$select.data("vegacorp-already-init", "1");
			}

		});

	}

});
