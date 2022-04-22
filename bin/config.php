<?php
/*
 * RssQTsend - 配置文件
 * Version 1.0.1
 *
 * Made by Ancgk Studio
 * @ Ski_little <ski@ancgk.com>
 */
$rootDir = dirname(__FILE__);
require_once "common.php";
class config
{
	private function sendArr()
	{
		return array(
			"printErrorUrl"=>true,
			"targetServerClass"=>"test",
			"Retweet"=>true,
			"ErrorResend"=>true,
			"class"=>"00000000",
			"channel"=>"00000",
			"token"=>"0bc10be7288542bb41783f039eff2624"
		);
	}
	
	private function subArr()
	{
		return array(
			"subscriptionName"=>"twitter_user_ski_little",
			"rssServer"=>"http://127.0.0.1:1200",
			"routing"=>"twitter/user/ski_little",
			"filter"=>[],
			"omit"=>300,
			"send"=>$this->sendArr()
		);
	}
	
	public function mainArr($v)
	{
		return array(
			"version"=>$v,
			"log"=>true,
			"proxyServer"=>"127.0.0.1:1080",
			"cycle"=>1000,
			"errorResendCycle"=>3000,
			"rssProxy"=>false,
			"msgCache"=>50,
			"fileCache"=>1,
			"downloadTimeout"=>30,
			"maximumSend"=>5,
			"maximumDownload"=>5,
			"targetServer"=>[["test", "127.0.0.1:5700", "cqhttp"]],
			"filter"=>[],
			"subscription"=>$this->subArr()
		);
	}
}
