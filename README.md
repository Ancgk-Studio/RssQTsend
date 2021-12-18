# RssQTsend
将订阅的RSS消息通过机器人发送到QQ频道

## 支持

##### 支持的rss服务器
* Rsshub
* 部分网站的rss链接 例如[https://www.hanmoto.com](https://www.hanmoto.com)

##### 支持的机器人框架
* [go-cqhttp](https://github.com/Mrs4s/go-cqhttp/)
* skiqsend

##### 支持解析的Rss内容
* bilibili用户动态(bilibili/user/dynamic/)
* Twitter推文(twitter/user/)
* 百度贴吧(tieba/forum/)
* www.hanmoto.com的rss链接

如果有其他需要支持的请在issue中提出

## 使用方法

将 `config.json` 对应 [配置文件描述](#配置文件描述) 按需求修改

若你使用 [发行版](https://github.com/skilittle/RssQTsend/releases) 则直接运行 `start.bat` 即可

若你直接 clone 此仓库请见 [使用方法(源码)](#使用方法源码)

## 配置文件描述

使用 cqhttp 时 `频道信息` 为对应的 [`频道ID`](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md#%E6%94%B6%E5%88%B0%E9%A2%91%E9%81%93%E6%B6%88%E6%81%AF) ，`子频道` 信息为对应的 [`子频道ID`](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md#%E6%94%B6%E5%88%B0%E9%A2%91%E9%81%93%E6%B6%88%E6%81%AF)

```
{
	"rssServer": "http://127.0.0.1:1200", # 填写 rss 服务器地址默认为http80和https443端口 , 其他端口请自行添加 例如 https://rsshub.app或http://xxxx.xx:1200
	"proxyServer": "127.0.0.1:1080", # 填写代理服务器地址 , 删除该参数将直连
	"cycle": 60000, # 每次获取 Rss 消息的时间间隔 以毫秒为单位
	"errorResendCycle": 3000, # 发送错误后重试时间间隔 以毫秒为单位
	"log": true, # 启用日志
	"rssProxy": false, # 允许Rss服务器通过代理连接
	"msgCache": 50, # 每个订阅的最大缓存消息条数
	"fileCache": 7, # 视频与图片的最大缓存天数 若填写为7则7天后的文件会被删除
	"MaximumNumberRetries": 5, # 发送错误后最大重试次数
	"targetServer": [ # 远程服务器列表
	# ["服务器名", "服务器地址", "服务器类型 目前支持skiqsend与cqhttp"]
		["ski", "127.0.0.1:8081", "skiqsend"]
	],
	"subscription": [{
		"routing": "twitter/user/ski_little", # rss消息路径
		"limitAmount": 20, # 一次性获取条数 , 部分rss可能不支持
		"send": [{
			"targetServerClass": "ski", # 填写在 targetServer 中配置的 "服务器名"
			"printErrorUrl": true, # 使用 cqhttp 服务器时若出现错误则可以将此项调为 true , 会在发送失败后显示发送消息所用的 URL 以便进行调试
			"Retweet": true, # 是否发送转推 (推特专用)
			"ErrorResend": true, # 发送失败时是否尝试重新发送
			"class": "00000000000000000", # 填写 频道信息
			"channel": "1234567", # 填写 子频道信息
			"token":"A8597E5630FFE8A6531B" # 如果服务器需要验证token需要填写对应的token , 没有则删除改行
		}]
	}, {
		"routing": "bilibili/user/dynamic/144630821",
		"limitAmount": 20,
		"send": [{
			"targetServerClass": "ski",
			"printErrorUrl": true,
			"Retweet": true,
			"ErrorResend": true,
			"class": "00000000000000000",
			"channel": "1234567"
		}]
	}, {
		"routing": "tieba/forum/请问您今天要来点兔子吗", # 百度贴吧rss不支持limitAmount , 请将它删除防止出现错误
		"numberCoded": true, # 由于cqhttp奇怪的bug , 可以通过启用数字全角转换来防止发送失败
		"send": [{
			"targetServerClass": "ski",
			"printErrorUrl": true,
			"Retweet": true,
			"ErrorResend": true,
			"class": "00000000000000000",
			"channel": "1234567"
		}]
	}]
}
```

## 使用方法(源码)

若你直接 clone 仓库运行，则需要自己准备一个PHP环境

1. [下载 PHP](https://www.php.net/downloads.php)
2. 在 `php.ini` 中启用 `curl` 与 `gd2` 扩展
3. 在程序根目录中使用如下命令行运行

```php.exe .\bin\main.php```
