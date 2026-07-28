<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NavLink from '@/Components/NavLink.vue';
import Icon from '@/Shared/Icon.vue';


defineProps({
    title: String,
    auth: Object,
});

const sidebarHovered = ref(false);

// Profile avatar: fall back to initials when there is no photo, or when the
// photo URL (Jetstream's default is an external service) fails to load.
const page = usePage();
const photoFailed = ref(false);
const userInitials = computed(() => {
    const u = page.props.auth?.user;
    const source = (u?.name || u?.email || '?').trim();
    // First letter of every word: "Ren B. Tum" -> "RBT" (capped at 3).
    const letters = source.split(/\s+/).filter(Boolean).map((w) => w[0]).join('').toUpperCase().slice(0, 3);
    return letters || '?';
});
const showPhoto = computed(() =>
    page.props.jetstream?.managesProfilePhotos &&
    page.props.auth?.user?.profile_photo_url &&
    !photoFailed.value
);

// Below this width the fixed hover-rail becomes an off-canvas drawer.
const isMobile = ref(false);
const mobileOpen = ref(false);
let mq;
const applyMq = (e) => {
    isMobile.value = e.matches;
    if (!e.matches) mobileOpen.value = false; // leaving mobile closes the drawer
};

onMounted(() => {
    mq = window.matchMedia('(max-width: 991px)');
    isMobile.value = mq.matches;
    mq.addEventListener('change', applyMq);
});
onBeforeUnmount(() => mq && mq.removeEventListener('change', applyMq));

// On mobile the drawer is always full-width when open; on desktop it expands on hover.
const expanded = computed(() => (isMobile.value ? true : sidebarHovered.value));

const sidebarStyle = computed(() => ({
    position: 'fixed',
    top: '0',
    left: '0',
    height: '100vh',
    zIndex: isMobile.value ? 1050 : 1,
    transform: isMobile.value && !mobileOpen.value ? 'translateX(-100%)' : 'translateX(0)',
    transition: 'transform 0.3s ease, width 0.3s ease',
}));

const topbarStyle = computed(() => ({
    top: '0',
    left: isMobile.value ? '0' : expanded.value ? '250px' : '70px',
    width: isMobile.value ? '100%' : expanded.value ? 'calc(100% - 250px)' : 'calc(100% - 70px)',
    height: '60px',
    zIndex: 100,
    transition: 'left 0.3s ease, width 0.3s ease',
}));

const contentStyle = computed(() => ({
    marginLeft: isMobile.value ? '0' : sidebarHovered.value ? '250px' : '70px',
    marginTop: '60px',
    transition: 'margin-left 0.3s ease',
    minHeight: 'calc(100vh - 60px)',
    position: 'relative',
}));

const onSidebarHover = (isHovering) => {
    if (!isMobile.value) sidebarHovered.value = isHovering;
};

const closeOnMobile = () => {
    if (isMobile.value) mobileOpen.value = false;
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="d-flex" style="min-height: 100vh;">
            <!-- Mobile drawer backdrop -->
            <div v-if="isMobile && mobileOpen" class="sidebar-backdrop" @click="mobileOpen = false"></div>

            <!-- Sidebar: hover-rail on desktop, off-canvas drawer on mobile -->
            <nav
                id="sidebar"
                :class="{'sidebar-expanded': expanded}"
                @mouseenter="onSidebarHover(true)"
                @mouseleave="onSidebarHover(false)"
                :style="sidebarStyle"
            >
                <div class="sidebar-header position-relative">
                    <Link href="/dashboard" class="navbar-brand" @click="closeOnMobile">
                        <ApplicationMark class="application-mark" />
                        <span v-if="expanded" class="brand-text">CRMS</span>
                    </Link>
                </div>
                <ul class="components">
                    <li>
                        <NavLink href="/dashboard" active="/dashboard" :class="{'active': $page.url === '/dashboard'}" data-title="Dashboard" @click="closeOnMobile">
                            <Icon name="dashboard" class="icon" />
                            <span v-if="expanded" class="nav-text">Dashboard</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink href="/service_units" active="/service_units" :class="{'active': $page.url.startsWith('/service_units')}" data-title="Service Units" @click="closeOnMobile">
                            <Icon name="office" class="icon" />
                            <span v-if="expanded" class="nav-text">Service Units</span>
                        </NavLink>
                    </li>
                    <li>
                        <NavLink href="/libraries" active="/libraries" :class="{'active': $page.url.startsWith('/libraries')}" data-title="Libraries" @click="closeOnMobile">
                            <Icon name="users" class="icon" />
                            <span v-if="expanded" class="nav-text">Libraries</span>
                        </NavLink>
                    </li>
                </ul>
            </nav>

            <!-- Top navigation bar -->
            <nav
                class="navbar navbar-light bg-white border-bottom shadow-sm position-fixed"
                :style="topbarStyle"
            >
                <div class="container-fluid d-flex align-items-center">
                    <!-- Hamburger (mobile only) -->
                    <button
                        v-if="isMobile"
                        class="btn hamburger-btn"
                        type="button"
                        aria-label="Toggle navigation"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <Icon name="menu" class="hamburger-icon" />
                    </button>

                    <!-- Spacer to push profile to the right -->
                    <div class="flex-grow-1"></div>

                    <!-- Profile Dropdown (Right Side) -->
                    <div class="dropdown profile-dropdown" style="z-index: 1000;">
                        <button class="btn profile-dropdown-btn d-flex align-items-center" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="z-index: 1001;">
                            <div class="profile-avatar me-2">
                                <img v-if="showPhoto" class="avatar-img" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" @error="photoFailed = true">
                                <span v-else class="avatar-initials">{{ userInitials }}</span>
                            </div>
                            <span class="profile-name fw-semibold d-none d-sm-inline">{{ $page.props.auth.user.name || 'Account' }}</span>
                            <Icon name="chevron-down" class="ms-2 dropdown-arrow" />
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end profile-dropdown-menu shadow" aria-labelledby="profileDropdown" style="z-index: 1002;">
                            <li>
                                <div class="dropdown-identity">
                                    <div class="profile-avatar profile-avatar-lg">
                                        <img v-if="showPhoto" class="avatar-img" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" @error="photoFailed = true">
                                        <span v-else class="avatar-initials">{{ userInitials }}</span>
                                    </div>
                                    <div class="identity-text">
                                        <span class="identity-name">{{ $page.props.auth.user.name || 'Account' }}</span>
                                        <span class="identity-email">{{ $page.props.auth.user.email }}</span>
                                    </div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><Link href="/profile" class="dropdown-item profile-dropdown-item"><Icon name="user" class="me-2" />My Profile</Link></li>
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

            <!-- Main Content Area -->
            <div class="flex-grow-1 bg-light" :style="contentStyle">

                <!-- Page Heading -->
                <header v-if="$slots.header" class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-3 p-md-4">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Mobile drawer backdrop */
.sidebar-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1040;
    background: rgba(5, 9, 24, 0.5);
    backdrop-filter: blur(2px);
    animation: fade-in 0.2s ease;
}
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

/* Hamburger */
.hamburger-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    padding: 0;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #10214a;
    transition: background 0.2s ease, border-color 0.2s ease;
}
.hamburger-btn:hover { background: #f1f6fc; border-color: #cbd5e1; }
.hamburger-icon { font-size: 1.35rem; line-height: 1; }

/* Profile Dropdown Styles */
.profile-dropdown-btn {
    background: #fff;
    border: 1px solid #dbe4f0;
    border-radius: 999px;
    padding: 5px 14px 5px 5px;
    color: #10214a;
    text-decoration: none;
    transition: all 0.2s ease;
}

.profile-dropdown-btn:hover {
    border-color: #38bdf8;
    color: #0f172a;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(56, 189, 248, 0.18);
}

.profile-dropdown-btn:focus {
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.22);
}

.profile-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    overflow: hidden;
}
.avatar-img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.avatar-initials {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #38bdf8, #6366f1);
    color: #fff;
    font-weight: 800;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
}
.profile-avatar-lg { width: 44px; height: 44px; }
.profile-avatar-lg .avatar-initials { font-size: 1.05rem; }

.profile-name {
    color: #10214a;
    font-size: 0.95rem;
}

/* Identity block in the menu */
.dropdown-identity {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px 10px;
}
.identity-text { display: flex; flex-direction: column; min-width: 0; }
.identity-name { font-weight: 700; color: #10214a; font-size: 0.92rem; line-height: 1.2; }
.identity-email {
    color: #64789a;
    font-size: 0.78rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 190px;
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



