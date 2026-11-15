<template>
  <div class="admin-layout" :class="{ 'dark': isDark }">
    <!-- Admin Navigation Sidebar -->
    <AdminSidebar @logout="handleLogout" />

    <!-- Main Content Area -->
    <main class="admin-main">
      <!-- Top Banner / Header -->
      <div class="admin-header">
        <div>
          <div class="breadcrumb">
            <span class="crumb-root">Admin Portal</span>
            <span class="crumb-separator">/</span>
            <span class="crumb-active">User & Access Management</span>
          </div>
          <h1 class="page-title">User & Access Management</h1>
          <p class="page-subtitle">
            Manage public user registrations, review business partner trade credentials, and provision internal staff accounts.
          </p>
        </div>

        <div class="header-actions">
          <button @click="fetchUsers" class="btn-secondary" :disabled="loading" title="Refresh user list">
            <RefreshCw class="btn-icon" :class="{ 'spinning': loading }" />
            <span>Refresh</span>
          </button>

          <button @click="openCreateStaffModal" class="btn-primary" title="Create internal staff account (Workflow 3)">
            <UserPlus class="btn-icon" />
            <span>Create Internal Staff</span>
          </button>
        </div>
      </div>

      <!-- Feedback Banner -->
      <transition name="fade">
        <div v-if="successMessage" class="alert-banner success">
          <CheckCircle2 class="alert-icon" />
          <span>{{ successMessage }}</span>
          <button @click="successMessage = ''" class="alert-close">×</button>
        </div>
      </transition>

      <transition name="fade">
        <div v-if="errorMessage" class="alert-banner error">
          <AlertCircle class="alert-icon" />
          <span>{{ errorMessage }}</span>
          <button @click="errorMessage = ''" class="alert-close">×</button>
        </div>
      </transition>

      <!-- KPI / Stats Summary Cards -->
      <div class="kpi-grid">
        <div class="kpi-card" @click="applyQuickFilter('all')" :class="{ 'kpi-card-active': activeWorkflowTab === 'all' }">
          <div class="kpi-icon total">
            <Users class="icon" />
          </div>
          <div class="kpi-body">
            <span class="kpi-label">Total Users</span>
            <span class="kpi-value">{{ stats.total_users }}</span>
            <span class="kpi-sub">Across all 3 workflows</span>
          </div>
        </div>

        <div class="kpi-card" @click="applyQuickFilter('active')" :class="{ 'kpi-card-active': activeWorkflowTab === 'active' }">
          <div class="kpi-icon active">
            <CheckCircle2 class="icon" />
          </div>
          <div class="kpi-body">
            <span class="kpi-label">Active & Verified</span>
            <span class="kpi-value">{{ stats.active_users }}</span>
            <span class="kpi-sub">Operational accounts</span>
          </div>
        </div>

        <div class="kpi-card kpi-warning" @click="applyQuickFilter('pending')" :class="{ 'kpi-card-active': activeWorkflowTab === 'pending' }">
          <div class="kpi-icon warning">
            <Clock class="icon" />
          </div>
          <div class="kpi-body">
            <div class="kpi-header-row">
              <span class="kpi-label">Pending Partners</span>
              <span v-if="stats.pending_partners > 0" class="badge-alert">{{ stats.pending_partners }} Action Required</span>
            </div>
            <span class="kpi-value warning-text">{{ stats.pending_partners }}</span>
            <span class="kpi-sub">Workflow 2: Trade License Review</span>
          </div>
        </div>

        <div class="kpi-card" @click="applyQuickFilter('staff')" :class="{ 'kpi-card-active': activeWorkflowTab === 'staff' }">
          <div class="kpi-icon staff">
            <ShieldCheck class="icon" />
          </div>
          <div class="kpi-body">
            <span class="kpi-label">Internal Staff</span>
            <span class="kpi-value">{{ internalStaffCount }}</span>
            <span class="kpi-sub">Workflow 3: Finance & Experts</span>
          </div>
        </div>
      </div>

      <!-- Architecture Guide Info Banner -->
      <div class="architecture-strip">
        <div class="arch-col">
          <div class="arch-badge wf1">Workflow 1</div>
          <div class="arch-text">
            <strong>Self Sign-Up (Direct Public)</strong>
            <span>Farmers & Buyers register via phone + OTP for immediate access.</span>
          </div>
        </div>
        <div class="arch-divider"></div>
        <div class="arch-col">
          <div class="arch-badge wf2">Workflow 2</div>
          <div class="arch-text">
            <strong>Partner Request & Approval</strong>
            <span>Suppliers, Transporters & Cooperatives submit trade licenses; locked until admin approval.</span>
          </div>
        </div>
        <div class="arch-divider"></div>
        <div class="arch-col">
          <div class="arch-badge wf3">Workflow 3</div>
          <div class="arch-text">
            <strong>Direct Admin Creation</strong>
            <span>Finance Managers & Agricultural Experts are created exclusively by administrators.</span>
          </div>
        </div>
      </div>

      <!-- Filter Tabs & Controls Card -->
      <div class="table-container-card">
        <div class="card-toolbar">
          <!-- Workflow Filter Tabs -->
          <div class="workflow-tabs">
            <button
              v-for="tab in workflowTabs"
              :key="tab.id"
              class="wf-tab"
              :class="{ 'active': activeWorkflowTab === tab.id }"
              @click="setWorkflowTab(tab.id)"
            >
              <component :is="tab.icon" class="tab-icon" />
              <span>{{ tab.label }}</span>
              <span v-if="tab.badge !== undefined && tab.badge > 0" class="tab-badge" :class="tab.badgeClass">
                {{ tab.badge }}
              </span>
            </button>
          </div>

          <!-- Search and Secondary Filters -->
          <div class="filters-row">
            <div class="search-box">
              <Search class="search-icon" />
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Search by name, email, or phone..."
                @keyup.enter="fetchUsers"
              />
              <button v-if="searchTerm" @click="searchTerm = ''; fetchUsers()" class="clear-search">×</button>
            </div>

            <div class="dropdown-filter">
              <label>Role:</label>
              <select v-model="selectedRole" @change="fetchUsers">
                <option value="all">All Roles</option>
                <option value="farmer">Farmer (WF 1)</option>
                <option value="buyer">Buyer (WF 1)</option>
                <option value="supplier">Supplier (WF 2)</option>
                <option value="transport">Transporter (WF 2)</option>
                <option value="cooperative">Cooperative (WF 2)</option>
                <option value="expert">Agricultural Expert (WF 3)</option>
                <option value="financial">Finance Manager (WF 3)</option>
                <option value="admin">Administrator</option>
              </select>
            </div>

            <div class="dropdown-filter">
              <label>Status:</label>
              <select v-model="selectedStatus" @change="fetchUsers">
                <option value="all">All Statuses</option>
                <option value="active">Active & Operational</option>
                <option value="pending">Pending Approval (Partners)</option>
                <option value="suspended">Suspended / Inactive</option>
              </select>
            </div>

            <button @click="resetFilters" class="btn-ghost" title="Reset all filters">
              <RotateCcw class="btn-icon" />
              <span>Reset</span>
            </button>
          </div>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>User Details</th>
                <th>Role & Workflow</th>
                <th>Trade Credentials / Info</th>
                <th>Status</th>
                <th>Registered</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loading Skeleton -->
              <tr v-if="loading && users.length === 0">
                <td colspan="6" class="table-empty">
                  <div class="spinner-inline"></div>
                  <p>Loading users directory...</p>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-else-if="users.length === 0">
                <td colspan="6" class="table-empty">
                  <UserX class="empty-icon" />
                  <h3>No users found</h3>
                  <p>Try adjusting your search query, status, or role filters.</p>
                  <button @click="resetFilters" class="btn-secondary btn-sm mt-3">Reset Filters</button>
                </td>
              </tr>

              <!-- User Rows -->
              <tr v-for="user in users" :key="user.id" :class="{ 'row-highlight': isPartnerPending(user) }">
                <!-- User Details -->
                <td>
                  <div class="user-cell">
                    <div class="user-avatar" :class="getAvatarClass(user.role)">
                      {{ getInitials(user.name) }}
                    </div>
                    <div class="user-info">
                      <div class="user-name">
                        {{ user.name }}
                        <span v-if="user.id === auth.user?.id" class="badge-you">You</span>
                      </div>
                      <div class="user-email">{{ user.email }}</div>
                      <div v-if="user.phone" class="user-phone">
                        <Phone class="inline-icon" />
                        {{ user.phone }}
                      </div>
                      <div v-if="user.region" class="user-region">
                        <MapPin class="inline-icon" />
                        {{ user.region }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Role & Workflow -->
                <td>
                  <div class="role-cell">
                    <span class="role-pill" :class="getRoleClass(user.role)">
                      <component :is="getRoleIcon(user.role)" class="pill-icon" />
                      {{ formatRoleName(user.role) }}
                    </span>
                    <span class="workflow-indicator" :class="getWorkflowClass(user.role)">
                      {{ getWorkflowLabel(user.role) }}
                    </span>
                  </div>
                </td>

                <!-- Trade Credentials / Partner Info -->
                <td>
                  <div v-if="isPartnerRole(user.role)" class="credentials-cell">
                    <div v-if="getPartnerCompanyName(user)" class="company-name">
                      <Building2 class="inline-icon" />
                      <strong>{{ getPartnerCompanyName(user) }}</strong>
                    </div>
                    <div v-if="getPartnerLicense(user)" class="credential-item">
                      <span class="cred-label">License:</span>
                      <code class="cred-code">{{ getPartnerLicense(user) }}</code>
                    </div>
                    <div v-if="getPartnerTaxId(user)" class="credential-item">
                      <span class="cred-label">TIN:</span>
                      <code class="cred-code">{{ getPartnerTaxId(user) }}</code>
                    </div>
                    <div v-if="user.documents && user.documents.length > 0" class="doc-tag">
                      <FileCheck class="inline-icon" />
                      <span>{{ user.documents.length }} trade doc(s) submitted</span>
                    </div>
                  </div>
                  <div v-else-if="user.role === 'expert'" class="staff-cell">
                    <span class="staff-spec">Specialization: Agronomy & Advisory</span>
                  </div>
                  <div v-else-if="user.role === 'financial'" class="staff-cell">
                    <span class="staff-spec">Role: Escrow & Payout Control</span>
                  </div>
                  <div v-else class="text-muted">
                    <span>Direct Account</span>
                  </div>
                </td>

                <!-- Status -->
                <td>
                  <div class="status-cell">
                    <span v-if="user.is_active" class="status-badge active">
                      <span class="status-dot"></span>
                      Active
                    </span>
                    <span v-else-if="isPartnerPending(user)" class="status-badge pending">
                      <Clock class="status-icon" />
                      Pending Review
                    </span>
                    <span v-else class="status-badge suspended">
                      <Ban class="status-icon" />
                      Suspended
                    </span>
                  </div>
                </td>

                <!-- Registered Date -->
                <td class="date-cell">
                  <div class="date-text">{{ formatDate(user.created_at) }}</div>
                  <div class="date-relative">{{ formatTimeAgo(user.created_at) }}</div>
                </td>

                <!-- Actions -->
                <td class="text-right">
                  <div class="actions-wrapper">
                    <!-- Approve Button for Pending Partners -->
                    <button
                      v-if="isPartnerPending(user)"
                      @click="approvePartner(user)"
                      class="btn-action approve"
                      title="Approve partner trade license & activate account"
                      :disabled="actionLoading === user.id"
                    >
                      <CheckCircle2 class="action-icon" />
                      <span>Approve</span>
                    </button>

                    <!-- View Dossier / Details Button -->
                    <button
                      @click="viewUserDetails(user)"
                      class="btn-action view"
                      title="View full credentials and document dossier"
                    >
                      <Eye class="action-icon" />
                    </button>

                    <!-- Suspend / Reactivate Button -->
                    <button
                      v-if="user.id !== auth.user?.id"
                      @click="toggleUserStatus(user)"
                      class="btn-action"
                      :class="user.is_active ? 'suspend' : 'reactivate'"
                      :title="user.is_active ? 'Suspend user access' : 'Activate user'"
                      :disabled="actionLoading === user.id"
                    >
                      <Ban v-if="user.is_active" class="action-icon" />
                      <CheckCircle2 v-else class="action-icon" />
                    </button>

                    <!-- Delete Button -->
                    <button
                      v-if="user.id !== auth.user?.id"
                      @click="confirmDeleteUser(user)"
                      class="btn-action delete"
                      title="Delete user account"
                      :disabled="actionLoading === user.id"
                    >
                      <Trash2 class="action-icon" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Bar -->
        <div class="pagination-bar">
          <div class="pagination-info">
            Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} users
          </div>
          <div class="pagination-controls">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1 || loading"
              class="btn-page"
            >
              <ChevronLeft class="page-icon" />
              <span>Previous</span>
            </button>
            <span class="page-indicator">
              Page {{ pagination.current_page }} of {{ pagination.last_page || 1 }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page || loading"
              class="btn-page"
            >
              <span>Next</span>
              <ChevronRight class="page-icon" />
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- MODAL 1: CREATE INTERNAL STAFF (WORKFLOW 3) -->
    <div v-if="showCreateStaffModal" class="modal-backdrop" @click.self="showCreateStaffModal = false">
      <div class="modal-dialog">
        <div class="modal-header">
          <div class="modal-title-group">
            <div class="modal-icon-badge staff">
              <UserPlus class="icon" />
            </div>
            <div>
              <h2 class="modal-title">Create Internal Staff Account</h2>
              <p class="modal-subtitle">Workflow 3: Direct Admin Creation for Internal Staff</p>
            </div>
          </div>
          <button @click="showCreateStaffModal = false" class="modal-close-btn">×</button>
        </div>

        <form @submit.prevent="submitCreateStaff" class="modal-body">
          <div class="modal-notice">
            <ShieldCheck class="notice-icon" />
            <div class="notice-text">
              <strong>Workflow 3 Policy:</strong> Public registration is strictly forbidden for internal staff. Staff accounts created here are instantly provisioned with active status (<code>is_active: true</code>) and role-specific permissions.
            </div>
          </div>

          <!-- Role Selection Cards -->
          <div class="form-group">
            <label class="form-label required">Select Staff Role</label>
            <div class="role-selection-grid">
              <label
                class="role-choice-card"
                :class="{ 'selected': staffForm.role === 'expert' }"
              >
                <input type="radio" v-model="staffForm.role" value="expert" class="sr-only" />
                <div class="choice-icon expert">
                  <GraduationCap class="icon" />
                </div>
                <div class="choice-content">
                  <div class="choice-title">Agricultural Expert</div>
                  <div class="choice-desc">Provide agronomy consultations, disease diagnosis, and crop advisory services.</div>
                </div>
              </label>

              <label
                class="role-choice-card"
                :class="{ 'selected': staffForm.role === 'financial' }"
              >
                <input type="radio" v-model="staffForm.role" value="financial" class="sr-only" />
                <div class="choice-icon financial">
                  <BadgeDollarSign class="icon" />
                </div>
                <div class="choice-content">
                  <div class="choice-title">Finance Manager</div>
                  <div class="choice-desc">Oversee escrow releases, payment dispute reversals, and financial reporting.</div>
                </div>
              </label>

              <label
                class="role-choice-card"
                :class="{ 'selected': staffForm.role === 'admin' }"
              >
                <input type="radio" v-model="staffForm.role" value="admin" class="sr-only" />
                <div class="choice-icon admin">
                  <ShieldAlert class="icon" />
                </div>
                <div class="choice-content">
                  <div class="choice-title">Co-Administrator</div>
                  <div class="choice-desc">Full administrative privileges over user access, compliance, and platform settings.</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Staff Details Inputs -->
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label required">Full Name</label>
              <input
                v-model="staffForm.name"
                type="text"
                placeholder="e.g. Dr. Yohannes Gebre"
                required
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label class="form-label required">Work Email Address</label>
              <input
                v-model="staffForm.email"
                type="email"
                placeholder="yohannes@agritech.et"
                required
                class="form-input"
              />
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Phone Number</label>
              <input
                v-model="staffForm.phone"
                type="tel"
                placeholder="+251 91 123 4567"
                class="form-input"
              />
            </div>

            <div class="form-group">
              <label class="form-label">Operating Region / Base</label>
              <select v-model="staffForm.region" class="form-input">
                <option value="Addis Ababa">Addis Ababa (Headquarters)</option>
                <option value="Oromia">Oromia Regional Hub</option>
                <option value="Amhara">Amhara Regional Hub</option>
                <option value="Sidama">Sidama Regional Hub</option>
                <option value="Tigray">Tigray Regional Hub</option>
                <option value="SNNPR">Central Ethiopia Regional Hub</option>
              </select>
            </div>
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <div class="label-with-action">
              <label class="form-label required">Initial Password</label>
              <button type="button" @click="generatePassword" class="btn-text-action">
                <Sparkles class="mini-icon" />
                <span>Auto-Generate Secure Password</span>
              </button>
            </div>
            <div class="password-input-wrapper">
              <input
                v-model="staffForm.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Minimum 6 characters"
                required
                minlength="6"
                class="form-input"
              />
              <button type="button" @click="showPassword = !showPassword" class="password-toggle">
                <Eye v-if="!showPassword" class="icon" />
                <EyeOff v-else class="icon" />
              </button>
            </div>
            <span class="input-hint">Provide these login credentials securely to the staff member.</span>
          </div>

          <div v-if="modalError" class="alert-banner error mt-3">
            <AlertCircle class="alert-icon" />
            <span>{{ modalError }}</span>
          </div>

          <div class="modal-footer">
            <button type="button" @click="showCreateStaffModal = false" class="btn-secondary">
              Cancel
            </button>
            <button type="submit" class="btn-primary" :disabled="submittingStaff">
              <UserPlus class="btn-icon" />
              <span>{{ submittingStaff ? 'Creating Staff Profile...' : 'Create & Activate Account' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: PARTNER VERIFICATION & LEGAL CREDENTIALS DOSSIER (WORKFLOW 2) -->
    <div v-if="showDetailsModal && selectedUser" class="modal-backdrop" @click.self="showDetailsModal = false">
      <div class="modal-dialog modal-lg">
        <div class="modal-header">
          <div class="modal-title-group">
            <div class="modal-icon-badge" :class="getRoleClass(selectedUser.role)">
              <component :is="getRoleIcon(selectedUser.role)" class="icon" />
            </div>
            <div>
              <h2 class="modal-title">{{ selectedUser.name }}</h2>
              <p class="modal-subtitle">
                {{ formatRoleName(selectedUser.role) }} Dossier & Verification Profile
              </p>
            </div>
          </div>
          <button @click="showDetailsModal = false" class="modal-close-btn">×</button>
        </div>

        <div class="modal-body">
          <!-- Status Banner -->
          <div class="dossier-status-banner" :class="isPartnerPending(selectedUser) ? 'pending' : (selectedUser.is_active ? 'active' : 'suspended')">
            <div class="banner-icon-col">
              <Clock v-if="isPartnerPending(selectedUser)" class="b-icon" />
              <CheckCircle2 v-else-if="selectedUser.is_active" class="b-icon" />
              <Ban v-else class="b-icon" />
            </div>
            <div class="banner-text-col">
              <h4>
                Account Status:
                {{ isPartnerPending(selectedUser) ? 'Pending Legal Trade Approval' : (selectedUser.is_active ? 'Active & Fully Verified' : 'Suspended / Deactivated') }}
              </h4>
              <p v-if="isPartnerPending(selectedUser)">
                This partner registered under <strong>Workflow 2 (Partner Request & Approval)</strong>. Account remains locked from login until trade credentials are confirmed by an administrator.
              </p>
              <p v-else-if="selectedUser.is_active">
                Account is verified and authorized for commercial operations on the AgriTech platform.
              </p>
              <p v-else>
                Account access has been deactivated. User cannot access the platform.
              </p>
            </div>
          </div>

          <!-- Partner Legal Information Section -->
          <div v-if="isPartnerRole(selectedUser.role)" class="dossier-section">
            <h3 class="section-heading">
              <Building2 class="heading-icon" />
              Legal Business Entity & Credentials
            </h3>
            <div class="info-grid">
              <div class="info-card">
                <span class="info-label">Registered Company Name</span>
                <span class="info-value strong">{{ getPartnerCompanyName(selectedUser) || 'Not provided' }}</span>
              </div>
              <div class="info-card">
                <span class="info-label">Partner Classification</span>
                <span class="info-value">{{ formatRoleName(selectedUser.role) }}</span>
              </div>
              <div class="info-card">
                <span class="info-label">Trade License Number</span>
                <span class="info-value mono">{{ getPartnerLicense(selectedUser) || 'N/A' }}</span>
              </div>
              <div class="info-card">
                <span class="info-label">Tax Identification Number (TIN)</span>
                <span class="info-value mono">{{ getPartnerTaxId(selectedUser) || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <!-- Representative Contact Section -->
          <div class="dossier-section">
            <h3 class="section-heading">
              <User class="heading-icon" />
              Authorized Representative Contact
            </h3>
            <div class="info-grid">
              <div class="info-card">
                <span class="info-label">Contact Person</span>
                <span class="info-value">{{ selectedUser.name }}</span>
              </div>
              <div class="info-card">
                <span class="info-label">Email Address</span>
                <span class="info-value">{{ selectedUser.email }}</span>
              </div>
              <div class="info-card">
                <span class="info-label">Phone Number</span>
                <span class="info-value">{{ selectedUser.phone || 'Not recorded' }}</span>
              </div>
              <div class="info-card">
                <span class="info-label">Region / Territory</span>
                <span class="info-value">{{ selectedUser.region || 'Addis Ababa' }}</span>
              </div>
            </div>
          </div>

          <!-- Submitted Legal Trade Documents -->
          <div class="dossier-section">
            <h3 class="section-heading">
              <FileText class="heading-icon" />
              Trade Documents & Verification Audit
            </h3>
            <div v-if="selectedUser.documents && selectedUser.documents.length > 0" class="docs-list">
              <div v-for="doc in selectedUser.documents" :key="doc.id" class="doc-card">
                <div class="doc-left">
                  <FileCheck class="doc-file-icon" />
                  <div>
                    <div class="doc-title">{{ doc.document_name || 'Trade License / Registration' }}</div>
                    <div class="doc-meta">Type: {{ doc.document_type }} • Submitted: {{ formatDate(doc.created_at) }}</div>
                  </div>
                </div>
                <div class="doc-right">
                  <span class="doc-status" :class="doc.verification_status">
                    {{ doc.verification_status || 'Pending Review' }}
                  </span>
                </div>
              </div>
            </div>
            <div v-else class="empty-docs-box">
              <FileQuestion class="empty-icon" />
              <p>No external document files uploaded. Credentials recorded via digital registration audit trail.</p>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="showDetailsModal = false" class="btn-secondary">Close Dossier</button>

            <!-- Partner Approval Actions -->
            <button
              v-if="isPartnerPending(selectedUser)"
              @click="approvePartner(selectedUser); showDetailsModal = false"
              class="btn-primary btn-success"
              :disabled="actionLoading === selectedUser.id"
            >
              <CheckCircle2 class="btn-icon" />
              <span>Approve & Activate Partner Account</span>
            </button>

            <button
              v-if="selectedUser.is_active && selectedUser.id !== auth.user?.id"
              @click="toggleUserStatus(selectedUser); showDetailsModal = false"
              class="btn-danger"
              :disabled="actionLoading === selectedUser.id"
            >
              <Ban class="btn-icon" />
              <span>Suspend Account</span>
            </button>

            <button
              v-if="!selectedUser.is_active && !isPartnerPending(selectedUser)"
              @click="toggleUserStatus(selectedUser); showDetailsModal = false"
              class="btn-primary"
              :disabled="actionLoading === selectedUser.id"
            >
              <CheckCircle2 class="btn-icon" />
              <span>Reactivate Account</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useTheme } from '@/composables/useTheme'
import { adminAPI } from '@/api/admin'
import AdminSidebar from '@/components/Sidebar/AdminSidebar.vue'
import {
  Users, UserPlus, RefreshCw, CheckCircle2, AlertCircle, Clock, ShieldCheck,
  ShieldAlert, Search, RotateCcw, UserX, Phone, MapPin, Building2, FileCheck,
  FileText, FileQuestion, Eye, EyeOff, Ban, Trash2, ChevronLeft, ChevronRight,
  GraduationCap, BadgeDollarSign, Sparkles, Sprout, ShoppingCart, Truck,
  Store, Landmark, User
} from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStore()
const { isDark } = useTheme()

// State
const users = ref<any[]>([])
const loading = ref(false)
const actionLoading = ref<number | null>(null)
const successMessage = ref('')
const errorMessage = ref('')
const modalError = ref('')

// Filters & Pagination
const searchTerm = ref('')
const selectedRole = ref('all')
const selectedStatus = ref('all')
const activeWorkflowTab = ref('all')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
  per_page: 15
})

// Statistics
const stats = ref({
  total_users: 0,
  active_users: 0,
  pending_partners: 0,
  suspended_users: 0,
  by_role: {
    farmer: 0,
    buyer: 0,
    supplier: 0,
    transport: 0,
    cooperative: 0,
    expert: 0,
    financial: 0,
    admin: 0
  }
})

// Modals State
const showCreateStaffModal = ref(false)
const showDetailsModal = ref(false)
const selectedUser = ref<any>(null)
const submittingStaff = ref(false)
const showPassword = ref(false)

const staffForm = ref({
  name: '',
  email: '',
  phone: '',
  role: 'expert',
  region: 'Addis Ababa',
  password: ''
})

// Workflow Filter Tabs Definition
const workflowTabs = computed(() => [
  { id: 'all', label: 'All Users', icon: Users },
  {
    id: 'pending',
    label: 'Pending Partners',
    icon: Clock,
    badge: stats.value.pending_partners,
    badgeClass: 'badge-amber'
  },
  { id: 'wf1', label: 'WF 1: Direct Sign-Ups', icon: Sprout },
  { id: 'wf2', label: 'WF 2: Business Partners', icon: Building2 },
  { id: 'wf3', label: 'WF 3: Internal Staff', icon: ShieldCheck },
  { id: 'suspended', label: 'Suspended', icon: Ban }
])

const internalStaffCount = computed(() => {
  return (stats.value.by_role?.expert || 0) + (stats.value.by_role?.financial || 0)
})

// Methods
const fetchStats = async () => {
  try {
    const res = await adminAPI.getUserStats()
    if (res.data && res.data.data) {
      stats.value = res.data.data
    }
  } catch (err) {
    console.warn('Failed to load user statistics:', err)
  }
}

const fetchUsers = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const params: any = {
      page: pagination.value.current_page,
      limit: pagination.value.per_page
    }

    if (searchTerm.value.trim()) {
      params.search = searchTerm.value.trim()
    }

    // Role filter
    if (selectedRole.value !== 'all') {
      params.role = selectedRole.value
    }

    // Status filter
    if (selectedStatus.value !== 'all') {
      params.status = selectedStatus.value
    }

    // Apply workflow tab specific filters if set
    if (activeWorkflowTab.value === 'pending') {
      params.status = 'pending'
    } else if (activeWorkflowTab.value === 'suspended') {
      params.status = 'suspended'
    }

    const res = await adminAPI.getUsers(params)
    const data = res.data?.data || res.data

    if (data && Array.isArray(data.data)) {
      let result = data.data

      // Client-side workflow filter refinement for tab views
      if (activeWorkflowTab.value === 'wf1') {
        result = result.filter((u: any) => ['farmer', 'buyer'].includes(u.role))
      } else if (activeWorkflowTab.value === 'wf2') {
        result = result.filter((u: any) => ['supplier', 'transport', 'cooperative'].includes(u.role))
      } else if (activeWorkflowTab.value === 'wf3') {
        result = result.filter((u: any) => ['expert', 'financial', 'admin'].includes(u.role))
      }

      users.value = result
      pagination.value = {
        current_page: data.current_page || 1,
        last_page: data.last_page || 1,
        total: data.total || result.length,
        from: data.from || 1,
        to: data.to || result.length,
        per_page: data.per_page || 15
      }
    } else if (Array.isArray(data)) {
      users.value = data
    }
  } catch (err: any) {
    console.error('Failed to fetch users:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to load user directory.'
  } finally {
    loading.value = false
  }
}

const setWorkflowTab = (tabId: string) => {
  activeWorkflowTab.value = tabId
  pagination.value.current_page = 1

  if (tabId === 'pending') {
    selectedStatus.value = 'pending'
    selectedRole.value = 'all'
  } else if (tabId === 'suspended') {
    selectedStatus.value = 'suspended'
    selectedRole.value = 'all'
  } else if (tabId === 'wf1') {
    selectedStatus.value = 'all'
    selectedRole.value = 'all'
  } else if (tabId === 'wf2') {
    selectedStatus.value = 'all'
    selectedRole.value = 'all'
  } else if (tabId === 'wf3') {
    selectedStatus.value = 'all'
    selectedRole.value = 'all'
  } else {
    selectedStatus.value = 'all'
    selectedRole.value = 'all'
  }

  fetchUsers()
}

const applyQuickFilter = (type: string) => {
  if (type === 'all') {
    setWorkflowTab('all')
  } else if (type === 'active') {
    activeWorkflowTab.value = 'all'
    selectedStatus.value = 'active'
    fetchUsers()
  } else if (type === 'pending') {
    setWorkflowTab('pending')
  } else if (type === 'staff') {
    setWorkflowTab('wf3')
  }
}

const resetFilters = () => {
  searchTerm.value = ''
  selectedRole.value = 'all'
  selectedStatus.value = 'all'
  activeWorkflowTab.value = 'all'
  pagination.value.current_page = 1
  fetchUsers()
}

const changePage = (newPage: number) => {
  if (newPage < 1 || newPage > pagination.value.last_page) return
  pagination.value.current_page = newPage
  fetchUsers()
}

// Approve Partner Account (Workflow 2)
const approvePartner = async (user: any) => {
  actionLoading.value = user.id
  try {
    const res = await adminAPI.approveUser(user.id)
    successMessage.value = res.data?.message || `Partner ${user.name} approved and activated successfully!`
    user.is_active = true

    // Refresh metrics & list
    await fetchStats()
    await fetchUsers()
    setTimeout(() => { successMessage.value = '' }, 5000)
  } catch (err: any) {
    console.error('Failed to approve partner:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to approve partner account.'
  } finally {
    actionLoading.value = null
  }
}

// Suspend / Reactivate User
const toggleUserStatus = async (user: any) => {
  actionLoading.value = user.id
  try {
    if (user.is_active) {
      const res = await adminAPI.suspendUser(user.id)
      successMessage.value = res.data?.message || `User ${user.name} has been suspended.`
      user.is_active = false
    } else {
      const res = await adminAPI.approveUser(user.id)
      successMessage.value = res.data?.message || `User ${user.name} has been activated.`
      user.is_active = true
    }
    await fetchStats()
    setTimeout(() => { successMessage.value = '' }, 4000)
  } catch (err: any) {
    console.error('Failed to update user status:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to update user status.'
  } finally {
    actionLoading.value = null
  }
}

// Delete User
const confirmDeleteUser = async (user: any) => {
  if (!confirm(`Are you sure you want to permanently delete user "${user.name}" (${user.email})?`)) {
    return
  }

  actionLoading.value = user.id
  try {
    await adminAPI.deleteUser(user.id)
    successMessage.value = `User ${user.name} deleted successfully.`
    users.value = users.value.filter(u => u.id !== user.id)
    await fetchStats()
    setTimeout(() => { successMessage.value = '' }, 4000)
  } catch (err: any) {
    console.error('Failed to delete user:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to delete user.'
  } finally {
    actionLoading.value = null
  }
}

// Open Details Modal
const viewUserDetails = (user: any) => {
  selectedUser.value = user
  showDetailsModal.value = true
}

// Open Create Staff Modal
const openCreateStaffModal = () => {
  staffForm.value = {
    name: '',
    email: '',
    phone: '',
    role: 'expert',
    region: 'Addis Ababa',
    password: generateRandomPassword()
  }
  modalError.value = ''
  showPassword.value = false
  showCreateStaffModal.value = true
}

const generatePassword = () => {
  staffForm.value.password = generateRandomPassword()
}

const generateRandomPassword = () => {
  const chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*'
  let pwd = ''
  for (let i = 0; i < 10; i++) {
    pwd += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return pwd
}

// Submit Create Staff (Workflow 3)
const submitCreateStaff = async () => {
  modalError.value = ''
  submittingStaff.value = true
  try {
    const payload = {
      name: staffForm.value.name,
      email: staffForm.value.email,
      phone: staffForm.value.phone || null,
      role: staffForm.value.role,
      region: staffForm.value.region,
      password: staffForm.value.password
    }

    const res = await adminAPI.createUser(payload)
    successMessage.value = res.data?.message || `Staff profile for ${staffForm.value.name} created and activated successfully.`
    showCreateStaffModal.value = false

    await fetchStats()
    await fetchUsers()
    setTimeout(() => { successMessage.value = '' }, 6000)
  } catch (err: any) {
    console.error('Failed to create staff account:', err)
    modalError.value = err.response?.data?.message || 'Failed to create internal staff account. Verify email uniqueness.'
  } finally {
    submittingStaff.value = false
  }
}

// Logout handler for Sidebar
const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

// Helpers
const isPartnerRole = (role: string) => {
  return ['supplier', 'transport', 'cooperative'].includes(role)
}

const isPartnerPending = (user: any) => {
  return !user.is_active && isPartnerRole(user.role)
}

const formatRoleName = (role: string) => {
  const map: Record<string, string> = {
    farmer: 'Farmer',
    buyer: 'Buyer',
    supplier: 'Supplier',
    transport: 'Transporter',
    cooperative: 'Cooperative',
    expert: 'Agricultural Expert',
    financial: 'Finance Manager',
    admin: 'Administrator'
  }
  return map[role] || (role ? role.toUpperCase() : 'Unknown')
}

const getRoleIcon = (role: string) => {
  switch (role) {
    case 'farmer': return Sprout
    case 'buyer': return ShoppingCart
    case 'supplier': return Store
    case 'transport': return Truck
    case 'cooperative': return Landmark
    case 'expert': return GraduationCap
    case 'financial': return BadgeDollarSign
    case 'admin': return ShieldAlert
    default: return User
  }
}

const getRoleClass = (role: string) => {
  return `role-${role}`
}

const getAvatarClass = (role: string) => {
  return `avatar-${role}`
}

const getWorkflowLabel = (role: string) => {
  if (['farmer', 'buyer'].includes(role)) return 'WF 1: Direct Sign-Up'
  if (['supplier', 'transport', 'cooperative'].includes(role)) return 'WF 2: Partner Review'
  return 'WF 3: Staff Provision'
}

const getWorkflowClass = (role: string) => {
  if (['farmer', 'buyer'].includes(role)) return 'wf-badge-1'
  if (['supplier', 'transport', 'cooperative'].includes(role)) return 'wf-badge-2'
  return 'wf-badge-3'
}

const getPartnerCompanyName = (user: any) => {
  return user.supplier?.company_name ||
    user.transporter?.company_name ||
    user.cooperative?.name ||
    user.cooperative?.company_name ||
    null
}

const getPartnerLicense = (user: any) => {
  return user.supplier?.license_number ||
    user.transporter?.license_number ||
    user.cooperative?.registration_number ||
    user.documents?.find((d: any) => d.document_type === 'trade_license')?.document_name ||
    null
}

const getPartnerTaxId = (user: any) => {
  return user.supplier?.tax_id ||
    user.transporter?.tax_id ||
    user.cooperative?.tax_id ||
    null
}

const getInitials = (name: string) => {
  if (!name) return 'U'
  return name
    .split(' ')
    .filter(Boolean)
    .map(w => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return 'N/A'
  try {
    return new Date(dateStr).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    })
  } catch {
    return dateStr
  }
}

const formatTimeAgo = (dateStr: string) => {
  if (!dateStr) return ''
  try {
    const date = new Date(dateStr)
    const now = new Date()
    const diffMs = now.getTime() - date.getTime()
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
    if (diffHours < 24) {
      if (diffHours <= 0) return 'Just now'
      return `${diffHours}h ago`
    }
    const diffDays = Math.floor(diffHours / 24)
    if (diffDays < 30) return `${diffDays}d ago`
    return ''
  } catch {
    return ''
  }
}

onMounted(async () => {
  await fetchStats()
  await fetchUsers()
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.admin-layout {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
  color: #0f172a;
}

.admin-layout.dark {
  background-color: #0b0f19;
  color: #f1f5f9;
}

.admin-main {
  margin-left: 280px;
  width: calc(100% - 280px);
  padding: 32px 40px;
  min-height: 100vh;
  transition: all 0.2s ease;
}

/* Header */
.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
  gap: 20px;
  flex-wrap: wrap;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  margin-bottom: 6px;
}

.crumb-separator {
  color: #94a3b8;
}

.crumb-active {
  color: #2563eb;
  font-weight: 600;
}

.page-title {
  font-size: 28px;
  font-weight: 800;
  letter-spacing: -0.02em;
  margin: 0 0 6px 0;
  color: #0f172a;
}

.dark .page-title {
  color: #ffffff;
}

.page-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  max-width: 650px;
  line-height: 1.5;
}

.dark .page-subtitle {
  color: #94a3b8;
}

.header-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* Buttons */
.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #2563eb;
  color: #ffffff;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn-primary:hover:not(:disabled) {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
}

.btn-primary.btn-success {
  background: #16a34a;
  box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);
}

.btn-primary.btn-success:hover {
  background: #15803d;
  box-shadow: 0 4px 10px rgba(22, 163, 74, 0.3);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #ffffff;
  color: #334155;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  border: 1px solid #cbd5e1;
  cursor: pointer;
  transition: all 0.2s;
}

.dark .btn-secondary {
  background: #1e293b;
  color: #e2e8f0;
  border-color: #334155;
}

.btn-secondary:hover:not(:disabled) {
  background: #f1f5f9;
  border-color: #94a3b8;
}

.dark .btn-secondary:hover:not(:disabled) {
  background: #334155;
}

.btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  color: #64748b;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-ghost:hover {
  color: #0f172a;
  background: #f1f5f9;
}

.dark .btn-ghost:hover {
  color: #ffffff;
  background: #1e293b;
}

.btn-danger {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #dc2626;
  color: white;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-danger:hover {
  background: #b91c1c;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Alert Banners */
.alert-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 18px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 14px;
  position: relative;
}

.alert-banner.success {
  background: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.dark .alert-banner.success {
  background: rgba(16, 185, 129, 0.15);
  color: #6ee7b7;
  border-color: rgba(16, 185, 129, 0.3);
}

.alert-banner.error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.dark .alert-banner.error {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.3);
}

.alert-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.alert-close {
  margin-left: auto;
  background: none;
  border: none;
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
  color: inherit;
  opacity: 0.6;
}

.alert-close:hover {
  opacity: 1;
}

/* KPI Summary Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 18px;
  margin-bottom: 22px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.dark .kpi-card {
  background: #131b2e;
  border-color: #1e293b;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
  border-color: #cbd5e1;
}

.dark .kpi-card:hover {
  border-color: #334155;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
}

.kpi-card-active {
  border-color: #2563eb !important;
  box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2) !important;
}

.kpi-warning {
  border-color: #fde68a;
  background: #fffbeb;
}

.dark .kpi-warning {
  background: #181923;
  border-color: #78350f;
}

.kpi-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon .icon {
  width: 24px;
  height: 24px;
}

.kpi-icon.total {
  background: #eff6ff;
  color: #2563eb;
}

.dark .kpi-icon.total {
  background: rgba(37, 99, 235, 0.2);
  color: #60a5fa;
}

.kpi-icon.active {
  background: #ecfdf5;
  color: #10b981;
}

.dark .kpi-icon.active {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
}

.kpi-icon.warning {
  background: #fff7ed;
  color: #f59e0b;
}

.dark .kpi-icon.warning {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
}

.kpi-icon.staff {
  background: #f5f3ff;
  color: #8b5cf6;
}

.dark .kpi-icon.staff {
  background: rgba(139, 92, 246, 0.2);
  color: #a78bfa;
}

.kpi-body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.kpi-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.kpi-label {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #64748b;
  margin-bottom: 4px;
}

.dark .kpi-label {
  color: #94a3b8;
}

.kpi-value {
  font-size: 26px;
  font-weight: 800;
  line-height: 1.1;
  color: #0f172a;
}

.dark .kpi-value {
  color: #ffffff;
}

.warning-text {
  color: #d97706 !important;
}

.dark .warning-text {
  color: #fbbf24 !important;
}

.kpi-sub {
  font-size: 12px;
  color: #94a3b8;
  margin-top: 4px;
}

.badge-alert {
  font-size: 11px;
  font-weight: 700;
  background: #f59e0b;
  color: #ffffff;
  padding: 2px 8px;
  border-radius: 9999px;
  letter-spacing: 0.02em;
}

/* Architecture Strip */
.architecture-strip {
  display: flex;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 24px;
  gap: 20px;
  align-items: center;
}

.dark .architecture-strip {
  background: #131b2e;
  border-color: #1e293b;
}

.arch-col {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
}

.arch-badge {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  padding: 4px 8px;
  border-radius: 6px;
  white-space: nowrap;
}

.arch-badge.wf1 {
  background: #ecfdf5;
  color: #059669;
}

.arch-badge.wf2 {
  background: #fffbeb;
  color: #d97706;
}

.arch-badge.wf3 {
  background: #eff6ff;
  color: #2563eb;
}

.dark .arch-badge.wf1 {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
}

.dark .arch-badge.wf2 {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
}

.dark .arch-badge.wf3 {
  background: rgba(37, 99, 235, 0.2);
  color: #60a5fa;
}

.arch-text {
  display: flex;
  flex-direction: column;
  font-size: 12px;
  line-height: 1.4;
}

.arch-text strong {
  font-size: 13px;
  color: #0f172a;
}

.dark .arch-text strong {
  color: #f1f5f9;
}

.arch-text span {
  color: #64748b;
}

.dark .arch-text span {
  color: #94a3b8;
}

.arch-divider {
  width: 1px;
  height: 38px;
  background: #e2e8f0;
}

.dark .arch-divider {
  background: #1e293b;
}

/* Table Container Card */
.table-container-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.dark .table-container-card {
  background: #131b2e;
  border-color: #1e293b;
}

.card-toolbar {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.dark .card-toolbar {
  border-color: #1e293b;
}

/* Workflow Tabs */
.workflow-tabs {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 12px;
}

.dark .workflow-tabs {
  border-color: #1e293b;
}

.wf-tab {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  background: transparent;
  color: #64748b;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.dark .wf-tab {
  color: #94a3b8;
}

.wf-tab:hover {
  background: #f8fafc;
  color: #0f172a;
}

.dark .wf-tab:hover {
  background: #1e293b;
  color: #ffffff;
}

.wf-tab.active {
  background: #eff6ff;
  color: #2563eb;
  border-color: #bfdbfe;
}

.dark .wf-tab.active {
  background: rgba(37, 99, 235, 0.2);
  color: #60a5fa;
  border-color: rgba(37, 99, 235, 0.4);
}

.tab-icon {
  width: 15px;
  height: 15px;
}

.tab-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 9999px;
  line-height: 1;
}

.badge-amber {
  background: #f59e0b;
  color: #ffffff;
}

/* Filters Row */
.filters-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #94a3b8;
}

.search-box input {
  width: 100%;
  padding: 9px 36px 9px 36px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 13px;
  background: #f8fafc;
  color: #0f172a;
  outline: none;
  transition: all 0.2s;
}

.dark .search-box input {
  background: #0f172a;
  border-color: #334155;
  color: #f1f5f9;
}

.search-box input:focus {
  border-color: #2563eb;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.dark .search-box input:focus {
  background: #1e293b;
}

.clear-search {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  font-size: 16px;
  color: #94a3b8;
  cursor: pointer;
}

.dropdown-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}

.dropdown-filter select {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 13px;
  background: #ffffff;
  color: #0f172a;
  outline: none;
  cursor: pointer;
}

.dark .dropdown-filter select {
  background: #0f172a;
  border-color: #334155;
  color: #f1f5f9;
}

/* Table Responsive */
.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13px;
}

.data-table th {
  padding: 14px 20px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #64748b;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.dark .data-table th {
  background: #0f172a;
  color: #94a3b8;
  border-color: #1e293b;
}

.data-table td {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.dark .data-table td {
  border-color: #1e293b;
}

.data-table tr:hover td {
  background: #f8fafc;
}

.dark .data-table tr:hover td {
  background: #182238;
}

.row-highlight td {
  background: #fffbeb;
}

.dark .row-highlight td {
  background: #1c1d28;
}

.text-right {
  text-align: right;
}

/* User Details Cell */
.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  color: white;
  flex-shrink: 0;
}

.avatar-farmer { background: #16a34a; }
.avatar-buyer { background: #2563eb; }
.avatar-supplier { background: #9333ea; }
.avatar-transport { background: #d97706; }
.avatar-cooperative { background: #0891b2; }
.avatar-expert { background: #059669; }
.avatar-financial { background: #0284c7; }
.avatar-admin { background: #e11d48; }

.user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.user-name {
  font-weight: 700;
  color: #0f172a;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.dark .user-name {
  color: #f1f5f9;
}

.badge-you {
  font-size: 10px;
  font-weight: 700;
  background: #e0e7ff;
  color: #3730a3;
  padding: 1px 5px;
  border-radius: 4px;
}

.user-email {
  color: #64748b;
  font-size: 12px;
}

.dark .user-email {
  color: #94a3b8;
}

.user-phone, .user-region {
  font-size: 11px;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 4px;
}

.inline-icon {
  width: 12px;
  height: 12px;
}

/* Role Cell */
.role-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: flex-start;
}

.role-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.pill-icon {
  width: 13px;
  height: 13px;
}

.role-farmer { background: #ecfdf5; color: #059669; }
.role-buyer { background: #eff6ff; color: #2563eb; }
.role-supplier { background: #faf5ff; color: #9333ea; }
.role-transport { background: #fffbeb; color: #d97706; }
.role-cooperative { background: #ecfeff; color: #0891b2; }
.role-expert { background: #f0fdf4; color: #16a34a; }
.role-financial { background: #f0f9ff; color: #0284c7; }
.role-admin { background: #fff1f2; color: #e11d48; }

.dark .role-farmer { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.dark .role-buyer { background: rgba(37, 99, 235, 0.2); color: #60a5fa; }
.dark .role-supplier { background: rgba(147, 51, 234, 0.2); color: #c084fc; }
.dark .role-transport { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.dark .role-cooperative { background: rgba(6, 182, 212, 0.2); color: #22d3ee; }
.dark .role-expert { background: rgba(22, 163, 74, 0.2); color: #4ade80; }
.dark .role-financial { background: rgba(2, 132, 199, 0.2); color: #38bdf8; }
.dark .role-admin { background: rgba(225, 29, 72, 0.2); color: #fb7185; }

.workflow-indicator {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.02em;
}

.wf-badge-1 { background: #f1f5f9; color: #475569; }
.wf-badge-2 { background: #fef3c7; color: #92400e; }
.wf-badge-3 { background: #e0e7ff; color: #3730a3; }

.dark .wf-badge-1 { background: #1e293b; color: #94a3b8; }
.dark .wf-badge-2 { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.dark .wf-badge-3 { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; }

/* Credentials Cell */
.credentials-cell {
  display: flex;
  flex-direction: column;
  gap: 3px;
  font-size: 12px;
}

.company-name {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #0f172a;
}

.dark .company-name {
  color: #f1f5f9;
}

.credential-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.cred-label {
  color: #94a3b8;
  font-size: 11px;
}

.cred-code {
  font-family: monospace;
  background: #f1f5f9;
  color: #334155;
  padding: 1px 4px;
  border-radius: 4px;
  font-size: 11px;
}

.dark .cred-code {
  background: #1e293b;
  color: #cbd5e1;
}

.doc-tag {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #059669;
  font-weight: 500;
}

.staff-cell {
  font-size: 12px;
  color: #64748b;
}

.dark .staff-cell {
  color: #94a3b8;
}

.text-muted {
  color: #94a3b8;
  font-size: 12px;
}

/* Status Cell */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.active {
  background: #ecfdf5;
  color: #059669;
}

.dark .status-badge.active {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
}

.status-badge.pending {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}

.dark .status-badge.pending {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
  border-color: rgba(245, 158, 11, 0.4);
}

.status-badge.suspended {
  background: #fef2f2;
  color: #dc2626;
}

.dark .status-badge.suspended {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.status-icon {
  width: 12px;
  height: 12px;
}

/* Date Cell */
.date-cell {
  font-size: 12px;
  color: #334155;
}

.dark .date-cell {
  color: #cbd5e1;
}

.date-relative {
  font-size: 11px;
  color: #94a3b8;
}

/* Actions Cell */
.actions-wrapper {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.dark .btn-action {
  background: #1e293b;
  border-color: #334155;
  color: #94a3b8;
}

.btn-action:hover:not(:disabled) {
  background: #e2e8f0;
  color: #0f172a;
}

.dark .btn-action:hover:not(:disabled) {
  background: #334155;
  color: #ffffff;
}

.btn-action.approve {
  width: auto;
  padding: 0 10px;
  background: #16a34a;
  border-color: #16a34a;
  color: white;
  font-weight: 600;
  font-size: 12px;
  gap: 4px;
}

.btn-action.approve:hover:not(:disabled) {
  background: #15803d;
}

.btn-action.suspend:hover:not(:disabled) {
  color: #dc2626;
  border-color: #fca5a5;
  background: #fef2f2;
}

.btn-action.reactivate:hover:not(:disabled) {
  color: #16a34a;
  border-color: #86efac;
  background: #ecfdf5;
}

.btn-action.delete:hover:not(:disabled) {
  color: #dc2626;
  border-color: #fca5a5;
  background: #fef2f2;
}

.action-icon {
  width: 14px;
  height: 14px;
}

/* Table Empty & Loading States */
.table-empty {
  padding: 60px 20px !important;
  text-align: center;
  color: #64748b;
}

.dark .table-empty {
  color: #94a3b8;
}

.empty-icon {
  width: 44px;
  height: 44px;
  margin: 0 auto 12px;
  opacity: 0.4;
}

.spinner-inline {
  width: 36px;
  height: 36px;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 12px;
}

/* Pagination Bar */
.pagination-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-top: 1px solid #e2e8f0;
  font-size: 13px;
  color: #64748b;
}

.dark .pagination-bar {
  border-color: #1e293b;
  color: #94a3b8;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-page {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: #ffffff;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s;
}

.dark .btn-page {
  background: #1e293b;
  border-color: #334155;
  color: #e2e8f0;
}

.btn-page:hover:not(:disabled) {
  background: #f1f5f9;
}

.btn-page:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-icon {
  width: 14px;
  height: 14px;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
}

.modal-dialog {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid #e2e8f0;
}

.dark .modal-dialog {
  background: #131b2e;
  border-color: #1e293b;
  color: #f1f5f9;
}

.modal-dialog.modal-lg {
  max-width: 750px;
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.dark .modal-header {
  border-color: #1e293b;
}

.modal-title-group {
  display: flex;
  align-items: center;
  gap: 14px;
}

.modal-icon-badge {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-icon-badge.staff {
  background: #eff6ff;
  color: #2563eb;
}

.dark .modal-icon-badge.staff {
  background: rgba(37, 99, 235, 0.2);
  color: #60a5fa;
}

.modal-icon-badge .icon {
  width: 22px;
  height: 22px;
}

.modal-title {
  font-size: 18px;
  font-weight: 800;
  margin: 0 0 2px 0;
  color: #0f172a;
}

.dark .modal-title {
  color: #ffffff;
}

.modal-subtitle {
  font-size: 13px;
  color: #64748b;
  margin: 0;
}

.dark .modal-subtitle {
  color: #94a3b8;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
}

.modal-close-btn:hover {
  color: #0f172a;
}

.dark .modal-close-btn:hover {
  color: #ffffff;
}

.modal-body {
  padding: 24px;
}

.modal-notice {
  display: flex;
  gap: 12px;
  padding: 14px 16px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  font-size: 13px;
  color: #1e40af;
  margin-bottom: 20px;
  line-height: 1.5;
}

.dark .modal-notice {
  background: rgba(37, 99, 235, 0.15);
  border-color: rgba(37, 99, 235, 0.3);
  color: #93c5fd;
}

.notice-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

/* Form Styles */
.form-group {
  margin-bottom: 18px;
}

.form-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
}

.dark .form-label {
  color: #cbd5e1;
}

.form-label.required::after {
  content: ' *';
  color: #dc2626;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 14px;
  background: #ffffff;
  color: #0f172a;
  outline: none;
  transition: all 0.2s;
}

.dark .form-input {
  background: #0f172a;
  border-color: #334155;
  color: #f1f5f9;
}

.form-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.input-hint {
  display: block;
  font-size: 11px;
  color: #94a3b8;
  margin-top: 4px;
}

.label-with-action {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.btn-text-action {
  background: none;
  border: none;
  color: #2563eb;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.btn-text-action:hover {
  text-decoration: underline;
}

.mini-icon {
  width: 12px;
  height: 12px;
}

.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
}

.password-toggle .icon {
  width: 16px;
  height: 16px;
}

/* Role Choice Grid */
.role-selection-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 10px;
}

.role-choice-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
  background: #ffffff;
}

.dark .role-choice-card {
  background: #0f172a;
  border-color: #1e293b;
}

.role-choice-card:hover {
  border-color: #cbd5e1;
}

.role-choice-card.selected {
  border-color: #2563eb;
  background: #eff6ff;
}

.dark .role-choice-card.selected {
  background: rgba(37, 99, 235, 0.15);
  border-color: #3b82f6;
}

.choice-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.choice-icon.expert { background: #ecfdf5; color: #10b981; }
.choice-icon.financial { background: #eff6ff; color: #2563eb; }
.choice-icon.admin { background: #fff1f2; color: #e11d48; }

.dark .choice-icon.expert { background: rgba(16, 185, 129, 0.2); }
.dark .choice-icon.financial { background: rgba(37, 99, 235, 0.2); }
.dark .choice-icon.admin { background: rgba(225, 29, 72, 0.2); }

.choice-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}

.dark .choice-title {
  color: #f1f5f9;
}

.choice-desc {
  font-size: 12px;
  color: #64748b;
  line-height: 1.4;
}

.dark .choice-desc {
  color: #94a3b8;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
  margin-top: 24px;
}

.dark .modal-footer {
  border-color: #1e293b;
}

/* Dossier Modal Styles */
.dossier-status-banner {
  display: flex;
  gap: 14px;
  padding: 16px;
  border-radius: 10px;
  margin-bottom: 22px;
}

.dossier-status-banner.pending {
  background: #fffbeb;
  border: 1px solid #fde68a;
  color: #92400e;
}

.dark .dossier-status-banner.pending {
  background: rgba(245, 158, 11, 0.15);
  border-color: rgba(245, 158, 11, 0.3);
  color: #fde68a;
}

.dossier-status-banner.active {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
}

.dark .dossier-status-banner.active {
  background: rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.3);
  color: #a7f3d0;
}

.dossier-status-banner.suspended {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.dark .dossier-status-banner.suspended {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
  color: #fecaca;
}

.banner-icon-col .b-icon {
  width: 24px;
  height: 24px;
}

.banner-text-col h4 {
  font-size: 15px;
  font-weight: 700;
  margin: 0 0 4px 0;
}

.banner-text-col p {
  font-size: 13px;
  margin: 0;
  line-height: 1.4;
}

.dossier-section {
  margin-bottom: 24px;
}

.section-heading {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 14px 0;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.dark .section-heading {
  color: #f1f5f9;
}

.heading-icon {
  width: 16px;
  height: 16px;
  color: #2563eb;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
}

.info-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
}

.dark .info-card {
  background: #0f172a;
  border-color: #1e293b;
}

.info-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  color: #64748b;
  margin-bottom: 4px;
}

.dark .info-label {
  color: #94a3b8;
}

.info-value {
  font-size: 14px;
  color: #0f172a;
}

.dark .info-value {
  color: #f1f5f9;
}

.info-value.strong {
  font-weight: 700;
}

.info-value.mono {
  font-family: monospace;
  font-weight: 600;
}

.docs-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.doc-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f8fafc;
}

.dark .doc-card {
  background: #0f172a;
  border-color: #1e293b;
}

.doc-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.doc-file-icon {
  width: 22px;
  height: 22px;
  color: #2563eb;
}

.doc-title {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
}

.dark .doc-title {
  color: #f1f5f9;
}

.doc-meta {
  font-size: 11px;
  color: #64748b;
}

.doc-status {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 9999px;
  text-transform: capitalize;
}

.doc-status.verified {
  background: #ecfdf5;
  color: #059669;
}

.doc-status.pending {
  background: #fffbeb;
  color: #d97706;
}

.empty-docs-box {
  text-align: center;
  padding: 24px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 8px;
  color: #64748b;
  font-size: 13px;
}

.dark .empty-docs-box {
  background: #0f172a;
  border-color: #334155;
  color: #94a3b8;
}

.mt-3 {
  margin-top: 12px;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 1024px) {
  .admin-main {
    margin-left: 0;
    width: 100%;
    padding: 20px;
  }
  .form-grid-2 {
    grid-template-columns: 1fr;
  }
  .architecture-strip {
    flex-direction: column;
    align-items: flex-start;
  }
  .arch-divider {
    display: none;
  }
}
</style>
