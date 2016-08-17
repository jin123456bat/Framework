<?php
namespace vendor;

class image extends file
{
	/**
	 * 获取图片的宽度
	 * @return int
	 */
	function width()
	{
		
	}
	
	/**
	 * 获取图片的高度
	 */
	function height()
	{
		
	}
	
	/**
	 * 更改图片的高度或者宽度
	 * @param int $height 高度 当$height<1的时候为按比例压缩高度
	 * @param int $width 宽度 当$width<1的时候为按比例压缩宽度
	 * @param boolean [optinal] $strict = false 是否保持高宽比
	 */
	function resize($height,$width,$strict = false)
	{
	}
	
	/**
	 * 图片增加水印
	 */
	function water()
	{
		
	}
}