/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
require.config({
	baseUrl: RAZOR_BASE_URL + 'library/js',
	waitSeconds: 60,

	paths: {
		"angular": "angular/angular_1_2_14.min",
		"angular-route": "angular/angular-route.min",
		"angular-resource": "angular/angular-resource.min",
		"angular-sanitize": "angular/angular-sanitize.min",
		"angular-cookies": "angular/angular-cookies.min",

		"text-angular": "text-angular/text-angular-custom",
		"text-angular-sanitize": "text-angular/text-angular-sanitize-custom",

		"ui-bootstrap": "ui-bootstrap/ui-bootstrap-custom-tpls-0.10.0.min",

		"cookie-monster": "cookie-monster/cookie-monster",
		
		"jquery": "jquery/jquery-2.1.0.min",
		"jquery-bootstrap": "jquery-bootstrap/bootstrap.min"
	},
	
	shim: {
		"angular": { exports: "angular" },
		"angular-route": { deps: ["angular"] },
		"angular-resource": { deps: ["angular"] },
		"angular-sanitize": { deps: ["angular"] },
		"angular-cookies": { deps: ["angular"] },

		"text-angular-sanitize": { deps: ["angular"] },
		"text-angular": { deps: ["angular", "text-angular-sanitize", "ui-bootstrap"] },

		"ui-bootstrap": { deps: ["angular"] },
		
		"jquery": { exports: "$" },
		"jquery-bootstrap": { deps: ["jquery"] }
	}
});