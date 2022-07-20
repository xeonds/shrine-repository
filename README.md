# 神社的储物间

自用博客系统。同时作为PHP学习记录。

[点击访问](http://mxts.jiujiuer.xyz/pages/repo/index.php)|[源码下载（密码90xa）](https://dreamweb.lanzoui.com/b016lkrfg)

之前用WordPress那会**用的主题有点问题，导致没法评论，而且数据迁移比较麻烦，同时感觉mysql略有笨重**，这才下决心换成了hexo。然而安装上之后，发现空间占用貌似比较大（现在看来应该是node缓存之类的），而且也有各种不尽人意的地方。正巧那会刚接触PHP，在用PHP升级我以前的一个静态站（神社的储物间，全是纯手写的html）。于是就决定把博客合并到储物间那个静态站里，顺便也是为了学习PHP。  

于是就越写越多，成了这个博客（~~屎山~~）。

## 特性

作为一个不怎么喜欢数据库的人，我没有用任何数据库。我转向了markdown，并借鉴了hexo的魔改md思想（雾），把文章信息、文章内容、评论区都塞到了一个markdown文件里。

- 无数据库，开箱即用
- 支持markdown（不过是类似于hexo那样魔改的格式）
- 在线编辑(很简陋，后面完善下)
- 文章管理、数据统计（总字数，文章总数、标签数。后面添加更多信息）
- 文章评论（数据以特殊格式保存在该文章尾部）

最近想重构一下，融合了点想法：

- 元：使用新的方式组织内容。整体上保持按照文件目录组织内容的方式，同时每个目录下添加`config.json`，存储元数据，每个目录都作为一个元来管理
- 前后端分离：之前使用了自己编写的简单的模板系统，整体依旧是服务端渲染。现在想要前后端分离，不过这样的话逻辑就需要大规模重写了
- Git：主要是馋它的push/pull功能。平时不用打开网站后台，使用自己的IDE就能完成笔记，想想就爽。版本管理啥的无所谓了

## 使用教程

网站自带后台管理，密码为1234。后台管理登录入口默认显示。`post`为文章以及文件的根目录。文件、文章同等管理。要新增文章，只需要把内容放在该目录下即可。

## 开发计划

- 搜索
- 主题
- 目录无极嵌套

## 更新日志

- 2022.02.27 之前写的实在太烂了，回头重构下  
- 2022.03.21 新分支建立了。以后发个7.x的release再合并吧
