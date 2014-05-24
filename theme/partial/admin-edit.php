<!-- admin html -->
<body id="razor-admin" class="ng-cloak razor-admin" ng-controller="edit" ng-init="init()">
 
	<!--[if lt IE 9]>
		<div class="ie8 ie8-admin">
			<p class="message">
				<i class="fa fa-exclamation-triangle"></i> You are using an outdated version of Internet Explorer that is not supported, 
				please update your browser or consider using an alternative, modern browser, such as 
				<a href="http://www.google.com/chrome">Google Chome</a>.
			</p>
		</div>
	<![endif]-->

	<global-notification></global-notification>

	<div class="razor-admin-panel">
		<div class="container">
			<div class="row" ng-if="user.id">
				<div class="col-xs-6">
					<?php if ($this->logged_in > 5): ?>
						<div class="editor-controls">
							<button class="btn btn-sm btn-primary" ng-click="startEdit()" ng-hide="toggle">
								<i class="fa fa-pencil"></i><span class="mobile-hide-inline"> Continue Editing</span>
							</button>
							<button class="btn btn-sm btn-primary" ng-click="stopEdit()" ng-show="toggle">
								<i class="fa fa-eye"></i><span class="mobile-hide-inline"> View Changes</span>
							</button>
							<button class="btn btn-sm btn-success" ng-click="saveEdit()" ng-show="changed">
								<i class="fa fa-check"></i><span class="mobile-hide-inline"> Save Page</span>
							</button>
							<a href="?" class="btn btn-sm btn-danger" ng-show="changed">
								<i class="fa fa-times"></i><span class="mobile-hide-inline"> Cancel Changes</span>
							</a>
						</div>
					<?php endif ?>
				</div>
				<div class="col-xs-6">
					<div class="account-details text-right" ng-show="user.id">
						<span class="name">{{user.name}} <a href="#" ng-click="logout()"><i class="fa fa-sign-out" data-toggle="tooltip" data-placement="bottom" title="Sign Out"></i></a></span>
						<span class="last-login-date">Last login: {{user.last_logged_in * 1000 | date:'EEE, MMM d, y'}}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
<!-- /body in template - do not close -->