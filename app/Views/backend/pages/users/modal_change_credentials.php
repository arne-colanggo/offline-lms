<!-- Change Credentials Modal -->
<div class="modal fade" id="changeCredentialsModal" tabindex="-1" role="dialog"
    aria-labelledby="changeCredentialsModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="changeCredentialsModalLabel">
                    <i class="fas fa-user-lock mr-2"></i>
                    Change Username / Password
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Form -->
            <form id="changeCredentialsForm" action="<?= route_to('post.user.change.credentials') ?>" method="POST">

                <?= csrf_field() ?>

                <div class="modal-body">

                    <!-- Alert -->
                    <div id="credentialAlert" class="alert d-none" role="alert">
                    </div>

                    <!-- Current Password -->
                    <div class="form-group">
                        <label for="current_password">
                            Current Password
                        </label>

                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                placeholder="Enter your current password" required>

                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="new_username">
                            New Username
                        </label>

                        <input type="text" class="form-control" id="new_username" name="new_username"
                            value="<?= esc(session('userdata')['username'] ?? '') ?>" placeholder="Enter new username">

                        <small class="form-text text-muted">
                            Leave blank if you don't want to change your username.
                        </small>
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="new_password">
                            New Password
                        </label>

                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                placeholder="Enter new password">

                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <small class="form-text text-muted">
                            Leave blank if you don't want to change your password.
                        </small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="confirm_password">
                            Confirm New Password
                        </label>

                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                placeholder="Re-enter new password">

                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password"
                                    data-target="#confirm_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary" id="saveCredentialsBtn">

                        <i class="fas fa-save mr-1"></i>
                        Change Credentials
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>