<?php
/*
 * RssQTsend - 公共模块
 * Version 1.0.2
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
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		if (!empty($data["proxy"])) {
			$address = explode(":", $data["proxy"]);
			curl_setopt($ch, CURLOPT_PROXY, $address[0]);
			curl_setopt($ch, CURLOPT_PROXYPORT, $address[1]);
		}
		if (!empty($data["timeout"])) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $data["timeout"]);
		}
		if (!empty($data["mode"]) && !empty($data["data"]) && $data["mode"] == "POST") {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data["data"]);
		}
		$res = curl_exec($ch);
		if (curl_errno($ch)) {
			return array("code"=>curl_errno($ch),"message"=>"Request failed");
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
	
	public function urlCode($text)
	{
		$rawLink = explode("?", $text, 2);
		$routing = explode("/", $rawLink[0]);
		foreach ($routing as $key => $value) {
			$routing[$key] = urlencode($value);
		}
		if ($routing[1] == "") {
			$routing[0] = urldecode($routing[0]);
			$routing[2] = urldecode($routing[2]);
		} elseif (strstr($routing[0], ".")) {
			$routing[0] = urldecode($routing[0]);
		}
		$routing = implode("/", $routing);
		if (!empty($rawLink[1])) {
			$routing .="?";
			$getDataLink = explode("&", $rawLink[1]);
			foreach ($getDataLink as $key => $value) {
				if ($key != 0) {
					$routing .= "&";
				}
				$getAtt = explode("=", $value);
				foreach ($getAtt as $getKey => $getValue) {
					if ($getKey%2 == 1) {
						$routing .= urlencode($getAtt[$getKey - 1])."=".urlencode($getAtt[$getKey]);
					}
				}
			}
		}
		return $routing;
	}
	
	public function html_decode($text)
	{
		foreach ([['\'', '&apos;']] as $key => $value) {
			$text = implode($value[0], explode($value[1], $text));
		}
		return $text;
	}
}
