<script setup>
    import { computed, ref } from 'vue';
    const props = defineProps({
        data: {
            type: Object,
            default: () => ({}),
        },
        form: {
            type: Object,
            default: () => ({}),
        },
    });
    const calculate = (total_score ,grand_total_score) => {
        const result = (total_score / grand_total_score) * 100;
        return result.toFixed(2);
    };

    const commentFilter = ref('all');
    const filteredComments = computed(() => {
        const list = props.data.comments || [];
        if (commentFilter.value === 'comments') return list.filter((c) => !c.is_complaint);
        if (commentFilter.value === 'complaints') return list.filter((c) => c.is_complaint);
        return list;
    });

    const piePalette = ['#2e7d32', '#8bc34a', '#9e9e9e', '#f57c00', '#c62828'];
    const pieLabels = ['Very Satisfied', 'Satisfied', 'Neither', 'Dissatisfied', 'Very Dissatisfied'];
    const pieChart = computed(() => {
        const counts = [
            Number(props.data.vs_grand_total_raw_points || 0),
            Number(props.data.s_grand_total_raw_points || 0),
            Number(props.data.n_grand_total_raw_points || 0),
            Number(props.data.d_grand_total_raw_points || 0),
            Number(props.data.vd_grand_total_raw_points || 0),
        ];
        const total = counts.reduce((a, b) => a + b, 0);
        if (!total) {
            return {
                background: `conic-gradient(${piePalette[2]} 0% 100%)`,
                legend: pieLabels.map((label, idx) => ({ label, color: piePalette[idx], pct: '0.00' })),
            };
        }
        const percentages = counts.map((v) => (v / total) * 100);
        let offset = 0;
        const slices = percentages.map((pct, idx) => {
            const start = offset;
            offset += pct;
            return `${piePalette[idx]} ${start.toFixed(2)}% ${offset.toFixed(2)}%`;
        });
        return {
            background: `conic-gradient(${slices.join(', ')})`,
            legend: pieLabels.map((label, idx) => ({ label, color: piePalette[idx], pct: percentages[idx].toFixed(2) })),
        };
    });
</script>
<template>
    <div class="quarter-preview-col cc-card" v-if="data && data.cc_data">
    <div class="quarter-preview-heading">
        PART I: CITIZEN'S CHARTER(CC)
    </div>
    <table style="width:100%; border: 1px solid #333; border-collapse: collapse; padding: 5px" class="text-center">
        <tr>
            <th></th>
            <th></th>
            <th>Respondents</th>
            <th>Percentage</th>
        </tr>
        <tr class="bg-blue-200">
            <th>CC1</th>
            <th colspan="3" class="text-left">Which of the following best describes your awareness of a CC?</th>
        </tr>
        <tr>
            <td>1</td>
            <td class="text-left">I know what a CC is and I saw this office's CC</td>
            <td>
                <span v-if="data?.cc_data?.cc1_data?.cc1_ans1 > 0">
                    {{ data?.cc_data?.cc1_data?.cc1_ans1 }}
                </span>
            </td>
            <td>{{ data?.cc_data?.cc1_data?.cc1_ans1_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>2</td>
            <td class="text-left">I know what a CC is but I did NOT see this office's CC</td>
            <td>
                <span v-if="data.cc_data.cc1_data.cc1_ans2 > 0">
                    {{ data.cc_data.cc1_data.cc1_ans2 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc1_data.cc1_ans2_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>3</td>
            <td class="text-left">I learned the CC when I saw this office's CC</td>
            <td>
                <span v-if=" data.cc_data.cc1_data.cc1_ans3 > 0">
                    {{ data.cc_data.cc1_data.cc1_ans3 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc1_data.cc1_ans3_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>4</td>
            <td class="text-left">I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)</td>
            <td>
                <span v-if="data.cc_data.cc1_data.cc1_ans4 > 0">
                    {{ data.cc_data.cc1_data.cc1_ans4 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc1_data.cc1_ans4_pct || 0 }}%</td>
        </tr>
        <tr class="bg-blue-200">
            <td></td>
            <td class="text-left"><strong>Total</strong></td>
            <td><strong>{{ data.cc_data?.cc1_data?.cc1_total || 0 }}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
        <tr class="bg-blue-200" >
            <th >CC2</th>
            <th colspan="3" class="text-left">If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?</th>
        </tr>
        <tr>
            <td>1</td>
            <td class="text-left">Easy to see</td>
            <td>
                <span v-if="data.cc_data.cc2_data.cc2_ans1 > 0">
                    {{ data.cc_data.cc2_data.cc2_ans1 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc2_data.cc2_ans1_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>2</td>
            <td class="text-left">Somewhat easy to see</td>
            <td>
                <span v-if="data.cc_data.cc2_data.cc2_ans2 > 0">
                    {{ data.cc_data.cc2_data.cc2_ans2 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc2_data.cc2_ans2_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>3</td>
            <td class="text-left">Difficult to see</td>
            <td>
                <span v-if="data.cc_data.cc2_data.cc2_ans3 > 0">
                    {{ data.cc_data.cc2_data.cc2_ans3 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc2_data.cc2_ans3_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>4</td>
            <td class="text-left">Not visible at all</td>
            <td>
                <span v-if="data.cc_data.cc2_data.cc2_ans4 > 0">
                    {{ data.cc_data.cc2_data.cc2_ans4 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc2_data.cc2_ans4_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>5</td>
            <td class="text-left">N/A</td>
            <td>
                <span v-if="data.cc_data.cc2_data.cc2_ans5 > 0">
                    {{ data.cc_data.cc2_data.cc2_ans5 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc2_data.cc2_ans5_pct || 0 }}%</td>
        </tr>
        <tr class="bg-blue-200">
            <td></td>
            <td class="text-left"><strong>Total</strong></td>
            <td><strong>{{ data.cc_data?.cc2_data?.cc2_total || 0 }}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
        <tr class="bg-blue-200">
            <th >CC3</th>
            <th colspan="3" class="text-left">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</th>
        </tr>
        <tr>
            <td>1</td>
            <td class="text-left">Helped Very Much</td>
            <td>
                <span v-if="data.cc_data.cc3_data.cc3_ans1 > 0">
                    {{ data.cc_data.cc3_data.cc3_ans1 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc3_data.cc3_ans1_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>2</td>
            <td class="text-left">Somewhat helped</td>
            <td>
                <span v-if="data.cc_data.cc3_data.cc3_ans2 > 0">
                    {{ data.cc_data.cc3_data.cc3_ans2 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc3_data.cc3_ans2_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>3</td>
            <td class="text-left">Did not help</td>
            <td>
                <span v-if="data.cc_data.cc3_data.cc3_ans3 > 0">
                    {{ data.cc_data.cc3_data.cc3_ans3 }}
                </span>
            </td>
            <td>{{ data.cc_data.cc3_data.cc3_ans3_pct || 0 }}%</td>
        </tr>
        <tr>
            <td>4</td>
            <td class="text-left">N/A</td>
            <td>
                <span v-if="data.cc_data.cc3_data.cc3_ans4 > 0">
                    {{ data.cc_data.cc3_data.cc3_ans4 }}
                </span>

            </td>
            <td>{{ data.cc_data.cc3_data.cc3_ans4_pct || 0 }}%</td>
        </tr>
        <tr class="bg-blue-200">
            <td></td>
            <td class="text-left"><strong>Total</strong></td>
            <td><strong>{{ data.cc_data?.cc3_data?.cc3_total || 0 }}</strong></td>
            <td><strong>100%</strong></td>
        </tr>
    </table>
    </div>

    <div class="quarter-preview-grid">
    <div class="quarter-preview-col">
        <div class="quarter-preview-heading">
            PART I: CUSTOMER RATING OF SERVICE QUALITY
        </div>
        <table class="w-full border ">
            <tr class="text-left font-bold text-center bg-blue-200">
                <th colspan="3">Service Quality Attributes</th>
                <th >OCT</th> 
                <th >NOV</th>
                <th >DEC</th>
                <th>Total Raw Points</th>
                <th>Total Score</th>
                <th >Likert Scale Rating</th>
            </tr>

            <template v-for="(dimension, index) in data.dimensions" :key="dimension.id" class="border border-solid hover:bg-gray-100 focus-within:bg-gray-100">                     
                    <tr>
                        <td class="text-center" rowspan="7">
                            [{{ index + 1 }}] {{ dimension.name }}    
                        </td>             
                    </tr> 
                    <tr class="row-vs">
                        <td class="text-left pl-3">5</td>
                        <td>Very Satisfied</td>
                        <td v-if="data.vs_totals" class="text-center"  v-for="total in data.vs_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }} 
                            </span>
                        </td>
                        <td v-if="data.trp_totals" class="text-center" >
                            <span v-if="data.trp_totals[index+1].vs_total_raw_points > 0">
                                {{ data.trp_totals[index+1].vs_total_raw_points }} 
                            </span>
                        </td>
                        <td v-if="data.p1_total_scores" class="text-center" >
                            <span v-if="data.p1_total_scores[index+1].x_vs_total > 0">
                                {{ data.p1_total_scores[index+1].x_vs_total }}
                            </span>
                        </td>
                        <td v-if="data.lsr_totals" class="text-center" >
                            <span v-if="data.lsr_totals[index+1].vs_lsr_total > 0">
                                {{ data.lsr_totals[index+1].vs_lsr_total }} 
                            </span>
                        </td>
                    </tr>
                    <tr class="row-s">
                        <td class="text-center">4</td>
                        <td>Satisfied</td>
                        <td v-if="data.s_totals" class="text-center"  v-for="total in data.s_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.trp_totals"  class="text-center" >
                            <span v-if="data.trp_totals[index+1].s_total_raw_points > 0">
                                {{ data.trp_totals[index+1].s_total_raw_points }}
                            </span>
                        </td>
                        <td v-if="data.p1_total_scores" class="text-center" >
                            <span v-if="data.p1_total_scores[index+1].x_s_total > 0">
                                {{ data.p1_total_scores[index+1].x_s_total }}
                            </span>
                        </td>
                        <td v-if="data.lsr_totals" class="text-center" >
                            <span v-if="data.lsr_totals[index+1].s_lsr_total > 0">
                                {{ data.lsr_totals[index+1].s_lsr_total }} 
                            </span>
                        </td>
                    </tr>
                    <tr class="row-n">
                        <td class="text-center">3</td>
                        <td>Neither</td>
                        <td v-if="data.n_totals" class="text-center"  v-for="total in data.n_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.trp_totals" class="text-center" >
                            <span v-if="data.trp_totals[index+1].n_total_raw_points > 0">
                                {{ data.trp_totals[index+1].n_total_raw_points }}
                            </span>
                        </td>
                        <td v-if="data.p1_total_scores" class="text-center" >
                            <span v-if="data.p1_total_scores[index+1].x_n_total > 0">
                                {{ data.p1_total_scores[index+1].x_n_total }}
                            </span>
                        </td>
                        <td v-if="data.lsr_totals" class="text-center" >
                            <span v-if="data.p1_total_scores[index+1].x_n_total > 0">
                                {{ data.lsr_totals[index+1].n_lsr_total }}
                            </span>
                        </td>
                    </tr>
                    <tr class="row-d">
                        <td class="text-center">2</td>
                            <td>Dissatisfied</td>
                        <td v-if="data.d_totals" class="text-center"  v-for="total in data.d_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.trp_totals" class="text-center" >
                            <span v-if="data.trp_totals[index+1].d_total_raw_points > 0">
                                {{ data.trp_totals[index+1].d_total_raw_points }}
                            </span>
                        </td>
                        <td v-if="data.p1_total_scores" class="text-center" >
                            <span v-if="data.p1_total_scores[index+1].x_d_total > 0">
                                {{ data.p1_total_scores[index+1].x_d_total }}
                            </span>
                        </td>
                        <td v-if="data.lsr_totals" class="text-center" >
                            <span v-if="data.lsr_totals[index+1].d_lsr_total > 0">
                                {{ data.lsr_totals[index+1].d_lsr_total }}
                            </span>
                        </td>
                    </tr>
                    <tr class="row-vd">
                        <td class="text-center">1</td>
                            <td>Very Dissatisfied</td>
                        <td v-if="data.vd_totals" class="text-center"  v-for="total in data.vd_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }} 
                            </span>
                        </td>
                        <td v-if="data.trp_totals" class="text-center" >
                            <span v-if="data.trp_totals[index+1].vd_total_raw_points > 0">
                                {{ data.trp_totals[index+1].vd_total_raw_points }}
                            </span>
                        </td>
                        <td v-if="data.p1_total_scores" class="text-center" >
                            <span v-if="data.p1_total_scores[index+1].x_vd_total > 0">
                                {{ data.p1_total_scores[index+1].x_vd_total }}
                            </span>
                        </td>
                        <td v-if="data.lsr_totals" class="text-center" >
                            <span v-if="data.lsr_totals[index+1].vd_lsr_total > 0">
                                {{ data.lsr_totals[index+1].vd_lsr_total }} 
                            </span>
                        </td>
                    </tr>
                    <tr class="row-total">
                        <td class="text-center" colspan="2"></td>
                        <td v-if="data.grand_totals" class="text-center bg-gray-300"  v-for="total in data.grand_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }} 
                            </span>
                        </td>        
                        <td v-if="data.trp_totals" class="text-center bg-gray-200" >
                            <span v-if="data.trp_totals[index+1].total_raw_points > 0">
                                {{ data.trp_totals[index+1].total_raw_points }}
                            </span>
                        </td>  
                        <td v-if="data.p1_total_scores" class="text-center bg-gray-200" >
                            <span v-if="data.p1_total_scores[index+1].x_total_score > 0">
                                {{ data.p1_total_scores[index+1].x_total_score }}
                            </span>
                        </td>   
                        <td v-if="data.lsr_totals" class="text-center bg-gray-200" >
                            <span v-if="data.lsr_totals[index+1].lsr_total > 0">
                                {{ data.lsr_totals[index+1].lsr_total }}
                            </span>
                        </td>             
                    </tr>

            </template>   

            <!-- totals   -->
            <tr>
                <td></td>
            </tr>
                <tr>
                <td colspan="3" class="text-right">Total No. of Very Satisfied (VS) Responses:</td>
                <td class="text-center">
                    <span v-if="data.first_month_vs_grand_total > 0">
                        {{ data.first_month_vs_grand_total }} 
                    </span>
                </td>
                <td class="text-center"> 
                    <span v-if="data.second_month_vs_grand_total  > 0">
                        {{ data.second_month_vs_grand_total  }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.third_month_vs_grand_total   > 0">
                        {{ data.third_month_vs_grand_total   }}
                    </span>
                 </td>
                <td class="text-center">
                    <span v-if="data.vs_grand_total_raw_points  > 0">
                        {{ data.vs_grand_total_raw_points   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.vs_grand_total_score  > 0">
                        {{ data.vs_grand_total_score   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.vs_grand_total_score  > 0 && data.grand_total_score">
                        {{ calculate(data.vs_grand_total_score, data.grand_total_score) }} 
                    </span>  
                </td>

            </tr>
            <tr>
                <td colspan="3" class="text-right">Total No. of Satisfied (S) Responses:</td>
                <td class="text-center">
                    <span v-if="data.first_month_s_grand_total  > 0">
                        {{ data.first_month_s_grand_total   }}
                    </span>
                </td>
                <td class="text-center"> 
                    <span v-if="data.second_month_s_grand_total  > 0">
                        {{ data.second_month_s_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.third_month_s_grand_total  > 0">
                        {{ data.third_month_s_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.s_grand_total_raw_points  > 0">
                        {{ data.s_grand_total_raw_points   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.s_grand_total_score  > 0">
                        {{ data.s_grand_total_score   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.s_grand_total_score  > 0 && data.grand_total_score  > 0">
                        {{ calculate(data.s_grand_total_score, data.grand_total_score) }}
                    </span>
                </td>


            </tr>
            <tr>
                <td colspan="3" class="text-right">Total No. of Other (N, D, VD) Responses:</td>
                <td class="text-center">
                    <span v-if="data.first_month_ndvd_grand_total  > 0">
                        {{ data.first_month_ndvd_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.second_month_ndvd_grand_total  > 0">
                        {{ data.second_month_ndvd_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.third_month_ndvd_grand_total  > 0">
                        {{ data.third_month_ndvd_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.ndvd_grand_total_raw_points  > 0">
                        {{ data.ndvd_grand_total_raw_points   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.ndvd_grand_total_score  > 0">
                        {{ data.ndvd_grand_total_score   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.ndvd_grand_total_score  > 0 && data.grand_total_score  > 0">
                        {{ calculate(data.ndvd_grand_total_score, data.grand_total_score) }}
                    </span>     
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Total No. of All Responses:</td>
                <td class="text-center">
                    <span v-if="data.first_month_grand_total  > 0">
                        {{ data.first_month_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.second_month_grand_total  > 0">
                        {{ data.second_month_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.third_month_grand_total  > 0">
                        {{ data.third_month_grand_total   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.grand_total_raw_points  > 0">
                        {{ data.grand_total_raw_points   }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.grand_total_score  > 0">
                        {{ data.grand_total_score   }}
                    </span>
                </td>
                <td class="text-center"></td>
            </tr>
            <tr>
                <td colspan="8" class="text-right">Total No. of Respondents/Customers:</td>
                <td class="text-center">
                    <span v-if="data.total_respondents  > 0">
                        {{ data.total_respondents   }}
                    </span>
                </td>
            </tr>

            <tr>
                <td colspan="8" class="text-right">Total No. of Respondents/Customers who rated VS or S:</td>
                <td class="text-center">
                    <span v-if="data.total_vss_respondents > 0">
                        {{ data.total_vss_respondents    }}
                    </span>
                </td>
            </tr>
                <tr>
                <td colspan="8" class="text-right">Percentage No. of Respondents/Customers who rated VS or S:</td>
                <td class="text-center">
                    <span v-if="data.percentage_vss_respondents  > 0">
                        {{ data.percentage_vss_respondents   }}
                    </span>
                </td>
            </tr>
            <tr>
                <td colspan="8" class="text-right"> Likert Scale Rating (Average):</td>      
                <td class="text-center">
                    <span v-if="data.lsr_average > 0">
                        {{ data.lsr_average   }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="quarter-preview-col">
        <div class="quarter-preview-heading">
            PART II: IMPORTANCE OF THESE ATTRIBUTES TO THE CUSTOMERS
        </div>
            <table class="w-full border ">
            <tr class="text-left font-bold text-center bg-blue-200">
                <th  colspan="3">Importance Service Quality Attributes</th>
                <th >OCT</th>
                <th >NOV</th>
                <th >DEC</th>
                <th >Total Raw Points</th>
                <th  colspan="2">Total Score</th>
            </tr>

            <template v-for="(dimension, index) in data.dimensions" :key="dimension.id" class="border border-solid hover:bg-gray-100 focus-within:bg-gray-100">                     
                    <tr>
                        <td class="text-left pl-3" rowspan="6">
                            [{{ index + 1 }}] {{ dimension.name }}    
                        </td>             
                    </tr> 
                    <tr class="row-vs">
                        <td class="text-center">5</td>
                        <td>Very Important</td>
                        <td v-if="data.vi_totals" class="text-center"  v-for="total in data.vi_totals[index+1]">       
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.i_trp_totals" class="text-center" >
                            <span v-if="data.i_trp_totals[index+1].vi_total_raw_points > 0">
                                {{ data.i_trp_totals[index+1].vi_total_raw_points }}
                            </span>                    
                        </td>
                        <td v-if="data.i_total_scores" class="text-center" >
                            <span v-if="data.i_total_scores[index+1].x_vi_total  > 0">
                                {{ data.i_total_scores[index+1].x_vi_total }}
                            </span>     
                        </td>
                    </tr>
                    <tr class="row-s">
                        <td class="text-center">4</td>
                            <td>Important</td>
                        <td v-if="data.i_totals" class="text-center"  v-for="total in data.i_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.i_trp_totals" class="text-center" >
                            <span v-if="data.i_trp_totals[index+1].i_total_raw_points  > 0">
                                {{ data.i_trp_totals[index+1].i_total_raw_points }}
                            </span>     
                        </td>
                        <td v-if="data.i_total_scores" class="text-center" >
                            <span v-if="data.i_total_scores[index+1].x_i_total  > 0">
                                {{ data.i_total_scores[index+1].x_i_total }}
                            </span>     
                        </td>
                    </tr>
                    <tr class="row-n">
                        <td class="text-center">3</td>
                        <td>Moderately Important</td>
                        <td v-if="data.mi_totals" class="text-center"  v-for="total in data.mi_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.i_trp_totals" class="text-center" >
                            <span v-if="data.i_trp_totals[index+1].mi_total_raw_points  > 0">
                                {{ data.i_trp_totals[index+1].mi_total_raw_points }}
                            </span>  
                        </td>
                        <td v-if="data.i_total_scores" class="text-center" >
                            <span v-if="data.i_total_scores[index+1].x_mi_total > 0">
                                {{ data.i_total_scores[index+1].x_mi_total }}
                            </span>  
                        </td>
                        
                    </tr>
                    <tr class="row-d">
                        <td class="text-center">2</td>
                            <td>Slightly Important</td>
                        <td v-if="data.si_totals" class="text-center"  v-for="total in data.si_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.i_trp_totals" class="text-center" >
                            <span v-if="data.i_trp_totals[index+1].si_total_raw_points > 0">
                                {{ data.i_trp_totals[index+1].si_total_raw_points }}
                            </span>  
                        </td>
                        <td v-if="data.i_total_scores" class="text-center" >
                            <span v-if="data.i_total_scores[index+1].x_si_total  > 0">
                                {{  data.i_total_scores[index+1].x_si_total  }}
                            </span>  
                        </td>
                    </tr>
                    <tr class="row-vd">
                        <td class="text-center">1</td>
                            <td>Not all Important</td>
                        <td v-if="data.nai_totals" class="text-center"  v-for="total in data.nai_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }}
                            </span>
                        </td>
                        <td v-if="data.i_trp_totals" class="text-center" >
                            <span v-if="data.i_trp_totals[index+1].nai_total_raw_points  > 0">
                                {{  data.i_trp_totals[index+1].nai_total_raw_points  }} 
                            </span>  
                        </td>
                        <td v-if="data.i_total_scores" class="text-center" >
                            <span v-if="data.i_total_scores[index+1].x_nai_total  > 0">
                                {{  data.i_total_scores[index+1].x_nai_total  }}
                            </span>  
                        </td>
                    </tr>
                    <tr class="text-center row-total">
                        <td colspan="3"></td>
                            <td v-if="data.grand_totals" class="text-center bg-gray-300"  v-for="total in data.grand_totals[index+1]">
                            <span v-if="total > 0">
                                {{ total }} 
                            </span>
                        </td>        
                        <td v-if="data.i_trp_totals" class="text-center bg-gray-200" >
                            <span v-if="data.i_trp_totals[index+1].total_raw_points  > 0">
                                {{  data.i_trp_totals[index+1].total_raw_points  }} 
                            </span>  
                        </td>  
                        <td v-if="data.i_total_scores" class="text-center bg-gray-200" >
                            <span v-if="data.i_total_scores[index+1].x_i_total_score  > 0">
                                {{  data.i_total_scores[index+1].x_i_total_score  }} 
                            </span>  
                        </td>             
                    </tr>
            </template>                 

            <!-- totals -->
            <tr>
                <td></td>
            </tr>

                <tr class="text-center bg-blue-200">
                <td colspan="3"></td>
                <th >OCT</th>
                <th >NOV</th>
                <th >DEC</th>
                <td colspan="2">AVERAGE(%)</td>

            </tr>

            <tr>
                <td colspan="3" class="text-right">% of Promoters:</td>
                <td class="text-center" >
                    <span v-if="data.first_month_percentage_promoters  > 0">
                        {{  data.first_month_percentage_promoters  }}
                    </span>  
                </td>  
                <td  class="text-center " >
                    <span v-if="data.second_month_percentage_promoters  > 0">
                        {{  data.second_month_percentage_promoters  }}
                    </span>  
                </td>
                <td  class="text-center" >
                    <span v-if="data.third_month_percentage_promoters  > 0">
                        {{  data.third_month_percentage_promoters  }}
                    </span>  
                </td>
                <td  colspan="2"  class="text-center">
                    <span v-if="data.average_percentage_promoters  > 0">
                        {{  data.average_percentage_promoters  }}
                    </span>  
                </td>

            </tr>
            <tr>
                <td colspan="3" class="text-right">% of Detractors:</td>
                <td  class="text-center" >
                    <span v-if="data.first_month_percentage_detractors  > 0">
                        {{  data.first_month_percentage_detractors  }}
                    </span>  
                </td>  
                <td  class="text-center " >
                    <span v-if="data.second_month_percentage_detractors  > 0">
                        {{  data.second_month_percentage_detractors  }}
                    </span>  
                </td>
                <td  class="text-center" >
                    <span v-if="data.third_month_percentage_detractors  > 0">
                        {{  data.third_month_percentage_detractors  }}
                    </span>  
                </td>
                <td  colspan="2"  class="text-center">
                    <span v-if="data.average_percentage_detractors > 0">
                        {{  data.average_percentage_detractors  }}
                    </span>  
                </td>

            </tr>
            <tr>
                <td colspan="3" class="text-right">Net Promoter Score:</td>
                <td class="text-center" >
                    <span v-if="data.first_month_net_promoter_score > 0">
                        {{  data.first_month_net_promoter_score  }}
                    </span> 
                </td>  
                <td class="text-center " >
                    <span v-if="data.second_month_net_promoter_score > 0">
                        {{  data.second_month_net_promoter_score  }}
                    </span> 
                </td>
                <td class="text-center" >
                    <span v-if="data.third_month_net_promoter_score > 0">
                        {{  data.third_month_net_promoter_score  }}
                    </span> 

                </td>
                <td colspan="2"  class="text-center">
                    <span v-if="data.ave_net_promoter_score > 0">
                        {{  data.ave_net_promoter_score  }}
                    </span> 
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Customer Satisfaction Index (CSI):</td>
                <td class="text-center" >
                    <span v-if="data.first_month_csi > 0" >
                        {{  data.first_month_csi  }}
                    </span> 
                </td>
                <td class="text-center">
                    <span v-if="data.second_month_csi > 0" >
                        {{  data.second_month_csi  }}
                    </span>
                </td>
                <td class="text-center">
                    <span v-if="data.third_month_csi > 0" >
                        {{  data.third_month_csi  }}
                    </span>
                </td>
                <td  colspan="2" class="text-center">
                    <span v-if="data.csi > 0" >
                        {{  data.csi  }}
                    </span>
                </td>
            </tr> 
            <tr>
                <td colspan="8"></td>
                
            </tr>
            <tr class="bg-blue-200">
                <td colspan="3" class="text-right" style="font-weight:bold">Customer Satisfaction (CSAT) Score Rating:</td>
                <td colspan="5" class="text-center" style="font-weight:bold">
                    <span v-if="data.customer_satisfaction_rating > 0">
                        {{ data.customer_satisfaction_rating }}%
                    </span>
                </td>
            </tr>
        </table>
    </div>

    </div>

    <div class="quarter-preview-bottom">
        <div class="quarter-preview-bottom-grid">
            <div class="bottom-pie">
                <div class="quarter-preview-heading">SATISFACTION RATING</div>
                <div class="pie-circle" :style="{ background: pieChart.background }"></div>
                <table class="pie-legend-table">
                    <tr v-for="item in pieChart.legend" :key="item.label">
                        <td>
                            <span class="legend-dot" :style="{ backgroundColor: item.color }"></span>
                            {{ item.label }}
                        </td>
                        <td class="text-center">{{ item.pct }}%</td>
                    </tr>
                </table>
            </div>

            <div class="bottom-comments">
                <div class="quarter-preview-heading">
                    COMMENTS/COMPLAINTS
                </div>
                <div class="comment-filters">
                    <button type="button" class="count-badge count-badge-neutral" :class="{ 'is-active': commentFilter === 'all' }" @click="commentFilter = 'all'">
                        All {{ (data.comments || []).length }}
                    </button>
                    <button type="button" class="count-badge count-badge-primary" :class="{ 'is-active': commentFilter === 'comments' }" @click="commentFilter = 'comments'">
                        {{ data.total_comments || 0 }} comments
                    </button>
                    <button type="button" class="count-badge count-badge-danger" :class="{ 'is-active': commentFilter === 'complaints' }" @click="commentFilter = 'complaints'">
                        {{ data.total_complaints || 0 }} complaints
                    </button>
                </div>
                <div class="comments-list">
                    <div v-if="filteredComments.length > 0" v-for="(comment, index) in filteredComments" :key="index" class="comment-item">
                        <span class="comment-index">{{ index + 1 }}</span>
                        <span class="comment-body">
                            {{ comment.text }}
                            <span v-if="comment.is_complaint" class="comment-tag comment-tag-complaint">Complaint</span>
                            <span v-else class="comment-tag comment-tag-comment">Comment</span>
                        </span>
                    </div>
                    <div v-else class="comment-empty">No {{ commentFilter === 'all' ? 'comments or complaints' : commentFilter }} submitted.</div>
                </div>
            </div>

            <div class="bottom-assessment">
                <div class="quarter-preview-heading">ASSESSMENT</div>
                <div class="assessment-text">
                    The <span>{{ data.unit.data?.[0]?.unit_name }}</span> Unit for the <span style="text-transform:lowercase">{{ form.selected_quarter }}</span> of <span>{{ form.selected_year }}</span>
                    had a total of <span>{{ data.total_respondents }}</span> respondents who filled out and rated the Customer Satisfaction Feedback.
                    <span>{{ data.total_vss_respondents }}</span> (out of <span>{{ data.total_respondents }}</span>, or <span>{{ data.percentage_vss_respondents }}</span>%) of the respondents rated the CSF as either very satisfied (VS) or satisfied (S),
                    which resulted in an overall average Customer Satisfaction Index (CSI) of <span>{{ data.csi }}</span>%,
                    a Net Promoter Score of {{ data.ave_net_promoter_score }}%, and an average Likert Scale Rating of <span>{{ data.lsr_average }}</span>, which translates to "very satisfied" for
                    the <span style="text-transform:lowercase">{{ form.selected_quarter }}</span> of <span>{{ form.selected_year }}</span>.

                    The <span>{{ data.unit.data?.[0]?.unit_name }}</span> unit's Customer Satisfaction Survey resulted in an Overall Customer Satisfaction Score Rating of <span>{{ data.customer_satisfaction_rating }}</span>%
                    for the <span style="text-transform:lowercase">{{ form.selected_quarter }}</span> of <span>{{ form.selected_year }}</span>, which <span v-if="data.customer_satisfaction_rating < 95">did not achieve</span><span v-else>achieved</span> its quality objective of at least 95% of customers being satisfied with the S&T services.
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.v-card) {
    border: 1px solid #d8e5f5;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(22, 54, 93, 0.08);
}

:deep(.v-card-title) {
    background: linear-gradient(90deg, #1b365d, #2a568f) !important;
    color: #fff !important;
    font-weight: 700 !important;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 0;
}

tr, th, td {
    border: 1px solid #b8cbe2 !important;
    padding: 4px;
    vertical-align: middle;
}

.bg-blue-200 {
    background-color: #e6f1ff !important;
}

.row-vs { background-color: #eafaf0; }
.row-s { background-color: #f4fbf6; }
.row-n { background-color: #ffffff; }
.row-d { background-color: #fff8ec; }
.row-vd { background-color: #fdf0ef; }
.row-total td {
    background-color: #dde4f2 !important;
    font-weight: 700;
}

.quarter-preview-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    align-items: start;
    margin-bottom: 1rem;
}
@media (max-width: 992px) {
    .quarter-preview-grid {
        grid-template-columns: 1fr;
    }
}
.quarter-preview-bottom {
    border: 1px solid #d8e5f5;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(22, 54, 93, 0.08);
    background: #fff;
    margin-bottom: 1rem;
}
.quarter-preview-bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1.4fr 1.6fr;
    gap: 16px;
    padding: 10px;
}
@media (max-width: 992px) {
    .quarter-preview-bottom-grid {
        grid-template-columns: 1fr;
    }
}
.bottom-pie, .bottom-comments, .bottom-assessment {
    min-width: 0;
    display: flex;
    flex-direction: column;
    border: 1px solid #d8e5f5;
    border-radius: 8px;
    padding: 8px;
    background: #fff;
}
.quarter-preview-col {
    border: 1px solid #d8e5f5;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(22, 54, 93, 0.08);
    background: #fff;
    padding: 10px;
}
.cc-card {
    margin-bottom: 1rem;
}
.quarter-preview-heading {
    background: linear-gradient(90deg, #1b365d, #2a568f);
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    padding: 6px 10px;
    margin: 0 0 8px 0;
    border-radius: 6px;
}
.quarter-preview-col > .quarter-preview-heading {
    margin: -10px -10px 8px -10px;
    border-radius: 0;
}
.quarter-preview-side {
    font-size: 13px;
}
.pie-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    margin: 10px auto;
    border: 1px solid #b8cbe2;
}
.pie-legend-table {
    width: 100%;
    border-collapse: collapse;
}
.pie-legend-table td {
    border: 1px solid #b8cbe2;
    padding: 4px 6px;
    font-size: 12px;
}
.legend-dot {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    margin-right: 5px;
}
.count-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    border-radius: 999px;
    padding: 2px 8px;
    margin-left: 6px;
    border: none;
    cursor: pointer;
    opacity: 0.55;
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.count-badge.is-active {
    opacity: 1;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.12) inset;
}
.count-badge:hover {
    opacity: 0.85;
}
.count-badge-neutral {
    background: #64748b;
}
.count-badge-primary {
    background: #2a568f;
}
.count-badge-danger {
    background: #c0392b;
}
.comment-filters {
    margin-bottom: 6px;
}
.comments-list {
    max-height: 220px;
    overflow-y: auto;
    margin-top: 6px;
}
.comment-item {
    display: flex;
    gap: 6px;
    padding: 6px 0;
    border-bottom: 1px solid #eef2f7;
    font-size: 12px;
}
.comment-item:last-child {
    border-bottom: none;
}
.comment-index {
    flex: 0 0 auto;
    width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    border-radius: 50%;
    background: #eef2f7;
    color: #2a568f;
    font-weight: 700;
    font-size: 10px;
}
.comment-body {
    flex: 1 1 auto;
}
.comment-tag {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-radius: 4px;
    padding: 1px 5px;
    margin-left: 6px;
    vertical-align: middle;
}
.comment-tag-complaint {
    background: #fdecea;
    color: #c0392b;
}
.comment-tag-comment {
    background: #eaf3fc;
    color: #2a568f;
}
.comment-empty {
    font-size: 12px;
    color: #6c8098;
    font-style: italic;
    margin-top: 6px;
}
.assessment-text {
    text-align: justify;
    font-size: 12px;
    margin-top: 6px;
}
</style>
