<?php
/**
 * basePath: is the relative path to the document root
 * minPath: is the path for minified assets. Assets will be minified on the fly and cached.
 * files: list of asset files, please note that only alphanumeric, - (dash) and . (dot)
 *        characters are allowed for file aliases.
 * dependencies: list of dependencies for aliases, dependencies will be loaded before dependent file
 *               and in order of their array position.
 */
return [
    "css" => [
        "basePath" => "assets/css/",
        "minPath" => "assets/min/",
        "files" => [
            "base" => "",
            "bs" => "vendor/bootstrap/bootstrap.min.css",
            "fa" => "vendor/fontawesome/css/font-awesome.min.css",
            "style" => "style.css",
            "tether" => "vendor/tether/tether-theme-basic.min.css",
        ],
        "dependencies" => [
            "base" => ["tether", "bs", "fa", "style"]
        ]
    ],
    
    "js" => [
        "basePath" => "assets/js/",
        "minPath" => "assets/min/",
        "files" => [
            "base" => "",
            "app" => "app.js",
            "bs" => "vendor/bootstrap/bootstrap.min.js",
            "filters" => "filters/filters.js",
            "jq" => "vendor/jquery/jquery-2.2.4.min.js",
            "ng" => "vendor/angular/angular.min.js",
            // {LANG} will be replaced with requested language code
            "ng-locale" => "vendor/angular/locale/angular-locale_{LANG}.js",
            "tether" => "vendor/tether/tether.min.js",
        ],
        "dependencies" => [
            "base" => ["jq", "tether", "bs", "ng", "ng-locale", "app", "filters"],
        ]
    ],
    
    "img" => ["basePath" => "assets/img/"]
];