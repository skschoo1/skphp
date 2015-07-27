# skphp
A simple PHP framework


SKPHP
│ index.php           入口文件 
│ application         应用目录
│　├─ common          公共配置函数目录
│　│　　 ├─ common    公共函数目录
│　│　　 └─ conf      公共配置目录
│　├─ controller      控制器目录 
│　│　　 └─  // ....
│　├─ model           模型目录
│　│　　 └─  // ....
│　├─ view            视图目录
│　│　　 └─  // ....
│　├─ cache           项目缓存目录
│　│　　 ├─ data      数据文件缓存
│　│　　 ├─ logs      错误记录缓存
│　│　　 └─ tpl       模板缓存	
│ skphp               核心目录
│　├─ common          核心函数
│　├─ conf            核心配置文件
│　├─ library         核心类库
│　│　　 ├─ myclass   自定义类
│　│　　 └─ sk        核心类库
│　└─ tpl             系统模板文件
