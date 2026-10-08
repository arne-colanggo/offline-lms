<div class="left-side-bar">
	<div class="brand-logo">
		<a href="<?= route_to('admin.dashboard') ?>">
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
					<a href="<?= route_to('admin.dashboard') ?>" class="dropdown-toggle no-arrow">
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
						<span class="micon dw dw-user-2"></span><span class="mtext">School Setting</span>
					</a>
					<ul class="submenu ">
						<li>
							<a href="<?= route_to('gradelevel') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Grade Level</span>
							</a>
						</li>
						<li>
							<a href="<?= route_to('section') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Section</span>
							</a>
						</li>
						<li>
							<a href="<?= route_to('subject') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Subject</span>
							</a>
						</li>
						<li>
							<a href="<?= route_to('schoolyear') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">School Year</span>
							</a>
						</li>
					</ul>
				</li>
				<!-- Budget of Work -->
				<li>
					<a href="<?= route_to('budgetofwork') ?>" class="dropdown-toggle no-arrow">

						<span class="micon dw dw-time-management"></span><span class="mtext">Budget of Works</span>
					</a>
				</li>
				<li class="dropdown">
					<a href="javascript:;" class="dropdown-toggle">
						<span class="micon dw dw-add-user"></span><span class="mtext">Student</span>
					</a>
					<ul class="submenu ">
						<li>
							<a href="<?= route_to('student') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Student</span>
							</a>
						</li>

						<li>
							<a href="<?= route_to('upload.sf1') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Upload SF1</span>
							</a>
						</li>

						<li>
							<a href="<?= route_to('upload-nonsf') ?>" class="dropdown-toggle no-arrow">
								<span class="mtext">Upload Non SF Template</span>
							</a>
						</li>
					</ul>
				</li>
				<!-- #region Class -->
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
						<li class="dropdown">
							<a href="javascript:;" class="dropdown-toggle">
								<span class="micon dw dw-user-2"></span><span class="mtext">Manage Assessment</span>
							</a>
							<ul class="submenu ">
								<li>
									<a href="<?= route_to('get-tos') ?>" class="dropdown-toggle no-arrow">
										<span class="mtext">Table of Specification</span>
									</a>
								</li>

								<li>
									<a href="<?= route_to('schoolyear') ?>" class="dropdown-toggle no-arrow">
										<span class="mtext">School Year</span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li>
				<!-- #region Class -->


				<li>
					<div class="dropdown-divider"></div>
				</li>
				<li>
					<div class="sidebar-small-cap">Settings</div>
				</li>

				<li>
					<a href="<?= route_to('admin.profile'); ?>" class="dropdown-toggle no-arrow">
						<span class="micon dw dw-user"></span>
						<span class="mtext">Profile</span>
					</a>
				</li>

				<li>
					<a href="<?= route_to('settings'); ?>" class="dropdown-toggle no-arrow">
						<span class="micon dw dw-settings"></span>
						<span class="mtext">Settings</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>