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

    })

    .controller("userListAccordion", function($scope, rars, $rootScope, $timeout)
    {
        $scope.oneAtATime = true;
        $scope.accessLevels = [
            {"name": "Admin", "value": 9},
            {"name": "Manager", "value": 8},
            {"name": "Editor", "value": 7},
            {"name": "Contributer", "value": 6},
            {"name": "User", "value": 1}
        ];

        //grab content list
        rars.get("user/list", "all", monster.get("token")).success(function(data)
        {
            $scope.users = data.users;
        }); 

        $scope.saveUser = function(index)
        {
            $scope.processing = true;

            rars.post("user/data", $scope.users[index], monster.get("token")).success(function(data)
            {
                $scope.processing = false;
                if (!!data.reload)
                {
                    $rootScope.$broadcast("global-notification", {"type": "success", "text": "User details saved, password change, logging out in 3 seconds."});

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

        $scope.removeUser = function(index)
        {

        };
    });
});