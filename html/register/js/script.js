console.log("cargado");
//Mobile touch support
$(document).on('touchstart', function() {
    documentClick = true;
});
$(document).on('touchmove', function() {
    documentClick = false;
});

//Dropdowns
new jBox('Tooltip', {
    attach: '#about_dropdown_button',
    content: $('#about_dropdown'),
    closeOnMouseleave: true
});
new jBox('Tooltip', {
    attach: '#services_dropdown_button',
    content: $('#services_dropdown'),
    closeOnMouseleave: true
});

//Modals
var register_modal = new jBox('Modal', {
    attach: '#sign_up',
    content: $('#register_modal'),
    isolateScroll: true,
    closeOnClick: false,
    blockScroll: true
});
var register_success_modal = new jBox('Modal', {
    content: $('#register_success_modal'),
    isolateScroll: true,
    closeOnClick: false,
    blockScroll: true
});
var activation_required_modal = new jBox('Modal', {
    content: $('#activation_required_modal'),
    isolateScroll: true,
    closeOnClick: false,
    blockScroll: true
});
var login_modal = new jBox('Modal', {
    attach: '#log_in',
    content: $('#login_modal'),
    isolateScroll: true,
    closeOnClick: false,
    blockScroll: true
});
var mobile_menu_modal = new jBox('Modal', {
    attach: '#expand_menu',
    content: $('#mobile_menu_modal'),
    position: {
        x: "left",
        y: "top"
    },
    target: window,
    minHeight: $(window).height(),
    minWidth: 280,
    maxWidth: 280,
    animation: {
        open: "move:right",
        close: "move:left"
    },
    addClass: "style_jbox",
});

//Mobile menu
$(".dropdown_mobile").on('click touchend', function() {
    if (event.type == "click") {
        documentClick = true;
    }
    if (documentClick) {
        if($(this).next("li").hasClass("open")) {
            $(this).find("img").css("transform", "rotate(0deg)");
            $(this).next("li").slideUp(250, function() {
                $(this).removeClass("open");
            });
        }
        else {
            $(this).find("img").css("transform", "rotate(180deg)");
            $(this).next("li").slideDown(250, function() {
                $(this).addClass("open");
            });
        }
    }
});
$("#log_in_mobile").on('click', function() {
        mobile_menu_modal.close();

        login_modal = new jBox('Modal', {
            content: $('#login_modal'),
            isolateScroll: true,
            closeOnClick: false,
            blockScroll: true
        });

        login_modal.open();
    
});
$("#sign_up_mobile").on('click', function() {
        mobile_menu_modal.close();

        var sign_up_mobile_modal = new jBox('Modal', {
            content: $('#register_modal'),
            isolateScroll: true,
            closeOnClick: false,
            blockScroll: true
        });

        sign_up_mobile_modal.open();
    
});

//Loader buttons
$(".btn_loader").each(function() {
    $(this).after('<button class="btn_loader_2"><img src="/register/media/ajax_loader.svg"></button>');
});
$(".btn_loader").on('click touchend', function() {
    $(this).next(".btn_loader_2").css("width", $(this).outerWidth() + "px");
    $(this).next(".btn_loader_2").css("height", $(this).outerHeight() + "px");
    $(this).next(".btn_loader_2").css("display", "block");
    $(this).hide();
});

function hideLoaderButton(element) {
    $(element).show();
    $(element).next(".btn_loader_2").hide();
}

//Register
$("#register_submit").on('click touchend', function() {
    $("#register_modal .form_verification > span").remove();
    $("#register_modal input").css("border-bottom", "0.15rem solid #a7a7a7");
    $("#register_modal select").css("border-bottom", "0.15rem solid #a7a7a7");
    var user_input = [
    $("input[name='register_email']").val(),
    $("input[name='register_cemail']").val(),
    $("input[name='register_password']").val(),
    $("input[name='register_cpassword']").val(),
    $("input[name='register_name']").val(),
    $("input[name='register_lname']").val(),
    $("input[name='register_dob']").val(),
    $("select[name='register_sex']").val(),
    $("select[name='register_country']").val(),
    $("select[name='register_state']").val(),
    $("input[name='register_address']").val(),
    $("input[name='register_phone']").val(),
    $("input[name='register_zipcode']").val(),
    $("select[name='register_security_1_question']").val(),
    $("input[name='register_security_1_answer']").val(),
    $("select[name='register_security_2_question']").val(),
    $("input[name='register_security_2_answer']").val(),
    $("select[name='register_find']").val(),
    $("input[name='register_terms']").is(":checked"),
    $("input[name='register_newsletter']").is(":checked"),
    $("[name='g-recaptcha-response']").val()
    ];

    var register = JSON.stringify(user_input);

    $.post("/register/php/dependencies/ajax.php", {
        register: register
    }, function(res) {
        console.log(res);
        if (res != null && res.length > 0) {
            try {
                var res = $.parseJSON(res);
            } catch (err) {
                console.log(err);
            }
            if (res != null && res.length == user_input.length) {
                for (var i = 0; i < $("#register_modal .form_verification").length; i++) {
                    if (res[i] != true) {
                        if ($("#register_modal .form_verification:eq(" + i + ")").find(".checkbox_container").length > 0) {
                            $("#register_modal .form_verification:eq(" + i + ")").append('<span style="bottom: 0.15rem; left: 1.85rem"><img src="/register/media/round-error-symbol.svg">' + res[i] + '</span>');
                        } else {
                            $("#register_modal .form_verification:eq(" + i + ")").append('<span><img src="/register/media/round-error-symbol.svg">' + res[i] + '</span>');
                            $("#register_modal .form_verification:eq(" + i + ")").find("input").css('border-bottom', '0.15rem solid #ef5a5a');
                            $("#register_modal .form_verification:eq(" + i + ")").find("select").css('border-bottom', '0.15rem solid #ef5a5a');
                        }
                    }
                }
                $("#register_modal .form_verification span").animate({
                    opacity: "1"
                }, 250);
            }
        } else {
                //Success
                register_modal.close();
                register_success_modal.open();
            }

            hideLoaderButton($("#register_submit"));
        });
});

//Login
$("#login_submit").on('click touchend', function() {
    $("#login_error").hide();
    $("#login_modal input").css("border-bottom", "0.15rem solid #a7a7a7");
    var user_input = [
    $("input[name='login_email']").val(),
    $("input[name='login_password']").val(),
    $("input[name='login_cookie']").is(":checked")
    ];

    var login = JSON.stringify(user_input);

    $.post("/register/php/dependencies/ajax.php", {
        login: login
    }, function(res) {
        if(res != null && res.length > 0) {
            if(res == "Activation required.") {
                login_modal.close();
                activation_required_modal.open();
            }
            else {
                $("#login_error").fadeIn(250);
                $("#login_modal input").css("border-bottom", "0.15rem solid #ef5a5a");
            }
        }
        else {
                //success
                window.location = "/register/account_setup.php";
            }
            hideLoaderButton($("#login_submit"));
        });
});

//PRESSING ENTER
$(document).on('keypress',function(e) {
    if(e.which == 13) {
        if($("#login_modal").is(":visible")) {
            $("#login_submit").trigger("click");
        }
        else if($("#register_modal").is(":visible")) {
            $("#register_submit").trigger("click");
        }
    }
});