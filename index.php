<?php

Kirby::plugin('pepper/crawlers', [
	'snippets' => [
		'sitemap' => __DIR__ . '/snippets/sitemap.php'
	],

	'routes' => [
		// robots.txt
		[
			'pattern' => 'robots.txt',
			'action' => function () {
				$robotsConfig = kirby()->option('pepper.crawlers.robots', []);
				$disallowRules = array_merge($robotsConfig['disallow'] ?? ['/panel/'], []);
				$allowRules = array_merge($robotsConfig['allow'] ?? ['/'], []);
				$content = ['User-agent: *'];
				
				foreach ($disallowRules as $rule) {
					$content[] = 'Disallow: ' . $rule;
				}
				
				foreach ($allowRules as $rule) {
					$content[] = 'Allow: ' . $rule;
				}
				
				if (isset($robotsConfig['custom']) && is_array($robotsConfig['custom'])) {
					$content = array_merge($content, $robotsConfig['custom']);
				}
				
				$content[] = 'Sitemap: ' . kirby()->url() . '/sitemap.xml';
				return new Kirby\Cms\Response(implode("\n", $content), 'text/plain');
			}
		],

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
				return new Kirby\Cms\Response(implode("\n", $content), 'text/plain');
			}
		],

		// sitemap redirection
		[
			'pattern' => 'sitemap',
			'action' => function () {
				return go('sitemap.xml', 301);
			}
		],

		// indexnow key file
		[
			'pattern' => '(:any).txt',
			'action' => function($givenKey) {
				$apiKey = kirby()->option('pepper.crawlers.indexnow.key', '');

				if (!empty($apiKey) && $apiKey === $givenKey) {
					return new Response($apiKey, 'text/plain');
				}
			}
		]
	],

	'pageMethods' => [
		'indexnow' => function () {
			$apiKey = kirby()->option('pepper.crawlers.indexnow.key', '');
			$apiUrl = kirby()->option('pepper.crawlers.indexnow.api', 'https://www.bing.com/indexnow');

			if(!empty($apiKey) && !kirby()->option('debug') && $this->isPublished()) {
				if (!kirby()->collections()->has('sitemap') || (kirby()->collections()->has('sitemap') && kirby()->collection('sitemap')->has($this))) {
					$pageUrl = $this->url();
					$siteUrl = site()->url();

					try {
						$response = Remote::request($apiUrl, [
							'method' => 'POST',
							'headers' => ['Content-Type' => 'application/json'],
							'data' => json_encode([
								'host' => parse_url($siteUrl, PHP_URL_HOST),
								'key' => $apiKey,
								'keyLocation' => $siteUrl . '/' . $apiKey . '.txt',
								'urlList' => [$pageUrl]
							])
						]);
						return $response->code() === 200;
					} catch (Exception $e) {
						return false;
					}
				}
			}
			return false;
		}
	],

	'hooks' => [
		// indexnow nofication
		'page.create:after' => function ($page) {
			$page->indexnow();
		},
		'page.update:after' => function ($newPage, $oldPage) {
			$newPage->indexnow();
		}
	]
]);
