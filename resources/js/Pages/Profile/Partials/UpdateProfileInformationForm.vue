<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

// Show the uploaded photo when there is one (and it loads); otherwise fall back
// to a gradient initials avatar rather than Jetstream's broken default image.
const photoFailed = ref(false);
const hasPhoto = computed(() => !!props.user.profile_photo_path && !photoFailed.value);
const userInitials = computed(() => {
    const source = (props.user.name || props.user.email || '?').trim();
    // First letter of every word: "Ren B. Tum" -> "RBT" (capped at 3).
    const letters = source.split(/\s+/).filter(Boolean).map((w) => w[0]).join('').toUpperCase().slice(0, 3);
    return letters || '?';
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    style="display: none;"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Photo" />

                <div class="photo-field mt-2">
                    <!-- Avatar: new selection preview, uploaded photo, or initials -->
                    <div class="avatar-frame">
                        <img v-if="photoPreview" :src="photoPreview" alt="New photo preview" class="avatar-media">
                        <img v-else-if="hasPhoto" :src="user.profile_photo_url" :alt="user.name" class="avatar-media" @error="photoFailed = true">
                        <span v-else class="avatar-letters">{{ userInitials }}</span>
                    </div>

                    <div class="photo-actions">
                        <div class="photo-buttons">
                            <SecondaryButton type="button" @click.prevent="selectNewPhoto">
                                <i class="ri-upload-2-line me-1"></i>Select A New Photo
                            </SecondaryButton>

                            <SecondaryButton
                                v-if="user.profile_photo_path"
                                type="button"
                                @click.prevent="deletePhoto"
                            >
                                <i class="ri-delete-bin-line me-1"></i>Remove Photo
                            </SecondaryButton>
                        </div>
                        <p class="photo-hint">JPG, PNG or GIF — a square image works best.</p>
                    </div>
                </div>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2">
                        Your email address is unverified.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.prevent="sendEmailVerification"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>
    </FormSection>
</template>

<style scoped>
.photo-field {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}
.avatar-frame {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.28), 0 6px 16px rgba(13, 47, 84, 0.15);
}
.avatar-media {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.avatar-letters {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #38bdf8, #6366f1);
    color: #fff;
    font-weight: 800;
    font-size: 1.9rem;
    letter-spacing: 1px;
}
.photo-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.photo-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.photo-hint {
    margin: 0;
    font-size: 0.8rem;
    color: #64789a;
}
</style>
