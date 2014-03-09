define(["angular", "angular-resource"], function(angular)
{
    angular.module("razor.directives.validation", [])
 
    .directive('confirm', function() 
    {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function(scope, elm, attrs, ctrl) 
            {      
                ctrl.$parsers.unshift(function(viewValue) 
                {
                    if (viewValue === attrs.confirm) 
                    {
                        // it is valid
                        ctrl.$setValidity('confirm', true);
                        return viewValue;
                    } 
                    else 
                    {
                        // it is invalid, return undefined (no model update)
                        ctrl.$setValidity('confirm', false);
                        return undefined;
                    }
                });
            }
        };
    });
});