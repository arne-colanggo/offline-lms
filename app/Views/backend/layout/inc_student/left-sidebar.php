<div class="left-side-bar">
	<div class="brand-logo">
		<a href="<?= route_to('student.home') ?>">
			<img src="<?= '/images/settings/' . get_settings()->logo ?>" alt="" class="dark-logo"
				style="height:50px; width:50px;" />
			<img src="<?= '/images/settings/' . get_settings()->logo ?>" alt="" class="light-logo"
				style="height:50px; width:50px;" />
		</a>
		<div class="close-sidebar" data-toggle="left-sidebar-close">
			<i class="ion-close-round"></i>
		</div>
	</div>
	<div class="menu-block customscroll">
		<div class="sidebar-menu">
			<ul id="accordion-menu">
				<li>
					<a href="<?= route_to('student.home') ?>" class="dropdown-toggle no-arrow">
						<span class="micon dw dw-home"></span><span class="mtext">Home</span>
					</a>
				</li>

				<li>
					<div class="dropdown-divider"></div>
				</li>
				<li>
					<div class="sidebar-small-cap">Settings</div>
				</li>

				<li>
					<a href="<?= route_to('student.profile'); ?>" class="dropdown-toggle no-arrow">
						<span class="micon dw dw-user"></span>
						<span class="mtext">Profile</span>
					</a>
				</li>

			</ul>
		</div>
	</div>
</div>