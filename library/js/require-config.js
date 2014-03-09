/*global require */

require.config({
    baseUrl: RAZOR_BASE_URL + 'library/js',
    waitSeconds: 60,

	paths: {
		"angular": "angular/angular_1_2_14.min",
		"angular-route": "angular/angular-route.min",
		"angular-resource": "angular/angular-resource.min",
		"angular-cookies": "angular/angular-cookies.min",
		"ui-bootstrap": "ui-bootstrap/ui-bootstrap-custom-tpls-0.10.0.min",
		"cookie-monster": "cookie-monster/cookie-monster",
		"nicedit": "nicedit/nicedit"
	},
	
	shim: {
		"angular": { exports: "angular" },
		"angular-route": { deps: ["angular"] },
		"angular-resource": { deps: ["angular"] },
		"angular-cookies": { deps: ["angular"] },
		"ui-bootstrap": { deps: ["angular"] }
	}
});