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
    angular.module("razor.admin.settings", ["ui.bootstrap"])

    .controller("settings", function($scope, rars, $rootScope, $http, $modal)
    {
        $scope.current = null;

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

        $scope.upgradeVersion = function()
        {            
            $modal.open(
            {
                templateUrl: RAZOR_BASE_URL + "theme/partial/modal/upgrade-version.html",
                controller: "upgradeVersionModal",
                resolve: {
                    sys: function(){ return $scope.system; },
                    cur: function(){ return $scope.current; }
                }
            }).result.then(function(response)
            {

            });
        };
    })
    
    .controller("upgradeVersionModal", function($scope, $modalInstance, rars, $rootScope, $timeout, sys, cur)
    {
        $scope.razorBaseUrl = function()
        {
            return RAZOR_BASE_URL;
        };

        $scope.getVersion = function()
        {
            return {'sys': sys, 'cur': cur};
        };

        $scope.cancel = function()
        {
            $modalInstance.dismiss('cancel');
        };
    
        $scope.downloadVersion = function()
        {
            $scope.stage = 1;
            $scope.upgrading = 1;

            // use timer to slow down
            var timed = false;
            var finished = false;
            $timeout(function()
            {
                timed = true;
                if (finished) $scope.createBackup();
            }, 5000);

	        rars.get("file/package", "system_upgrade", monster.get("token")).success(function(data)
	        {
                finished = true;
                if (timed) $scope.createBackup();
	        }).error(function(data)
	        {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Error downloading upgrade file. Please upgrade manually."});
	        });
        };
    
        $scope.createBackup = function()
        {
            $scope.stage = 2;
            $scope.upgrading = 2;

            // use timer to slow down
            var timed = false;
            var finished = false;
            $timeout(function()
            {
                timed = true;
                if (finished) $scope.upgradeSystem();
            }, 5000);

            rars.get("file/backup", "full", monster.get("token")).success(function(data)
            {
                $scope.backupLink = data.backup;
                finished = true;
                if (timed) $scope.upgradeSystem();
            }).error(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Error creating backup."});
            });
        };
    
        $scope.upgradeSystem = function()
        {
            $scope.stage = 3;
            $scope.upgrading = 3;

            // use timer to slow down
            var timed = false;
            var finished = false;
            $timeout(function()
            {
                timed = true;
                if (finished)
                {
                    $scope.stage = 4;
                    $scope.upgrading = 4;
                    $scope.completeUpgrade();
                }
            }, 5000);

            rars.get("system/upgrade", "now", monster.get("token")).success(function(data)
            {
                finished = true;
                if (timed)
                {
                    $scope.stage = 4;
                    $scope.upgrading = 4;
                    $scope.completeUpgrade();
                }
            }).error(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Error upgrading system"});
            });
        };
    
        $scope.completeUpgrade = function()
        {
            $scope.stage = 4;
            $scope.upgrading = 6;

            // use timer to slow down
            var timed = false;
            var finished = false;
            $timeout(function()
            {
                timed = true;
                if (finished)
                {
                    $scope.stage = 5;
                    $scope.reloadSystem();
                }
            }, 5000);

            rars.get("system/upgrade", "complete", monster.get("token")).success(function(data)
            {
                finished = true;
                if (time)
                {
                    $scope.stage = 5;
                    $scope.reloadSystem();
                }
            }).error(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Error completing upgrade, please manually remove the 'tmp' folder found in 'storage' to tidy up your system."});
            });
        };

        $scope.reloadSystem = function()
        {
            $scope.upgrading = 7;
            $scope.refreshSeconds = 20;
            $scope.clrInt = setInterval(function()
            {
                // decrease timer
                $scope.refreshSeconds--;
                angular.element(document.querySelector(".seconds-counter")).text($scope.refreshSeconds); // hmmmm wont bind so done this
                if($scope.refreshSeconds == 0)
                {
                    clearInterval($scope.clrInt);
                    window.location = RAZOR_BASE_URL;
                }
            }, 1000);
        };
    
        $scope.revertUpgrade = function()
        {
            rars.post("system/upgrade", {"backup": $scope.backupLink}, monster.get("token")).success(function(data)
            {
                $scope.upgrading = 8;
            }).error(function(data)
            {
                $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Error reverting upgrade, please manually overwrite your whole system with the backup you downloaded to revert (use ftp)."});
            });
        };
    });
});