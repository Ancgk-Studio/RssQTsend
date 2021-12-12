<?php
/*
 * RssQTsend - 主程序
 * Version 1.0.5
 *
 * Made by Ancgk Studio
 * @ Ski_little <ski@ancgk.com>
 */
$rootDir = dirname(__FILE__);
require_once "common.php";
require_once "getMsg.php";
require_once "sandMsg.php";
$common = new common();
$getMsg = new getMsg();
$sandMsg = new sandMsg();
$configFile = file_get_contents($rootDir."/../config.json");
$configArr = json_decode($configFile, true);
$logDir = $rootDir."/../log";
$common->log("[I] RssQTsend 当前版本:v0.0.2", $configArr["log"] ? $logDir : "");
if (!empty($configArr["proxyServer"])) {
	while (true) {
		$res = $common->httpCurl(array("url"=>"http://".$configArr["proxyServer"]));
		if ($res["code"] == 0 && $res["message"] == "Ok") {
			$common->log("[I] 连接代理服务器 ".$configArr["proxyServer"]." 成功", $configArr["log"] ? $logDir : "");
			break;
		} else {
			$common->log("[E] 连接代理服务器 ".$configArr["proxyServer"]." 失败", $configArr["log"] ? $logDir : "");
			sleep(3);
		}
	}
}
$remoteServer[] = $configArr["rssServer"]."/twitter/user/ski_little";
foreach ($configArr["targetServer"] as $key => $value) {
	$remoteServer[] = $value[1];
}
foreach ($remoteServer as $key => $value) {
	while (true) {
		$res = $common->httpCurl(array("url"=>"http://".$value));
		if ($res["code"] == 0 && $res["message"] == "Ok") {
			$common->log("[I] 连接远程服务器 ".$value." 成功", $configArr["log"] ? $logDir : "");
			break;
		} else {
			$common->log("[E] 连接远程服务器 ".$value." 失败", $configArr["log"] ? $logDir : "");
			sleep(3);
		}
	}
}
while (true) {
	foreach ($configArr["subscription"] as $key => $value) {
		$routingName = str_ireplace("/", "_", $value["routing"]);
		if (!file_exists($rootDir."/../data/subscription/".$routingName.".xml")) {
			$common->log("[W] ".$routingName." 的缓存不存在，程序将自动创建", $configArr["log"] ? $logDir : "");
			$xmlFile = new DOMDocument("1.0", "utf-8");
			$xmlFile->preserveWhiteSpace = false;
			$xmlFile->formatOutput = true;
			$xmlFile->appendChild($xmlFile->createElement('xml'));
			$xmlFile->save($rootDir."/../data/subscription/".$routingName.".xml");
		} else {
			$xmlFile = new DOMDocument("1.0", "utf-8");
			$xmlFile->preserveWhiteSpace = false;
			$xmlFile->formatOutput = true;
			$xmlFile->load($rootDir."/../data/subscription/".$routingName.".xml");
		}
		$rootDoc = $xmlFile->getElementsByTagName("xml")->item(0);
		if (count($rootDoc->getElementsByTagName("item")) == 0) {
			$oldLink = "";
		} else {
			$oldLink = $rootDoc->getElementsByTagName("item")->item(0)->getElementsByTagName("link")->item(0)->nodeValue;
		}
		$rssContent = $getMsg->getRss($configArr["rssServer"], $value["routing"], $value["limitAmount"]);
		if ($rssContent === false || empty($rssContent)) {
			$common->log("[W] 获取 ".$routingName." 的Rss订阅内容失败", $configArr["log"] ? $logDir : "");
		} elseif (!empty($rssContent)) {
			$saveRssContent = array();
			foreach ($rssContent->getElementsByTagName("item") as $docKey => $docValue) {
				if ((function ($rootDoc, $docValue) {
					foreach ($rootDoc->getElementsByTagName("item") as $tempFileKey => $tempFileValue) {
						if ($docValue->getElementsByTagName("link")->item(0)->nodeValue == $tempFileValue->getElementsByTagName("link")->item(0)->nodeValue) {
							return true;
						}
					}
				})($rootDoc, $docValue) == true) {
					break;
				} else {
					$author = empty($docValue->getElementsByTagName("author")->item(0)->nodeValue) ? $rssContent->getElementsByTagName("title")->item(0)->nodeValue : $docValue->getElementsByTagName("author")->item(0)->nodeValue;
					$moDoc = array(
						"description"=>$xmlFile->createElement("description"),
						"link"=>$xmlFile->createElement("link", $docValue->getElementsByTagName("link")->item(0)->nodeValue),
						"date"=>$xmlFile->createElement("date", date("Y-m-d H:i:s", strtotime($docValue->getElementsByTagName("pubDate")->item(0)->nodeValue))) ,
						"author"=>$xmlFile->createElement("author", $author),
						"state"=>$xmlFile->createElement("state", "none")
					);
					$cdata = $xmlFile->createCDATASection($docValue->getElementsByTagName("description")->item(0)->nodeValue);
					$moDoc["description"]->appendChild($cdata);
					array_unshift($saveRssContent, $moDoc);
				}
			}
			foreach ($saveRssContent as $rssKey => $rssValue) {
				$xinDoc = $xmlFile->createElement("item");
				foreach ($rssValue as $moKey => $moValue) {
					$xinDoc->appendChild($moValue);
				}
				$rootDoc->appendChild($xinDoc);
				if (count($rootDoc->getElementsByTagName("item")) > $configArr["msgCache"]) {
					$rootDoc->removeChild($rootDoc->firstChild);
				}
			}
			if (count($saveRssContent) > 0) {
				$xmlFile->save($rootDir."/../data/subscription/".$routingName.".xml");
			}
			if ($docKey > 0) {
				foreach ($saveRssContent as $msgKey => $msgValue) {
					$description = $msgValue["description"]->nodeValue;
					$mediaUrl = $getMsg->getMediaUrl($description);
					$mediaFiles = array();
					$mediaType = "null";
					foreach ($mediaUrl as $mediaKey => $mediaValue) {
						while (true) {
							if (!empty($configArr["proxyServer"])) {
								$mediaFile = $common->httpCurl(array("url"=>$mediaValue,"proxy"=>$configArr["proxyServer"]));
							} else {
								$mediaFile = $common->httpCurl(array("url"=>$mediaValue));
							}
							$fileName = explode("/", $mediaValue);
							$fileName = explode("?", $fileName[count($fileName) - 1]);
							$fileName = explode(".", $fileName[0]);
							$file = "";
							if (count($fileName) == 1 || $fileName[1] == "jpg" || $fileName[1] == "png") {
								$file = $fileName[0].".png";
								if ($mediaFile["code"] == 0 && $mediaFile["message"] == "Ok") {
									$mediaType = "image";
									$imageRes = imagecreatefromstring($mediaFile["data"]);
									imagepng($imageRes, $rootDir."/../data/temp/".$file);
								}
							} else {
								$file = $fileName[0].".".$fileName[1];
								if ($mediaFile["code"] == 0 && $mediaFile["message"] == "Ok") {
									$mediaType = "video";
									file_put_contents($rootDir."/../data/temp/".$file, $mediaFile["data"]);
								}
							}
							if ($mediaFile["code"] == 0 && $mediaFile["message"] == "Ok" && file_exists($rootDir."/../data/temp/".$file)) {
								$mediaFiles[] = dirname($rootDir)."/data/temp/".$file;
								$tempFiles = $common->getFileList($rootDir."/../data/temp");
								foreach ($tempFiles as $tempFileKey => $tempFileValue) {
									$tempFileTime = filemtime($rootDir."/../data/temp/".$tempFileValue);
									if (time() - $tempFileTime >= ($configArr["fileCache"] * 24 * 60 * 60)) {
										unlink($rootDir."/../data/temp/".$tempFileValue);
									}
								}
								break;
							} else {
								$common->log("[W] 下载媒体文件 ".$file." 失败", $configArr["log"] ? $logDir : "");
								sleep(1);
							}
						}
						$common->log("[I] 下载媒体文件 ".$file." 完成", $configArr["log"] ? $logDir : "");
					}
					foreach ($value["send"] as $sendKey => $sendValue) {
						if ($sendValue["Retweet"] == false && strpos($description, "RT") === 0) {
							$sendStu = false;
						} else {
							$sendStu = true;
						}
						$sendMsgs = 1;
						while ($sendStu == true) {
							$msgSand = $sandMsg->sand(array(
								"mediaType"=>$mediaType,
								"title"=>$rssContent->getElementsByTagName("title")->item(0)->nodeValue,
								"msg"=>$getMsg->getMsgText($description),
								"config"=>$configArr,
								"info"=>$sendValue,
								"file"=>$mediaFiles,
								"date"=>$msgValue["date"]->nodeValue,
								"link"=>$msgValue["link"]->nodeValue
							));
							if ($sendValue["ErrorResend"] == false) {
								$sendStu = false;
							}
							if ($msgSand["code"] == 1) {
								$common->log("[I] 将订阅的 ".$routingName." 发送到 [".$sendValue["class"]."] [".$sendValue["channel"]."] 成功", $configArr["log"] ? $logDir : "");
								$sendStu = false;
							} elseif ($msgSand["code"] == 2) {
								$common->log("[E] 不支持的机器人服务器类型", $configArr["log"] ? $logDir : "");
							} else {
								$common->log("[W] 将订阅的 ".$routingName." 发送到 [".$sendValue["class"]."] [".$sendValue["channel"]."] 失败", $configArr["log"] ? $logDir : "");
							}
							if ($sendMsgs >= $configArr["MaximumNumberRetries"]) {
								$common->log("[E] 多次尝试重发失败,将跳过该条消息", $configArr["log"] ? $logDir : "");
								if ($sendValue["printErrorUrl"] == true) {
									echo("如有需要,可使用此Url尝试手动重发: ".$msgSand["url"]);
								}
								$sendStu = false;
							}
							$sendMsgs++;
							sleep($configArr["errorResendCycle"]/1000);
						}
					}
				}
			}
		}
	}
	sleep($configArr["cycle"]/1000);
}
