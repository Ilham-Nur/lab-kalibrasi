<header class="navbar">
      <div class="navbar-left">
        <button id="sidebar-toggle-btn" class="icon-btn" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </button>
        <div class="breadcrumb-nav">
          <span class="breadcrumb-parent">Main</span>
          <i class="bi bi-chevron-right breadcrumb-sep"></i>
          <span class="breadcrumb-current">Dashboard</span>
        </div>
      </div>

      <div class="navbar-right">
        <div class="navbar-search">
          <i class="bi bi-search search-icon"></i>
          <input type="text" class="search-input" placeholder="Quick search..." />
          <kbd class="search-kbd">âŒ˜K</kbd>
        </div>

        <button class="icon-btn notif-btn" aria-label="Notifikasi">
          <i class="bi bi-bell"></i>
          <span class="notif-dot"></span>
        </button>

        <div class="profile-dropdown-wrapper" id="profile-dropdown-wrapper">
          <button class="profile-trigger" id="profile-trigger" aria-haspopup="true" aria-expanded="false">
            <div class="avatar">A</div>
            <div class="profile-info">
              <span class="profile-name">{{ auth()->user()?->name ?? 'Admin Utama' }}</span>
              <span class="profile-role">{{ auth()->user()?->username ?? 'admin' }}</span>
            </div>
            <i class="bi bi-chevron-down profile-chevron"></i>
          </button>

          <div class="profile-dropdown" id="profile-dropdown" aria-hidden="true">
            <div class="dropdown-header">
              <div class="avatar avatar-lg">A</div>
              <div>
                <p class="dropdown-name">{{ auth()->user()?->name ?? 'Admin Utama' }}</p>
                <p class="dropdown-email">{{ auth()->user()?->username ?? 'admin' }}</p>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <ul class="dropdown-menu-list">
              <li><a href="#" class="dropdown-item"><i class="bi bi-person-fill"></i> Profil Saya</a></li>
              <li><a href="#" class="dropdown-item"><i class="bi bi-gear-fill"></i> Pengaturan</a></li>
              <li><a href="#" class="dropdown-item"><i class="bi bi-shield-lock-fill"></i> Keamanan</a></li>
            </ul>
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item dropdown-item-danger logout-button">
                <i class="bi bi-box-arrow-right"></i> Keluar
              </button>
            </form>
          </div>
        </div>
      </div>
    </header>
