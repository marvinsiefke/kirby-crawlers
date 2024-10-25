
# kirby-crawlers
kirby-crawlers is a very lightweight Kirby plugin (tested with Kirby 4) for providing sitemap.xml, sitemap.txt and robots.txt. The plugin also notifies Bing via IndexNow if an api key is given.

## Demo
You can see some exemplary results here:

 - https://pepper.green/sitemap.xml
 - https://pepper.green/sitemap.txt
 - https://pepper.green/robots.txt

## Configuration

It is recommended to define a sitemap collection like this in site/collections/sitemap.php:

    return function ($site) {
      return $site->pages()->index()->filterBy('noindex', '!=', 'enabled');
    };

You can read more in Kirby’s documentation: https://getkirby.com/docs/guide/templates/collections.

Otherwise the plugin uses a fallback:

    site()->pages()->index();

You can also set up custom rules for the robots.txt and define the IndexNow key in your config.php:

    'pepper.crawlers' => [
    	'robots' => [
    		'disallow' => ['/panel/', '/backend/'],
    		'allow' => ['/', '/public/'],
    		'custom' => ['Crawl-delay: 10']
    	],
    	'indexnow' => [
    		'key' => '9e7e357659434acf91e78430729ecad7',
    		// 'api' => 'https://www.bing.com/indexnow'
    	]
    ],

You can generate your api key here: https://www.bing.com/indexnow/getstarted#implementation
