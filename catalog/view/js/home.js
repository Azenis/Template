$(document).ready(function() {
	$("#gem").click(function() {
		var pids = [];
		var types = [];
		$("input:checked").each(function() {
			pids.push($(this).attr("data--pid"));
			types.push($(this).val());
		});
				
		$.ajax({
			url: "lib/ajax/ajax_gem_rejse.php",
			type: "POST",
			dataType: "json",
			data: {
				pris: $("#pris").val(),
				pids:pids,
				types:types
			},
			success: function(res) {
				$("#gem").fadeOut();
				$(".response").html(res.msg);
				setTimeout(function(){
					window.location = window.location;
				}, 2000);
			}
		});
	});
});
