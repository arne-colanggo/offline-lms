<div class="left-side-bar">
	<div class="brand-logo">
		<a href="<?= route_to('teacher.home') ?>">
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
					<a href="<?= route_to('teacher.home') ?>" class="dropdown-toggle no-arrow">
						<span class="micon dw dw-home"></span><span class="mtext">Home</span>
					</a>
				</li>
				<li>
					<a href="<?= route_to('school-calendar') ?>" class="dropdown-toggle no-arrow">
						<span class="micon dw dw-calendar-8"></span><span class="mtext">Calendar</span>
					</a>
				</li>
				<li class="dropdown">
					<a href="javascript:;" class="dropdown-toggle">
						<span class="micon dw dw-chat-2"></span><span class="mtext">Class</span>
					</a>
					<ul class="submenu ">
						<li>
							<a href="<?= route_to('classes') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Classes</span>
							</a>
						</li>

					</ul>
				</li>
				<!-- #region Class -->





			</ul>
		</div>
	</div>
</div>