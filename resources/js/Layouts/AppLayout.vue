<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NavLink from '@/Components/NavLink.vue';
import Icon from '@/Shared/Icon.vue';


defineProps({
    title: String,
    auth: Object,
});

const showingNavigationDropdown = ref(false);
const sidebarHovered = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post('/logout');
};

const onSidebarHover = (isHovering) => {
    sidebarHovered.value = isHovering;
};

</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="d-flex" style="min-height: 100vh;">
            <!-- Sidebar (Full Height from Top) - Expands on hover -->
            <nav
                id="sidebar"
                :class="{'sidebar-expanded': sidebarHovered}"
                @mouseenter="onSidebarHover(true)"
                @mouseleave="onSidebarHover(false)"
                :style="{
                    position: 'fixed',
                    top: '0',
                    left: '0',
                    height: '100vh',
                    width: sidebarHovered ? '250px' : '70px',
                    zIndex: 1,
                    transition: 'width 0.3s ease'
                }"
            >

            <!-- Top Navigation Bar (Remaining Width After Sidebar) -->
            <nav
                class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm position-fixed"
                :style="{
                    top: '0',
                    left: sidebarHovered ? '250px' : '70px',
                    width: sidebarHovered ? 'calc(100% - 250px)' : 'calc(100% - 70px)',
                    height: '60px',
                    zIndex: 100,
                    transition: 'left 0.3s ease, width 0.3s ease'
                }"
            >
                <div class="container-fluid d-flex align-items-center">
                  
                 

                    <!-- Spacer to push profile to the right -->
                    <div class="flex-grow-1"></div>

                    <!-- Profile Dropdown (Right Side) -->
                    <div class="dropdown profile-dropdown" style="z-index: 1000;">
                        <button class="btn profile-dropdown-btn d-flex align-items-center" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="z-index: 1001;">
                            <div class="profile-avatar me-2">
                                <img v-if="$page.props.jetstream.managesProfilePhotos" class="rounded-circle" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" style="width: 36px; height: 36px; object-fit: cover;">
                                <div v-else class="bg-primary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <Icon name="user" class="text-primary fs-6" />
                                </div>
                            </div>
                            <span class="profile-name fw-medium">{{ $page.props.auth.user.name }}</span>
                            <Icon name="chevron-down" class="ms-2 dropdown-arrow" />
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu shadow" aria-labelledby="profileDropdown" style="z-index: 1002;">
                            <li><h6 class="dropdown-header profile-dropdown-header">Manage Account</h6></li>
                            <li><Link href="/profile" class="dropdown-item profile-dropdown-item"><Icon name="user" class="me-2" />Profile</Link></li>
                            <li v-if="$page.props.jetstream.hasApiFeatures"><Link href="/api-tokens.index" class="dropdown-item profile-dropdown-item"><Icon name="key" class="me-2" />API Tokens</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form @submit.prevent="logout">
                                    <button type="submit" class="dropdown-item profile-dropdown-item text-danger"><Icon name="logout" class="me-2" />Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
                <div class="sidebar-header position-relative">
                    <Link href="/dashboard" class="navbar-brand">
                        <ApplicationMark class="application-mark" />
                        <span v-if="sidebarHovered" class="brand-text">CRMS</span>
                    </Link>
                </div>
                <ul class="components">
                    <li>
                        <NavLink href="/dashboard" active="/dashboard" :class="{'active': $page.url === '/dashboard'}" data-title="Dashboard">
                            <Icon name="dashboard" class="icon" />
                            <span v-if="sidebarHovered" class="nav-text">Dashboard</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink href="/service_units" active="/service_units" :class="{'active': $page.url.startsWith('/service_units')}" data-title="Service Units">
                            <Icon name="office" class="icon" />
                            <span v-if="sidebarHovered" class="nav-text">Service Units</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink href="/libraries" active="/libraries" :class="{'active': $page.url.startsWith('/libraries')}" data-title="Libraries">
                            <Icon name="users" class="icon" />
                            <span v-if="sidebarHovered" class="nav-text">Libraries</span>
                        </NavLink>
                    </li>
                </ul>
              
            </nav>

            <!-- Main Content Area -->
            <div class="flex-grow-1 bg-light" :style="{ marginLeft: sidebarHovered ? '250px' : '70px', marginTop: '60px', transition: 'margin-left 0.3s ease', minHeight: 'calc(100vh - 60px)', position: 'relative' }">

                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Profile Dropdown Styles */
.profile-dropdown-btn {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 6px 14px;
    color: #1e293b;
    text-decoration: none;
    transition: all 0.2s ease;
}

.profile-dropdown-btn:hover {
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    border-color: #94a3b8;
    color: #0f172a;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-dropdown-btn:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.profile-avatar {
    display: flex;
    align-items: center;
}

.profile-name {
    color: #1e293b;
    font-size: 0.95rem;
}

.dropdown-arrow {
    color: #64748b;
    font-size: 0.85rem;
    transition: transform 0.2s ease;
}

.profile-dropdown.show .dropdown-arrow {
    transform: rotate(180deg);
}

.profile-dropdown-menu {
    border: none;
    border-radius: 12px;
    padding: 8px;
    min-width: 200px;
    background: #ffffff;
    margin-top: 8px;
    display: none;
}

.profile-dropdown-menu.show {
    display: block;
}

.profile-dropdown-menu::before {
    content: '';
    position: absolute;
    top: -8px;
    right: 20px;
    width: 16px;
    height: 16px;
    background: #ffffff;
    transform: rotate(45deg);
    border-left: 1px solid #e2e8f0;
    border-top: 1px solid #e2e8f0;
}

.profile-dropdown-header {
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 12px 4px;
}

.profile-dropdown-item {
    color: #334155;
    font-size: 0.9rem;
    padding: 10px 12px;
    border-radius: 8px;
    margin: 2px 0;
    transition: all 0.15s ease;
}

.profile-dropdown-item:hover {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1d4ed8;
}

.profile-dropdown-item:active {
    background: #dbeafe;
    color: #1e40af;
}

.profile-dropdown-item.text-danger:hover {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
}

.dropdown-divider {
    margin: 8px 0;
    border-color: #e2e8f0;
}
</style>



