/**
 * razorCMS FBCMS
 *
 * Copywrite 2014 to Present Day - Paul Smith (aka smiffy6969, razorcms)
 *
 * @author Paul Smith
 * @site ulsmith.net
 * @created Feb 2014
 */
 
define(["angular", "angular-resource"], function(angular)
{
	angular.module("razor.services.rars", ["ngResource"])

	.factory("rars", function($http)
	{
		return {
			"get": function(funcPath, id, token)
			{
				return $http.get(RAZOR_BASE_URL + "rars/" + funcPath + (!!id ? "/" + id : ""), {headers: {"Authorization": token}});
			},
			"post": function(funcPath, data, token)
			{
				if (!!data.files)
				{
					var fd = new FormData();
					for (var i = 0; i < data.files.length; i++) {
						fd.append('file', data.files[i]);
					};

					return $http.post(RAZOR_BASE_URL + "rars/" + funcPath, fd, {transformRequest: angular.identity, headers: {'Content-Type': undefined, "Authorization": token}});
				}
				else return $http.post(RAZOR_BASE_URL + "rars/" + funcPath, data, {headers: {"Authorization": token}});
			},
			"put": function(funcPath, data, token)
			{
				return $http.put(RAZOR_BASE_URL + "rars/" + funcPath, data, {headers: {"Authorization": token}});
			},
			"delete": function(funcPath, id, token)
			{
				// this fixes IE8 issue
				return $http["delete"](RAZOR_BASE_URL + "rars/" + funcPath + (!!id ? "/" + id : ""), {headers: {"Authorization": token}});
			}
		}
	});
});