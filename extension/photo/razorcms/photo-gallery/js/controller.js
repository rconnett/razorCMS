define(["angular"], function(angular)
{
    angular.module("extension.photo.razorcms.photoGallery.controller", [])

    .controller("photoGallery", function($scope, $timeout)
    {
        $scope.photos = [];
        $scope.photoFrame = null;
        $scope.photoFrameHelper = null;
        $scope.position = 0;
        $scope.sliderListStyle = {"width": "0px", "margin-left": "10px"};
        $scope.helperCanvasStyle = {"z-index": "-1px"};

        $scope.init = function(photos)
        {
            $scope.photos = photos;
            $scope.setSliderWidth();
        };

        $scope.setSliderWidth = function()
        {
            $scope.sliderListStyle["width"] = ($scope.photos.length < 1 ? "0px" :  ($scope.photos.length * 85) + "px");
        };

        $scope.scrollThumbs = function(direction)
        {
        	var sliderFrameWidth = document.querySelector(".photo-gallery-slider").offsetWidth;
            var sliderWidth = $scope.sliderListStyle["width"].substring(0, $scope.sliderListStyle["width"].length - 2)
            var margin = parseInt($scope.sliderListStyle["margin-left"].substring(0, $scope.sliderListStyle["margin-left"].length - 2))

            if (direction == "right" && sliderWidth > sliderFrameWidth && margin > (sliderWidth - sliderWidth - sliderWidth) + sliderFrameWidth)
            {
                $scope.sliderListStyle["margin-left"] = margin - 85 + "px";
            }

            if (direction == "left" && margin < 0)
            {
                $scope.sliderListStyle["margin-left"] = margin + 85 + "px";
            }
        };

        $scope.scrollPhotos = function(direction)
        {
            if (direction == "left" && $scope.position > 0)
            {
                $scope.position--;
                $scope.changePhoto();
            }
            else if (direction == "right" && $scope.position < $scope.photos.length - 1)
            {
                $scope.position++;
                $scope.changePhoto();
            }
        };

        $scope.selectPhoto = function(index)
        {
            $scope.position = index;
            $scope.changePhoto();
        };

        $scope.changePhoto = function()
        {
            $scope.showBox = false;
            $scope.turnPhoto = true;
            
            // change helper and reset
            $timeout(function() 
            {
                $scope.photoFrame = $scope.photos[$scope.position];
                    $timeout(function()
                    {
                        $scope.turnPhoto = false;
                        $scope.showBox = true;
                    }, 300);
            }, 300);
            
        };
    });
});