<?php
/*
 * RssQTsend - 接收模块
 * Version 1.0.3
 *
 * Made by Ancgk Studio
 * @ Ski_little <ski@ancgk.com>
 */
require_once "common.php";
class getMsg
{
	public function getRss($server, $proxy, $routing, $limit)
	{
		$common = new common();
		$routing = explode("/", $routing);
		$routing[count($routing) - 1] = urlencode($routing[count($routing) - 1]);
		$routing = implode("/", $routing);
		$url = (strstr($server, "http://") || strstr($server, "https://") ? $server : "http://".$server)."/".$routing.($limit != false ? "?limit=".$limit : "");
		if ($proxy == false) {
			$res = $common->httpCurl(array("url"=>$url));
		} else {
			$res = $common->httpCurl(array("url"=>$url, "proxy"=>$proxy));
		}
		if ($res["code"] == 0 && $res["message"] == "Ok" && strpos($res["data"], "</rss>")) {
			$doc = new DOMDocument("1.0", "utf-8");
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;
			$doc->loadXML($res["data"]);
			return $doc;
		} else {
			return false;
		}
	}
	
	public function getMediaUrl($text)
	{
		preg_match_all("/<img.+?>/", $text, $imgList);
		preg_match_all("/<video.+<\/video>/", $text, $videoList);
		$mediaList = implode("", $imgList[0]).implode("", $videoList[0]);
		$mediaList = implode("", preg_split('/src=" <a.+?>/', $mediaList));
		preg_match_all('/src=".+?"|poster=".+?"/', $mediaList, $mediaList);
		$mediaList = explode('"', implode("", $mediaList[0]));
		$mediaUrlList = array();
		foreach ($mediaList as $mediaKey => $mediaValue) {
			if ($mediaKey%2 == 1) {
				$mediaUrlList[] = $mediaValue;
			}
		}
		return $mediaUrlList;
	}
	
	public function getMsgText($text)
	{
		if (strpos($text, "RT") === 0) {
			$text = "转推了:\n".implode("", explode("RT", $text, 2));
		} elseif (strpos($text, "Re") === 0) {
			$text = "回复:\n".implode("", explode("Re", $text, 2));
		}
		$text = implode("<img", explode("<br><br><img", $text));
		$text = implode("<img", explode("<br><img", $text));
		$text = implode("<iframe", explode("<br><br><iframe", $text));
		$text = implode("<video", explode("<br><video", $text));
		$texted = str_ireplace("<br>", "\n", $text);
		$texted = str_ireplace("</p>", "\n", $texted);
		$texted = str_ireplace("<br />", "\n", $texted);
		$texted = implode("", preg_split("/<p>/", $texted));
		$texted = implode("", preg_split("/<iframe.+<\/iframe>/", $texted));
		$texted = implode("", preg_split("/<img.+?>/", $texted));
		$texted = implode("", preg_split("/<video.+<\/video>/", $texted));
		$texted = implode("", preg_split("/<a.+?>/", $texted));
		$texted = implode("", preg_split("/<\/a>/", $texted));
		return $texted;
	}
}
