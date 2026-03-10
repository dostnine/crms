<script setup>
import { onMounted } from 'vue';
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
                        <div class="summary-hero-content">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-hero-avatar">
                                    <img 
                                        v-if="$page.props.jetstream.managesProfilePhotos && $page.props.auth.user.profile_photo_url" 
                                        :src="$page.props.auth.user.profile_photo_url" 
                                        :alt="$page.props.auth.user.name" 
                                        class="rounded-circle"
                                        style="width: 64px; height: 64px; object-fit: cover;"
                                    >
                                    <div v-else class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                        <i class="ri-user-line text-primary fs-3"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="summary-kicker mb-1">User Profile</p>
                                    <h3 class="summary-title mb-1">{{ $page.props.auth.user.name }}</h3>
                                    <p class="summary-text mb-0">
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                </div>
                            </div>
                            <div class="summary-stats">
                                <div class="stat-pill">
                                    <span class="stat-label">Account</span>
                                    <span class="stat-value">Active</span>
                                </div>
                                <div class="stat-pill">
                                    <span class="stat-label">Role</span>
                                    <span class="stat-value">User</span>
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
    border-radius: 16px;
    border: 1px solid #d9e7f7;
    background:
        radial-gradient(circle at top right, rgba(71, 153, 233, 0.3) 0, rgba(71, 153, 233, 0) 45%),
        linear-gradient(135deg, #f6fbff 0%, #e8f2ff 100%);
    overflow: hidden;
}

.summary-hero-content {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.summary-kicker {
    font-size: 0.76rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #3f6c9e;
    font-weight: 700;
}

.summary-title {
    color: var(--text-strong);
    font-size: 1.35rem;
    font-weight: 800;
}

.summary-text {
    color: #38506b;
    font-size: 0.92rem;
}

.summary-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
}

.stat-pill {
    background: #ffffff;
    border: 1px solid #d3e4f8;
    border-radius: 12px;
    min-width: 100px;
    padding: 8px 14px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 8px rgba(27, 72, 122, 0.08);
}

.stat-label {
    color: #5f7893;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.stat-value {
    color: #0d2f54;
    font-size: 0.9rem;
    font-weight: 800;
    line-height: 1.2;
}

/* Profile Card */
.profile-card {
    border-radius: 14px;
    overflow: hidden;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.profile-card-header {
    background: linear-gradient(90deg, var(--brand-navy), var(--brand-blue));
    border-bottom: none;
    padding: 14px 20px;
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

