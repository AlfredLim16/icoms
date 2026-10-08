<?php
    $current = basename($_SERVER['PHP_SELF']);
    $isOwner = $user['role'] === 'owner';
?>

<aside class="sidebar p-3 d-flex flex-column">

  <div class="brand mb-4">
    <i class="bi bi-cup-hot-fill"></i> ICOMS
  </div>

  <div class="sidebar-user mb-4 p-2 rounded">
    <div class="sidebar-user-label">Signed in as</div>
    <div class="sidebar-user-name text-truncate"><?= htmlspecialchars($user['name']) ?></div>
    <span class="badge bg-<?= $isOwner ? 'warning text-dark' : 'info text-dark' ?> mt-1">
      <?= ucfirst($user['role']) ?>
    </span>
  </div>

  <nav class="sidebar-nav flex-grow-1">

    <div class="nav-category">Main</div>
    <ul class="nav flex-column mb-3">
      <li>
        <a class="nav-link <?= $current==='dashboard.php'?'active':'' ?>"
           href="/icoms/views/<?= $user['role'] ?>/dashboard.php">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </a>
      </li>
    </ul>

    <?php if ($isOwner): ?>

      <div class="nav-category">Management</div>
      <ul class="nav flex-column mb-3">
        <li>
          <a class="nav-link <?= $current==='users.php'?'active':'' ?>"
             href="/icoms/views/owner/users.php">
            <i class="bi bi-people"></i>
            <span>Users</span>
          </a>
        </li>
        <li>
          <a class="nav-link <?= $current==='menu.php'?'active':'' ?>"
             href="/icoms/views/owner/menu.php">
            <i class="bi bi-journal-text"></i>
            <span>Menu</span>
          </a>
        </li>
        <li>
          <a class="nav-link <?= $current==='inventory.php'?'active':'' ?>"
             href="/icoms/views/owner/inventory.php">
            <i class="bi bi-box-seam"></i>
            <span>Inventory</span>
          </a>
        </li>
      </ul>

      <div class="nav-category">Operations</div>
      <ul class="nav flex-column mb-3">
        <li>
          <a class="nav-link <?= $current==='waste.php'?'active':'' ?>"
             href="/icoms/views/staff/waste.php">
            <i class="bi bi-trash"></i>
            <span>Waste Log</span>
          </a>
        </li>
        <li>
          <a class="nav-link <?= $current==='orders.php'?'active':'' ?>"
             href="/icoms/views/owner/orders.php">
            <i class="bi bi-clock-history"></i>
            <span>Order History</span>
          </a>
        </li>
      </ul>

      <div class="nav-category">Analytics</div>
      <ul class="nav flex-column mb-3">
        <li>
          <a class="nav-link <?= $current==='reports.php'?'active':'' ?>"
             href="/icoms/views/owner/reports.php">
            <i class="bi bi-graph-up"></i>
            <span>Reports</span>
          </a>
        </li>
      </ul>

    <?php else: ?>
      
      <div class="nav-category">Operations</div>
      <ul class="nav flex-column mb-3">
        <li>
          <a class="nav-link <?= $current==='ordering.php'?'active':'' ?>"
             href="/icoms/views/staff/ordering.php">
            <i class="bi bi-cart-check"></i>
            <span>Ordering</span>
          </a>
        </li>
        <li>
          <a class="nav-link <?= $current==='waste.php'?'active':'' ?>"
             href="/icoms/views/staff/waste.php">
            <i class="bi bi-trash"></i>
            <span>Record Waste</span>
          </a>
        </li>
        <li>
          <a class="nav-link <?= $current==='history.php'?'active':'' ?>"
             href="/icoms/views/staff/history.php">
            <i class="bi bi-clock-history"></i>
            <span>Order History</span>
          </a>
        </li>
      </ul>

    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a class="nav-link logout-link" href="#" id="logoutBtn">
      <i class="bi bi-box-arrow-right"></i>
      <span>Logout</span>
    </a>
  </div>

</aside>
<main class="flex-grow-1 p-4">