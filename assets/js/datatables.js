var datatables = function(argments){
	
	var $_ajax_url = argments.ajax.url||argments.ajax;
	
	var $_drawal = 0;

	var $_start = 0;
	var $_length = 0;
	var $_total = 0;

	var $_ajax_parameter = {};

	var $_pagesize = argments.pagesize||10;
	
	var $_response = null;
	
	var getColumnsDef = function(columnum,columname){
		for(var i=0;i<argments.columnDefs.length;i++)
		{
			if(typeof argments.columnDefs[i].targets == 'number')
			{
				if(argments.columnDefs[i].targets == columnum)
				{
					return argments.columnDefs[i][columname];
				}
			}
		}
		return undefined;
	}
	
	var getPk = function(tr){
		return tr.prop('data-pk');
	}
	
	var flush = function(tr){
		var pk = getPk(tr);
		
		var columns = argments.columns;

		var data = {draw:$_drawal,pk:pk,columns:columns};

		$.post($_ajax_url,data,function(response){
			tr.replaceWith(createTr(response.data[0]));
		})
	}
	
	var bindFlushEvent = function(){
		argments.table.on('flush.datatables','tr',function(){
			flush($(this));
		});
	}
	bindFlushEvent();
	
	
	var createTr = function(data){
		var pk = [];
		var tr = '<tr>';
		for(var j=0;j<argments.columns.length;j++)
		{
			var visible = argments.columns[j].visible == undefined?true:argments.columns[j].visible;
			var column = argments.columns[j].data;
			if(visible)
			{
				var render = getColumnsDef(j,'render');
				var style = getColumnsDef(j,'style');
				if(render == undefined)
				{
					tr += '<td '+(style?'style="'+style+'"':'')+'>'+data[column]+'</td>';
				}
				else
				{
					tr += '<td '+(style?'style="'+style+'"':'')+'>'+render(data[column],data)+'</td>';
				}
			}

			if(argments.columns[j].pk)
			{
				pk.push({key:argments.columns[j].name,value:data[column]});
			}
		}
		tr += '</tr>';
		tr = $(tr);
		tr.prop('data-pk',pk);
		if(argments.onRowLoaded)
		{
			argments.onRowLoaded(tr,data);
		}
		return tr;
	}

	var load = function(start,length){
		if(length!=-1)
		{
			start = parseInt(start);
			length = parseInt(length);

			if(start<0)
			{
				return false;
			}
			if(length<=0)
			{
				return false;
			}
			if(start >= $_total && start!=0)
			{
				return false;
			}

			$_start = start;
			$_length = length;
		}
		else
		{
			start = 0;
			length = -1;
		}
		
		clear();

		var columns = argments.columns;
		
		var order = argments.sort||[];

		var data = {draw:$_drawal,start:start,length:length,columns:columns,order:order};

		$.each($_ajax_parameter,function(index,value){
			data[index] = value;
		});
		
		if(argments.ajax.data)
		{
			data['ajaxData'] = argments.ajax.data;
		}

		$.post($_ajax_url,data,function(response){

			if(response.draw == $_drawal)
			{
				$_response = response;
				
				$_total = parseInt(response.recordsFiltered);
				
				tfooter($_total);
				
				if(response.data.length === 0)
				{
					var empty = '<tr><td colspan="100" style="text-align:center;">尚无数据</td></tr>';
					if(argments.empty)
					{
						empty = argments.empty;
					}
					argments.table.find('tbody').append(empty);
				}
				else
				{
					for(var i=0;i<response.data.length;i++)
					{
						var tr = createTr(response.data[i]);
						argments.table.find('tbody').append(tr);
					}
				}

				if(argments.afterTableLoaded)
				{
					argments.afterTableLoaded(argments.table);
				}
				
				$_drawal++;
			}
		});
	}



	var tfooter = function(total){
		if($_pagesize == -1)
		{
			return false;
		}
		
		var split_page = argments.table.find('tfoot #split_page');
		if(split_page.length == 0)
		{
			var split_page = argments.table.find('tfoot td');	
		}
		
		split_page.empty();

		var pagenum = Math.ceil(total/$_pagesize);

		var str = '';
		for(var i=1;i<=pagenum;i++)
		{
			current_page = ($_start/$_pagesize)+1;
			str += '<option value="'+i+'" '+((i==current_page)?'selected="selected"':'')+'>'+i+'</option>';
		}

		var tpl = '总计'+total+'个纪录，分'+pagenum+'页 |  '
											+'每页 <input id="set_pagesize" type="text" value="'+$_pagesize+'" style="width: 34px; height: 22px; outline: none;"> | '
											+'<i id="first_page" class="fa fa-step-backward" style="margin: 0px 5px;"></i>'
											+'<i id="prev_page" class="fa fa-play rotate-180" style="margin: 0px 5px;"></i>'
											+'<select id="set_page" style="width: 36px; height: 22px; margin: 0px 5px;">'+str+'</select>'
											+'<i id="next_page" class="fa fa-play" style="margin: 0px 5px;"></i>'
											+'<i id="last_page" class="fa fa-step-forward" style="margin: 0px 5px;"></i>';
		
		
		split_page.append(tpl);

		split_page.find('#first_page').on('click',function(){
			if($_start!=0)
			{
				load(0,$_pagesize);
			}
		});
		split_page.find('#prev_page').on('click',function(){
			load($_start-$_pagesize,$_pagesize);
		});
		split_page.find('#next_page').on('click',function(){
			load($_start+$_pagesize,$_pagesize);
		});
		split_page.find('#last_page').on('click',function(){
			if(Math.floor($_total/$_pagesize)*$_pagesize != $_start)
			{
				load(Math.floor($_total/$_pagesize)*$_pagesize,$_pagesize);
			}
		});
		split_page.find('#set_pagesize').on('change',function(){
			pagesize = parseInt($(this).val());
			if(pagesize>0)
			{
				$_pagesize = pagesize;
				load(0,$_pagesize);
			}
		});
		split_page.find('#set_page').on('change',function(){
			page = parseInt($(this).val());
			if(page>0)
			{
				load((page-1) * $_pagesize,$_pagesize);
			}
		});
	};

	var clear = function(){
		argments.table.find('tbody').empty();
		//argments.table.find('tfoot').empty();
	};

	var addAjaxParameter = function(key,value){
		$_ajax_parameter[key] = value;
	};
	
	var clearAjaxParameter = function(){
		console.log('ajax parameter clear');
		$_ajax_parameter = {};
	};

	load(0,$_pagesize);

	return {
		search:function(keyword){
			addAjaxParameter('keywords',keyword);
			load(0,$_pagesize);
		},
		reload:function(){
			load($_start,$_length);
		},
		addAjaxParameter(key,value){
			addAjaxParameter(key,value);
		},
		getResultPrimaryKey:function(){
			return $_response.pk;
		},
		clearAjaxParameter:function(){
			clearAjaxParameter();
		}
	};
}