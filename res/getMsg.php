<?php
/*
 * RssQTsend - 接收模块
 * Version 1.0.8
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
		if ($res["code"] == 0 && $res["message"] == "Ok" && strpos($res["data"], "</rss>") !== false) {
			$doc = new DOMDocument("1.0", "utf-8");
			$doc->preserveWhiteSpace = false;
			$doc->formatOutput = true;
			$doc->loadXML($res["data"]);
			return $doc;
		} elseif ($res["code"] == 0 && $res["message"] == "Ok" && strpos($res["data"], "</feed>") !== false) {
			return $this->atomConversion($res["data"]);
		} else {
			return false;
		}
	}
	
	public function atomConversion($data)
	{
		$common = new common();
		$doc = new DOMDocument("1.0", "utf-8");
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($data);
		$nowDoc = new DOMDocument("1.0", "utf-8");
		$nowDoc->preserveWhiteSpace = false;
		$nowDoc->formatOutput = true;
		$rssRoot = $nowDoc->createElement("rss");
		$rssRoot->setAttribute("version", "2.0");
		$rssRoot->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
		$channel = $nowDoc->createElement("channel");
		foreach ([
			["title",$doc->getElementsByTagName("title")->item(0)->nodeValue],
			["link",$doc->getElementsByTagName("link")->item(0)->getAttribute('href')],
			["description", $doc->getElementsByTagName("subtitle")->item(0)->nodeValue],
			["pubDate", $doc->getElementsByTagName("updated")->item(0)->nodeValue],
			["lastBuildDate", $doc->getElementsByTagName("updated")->item(0)->nodeValue],
			["generator", "RssQTsend"],
			["language","en-US"]
		] as $key => $value) {
			$channelInfo = $nowDoc->createElement($value[0], $value[1]);
			$channel->appendChild($channelInfo);
		}
		foreach ($doc->getElementsByTagName("entry") as $key => $value) {
			$channelItem = $nowDoc->createElement("item");
			foreach ([
				["title", $value->getElementsByTagName("title")->item(0)->nodeValue],
				["link", $value->getElementsByTagName("link")->item(0)->getAttribute('href')],
				["guid", hash("sha256", $value->getElementsByTagName("link")->item(0)->getAttribute('href').(time())), ["isPermaLink", "false"]],
				["pubDate", $value->getElementsByTagName("published")->item(0)->nodeValue]
			] as $itemKey => $itemValue) {
				$cdata = $nowDoc->createCDATASection($itemValue[1]);
				$ItemInfo = $nowDoc->createElement($itemValue[0]);
				$ItemInfo->appendChild($cdata);
				if (!empty($itemValue[2])) {
					$ItemInfo->setAttribute($itemValue[2][0], $itemValue[2][1]);
				}
				$channelItem->appendChild($ItemInfo);
			}
			$contentDoc = $nowDoc->createElement("description");
			$content = html_entity_decode($value->getElementsByTagName("content")->item(0)->nodeValue, ENT_QUOTES, "UTF-8");
			$content = html_entity_decode($content, ENT_QUOTES, "UTF-8");
			$content = $common->html_decode($content);
			$contentCDATA = $nowDoc->createCDATASection($content);
			$contentDoc->appendChild($contentCDATA);
			$channelItem->appendChild($contentDoc);
			$channel->appendChild($channelItem);
		}
		$rssRoot->appendChild($channel);
		$nowDoc->appendChild($rssRoot);
		return $nowDoc;
	}
	
	public function getMediaUrl($text)
	{
		$mediaUrlList = array();
		preg_match_all("/<img.+?>/", $text, $imgList);
		preg_match_all("/<video.+<\/video>/", $text, $videoList);
		foreach ([$imgList, $videoList] as $nodeKey => $nodeValue) {
			foreach ($nodeValue[0] as $srcKey => $srcValue) {
				if (!preg_match_all('/src=" <a.+?>/', $srcValue)) {
					preg_match_all('/src=".+?"/', $srcValue, $mediaUrl);
					$mediaUrl = str_replace("&amp;","&",explode('"', implode("", $mediaUrl[0])));
					if ($mediaUrl[1] != "undefined" && !empty($mediaUrl[1])) {
						$mediaUrlList[] = [$mediaUrl[1], $nodeKey == 0 ? "image" : "video"];
					}
					if (preg_match_all('/poster=".+?"/', $srcValue, $mediaUrl)) {
						$mediaUrl = str_replace("&amp;","&",explode('"', implode("", $mediaUrl[0])));
						$mediaUrlList[] = [$mediaUrl[1], "image"];
					}
				}
			}
		}
		return $mediaUrlList;
	}
	
	public function getMsgText($text)
	{
		$common = new common();
		$text = html_entity_decode($text, ENT_QUOTES, "UTF-8");
		$text = html_entity_decode($text, ENT_QUOTES, "UTF-8");
		$text = $common->html_decode($text);
		foreach (array("  ", "\t", "\r", "\n ", "\r\n") as $key => $value) {
			$text = str_ireplace($value, "", $text);
		}
		if (strpos($text, "RT") === 0) {
			$text = "转推了:\n".implode("", explode("RT", $text, 2));
		} elseif (strpos($text, "Re") === 0) {
			$text = "回复:\n".implode("", explode("Re", $text, 2));
		}
		$text = str_ireplace("\n", "<br />", $text);
		preg_match_all("/<ol.*?<\/ol>/", $text, $loValue);
		$listMcl = false;
		foreach ($loValue[0] as $key => $value) {
			$listNum = 1;
			$listText = "";
			preg_match_all("/<li.*?<\/li>/", $value, $listArray);
			foreach ($listArray[0] as $listKey => $listValue) {
				$listText .= str_ireplace("</li>", "\n", str_ireplace("<li>", $listNum.".", $listValue));
				$listNum++;
			}
			$listMcl[$key] = $listText;
		}
		if ($listMcl !== false) {
			$listText = "";
			$listArray = preg_split('/<ol.*?<\/ol>/', $text);
			foreach ($listArray as $key => $value) {
				$listText .= $value."\n".(!empty($listMcl[$key]) ? $listMcl[$key] : "");
			}
			$text = $listText;
		}
		foreach (array(["<img", "<br><br><img"], ["<img", "<br><img"], ["<iframe", "<br><br><iframe"], ["<video", "<br><video"], ["·", "<li>"],
		["\n————————————\n", "<hr>"], ["\n————————————\n", "<small>"], ["\n————————————\n", "<tr>"], ["\n————————————\n", "</tr>"], ["|", "<td>"],
		 ["|", "</td>"]) as $key => $value) {
			$text = implode($value[0], explode($value[1], $text));
		}
		foreach (array("</p>", "</li>", "</br>", "<br>", "<br />", "<br/>", "</div>") as $key => $value) {
			$text = str_ireplace($value, "\n", $text);
		}
		foreach (array("/<p.*?>/", "/<div.*?>/", "/<iframe.+<\/iframe>/", "/<img.*?>/", "/<video.+<\/video>/", "/<a.*?>/", "/<\/a>/", "/<b>/", "/<\/b>/",
		"/<span.*?>/", "/<\/span>/", "/<h.*?>/", "/<\/h.*?>/", "/<\/small>/", "/<strong.*?>/", "/<\/strong>/", "/<table>/", "/<\/table>/") as $key => $value) {
			$text = preg_replace($value, "", $text);
		}
		return $text;
	}
}
