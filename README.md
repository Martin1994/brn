Battle Royale Next-gen
===

No I18N available. Sorry for that.

BRN 是完全重构的 Battle Royale 游戏引擎，遵循 cc-by-sa 3.0 开源。BRN 无法单独运行，需要 mod 配合，代码中自带 Battle Royale Advanced+ mod （还原 BRA 的游戏方式），但此 mod 很少用到 BRN 所提供的新特性。官方提供 thbr mod 其中使用了绝大多数 BRN 的新特性。

支持 mysql 与 mongodb 两种数据库，其中 mysql 支持 PDO 与原生库。

全面依赖推送（差一点点就做成上下行分离了啊..），在纯 PHP 环境中服务端会轮询缓存来模拟事件驱动的效果。

可以使用文件缓存或 memcache。

支持 SAE 也可以在标准 LAMP 环境下跑。
