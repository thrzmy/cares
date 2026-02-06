<h1>Accounts</h1>

<?php if (!empty($users)): ?>
  <table border="1" cellpadding="6">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Active</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e($u['role']) ?></td>
          <td><?= (int)$u['is_active'] === 1 ? 'Yes' : 'No' ?></td>
          <td>
            <a href="<?= BASE_PATH ?>/admin/accounts/edit?id=<?= (int)$u['id'] ?>">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No accounts found.</p>
<?php endif; ?>
