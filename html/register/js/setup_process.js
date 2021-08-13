class SetupProcess {
	constructor(args) {
		//VERIFY USER PARAMETERS
		if(args.attach != null && $(args.attach).length > 0 && args.title != null && args.questions != null && args.questions.length > 0 && this.verifyQuestions(args.questions)) {
			//CLASS VARIABLES
			this.questions = args.questions;
			this.setup_instance = "s_p_"+Math.floor(Math.random()*10000);
			this.current_sub_question = -1;
			this.current_question = 0;
			this.question_counter = 1;
			this.process_running = false;
			this.questions_length = this.questions.length;
			this.sub_question_index = [];
			this.check_resume = false;

			//RESUME SETUP
			var _self = this;
			$.post(this.questions[0].ajax, {get_status: true}, function(res) {
				var parse_correct = false;
				try {
					var status = $.parseJSON(res);

					if(status['sub_question_index'] != null) {
						try {
							status['sub_question_index'] = $.parseJSON(status['sub_question_index']);
							parse_correct = true;
						} catch(err2) {}
					}
				} catch(err) {}

				if(parse_correct && status['current_question'] != null && status['current_sub_question'] != null && status['sub_question_index'] != null && status['question_counter'] != null) {
					_self.current_question = status['current_question'];
					_self.sub_question_index = status['sub_question_index'];
					_self.question_counter = status['question_counter'];
					_self.current_sub_question = status['current_sub_question'];
				}

				_self.check_resume = true;
			});

			//INITIALIZE TEMPLATE
			$(args.attach).append('<div class="setup_process" id="'+this.setup_instance+'"><img src="/register/media/ajax_loader.svg"><div><div class="setup_process_header"><a href="/index.php"><div class="logo"><img src="/register/media/logo.svg"><span>Inveltio</span></div></a><div></div></div><div class="setup_process_body"><h1></h1></div><div class="setup_process_footer"><div class="go_back"><div><img src="/register/media/back.svg"><span>Previous</span></div></div><div class="footer_text">'+args.title+' - Question #<span></span></div><div class="go_forward"><div><span>Next</span><img src="/register/media/right-arrow.svg"></div></div></div></div></div>');
			this.toggleLoader();
			$("#"+this.setup_instance).children("div").css("opacity", "0");

			//PRINT FIRST QUESTION
			var check_resume_interval = setInterval(function() {
				if(_self.check_resume) {
					_self.toggleLoader();
					_self.printCurrentQuestion();
					clearInterval(check_resume_interval);
				}
			}, 100);
		}
	}

	initializeEventHandlers() {
		//REMOVE DUPLICATE CLICK EVENTS
		$("#"+this.setup_instance+" .radio").unbind("click");

		var _self = this;

		$("#"+this.setup_instance+" .radio").click(function() {
			$(this).parent().find(".radio").each(function() {
				$(this).css("opacity", "1");
				$(this).css("color", "#444444");
				$(this).css("background", "#F4F4F4");
				$(this).css("box-shadow", "1px 3px 0 rgba(49,60,71,.07)");
				$(this).children("div").css("background", "#f4f4f4");
				$(this).children("div").find("img").removeClass("radio_selected");
				$(this).children("div").find(".unchecked").addClass("radio_selected");
			});
			$(this).css("color", "white");
			$(this).css("background", "#0a0a86");
			$(this).css("box-shadow", "none");
			$(this).children("div").css("background", "#f4f4f4");
			$(this).children("div").find("img").removeClass("radio_selected");
			$(this).children("div").find(".checked").addClass("radio_selected");

			$(this).parent().find(".radio").each(function() {
				if($(this).find(".unchecked").hasClass("radio_selected")) {
					$(this).css("opacity", "0.75");
					$(this).css("box-shadow", "none");
				}
			});
		});
	}

	verifyQuestions(questions) {
		for (var i = 0; i < questions.length; i++) {
			if(questions[i].title == null || questions[i].ajax == null || ((questions[i].options == null || questions[i].options.length == 0) && (questions[i].inputs == null || questions[i].inputs.length == 0))) {
				return false;
			}
			/*else {
				for (var j = 0; j < questions[i].options.length; j++) {
					if(questions[i].options[j].text == null) {
						return false;
					}
				}
			}*/
		}

		return true;
	}

	printCurrentQuestion() {
		//REMOVE ERRORS
		$("#"+this.setup_instance).find(".setup_process_error").remove();

		//PRINT QUESTION NUMBER
		$("#"+this.setup_instance).find(".footer_text span").html(this.question_counter);

		//ENABLE - DISABLE NEXT AND BACK BUTTONS
		$("#"+this.setup_instance).find(".go_back").removeClass("disabled");
		$("#"+this.setup_instance).find(".go_forward").removeClass("disabled");

		if(this.question_counter == 1) {
			$("#"+this.setup_instance).find(".go_back").addClass("disabled");
		}

		//REMOVE PREVIOUS QUESTION
		$("#"+this.setup_instance).find(".setup_process_body").html("<h1></h1>");

		//CHECK IF IT'S A REGULAR QUESTION OR A SUB-QUESTION
		this.question = this.questions[this.current_question];

		if(this.current_sub_question != -1) {
			this.question = this.question.subquestions[this.sub_question_index.slice(-1)[0]][this.current_sub_question];
		}

		//PRINT QUESTION TITLE
		$("#"+this.setup_instance).find(".setup_process_header > div").html(this.question.header);
		$("#"+this.setup_instance).find(".setup_process_body h1").html(this.question.title);

		if(this.question.img != null) {
			$("#"+this.setup_instance).find(".setup_process_body h1").prepend("<img height='24' width='24' src='"+this.question.img+"'>");
		}

		//PRINT QUESTION SUBTITLE
		if(this.question.subtitle != null) {
			$("#"+this.setup_instance).find(".setup_process_body h1").after("<p class='setup_process_subtitle'>"+this.question.subtitle+"</p>");
		}

		//PRINT QUESTION OPTIONS && INPUTS
		if(this.question.options != null) {
			for (var i = 0; i < this.question.options.length; i++) {
				if(this.question.options[i].img != null) {
					$("#"+this.setup_instance).find(".setup_process_body").append('<div class="radio"><div><img height="20" width="20" class="unchecked radio_selected" src="/register/media/empty-square.svg"><img height="20" width="20" class="checked" src="/register/media/checked.svg"></div><img height="24" width="24" src="'+this.question.options[i].img+'"><span>'+this.question.options[i].text+'</span></div>'+((this.question.style != null && this.question.style == "vertical") ? '<div></div>' : ''));
				}
				else {
					$("#"+this.setup_instance).find(".setup_process_body").append('<div class="radio"><div><img height="20" width="20" class="unchecked radio_selected" src="/register/media/empty-square.svg"><img height="20" width="20" class="checked" src="/register/media/checked.svg"></div><span style="margin-left: 1rem">'+this.question.options[i].text+'</span></div>'+((this.question.style != null && this.question.style == "vertical") ? '<div></div>' : ''));
				}
			}

			var largest_option = 0;

			for (var i = 0; i < $("#"+this.setup_instance).find(".radio").length; i++) {

				if($("#"+this.setup_instance).find(".radio:eq("+i+")").width() > largest_option) {
					largest_option = $("#"+this.setup_instance).find(".radio:eq("+i+")").width();
				}
				if($("#"+this.setup_instance).find(".radio:eq("+i+")").height() >= 55) {
					var current_font_size = $("#"+this.setup_instance).find(".radio:eq("+i+") > span").css("font-size");
					$("#"+this.setup_instance).find(".radio > span").css("margin-left", "0.25rem");
					$("#"+this.setup_instance).find(".radio > span").css("margin-right", "0.25rem");
					$("#"+this.setup_instance).find(".radio > img").remove();

					for (var j = 0.1; j < 10; j+=0.1) {
						var new_font_size = parseFloat(current_font_size)-j;
						$("#"+this.setup_instance).find(".radio > span").css("font-size", new_font_size+"px");
			
						if($("#"+this.setup_instance).find(".radio:eq("+i+")").height() < 55) {
							break;
						}
					}
				}
			}

			$("#"+this.setup_instance).find(".radio").css("width", largest_option+"px");
		}
		if(this.question.inputs != null) {
			for (var i = 0; i < this.question.inputs.length; i++) {
				$("#"+this.setup_instance).find(".setup_process_body").append('<div class="input"><label>'+this.question.inputs[i].label+'</label><input type="'+this.question.inputs[i].type+'" name="'+this.question.inputs[i].name+'"></div>');
			}
		}
		//ACTIVATE SUBMIT HANDLER
		this.submitHandler();

		//INITIALIZE HANDLERS
		this.initializeEventHandlers();

		//SCROLL TO TOP
		$("body, html").animate({
			scrollTop: 0,
		}, 0);
	}

	submitHandler() {
		//REMOVE DUPLICATE CLICK EVENTS
		$("#"+this.setup_instance).find(".go_forward > div").unbind("click");
		$("#"+this.setup_instance).find(".go_back> div").unbind("click");

		//NEXT QUESTION
		var _self = this;
		$("#"+this.setup_instance).find(".go_forward > div").click(function() {
			if(!_self.process_running) {
				_self.process_running = true;
				if(_self.questions_length > _self.current_question && (_self.checkSelectedOption() != -1 || _self.getInputs())) {
					//TOGGLE LOADER
					_self.toggleLoader();
					$.post(_self.question.ajax, { question: _self.question.title, answer: (_self.checkSelectedOption() == -1 ? null : _self.question.options[_self.checkSelectedOption()].text), inputs: _self.getInputs()}, function(res) {
						if(res.length == 0) {
							//CHECK IF QUESTION HAS SUB-QUESTIONS
							if(_self.questions[_self.current_question].subquestions != null) {
								//INITIALIZE SUB-QUESTIONS
								if(_self.current_sub_question == -1) {
									_self.sub_question_index.push(_self.checkSelectedOption());
									_self.current_sub_question = 0;
								}
								//LAST SUB-QUESTION
								else if(_self.questions[_self.current_question].subquestions[_self.sub_question_index.slice(-1)[0]].length == _self.current_sub_question+1) {
									_self.current_sub_question = -1;
									_self.current_question++;
								}
								//INCREMENT SUB-QUESTION
								else {
									_self.current_sub_question++;
								}
							}
							else {
								_self.current_question++;
							}
							
							if(_self.questions_length == _self.current_question && _self.current_sub_question == -1) {
								window.location.reload();
							}
							else {
								_self.question_counter++;
								_self.printCurrentQuestion();
								$.post(_self.question.ajax, { status: {'current_question': _self.current_question, 'current_sub_question': _self.current_sub_question, 'question_counter': _self.question_counter, 'sub_question_index': JSON.stringify(_self.sub_question_index)} }, function(res2) {});
							}
						}
						else {
							_self.errorMessage(res);
						}
						_self.process_running = false;
						_self.toggleLoader();
					});
				}
			}
		});

		//PREVIOUS QUESTION
		$("#"+this.setup_instance).find(".go_back > div").click(function() {
			if(!_self.process_running) {
				_self.process_running = true;
				if(_self.current_question > 0 || _self.current_sub_question >= 0) {
					//TOGGLE LOADER
					_self.toggleLoader();

					//PREVIOUS QUESTION (BEFORE SUB-QUESTION)
					if(_self.current_sub_question == 0) {
						_self.current_sub_question = -1;
						_self.sub_question_index.splice(-1);
					}
					//PREVIOUS SUB-QUESTION
					else if(_self.current_sub_question > 0) {
						_self.current_sub_question--;
					}
					//PREVIOUS QUESTION (BEFORE QUESTION)
					else if(_self.current_question > 0) {
						_self.current_question--;
						if(_self.questions[_self.current_question].subquestions != null) {
							_self.current_sub_question = _self.questions[_self.current_question].subquestions.slice(-1)[0].length-1; 
						}
					}
					_self.question_counter--;
					_self.printCurrentQuestion();

					_self.process_running = false;
					_self.toggleLoader();
				}
				else {
					console.log("First Question");
					_self.process_running = false;
				}
			}
		});
	}

	errorMessage(message) {
		//REMOVE ERRORS
		$("#"+this.setup_instance).find(".setup_process_error").remove();

		$("#"+this.setup_instance).find(".setup_process_body:eq(0)").after("<div class='setup_process_error'><img src='/register/media/round-error-symbol.svg'><span>"+message+"</span></div>");
	}

	checkSelectedOption() {
		for (var i = 0; i < $("#"+this.setup_instance).find(".radio").length; i++) {
			if($("#"+this.setup_instance).find(".radio:eq("+i+")").find(".checked").hasClass("radio_selected")) {
				return i;
			}
		}
		return -1;
	}

	getInputs() {
		var inputs = {};

		if(this.question.inputs != null) {
			for (var i = 0; i < $("#"+this.setup_instance).find(".input").length; i++) {
				/*if($("#"+this.setup_instance).find(".input:eq("+i+") input").val().length == 0) {
					return false;
				}
				else {*/
					inputs[$("#"+this.setup_instance).find(".input:eq("+i+") input").attr("name")] = $("#"+this.setup_instance).find(".input:eq("+i+") input").val(); 
				/*}*/
			}
		}

		return JSON.stringify(inputs);
	}

	toggleLoader() {
		if($("#"+this.setup_instance).children("img").css("display") == "none") {
			var instance_width = $("#"+this.setup_instance).width();
			var instance_height = $("#"+this.setup_instance).height();
			var loader_width = $("#"+this.setup_instance+" > img:eq(0)").width();
			var loader_height = $("#"+this.setup_instance+" > img:eq(0)").height();

			$("#"+this.setup_instance).children("img").css("left", ((instance_width/2)-(loader_width/2))+"px");
			$("#"+this.setup_instance).children("img").css("top", ((instance_height/2)-(loader_height/2))+"px");
			$("#"+this.setup_instance).children("div").css("opacity", "0.5");
			$("#"+this.setup_instance).children("img").css("display", "inline-block");
		}
		else {
			$("#"+this.setup_instance).children("div").css("opacity", "1");
			$("#"+this.setup_instance).children("img").css("display", "none");			
		}
	}
}

var account_setup = new SetupProcess({
	attach: "body",
	title: "Account Setup",
	questions: [
		{
			title: "Choose your account type",
			header: "Account Type",
			options: [
				{ 
					text: "Personal",
					img: "/register/media/teamwork.svg"
				},
				{ 
					text: "Corporate",
					img: "/register/media/enterprise.svg"
				}
			], 
			style: "horizontal",
			ajax: "/register/php/dependencies/ajax.php",
			subquestions: [
				[{
					header: "Net Worth",
					title: "What is your net worth?",
					options: [
						{ 
							text: "$10,000 - $20,000"
						}, 
						{ 
							text: "$20,000 - $50,000"
						},
						{ 
							text: "$50,000 - $100,000" 
						},
						{ 
							text: "$100,000 - $200,000"
						},
						{ 
							text: "More than $200,000" 
						}
					], 
					style: "vertical",
					ajax: "/register/php/dependencies/ajax.php"
				},
				{
					header: "Annual Income",
					title: "What is your annual income?",
					img: "/register/media/hand.svg",
					options: [
						{ 
							text: "Less than $10,000"
						}, 
						{ 
							text: "$10,000 - $30,000"
						},
						{ 
							text: "$30,000 - $60,000" 
						},
						{ 
							text: "$60,000 - $100,000"
						},
						{ 
							text: "$100,000 - $150,000" 
						},
						{ 
							text: "More than $150,000" 
						}
					], 
					style: "vertical",
					ajax: "/register/php/dependencies/ajax.php"
				},
				{
					header: "SSN",
					title: "Social Security Number",
					inputs: [
						{ 
							label: "Enter your SSN",
							name: "ssn",
							placeholder: "",
							type: "text"
						}
					], 
					style: "horizontal",
					ajax: "/register/php/dependencies/ajax.php"
				}],
				[{
					header: "Net Worth",
					title: "What is the net worth of your business?",
					options: [
						{ 
							text: "$100,000 - $200,000"
						}, 
						{ 
							text: "$200,000 - $500,000"
						},
						{ 
							text: "$500,000 - $1,000,000" 
						},
						{ 
							text: "$1,000,000 - $2,000,000"
						},
						{ 
							text: "More than $2,000,000" 
						}
					], 
					style: "vertical",
					ajax: "/register/php/dependencies/ajax.php"
				},
				{
					header: "Annual Income",
					title: "What is the annual income of your business?",
					img: "/register/media/hand.svg",
					options: [
						{ 
							text: "Less than $100,000"
						}, 
						{ 
							text: "$100,000 - $300,000"
						},
						{ 
							text: "$300,000 - $600,000" 
						},
						{ 
							text: "$600,000 - $1,000,000"
						},
						{ 
							text: "$1,000,000 - $1,500,000" 
						},
						{ 
							text: "More than $1,500,000" 
						}
					], 
					style: "vertical",
					ajax: "/register/php/dependencies/ajax.php"
				},
				{
					header: "EIN",
					title: "Employer Identification Number",
					inputs: [
						{ 
							label: "Enter the EIN of your business",
							name: "ein",
							placeholder: "",
							type: "text"
						}
					], 
					style: "horizontal",
					ajax: "/register/php/dependencies/ajax.php"
				}]
			]
		},
		{
			title: "Investing Experience (Equites)",
			header: "Investing Experience",
			options: [
				{ 
					text: "Less than 1 year"
				},
				{ 
					text: "1 - 3 years"
				},
				{ 
					text: "More than 3 years"
				}
			], 
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			title: "Investing Experience (Bonds)",
			header: "Investing Experience",
			options: [
				{ 
					text: "Less than 1 year"
				},
				{ 
					text: "1 - 3 years"
				},
				{ 
					text: "More than 3 years"
				}
			], 
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			title: "Investing Experience (Forex)",
			header: "Investing Experience",
			options: [
				{ 
					text: "Less than 1 year"
				},
				{ 
					text: "1 - 3 years"
				},
				{ 
					text: "More than 3 years"
				}
			], 
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			title: "Investing Experience (Others)",
			header: "Investing Experience",
			options: [
				{ 
					text: "Less than 1 year"
				},
				{ 
					text: "1 - 3 years"
				},
				{ 
					text: "More than 3 years"
				}
			], 
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			title: "Enter your bank account information",
			subtitle: "Optional. Can be left blank and be completed later.",
			header: "Bank Account",
			inputs: [
				{ 
					label: "Bank name",
					name: "bank_name",
					type: "text"
				},
				{ 
					label: "Account number",
					name: "account_number",
					type: "text"
				},
				{ 
					label: "Routing number",
					name: "routing_number",
					type: "text"
				},
				{ 
					label: "Billing address",
					name: "billing_address",
					type: "text"
				}
			], 
			style: "vertical",
			required: false,
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			title: "What are your investment plans?",
			header: "Investment Motivation",
			options: [
				{ 
					text: "Long term growth (1 year or more)"
				},
				{ 
					text: "Short term growth (Less than 1 year)"
				},
				{ 
					text: "College"
				},
				{ 
					text: "Savings"
				}
			],
			inputs: [
				{
					label: "Other",
					name: "purpose_other",
					type: "text",
					required: false
				}
			],
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			title: "Please select how much risk you're willing to take",
			header: "Risk Profile",
			options: [
				{ 
					text: "Low volatility"
				},
				{ 
					text: "Medium volatility"
				},
				{ 
					text: "High volatility"
				}
			], 
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		},
		{
			header: "Asset Allocation",
			title: "Where would you like to allocate your assets?",
			options: [
				{ 
					text: "International and domestic (US) markets",
					img: "/register/media/internet.svg"
				},
				{ 
					text: "US market only",
					img: "/register/media/united-states.svg"
				}
			], 
			style: "vertical",
			ajax: "/register/php/dependencies/ajax.php"
		}
	] 
});

