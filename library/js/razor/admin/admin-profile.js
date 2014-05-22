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
    angular.module("razor.admin.profile", [])

    .controller("profile", function($scope, rars, $rootScope, $timeout)
    {
        $scope.save = function()
        {
        	$scope.processing = true;

	        rars.post("user/data", $scope.user, monster.get("token")).success(function(data)
	        {
	            $scope.processing = false;
	            if (!!data.reload)
	            {
		        	$rootScope.$broadcast("global-notification", {"type": "success", "text": "User details saved, logging out in 3 seconds."});
	
	            	$timeout(function()
	            	{
	            		window.location = RAZOR_BASE_URL;
	            	}, 3000);
	            }
	            else $rootScope.$broadcast("global-notification", {"type": "success", "text": "User details saved."});
	        }).error(function() 
	        { 
	        	$rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not save details, please try again later."});
	        	$scope.processing = false; 
	        });
        };

        $scope.editUser = function()
        {            
            $modal.open(
            {
                templateUrl: RAZOR_BASE_URL + "theme/partial/modal/user-details.html",
                controller: "userDetailsController"
            }).result.then(function(user)
            {

            });
        };
    })

    .controller("userDetailsController", function($scope, $modalInstance)
    {
        $scope.cancel = function()
        {
            $modalInstance.dismiss('cancel');
        };

        $scope.close = function(theme)
        {
            $modalInstance.close(theme);
        };    
    })

    .controller("userListAccordion", function($scope, rars)
    {
        $scope.oneAtATime = true;

        //grab content list
        rars.get("user/list", "all", monster.get("token")).success(function(data)
        {
            $scope.users = data.users;
        }); 

        // $scope.selectTheme = function(theme)
        // {
        //     $scope.$parent.close(theme);
        // };
    });
});