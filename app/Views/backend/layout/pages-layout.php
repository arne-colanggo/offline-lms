<!DOCTYPE html>
<html>

<head>
	<!-- Basic Page Info -->
	<meta charset="utf-8" />
	<title><?= isset($pageTitle) ? $pageTitle : 'New Page Title' ?></title>

	<!-- Site favicon -->

	<link rel="icon" type="image/png" sizes="16x16" href="/images/settings/<?= get_settings()->favicon ?>" />

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

	<!-- Google Font
		<link
			href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
			rel="stylesheet"
		/> -->
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="/backend/vendors/styles/core.css" />
	<link rel="stylesheet" type="text/css" href="/backend/vendors/styles/icon-font.min.css" />
	<link rel="stylesheet" type="text/css" href="/backend/src/plugins/sweetalert2/sweetalert2.css" />
	<link rel="stylesheet" type="text/css" href="/backend/vendors/styles/style.css" />
	<link rel="stylesheet" href="/backend/vendors/styles/toastr.min.css">
	<link rel="stylesheet" href="/extra-assets/ijaboCropTool/ijaboCropTool.min.css">
	<link rel="stylesheet" href="/extra-assets/bootstrap-toggle/css/bootstrap-toggle.min.css">

	<style>
		.swal2-popup {
			font-size: .60em;
		}
	</style>
	<?= $this->renderSection('stylesheets') ?>
</head>

<body>


	<?php include('inc_admin/header.php') ?>
	<?php include('inc_admin/right-sidebar.php') ?>
	<?php include('inc_admin/left-sidebar.php') ?>

	<div class="mobile-menu-overlay"></div>

	<div class="main-container">
		<div class="pd-ltr-20 xs-pd-20-10">
			<div class="min-height-200px">
				<?= $this->renderSection('content') ?>
			</div>

			<?php include('inc_admin/footer.php') ?>
		</div>
	</div>

	<script src="/backend/vendors/scripts/core.js"></script>
	<script src="/backend/vendors/scripts/script.min.js"></script>
	<script src="/backend/vendors/scripts/process.js"></script>
	<script src="/backend/vendors/scripts/layout-settings.js"></script>
	<script src="/backend/vendors/scripts/toastr.min.js"></script>
	<script src="/extra-assets/ijaboCropTool/ijaboCropTool.min.js"></script>

	<!-- add sweet alert js & css in footer -->
	<script src="/backend/src/plugins/sweetalert2/sweetalert2.all.js"></script>
	<script src="/backend/src/plugins/sweetalert2/sweet-alert.init.js"></script>
	<script src="/extra-assets/bootstrap-toggle/js/bootstrap-toggle.min.js"></script>
	<?= $this->renderSection('scripts') ?>

</body>

</html>