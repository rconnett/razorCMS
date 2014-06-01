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
				if ($scope.activePage == "user-activated") $rootScope.$broadcast("global-notification", {"type": "success", "text": "Your account is now activated, you may now log in."});
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
					if (data.login_error_code == 103) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Account not activated."});
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
					if ($scope.user.access_level > 5) $scope.load();
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

					var latestBuild = (data.version * 1000000) + (data.milestone * 1000) + data.release;
					var systemBuild = ($scope.system.version * 1000000) + ($scope.system.milestone * 1000) + $scope.system.release;

					if (latestBuild > systemBuild) $scope.upgrade = true;
					else $scope.noUpgrade = true;

				}).error(function(data)
				{
					$scope.versionError = true;
				});
			});

			// get site data
			rars.get("setting/editor", "all", monster.get("token")).success(function(data)
			{
				$scope.site = data.settings;
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

		$scope.register = function()
		{			
			$modal.open(
			{
				templateUrl: RAZOR_BASE_URL + "theme/partial/modal/register-user.html",
				controller: "registerUserModal"
			});
		};

		$scope.editProfile = function()
		{			
			$modal.open(
			{
				templateUrl: RAZOR_BASE_URL + "theme/partial/modal/user-profile.html",
				controller: "userProfileModal",
				resolve: {
					user: function(){ return $scope.user; }
				}
			});
		};
	})

	.controller("userProfileModal", function($scope, $modalInstance, rars, $rootScope, user, $timeout)
	{
		$scope.user = user;

		$scope.cancel = function()
		{
			$modalInstance.dismiss('cancel');
		}; 

		$scope.saveUser = function(profile)
		{
			$scope.processing = true;

			rars.post("user/data", profile, monster.get("token")).success(function(data)
			{
				$scope.processing = false;
				
				if (!!data.reload)
				{
					$rootScope.$broadcast("global-notification", {"type": "success", "text": "User profile updated, logging out in 3 seconds."});
	
					$timeout(function()
					{
						window.location = RAZOR_BASE_URL;
					}, 3000);
				}
				else $rootScope.$broadcast("global-notification", {"type": "success", "text": "User profile updated."});

			}).error(function(data, header) 
			{ 
				$scope.processing = false;
				if (header == 409) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not update user profile, email address already registered."});
				else $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not update user profile."});
			});
		};   
	})

	.controller("registerUserModal", function($scope, $modalInstance, $rootScope, rars, $timeout)
	{
		$scope.newUser = {"signature": RAZOR_FORM_SIGNATURE};

		$scope.cancel = function()
		{
			$modalInstance.dismiss('cancel');
		};

		$scope.saveUser = function(newUser)
		{
			rars.post("user/register", newUser, monster.get("token")).success(function(data)
			{
				if (data.manual_activation) $rootScope.$broadcast("global-notification", {"type": "success", "text": "Registration completed, please allow time for administration to activate account."});
				else $rootScope.$broadcast("global-notification", {"type": "success", "text": "Registration completed, please click link in activation email to complete."});
				$modalInstance.close();
			}).error(function(data, header) 
			{ 
				if (header == 409) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not register user, email address already registered."});
				else if (header == 406) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not register user, you are not human."});
				else $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not register user, please try again later."});
				$modalInstance.close();
				$timeout(function()
				{
					window.location.href = RAZOR_BASE_URL + "/login#register";
				}, 3000);
			});
		};	
	})

	.controller("addNewPageModal", function($scope, $modalInstance, rars, $rootScope)
	{
		$scope.page = {};
		$scope.processing = null;
		$scope.completed = null;
		$scope.newPage = null;
		
		$scope.accessLevels = [
			{"name": "Public Access", "value": 0},
			{"name": "User Level 1", "value": 1},
			{"name": "User Level 2", "value": 2},
			{"name": "User Level 3", "value": 3},
			{"name": "User Level 4", "value": 4},
			{"name": "User Level 5", "value": 5}
		];

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
			}).error(function(data)
			{
				if (!data.response.code) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Could not save page, please try again later."});
				else if (data.response.code == 101) $rootScope.$broadcast("global-notification", {"type": "danger", "text": "Link is not unique, already being used by another page."});
				$scope.processing = false;
				console.debug(1212);
			}); 
		};  

	});
});