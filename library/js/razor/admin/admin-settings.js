define(["angular", "cookie-monster"], function(angular, monster)
{
    angular.module("razor.admin.settings", [])

    .controller("settings", function($scope)
    {
        $scope.test = "settings test, 1, 2, 3...";
    });
});