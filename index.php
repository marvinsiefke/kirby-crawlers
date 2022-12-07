<?php

Kirby::plugin('pepper/bots', [
	'snippets' => [
		'sitemap' => __DIR__ . '/snippets/sitemap.php'
	],
	'routes' => [
		// sitemap.xml
		[
			'pattern' => 'sitemap.xml',
			'action'  => function() {
				$pages = kirby()->collections()->has('sitemap') ? kirby()->collection('sitemap') : site()->pages()->index();
				$content = snippet('sitemap', compact('pages'), true);
				return new Kirby\Cms\Response($content, 'application/xml');
			}
		],
		// sitemap.txt
		[
			'pattern' => 'sitemap.txt',
			'action' => function () {
				$pages = kirby()->collections()->has('sitemap') ? kirby()->collection('sitemap') : site()->pages()->index();
				$content = [];
				foreach ($pages as $page) {
					$content[] = $page->url();
				}
				return new Kirby\Cms\Response(join("\n", $content), 'text/plain');
			},
		],
		// sitemap redirection
		[
			'pattern' => 'sitemap',
			'action' => function () {
				return go('sitemap.txt', 301);
			},
		],
		// robots.txt
		[
			'pattern' => 'robots.txt',
			'action' => function () {
				$content = [
					'User-agent: *',
					'Disallow: /panel/',
					'Allow: /',
					'Sitemap: '.kirby()->url().'/sitemap.xml'
				];
				return new Kirby\Cms\Response(join("\n", $content), 'text/plain');
			},
		]
	],
]);
