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
		//每5分钟执行
		if ($minute%5 === 0)
		{
			$minute5->start();
			//首页 最近24小时的数据
			$string = 'php '.ROOT.'/index.php -c main -a overview -duration minutely -timemode 1';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'main_overview_minutely_1',
				'response' => exec($string),
			));
			
			//内容交付概览  最近24小时的数据
			$string = 'php '.ROOT.'/index.php -c content -a overview -duration minutely -timemode 1';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_overview_minutely_1',
				'response' => exec($string),
			));
			
			//内容交付视频点播 最近24小时的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoDemand -duration minutely -timemode 1';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoDemand_minutely_1',
				'response' => exec($string),
			));
			
			//内容交付视频直播 最近24小时的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoLive -duration minutely -timemode 1';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoLive_minutely_1',
				'response' => exec($string),
			));
			
			//内容交付移动应用 最近24小时的数据
			$string = 'php '.ROOT.'/index.php -c content -a mobile -duration minutely -timemode 1';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_mobile_minutely_1',
				'response' => exec($string),
			));
			
			//内容交付常规资源 最近24小时的数据
			$string = 'php '.ROOT.'/index.php -c content -a http -duration minutely -timemode 1';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_http_minutely_1',
				'response' => exec($string),
			));
			$minute5->stop();
		}
		
		//每小时的5分钟执行一次
		if ($minute == '05')
		{
			$hour1->start();
			
			$hour1->stop();
		}
		
		//每2小时的5分钟执行一次
		$hour = date('H');
		if ($hour%2===0 && $minute=='05')
		{
			$hour2->start();
			
			$hour2->stop();
		}
		
		//每天的05分钟执行一次
		if ($hour === '00' && $minute == '05')
		{
			$day1->start();
			//首页 昨天的数据
			$string = 'php '.ROOT.'/index.php -c main -a overview -duration minutely -timemode 2';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'main_overview_minutely_2',
				'response' => exec($string),
			));
			
			//首页近7天的数据
			$string = 'php '.ROOT.'/index.php -c main -a overview -duration hourly -timemode 3';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'main_overview_hourly_3',
				'response' => exec($string),
			));
			
			//首页近30天的数据
			$string = 'php '.ROOT.'/index.php -c main -a overview -duration daily -timemode 5';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'main_overview_daily_5',
				'response' => exec($string),
			));
			
			
			//内容交付概览  昨天的数据
			$string = 'php '.ROOT.'/index.php -c content -a overview -duration minutely -timemode 2';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_overview_minutely_2',
				'response' => exec($string),
			));
			
			//内容交付视频点播 昨天的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoDemand -duration minutely -timemode 2';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoDemand_minutely_2',
				'response' => exec($string),
			));
			
			//内容交付视频直播 昨天的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoLive -duration minutely -timemode 2';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoLive_minutely_2',
				'response' => exec($string),
			));
			
			//内容交付移动应用 昨天的数据
			$string = 'php '.ROOT.'/index.php -c content -a mobile -duration minutely -timemode 2';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_mobile_minutely_2',
				'response' => exec($string),
			));
			
			//内容交付常规资源 昨天的数据
			$string = 'php '.ROOT.'/index.php -c content -a http -duration minutely -timemode 2';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_http_minutely_2',
				'response' => exec($string),
			));
			
			
			//内容交付概览 7天的数据
			$string = 'php '.ROOT.'/index.php -c content -a overview -duration hourly -timemode 3';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_overview_hourly_3',
				'response' => exec($string),
			));
			
			//内容交付视频点播 7天的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoDemand -duration hourly -timemode 3';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoDemand_hourly_3',
				'response' => exec($string),
			));
			
			//内容交付视频直播 7天的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoLive -duration hourly -timemode 3';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoLive_hourly_3',
				'response' => exec($string),
			));
			
			//内容交付移动应用 7天的数据
			$string = 'php '.ROOT.'/index.php -c content -a mobile -duration hourly -timemode 3';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_mobile_hourly_3',
				'response' => exec($string),
			));
			
			//内容交付常规资源 7天的数据
			$string = 'php '.ROOT.'/index.php -c content -a http -duration hourly -timemode 3';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_http_hourly_3',
				'response' => exec($string),
			));
			
			
			//内容交付概览 30天的数据
			$string = 'php '.ROOT.'/index.php -c content -a overview -duration daily -timemode 5';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_overview_daily_5',
				'response' => exec($string),
			));
			
			//内容交付视频点播 30天的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoDemand -duration daily -timemode 5';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoDemand_daily_5',
				'response' => exec($string),
			));
			
			//内容交付视频直播 30天的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoLive -duration daily -timemode 5';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoLive_daily_5',
				'response' => exec($string),
			));
			
			//内容交付移动应用 30天的数据
			$string = 'php '.ROOT.'/index.php -c content -a mobile -duration daily -timemode 5';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_mobile_daily_5',
				'response' => exec($string),
			));
			
			//内容交付常规资源 30天的数据
			$string = 'php '.ROOT.'/index.php -c content -a http -duration daily -timemode 5';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_http_daily_5',
				'response' => exec($string),
			));
			$day1->stop();
		}
		
		//每个星期一的0点5分执行一次
		$week = date('N');
		if ($week == 1 && $hour === '00' && $minute === '05')
		{
			$week1->start();
			//首页上周的数据
			$string = 'php '.ROOT.'/index.php -c main -a overview -duration hourly -timemode 4';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'main_overview_hourly_4',
				'response' => exec($string),
			));
			
			//内容交付概览 上周的数据
			$string = 'php '.ROOT.'/index.php -c content -a overview -duration hourly -timemode 4';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_overview_hourly_4',
				'response' => exec($string),
			));
			
			//内容交付视频点播 上周的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoDemand -duration hourly -timemode 4';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoDemand_hourly_4',
				'response' => exec($string),
			));
			
			//内容交付视频直播 上周的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoLive -duration hourly -timemode 4';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoLive_hourly_4',
				'response' => exec($string),
			));
			
			//内容交付移动应用 上周的数据
			$string = 'php '.ROOT.'/index.php -c content -a mobile -duration hourly -timemode 4';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_mobile_hourly_4',
				'response' => exec($string),
			));
			
			//内容交付常规资源 上周的数据
			$string = 'php '.ROOT.'/index.php -c content -a http -duration hourly -timemode 4';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_http_hourly_4',
				'response' => exec($string),
			));
			$week1->stop();
		}
		
		//每个月1号，0点5分执行
		$day = date('d');
		if ($day == '01' && $hour === '00' && $minute == '05')
		{
			$month1->start();
			
			//首页上月的数据
			$string = 'php '.ROOT.'/index.php -c main -a overview -duration daily -timemode 6';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'main_overview_daily_6',
				'response' => exec($string),
			));
			
			//内容交付概览 上月的数据
			$string = 'php '.ROOT.'/index.php -c content -a overview -duration daily -timemode 6';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_overview_daily_6',
				'response' => exec($string),
			));
			
			//内容交付视频点播 上月的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoDemand -duration daily -timemode 6';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoDemand_daily_6',
				'response' => exec($string),
			));
			
			//内容交付视频直播 上月的数据
			$string = 'php '.ROOT.'/index.php -c content -a videoLive -duration daily -timemode 6';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_videoLive_daily_6',
				'response' => exec($string),
			));
			
			//内容交付移动应用 上月的数据
			$string = 'php '.ROOT.'/index.php -c content -a mobile -duration daily -timemode 6';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_mobile_daily_6',
				'response' => exec($string),
			));
			
			//内容交付常规资源 上月的数据
			$string = 'php '.ROOT.'/index.php -c content -a http -duration daily -timemode 6';
			$this->model('task_detail')->insert(array(
				'time' => date('Y-m-d H:i:s'),
				'name' => 'content_http_daily_6',
				'response' => exec($string),
			));
			$month1->stop();
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
	 * 清空所有缓存数据，重新生成
	 */
	function rebuild()
	{
		$this->model('cache')->truncate();
	}
}