<?php
use framework\view\button;
use framework\view\html;
use framework\view\div;
use framework\view\body;
use framework\view\head;
use framework\view\link;
use framework\view\script;
use framework\view\meta;

return new html(
	array(
		new head(
			array(
				new meta('X-UA-Compatible','IE=edge',true),
				new link('/a/b/1.css'),
				new link('/a/b/2.css'),
				new script('/a/b/c/1.js'),
				new script('/a/b/c/2.js'),
				new script('/a/b/c/3.js'),
			)
		),
		new body(
			array(
				new div(
					array(
						new button('按钮1',array(
							'style' => array(
								'border-color' => 'red',
								'border-width' => '1px',
								'border-style' => 'solid',
							)
						)),
						new button('按钮2'),
					),
					array(
						'class' => 'header',
					)
				),
				new div(array(
					new button('按钮3'),
					new button('按钮4'),
				),array(
					'class' => 'body',
				)),
			)
		)
	)
);
