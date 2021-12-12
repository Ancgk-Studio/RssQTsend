<?php
/*
 * RssQTsend - 公共模块
 * Version 1.0.0
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
		curl_close($ch);
		return array("code"=>0,"message"=>"Ok","data"=>$res);
	}
}
