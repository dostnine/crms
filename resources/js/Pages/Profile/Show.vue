<script setup>
import { onMounted, ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import AOS from 'aos';
import 'aos/dist/aos.css';

onMounted(() => {
    AOS.init();
});

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

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
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">My Profile</h2>
                <p class="page-heading-subtitle mb-0">Manage your account settings and preferences.</p>
            </div>
        </template>

        <div class="container-fluid py-4 profile-page">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <!-- Profile Summary Hero -->
                    <div class="summary-hero mb-4" data-aos="fade-up">
                        <div class="hero-glow"></div>
                        <div class="summary-hero-content">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-hero-avatar">
                                    <img
                                        v-if="showPhoto"
                                        :src="$page.props.auth.user.profile_photo_url"
                                        :alt="$page.props.auth.user.name"
                                        class="hero-avatar-img"
                                        @error="photoFailed = true"
                                    >
                                    <span v-else class="hero-avatar-initials">{{ userInitials }}</span>
                                </div>
                                <div>
                                    <p class="summary-kicker mb-1"><i class="ri-verified-badge-line me-1"></i>User Profile</p>
                                    <h3 class="summary-title mb-1">{{ $page.props.auth.user.name || 'Account' }}</h3>
                                    <p class="summary-text mb-0">
                                        <i class="ri-mail-line me-1"></i>{{ $page.props.auth.user.email }}
                                    </p>
                                </div>
                            </div>
                            <div class="summary-stats">
                                <div class="stat-pill">
                                    <span class="stat-label">Account</span>
                                    <span class="stat-value"><span class="status-dot"></span>Active</span>
                                </div>
                                <div class="stat-pill">
                                    <span class="stat-label">Active Sessions</span>
                                    <span class="stat-value">{{ sessions?.length ?? 1 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information Card -->
                    <div class="card profile-card mb-4" data-aos="fade-up">
                        <div class="card-header profile-card-header text-white">
                            <h4 class="card-title mb-0 d-flex align-items-center">
                                <i class="ri-user-settings-line me-2"></i>
                                Profile Information
                            </h4>
                        </div>
                        <div class="card-body profile-card-body">
                            <UpdateProfileInformationForm :user="$page.props.auth.user" />
                        </div>
                    </div>

                    <SectionBorder />

                    <!-- Update Password Card -->
                    <div class="card profile-card mb-4" data-aos="fade-up">
                        <div class="card-header profile-card-header text-white">
                            <h4 class="card-title mb-0 d-flex align-items-center">
                                <i class="ri-lock-password-line me-2"></i>
                                Update Password
                            </h4>
                        </div>
                        <div class="card-body profile-card-body">
                            <UpdatePasswordForm />
                        </div>
                    </div>

                    <SectionBorder />

                    <!-- Two Factor Authentication Card -->
                    <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" class="card profile-card mb-4" data-aos="fade-up">
                        <div class="card-header profile-card-header text-white">
                            <h4 class="card-title mb-0 d-flex align-items-center">
                                <i class="ri-shield-key-line me-2"></i>
                                Two-Factor Authentication
                            </h4>
                        </div>
                        <div class="card-body profile-card-body">
                            <TwoFactorAuthenticationForm
                                :requires-confirmation="confirmsTwoFactorAuthentication"
                            />
                        </div>
                    </div>

                    <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                        <SectionBorder />

                        <!-- Delete Account Card -->
                        <div class="card profile-card mb-4" data-aos="fade-up">
                            <div class="card-header profile-card-header-danger text-white">
                                <h4 class="card-title mb-0 d-flex align-items-center">
                                    <i class="ri-delete-bin-line me-2"></i>
                                    Delete Account
                                </h4>
                            </div>
                            <div class="card-body profile-card-body">
                                <DeleteUserForm />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.profile-page {
    --brand-navy: #153b70;
    --brand-blue: #2266a8;
    --brand-sky: #e9f3ff;
    --surface: #ffffff;
    --text-strong: #12243a;
    --text-soft: #5b7088;
}

/* Page Heading */
.page-heading-title {
    margin: 0;
    color: var(--text-strong);
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.page-heading-subtitle {
    margin-top: 2px;
    color: var(--text-soft);
    font-size: 0.9rem;
}

/* Summary Hero */
.summary-hero {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    background: linear-gradient(120deg, #10214a 0%, #123a6b 55%, #0d2f54 100%);
    box-shadow: 0 18px 40px rgba(13, 47, 84, 0.28);
}

.hero-glow {
    position: absolute;
    top: -60%;
    right: -8%;
    width: 420px;
    height: 420px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.35) 0%, rgba(56, 189, 248, 0) 65%);
    pointer-events: none;
}

.summary-hero-content {
    position: relative;
    padding: 24px 26px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

/* Hero avatar */
.profile-hero-avatar {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(125, 211, 252, 0.35);
}
.hero-avatar-img { width: 100%; height: 100%; object-fit: cover; }
.hero-avatar-initials {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #38bdf8, #6366f1);
    color: #fff;
    font-weight: 800;
    font-size: 1.6rem;
    letter-spacing: 0.5px;
}

.summary-kicker {
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #7dd3fc;
    font-weight: 700;
}

.summary-title {
    color: #fff;
    font-size: 1.45rem;
    font-weight: 800;
    letter-spacing: -0.3px;
}

.summary-text {
    color: #b7c6e6;
    font-size: 0.92rem;
}

.summary-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
}

.stat-pill {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(125, 211, 252, 0.22);
    border-radius: 14px;
    min-width: 118px;
    padding: 10px 16px;
    display: flex;
    flex-direction: column;
    gap: 3px;
    backdrop-filter: blur(6px);
}

.stat-label {
    color: #8fa6cf;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.stat-value {
    display: inline-flex;
    align-items: center;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 800;
    line-height: 1.2;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.25);
    margin-right: 7px;
}

/* Profile Card */
.profile-card {
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #e2ecfa;
    box-shadow: 0 10px 28px rgba(13, 47, 84, 0.08);
}

.profile-card-header {
    background: linear-gradient(90deg, #10214a, #123a6b);
    border-bottom: none;
    padding: 15px 20px;
}

.profile-card-header-danger {
    background: linear-gradient(90deg, #dc2626, #b91c1c);
    border-bottom: none;
    padding: 14px 20px;
}

.profile-card-body {
    padding: 20px;
    background: var(--surface);
}

/* Responsive */
@media (max-width: 992px) {
    .summary-hero-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .summary-stats {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

