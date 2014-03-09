define(["angular", "cookie-monster"], function(angular, monster)
{
    angular.module("razor.admin.page", [])

    .controller("page", function($scope, rars, $rootScope)
    {
        $scope.save = function()
        {
        	$scope.processing = true;

	        rars.post("page/details", $scope.page, monster.get("token")).success(function(data)
	        {
	        	$rootScope.$broadcast("global-notification", {"type": "success", "text": "Page details saved."});
	            $scope.processing = false;
	        }).error(function() 
	        { 
	        	$rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not save details, please try again later."});
	        	$scope.processing = false; 
	        });
        };
    });
});