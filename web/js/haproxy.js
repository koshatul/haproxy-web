$(document).ready(function() {
	console.log("Init Slider");
	$("tr.servicemaster").click(function(event){
		console.log("click(): servicemaster");
		event.stopPropagation();
		var $target = $(event.target);
		if ($target[0].tagName == "INPUT") {
			console.log("click(): servicemaster: input type detected, not sliding");
			return;
		}
		var $targetservice = $(this).attr('targetservice');
		$("tr[id*=\""+$targetservice+"\"]").slideToggle();
	});
	$("input.smaster").click(function(event){
		console.log("click(): input.smaster");
		console.log("click(): input.smaster: targetservice="+$(this).attr('targetservice'))
		$("input[id*=\""+$(this).attr('targetservice')+"\"]").prop('checked', $(this).prop('checked'));
	});
	$.each($("tr.servicemaster"), function( index, value ) {
		$("tr[id*=\""+$(value).attr('targetservice')+"\"]").slideUp();
	});
});