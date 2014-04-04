/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
define(["angular", "cookie-monster"], function(angular, monster)
{
    angular.module("razor.admin.settings", [])

    .controller("settings", function($scope, rars, $rootScope, $http)
    {
       	$scope.save = function()
        {
        	$scope.processing = true;

	        rars.post("site/data", $scope.site, monster.get("token")).success(function(data)
	        {
	            $rootScope.$broadcast("global-notification", {"type": "success", "text": "Settings saved."});
	            $scope.processing = false;
	        }).error(function() 
	        { 
	        	$rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not save settings, please try again later."});
	        	$scope.processing = false; 
	        });
        };

        $scope.checkVersion = function()
        {
        	$scope.upgrade = null;
        	$scope.noUpgrade = null;
        	$scope.error = null;

	        rars.get("tools/version", "current").success(function(data)
	        {
	        	$scope.current = data;

	        	if (data.version > $scope.system.version) $scope.upgrade = true;
	        	else if (data.milestone > $scope.system.milestone) $scope.upgrade = true;
	        	else if (data.release > $scope.system.release) $scope.upgrade = true;
	        	else $scope.noUpgrade = true;
	        }).error(function(data)
	        {
	        	$scope.error = true;
	        });
	    };
    });
});