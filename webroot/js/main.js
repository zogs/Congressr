var spanLoader = '<span class="ajaxLoader" id="ajaxLoader"></span>';
var ajaxLoader = "#ajaxLoader";



/*===========================================================
	JQUERY 
============================================================*/
$(document).ready(function(){
	

	/*===========================================================
		Autocomplete cityName input
	============================================================*/

	var inputcity = $("#inputcityName");
	if(inputcity.size()){
		var hiddencity = $("#cityID");
		var url = inputcity.attr('data-autocomplete-url');

	  	inputcity.autocomplete({
	  			serviceUrl:url,
	  			minChars:3,
	  			onSelect:function(value,data){ 
	  				
	  				hiddencity.val(data)},
	  		});
  	}
	/*===========================================================
		Security token send with AJAX /!\
	============================================================*/

	$("body").bind("ajaxSend", function(elm, xhr, settings){
		if (settings.type == "POST") {
			if(settings.data) {
				settings.data += "&token="+CSRF_TOKEN;				
			}		
		}
	});

	/*===========================================================
		Tooltip bootstrap
	============================================================*/
	$('a.bubble-top').livequery(function(){

		$(this).tooltip( { delay: { show: 500, hide: 100 }} );
	});
	$('a.bubble-bottom').livequery(function(){

		$(this).tooltip( { placement : 'bottom', delay: { show: 2000, hide: 100 }} );
	});
	

	/*===========================================================
		EXPANDABLE
		@param data-maxlenght
		@param data-expandtext
		@param data-collapsetext
	============================================================*/
	var expands = $('.expandable');
	if(expands.size()){
		expands.livequery(function(){
	    	$(this).expander({
	    		slicePoint: $(this).attr('data-maxlength'),
	    		expandPrefix: ' ',
	    		expandText: $(this).attr('data-expandtext'),
	    		userCollapseText: $(this).attr('data-collapsetext'),
	    		userCollapsePrefix: ' ',
	    	});
    	});
	}

		
	/*===========================================================
		GEO LOCATE
	============================================================*/

	if($('.geo-select').length!=0)
    		$(".geo-select").select2();

    	if($('#CC1').length!=0)
    		$("#CC1").select2({ formatResult: addCountryFlagToSelectState, formatSelection: addCountryFlagToSelectState});


	/*===========================================================
		FORM AJAX
	============================================================*/
	$('form.form-ajax').livequery('submit',function(){

		var url = $(this).attr('action');
		var params = $(this).serialize();

		$.ajax({
			type : 'POST',
			url : url,
			data : params,
			contentType: 'multipart/form-data',
			success : function( data ){
				$('#myModal').empty().html( data );
			},
			dataType: 'html'
		});
		return false;
	});


	/*===========================================================
		CHECK DUPLICATE MAIL AND LOGIN
	============================================================*/

	$("#inputlogin,#inputemail").bind('blur',function(){

		var input = $(this);
		var control = input.parent().parent();
		var help = input.next('p.help-inline');
		var value = $(this).val();
		var url = $(this).attr('data-url');
		var type = $(this).attr('name');

		var c = forbiddenchar(value);
		if(c && type=='login'){
			control.addClass('control-error');
			help.removeClass('hide').empty().html("Le caractère suivant n'est pas autorisé : "+c);
		}
		else {
			control.removeClass('control-error');
			help.addClass('hide').empty();


			$.ajax({
				type: 'GET',
				url: url,
				data: {type : type, value : value},
				success: function(data){

					if(data.error){	
						control.removeClass('control-success');					
						control.addClass('control-error');
						help.removeClass('hide').empty().html( data.error );
					}
					if(data.available) {;
						control.removeClass('control-error');
						control.removeClass('control-success');
						help.removeClass('hide').empty().html( data.available );
					}
				},
				dataType: 'json'
			});
		}


	});

	function forbiddenchar(string){

		var carac = new RegExp("[ @,\.;:/!&$£*§~#|)(}{ÀÂÇÈÉÊËÎÔÙÛàâçèéêëîôöùû]","g");
		var c = string.match(carac);
		if(c) return c;
	}

	/*===========================================================
		MODAL BOX
	============================================================*/
  	$('a.callModal').livequery('click',function(){
	        
	        var href = $(this).attr('href');
	        callModalBox(href);  	        
	        return false;
	  });
  	//===============================

});



/*===========================
	MODAL BOX
============================*/

modalBox = $("#myModal");

modalBox.modal({
        backdrop:true,
        keyboard: true,
        show:false
});

	
function callModalBox(href){

	var modal = $("#myModal");
	$.get(href,function(data){ $(modal).empty().html(data)},'html');
	$(modal).modal('show');
}



/*============================
	SELECTION GEOGRAPHIQUE
=============================*/
CC1 = ''; 
ADM1=''; 
ADM2=''; 
ADM3=''; 
ADM4='';
function showRegion(value,region)
{

	$("#"+region).nextAll('select').empty().remove();
	$("#"+region).next('.select2-container').nextAll('.select2-container').empty().remove();

	if(value!='')
	{		
		CC1 = $("#CC1").val();
		if(region=='ADM1') { ADM1 = value; ADM2=''; ADM3=''; ADM4=''; }
		if(region=='ADM2') { ADM2 = value; ADM3 = ''; ADM4 = ''; }
		if(region=='ADM3') { ADM3 = value; ADM4 = ''; }
		if(region=='ADM4') { ADM4 = value; }
		if(region=='city') return false;		

		var url = $('#submit-state').attr('data-url');

		$.ajax({
			type : 'GET',
			url : url,
			data : { parent:value, ADM: region, CC1:CC1, ADM1:ADM1, ADM2:ADM2, ADM3:ADM3, ADM4:ADM4 },
			dataType: 'json',
			success: function(data){
				
				if(trim(data)!='empty'){ 				
					$('#'+region).next('.select2-container').after(data.SelectELEMENT);
					$("#"+data.SelectID).select2();
				}
			}
		});
	}
}

//Function for select2 plugin
function addCountryFlagToSelectState(state) {

	return "<i class='flag flag-"+state.id.toLowerCase()+"'></i>"+state.text;
}

/*============================
	SELECTION CATEGORY
=============================*/
function showCategory(parent,level){

	var url = $('#submit-category').attr('data-url');

	$.ajax({
		type:'POST',
		url:url,
		data: { parent:parent, level:level},
		success: function(data){
			//alert(data);
			if(trim(data)!='empty'){
				$('#cat'+level).empty().remove();
				$('#cat'+(level-1)).after(data);
			}
		}
	});
}



//=============================
//    LOCAL STORAGE
//============================

jQuery(function($){

	$.fn.formBackUp = function(){

		if(!localStorage){
			return false;
		}

		var forms = this;
		var datas = {};
		var ls = false;
		datas.href = window.location.href;

		if(localStorage['formBackUp']){
			ls = JSON.parse(localStorage['formBackUp']);
			if(ls.href = datas.href){
				for( var id in ls){
					if(id != "href"){
						$("#"+id).val(ls[id]);
						datas[id] = ls[id];
					}
				}
			}
		}

		forms.find('input,textarea').keyup(function(){
			datas[$(this).attr('id')] = $(this).val();
			localStorage.setItem('formBackUp',JSON.stringify(datas));
		});

		forms.submit(function(e){
			localStorage.removeItem('formBackUp');
		});
	}

});