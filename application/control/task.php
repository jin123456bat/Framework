<?php
namespace application\control;

use application\extend\bgControl;

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
		
		
		$starttime = date('Y-m-d H:i:s',time());
		
		$minute = date('i');
		//每5分钟执行
		if ($minute%5 === 0)
		{
			//首页 最近24小时的数据
			$string = 'php '.ROOT.'\index.php -c main -a overview -duration minutely -timemode 1';
			exec($string);
			
			//内容交付概览  最近24小时的数据
			$string = 'php '.ROOT.'\index.php -c content -a overview -duration minutely -timemode 1';
			exec($string);
			//内容交付视频点播 最近24小时的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoDemand -duration minutely -timemode 1';
			exec($string);
			//内容交付视频直播 最近24小时的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoLive -duration minutely -timemode 1';
			exec($string);
			//内容交付移动应用 最近24小时的数据
			$string = 'php '.ROOT.'\index.php -c content -a mobile -duration minutely -timemode 1';
			exec($string);
			//内容交付常规资源 最近24小时的数据
			$string = 'php '.ROOT.'\index.php -c content -a http -duration minutely -timemode 1';
			exec($string);
		}
		
		//每小时的5分钟执行一次
		if ($minute == '05')
		{
		}
		
		//每2小时的5分钟执行一次
		$hour = date('H');
		if ($hour%2===0 && $minute=='05')
		{
			
		}
		
		//每天的05分钟执行一次
		if ($hour === '00' && $minute == '05')
		{
			//首页 昨天的数据
			$string = 'php '.ROOT.'\index.php -c main -a overview -duration minutely -timemode 2';
			exec($string);
			//首页近7天的数据
			$string = 'php '.ROOT.'\index.php -c main -a overview -duration hourly -timemode 3';
			exec($string);
			//首页近30天的数据
			$string = 'php '.ROOT.'\index.php -c main -a overview -duration daily -timemode 5';
			exec($string);
			
			
			//内容交付概览  昨天的数据
			$string = 'php '.ROOT.'\index.php -c content -a overview -duration minutely -timemode 2';
			exec($string);
			//内容交付视频点播 昨天的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoDemand -duration minutely -timemode 2';
			exec($string);
			//内容交付视频直播 昨天的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoLive -duration minutely -timemode 2';
			exec($string);
			//内容交付移动应用 昨天的数据
			$string = 'php '.ROOT.'\index.php -c content -a mobile -duration minutely -timemode 2';
			exec($string);
			//内容交付常规资源 昨天的数据
			$string = 'php '.ROOT.'\index.php -c content -a http -duration minutely -timemode 2';
			exec($string);
			
			
			//内容交付概览 7天的数据
			$string = 'php '.ROOT.'\index.php -c content -a overview -duration hourly -timemode 3';
			exec($string);
			//内容交付视频点播 7天的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoDemand -duration hourly -timemode 3';
			exec($string);
			//内容交付视频直播 7天的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoLive -duration hourly -timemode 3';
			exec($string);
			//内容交付移动应用 7天的数据
			$string = 'php '.ROOT.'\index.php -c content -a mobile -duration hourly -timemode 3';
			exec($string);
			//内容交付常规资源 7天的数据
			$string = 'php '.ROOT.'\index.php -c content -a http -duration hourly -timemode 3';
			exec($string);
			
			
			//内容交付概览 30天的数据
			$string = 'php '.ROOT.'\index.php -c content -a overview -duration daily -timemode 5';
			exec($string);
			//内容交付视频点播 30天的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoDemand -duration daily -timemode 5';
			exec($string);
			//内容交付视频直播 30天的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoLive -duration daily -timemode 5';
			exec($string);
			//内容交付移动应用 30天的数据
			$string = 'php '.ROOT.'\index.php -c content -a mobile -duration daily -timemode 5';
			exec($string);
			//内容交付常规资源 30天的数据
			$string = 'php '.ROOT.'\index.php -c content -a http -duration daily -timemode 5';
			exec($string);
		}
		
		//每个星期一的0点5分执行一次
		$week = date('N');
		if ($week == 1 && $hour === '00' && $minute === '05')
		{
			//首页上周的数据
			$string = 'php '.ROOT.'\index.php -c main -a overview -duration hourly -timemode 4';
			exec($string,$output,$return_var);
			
			//内容交付概览 上周的数据
			$string = 'php '.ROOT.'\index.php -c content -a overview -duration hourly -timemode 4';
			exec($string);
			//内容交付视频点播 上周的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoDemand -duration hourly -timemode 4';
			exec($string);
			//内容交付视频直播 上周的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoLive -duration hourly -timemode 4';
			exec($string);
			//内容交付移动应用 上周的数据
			$string = 'php '.ROOT.'\index.php -c content -a mobile -duration hourly -timemode 4';
			exec($string);
			//内容交付常规资源 上周的数据
			$string = 'php '.ROOT.'\index.php -c content -a http -duration hourly -timemode 4';
			exec($string);
		}
		
		//每个月1号，0点5分执行
		$day = date('d');
		if ($day == '01' && $hour === '00' && $minute == '05')
		{
			//首页上月的数据
			$string = 'php '.ROOT.'\index.php -c main -a overview -duration daily -timemode 6';
			exec($string);
			//内容交付概览 上月的数据
			$string = 'php '.ROOT.'\index.php -c content -a overview -duration daily -timemode 6';
			exec($string);
			//内容交付视频点播 上月的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoDemand -duration daily -timemode 6';
			exec($string);
			//内容交付视频直播 上月的数据
			$string = 'php '.ROOT.'\index.php -c content -a videoLive -duration daily -timemode 6';
			exec($string);
			//内容交付移动应用 上月的数据
			$string = 'php '.ROOT.'\index.php -c content -a mobile -duration daily -timemode 6';
			exec($string);
			//内容交付常规资源 上月的数据
			$string = 'php '.ROOT.'\index.php -c content -a http -duration daily -timemode 6';
			exec($string);
		}
		
		$endtime = date('Y-m-d H:i:s',time());
		file_put_contents(ROOT.'/task_log.log', $starttime.'-'.$endtime."\r\n",FILE_APPEND);
	}
	
	/**
	 * 清空所有缓存数据，重新生成
	 */
	function rebuild()
	{
		$this->model('cache')->truncate();
	}
}