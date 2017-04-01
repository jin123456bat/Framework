<?php
namespace framework\vendor;

class image extends file
{
	/**
	 * 获取图像类型
	 * 1	IMAGETYPE_GIF
	 * 2	IMAGETYPE_JPEG
	 * 3	IMAGETYPE_PNG
	 * 4	IMAGETYPE_SWF
	 * 5	IMAGETYPE_PSD
	 * 6	IMAGETYPE_BMP
	 * 7	IMAGETYPE_TIFF_II（Intel 字节顺序）
	 * 8	IMAGETYPE_TIFF_MM（Motorola 字节顺序）
	 * 9	IMAGETYPE_JPC
	 * 10	IMAGETYPE_JP2
	 * 11	IMAGETYPE_JPX
	 * 12	IMAGETYPE_JB2
	 * 13	IMAGETYPE_SWC
	 * 14	IMAGETYPE_IFF
	 * 15	IMAGETYPE_WBMP
	 * 16	IMAGETYPE_XBM
	 * @return number
	 */
	function imageType()
	{
		return exif_imagetype($this->path());
	}
	/**
	 * 获取图片的宽度
	 *
	 * @return int
	 */
	function width()
	{
		return imagesx($this->path());
	}

	/**
	 * 获取图片的高度
	 */
	function height()
	{
		return imagesy($this->path());
	}

	/**
	 * 更改图片的高度或者宽度
	 *
	 * @param int $height
	 *        	高度 当$height<1的时候为按比例压缩高度
	 * @param int $width
	 *        	宽度 当$width<1的时候为按比例压缩宽度
	 * @param
	 *        	boolean [optinal] $strict = false 是否保持高宽比
	 */
	function resize($height, $width, $strict = false)
	{
	}

	/**
	 * 图片增加水印
	 *
	 * @param string $string
	 *        	文字或者图片路径
	 */
	function water($string, $pos_x, $pos_y)
	{
		if ($string instanceof file)
		{
			// 文件来处理
			$path = $string->path();
		}
		else if (is_file($string))
		{
			// 文件来处理
		}
		else
		{
			// 文字来处理
		}
	}

	/**
	 * 裁剪
	 */
	function cut($width, $height, $pos_x, $pos_y)
	{
	}
}
