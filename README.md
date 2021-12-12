# RssQTsend
将订阅的RSS消息通过机器人发送到QQ频道

## 使用方法

配置受支持的QQ机器人框架 (目前支持 skiqsend 和 [go-cqhttp](https://github.com/Mrs4s/go-cqhttp/))

将 `config.json` 对应 [配置文件描述](#配置文件描述) 按需求修改

若你使用 [发行版](https://github.com/skilittle/RssQTsend/releases) 则直接运行 `start.bat` 即可

若你直接 clone 此仓库请见 [使用方法(源码)](#使用方法源码)

## 配置文件描述

使用 cqhttp 时 `频道信息` 为对应的 [`频道ID`](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md#%E6%94%B6%E5%88%B0%E9%A2%91%E9%81%93%E6%B6%88%E6%81%AF) ，`子频道` 信息为对应的 [`子频道ID`](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md#%E6%94%B6%E5%88%B0%E9%A2%91%E9%81%93%E6%B6%88%E6%81%AF)

```

	"rssServer": "127.0.0.1:1200", # 填写 rss 服务器地址 默认端口为80 使用SSL时请自行添加443端口 例如 rsshub.app:443
	"proxyServer": "127.0.0.1:1080", # 填写获取图片时使用代理的代理地址 留空则直连
	"cycle": 60000, # 每次获取 Rss 消息的时间间隔 以毫秒为单位
	"errorResendCycle": 3000, # 发送错误后重试时间间隔 以毫秒为单位
	"log": true, # 日志
	"msgCache": 50, # 每个订阅的最高缓存数量
	"fileCache": 7, # 视频与图片的最高缓存天数 若填写为7则7天后的文件会被删除
	"MaximumNumberRetries": 5, # 最高重试次数
	"targetServer": [ # 填写服务器列表
		# ["服务器名", "服务器地址", "服务器类型 目前支持skiqsend与cqhttp"]
		["ski", "127.0.0.1:8081", "skiqsend"]
		["cqhttp", "127.0.0.1:5700", "cqhttp"]
	],
	"subscription": [{
		"routing": "twitter/user/ski_little", # rss消息地址
		"limitAmount": 20, # 一次性获取条数
		"send": [{ # 发送参数
			"targetServerClass": "ski", # 填写在 targetServer 中配置完成的 "服务器名"
			"printErrorUrl": true, # 使用 cqhttp 服务器时若出现错误则可以将此项调为 true , 会显示发送消息所用的 URL 以便进行调试
			"Retweet": true, # 是否发送转推 (推特专用)
			"ErrorResend": true, # 发送失败时是否尝试重新发送
			"class": "00000000000000000", # 填写 频道信息
			"channel": "1234567" # 填写子频道信息
		}]
	}, {
		"routing": "bilibili/user/dynamic/144630821",
		"limitAmount": 20,
		"send": [{
			"targetServerClass": "cqhttp",
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

```php.exe .\bin\main.php -c .\config.json```
