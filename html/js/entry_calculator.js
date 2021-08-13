$("#entry_points input").keypress(function(key) {
	if ((key.charCode < 48 || key.charCode > 57) && key.charCode != 46) {
		return false;	
	}
	else{
		return true;
	}
});
$("#entry_points input").keyup(function() {
	//Inputs
	var current_price = parseFloat($("#current_price").val());
	var total_capital = parseFloat($("#total_capital").val());
	var entry_1_percent = parseFloat($("#entry_1_percent").val());
	var entry_2_percent = parseFloat($("#entry_2_percent").val());
	var entry_3_percent = parseFloat($("#entry_3_percent").val());

	var entry_1_capital = parseFloat($("#entry_1_capital").val());
	var entry_2_capital = parseFloat($("#entry_2_capital").val());
	var entry_3_capital = parseFloat($("#entry_3_capital").val());

	var entry_1_price = parseFloat($("#entry_1_price").val());
	var entry_2_price = parseFloat($("#entry_2_price").val());
	var entry_3_price = parseFloat($("#entry_3_price").val());

	var best_case = parseFloat($("#best_case").val());
	var worst_case = parseFloat($("#worst_case").val());

	//Calculate
	if($(this).attr("id").includes("percent")){
		while((entry_1_percent + entry_2_percent + entry_3_percent) > 100){
			$(this).val(parseFloat($(this).val())-1);
			entry_1_percent = parseFloat($("#entry_1_percent").val());
			entry_2_percent = parseFloat($("#entry_2_percent").val());
			entry_3_percent = parseFloat($("#entry_3_percent").val());
		}

		if($.isNumeric(total_capital)){
			$("#entry_1_capital").val(total_capital*(entry_1_percent/100));
			$("#entry_2_capital").val(total_capital*(entry_2_percent/100));
			$("#entry_3_capital").val(total_capital*(entry_3_percent/100));
		}
	}
	else if($(this).attr("id").includes("total_capital")){
		if($.isNumeric(total_capital)){
			$("#entry_1_capital").val(total_capital*(entry_1_percent/100));
			$("#entry_2_capital").val(total_capital*(entry_2_percent/100));
			$("#entry_3_capital").val(total_capital*(entry_3_percent/100));
		}
	}
	else if($(this).attr("id").includes("capital")){
		while((entry_1_capital + entry_2_capital + entry_3_capital) > total_capital){
			$(this).val(parseFloat($(this).val())-1);
			entry_1_capital = parseFloat($("#entry_1_capital").val());
			entry_2_capital = parseFloat($("#entry_2_capital").val());
			entry_3_capital = parseFloat($("#entry_3_capital").val());
		}

		if($.isNumeric(total_capital) && total_capital > 0){
			$("#entry_1_percent").val((entry_1_capital*100)/total_capital);
			$("#entry_2_percent").val((entry_2_capital*100)/total_capital);
			$("#entry_3_percent").val((entry_3_capital*100)/total_capital);
		}
	}

	if($.isNumeric(best_case) && $.isNumeric(worst_case)){
		if($.isNumeric(entry_1_price) && entry_1_price > 0){
			$("#profit_1").html((((best_case*100)/entry_1_price)-100).toFixed(2)+"%");
			$("#loss_1").html((((worst_case*100)/entry_1_price)-100).toFixed(2)+"%");
		}
		if($.isNumeric(entry_2_price) && entry_2_price > 0){
			$("#profit_2").html((((best_case*100)/entry_2_price)-100).toFixed(2)+"%");
			$("#loss_2").html((((worst_case*100)/entry_2_price)-100).toFixed(2)+"%");
		}
		if($.isNumeric(entry_3_price) && entry_3_price > 0){
			$("#profit_3").html((((best_case*100)/entry_3_price)-100).toFixed(2)+"%");
			$("#loss_3").html((((worst_case*100)/entry_3_price)-100).toFixed(2)+"%");
		}
	}

	if($.isNumeric(parseFloat($("#loss_1").html())) && $.isNumeric(parseFloat($("#profit_1").html())) && parseFloat($("#profit_1").html()) > 0){
		$("#risk_reward_1").html(Math.abs(parseFloat($("#loss_1").html())/parseFloat($("#profit_1").html())).toFixed(2)+"/1");
	}
	if($.isNumeric(parseFloat($("#loss_2").html())) && $.isNumeric(parseFloat($("#profit_2").html())) && parseFloat($("#profit_2").html()) > 0){
		$("#risk_reward_2").html(Math.abs(parseFloat($("#loss_2").html())/parseFloat($("#profit_2").html())).toFixed(2)+"/1");
	}
	if($.isNumeric(parseFloat($("#loss_3").html())) && $.isNumeric(parseFloat($("#profit_3").html())) && parseFloat($("#profit_3").html()) > 0){
		$("#risk_reward_3").html(Math.abs(parseFloat($("#loss_3").html())/parseFloat($("#profit_3").html())).toFixed(2)+"/1");
	}

	if($.isNumeric(entry_1_capital) && $.isNumeric(entry_2_capital) && $.isNumeric(entry_3_capital)){
		$("#best_return_capital").val("$"+((entry_1_capital*(parseFloat($("#profit_1").html())/100))+(entry_2_capital*(parseFloat($("#profit_2").html())/100))+(entry_3_capital*(parseFloat($("#profit_3").html())/100))).toFixed(2));
		if($.isNumeric(total_capital) && total_capital > 0){
			$("#best_return_percent").val((parseFloat($("#best_return_capital").val().substring(1))*100/total_capital).toFixed(2)+"%");
		}
	}

	if($.isNumeric(entry_1_capital) && $.isNumeric(entry_2_capital) && $.isNumeric(entry_3_capital)){
		$("#worst_return_capital").val("$"+((entry_1_capital*(parseFloat($("#loss_1").html())/100))+(entry_2_capital*(parseFloat($("#loss_2").html())/100))+(entry_3_capital*(parseFloat($("#loss_3").html())/100))).toFixed(2));
		if($.isNumeric(total_capital) && total_capital > 0){
			$("#worst_return_percent").val((parseFloat($("#worst_return_capital").val().substring(1))*100/total_capital).toFixed(2)+"%");
		}
	}

	var risk_reward_1 = parseFloat($("#risk_reward_1").html().split("/")[0]);
	var risk_reward_2 = parseFloat($("#risk_reward_2").html().split("/")[0]);
	var risk_reward_3 = parseFloat($("#risk_reward_3").html().split("/")[0]);

	if($.isNumeric(risk_reward_1) && $.isNumeric(risk_reward_2) && $.isNumeric(risk_reward_3)){
		$("#risk_reward_total").val(((risk_reward_1*(entry_1_percent/100))+(risk_reward_2*(entry_2_percent/100))+(risk_reward_3*(entry_3_percent/100))).toFixed(2)+"/1");
	}
});