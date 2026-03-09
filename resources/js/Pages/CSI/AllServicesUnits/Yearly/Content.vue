<script setup>
    const props = defineProps({
        data: {
            type: Object,
        },
        form: {
            type: Object,
        },
    });

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
                        <tr>
                            <td>4</td>
                            <td class="text-left">I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc1_data?.cc1_ans4 > 0">{{props.data.cc_data.cc1_data.cc1_ans4}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr class="bg-blue-200">
                            <th>CC2</th>
                            <th colspan="2" class="text-left">If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td class="text-left">Easy to see</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc2_data?.cc2_ans1 > 0">{{props.data.cc_data.cc2_data.cc2_ans1}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="text-left">Somewhat easy to see</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc2_data?.cc2_ans2 > 0">{{props.data.cc_data.cc2_data.cc2_ans2}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="text-left">Difficult to see</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc2_data?.cc2_ans3 > 0">{{props.data.cc_data.cc2_data.cc2_ans3}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="text-left">Not visible at all</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc2_data?.cc2_ans4 > 0">{{props.data.cc_data.cc2_data.cc2_ans4}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td class="text-left">N/A</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc2_data?.cc2_ans5 > 0">{{props.data.cc_data.cc2_data.cc2_ans5}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr class="bg-blue-200">
                            <th>CC3</th>
                            <th colspan="2" class="text-left">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td class="text-left">Helped Very Much</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc3_data?.cc3_ans1 > 0">{{props.data.cc_data.cc3_data.cc3_ans1}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="text-left">Somewhat helped</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc3_data?.cc3_ans2 > 0">{{props.data.cc_data.cc3_data.cc3_ans2}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="text-left">Did not help</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc3_data?.cc3_ans3 > 0">{{props.data.cc_data.cc3_data.cc3_ans3}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="text-left">N/A</td>
                            <td class="text-center" v-if="props.data.cc_data?.cc3_data?.cc3_ans4 > 0">{{props.data.cc_data.cc3_data.cc3_ans4}}</td>
                            <td class="text-center" v-else>0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3 shadow-lg mx-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="ri-star-line me-2"></i>
                PART II: SERVICE UNITS OVERVIEW - YEARLY SUMMARY
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th>Service Unit</th>
                            <th>Total No. of Respondents</th>
                            <th>Total No. of Respondents who rated VS and S</th>
                            <th>Percentage of Respondents who rated VS and S</th>
                            <th>Customer Satisfaction Rating</th>
                            <th>Customer Satisfaction Index (CSI)</th>
                            <th>Net Promoter Score</th>
                            <th>Likert Scale Rating (Attribute Average)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="props.data && props.data.services_units && props.data.services_units.data">
                            <template v-for="(service, index) in props.data.services_units.data" :key="index">
                                <tr class="bg-blue-200">
                                    <td colspan="8"><strong>{{ service.services_name }}</strong></td>
                                </tr>
                                <template v-for="(unit, unitIndex) in service.units" :key="unitIndex">
                                    <!-- Hide row if respondent count is 0 -->
                                    <tr v-if="(service.id == 1 && unit.id == 1 && props.data.ord_total_respondents?.secretariat_total_respo > 0) || 
                                              (service.id == 1 && unit.id == 2 && props.data.ord_total_respondents?.gad_total_respo > 0) || 
                                              (service.id == 1 && unit.id == 3 && props.data.ord_total_respondents?.fairness_total_respo > 0)">
                                        <td class="pl-4">{{ unit.unit_name }}</td>

                                        <!-- Total Respondents -->
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 1">{{ props.data.ord_total_respondents?.secretariat_total_respo || 0 }}</td>
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 2">{{ props.data.ord_total_respondents?.gad_total_respo || 0 }}</td>
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 3">{{ props.data.ord_total_respondents?.fairness_total_respo || 0 }}</td>
                                        
                                        <!-- Total Respondents who rated VS or S -->
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 1">{{ props.data.ord_total_vss_respondents?.secretariat_total_vss_respo || 0 }}</td>
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 2">{{ props.data.ord_total_vss_respondents?.gad_total_vss_respo || 0 }}</td>
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 3">{{ props.data.ord_total_vss_respondents?.fairness_total_vss_respo || 0 }}</td>
                                        
                                        <!-- Percentage Respondents who rated VS or S -->
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 1">{{ props.data.ord_percentage_vss_respondents?.secretariat_percentage_vss_respo || 0 }}%</td>
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 2">{{ props.data.ord_percentage_vss_respondents?.gad_percentage_vss_respo || 0 }}%</td>
                                        <td class="text-center" v-if="service.id == 1 && unit.id == 3">{{ props.data.ord_percentage_vss_respondents?.fairness_percentage_vss_respo || 0 }}%</td>
                                        
                                        <!-- Customer Satisfaction Rating -->
                                        <td class="text-center">{{ props.data.ord_percentage_vss_respondents?.secretariat_percentage_vss_respo || 0 }}%</td>
                                        
                                        <!-- CSI Total -->
                                        <td class="text-center">{{ props.data.csi_total || 0 }}</td>
                                        
                                        <!-- Net Promoter Score -->
                                        <td class="text-center">0</td>
                                        
                                        <!-- Likert Scale Rating -->
                                        <td class="text-center">0</td>
                                    </tr>
                                </template>
                            </template>
                        </template>
                        <tr v-else>
                            <td colspan="8" class="text-center text-muted">No data available</td>
                        </tr>
                    
                        <tr class="total-row">
                            <td><strong>Total:</strong></td>
                            <td class="text-center"><strong>{{ props.data?.ord_total_respondents?.secretariat_total_respo || 0 }}</strong></td>
                            <td class="text-center"><strong>{{ props.data?.ord_total_vss_respondents?.secretariat_total_vss_respo || 0 }}</strong></td>
                            <td class="text-center"><strong>{{ props.data?.ord_percentage_vss_respondents?.secretariat_percentage_vss_respo || 0 }}%</strong></td>
                            <td class="text-center"><strong>{{ props.data?.ord_percentage_vss_respondents?.secretariat_percentage_vss_respo || 0 }}%</strong></td>
                            <td class="text-center"><strong>{{ props.data?.csi_total || 0 }}</strong></td>
                            <td class="text-center">0</td>
                            <td class="text-center">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quarterly Breakdown -->
    <div class="card mb-3 shadow-lg mx-3">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="ri-calendar-todo-line me-2"></i>
                PART III: QUARTERLY BREAKDOWN
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th>Quarter</th>
                            <th>Total Respondents</th>
                            <th>VS + S Respondents</th>
                            <th>Percentage</th>
                            <th>CSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><strong>Q1</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td class="text-center"><strong>Q2</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td class="text-center"><strong>Q3</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                        <tr>
                            <td class="text-center"><strong>Q4</strong></td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assessment Summary -->
    <div class="card shadow-lg mx-3">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="ri-analysis-line me-2"></i>
                ASSESSMENT SUMMARY - YEAR {{ props.form?.selected_year || 'N/A' }}
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p class="mb-2">
                    <i class="ri-information-line me-2"></i>
                    The Department of Science and Technology IX for the year <strong>{{ props.form?.selected_year || 'N/A' }}</strong> had a total of 
                    <strong>{{ props.data?.ord_total_respondents?.secretariat_total_respo || 0 }}</strong> respondents who filled out and rated the Customer Satisfaction Feedback. 
                    <strong>{{ props.data?.ord_total_vss_respondents?.secretariat_total_vss_respo || 0 }}</strong> (or <strong>{{ props.data?.ord_percentage_vss_respondents?.secretariat_percentage_vss_respo || 0 }}%</strong>) 
                    of the respondents rated the CSF as either very satisfied (VS) or satisfied (S).
                </p>
                <p class="mb-0">
                    This resulted in an overall average Customer Satisfaction Index (CSI) of <strong>{{ props.data?.csi_total || 0 }}%</strong>.
                </p>
            </div>
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
</style>
