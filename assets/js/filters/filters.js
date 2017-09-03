angular.module("app")
	.filter("thumbSize", function() {
		return function(input, size) {
			return (input||'').replace(/(.+)(\.[^\.]+)$/, "$1" + size + "$2");
		};
	})
;