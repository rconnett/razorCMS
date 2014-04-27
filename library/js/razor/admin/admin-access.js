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
    angular.module("razor.admin.access", ['ui.bootstrap'])

    .controller("access", function($scope, $location, rars, $modal, $timeout, $rootScope, $http)
    {
        $scope.location = $location;
        $scope.user = null;
        $scope.loginDetails = {"u": null, "p": null};
        $scope.passwordDetails = {"password": null, "repeat_password": null};
        $scope.dash = null;

        $scope.site = null;
        $scope.page = null;
        $scope.system = null;
        
        $scope.latestVersion = null;
        $scope.upgrade = null;
        $scope.noUpgrade = null;
        $scope.versionError = null;

        $scope.init = function()
        {
            $scope.loginCheck();
            
            // nav active watcher
            $scope.$watch("location.path()", function(path)
            {
                if (path) $scope.activePage = path.split("/")[1];
            });
        };

        $scope.login = function()
        {
            $scope.processing = true;

            rars.post("login", $scope.loginDetails).success(function(data)
            {
                if (!!data.user)
                {
                    // save cookie and redirect user based on access level
                    monster.set("token", data.token, null, "/");
                    $scope.user = data.user;
                    $scope.showLogin = false;
                    $scope.processing = false;
                    window.location.href = RAZOR_BASE_URL;
                }
                else
                {
                    // clear token and user
                    monster.remove("token");

                    $scope.showLogin = true;
                    $scope.processing = false;

                    if (data.login_error_code == 101) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Login failed."});
                    if (data.login_error_code == 102) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "You have been locked out, try again in " + (!!data.time_left ? Math.ceil(data.time_left / 60) : 0) + "min."});
                    if (data.login_error_code == 103) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Account not activated, click link in activation email to activate."});
                    if (data.login_error_code == 104) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Too many failed attempts, your IP has been banned."});
                }
            })
            .error(function(data)
            {
                $scope.showLogin = true;
                $scope.processing = false;
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Login failed."}); 
            });
        };

        $scope.forgotLogin = function()
        {
            $scope.processing = true;

            rars.post("user/reminder", {"email": $scope.loginDetails.u})
                .success(function(data)
                {
                    $rootScope.$broadcast("global-notification", {"type": "success", "text": "Password reset link emailed to you, you have one hour to use the link."});
                    $scope.processing = false;
                })
                .error(function(data, header) 
                {
                    $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not send password request, user not found or too many requests in last ten minutes."});
                    $scope.processing = false;
                }
            );
        };

        $scope.passwordReset = function()
        {
            // only runs if page set to password-reset due to ng-init and ng-if
            // check for token, do base check on it
            var token = $location.path().split("/")[2];

            if (token.length < 20) return;

            // if there, send of for reset (which is only valid for an hour anyway)
            $scope.processing = true;

            rars.post("user/password", {"passwords": $scope.passwordDetails, "token": token}).success(function(data)
            {
                // show success message, show login form
                $rootScope.$broadcast("global-notification", {"type": "success", "text": "Password reset complete, please log in."});
                $scope.processing = false;
                $scope.location.path('page');
            })
            .error(function(data)
            {
                // show failed message but give no reason why
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not reset password, try requesting a new reset. Returning home in 5 seconds"});
                $timeout(function() { window.location.href = RAZOR_BASE_URL }, 5000);
            });
        };

    	$scope.loginCheck = function()
        {
            rars.get("user/basic", "current", monster.get("token")).success(function(data)
            {
                if (!!data.user)
                {
                    $scope.user = data.user;
                    $scope.loggedIn = true;
                    $scope.showLogin = false;
                    $scope.load();
                }
                else
                {
                    // clear token and user
                    monster.remove("token");
                    $scope.user = null;
                    $scope.loggedIn = false;
                    $scope.showLogin = true;
                }
            });
        };

        $scope.logout = function()
        {
            monster.remove("token");
            $scope.user = null;
            $scope.loggedIn = false;
            window.location.href = RAZOR_BASE_URL;
        };

        $scope.load = function()
        {
            //get system data
            rars.get("system/data", "all", monster.get("token")).success(function(data)
            {
                $scope.system = data.system;

                rars.get("tools/version", "current").success(function(data)
                {
                    $scope.latestVersion = data;

                    if (data.version > $scope.system.version) $scope.upgrade = true;
                    else if (data.milestone > $scope.system.milestone) $scope.upgrade = true;
                    else if (data.release > $scope.system.release) $scope.upgrade = true;
                    else $scope.noUpgrade = true;
                }).error(function(data)
                {
                    $scope.versionError = true;
                });
            });

            // get site data
            rars.get("site/editor", "all").success(function(data)
            {
                $scope.site = data.site;
            });

            // grab page data
            rars.get("page/details", RAZOR_PAGE_ID).success(function(data)
            {
                $scope.page = data.page;

                if (!$scope.page.theme) return;

                // load in theme data
                $http.get(RAZOR_BASE_URL + "extension/theme/" + $scope.page.theme).then(function(response) 
                { 
                    $scope.page.themeData = response.data; 
                });
            });
        };

        $scope.openDash = function()
        {
            $scope.dash = true;
            $scope.location.path('page');
        };

        $scope.closeDash = function()
        {
            $scope.dash = false;
            $scope.location.path('page');
        };

        $scope.addNewPage = function(loc)
        {
            $modal.open(
            {
                templateUrl: RAZOR_BASE_URL + "theme/partial/modal/add-new-page.html",
                controller: "addNewPageModal"
            }).result.then(function(redirect)
            {
                if (!!redirect) window.location = RAZOR_BASE_URL + redirect;
            });
        };
    })

    .controller("addNewPageModal", function($scope, $modalInstance, rars, $rootScope)
    {
        $scope.page = {};
        $scope.processing = null;
        $scope.completed = null;
        $scope.newPage = null;

        $scope.cancel = function()
        {
            $modalInstance.dismiss();
        };

        $scope.closeAndEdit = function()
        {
            $modalInstance.close($scope.newPage.link);
        };  

        $scope.addAnother = function()
        {
            $scope.completed = null;
            $scope.processing = null;
            $scope.page = {};
        };  

        $scope.saveNewPage = function()
        {
            $scope.processing = true;
            $scope.completed = false;

            rars.post("page/data", $scope.page, monster.get("token")).success(function(data)
            {
                $scope.newPage = data;
                $rootScope.$broadcast("global-notification", {"type": "success", "text": "New page saved successfully."});
                $scope.processing = false;
                $scope.completed = true;
            }).error(function()
            {
                if (!data.code) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not save page, please try again later."});
                else if (data.code == 101) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Link is not unique, already being used by another page."});
                $scope.processing = false;
            }); 
        };  

    });
});