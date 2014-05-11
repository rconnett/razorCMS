/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
define(["angular", "cookie-monster", "ui-bootstrap"], function(angular, monster)
{
    angular.module("razor.admin.extensions", ["ui.bootstrap"])

    .controller("extensions", function($scope, rars, $rootScope, $modal)
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

        $scope.searchExtensions = function()
        {
            $modal.open(
            {
                templateUrl: RAZOR_BASE_URL + "theme/partial/modal/search-extensions.html",
                controller: "searchExtensionsModal"
            }).result.then(function(ext)
            {
                // if (theme == "default")
                // {
                //     $scope.page.theme = "";
                //     $scope.page.themeData = null;
                // }
                // else
                // {
                //     $scope.page.theme = theme.handle + "/" + theme.extension + "/" + theme.manifest + ".manifest.json";
                //     $scope.page.themeData = theme;
                // }

                // $scope.themeChanged = true; // flag so we can reload
            });
        }
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
    })

    .controller("searchExtensionsModal", function($scope, $modalInstance)
    {
        $scope.cancel = function()
        {
            $modalInstance.dismiss('cancel');
        };

        $scope.close = function(ext)
        {
            $modalInstance.close(ext);
        };    
    })

    .controller("searchExtensionsAccordion", function($scope, rars, $rootScope)
    {
        $scope.extensionDetails = null;
        $scope.oneAtATime = true;

        // grab content list
        rars.get("extension/repository", "update", monster.get("token")).success(function(data)
        {
            $scope.repo = data.repository;
        }).error(function(){
            $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Failed to load extension list."});
        }); 

        // get extension details
        $scope.getExtensionDetails = function(ext)
        {
            $scope.extensionDetails = null;

            //grab content list
            rars.post("extension/repository", ext, monster.get("token")).success(function(data)
            {
                $scope.extensionDetails = data.details;
            }).error(function(){
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Failed to load extension details."});
            }); 
        };

        // install extension
        $scope.installExtension = function(ext)
        {
            //grab content list
            rars.post("extension/install", ext, monster.get("token")).success(function(data)
            {
                console.debug("installed");
            }).error(function(){
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Failed to install extension, please install manually."});
            }); 
        };
    });
});