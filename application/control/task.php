<?php
namespace application\control;

use application\extend\bgControl;
use framework\core\debugger;

/**
 * 生成各个接口的文件数据
 * @author fx
 */
class task extends bgControl
{
	function buildHistory()
	{
		$cacheComponent = new \application\algorithm\cache();
		$starttime = '2016-11-01 00:00:00';
		$endtime = date('Y-m-d H:i:s');
		for ($t_time = $starttime;strtotime($t_time) < strtotime($endtime);$t_time = date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))))
		{
			$cacheComponent->traffic_stat(300,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->traffic_stat(1800,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->traffic_stat(3600,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->traffic_stat(7200,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->traffic_stat(86400,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->operation_stat(30*60,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->operation_stat(5*60,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->operation_stat(60*60,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->operation_stat(2*60*60,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
			$cacheComponent->operation_stat(24*60*60,$t_time,date('Y-m-d H:i:s',strtotime("+2 day",strtotime($t_time))));
		}
	}
	
	function minute5()
	{
		$minute5 = new debugger();
		$minute5->start();
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(300);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>300,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->operation_stat(300);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'operation_stat',
			'duration'=>300,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$commands = array(
			'node_cacheSnList' => 'php '.ROOT.'/index.php -c node -a cacheSnList',
			'node_cds' => 'php '.ROOT.'/index.php -c node -a cds_cache',//CDS列表的数据
			'main_overview_minutely_1' => 'php '.ROOT.'/index.php -c main -a overview -duration minutely -timemode 1',//首页 最近24小时的数据
			'content_overview_minutely_1' => 'php '.ROOT.'/index.php -c content -a overview -duration minutely -timemode 1',//内容交付概览  最近24小时的数据
			'content_videoDemand_minutely_1' => 'php '.ROOT.'/index.php -c content -a videoDemand -duration minutely -timemode 1',//内容交付视频点播 最近24小时的数据
			'content_videoLive_minutely_1' => 'php '.ROOT.'/index.php -c content -a videoLive -duration minutely -timemode 1',//内容交付视频直播 最近24小时的数据
			'content_mobile_minutely_1' => 'php '.ROOT.'/index.php -c content -a mobile -duration minutely -timemode 1',//内容交付移动应用 最近24小时的数据
			'content_http_minutely_1' => 'php '.ROOT.'/index.php -c content -a http -duration minutely -timemode 1',//内容交付常规资源 最近24小时的数据
		);
		
		//cds详情
		/* $sn = $this->combineSns();
		foreach ($sn as $s)
		{
			$commands['node_detail_'.$s] = 'php '.ROOT.'/index.php -c node -a detail -sn '.$s;
		} */
		
		$this->runTask($commands);
		
		
		
		$minute5->stop();
		return $minute5;
	}
	
	function minute30()
	{
		//创建30分钟的流量图
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(1800);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>1800,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));

		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->operation_stat(1800);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'operation_stat',
			'duration'=>1800,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
	}
	
	/**
	 * 生成每小时的数据报告
	 * @return \framework\core\debugger
	 */
	function hour()
	{
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(3600);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>3600,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->operation_stat(3600);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'operation_stat',
			'duration'=>3600,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$hour1 = new debugger();
		$hour1->start();
		//生成api的缓存数据
		/* $commands = array();
		$data = $this->model('sn_in_cache')->select();
		$build_sn_list = array();
		foreach ($data as $sns)
		{
			$commands['api_overview_hourly_1_'.$sns['sns']] = 'php '.ROOT.'/index.php -c api -a overview -duration hourly -timemode 1 -sn '.$sns['sns'];
			$sn = explode(',', $sns['sns']);
			foreach ($sn as $s)
			{
				if (!in_array($s, $build_sn_list,true))
				{
					$build_sn_list[] = $s;
					$commands['api_detail_hourly_1_'.$s] = 'php '.ROOT.'/index.php -c api -a detail -duration hourly -timemode 1 -sn '.$s;
				}
			}
		} */
		$this->runTask($commands);
		$hour1->stop();
		return $hour1;
	}
	
	/**
	 * 每2小时的数据报告
	 */
	function hour2()
	{
		$hour2 = new debugger();
		$hour2->start();
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(7200);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>7200,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->operation_stat(7200);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'operation_stat',
			'duration'=>7200,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$hour2->stop();
		return $hour2;
	}
	
	/**
	 * 每天的数据报告
	 */
	function day()
	{
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(86400);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>86400,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->operation_stat(24*3600);;
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'operation_stat',
			'duration'=>24*3600,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		
		$day1 = new debugger();
		$day1->start();
		$commands = array(
			'main_overview_minutely_2' => 'php '.ROOT.'/index.php -c main -a overview -duration minutely -timemode 2',//首页 昨天的数据
			'main_overview_hourly_3' => 'php '.ROOT.'/index.php -c main -a overview -duration hourly -timemode 3',//首页近7天的数据
			'main_overview_daily_5' => 'php '.ROOT.'/index.php -c main -a overview -duration daily -timemode 5',//首页近30天的数据
			'content_overview_minutely_2' => 'php '.ROOT.'/index.php -c content -a overview -duration minutely -timemode 2',//内容交付概览  昨天的数据
			'content_videoDemand_minutely_2' => 'php '.ROOT.'/index.php -c content -a videoDemand -duration minutely -timemode 2',//内容交付视频点播 昨天的数据
			'content_videoLive_minutely_2' => 'php '.ROOT.'/index.php -c content -a videoLive -duration minutely -timemode 2',//内容交付视频直播 昨天的数据
			'content_mobile_minutely_2' => 'php '.ROOT.'/index.php -c content -a mobile -duration minutely -timemode 2',//内容交付移动应用 昨天的数据
			'content_http_minutely_2' => 'php '.ROOT.'/index.php -c content -a http -duration minutely -timemode 2',//内容交付常规资源 昨天的数据
			'content_overview_hourly_3' => 'php '.ROOT.'/index.php -c content -a overview -duration hourly -timemode 3',//内容交付概览 7天的数据
			'content_videoDemand_hourly_3' => 'php '.ROOT.'/index.php -c content -a videoDemand -duration hourly -timemode 3',//内容交付视频点播 7天的数据
			'content_videoLive_hourly_3' => 'php '.ROOT.'/index.php -c content -a videoLive -duration hourly -timemode 3',//内容交付视频直播 7天的数据
			'content_mobile_hourly_3' => 'php '.ROOT.'/index.php -c content -a mobile -duration hourly -timemode 3',//内容交付移动应用 7天的数据
			'content_http_hourly_3' => 'php '.ROOT.'/index.php -c content -a http -duration hourly -timemode 3',//内容交付常规资源 7天的数据
			'content_overview_daily_5' => 'php '.ROOT.'/index.php -c content -a overview -duration daily -timemode 5',//内容交付概览 30天的数据
			'content_videoDemand_daily_5' => 'php '.ROOT.'/index.php -c content -a videoDemand -duration daily -timemode 5',//内容交付视频点播 30天的数据
			'content_videoLive_daily_5' => 'php '.ROOT.'/index.php -c content -a videoLive -duration daily -timemode 5',//内容交付视频直播 30天的数据
			'content_mobile_daily_5' => 'php '.ROOT.'/index.php -c content -a mobile -duration daily -timemode 5',//内容交付移动应用 30天的数据
			'content_http_daily_5' => 'php '.ROOT.'/index.php -c content -a http -duration daily -timemode 5',//内容交付常规资源 30天的数据
		);
			
		//生成api的缓存数据
		/* $data = $this->model('sn_in_cache')->select();
		$build_sn_list = array();
		foreach ($data as $sns)
		{
			$commands['api_overview_hourly_2_'.$sns['sns']] = 'php '.ROOT.'/index.php -c api -a overview -duration hourly -timemode 2 -sn '.$sns['sns'];
			$commands['api_overview_daily_3_'.$sns['sns']] = 'php '.ROOT.'/index.php -c api -a overview -duration daily -timemode 3 -sn '.$sns['sns'];
			$commands['api_overview_daily_5_'.$sns['sns']] = 'php '.ROOT.'/index.php -c api -a overview -duration daily -timemode 5 -sn '.$sns['sns'];
		
			$sn = explode(',', $sns['sns']);
			foreach ($sn as $s)
			{
				if (!in_array($s, $build_sn_list,true))
				{
					$build_sn_list[] = $s;
					$commands['api_detail_hourly_2_'.$s] = 'php '.ROOT.'/index.php -c api -a detail -duration hourly -timemode 2 -sn '.$s;
					$commands['api_detail_daily_3_'.$s] = 'php '.ROOT.'/index.php -c api -a detail -duration daily -timemode 3 -sn '.$s;
					$commands['api_detail_daily_5_'.$s]	= 'php '.ROOT.'/index.php -c api -a detail -duration daily -timemode 5 -sn '.$s;
				}
			}
		} */
		$this->runTask($commands);
		$day1->stop();
		return $day1;
	}
	
	/**
	 * 生成每周的数据报告
	 */
	function week()
	{
		$week1 = new debugger();
		$week1->start();
		$commands = array(
			'main_overview_hourly_4' => 'php '.ROOT.'/index.php -c main -a overview -duration hourly -timemode 4',//首页上周的数据
			'content_overview_hourly_4' => 'php '.ROOT.'/index.php -c content -a overview -duration hourly -timemode 4',//内容交付概览 上周的数据
			'content_videoDemand_hourly_4' => 'php '.ROOT.'/index.php -c content -a videoDemand -duration hourly -timemode 4',//内容交付视频点播 上周的数据
			'content_videoLive_hourly_4' => 'php '.ROOT.'/index.php -c content -a videoLive -duration hourly -timemode 4',//内容交付视频直播 上周的数据
			'content_mobile_hourly_4' => 'php '.ROOT.'/index.php -c content -a mobile -duration hourly -timemode 4',//内容交付移动应用 上周的数据
			'content_http_hourly_4' => 'php '.ROOT.'/index.php -c content -a http -duration hourly -timemode 4',//内容交付常规资源 上周的数据
		);
			
		//生成api的缓存数据
		/* $data = $this->model('sn_in_cache')->select();
		$build_sn_list = array();
		foreach ($data as $sns)
		{
			$commands['api_overview_daily_4_'.$sns['sns']] = 'php '.ROOT.'/index.php -c api -a overview -duration daily -timemode 4 -sn '.$sns['sns'];
		
			$sn = explode(',', $sns['sns']);
			foreach ($sn as $s)
			{
				if (!in_array($s, $build_sn_list,true))
				{
					$build_sn_list[] = $s;
					$commands['api_detail_daily_4_'.$s] = 'php '.ROOT.'/index.php -c api -a detail -duration daily -timemode 4 -sn '.$s;
				}
			}
		} */
		$this->runTask($commands);
		$week1->stop();
		return $week1;
	}
	
	function month()
	{
		$month1 = new debugger();
		$month1->start();
		$commands = array(
			'main_overview_daily_6' => 'php '.ROOT.'/index.php -c main -a overview -duration daily -timemode 6',//首页上月的数据
			'content_overview_daily_6' => 'php '.ROOT.'/index.php -c content -a overview -duration daily -timemode 6',//内容交付概览 上月的数据
			'content_videoDemand_daily_6' => 'php '.ROOT.'/index.php -c content -a videoDemand -duration daily -timemode 6',//内容交付视频点播 上月的数据
			'content_videoLive_daily_6' => 'php '.ROOT.'/index.php -c content -a videoLive -duration daily -timemode 6',//内容交付视频直播 上月的数据
			'content_mobile_daily_6' => 'php '.ROOT.'/index.php -c content -a mobile -duration daily -timemode 6',//内容交付移动应用 上月的数据
			'content_http_daily_6' => 'php '.ROOT.'/index.php -c content -a http -duration daily -timemode 6',//内容交付常规资源 上月的数据
		);
		
		//生成api的缓存数据
		/* $data = $this->model('sn_in_cache')->select();
		$build_sn_list = array();
		foreach ($data as $sns)
		{
			//上个月的数据
			$commands['api_overview_daily_6_'.$sns['sns']] = 'php '.ROOT.'/index.php -c api -a overview -duration daily -timemode 6 -sn '.$sns['sns'];
			$sn = explode(',', $sns['sns']);
			foreach ($sn as $s)
			{
				if (!in_array($s, $build_sn_list,true))
				{
					$build_sn_list[] = $s;
					$commands['api_detail_daily_6_'.$s] = 'php '.ROOT.'/index.php -c api -a detail -duration daily -timemode 6 -sn '.$s;
				}
			}
		} */
		$this->runTask($commands);
		$month1->stop();
		return $month1;
	}
	
	/**
	 * 总方法是每分钟执行一次
	 */
	function run()
	{
		$config = self::getConfig('app');
		if (!$config['cache'])
		{
			return ;
		}
		$debugger = new debugger();
		$minute5 = new debugger();
		$hour1 = new debugger();
		$hour2 = new debugger();
		$day1 = new debugger();
		$week1 = new debugger();
		$month1 = new debugger();
		
		
		$minute = date('i');
		$hour = date('H');
		$week = date('N');
		$day = date('d');
		
		if ($minute%5===0)
		{
			$minute5 = $this->minute5();
		}
		
		
		//每半小时执行一次
		if ($minute == '35' || $minute == '05')
		{
			$this->minute30();
		}
		
		//每小时的5分钟执行一次
		if ($minute == '05')
		{
			$hour1 = $this->hour();
		}
		
		//每2小时的5分钟执行一次
		if ($hour%2===0 && $minute=='05')
		{
			$hour2 = $this->hour2();
		}
		
		//每天的05分钟执行一次
		if ($hour === '00' && $minute == '05')
		{
			$day1 = $this->day();
		}
		
		//每个星期一的0点5分执行一次
		if ($week == 1 && $hour === '00' && $minute === '05')
		{
			$week1 = $this->week();
		}
		
		//每个月1号，0点5分执行
		if ($day == '01' && $hour === '00' && $minute == '05')
		{
			$month1 = $this->month();
		}
		$debugger->stop();
		
		if ($minute%5===0)
		{
			$this->log($debugger,$minute5,$hour1,$hour2,$day1,$week1,$month1);
		}
	}
	
	/**
	 * 记录运行日志
	 * @param debugger $debugger
	 * @param debugger $minute5
	 * @param debugger $hour1
	 * @param debugger $hour2
	 * @param debugger $day1
	 * @param debugger $week1
	 * @param debugger $month1
	 */
	function log(debugger $debugger,debugger $minute5,debugger $hour1,debugger $hour2,debugger $day1,debugger $week1,debugger $month1)
	{
		$data = array(
			'starttime' => date('Y-m-d H:i:s',intval($debugger->getStarttime())),
			'endtime' => date('Y-m-d H:i:s',intval($debugger->getEndtime())),
			'runtime' => $debugger->getTime(),
			'task_5minutes_starttime' => date('Y-m-d H:i:s',intval($minute5->getStarttime())),
			'task_5minutes_endtime' => date('Y-m-d H:i:s',intval($minute5->getEndtime())),
			'task_5minutes_time' => $minute5->getTime(),
			'task_5minutes_memory' => $minute5->getMemory(),
			'task_1hour_starttime' => date('Y-m-d H:i:s',intval($hour1->getStarttime())),
			'task_1hour_endtime' => date('Y-m-d H:i:s',intval($hour1->getEndtime())),
			'task_1hour_time' => $hour1->getTime(),
			'task_1hour_memory' => $hour1->getMemory(),
			'task_2hour_starttime' => date('Y-m-d H:i:s',intval($hour2->getStarttime())),
			'task_2hour_endtime' => date('Y-m-d H:i:s',intval($hour2->getEndtime())),
			'task_2hour_time' => $hour2->getTime(),
			'task_2hour_memory' => $hour2->getMemory(),
			'task_1day_starttime' => date('Y-m-d H:i:s',intval($day1->getStarttime())),
			'task_1day_endtime' => date('Y-m-d H:i:s',intval($day1->getEndtime())),
			'task_1day_time' => $day1->getTime(),
			'task_1day_memory' => $day1->getMemory(),
			'task_1week_starttime' => date('Y-m-d H:i:s',intval($week1->getStarttime())),
			'task_1week_endtime' => date('Y-m-d H:i:s',intval($week1->getEndtime())),
			'task_1week_time' => $week1->getTime(),
			'task_1week_memory' => $week1->getMemory(),
			'task_1month_starttime' => date('Y-m-d H:i:s',intval($month1->getStarttime())),
			'task_1month_endtime' => date('Y-m-d H:i:s',intval($month1->getEndtime())),
			'task_1month_time' => $month1->getTime(),
			'task_1month_memory' => $month1->getMemory(),
		);
		$this->model('task_run_log')->insert($data);
	}
	
	/**
	 * 运行单条命令
	 * @param unknown $command
	 * @param unknown $name
	 */
	function runTask($command,$name = '')
	{
		if (is_string($command))
		{
			$createtime = date('Y-m-d H:i:s',time());
			$debugger = new debugger();
			$response = exec($command,$output);
			$output = implode('', $output);
			$debugger->stop();
			$this->model('task_detail')->insert(array(
				'createtime'=>$createtime,
				'endtime' => date('Y-m-d H:i:s',time()),
				'time' => $debugger->getTime(),
				'name' => $name,
				'response' => $output,
				'command' => $command,
			));
		}
		else if (is_array($command))
		{
			foreach ($command as $name => $com)
			{
				$this->runTask($com,$name);
			}
		}
	}
	
	/**
	 * 清空所有缓存数据，重新生成
	 */
	function rebuild()
	{
		$this->model('cache')->truncate();
	}
	
	/**
	 * 创建第三种算法的数据
	 */
	function buildData()
	{
		
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(300);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>300,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(60*60);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>60*60,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(2*60*60);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>2*60*60,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
		$starttime = date('Y-m-d H:i:s');
		$datadebugger = new debugger();
		$cacheComponent = new \application\algorithm\cache();
		$time = $cacheComponent->traffic_stat(24*60*60);
		$datadebugger->stop();
		$this->model('build_data_log')->insert(array(
			'name' => 'traffic_stat',
			'duration'=>24*60*60,
			'run_starttime' => $starttime,
			'run_endtime' => date('Y-m-d H:i:s'),
			'data_starttime' => $time['starttime'],
			'data_endtime' => $time['endtime'],
			'runtime' => $datadebugger->getTime(),
		));
	}
}