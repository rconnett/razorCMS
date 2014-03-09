define(["angular", "cookie-monster"], function(angular, monster)
{
    angular.module("razor.admin.settings", [])

    .controller("settings", function($scope, rars, $rootScope)
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
    });
});