function twwgetCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
}
function setCountdown($ele, countDownDate){
	var x = setInterval(function() {
		var now = new Date().getTime();
		var distance = countDownDate - now;
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);
		$ele.html('We will hold your webinar ('+$ele.data('title')+') seats for <ins>'+minutes+' minutes and '+seconds+' seconds</ins> only.');
		if (distance < 0) {
			$ele.html('Clearing your seats.');
			clearInterval(x);
			location.reload();
		}
	},1000);
}
jQuery(document).ready( function($){ 
	console.log(twwtfa);
	$(".sss").click(function(e) {
        
    
		$.ajax({
				dataType: "json",
				method: "POST",
				url: twwtfa.ajaxurl,
				data: {
				  dataType: "json",
				  'action': 'twwt_woo_ajax_function', //the function in php functions to call
				  'type': 'otp-send',
				  'nonce': twwtfa.nonce
				},
				success: function(data){
				},
				error: function(err){
					console.log(err);
					_submitbutton.html(_submitbutton_prv);
					_submitbutton.removeAttr('disabled');
				},
				cache:false,
		});
	});
	
	if($("body").find(".twwt_woo_cc_notice").length>0){
		
		var $nel = $("body").find(".twwt_woo_cc_notice");
		$nel.each(function(index, element) {
			var $this = $(this);
            var _seat_var = $this.data("id");
			
			var unixTimestamp = twwgetCookie('seat_selected_'+_seat_var);
			
			dateObj = new Date(unixTimestamp * 1000);
			countDownDate = dateObj.getTime();
			console.log(unixTimestamp);
			console.log(countDownDate);
			/*Set Countdown*/
			setCountdown($this, countDownDate);
			//time = utcString.slice(-11, -4);
			
        });
	}
	
	//## Select Seat Type and Qty ##
	$(".ticket-box").change(function(e) {
		var $ele = $(this);
		var _ckdln = $ele.closest('ul.my-tickets').find('input:checkbox:checked').length;
		if(_ckdln>0){
			var _vid = $ele.data("vid");
			var seat = $ele.val();
			$ele.addClass('wait');
			$.get('?availabilitycheck=true&variationid='+_vid+'&seat='+seat, function(cdata){
				$ele.removeClass('wait');
				if(cdata==1){
					$('ul.my-tickets').addClass('de-active');
					$ele.closest('ul.my-tickets').removeClass('de-active');
					$(".proceed-cart").show();
					$(".proceed-cart-info").hide();
					var seat_txt = "seats";
					if(_ckdln==1){
						seat_txt = "seat";
					}
					$(".prc-qty").html("You have selected "+_ckdln+" "+seat_txt+".");
					as_reset($ele)
				}
				else{
					$ele.prop('checked', false);
					$ele.prop('disabled', true);
					$ele.addClass('rbtn-tt-temp');
					$(".availabilitycheck-msg").html(cdata).show();
					setTimeout(function(){
						$(".availabilitycheck-msg").html('').hide();
					},3000);
					as_reset($ele)
				}
			});
		}
		else{
			$('ul.my-tickets').removeClass('de-active');
			$(".proceed-cart").hide();
			$(".proceed-cart-info").show();
			as_reset($ele)
		}
		
    });
	$(".prc-btn").click(function(e) {
        $(".single_add_to_cart_button").trigger('click');
    });
	if($(".tab-participant").length>0){
		var _id = $(".tab-participant").data("id");
		getParticipant(_id);
		
	}
});

function as_reset($ele){
	var _ckdln = $ele.closest('ul.my-tickets').find('input:checkbox:checked').length;
	setTimeout(function(){
		jQuery('body').find("ul.my-tickets.de-active .ticket-box").prop('checked', false);
	},10);
	var _atr = $ele.data("value");
	jQuery("body").find("#pa_seat").val(_atr).change();
	jQuery("body").find("input[name=quantity]").val(_ckdln).change();
}

function getParticipant(_id){
	jQuery.get('?participant=true&productid='+_id, function(data){
		jQuery(".tab-participant").html(data);
		setTimeout(function(){
			getParticipant(_id);
		},60000);
	});
}

/*ONLY FOR WINNER SELECTION*/
jQuery(document).ready( function($){ 
	$("input[name=rbtnseats]").click(function(e) {
		var _id = $(this).data('id');
        $("#tw_orderid").val(_id);
    });
	$("#tw_mywinnerform").submit(function(e) {
        e.preventDefault();
		$("#btn_select_winnerf").attr('disabled', 'disabled');
		$("#btn_select_winnerf span").html('Please wait...');
		
		var _pid = $("#tw_pid").val();
		var _oid = $("#tw_orderid").val();
		var _seat = $("input[name=rbtnseats]:checked").val();
		$("#wsnm").html($("#wsnm_"+_seat).html());
		$.get('?myaction=selectwinner&pid='+_pid+'&oid='+_oid+'&seat='+_seat, function(data){
			$("#btn_select_winnerf").remove();
			$("input[name=rbtnseats]").attr('disabled', 'disabled');
			//$('#sbWinner').modal('show');
			$('#sbWinner').show();
		});
    });
    $(".btn-close").click(function(e){
        e.preventDefault();
        $('#sbWinner').hide();
        location.reload();
    });
});