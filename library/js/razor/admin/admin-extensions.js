define(["angular", "cookie-monster"], function(angular, monster)
{
    angular.module("razor.admin.extensions", [])

    .controller("extensions", function($scope, rars, $rootScope)
    {
    	$scope.content = null;

        $scope.loadExtensions = function()
        {
            // grab page data
            rars.get("extension/list", "all", monster.get("token")).success(function(data)
            {
                $scope.extensions = data.extensions;
            });
        };
    })

    .controller("extensionsListAccordion", function($scope, rars, $rootScope)
    {
        $scope.oneAtATime = true;

        $scope.saveSettings = function(e)
        {
            // grab page data
            rars.post("extension/data", e, monster.get("token")).success(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "success", "text": "Settings updated."});
            }).error(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not save setings, please try again later."});
            });
        };
    });
});