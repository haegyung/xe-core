<?php
/**
 * @author sol (sol@nhn.com)
 * @brief css 및 js Optimizer 처리 gateway
 *
 **/

if(!$_GET['t'] || !$_GET['l']) exit;

// set env
$XE_PATH = substr(dirname(__FILE__),0,strlen('common')*-1);
define('_XE_PATH_', $XE_PATH);
define('__ZBXE__', true);
define('__XE_LOADED_CLASS__', true);
include _XE_PATH_ . 'config/config.inc.php';
include _XE_PATH_ . 'files/config/db.config.php';
include _XE_PATH_ . 'classes/handler/Handler.class.php';
include _XE_PATH_ . 'classes/cache/CacheHandler.class.php';

$oCacheHandler = new CacheHandler('template', $db_info);
$cache_support = $oCacheHandler->isSupport();

$XE_WEB_PATH = substr($XE_PATH,strlen($_SERVER['DOCUMENT_ROOT']));
if(substr($XE_WEB_PATH,-1) != "/") $XE_WEB_PATH .= "/";
$cache_path = $XE_PATH . 'files/cache/optimized/';
$type = $_GET['t'];
$list_file = $cache_path . $_GET['l'];


function getRealPath($file){
	if($file{0}=='.' && $file{1} =='/') $file = _XE_PATH_.substr($file, 2);
	return $file;
}

function getMtime($file){
	$file = getRealPath($file);
	if(file_exists($file)) return filemtime($file);
}

function getMaxMtime($list){
	$mtime = array();
	foreach($list as $file) $mtime[] = getMtime($file);
	return max($mtime);
}

// check
if($cache_support){
	$list = $oCacheHandler->get($_GET['l']);
	$mtime = getMaxMtime($list);
}else{
	if(!file_exists($list_file)) exit;
	$list = include($list_file);
	$mtime = getMaxMtime(array_merge($list,array($list_file)));
}
if(!is_array($list)) exit;


// set content-type
if($type == '.css'){
	$content_type = 'text/css';
} else if($type == '.js') {
	$content_type = 'text/javascript';
}

header("Content-Type: ".$content_type."; charset=UTF-8");
// return 304
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $modifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($modifiedSince && ($modifiedSince == $mtime)) {
		header('HTTP/1.1 304 Not Modified');
		header("Connection: close");
		exit;
    }
}
function useContentEncoding(){ 
	if( (defined('__OB_GZHANDLER_ENABLE__') && __OB_GZHANDLER_ENABLE__ == 1) 
		&& strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')!==false 
		&& function_exists('ob_gzhandler') 
		&& extension_loaded('zlib')) {
		return true;
	}
	return false;
}

function getCacheKey($list){
	global $cache_support;
	$content_encoding = useContentEncoding();
	$key = 'optimized:' . join('',$list); 
	$key .= $content_encoding?'gzip':'none';
	return $key;
}

function printFileList($list){
	global $mtime, $cache_support, $oCacheHandler;

	$content_encoding = useContentEncoding();
    $output = null;

	if($cache_support){
		$cache_key = getCacheKey($list);
		$output = $oCacheHandler->get($cache_key, $mtime);
	}
	
	if(!$output || trim($output)==''){
		for($i=0,$c=count($list);$i<$c;$i++){
			$file = getRealPath($list[$i]);
			if(file_exists($file)){
				$output .= file_get_contents($file);
				$output .= "\n";
			}
		}

		if($content_encoding) $output = ob_gzhandler($output, 5);
	}

	if($cache_support) $oCacheHandler->put($cache_key, $output);

	$size = strlen($output);

	if($size > 0){
		header("Cache-Control: private, max-age=2592000"); 
		header("Pragma: cache"); 
		header("Connection: close"); 
		header("Last-Modified: " . substr(gmdate('r', $mtime), 0, -5). "GMT");
		header("ETag: \"". md5(join(' ', $list)) .'-'. dechex($mtime) .'-'.dechex($size)."\""); 
	}

	header("Content-Length: ". $size); 

	if($content_encoding) header("Content-Encoding: gzip");

	echo $output;
}

function write($file_name, $buff, $mode='w'){
	$file_name = getRealPath($file_name);
	if(@!$fp = fopen($file_name,$mode)) return false;
	fwrite($fp, $buff);
	fclose($fp);
	@chmod($file_name, 0644);
}

function read($file_name) {
	$file_name = getRealPath($file_name);

	if(!file_exists($file_name)) return;
	$filesize = filesize($file_name);
	if($filesize<1) return;

	if(function_exists('file_get_contents')) return file_get_contents($file_name);

	$fp = fopen($file_name, "r");
	$buff = '';
	if($fp) {
		while(!feof($fp) && strlen($buff)<=$filesize) {
			$str = fgets($fp, 1024);
			$buff .= $str;
		}
		fclose($fp);
	}
	return $buff;
}

function makeCacheFileCSS($css_file, $cache_file, $return=false){
	$str = read($css_file);
	$str = replaceCssPath($css_file, trim(convertEncodingStr($str)));

	if($return){
		return $str;
	}else{
		write($cache_file, $str."\n");
		unset($str);
	}
}

function replaceCssPath($file, $str) {
	global $tmp_css_path;

	// css 파일의 위치를 구함
	$tmp_css_path = preg_replace("/^\.\//is","",dirname($file))."/";
	// url() 로 되어 있는 css 파일의 경로를 변경
	$str = preg_replace_callback('/url\(([^\)]*)\)/is', '_replaceCssPath', $str);

	// charset 지정 문구를 제거
	$str = preg_replace('!@charset([^;]*?);!is','',$str);

	return $str;
}

function _replaceCssPath($matches) {
	global $tmp_css_path, $XE_WEB_PATH;

	$path = str_replace(array('"',"'"),'',$matches[1]);
	if(substr($path,0,1)=='/' || strpos($path,'://')!==false || strpos($path,'.htc')!==false) return 'url('.$path.')';
	if(substr($path,0,2)=='./') $path = substr($path,2);
	$target = $XE_WEB_PATH.$tmp_css_path.$path;
	while(strpos($target,'/../')!==false) {
		$target = preg_replace('/\/([^\/]+)\/\.\.\//','/',$target);
	}

	return 'url('.$target.')';
}

function convertEncodingStr($str) {
	$charset_list = array(
			'UTF-8', 'EUC-KR', 'CP949', 'ISO8859-1', 'EUC-JP', 'SHIFT_JIS', 'CP932',
			'EUC-CN', 'HZ', 'GBK', 'GB18030', 'EUC-TW', 'BIG5', 'CP950', 'BIG5-HKSCS',
			'ISO2022-CN', 'ISO2022-CN-EXT', 'ISO2022-JP', 'ISO2022-JP-2', 'ISO2022-JP-1',
			'ISO8859-6', 'ISO8859-8', 'JOHAB', 'ISO2022-KR', 'CP1255', 'CP1256', 'CP862',
			'ASCII', 'ISO8859-1', 'ISO8850-2', 'ISO8850-3', 'ISO8850-4', 'ISO8850-5',
			'ISO8850-7', 'ISO8850-9', 'ISO8850-10', 'ISO8850-13', 'ISO8850-14',
			'ISO8850-15', 'ISO8850-16', 'CP1250', 'CP1251', 'CP1252', 'CP1253', 'CP1254',
			'CP1257', 'CP850', 'CP866',
			);

	for($i=0;$i<count($charset_list);$i++) {
		$charset = $charset_list[$i];
		if($str){
			$cstr = iconv($charset,$charset,$str);
			if($str == $cstr);
			return $cstr;
		}
	}

	return $str;
}

if($type == '.js'){
	printFileList($list);
}else if($type == '.css'){

	if($cache_support){
		foreach($list as $file){
			$cache_file = $cache_path . md5($file);
			$css[] = getRealPath($cache_file);
		}

		$cache_key = getCacheKey($css);
		$buff = $oCacheHandler->get($cache_key, $mtime);
		if(!$buff){
			$buff = '';
			$css = array();
			foreach($list as $file){
				$cache_file = $cache_path . md5($file);
				$buff .= makeCacheFileCSS($file, getRealPath($cache_file), true);
				$css[] = getRealPath($cache_file);
			}

			$oCacheHandler->put($cache_key, $buff);
		}

	}else{
		foreach($list as $file){
			$cache_file = $cache_path . md5($file);
			$cache_mtime = getMtime($cache_file);
			$css_mtime = getMtime($file);

			// check modified
			if($css_mtime > $cache_mtime){
				makeCacheFileCSS($file, getRealPath($cache_file));
			}
			$css[] = getRealPath($cache_file);
		}

	}

	printFileList($css);
}
?>
