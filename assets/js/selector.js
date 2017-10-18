var selector = function(obj,args){
	"use strict";
	
	var data = {};
	
	var $_current = [];
	
	var $_value = [];
	
	var $_stack = [];
	
	var $_index = 0;
	
	var $_focus = false;
	
	var url = args.ajax.url;
	$.ajax({
		method:'get',
		url:url,
		async:false,
		dataType:'json',
		success:function(response){
			data = response;
		}
	});
	
	var div = $('<div tabindex="0"></div>');
	
	var style = ['height','border','outline','display','width','cssText'];
	for(var i=0;i<style.length;i++)
	{
		var name = style[i];
		var value = obj.css(name);
		div.css(name,value);
	}
	div.css('cursor','pointer');
	
	//设置placeholder
	var placeholder = obj.attr('placeholder') || '请选择...';
	div.html(placeholder);
	
	//添加虚拟下拉select
	div.insertAfter(obj);
	
	//添加下拉框
	var pulldown = $('<div id="pulldown"></div>');
	pulldown.css({
		border:'1px solid rgba(215, 215, 215, 1)',
		borderTop:'none',
		backgroundColor:'#FFFFFF',
		zIndex:'1000',
		position:'initial',
		height:'auto',
		width:obj.css('width'),
		display:'none',
	});
	
	var pulldown_head = $('<div id="pulldown_head"></div>');
	pulldown_head.css({
		fontSize:'12px',
		padding:'7px',
		height:'30px',
	});
	
	var pulldown_head_submit = $('<div id="pulldown_head_submit">确定</div>');
	pulldown_head_submit.css({
		display:'inline',
		color:'#FF6600',
		cursor:'pointer',
	});
	pulldown_head_submit.on('click',function(){
		blur($_value);
	});
	pulldown_head.append(pulldown_head_submit);
	
	pulldown_head.append('<span class="selector_pulldown_head_empty" style="padding-right:5px;padding-left:5px;">></span>');
	var pulldown_head_select = $('<div class="selector_pulldown_head_empty">请选择分类</div>');
	pulldown_head_select.css({
		color:'#6699FF',
		display:'inline',
		cursor:'pointer',
	});
	pulldown_head.append(pulldown_head_select);
	
	var pulldown_head_append = function(text){
		
		pulldown_head.find('.selector_pulldown_head_empty').css('display','none');
		
		pulldown_head.append('<span class="selector_pulldown_head_iterator" style="padding-right:5px;padding-left:5px;">></span>');
		var pulldown_head_iterator = $('<div data-index="'+$_index+'" class="selector_pulldown_head_iterator">'+text+'</div>');
		pulldown_head_iterator.css({
			color:'#6699FF',
			display:'inline',
			cursor:'pointer',
		});
		pulldown_head_iterator.on('click',function(){
			var index = parseInt($(this).data('index'));
			clear();
			
			$_current = $_stack[index];
			//loadItemFromData(index+1,$_value[index]);
			for(i=0;i<$_stack[index].length;i++)
			{
				addItem($_stack[index][i].id,$_stack[index][i].name,index+1);
			}
			
			$_index = index;
			$_stack = $_stack.slice(0,index);
			$_value = $_value.slice(0,index);
			$(this).prev().remove();
			$(this).nextAll().remove();
			$(this).remove();
		});
		pulldown_head.append(pulldown_head_iterator);
		$_index++;
	};
	
	
	pulldown.append(pulldown_head);
	
	var pulldown_body = $('<div id="pulldown_body"></div>');
	pulldown_body.css({
		fontSize:'12px',
	});
	pulldown.append(pulldown_body);
	
	var ele = div;
	if(args.position)
	{
		ele = args.position(div);
	}
	pulldown.insertAfter(ele);
	
	var focus = function(){
		$_focus = true;
		pulldown.css('display','block');
	};
	
	var blur = function(val){
		$_focus = false;
		var content = '';
		pulldown_head.find('.selector_pulldown_head_iterator').each(function(index,value){
			content += $(value).html()+' ';
		});
		
		if(content.substr(4).length === 0)
		{
			div.html(placeholder);
		}
		else
		{
			div.html(content.substr(4));
		}
		pulldown.css('display','none');
		
		//设定select的值
		obj.find('option').remove();
		obj.append('<option value=""></option>');
		var option = $('<option value="'+val+'" selected="selected"></option>');
		obj.append(option);
	};
	
	var clear = function(){
		pulldown_body.find('.selector-item').remove();
	};
	
	var addItem = function(value,text,level){
		var icon;
		if(level === 1)
		{
			icon = 'Ⅰ';
		}
		else if(level === 2)
		{
			icon = 'Ⅱ';
		}
		else if(level === 3)
		{
			icon = 'Ⅲ';
		}
		var item = $('<div class="selector-item" value="'+value+'" data-level="'+level+'" style="cursor:pointer;height: 30px;line-height: 30px;"><div style="display: inline-block;height: 30px;width: 30px;text-align: center; border-right: 1px solid rgba(228, 228, 228, 1);">'+icon+'</div><div class="selector-item-text" style="display: inline-block;padding-left: 15px;">'+text+'</div></div>');
		item.on('mouseover',function(){
			$(this).css('backgroundColor','rgba(247, 247, 247, 1)');
		}).on('mouseout',function(){
			$(this).css('backgroundColor','#FFFFFF');
		}).on('click',function(){
			$_stack.push($_current);
			clear();
			$_value.push($(this).attr('value'));
			pulldown_head_append($(this).find('.selector-item-text').text());
			if(!loadItemFromData(parseInt($(this).data('level'))+1,$(this).attr('value')))
			{
				blur($_value);
			}
			
		});
		pulldown_body.append(item);
	};
	
	obj.on('change',function(){
		setValue($(this).val());
	});
	
	var setValue = function(value){
		if(value && value.length>0)
		{
			pulldown_head.find('.selector_pulldown_head_iterator:eq(1)').trigger('click');
			value = value.split(',');
			for(var z=0;z<value.length;z++)
			{
				var selected = pulldown_body.find('.selector-item[value='+value[z]+']');
				if(selected.length===1)
				{
					selected.trigger('click');
				}
				else
				{
					return false;
				}
			}
			return true;
		}
		else
		{
			pulldown_head.find('.selector_pulldown_head_iterator:eq(1)').trigger('click');
			pulldown_head_submit.trigger('click');
			return true;
		}
	};
	
	var getValue = function(){
		return $_value;
	};
	
	var loadItemFromData = function(level,uid){
		if(level === 1)
		{
			for(i=0;i<data.length;i++)
			{
				addItem(data[i].id,data[i].name,level);
			}
			$_current = data;
			return true;
		}
		else
		{
			for(i=0;i<$_current.length;i++)
			{
				if($_current[i].id===uid)
				{
					if($_current[i].child && $_current[i].child.length>0)
					{
						for(var j=0;j<$_current[i].child.length;j++)
						{
							addItem($_current[i].child[j].id,$_current[i].child[j].name,level);
						}
						$_current = $_current[i].child;
						return true;
					}
					else
					{
						return false;
					}
				}
			}
		}
	};
	
	loadItemFromData(1);
	
	
	div.on('click',function(){
		if(!$_focus)
		{
			focus();
		}
		else
		{
			blur($_value);
		}
		return false;
	});
	
	obj.css('display','none');
	
	return {
		addItem:function(value,text,level){
			addItem(value,text,level);
		},
		clear:function(){
			clear();
		},
		focus:function(){
			focus();
		},
		blur:function(){
			blur($_value);
		},
		val:function(value){
			if(value)
			{
				return setValue(value);
			}
			else
			{		
				return getValue();
			}
		},
	};
	
};