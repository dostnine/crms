<script setup>
import { ref } from 'vue'

const props = defineProps({
    data: {
        type: Object,
    },
    form: {
        type: Object,
    },
});

const showComments = ref(true);

const getServiceOverviewTotals = (serviceId) => {
    const unitsData = props.data?.all_units_data?.units_data?.[serviceId] || {};
    let totalRespondents = 0;
    let totalVssRespondents = 0;

    Object.values(unitsData).forEach((unit) => {
        if (!unit) return;
        totalRespondents += Number(unit.total_respo || 0);
        totalVssRespondents += Number(unit.total_vss_respo || 0);
    });

    const percentage = totalRespondents > 0 ? (totalVssRespondents / totalRespondents) * 100 : 0;

    return {
        totalRespondents,
        totalVssRespondents,
        percentage,
    };
};

const normalizeComment = (item) => {
    if (typeof item === 'string') {
        return { text: item, unit: 'N/A', isComplaint: false, date: '' };
    }
    if (!item || typeof item !== 'object') {
        return { text: '', unit: 'N/A', isComplaint: false, date: '' };
    }
    const rawDate = item.date || item.created_at || item['date'] || item['created_at'] || '';
    const text = (item.text ?? item.comment ?? item['text'] ?? item['comment'] ?? '').trim();
    const unit = (item.unit_name ?? item.unit ?? item['unit_name'] ?? item['unit'] ?? 'N/A').trim();
    const isComplaint = Boolean(
        item.is_complaint ?? item.isComplaint ?? item['is_complaint'] ?? item['isComplaint']
    );
    return {
        text,
        unit,
        isComplaint,
        date: rawDate || '',
    };
};
</script>

<template>
    <div class="card mb-3 shadow-lg mx-3" v-if="props.data && props.data.cc_data">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="ri-file-list-line me-2"></i>
                PART I: CITIZEN'S CHARTER(CC)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th></th>
                            <th></th>
<th>Number of Respondents</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-blue-200">
<th>CC1</th>
<th colspan="2" class="text-left">Which of the following best describes your awareness of a CC?</th>


                        </tr>
                        <tr>
                            <td>1</td>
                            <td class="text-left">I know what a CC is and I saw this office's CC</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc1_data?.cc1_ans1 > 0">{{props.data.cc_data.cc1_data.cc1_ans1}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="text-left">I know what a CC is but I did NOT see this office's CC</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc1_data?.cc1_ans2 > 0">{{props.data.cc_data.cc1_data.cc1_ans2}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="text-left">I learned the CC when I saw this office's CC</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc1_data?.cc1_ans3 > 0">{{props.data.cc_data.cc1_data.cc1_ans3}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <!-- Add other CC rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PART II: Service Units Overview -->
    <div class="card mb-3 shadow-lg mx-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="ri-star-line me-2"></i>
                PART II: SERVICE UNITS OVERVIEW - YEARLY SUMMARY
            </h5>
        </div>
        <!-- Service Units table - unchanged in this fix -->
        <div class="card-body p-0">
            <!-- Table content here -->
        </div>
    </div>

    <!-- Comments Section -->
    <div v-if="(props.data?.total_comments || 0) > 0 || (props.data?.total_complaints || 0) > 0 || (props.data?.comments && props.data.comments.length > 0)" class="card mb-3 shadow-lg mx-3">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark">
                    <i class="ri-chat-1-line me-2"></i>
                    COMMENTS AND COMPLAINTS
                </h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="showComments = !showComments">
                    {{ showComments ? 'Hide' : 'Show' }}
                </button>
            </div>
        </div>
        <div v-show="showComments" class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Comments:</span>
                        <span class="badge bg-primary fs-6">{{ props.data.total_comments || 0 }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Complaints:</span>
                        <span class="badge bg-danger fs-6">{{ props.data.total_complaints || 0 }}</span>
                    </div>
                </div>
            </div>
            <div v-if="props.data.comments && props.data.comments.length > 0" class="mt-3">
                <h6 class="text-muted mb-3">
                    <i class="ri-list-check me-1"></i>Comments List ({{ props.data.comments.length }} total)
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover table-sm comments-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px">#</th>
                                <th style="width: 150px">Unit</th>
                                <th>Comment</th>
                                <th style="width: 120px">Date</th>
                                <th style="width: 100px">Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(rawComment, index) in props.data.comments" :key="'comment-' + index" class="align-middle">
                                <td class="fw-bold text-muted">{{ index + 1 }}</td>
                                <td>
                                    <span class="badge bg-info text-wrap">{{ normalizeComment(rawComment).unit }}</span>
                                </td>
                                <td :class="normalizeComment(rawComment).isComplaint ? 'text-danger fw-semibold' : ''">
                                    <div v-if="(normalizeComment(rawComment).text || '').length > 100" class="comment-text">
                                        <span>{{ (normalizeComment(rawComment).text || '').substring(0, 100) }}...</span>
                                        <button class="btn btn-sm btn-link p-0 ms-1" @click.stop>Read more</button>
                                    </div>
                                    <div v-else>{{ normalizeComment(rawComment).text || '' }}</div>
                                </td>
                                <td><small class="text-muted">{{ normalizeComment(rawComment).date }}</small></td>
                                <td>
                                    <span v-if="normalizeComment(rawComment).isComplaint" class="badge bg-danger">Complaint</span>
                                    <span v-else class="badge bg-success">Comment</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div v-show="!showComments" class="card-body text-muted">
            Comments and complaints section is collapsed.
        </div>
    </div>
</template>

<style scoped>
.card {
    border: 1px solid #d8e5f5;
    border-radius: 12px;
    overflow: hidden;
}

.card-header.bg-primary,
.card-header.bg-success,
.card-header.bg-info {
    background: linear-gradient(90deg, #1b365d, #2a568f) !important;
}

table {
    border-collapse: collapse;
    width: 100%;
}

tr, th, td {
    border: 1px solid #b8cbe2 !important;
    padding: 8px;
}

.table-dark th {
    background: #1f3b6e !important;
    border-color: #365988 !important;
}

.total-row {
    font-weight: bold;
    background-color: #edf4ff;
}

.bg-blue-200 {
    background-color: #e6f1ff;
}

/* Comments table styles */
.comments-table {
    font-size: 0.875rem;
}

.comments-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.comments-table tbody tr:hover {
    background-color: #f8f9ff;
}

.comment-text {
    cursor: pointer;
}

.badge {
    font-size: 0.75rem;
}
</style>
