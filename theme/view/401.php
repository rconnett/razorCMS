<?php if (!defined("RAZOR_BASE_PATH")) die("No direct script access to this content"); ?>

<!DOCTYPE html>
<html xmlns:ng="http://angularjs.org" id="ng-app">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="404 Not Found">
		<meta name="keywords" content="404, Not, Found">

		<title><?php echo $this->site["name"] ?>::404 Not Found</title>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,800italic,400,700,800,600' rel='stylesheet' type='text/css'>
		<link rel="icon" href="<?php echo RAZOR_BASE_URL ?>theme/image/favicon.png" type="image/png">
		<link rel="shortcut icon" href="<?php echo RAZOR_BASE_URL ?>theme/image/favicon.ico">

		<!-- load razor base css (imports: bootstrap, font awesome) -->
		<link type="text/css" rel="stylesheet" href="<?php echo RAZOR_BASE_URL ?>library/style/bootstrap/bootstrap.min.css">
		<link type="text/css" rel="stylesheet" href="<?php echo RAZOR_BASE_URL ?>library/font/font-awesome/css/font-awesome.min.css">
		<link type="text/css" rel="stylesheet" href="<?php echo RAZOR_BASE_URL ?>library/style/razor/razor_base.css">
		
		<!-- load theme specific (no mixins from bootstrap) -->
		<link type="text/css" rel="stylesheet" href="<?php echo RAZOR_BASE_URL ?>theme/style/default.css">
	</head>
	<body>
	<body>
		<div class="template-wrapper">			
			<div class="template-header">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							<div class="template-header-content text-center">
								<p><strong><?php echo $this->site["name"] ?></strong> 401 Unhappy Dude</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="template-main">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							<div class="content-404 text-center">
								<i class="fa fa-meh-o icon-404"></i>
								<p>You have reached <em>unhappy dude</em> as you do not have permission to access this resource, to make <em>unhappy dude</em> happy, please try logging in...</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="template-footer">
				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							<div class="template-footer-content text-center">
								<p><a href="http://www.razorcms.co.uk">razorCMS File Based Content Management System</a></p>
								<p><a href="http://ulsmith.net">ulsmith.net</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>