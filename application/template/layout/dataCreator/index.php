<html>
<body>
<table border="1px">
	<thead>
		<tr>
			<th></th>
			<th>近24小时</th>
			<th>昨天</th>
			<th>上周</th>
			<th>近7天</th>
			<th>近30天</th>
			<th>上月</th>
			<th>自定义</th>
		</tr>
	</thead>
	<tbody>
		<tr data-a="main_overview_cds" >
			<td>CDS在线数量</td>
			<td><input type="checkbox" data-duration="minutely" data-time="1"></td>
			<td><input type="checkbox" data-duration="minutely" data-time="2"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="3"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="4"></td>
			<td><input type="checkbox" data-duration="daily" data-time="5"></td>
			<td><input type="checkbox" data-duration="daily" data-time="6"></td>
			<td>
				<input type="text" name="starttime" data-time="7" placeholder="开始时间">
				<input type="text" name="endtime" data-time="7" placeholder="结束时间">
				<select name="duration">
					<option selected="selected" disabled="disabled">时间颗粒度</option>
					<option value="minutely">分钟</option>
					<option value="hourly">小时</option>
					<option value="daily">天</option>
				</select>
			</td>
		</tr>
		<tr data-a="main_overview_online" >
			<td>CDS在线人数</td>
			<td><input type="checkbox" data-duration="minutely" data-time="1"></td>
			<td><input type="checkbox" data-duration="minutely" data-time="2"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="3"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="4"></td>
			<td><input type="checkbox" data-duration="daily" data-time="5"></td>
			<td><input type="checkbox" data-duration="daily" data-time="6"></td>
			<td>
				<input type="text" name="starttime" data-time="7" placeholder="开始时间">
				<input type="text" name="endtime" data-time="7" placeholder="结束时间">
				<select name="duration">
					<option selected="selected" disabled="disabled">时间颗粒度</option>
					<option value="minutely">分钟</option>
					<option value="hourly">小时</option>
					<option value="daily">天</option>
				</select>
			</td>
		</tr>
		<tr data-a="main_overview_service_max">
			<td>首页服务流速</td>
			<td><input type="checkbox" data-duration="minutely" data-time="1"></td>
			<td><input type="checkbox" data-duration="minutely" data-time="2"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="3"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="4"></td>
			<td><input type="checkbox" data-duration="daily" data-time="5"></td>
			<td><input type="checkbox" data-duration="daily" data-time="6"></td>
			<td>
				<input type="text" name="starttime" data-time="7" placeholder="开始时间">
				<input type="text" name="endtime" data-time="7" placeholder="结束时间">
				<select name="duration">
					<option selected="selected" disabled="disabled">时间颗粒度</option>
					<option value="minutely">分钟</option>
					<option value="hourly">小时</option>
					<option value="daily">天</option>
				</select>
			</td>
		</tr>
		<tr data-a="main_overview_cp_service">
			<td>首页分CP服务流速</td>
			<td><input type="checkbox" data-duration="minutely" data-time="1"></td>
			<td><input type="checkbox" data-duration="minutely" data-time="2"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="3"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="4"></td>
			<td><input type="checkbox" data-duration="daily" data-time="5"></td>
			<td><input type="checkbox" data-duration="daily" data-time="6"></td>
			<td>
				<input type="text" name="starttime" data-time="7" placeholder="开始时间">
				<input type="text" name="endtime" data-time="7" placeholder="结束时间">
				<select name="duration">
					<option selected="selected" disabled="disabled">时间颗粒度</option>
					<option value="minutely">分钟</option>
					<option value="hourly">小时</option>
					<option value="daily">天</option>
				</select>
			</td>
		</tr>
		<tr data-a="content_cache_service">
			<td>内容交付的回源流速和服务流速</td>
			<td><input type="checkbox" data-duration="minutely" data-time="1"></td>
			<td><input type="checkbox" data-duration="minutely" data-time="2"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="3"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="4"></td>
			<td><input type="checkbox" data-duration="daily" data-time="5"></td>
			<td><input type="checkbox" data-duration="daily" data-time="6"></td>
			<td>
				<input type="text" name="starttime" data-time="7" placeholder="开始时间">
				<input type="text" name="endtime" data-time="7" placeholder="结束时间">
				<select name="duration">
					<option selected="selected" disabled="disabled">时间颗粒度</option>
					<option value="minutely">分钟</option>
					<option value="hourly">小时</option>
					<option value="daily">天</option>
				</select>
			</td>
		</tr>
		<tr data-a="node_detail">
			<td>节点详情（CAS0530000150）</td>
			<td><input type="checkbox" data-duration="minutely" data-time="1"></td>
			<td><input type="checkbox" data-duration="minutely" data-time="2"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="3"></td>
			<td><input type="checkbox" data-duration="hourly" data-time="4"></td>
			<td><input type="checkbox" data-duration="daily" data-time="5"></td>
			<td><input type="checkbox" data-duration="daily" data-time="6"></td>
			<td>
				<input type="text" name="starttime" data-time="7" placeholder="开始时间">
				<input type="text" name="endtime" data-time="7" placeholder="结束时间">
				<select name="duration">
					<option selected="selected" disabled="disabled">时间颗粒度</option>
					<option value="minutely">分钟</option>
					<option value="hourly">小时</option>
					<option value="daily">天</option>
				</select>
			</td>
		</tr>
	</tbody>
	<tfoot align="center">
		<tr><td>模式</td><td colspan="3"><input class="mode" type="radio" name="mode" value="1">直线</td><td colspan="4"><input type="radio" class="mode" name="mode" checked="checked" value="0">随机</td></tr>
		<tr>
			<td colspan="4"><button id="build">开始生成</button></td><td colspan="4"><button id="clean">清空所有数据</button></td>
		</tr>
	</tfoot>
</table>
<script   src="https://code.jquery.com/jquery-2.2.4.min.js"   integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>
<script type="text/javascript">
$('#build').on('click',function(){
	var tr = $('tbody tr');
	
	tr.each(function(index,value){
		var input = $(value).find('input[type=checkbox]:checked');
		if(input.length > 0)
		{
			input.each(function(index,value){
				if($(value).is(':checked'))
				{
					$('#build').attr('disabled','disabled');
					var a = $(value).parents('tr').data('a');
					var time = $(value).data('time');
					var duration = $(value).data('duration');
					var mode = $('.mode:checked').val();
					$.post('./index.php?c=dataCreator&a='+a,{mode:mode,timemode:time,duration:duration},function(response){
						if(response.code==1)
						{
							alert('成功添加'+response.data+'条数据');
						}
						else
						{
							alert(response.result);
						}
						$('#build').removeAttr('disabled');
					});
				}
			});
		}
		else
		{
			var starttime = $.trim($(value).find('input[name=starttime]').val());
			var endtime = $.trim($(value).find('input[name=endtime]').val());
			var duration = $.trim($(value).find('select[name=duration]').val());
			var a = $(value).data('a');
			
			if(starttime.length != 0 && endtime.length != 0 && duration.length != 0)
			{
				$('#build').attr('disabled','disabled');
				$.post('./index.php?c=dataCreator&a='+a,{starttime:starttime,endtime:endtime,duration:duration},function(response){
					if(response.code==1)
					{
						alert('添加'+response.data+'条数据成功');
					}
					else
					{
						alert(response.result);
					}
					$('#build').removeAttr('disabled');
				});
			}
		}
	});
	return false;
});

$('#clean').on('click',function(){
	$.post('./index.php?c=dataCreator&a=clean',{},function(response){
		if(response.code==1)
		{
			alert('清空'+response.data+'条数据完成');
		}
	});
	return false;
});
</script>
</body>
</html>