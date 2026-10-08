<?php
$pageTitle = 'Users';
$pageScript = '/icoms/public/js/users.js';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">User Management</h3>
    <p class="text-muted small mb-0">Manage owner and staff accounts.</p>
  </div>
  <button class="btn btn-primary" id="btnAddUser"><i class="bi bi-person-plus"></i> Add User</button>
</div>

<div class="card">
  <div class="card-body">
    <table class="table table-hover align-middle mb-0" id="tblUsers">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Username</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="userForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalTitle">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input name="username" class="form-control" required>
          <div class="form-text">3–50 chars. Letters, numbers, dot, underscore.</div>
        </div>
        <div class="mb-3" id="passwordGroup">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" autocomplete="new-password">
          <div class="form-text">Minimum 8 characters.</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="staff">Staff</option>
            <option value="owner">Owner</option>
          </select>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeChk" checked>
          <label class="form-check-label" for="activeChk">Active (can log in)</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="pwdModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="pwdForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <p class="text-muted small">Set a new password for <strong id="pwdUserName"></strong>.</p>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input name="password" type="password" class="form-control" required autocomplete="new-password">
          <div class="form-text">Minimum 8 characters.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-warning">Reset Password</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>