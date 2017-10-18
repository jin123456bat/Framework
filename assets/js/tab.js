// JavaScript Document
var tab = function(){
	
	"use strict";
	
	var $_event = {};
	
	var trigger = function(eventName){
		if($_event[eventName])
		{
			$_event[eventName]();
		}
	};
	
	return {
		init:function(){
			$('.tab').on('click','.tab-title',function(){
	
				$(this).siblings().removeClass('active');
				$(this).addClass('active');

				var href = $(this).attr('href') || $(this).data('href');
				if($(href).length===1)
				{
					$(href).siblings().removeClass('active');
					$(href).addClass('active');
					
					trigger('tab.click.'+href.substr(1));
				}
				else
				{
					window.location = href;
				}
				return false;
			});
		},
		on:function(eventName,callback){
			$_event[eventName] = callback;
			return this;
		},
	};
}();

tab.init();