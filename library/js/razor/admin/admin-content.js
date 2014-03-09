define(["angular", "cookie-monster"], function(angular, monster)
{
    angular.module("razor.admin.content", [])

    .controller("content", function($scope, rars, $sce, $rootScope)
    {
    	$scope.content = null;

        $scope.loadContent = function()
        {
            // grab page data
            rars.get("content/list", "all").success(function(data)
            {
                $scope.content = data.content;
            });
        };

        $scope.deleteContent = function(contentId)
        {
            rars.delete("content/data", contentId, monster.get("token")).success(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "success", "text": "Content deleted successfully."});

                // clean up any locations or content in active data
                angular.forEach($scope.$parent.locations, function(index, loc)
                {
                    if (loc.content_id == contentId) $scope.$parent.locations.splice(index, 1);
                });

                // clean up any locations or content in active data
                angular.forEach($scope.$parent.content, function(index, con)
                {
                    if (con.content_id == contentId) $scope.$parent.content.splice(index, 1);
                });
            }).error(function()
            {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Error deleting page."});
            });       
        }; 

        $scope.loadHTML = function(html)
        {
            return $sce.trustAsHtml(html);
        };

        $scope.pageLink = function(link)
        {
            return RAZOR_BASE_URL + link;
        };
    })

    .controller("contentListAccordion", function($scope)
    {
        $scope.oneAtATime = true;
    });
});