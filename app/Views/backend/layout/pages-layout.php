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

		.required::after {
			content: ' *';
			color: red;
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
			<?= view('backend/pages/users/modal_change_credentials') ?>
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

	<script>
		$(document).ready(function () {

			/*
			 * Show / Hide Password
			 */
			$('.toggle-password').on('click', function () {

				let target = $(this).data('target');
				let input = $(target);
				let icon = $(this).find('i');

				if (input.attr('type') === 'password') {

					input.attr('type', 'text');

					icon.removeClass('fa-eye')
						.addClass('fa-eye-slash');

				} else {

					input.attr('type', 'password');

					icon.removeClass('fa-eye-slash')
						.addClass('fa-eye');
				}
			});


			/*
			 * Submit Form
			 */
			$('#changeCredentialsForm').on('submit', function (e) {

				e.preventDefault();

				let form = this;
				let formData = new FormData(form);
				let button = $('#saveCredentialsBtn');
				let alertBox = $('#credentialAlert');

				let username = $('#new_username').val().trim();
				let password = $('#new_password').val();
				let confirmPassword = $('#confirm_password').val();

				/*
				 * At least one change must be made
				 */
				if (username === '' && password === '') {

					alertBox
						.removeClass('d-none alert-success')
						.addClass('alert-danger')
						.html('Please enter a new username or password.');

					return;
				}

				/*
				 * Check password confirmation
				 */
				if (password !== '' && password !== confirmPassword) {

					alertBox
						.removeClass('d-none alert-success')
						.addClass('alert-danger')
						.html('New password and confirmation password do not match.');

					return;
				}

				/*
				 * Disable button
				 */
				button.prop('disabled', true)
					.html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

				/*
				 * Submit using AJAX
				 */
				$.ajax({
					url: $(form).attr('action'),
					method: $(form).attr('method'),
					data: formData,
					processData: false,
					dataType: 'json',
					contentType: false,
					success: function (response) {
						console.log(response);
						if (response.status === 'success') {
							alertBox
								.removeClass('d-none alert-danger')
								.addClass('alert-success')
								.html(response.message);
							setTimeout(function () {
								$('#changeCredentialsModal').modal('hide');
								location.reload();
							}, 1200);
						} else {

							alertBox
								.removeClass('d-none alert-success')
								.addClass('alert-danger')
								.html(response.message);
							button.prop('disabled', false)
								.html('<i class="fas fa-save mr-1"></i> Change Credentials');
						}
					},

					error: function (xhr) {

						let message = 'Unable to change credentials. Please try again.';

						if (xhr.responseJSON && xhr.responseJSON.message) {
							message = xhr.responseJSON.message;
						}

						alertBox
							.removeClass('d-none alert-success')
							.addClass('alert-danger')
							.html(message);

						button.prop('disabled', false)
							.html('<i class="fas fa-save mr-1"></i> Change Credentials');
					}

				});

			});


			/*
			 * Clear form when modal closes
			 */
			$('#changeCredentialsModal').on('hidden.bs.modal', function () {

				$('#changeCredentialsForm')[0].reset();

				$('#credentialAlert')
					.addClass('d-none')
					.removeClass('alert-success alert-danger')
					.html('');

				$('#saveCredentialsBtn')
					.prop('disabled', false)
					.html('<i class="fas fa-save mr-1"></i> Change Credentials');
			});

		});
	</script>
</body>

</html>