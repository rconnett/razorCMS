define(["angular"], function(angular)
{
    angular.module("extension.photo.razorcms.photoGallery.controller", [])

    .controller("photoGallery", function($scope)
    {
        $scope.photos = [];
        $scope.photoFrame = null;
        $scope.position = 0;
        $scope.sliderListStyle = {"width": "0px", "margin-left": "10px"};

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
            if (direction == "left" && $scope.position > 0) $scope.position--;
            else if (direction == "right" && $scope.position < $scope.photos.length - 1) $scope.position++;
            $scope.photoFrame = $scope.photos[$scope.position];         
        };

        $scope.selectPhoto = function(index)
        {
            $scope.position = index;
            $scope.photoFrame = $scope.photos[$scope.position];
        };
    });
});