## MIDL Framework

**Reasons why I've developed MIDL micro framework:**
- I don't want to spend too many times to learn a new framework 
which has totally different coding style to develop a simple website. For example most of them have
their own syntax/concepts for ORM, template engines etc. I just want to use pure PHP.
- No complex routing needed: in many applications I never need complex routing. 
	Mostly simple key-value mapping is enough for routing of my applications.
- I am developing this micro framework since 2009 and used in many web applications 
	and I found it very easy to develop simple applications (e.g. a quiz application). Though I improved it
	a lot since then.

**Requirements:**
- PHP 5.5+ with mcrypt extension
- PDO: PDO extension and [PDO driver](http://php.net/manual/pdo.drivers.php) for your database of choice must be enabled

**Features:**
- Events: events are powerful feature which let you build extensible and maintainable rich applications
- Logger: if you see a problem at application just check log files most probably you will see the problem there (if configured correctly)
- Internationalisation: build multilingual applications
- AssetLoader: Minify on the fly, dynamic asset loader allows you to load only necessary JS and CSS files for each page request.
	This will let you build lightweight applications.
	Also optionally you can combine files into one file to reduce server load.
	When you work in agency environment the main problem is that you need to do some modifications any time
	even during out of office time, so you have to do modifications when you are at home or outside to the CSS or JS files,
	it means you don't have access to office computer and you can't use normal application deployment - auto deploy to server -, 
	you just need to make a simple change to CSS or JS
	and re-upload to server. 
	(sometimes you work on multiple projects at the same time) 
	It means you cant minify and/or combine files. It was a big problem for me but with AssetManager you don't worry about
	all of these. Just do modification and upload to server. At the first request to the modified file it will be minified and cached for subsequent requests.
	(Please note that it is not best practice to minify assets on the fly, use build tools as much as you can)
- Theme based allows you use different layouts in one application, 
	e.g. login and register pages without header and footer 
	while other pages have header and footers and admin pages has totally different layout.
- Image resizing on the fly, build dynamic ui while saving space and performance


## Installation

Simply run [Composer](https://getcomposer.org/) install command to install dependencies of the project:

```sh
composer install
```

## Try it

Simply try it out at <http://localhost>.

## Unit Tests

All Unit Tests are located under /test directory.

## Contribution

This project is under heavy development so any contributions are welcome.

## License

MIDL framework is [MIT](http://opensource.org/licenses/MIT) licensed.


