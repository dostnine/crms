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
    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="ri-file-list-line me-2"></i>
                PART I: CITIZEN'S CHARTER(CC)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
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
                            <td class="text-center" v-if="data.cc_data?.cc1_data?.cc1_ans1">{{data.cc_data.cc1_data.cc1_ans1}}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="text-left">I know what a CC is but I did NOT see this office's CC</td>
                            <td class="text-center" v-if="data.cc_data?.cc1_data.cc1_ans2 ">{{data.cc_data.cc1_data.cc1_ans2}}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="text-left">I learned the CC when I saw this office's CC</td>
                            <td class="text-center" v-if="data.cc_data?.cc1_data?.cc1_ans3 > 0">{{data.cc_data.cc1_data.cc1_ans3}}</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="text-left">I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)</td>
                            <td class="text-center" v-if="data.cc_data?.cc1_data?.cc1_ans4 > 0">{{data.cc_data.cc1_data.cc1_ans4}}</td>
                        </tr>
                        <tr class="bg-blue-200">
                            <th>CC2</th>
                            <th colspan="2" class="text-left" >If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td class="text-left">Easy to see</td>
                            <td class="text-center" v-if="data.cc_data?.cc2_data?.cc1_ans1 > 0">{{data.cc_data.cc2_data.cc2_ans1}}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="text-left">Somewhat easy to see</td>
                            <td class="text-center" v-if="data.cc_data?.cc2_data?.cc2_ans2 > 0">{{data.cc_data.cc2_data.cc2_ans2}}</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="text-left">Difficult to see</td>
                            <td class="text-center" v-if="data.cc_data?.cc2_data?.cc2_ans3 > 0">{{data.cc_data.cc2_data.cc2_ans3}}</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="text-left">Not visible at all</td>
                            <td class="text-center" v-if="data?.cc_data?.cc2_data.cc2_ans4 > 0">{{data.cc_data.cc2_data.cc2_ans4}}</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td class="text-left">N/A</td>
                            <td class="text-center" v-if="data?.cc_data?.cc2_data.cc2_ans5 > 0">{{data.cc_data.cc2_data.cc2_ans5}}</td>
                        </tr>
                        <tr class="bg-blue-200">
                            <th>CC3</th>
                            <th colspan="2" class="text-left">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</th>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td class="text-left">Helped Very Much</td>
                            <td class="text-center" v-if="data?.cc_data?.cc3_data.cc3_ans1 > 0"><span v-if="data.cc_data.cc3_data.cc3_ans1">{{data.cc_data.cc3_data.cc3_ans1}}</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="text-left">Somewhat helped</td>
                            <td class="text-center"><span v-if="data.cc_data?.cc3_data.cc3_ans2">{{data.cc_data.cc3_data.cc3_ans2}}</span></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="text-left">Did not help</td>
                            <td class="text-center"><span v-if="data.cc_data?.cc3_data?.cc3_ans3">{{data.cc_data.cc3_data.cc3_ans3}}</span></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="text-left">N/A</td>
                            <td class="text-center"><span v-if="data.cc_data?.cc3_data?.cc3_ans4">{{data.cc_data.cc3_data.cc3_ans4}}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="ri-star-line me-2"></i>
                PART II: CUSTOMER RATING OF SERVICE QUALITY
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">Service Quality Attributes</th>
                            <th>5</th>
                            <th>4</th>
                            <th>3</th>
                            <th>2</th>
                            <th>1</th>
                            <th rowspan="2" class="align-middle">TOTAL SCORE</th>
                            <th rowspan="2" class="align-middle">Likert Scale Rating</th>
                            <th rowspan="2" class="align-middle">GAP</th>
                        </tr>
                        <tr class="text-center">
                            <th>Very Satisfied</th>
                            <th>Satisfied</th>
                            <th>Neither</th>
                            <th>Dissatisfied</th>
                            <th>Very Dissatisfied</th>
                        </tr>
                    </thead>
                    <tbody>

                    <tr v-for="(dimension, index) in data.dimensions" :key="dimension.id" class="border border-solid hover:bg-gray-100 focus-within:bg-gray-100">                     
                            <td class="border-t p-1 pl-3 ">
                                {{ index + 1 }}. {{ dimension.name }}
                            </td>
                            <td v-if="data.y_totals" class="text-center"  v-for="total in data.y_totals[index+1]">
                                <span v-if="total != 0">{{ total }}</span>
                                <span v-else></span>
                            </td>
                            <td v-if="data.x_totals" class="text-center"  v-for="total in data.x_totals[index+1]">
                                <span v-if="total != 0">{{ total }}</span>
                                <span v-else></span>
                            </td>
                                <td v-if="data.likert_scale_rating_totals" class=" text-center"  v-for="total in data.likert_scale_rating_totals[index+1]">
                                <span v-if="total != 0">{{ total }}</span>
                                <span v-else></span>
                            </td>          
                            <td v-if="data.gap_totals" class="text-center"  v-for="total in data.gap_totals[index+1]">
                                <span v-if="total != 0">{{ total }}</span>
                            </td>                   
                    </tr>
                    <tr class="text-center font-black p-1 m-3 border border-solid hover:bg-gray-100 focus-within:bg-gray-100" >
                        <td class="m-5 p-1">TOTAL SCORE</td>
                        <td ><span v-if="data.grand_vs_total != 0">{{ data.grand_vs_total }} </span> </td>
                        <td ><span v-if="data.grand_s_total != 0">{{ data.grand_s_total }} </span></td>
                        <td ><span v-if="data.grand_n_total != 0">{{ data.grand_n_total }}</span></td>
                        <td ><span v-if="data.grand_d_total != 0">{{ data.grand_d_total }}</span></td>
                        <td ><span v-if="data.grand_vd_total != 0">{{ data.grand_vd_total }}</span></td>
                        <!-- total score -->
                        <td ><span v-if="data.x_grand_total != 0">{{ data.x_grand_total }}</span></td> 
                         <!-- likert scale rating -->
                        <td ><span v-if="data.lsr_grand_total != 0">{{ data.lsr_grand_total }}</span></td>
                        <!--  gap grand total right side or by row-->
                        <td ><span v-if="data.gap_grand_total != 0">{{ data.gap_grand_total }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
      </div>
            
    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="ri-lightbulb-line me-2"></i>
                PART III: IMPORTANCE OF THIS ATTRIBUTE
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th rowspan="2" class="align-middle">Importance Service Quality Attributes</th>
                            <th>5</th>
                            <th>4</th>
                            <th>3</th>
                            <th>2</th>
                            <th>1</th>
                            <th rowspan="2" class="align-middle">TOTAL SCORE</th>
                            <th rowspan="2" class="align-middle">Likert Scale Rating</th>
                            <th rowspan="2" class="align-middle">WF</th>
                            <th rowspan="2" class="align-middle">SS</th>
                            <th rowspan="2" class="align-middle">WS</th>
                        </tr>
                        <tr class="text-center">
                            <th>Very Important</th>
                            <th>Important</th>
                            <th>Moderately Important</th>
                            <th>Slightly Important</th>
                            <th>Not All Important</th>
                        </tr>
                    </thead>
                    <tbody>


                        <tr v-for="(dimension, index) in data.dimensions" :key="dimension.id" class="border border-solid hover:bg-gray-100 focus-within:bg-gray-100">
                            
                                <td class="border-t p-2 text-center">
                                    {{ index + 1 }}. {{ dimension.name }}
                                </td>
                                <td v-if="data.importance_rate_score_totals" class="border-t p-2 text-center"  v-for="total in data.importance_rate_score_totals[index+1]">
                                    <span v-if="total != 0">{{ total }}</span>
                                </td>
                                <td v-if="data.x_importance_totals" class="border-t p-2 text-center"  v-for="total in data.x_importance_totals[index+1]">
                                    <span v-if="total != 0">{{ total }}</span>
                                </td>
                                <td v-if="data.likert_scale_rating_totals" class="border-t text-center"  v-for="total in data.importance_ilsr_totals[index+1]">
                                    <span v-if="total != 0">{{ total }}</span>
                                </td>  
                                <td v-if="data.wf_totals" class="border-t p-2 text-center"  v-for="total in data.wf_totals[index+1]">
                                    <span v-if="total != 0">{{ total }}</span>
                                </td>       
                                <td v-if="data.ss_totals" class="border-t p-2 text-center"  v-for="total in data.ss_totals[index+1]">
                                    <span v-if="total != 0">{{ total }}</span>
                                </td>  
                                <td v-if="data.ws_totals" class="border-t p-2 text-center mr-10"  v-for="total in data.ws_totals[index+1]">
                                    <span v-if="total != 0">{{ total }}</span>
                                </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="ri-group-line me-2"></i>
                        Total No. of Respondents/Customers
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-primary mb-0">{{ data.total_respondents || 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-info shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="ri-star-half-line me-2"></i>
                        Customer Satisfaction Index (CSI)
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-info mb-0">{{ data.customer_satisfaction_index || 0 }}%</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-success shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="ri-thumb-up-line me-2"></i>
                        Respondents who rated VS/S
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h4 class="text-success mb-1">{{ data.total_vss_respondents || 0 }}</h4>
                    <small class="text-muted">{{ data.percentage_vss_respondents || 0 }}%</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="ri-bar-chart-line me-2"></i>
                        Net Promoter Score (NPS)
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-warning mb-0">{{ data.net_promoter_score || 0 }}%</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="ri-emotion-happy-line me-2"></i>
                        Customer Satisfaction Rating (CSAT)
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-primary mb-0">{{ data.customer_satisfaction_rating || 0 }}%</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-secondary shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="ri-line-chart-line me-2"></i>
                        Likert Scale Rating (Average)
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-secondary mb-0">{{ data.lsr_grand_total || 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-success shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="ri-user-smile-line me-2"></i>
                        Percentage of Promoters
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-success mb-0">{{ data.percentage_promoters || 0 }}%</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="ri-user-unhappy-line me-2"></i>
                        Percentage of Detractors
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="text-danger mb-0">{{ data.percentage_detractors || 0 }}%</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-dark">
                <i class="ri-chat-1-line me-2"></i>
                COMMENTS AND COMPLAINTS
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Comments:</span>
                        <span class="badge bg-primary fs-6">{{ data.total_comments || 0 }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Complaints:</span>
                        <span class="badge bg-danger fs-6">{{ data.total_complaints || 0 }}</span>
                    </div>
                </div>
            </div>
            <div v-if="data.comments && data.comments.length > 0" class="mt-3">
                <h6 class="text-muted mb-3">Comments:</h6>
                <div v-for="(comment, index) in data.comments" :key="index" class="mb-2 p-2 bg-light rounded">
                    <strong>[{{ index + 1 }}]</strong> {{ comment }}
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-dark">
                <i class="ri-file-text-line me-2"></i>
                ANALYSIS
            </h5>
        </div>
        <div class="card-body">
            <div v-if="data.unit && data.service" class="text-justify">
                The <strong>{{ data.unit.unit_name }}</strong> unit had <strong>{{ data.total_respondents || 0 }}</strong> respondents who rated the CSF,
                and <strong>{{ data.total_vss_respondents || 0 }}</strong> (or <strong>{{ data.percentage_vss_respondents || 0 }}%</strong>) of those respondents rated
                the unit with satisfied responses (VS & S) for all service quality attributes. The <strong>{{ data.unit.unit_name }}</strong> unit had a
                <strong>{{ data.customer_satisfaction_index || 0 }}%</strong> Customer Satisfaction Index as well as a Net Promoter Score of <strong>{{ data.net_promoter_score || 0 }}%</strong>.
                The Customer Satisfaction Rating for the <strong>{{ data.unit.unit_name }}</strong>
                unit is <strong>{{ data.customer_satisfaction_rating || 0 }}%</strong>,
                which <span v-if="data.customer_satisfaction_rating < 95" class="text-danger"><strong>does not</strong></span><span v-else class="text-success"><strong>achieved</strong></span> its functional objective of 95% of customers surveyed are at least satisfied with the S&T services.
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

.card-header {
    border-bottom: 0;
    font-weight: 700;
}

.card-header.bg-primary,
.card-header.bg-success,
.card-header.bg-warning {
    background: linear-gradient(90deg, #1b365d, #2a568f) !important;
    color: #fff !important;
}

.card-header.bg-light {
    background: #eff6ff !important;
}

.table {
    margin-bottom: 0;
}

.table thead.table-dark th {
    background: #1f3b6e !important;
    border-color: #365988;
}

.table th,
.table td {
    border-color: #b8cbe2 !important;
    vertical-align: middle;
}

.bg-blue-200 {
    background-color: #e6f1ff !important;
}

.badge {
    border-radius: 999px;
    padding: 0.4rem 0.65rem;
}

.card-body h3 {
    font-weight: 800;
}
</style>
