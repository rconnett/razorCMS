<?php if (!defined("RAZOR_BASE_PATH")) die("No direct script access to this content"); ?>

<?php
	$image_files = array(".jpg", ".jpeg", ".png", ".gif");

	// grab settings for this content area and from that, find folder to use
	$content_ext_settings = json_decode($c_data["json_settings"]);

	$photos = "[]";
	if (isset($content_ext_settings->album_name))
	{
		// check if folders exist
		if (!is_dir(RAZOR_BASE_PATH."storage/files/razorcms")) mkdir(RAZOR_BASE_PATH."storage/files/razorcms");
		if (!is_dir(RAZOR_BASE_PATH."storage/files/razorcms/photo-gallery")) mkdir(RAZOR_BASE_PATH."storage/files/razorcms/photo-gallery");
		if (!is_dir(RAZOR_BASE_PATH."storage/files/razorcms/photo-gallery/{$content_ext_settings->album_name}")) mkdir(RAZOR_BASE_PATH."storage/files/razorcms/photo-gallery/{$content_ext_settings->album_name}");

		// grab folder here, load in the files for a particular folder
		$files = RazorFileTools::read_dir_contents(RAZOR_BASE_PATH."storage/files/razorcms/photo-gallery/{$content_ext_settings->album_name}", $type = 'files');

		// remove anything not an image file ext
		foreach ($files as $key => $file)
		{
			if (!in_array(strtolower(substr($file, -4)), $image_files))
			{
				unset($files[$key]);
				continue;
			}

			$files[$key] = RAZOR_BASE_URL."storage/files/razorcms/photo-gallery/{$content_ext_settings->album_name}/{$file}";
		}

		sort($files);

		// json encode
		$photos = str_replace('"', "'", json_encode(array_values($files)));
	}

	// get default settings layout settings
	$m = array();
	foreach ($manifest->content_settings as $m_set)
	{
		$m[$m_set->name] = (isset($content_ext_settings->{$m_set->name}) && !empty($content_ext_settings->{$m_set->name}) ? $content_ext_settings->{$m_set->name} : $m_set->value);
	}

	// get content settings
	$c = $content_ext_settings;
?>

<!-- module output -->
<div class="photo-razorcms-photo-gallery" class="ng-cloak" ng-controller="photoGallery" ng-init="init(<?php echo $photos ?>)">
	<div class="photo-gallery-frame text-center" style="height: <?php echo $m["frame_height"] ?>; width: <?php echo $m["frame_width"] ?>;">
		<div class="photo-gallery-canvas">
			<i class="fa fa-chevron-circle-left photo-control change-left" ng-click="scrollPhotos('left')"></i>
			<i class="fa fa-chevron-circle-right photo-control change-right" ng-click="scrollPhotos('right')"></i>
			<div class="center-box" style="line-height: <?php echo $m["frame_height"] ?>;">
				<img ng-show="photoFrame" ng-src="{{photoFrame}}" ng-init="photoFrame = photos[position]">
				<i ng-if="!photoFrame" class="fa fa-picture-o photo-placeholder"></i>
			</div>
		</div>
	</div>
	<div class="photo-gallery-controls" style="width: <?php echo $m["frame_width"] ?>;">
		<i class="fa fa-chevron-circle-left photo-control slide-left" ng-click="scrollThumbs('left')"></i>
		<i class="fa fa-chevron-circle-right photo-control slide-right" ng-click="scrollThumbs('right')"></i>
		<div class="photo-gallery-slider">
			<ul class="photo-gallery-thumbs" ng-style="sliderListStyle">
				<li ng-repeat="p in photos"><img ng-src="{{p}}" ng-click="selectPhoto($index)" ng-class="{'selected': $index == position}"></li>
			</ul>
		</div>
	</div>
</div>
<!-- module output -->

<!-- load dependancies -->
<?php if (!in_array("photo-razorcms-photo-gallery-style", $ext_dep_list)): ?>
	<?php $ext_dep_list[] = "photo-razorcms-photo-gallery-style" ?>
	<link type="text/css" rel="stylesheet" href="<?php echo RAZOR_BASE_URL ?>extension/photo/razorcms/photo-gallery/style/style.css">
<?php endif ?>
<?php if (!in_array("photo-razorcms-photo-gallery-module", $ext_dep_list)): ?>
	<?php $ext_dep_list[] = "photo-razorcms-photo-gallery-module" ?>
	<script src="<?php echo RAZOR_BASE_URL ?>extension/photo/razorcms/photo-gallery/js/module.js"></script>
<?php endif ?>
<!-- load dependancies -->