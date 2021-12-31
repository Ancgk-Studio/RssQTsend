<?php
/*
 * RssQTsend - 发送模块
 * Version 1.0.4
 *
 * Made by Ancgk Studio
 * @ Ski_little <ski@ancgk.com>
 */
$rootDir = dirname(__FILE__);
require_once "common.php";
class sendMsg
{
	public function sand($data)
	{
		$url = "http://";
		$res = 0;
		foreach ($data["config"]["targetServer"] as $key => $value) {
			if ($value[0] == $data["info"]["targetServerClass"]) {
				$url .= $value[1]."/";
				$serverType = $value[2];
				break;
			}
		}
		if ($serverType == "skiqsend") {
			$res = $this->skiqsend($data, $url);
		} elseif ($serverType == "cqhttp") {
			$res = $this->cqhttp($data, $url);
		} else {
			return array("code"=>2,"url"=>$url);
		}
		return array("code"=>$res["code"] != 1 ? 0 : 1,"url"=>$res["url"]);
	}
	
	public function skiqsend($data, $url)
	{
		$common = new common();
		$apiUrl = $url."api/uploadMsg/?";
		$fileAmount = 0;
		if (!empty($data["info"]["token"])) {
			$apiUrl .= "token=".$data["info"]["token"];
		}
		if (count($data["file"]) > 0 && $data["mediaType"] == "video") {
			$fileAmount = 1;
		} elseif (count($data["file"]) > 0 && $data["mediaType"] == "image") {
			foreach ($data["file"] as $key => $value) {
				$fileAmount++;
			}
		}
		$msgData = "class=".$data["info"]["class"]."&channel=".$data["info"]["channel"]."&date=".$data["date"];
		$msgData .= "&link=".$data["link"]."&title=".$data["title"]."&msg=".$data["msg"]."&type=".$data["mediaType"]."&amount="."$fileAmount";
		$msgRes = $common->httpCurl(array("url"=>$apiUrl,"mode"=>"POST","data"=>$msgData));
		if ($msgRes["code"] == 0 && $msgRes["message"] == "Ok") {
			$msgRet = json_decode($msgRes["data"], true);
			if ($msgRet["data"]["id"] != "null" && count($data["file"]) > 0) {
				$fileList = array();
				$uploadUrl = $url."api/uploadMedia/?";
				if (!empty($data["info"]["token"])) {
					$uploadUrl .= "token=".$data["info"]["token"];
				}
				$uploadUrl .= "&id=".$msgRet["data"]["id"];
				if ($data["mediaType"] == "video") {
					foreach ($data["file"] as $key => $value) {
						if (explode(".", $value)[count(explode(".", $value)) - 1] == "mp4") {
							$fileList[] = str_ireplace("\\", "/", $value);
						}
					}
				} elseif ($data["mediaType"] == "image") {
					foreach ($data["file"] as $key => $value) {
						if (explode(".", $value)[count(explode(".", $value)) - 1] == "png") {
							$fileList[] = str_ireplace("\\", "/", $value);
						}
					}
				}
				$fileData = array();
				foreach ($fileList as $key => $value) {
					$fileData[] = new CURLFile($value, $data["mediaType"] == "video" ? "video/mp4" : "image/jpeg", explode("/", $value)[count(explode("/", $value)) - 1]);
				}
				$fileRes = $common->httpCurl(array("url"=>$uploadUrl,"mode"=>"POST","data"=>$fileData));
				if ($fileRes["code"] == 0 && $fileRes["message"] == "Ok" && !empty($fileRes["data"])) {
					$resJson = json_decode($fileRes["data"], true);
					if ($resJson["code"] == 0 && $resJson["msg"] == "ok") {
						return array("code"=>1,"url"=>$apiUrl);
					} else {
						return array("code"=>2,"url"=>$apiUrl);
					}
				} else {
					return array("code"=>0,"url"=>$apiUrl);
				}
			} else {
				return array("code"=>1,"url"=>$apiUrl);
			}
		} else {
			return array("code"=>0,"url"=>$apiUrl);
		}
	}
	
	public function cqhttp($data, $url)
	{
		$common = new common();
		$msgCodeText = "【".$data["title"]."】更新了！\n----------------------\n内容：\n".$data["msg"]."\n";
		if (count($data["file"]) > 0) {
			$msgCodeText .= "\n媒体：\n";
			foreach ($data["file"] as $key => $value) {
				if (explode(".", $value)[count(explode(".", $value)) - 1] == "png") {
					$file = str_ireplace("\\", "/", $value);
					$msgCodeText .= "[CQ:image,file=file:///".$file."]\n";
				}
			}
			if ($data["mediaType"] == "video") {
				$msgCodeText .= "内容包含视频文件，请打开原链接查看\n";
			}
		}
		$msgCodeText .= "----------------------\n原链接：".$data["link"]."\n\n日期：".date("Y年m月d日 H:i:s", strtotime($data["date"]))."";
		$msgCodeText = urlencode($msgCodeText);
		$url .= "send_guild_channel_msg?guild_id=".$data["info"]["class"]."&channel_id=".$data["info"]["channel"]."&message=".$msgCodeText;
		
		if (!empty($data["info"]["token"])) {
			$url .= "&access_token=".$data["info"]["token"];
		}
		$res = $common->httpCurl(array("url"=>$url));
		if ($res["code"] == 0 && $res["message"] == "Ok" && !empty($res["data"])) {
			$resJson = json_decode($res["data"], true);
			if ($resJson["retcode"] == 0 && $resJson["status"] == "ok") {
				return array("code"=>1,"url"=>$url);
			} else {
				return array("code"=>2,"url"=>$url);
			}
		} else {
			return array("code"=>0,"url"=>$url);
		}
	}
}
