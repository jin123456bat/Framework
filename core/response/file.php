<?php
namespace framework\core\response;

use framework\core\response;
use framework\core\request;

class file extends response
{
	private static $_mime_type = array(
		'shtml' => 'text/html',
		'html' => 'text/html',
		'htm' => 'text/html',
		'css' => 'text/css',
		'xml' => 'text/xml',
		'gif' => 'image/gif',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'js' => 'application/x-javascript',
		'atom' => 'application/atom+xml',
		'rss' => 'application/rss+xml',
		'mml' => 'text/mathml',
		'txt' => 'text/plain',
		'jad' => 'text/vnd.sun.j2me.app-descriptor',
		'wml' => 'text/vnd.wap.wml',
		'htc' => 'text/x-component',
		'png' => 'image/png',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'wbmp' => 'image/vnd.wap.wbmp',
		'ico' => 'image/x-icon',
		'jng' => 'image/x-jng',
		'bmp' => 'image/x-ms-bmp',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
		'webp' => 'image/webp',
		'jar' => 'application/java-archive',
		'war' => 'application/java-archive',
		'ear' => 'application/java-archive',
		'hqx' => 'application/mac-binhex40',
		'doc' => 'application/msword',
		'pdf' => 'application/pdf',
		'ps' => 'application/postscript',
		'eps' => 'application/postscript',
		'ai' => 'application/postscript',
		'rtf' => 'application/rtf',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'wmlc' => 'application/vnd.wap.wmlc',
		'kml' => 'application/vnd.google-earth.kml+xml',
		'kmz' => 'application/vnd.google-earth.kmz',
		'7z' => 'application/x-7z-compressed',
		'cco' => 'application/x-cocoa',
		'jardiff' => 'application/x-java-archive-diff',
		'jnlp' => 'application/x-java-jnlp-file',
		'run' => 'application/x-makeself',
		'pl' => 'application/x-perl',
		'pm' => 'application/x-perl',
		'prc' => 'application/x-pilot',
		'pdb' => 'application/x-pilot',
		'rar' => 'application/x-rar-compressed',
		'rpm' => 'application/x-redhat-package-manager',
		'sea' => 'application/x-sea',
		'swf' => 'application/x-shockwave-flash',
		'sit' => 'application/x-stuffit',
		'tcl' => 'application/x-tcl',
		'tk' => 'application/x-tcl',
		'der' => 'application/x-x509-ca-cert',
		'pem' => 'application/x-x509-ca-cert',
		'crt' => 'application/x-x509-ca-cert',
		'xpi' => 'application/x-xpinstall',
		'xhtml' => 'application/xhtml+xml',
		'zip' => 'application/zip',
		'bin' => 'application/octet-stream',
		'exe' => 'application/octet-stream',
		'dll' => 'application/octet-stream',
		'deb' => 'application/octet-stream',
		'dmg' => 'application/octet-stream',
		'eot' => 'application/octet-stream',
		'iso' => 'application/octet-stream',
		'img' => 'application/octet-stream',
		'msi' => 'application/octet-stream',
		'msp' => 'application/octet-stream',
		'msm' => 'application/octet-stream',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'kar' => 'audio/midi',
		'mp3' => 'audio/mpeg',
		'ogg' => 'audio/ogg',
		'm4a' => 'audio/x-m4a',
		'ra' => 'audio/x-realaudio',
		'3gpp' => 'video/3gpp',
		'3gp' => 'video/3gpp',
		'mp4' => 'video/mp4',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mov' => 'video/quicktime',
		'webm' => 'video/webm',
		'flv' => 'video/x-flv',
		'm4v' => 'video/x-m4v',
		'mng' => 'video/x-mng',
		'asx' => 'video/x-ms-asf',
		'asf' => 'video/x-ms-asf',
		'wmv' => 'video/x-ms-wmv',
		'avi' => 'video/x-msvideo',
	);
	
	/**
	 * 文件路径
	 * @var string
	 */
	private $_path;
	
	/**
	 * 文件名，包含后缀
	 * @var string
	 */
	private $_basename;
	
	/**
	 * 文件后缀，不包含.
	 * @var string
	 */
	private $_extension;

	/**
	 * @param string|file $file
	 * @param bool $download  默认为false
	 * @param string $download_name 下载的时候的文件名 默认为文件本身的名称
	 * 当为false的时候 由浏览器自动判断，例如pdf文件浏览器可能直接显示出来，而不是期望中的下载，当为true的时候强制为下载
	 */
	function __construct($file,$download = false,$download_name = '')
	{
		parent::__construct();
		
		if (is_file($file))
		{
			$this->_path = $file;
			$pathinfo = pathinfo($this->_path);
			$this->_basename = $pathinfo['basename'];
			$this->_extension = $pathinfo['extension'];
		}
		else if ($file instanceof \framework\vendor\file)
		{
			$this->_path = $file->path();
			$this->_basename = $file->basename();
			$this->_extension = $file->extension();
		}
		
		$resource = fopen($file, 'rb');
		
		if ($download)
		{
			$this->setHeader(array(
				'Content-Type: application/force-download',
				'Cache-Control: must-revalidate, post-check=0, pre-check=0',
				'Content-Transfer-Encoding: binary',
				'Expires:0',
				'Pragma: no-cache',
			));
			
			if (empty($download_name))
			{
				$this->setHeader('Content-Disposition','attachment;filename='.$this->_basename);
			}
			else
			{
				if (stripos('.', $download_name) === false)
				{
					$download_name .= '.'.$this->_extension;
				}
				$this->setHeader('Content-Disposition','attachment;filename='.$download_name);
			}
		}
		else 
		{
			if (isset(self::$_mime_type[$this->_extension]))
			{
				$this->setHeader('Content-Type',self::$_mime_type[$this->_extension]);
			}
		}
		
		$range = request::header('Range');
		if (! empty($range))
		{
			// Range: bytes=5275648-
			
			// Range: bytes=0-499 下载第0-499字节范围的内容
			// Range: bytes=500-999 下载第500-999字节范围的内容
			// Range: bytes=-500 下载最后500字节的内容
			// Range: bytes=500- 下载从第500字节开始到文件结束部分的内容
			
			list ($start, $end) = sscanf(strtolower($range), "bytes=%s-%s");
			
			$start = trim($start);
			$end = trim($end);
			
			$contents = '';
			if ($start == '0' && ! empty($end))
			{
				$contents = fread($resource, $end);
			}
			else if (! empty($start) && $start != '0' && ! empty($end))
			{
				fseek($resource, $start);
				$contents = fread($resource, $end - $start);
			}
			else if ($start == '' && ! empty($end))
			{
				fseek($resource, - $end, SEEK_SET);
				$contents = fread($resource, $end);
			}
			else if ($end == '' && ! empty($start))
			{
				fseek($resource, $start);
				while (! feof($resource))
				{
					$contents .= fread($resource, 8192);
				}
			}
			
			$this->setBody($contents);
			$this->setHttpStatus(206);
			
			// Content-Range: bytes 5275648-15143085/15143086
			// Content-Length: 9867438
			$this->setHeader('Content-Range', 'bytes ' . self::setVariableType($start, 'i') . '-' . self::setVariableType($end, 'i') . '/' . filesize($this->_path) + 1);
			//$this->setHeader('Content-Length', self::setVariableType($end, 'i') - self::setVariableType($start, 'i') + 1);
		}
		else
		{
			$this->setBody(file_get_contents($this->_path));
			$this->setHttpStatus(200);
		}
		fclose($resource);
	}
}