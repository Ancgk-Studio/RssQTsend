<?php
/*
 * RssQTsend - 接收模块
 * Version 1.0.5
 *
 * Made by Ancgk Studio
 * @ Ski_little <ski@ancgk.com>
 */
require_once "common.php";
class getMsg
{
	public function getRss($server, $proxy, $routing, $timeout)
	{
		$common = new common();
		$routing = $common->urlCode($routing);
		$url = (strstr($server, "http://") || strstr($server, "https://") ? $server : "http://".$server)."/".$routing;
		if ($proxy == false) {
			$res = $common->httpCurl(array("url"=>$url, "timeout"=>$timeout));
		} else {
			$res = $common->httpCurl(array("url"=>$url, "proxy"=>$proxy, "timeout"=>$timeout));
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
			if ($mediaKey%2 == 1 && !empty($mediaValue) && $mediaValue != "undefined") {
				$mediaUrlList[] = $mediaValue;
			}
		}
		return $mediaUrlList;
	}
	
	public function getMsgText($text)
	{
		foreach (array("  ", "\t", "\r", "\n ", "\r\n") as $key => $value) {
			$text = str_ireplace($value, "", $text);
		}
		if (strpos($text, "RT") === 0) {
			$text = "转推了:\n".implode("", explode("RT", $text, 2));
		} elseif (strpos($text, "Re") === 0) {
			$text = "回复:\n".implode("", explode("Re", $text, 2));
		}
		$text = str_ireplace("\n", "<br />", $text);
		preg_match_all("/<ol.+?<\/ol>/", $text, $loValue);
		$listMcl = false;
		foreach ($loValue[0] as $key => $value) {
			$listNum = 1;
			$listText = "";
			preg_match_all("/<li.+?<\/li>/", $value, $listArray);
			foreach ($listArray[0] as $listKey => $listValue) {
				$listText .= str_ireplace("</li>", "\n", str_ireplace("<li>", $listNum.".", $listValue));
				$listNum++;
			}
			$listMcl[$key] = $listText;
		}
		if ($listMcl !== false) {
			$listText = "";
			$listArray = preg_split('/<ol.+?<\/ol>/', $text);
			foreach ($listArray as $key => $value) {
				$listText .= $value."\n".(!empty($listMcl[$key]) ? $listMcl[$key] : "");
			}
			$text = $listText;
		}
		foreach (array(["<img", "<br><br><img"], ["<img", "<br><img"], ["<iframe", "<br><br><iframe"], ["<video", "<br><video"], ["·", "<li>"], ["\n————————————\n", "<hr>"]) as $key => $value) {
			$text = implode($value[0], explode($value[1], $text));
		}
		foreach (array("</p>", "</li>", "</br>", "<br>", "<br />", "</div>") as $key => $value) {
			$text = str_ireplace($value, "\n", $text);
		}
		foreach (array("/<p>/", "/<div.+?>/", "/<iframe.+<\/iframe>/", "/<img.+?>/", "/<video.+<\/video>/", "/<a.+?>/", "/<\/a>/", "/<b>/", "/<\/b>/", "/<span.+?>/", "/<\/span>/", "/<h.+?>/", "/<\/h.+?>/") as $key => $value) {
			$text = implode("", preg_split($value, $text));
		}
		return $text;
	}
}
