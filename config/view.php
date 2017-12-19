<?php
return array(
	// (这里的模板名称是指出layout后面的部分)
	'compress' => false, // 页面是否开启压缩，假如是数组的话 是指在指定的模板才开启压缩 压缩和不压缩同时配置 优先压缩 因为这并不会有特大的性能开销
	'no_compress' => array(), // 可以在这里配置不压缩的模板名称
	
	'layout' => 'default', //布局文件夹名称
	
	'engine' => 'regp', //使用模板引擎的名称  默认regp  可以是php
);