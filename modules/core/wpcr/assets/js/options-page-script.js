
jQuery(document).ready(function ($) {

	$(".delete-conditions").hover(function () {
		$(this).css("color", "#ff0000");
	});

	$(".delete-conditions").mouseout(function () {
		$(this).css("color", "#c70000");
	});

	$(".delete-conditions").click(function () {

		//Getting the id of the post to delete
		var clicked_id = $(this).attr("id");

		var clicked_id_splited = clicked_id.split("-");

		var post_id = clicked_id_splited[1];

		//If the post is being deleted we do nothing
		if ($(this).hasClass("is-being-deleted")) {
			return;
		}

		//If above check fails delete begins
		$(this).addClass("is-being-deleted");

		$.ajax({
			url: vegacorp_conditions_options_page_obj.ajax.url,
			method: "post",
			dataType: "json",
			data: {
				action: $(this).data('delete_action'),
				post_id: post_id,
				ajax_delete_conditions_nonce: vegacorp_conditions_options_page_obj.ajax.ajax_delete_conditions_nonce
			},
			success: function (response) {
				if (response.data.condition_deleted == 1) {
					//If condition deleted it is removed from the html
					$("#condition-" + post_id).remove();
				} else {
					//Else if deleted fails is not being deleted anymore
					if ($(this).hasClass("is-being-deleted")) {
						$(this).removeClass("is-being-deleted");
					}
				}
				alert(response.data.message);
			},
			error: function (a, b, c) {
				
				console.log(a,b,c);
                                
				alert(vegacorp_conditions_options_page_obj.text.delete_condition_error_message);
			}
		});


	});


});