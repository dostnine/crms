<script setup>
    import { reactive, watch, ref } from 'vue'
    import { router } from '@inertiajs/vue3'
    import Swal from 'sweetalert2'

    const emit = defineEmits(['reloadPosts', 'input'])

    const props = defineProps({
        post: {
            type: Object,
            default: null,
        },
        value: {
            type: Boolean,
            default: false,
        },
        action: {
            type: String,
        },
        // the sheets a post can go on, and the one the list is showing
        sheets: {
            type: Array,
            default: () => [],
        },
        sheet: {
            type: String,
            default: '',
        },
    })

    const show_form_modal = ref(false)

    // The sheet whose posts may have a picture.
    const PICTURE_SHEET = 'others'

    const form = reactive({
        id: null,
        sheet: '',
        title: '',
        date_text: '',
        details: '',
        image: null,
        remove_image: false,
    })

    // the picture as the form shows it: the one chosen, or the one the post has
    const picture_shown = ref('')
    const picture_input = ref(null)

    const resetForm = () => {
        form.id = null
        // a new post goes on the sheet being looked at, the Bulletin if all are
        form.sheet = props.sheet || 'bulletin'
        form.title = ''
        form.date_text = ''
        form.details = ''
        form.image = null
        form.remove_image = false
        picture_shown.value = ''
    }

    watch(
        () => props.value,
        (value) => {
            show_form_modal.value = value
            if (!value) {
                return
            }
            resetForm()
            if (props.action === 'Update' && props.post) {
                form.id = props.post.id
                form.sheet = props.post.sheet
                form.title = props.post.title
                form.date_text = props.post.date_text || ''
                form.details = props.post.details || ''
                picture_shown.value = props.post.image_url || ''
            }
        }
    )

    // A picture as it is sent: no more than 2200 pixels a side, as a JPEG. A
    // poster straight from a camera or a design tool is several megabytes,
    // more than many servers take in one upload, and more than a screen can
    // show. One that is small already is sent as it is.
    const PICTURE_SIDE = 2200
    const PICTURE_SMALL = 1.5 * 1024 * 1024
    const sized = async (file) => {
        const bitmap = await createImageBitmap(file)
        const scale = Math.min(1, PICTURE_SIDE / Math.max(bitmap.width, bitmap.height))
        if (scale === 1 && file.size <= PICTURE_SMALL && /^image\/(jpeg|png|webp|gif)$/.test(file.type)) {
            return file
        }
        const canvas = document.createElement('canvas')
        canvas.width = Math.round(bitmap.width * scale)
        canvas.height = Math.round(bitmap.height * scale)
        const context = canvas.getContext('2d')
        context.fillStyle = '#ffffff'
        context.fillRect(0, 0, canvas.width, canvas.height)
        context.drawImage(bitmap, 0, 0, canvas.width, canvas.height)
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.88))
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' })
    }

    const pickPicture = async (event) => {
        const file = event.target.files[0]
        if (!file) {
            return
        }
        try {
            form.image = await sized(file)
            form.remove_image = false
            picture_shown.value = URL.createObjectURL(form.image)
        } catch (error) {
            event.target.value = ''
            Swal.fire({
                title: 'Not a picture',
                icon: 'error',
                text: 'That file could not be read as a picture. Please choose a JPG or PNG image.',
            })
        }
    }

    const removePicture = () => {
        form.image = null
        form.remove_image = true
        picture_shown.value = ''
        if (picture_input.value) {
            picture_input.value.value = ''
        }
    }

    const validateForm = () => {
        if (!form.title.trim()) {
            Swal.fire({
                title: 'Validation Error',
                icon: 'error',
                text: 'Please enter a title',
            })
            return false
        }
        return true
    }

    const savePost = async () => {
        if (!validateForm()) {
            return
        }

        const url = props.action === 'Add' ? '/charter-posts/add' : '/charter-posts/update'

        router.post(url, form, {
            // (the picture goes with it as a file)
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    title: 'Success',
                    icon: 'success',
                    text: props.action === 'Add' ? 'The post has been added.' : 'The post has been updated.',
                })
                closeDialog()
            },
            onError: (errors) => {
                Swal.fire({
                    title: 'Failed',
                    icon: 'error',
                    text: Object.values(errors || {})[0] || 'Something went wrong, please try again',
                })
            }
        })
    }

    const closeDialog = () => {
        emit('input', false)
        emit('reloadPosts')
    }
</script>

<template>
    <div v-if="show_form_modal" class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ri-megaphone-line me-2"></i>
                        {{ props.action }} Post
                    </h5>
                    <button type="button" class="btn-close btn-close-white" @click="closeDialog"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="savePost">
                        <div class="row">
                            <div class="col-12 col-md-5 mb-3">
                                <label class="form-label fw-semibold" for="post-sheet">Sheet <span class="text-danger">*</span></label>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text"><i class="ri-layout-top-line"></i></span>
                                    <select id="post-sheet" class="form-select custom-input" v-model="form.sheet" required>
                                        <option v-for="sheet in props.sheets" :key="sheet.value" :value="sheet.value">{{ sheet.label }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-7 mb-3">
                                <label class="form-label fw-semibold" for="post-date">Date</label>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input
                                        id="post-date"
                                        type="text"
                                        class="form-control custom-input"
                                        v-model="form.date_text"
                                        placeholder="e.g. 28 November 2026 (optional)"
                                        maxlength="80"
                                    />
                                </div>
                                <div class="form-text">Shown as typed, above the title.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold" for="post-title">Title <span class="text-danger">*</span></label>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text"><i class="ri-text"></i></span>
                                    <input
                                        id="post-title"
                                        type="text"
                                        class="form-control custom-input"
                                        v-model="form.title"
                                        placeholder="Enter the title"
                                        maxlength="150"
                                        required
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 mb-1">
                                <label class="form-label fw-semibold" for="post-details">Details</label>
                                <textarea
                                    id="post-details"
                                    class="form-control custom-input custom-textarea"
                                    v-model="form.details"
                                    rows="4"
                                    placeholder="A line or two under the title (optional)"
                                    maxlength="600"
                                ></textarea>
                                <div class="form-text text-end">{{ form.details.length }} / 600</div>
                            </div>
                        </div>

                        <div v-if="form.sheet === PICTURE_SHEET" class="row">
                            <div class="col-12 mb-1">
                                <label class="form-label fw-semibold" for="post-image">Picture</label>
                                <input
                                    id="post-image"
                                    ref="picture_input"
                                    type="file"
                                    class="form-control custom-input custom-file"
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    @change="pickPicture"
                                />
                                <div class="form-text">A poster or other picture to show on the Others sheet (optional). A large one is made smaller before it is sent.</div>
                                <div v-if="picture_shown" class="picture-preview">
                                    <img :src="picture_shown" alt="The picture of this post" />
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removePicture">
                                        <i class="ri-delete-bin-line me-1"></i>
                                        Remove picture
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="picture_shown" class="form-text text-danger">
                            Only a post on the Others sheet has a picture: on this sheet, this post's picture is taken off when it is saved.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-custom-secondary" @click="closeDialog">
                        <i class="ri-close-line me-1"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary btn-custom-primary" @click="savePost">
                        <i class="ri-save-line me-1"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Modal Custom Styling */
.modal-custom {
    border-radius: 20px;
    overflow: hidden;
}

.modal-content {
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    overflow: hidden;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: none;
    background: linear-gradient(90deg, #1b365d, #2a568f);
    color: white;
}

.modal-body {
    padding: 30px;
}

.modal-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

/* Custom Input Group */
.custom-input-group {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.custom-input-group:focus-within {
    box-shadow: 0 4px 20px rgba(31, 109, 179, 0.25);
}

.input-group-text {
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    color: white;
    border: none;
    padding: 12px 15px;
}

.custom-input {
    border: 1px solid #e9ecef;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.custom-input:focus {
    border-color: #1f6db3;
    box-shadow: 0 0 0 3px rgba(31, 109, 179, 0.1);
}

.custom-textarea {
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    resize: vertical;
}

.custom-file {
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.picture-preview {
    display: flex;
    align-items: flex-end;
    gap: 14px;
    margin-top: 12px;
}

.picture-preview img {
    max-width: 220px;
    max-height: 180px;
    border: 1px solid #d8e5f5;
    border-radius: 12px;
    background: #fff;
}

/* Form Label */
.form-label {
    color: #333;
    margin-bottom: 8px;
}

/* Button Styling */
.btn-custom-secondary {
    border-radius: 15px;
    padding: 10px 20px;
    transition: all 0.3s ease;
    background: #6c757d;
    border: none;
}

.btn-custom-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-custom-primary {
    border-radius: 15px;
    padding: 10px 20px;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border: none;
}

.btn-custom-primary:hover {
    background: linear-gradient(135deg, #1a5f9d, #164476);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(31, 109, 179, 0.4);
}

/* Animation */
.modal {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-dialog {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
