<?php
/*
 * RssQTsend - 公共模块
 * Version 1.0.1
 *
 * Made by Ancgk Studio
 * @ Ski_little <ski@ancgk.com>
 */
class common
{
	public function log($log, $logDir = "")
	{
		$log = date("Y-m-d H:i:s")." ".$log."\n";
		echo($log);
		if (!empty($logDir)) {
			$logContent = "";
			if (file_exists($logDir."/".date("Y-m-d").".log")) {
				$logContent = file_get_contents($logDir."/".date("Y-m-d").".log");
			}
			$logContent .= $log;
			file_put_contents($logDir."/".date("Y-m-d").".log", $logContent);
		}
	}
	
	public function getFileList($path)
	{
		$fileArr = array();
		$data = scandir($path);
		foreach ($data as $value) {
			if ($value != '.' && $value != '..') {
				$fileArr[] = $value;
			}
		}
		return $fileArr;
	}
	
	public function httpCurl($data)
	{
		if (!(strstr($data["url"], "http://") || strstr($data["url"], "https://"))) {
			return array("code"=>500,"message"=>"Is not a valid agreement");
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $data["url"]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, 1);
		if (!empty($data["proxy"])) {
			$address = explode(":", $data["proxy"]);
			curl_setopt($ch, CURLOPT_PROXY, $address[0]);
			curl_setopt($ch, CURLOPT_PROXYPORT, $address[1]);
		}
		if (!empty($data["mode"]) && !empty($data["data"]) && $data["mode"] == "POST") {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data["data"]);
		}
		$res = curl_exec($ch);
		if (curl_errno($ch)) {
			return array("code"=>curl_errno($ch),"message"=>"Request failed");
		}
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		return array("code"=>0, "message"=>"Ok", "httpCode"=>$httpCode, "data"=>$res);
	}
	
	public function numberCoded($text)
	{
		$uNum = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
		$vNum = array("１", "２", "３", "４", "５", "６", "７", "８", "９", "０");
		foreach ($uNum as $key => $value) {
			$text = str_ireplace($value, $vNum[$key], $text);
		}
		return $text;
	}
}
