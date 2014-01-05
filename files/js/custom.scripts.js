$(document).ready(function() {

	$('input[name="register_02"]').passwordStrength();

	//$('#coin-slider').coinslider({ width: 445, delay: 5000, height: 198, navigation: true, opacity: 0.9 });
	$('#coin-slider').coinslider({ width: 438, delay: 5000, height: 247, navigation: true, opacity: 0.9 });
	

	$('#register_01').on('blur', function(){ 

		var elem = $(this).next('.status');

		var nickReg = /^([\A-Za-z0-9\_]+)?$/;

		

		if(nickReg.test(this.value) && (this.value).length > 0) {

			$.ajax({

				url: "_validations.php?type=nick&value="+this.value,

				type: "GET",

				success: function(html)

				{   if(html == '1') { elem.removeClass('free').addClass('busy'); }

					else { elem.removeClass('busy').addClass('free'); } 

				}

			});

		}

		else

		{

			elem.removeClass('free').addClass('busy');

		}

	});

	

	var show = $('#create-table-form').attr('data-show');

	if(show == "false") {

		$('#create-table-form').css("display", "none");

	} else {

		$('#create-table-form').css("display", "block");

	}

	

	// Show/Hide form a new custom bet

	$("#create-table").click(function(e){

		e.preventDefault();

		$('#create-table-form').toggle('normal');

	

	});	

	

	// Expand Panel

	$("#open").click(function(e){

		e.preventDefault();

		$("div#panel").slideDown("slow");

	

	});	

	

	// Collapse Panel

	$("#close").click(function(e){

		e.preventDefault();

		$("div#panel").slideUp("slow");	

	});		

	

	// Switch buttons from "Log In | Register" to "Close Panel" on click

	$("#toggle a").click(function (e) {

		e.preventDefault();

		$("#toggle a").toggle();

	});		

	

	$('#register_03').on('blur', function(){ 

		var elem = $(this).next('.status');

		if( $('#register_02').val().length > 0 )

		{

			if((this.value).length > 5) {

				if( (this.value) == $('#register_02').val() )

				{

					elem.removeClass('busy').addClass('free'); 

				}

				else

				{

					elem.removeClass('free').addClass('busy');

				}

			}

			else

			{

				elem.removeClass('free').addClass('busy');

			}

		}

		

	});

	

	$('#register_04').on('blur', function(){ 

		var elem = $(this).next('.status');

		var mailReg = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,253}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,253}[a-zA-Z0-9])?)*$/;

		

		if(mailReg.test(this.value) && (this.value).length > 0) {

            $.ajax({

				url: "_validations.php?type=mail&value="+this.value,

				type: "GET",

				success: function(html)

				{   if(html == '1') { elem.removeClass('free').addClass('busy'); }

					else { elem.removeClass('busy').addClass('free'); } 

				}

			});

        }

		else

		{

			elem.removeClass('free').addClass('busy');

		}		

	});



	$('#mail_01').click(function () {

		var elems = new Array();

		elems[0] = $(this).parent().next('dt');

		elems[1] = $(this).parent().next('dt').next('dd');

		

		if( this.checked == true )

		{

			for(i = 0; i < elems.length; i++)

			{

				elems[i].show("normal");

			}

		}

		else

		{

			for(i = 0; i < elems.length; i++)

			{

				elems[i].hide("normal");

			}

		}

	});

	

	$('#pass_01').change(function(){

		var elems=[];

		elems[0]=$(this).parent().next('dt');

		elems[1]=elems[0].next('dd');

		elems[2]=elems[1].next('dt');

		elems[3]=elems[2].next('dd');

		if( this.checked == true )

		{

			for(i = 0; i < elems.length; i++)

			{

				elems[i].show("normal");

			}

		}

		else

		{

			for(i = 0; i < elems.length; i++)

			{

				elems[i].hide("normal");

			}

		}

	});	

		

	$("input#ref-nick").click(function(){

		this.select();

	});

	

});



$.fn.passwordStrength = function( options ){

	return this.each(function(){

		var that = this;that.opts = {};

		that.opts = $.extend({}, $.fn.passwordStrength.defaults, options);

		

		that.div = $(that.opts.targetDiv);

		that.defaultClass = that.div.attr('class');

		

		that.percents = (that.opts.classes.length) ? 100 / that.opts.classes.length : 100;



		 v = $(this)

		.keyup(function(){

			if( typeof el == "undefined" )

				this.el = $(this);

			var s = getPasswordStrength (this.value);

			var p = this.percents;

			var t = Math.floor( s / p );

			

			if( 100 <= s )

				t = this.opts.classes.length - 1;

				

			this.div

				.removeAttr('class')

				.addClass( this.defaultClass )

				.addClass( this.opts.classes[ t ] );

				

		});

	});



	function getPasswordStrength(H){

		var D=(H.length);

		if(D>5){

			D=5

		}

		var F=H.replace(/[0-9]/g,"");

		var G=(H.length-F.length);

		if(G>3){G=3}

		var A=H.replace(/\W/g,"");

		var C=(H.length-A.length);

		if(C>3){C=3}

		var B=H.replace(/[A-Z]/g,"");

		var I=(H.length-B.length);

		if(I>3){I=3}

		var E=((D*10)-20)+(G*10)+(C*15)+(I*10);

		if(E<0){E=0}

		if(E>100){E=100}

		return E

	}

};

	

$.fn.passwordStrength.defaults = {

	classes : Array('is10','is20','is30','is40','is50','is60','is70','is80','is90','is100'),

	targetDiv : '#passwordStrengthDiv',

	cache : {}

}