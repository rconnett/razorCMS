define(["angular"], function(angular)
{
    angular.module("razor.directives.formControls", [])
 
    .directive('slideSwitch', function() {
		return {
			restrict: 'E',
			scope: {"ssModel": "=", "ssDisabled": "="},
			template: '<div class="slide-switch" title="{{(ssDisabled ? \'Disabled (is home page)\' : \'\')}}" ng-class="{\'slide-switch-on\': ssModel, \'slide-switch-disabled\': ssDisabled}" ng-click="ssModel = (ssDisabled ? ssModel : !ssModel)"><span class="slide-switch-slider"><span ng-hide="ssModel">OFF</span><span ng-show="ssModel">ON</span></span></div>'
		};
    })
 
    .directive('multiSelect', function() {
		return {
			restrict: 'E',
			scope: {"msSelected": "=", "msOptions": "=", "msValue": "=", "msLabel": "="},
			template: '<div class="multi-select">' +
				'<ul class="ms-selected" ng-show="msSelected.length > 0">' +
					'<li ng-repeat="sel in msSelected" class="ms-selected-item">' +
						'{{sel[msLabel]}}' +
						'<i class="ms-remove-item fa fa-times" ng-click="msSelected.splice($index, 1)"></i>' +
					'</li>' +
				'</ul>' +	
				'<i class="ms-input-filter fa fa-filter" ng-show="selectaOptions"></i><i class="ms-input-select fa fa-caret-down" ng-hide="selectaOptions"></i>' +
				'<input class="form-control" class="ms-filter" type="text" ng-model="search" ng-focus="selectaOptions = true" ng-blur="hideOptions()" placeholder="Click to select, filter on options">' +
				'<ul class="ms-options" ng-show="selectaOptions">' +
					'<li ng-repeat="opt in msOptions | filter:search | filter:hideSelected" ng-click="msSelected.push(opt)" class="ms-option-item">{{opt[msLabel]}}</li>' +
					'<li class="ms-option-item-empty" ng-show="msOptions.length === msSelected.length"><i class="fa fa-ban"></i> empty</li>' +
				'</ul>' +
			'</div>',
			controller: function($scope, $timeout)
			{
				$scope.hideSelected = function(opt)
				{
				    var result = true

				    angular.forEach($scope.msSelected, function(val)
				    {
				        if (val[$scope.msValue] === opt[$scope.msValue]) result = false;
				    });

				    return result;
				};

				$scope.hideOptions = function()
				{
				    $timeout(function() {
				        $scope.selectaOptions = false;
				    }, 250);
				};
			}
		};
    });
});