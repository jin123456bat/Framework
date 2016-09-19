<?php
namespace application\control;
use application\extend\BaseControl;
use framework\core\response\json;

/**
 * 内容交付相关接口
 * @author fx
 *
 */
class content extends BaseControl
{
	/**
	 * 概览
	 */
	function overview()
	{
		$data = array(
			//分类型服务流速堆叠
			'category_service' => array(
				'http'=> array(
					
				),
				'mobile' => array(
					
				),
				'video_demand' => array(
					
				),
				'video_live' => array(
					
				)
			),
			//分类型回源流速堆叠
			'category_cache' => array(
				'http' => array(
					
				),
				'mobile' => array(
					
				),
				'video_demand' => array(
					
				),
				'video_live' => array(
					
				),
			),
			//流速对比
			'flow' => array(
				'total' => array(
					'service' => 0,
					'cache' => 0,
				),
				'http' => array(
					
				),
				'mobile' => array(
					
				),
				'video_demand' => array(
					
				),
				'video_live' => array(
					
				),
			),
			//资源热榜
			'topfile' => array(
				'http'=>array(
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),				
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
				),
				'mobile'=>array(
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
				),
				'video_live'=>array(
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
				),
				'video_demand'=>array(
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','host'=>'站点','filesize'=>'文件大小','service_size'=>'服务流量'),
				)
			)
		);
		return new json(json::OK,NULL,$data);
	}
	
	/**
	 * 视频点播
	 */
	function videoDemand()
	{
		
	}
	
	/**
	 * 视频直播
	 */
	function videoLive()
	{
		$data = array(
			//分CP服务流速堆叠
			'cp_service_flow' => array(
				'YY直播' => array(
					
				),
				'虎牙直播' => array(
					
				),
				'其他' => array(
					
				),
			),
			//分CP缓存服务堆叠
			'cp_cache_flow' => array(
				'YY直播' => array(
					'timenode' => 0,	
				),
				'虎牙直播' => array(
						
				),
				'其他' => array(
						
				),
			),
			//流量对比
			'cp_cache_service_sum' => array(
				'总流量' => array(
					'service' => 0,
					'cache' => 0,
				),
				'YY直播' => array(
					'service' => 0,
					'cache' => 0,
				),
				'虎牙直播' => array(
					'service' => 0,
					'cache' => 0,
				)
			),
			//资源热榜
			'topfile' => array(
				'熊猫' => array(
					array('filename' => '文件名','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','filesize'=>'文件大小','service_size'=>'服务流量'),
				),
				'虎牙直播' => array(
					array('filename' => '文件名','filesize'=>'文件大小','service_size'=>'服务流量'),
					array('filename' => '文件名','filesize'=>'文件大小','service_size'=>'服务流量'),
				),
			),
			//流速
			'flow' => array(
				'熊猫' => array(
					'service' => array(
						'timenode' => 0,
					),
					'cache' => array(
						'timenode' => 0,
					)
				),
				'虎牙直播' => array(
					'service' => array(
						'timenode' => 0,
					),
					'cache' => array(
						'timenode' => 0,
					)
				),
			),
			//流量
			'flow_sum' => array(
				'熊猫' => array(
					'service' => '服务流量',
					'cache' => '回源流量',
				),
				'虎牙直播' => array(
					'service' => '服务流量',
					'cache' => '回源流量',
				)
			)
		);
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 移动应用
	 */
	function mobile()
	{
		
	}
	
	/**
	 * 常规资源
	 */
	function http()
	{
		
	}
}