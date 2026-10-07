<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import ModalForm from '@/Pages/Libraries/CharterPosts/Modal.vue';
    import { router } from '@inertiajs/vue3';
    import { ref, watch } from 'vue';
    import Swal from 'sweetalert2';

    const props = defineProps({
        posts: Object,
        sheet: String,
    });

    // The sheets of the Citizen's Charter book that take posts.
    const sheets = [
        { value: 'bulletin', label: 'Bulletin', icon: 'ri-megaphone-line' },
        { value: 'events', label: 'Events', icon: 'ri-calendar-event-line' },
        { value: 'others', label: 'Others', icon: 'ri-pushpin-line' },
    ];
    const sheetOf = (value) => sheets.find((sheet) => sheet.value === value) || { label: value, icon: 'ri-pushpin-line' };

    const show_modal = ref(false);
    const action_clicked = ref(null);
    const post = ref({});
    const search = ref('');
    const sheet_shown = ref(props.sheet || '');

    const loadPosts = (page) => {
        router.get('/charter-posts', { page, search: search.value, sheet: sheet_shown.value }, { preserveState: true });
    };

    watch([search, sheet_shown], () => loadPosts());

    const showPostModal = async (is_show, action, post_data) => {
        show_modal.value = is_show;
        action_clicked.value = action;
        post.value = post_data;
    };

    const deleteRecord = async (id) => {

        Swal.fire({
            html: '<div style="font-weight: bold; font-size:25px">Are you sure you want to delete this post?</div> ',
            icon:'warning',

            showCancelButton: true,
            confirmButtonText: "Yes, I'm sure",
            showLoaderOnConfirm: true,
        }).then((result) => {
            if (result.isConfirmed) {
                router.post('/charter-posts/delete', { id }, { preserveScroll: true });
            }
        });

    };

    const reloadPosts = async () => {
        post.value = {};
    };
</script>


<template>
    <AppLayout title="Charter Posts">
        <template #header>
            <div class="page-heading">
                <h2 class="page-heading-title">Charter Posts</h2>
                <p class="page-heading-subtitle mb-0">What the Citizen's Charter shows on its Bulletin, Events and Others sheets.</p>
            </div>
        </template>

        <div class="container-fluid py-4 libraries-page">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-11">
                    <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                        <div class="card-header text-white position-relative overflow-hidden d-flex flex-wrap gap-3 justify-content-between align-items-center" style="padding: 20px 25px;">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="ri-megaphone-line" style="font-size: 80px;"></i>
                            </div>
                            <div class="position-relative">
                                <h3 class="card-title mb-0">
                                    <i class="ri-megaphone-line me-2"></i>
                                    Charter Posts
                                </h3>
                                <p class="mb-0 mt-1 opacity-75" style="font-size: 0.9rem;">Post on the Bulletin, Events and Others sheets of the Citizen's Charter</p>
                            </div>
                            <div class="d-flex flex-wrap gap-3 align-items-center position-relative">
                                <select class="form-select form-select-sm sheet-filter" v-model="sheet_shown" aria-label="Sheet">
                                    <option value="">All sheets</option>
                                    <option v-for="sheet in sheets" :key="sheet.value" :value="sheet.value">{{ sheet.label }}</option>
                                </select>
                                <div class="search-box">
                                    <div class="input-group input-group-sm" style="border-radius: 25px; overflow: hidden;">
                                        <span class="input-group-text bg-white border-0"><i class="ri-search-line text-muted"></i></span>
                                        <input type="text" class="form-control border-0 shadow-none" placeholder="Search..." v-model="search" style="border-radius: 0 25px 25px 0;">
                                    </div>
                                </div>
                                <a href="/citizens-charter" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm fw-semibold" style="border-radius: 20px;">
                                    <i class="ri-book-open-line me-1"></i> View the Charter
                                </a>
                                <button @click="showPostModal(true, 'Add', null)" class="btn btn-light btn-sm fw-semibold" style="border-radius: 20px;">
                                    <i class="ri-add-line me-1"></i> Add Post
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="min-width: 720px;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-start" style="width: 60px; border-radius: 0;">#</th>
                                            <th class="text-start" style="width: 140px;">Sheet</th>
                                            <th class="text-start">Post</th>
                                            <th class="text-start" style="width: 200px;">Date</th>
                                            <th class="text-center" style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(item, index) in posts.data"
                                            :key="item.id"
                                            class="align-middle"
                                        >
                                            <td class="fw-bold text-muted">{{ posts.from + index }}</td>
                                            <td>
                                                <span class="badge sheet-badge" :class="'sheet-' + item.sheet">
                                                    <i class="me-1" :class="sheetOf(item.sheet).icon"></i>{{ sheetOf(item.sheet).label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img v-if="item.image_url" :src="item.image_url" class="post-picture" alt="The picture of this post" />
                                                    <div>
                                                        <div class="fw-semibold post-title">{{ item.title }}</div>
                                                        <div v-if="item.details" class="text-muted post-details">{{ item.details }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span v-if="item.date_text">{{ item.date_text }}</span>
                                                <span v-else class="text-muted">&mdash;</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button @click="showPostModal(true, 'Update', item)" class="btn btn-primary" style="border-radius: 15px 0 0 15px;">
                                                        <i class="ri-edit-line me-1"></i> Update
                                                    </button>
                                                    <button @click="deleteRecord(item.id)" class="btn btn-danger" style="border-radius: 0 15px 15px 0;">
                                                        <i class="ri-delete-bin-line me-1"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="!posts.data.length">
                                            <td colspan="5" class="text-center text-muted py-5">
                                                {{ search || sheet_shown ? 'No post matches.' : 'Nothing is posted yet. Add Post puts the first one on a sheet.' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer bg-light" style="border-radius: 0 0 20px 20px;">
                                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                    <span class="text-muted">
                                        Showing <span class="fw-semibold">{{ posts.from || 0 }}</span> to <span class="fw-semibold">{{ posts.to || 0 }}</span> out of
                                        <span class="fw-bold text-primary">{{ posts.total }}</span> records
                                    </span>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li v-for="page in posts.last_page" :key="page" class="page-item" :class="{ active: page === posts.current_page }">
                                                <a class="page-link" href="#" @click.prevent="loadPosts(page)">{{ page }}</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted mt-3 mb-0 px-2 page-note">
                        <i class="ri-information-line me-1"></i>
                        A sheet lists its newest posts first, as many as fit on it; on a phone it lists them all. A post on the Others sheet can have a picture: with pictures, that sheet shows its eight newest posts side by side. A sheet with nothing posted is left out of the Charter's idle display. The Charter picks up a change within five minutes, or at once when it is opened again.
                    </p>
                </div>
            </div>
        </div>

        <ModalForm
            :value="show_modal"
            :post="post"
            :action="action_clicked"
            :sheets="sheets"
            :sheet="sheet_shown"
            @input="showPostModal"
            @reloadPosts="reloadPosts"
        ></ModalForm>
    </AppLayout>
</template>

<style scoped>
.libraries-page {
    background: linear-gradient(135deg, #f5f9ff 0%, #edf3fb 100%);
    min-height: 100vh;
}

.page-heading-title {
    margin: 0;
    color: #12243a;
    font-size: 1.25rem;
    font-weight: 700;
}

.page-heading-subtitle {
    color: #5b7088;
    font-size: 0.9rem;
}

.card {
    border: 1px solid #d8e5f5 !important;
    box-shadow: 0 10px 26px rgba(21, 59, 112, 0.08) !important;
}

.card-header {
    background: linear-gradient(90deg, #1b365d, #2a568f) !important;
}

.search-box .input-group {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.sheet-filter {
    width: auto;
    border: 0;
    border-radius: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.table-dark th {
    background: #1f3b6e !important;
    border-color: #365988 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(33, 94, 154, 0.06) !important;
}

.sheet-badge {
    border-radius: 15px;
    padding: 6px 12px;
    font-weight: 600;
}

.sheet-bulletin { background: #eaf1ff; color: #2f66b3; }
.sheet-events { background: #e8f9f1; color: #177a4e; }
.sheet-others { background: #fff3e8; color: #b05a1a; }

.post-title { color: #12243a; }

.post-picture {
    flex-shrink: 0;
    width: 56px;
    height: 56px;
    border: 1px solid #d8e5f5;
    border-radius: 10px;
    background: #fff;
    object-fit: cover;
}

.post-details {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    font-size: 0.86rem;
}

.page-note { font-size: 0.86rem; }

.btn-primary {
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg, #d04b5b, #a63f6a);
    border: none;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #1f6db3, #1a4f89);
    border-color: #1f6db3;
}
</style>
