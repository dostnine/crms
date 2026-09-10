<script setup>
    import { computed } from 'vue';
    const props = defineProps({
        data: {
            type: Object,
        },
        form: {
            type: Object,
        },
    });
        const calculate = (ndvd_grand_total_score ,grand_total_score) => {
            const result = (ndvd_grand_total_score / grand_total_score) * 100;
            return result.toFixed(2);
        };

        const piePalette = ['#4472c4', '#70ad47', '#ed7d31'];
        const pieChart = computed(() => {
            const grand = Number(props.data.grand_total_score || 0);
            const vs = Number(props.data.vs_grand_total_score || 0);
            const s = Number(props.data.s_grand_total_score || 0);
            const other = Number(props.data.ndvd_grand_total_score || 0);
            const total = vs + s + other;
            if (!grand || !total) {
                return {
                    background: `conic-gradient(${piePalette[2]} 0% 100%)`,
                    legend: [
                        { label: 'Very Satisfied', color: piePalette[0], pct: '0.00' },
                        { label: 'Satisfied', color: piePalette[1], pct: '0.00' },
                        { label: 'Other Responses', color: piePalette[2], pct: '0.00' },
                    ],
                };
            }
            const counts = [vs, s, other];
            const percentages = counts.map((v) => (v / total) * 100);
            let offset = 0;
            const slices = percentages.map((pct, idx) => {
                const start = offset;
                offset += pct;
                return `${piePalette[idx]} ${start.toFixed(2)}% ${offset.toFixed(2)}%`;
            });
            return {
                background: `conic-gradient(${slices.join(', ')})`,
                legend: [
                    { label: 'Very Satisfied', color: piePalette[0], pct: percentages[0].toFixed(2) },
                    { label: 'Satisfied', color: piePalette[1], pct: percentages[1].toFixed(2) },
                    { label: 'Other Responses', color: piePalette[2], pct: percentages[2].toFixed(2) },
                ],
            };
        });
</script>
<template>
    <div class="mb-3 print-id print landscape-report">
        <h5 style="text-transform:capitalize; text-align:center; margin-top: -8px">
           <div style="display:flex;justify-content:center;align-items:center;">
                    <img
                    data-aos="zoom-in"
                    data-aos-duration="500"
                    data-aos-delay="500"
                    style="width:40px; height:40px; margin-right:10px"
                    src="../../../../../../public/images/dost-logo.jpg"
                    alt="..">
                    <span style="text-align:center">
                        CUSTOMER SATISFACTION FEEDBACK <br>SUMMARY REPORT FOR
                        <u><span>{{ form.selected_quarter }}</span>  {{ form.selected_year }}</u>
                    </span>
                </div>
        </h5><br>
        <div style="display: flex; justify-content: space-between; margin-top:-26px">
            <div style="font-size: 12px;">
                Services : <u>{{ data.service.services_name }}</u>

            </div>
            <div style="font-size: 12px">
                Services Unit : <u v-if="data.unit.data.length > 0">{{ data.unit.data[0].unit_name }}</u><br>
                                <u v-if="form.client_type" style="margin-left: 75px">{{ form.client_type }}</u> <br v-if="form.client_type">
                                <u v-if="form.selected_unit_psto" style="margin-left: 75px">{{ form.selected_unit_psto.psto_name }}</u><br v-if="form.selected_unit_psto.length > 0">
                                <u v-if="data.sub_unit">{{ form.selected_sub_unit?.sub_unit_name }}</u>
                                <u v-if="form.sub_unit_type" style="margin-left: 5px">{{ form.sub_unit_type.type_name }}</u>
                                <u v-if="form.selected_sub_unit_psto" style="margin-left: 5px">{{ form.selected_sub_unit_psto.psto_name }}</u>
            </div>

        </div>

        <div style="margin-top: -5px; margin-bottom: 20px;text-align: center" v-if="data.cc_data">
                <div style="font-size: 12px;margin-right:20px; margin-bottom:15px; font-weight: bold;text-align: left">
                    PART I: CITIZEN'S CHARTER(CC)
                </div>
                <table style="font-size: 13px;width:100%; border: 1px solid #333; border-collapse: collapse;  padding: 3px">
                    <tr>
                        <th></th>
                        <th></th>
                        <th style="border: 1px solid #333;font-size: 12px;">Number of Respondents who select the option</th>
                    </tr>
                    <tr class="bg-blue-200">
                        <th>CC1</th>
                        <th colspan="2" style="text-align: left">Which of the following best describes your awareness of a CC?</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td style="text-align: left">I know what a CC is and I saw this office's CC</td>
                        <td>
                            <span>
                                {{data.cc_data.cc1_data.cc1_ans1}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td style="text-align: left">I know what a CC is but I did NOT see this office's CC</td>
                        <td>
                            <span>
                                {{data.cc_data.cc1_data.cc1_ans2}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td style="text-align: left">I learned the CC when I saw this office's CC</td>
                        <td>
                            <span>
                                {{data.cc_data.cc1_data.cc1_ans3}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td style="text-align: left">I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)</td>
                        <td>
                            <span>
                                {{data.cc_data.cc1_data.cc1_ans4}}
                            </span>
                        </td>
                    </tr>
                    <tr class="bg-blue-200" >
                        <th >CC2</th>
                        <th colspan="2" style="text-align: left">If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td style="text-align: left">Easy to see</td>
                        <td>
                            <span>
                                {{data.cc_data.cc2_data.cc2_ans1}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td style="text-align: left">Somewhat easy to see</td>
                        <td>
                            <span>
                                {{data.cc_data.cc2_data.cc2_ans2}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td style="text-align: left">Difficult to see</td>
                        <td>
                            <span>
                                {{data.cc_data.cc2_data.cc2_ans3}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td style="text-align: left">Not visible at all</td>
                        <td>
                            <span>
                                {{data.cc_data.cc2_data.cc2_ans4}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td style="text-align: left">N/A</td>
                        <td>
                            <span>
                                {{data.cc_data.cc2_data.cc2_ans5}}
                            </span>
                        </td>
                    </tr>
                    <tr class="bg-blue-200">
                        <th >CC3</th>
                        <th colspan="2" style="text-align: left">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td style="text-align: left">Helped Very Much</td>
                        <td>
                            <span>
                                {{data.cc_data.cc3_data.cc3_ans1}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td style="text-align: left">Somewhat helped</td>
                        <td>
                            <span>
                                {{data.cc_data.cc3_data.cc3_ans2}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td style="text-align: left">Did not help</td>
                        <td>
                            <span>
                                {{data.cc_data.cc3_data.cc3_ans3}}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td style="text-align: left">N/A</td>
                        <td>
                            <span>
                                {{data.cc_data.cc3_data.cc3_ans4}}
                            </span>
                        </td>
                    </tr>
                </table>
        </div>

        <!-- Landscape three-column summary: service quality | importance | satisfaction chart -->
        <div class="quarter-grid" style="page-break-before: always">
            <div class="quarter-col">
                <div style="font-size: 12px;margin-bottom:5px; font-weight: bold" >PART I: CUSTOMER RATING OF SERVICE QUALITY</div>
                <table style="font-size: 10px;width:100%; border: 1px solid #333; border-collapse: collapse;  padding: 2px">
                    <tr class="text-left font-bold text-center bg-blue-200">
                        <th colspan="3">Service Quality Attributes</th>
                        <th>OCT</th>
                        <th>NOV</th>
                        <th>DEC</th>
                        <th>Total Raw Points</th>
                        <th>Total Score</th>
                        <th >Likert Scale Rating</th>
                    </tr>

                    <template v-for="(dimension, index) in data.dimensions" :key="dimension.id" class="border border-solid hover:bg-gray-100 focus-within:bg-gray-100">
                            <tr>
                               <td style="text-align: left; padding: 3px" rowspan="7">
                                 [{{ index + 1 }}] {{ dimension.name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">5</td>
                                <td>Very Satisfied</td>
                                <td v-if="data.vs_totals" class="text-center"  v-for="total in data.vs_totals[index+1]">
                                    <span>
                                        {{total}}
                                    </span>
                                </td>
                                <td v-if="data.trp_totals" class="text-center" >
                                    <span v-if="data.trp_totals[index+1].vs_total_raw_points > 0">
                                        {{ data.trp_totals[index+1].vs_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.p1_total_scores" class="text-center" >
                                    <span v-if="data.p1_total_scores[index+1].x_vs_total > 0">
                                        {{data.p1_total_scores[index+1].x_vs_total}}
                                    </span>
                                </td>
                                <td v-if="data.lsr_totals" class="text-center" >
                                    <span v-if="data.lsr_totals[index+1].vs_lsr_total > 0">
                                        {{ data.lsr_totals[index+1].vs_lsr_total }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">4</td>
                                <td>Satisfied</td>
                                <td v-if="data.s_totals" class="text-center"  v-for="total in data.s_totals[index+1]">
                                    <span v-if="total > 0">
                                        {{total}}
                                    </span>
                                </td>
                                  <td v-if="data.trp_totals"  class="text-center" >
                                    <span v-if="data.trp_totals[index+1].s_total_raw_points > 0">
                                        {{ data.trp_totals[index+1].s_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.p1_total_scores" class="text-center" >
                                    <span v-if="data.p1_total_scores[index+1].x_s_total > 0">
                                        {{data.p1_total_scores[index+1].x_s_total}}
                                    </span>
                                </td>
                                <td v-if="data.lsr_totals" class="text-center" >
                                    <span v-if="data.lsr_totals[index+1].s_lsr_total > 0">
                                        {{data.lsr_totals[index+1].s_lsr_total}}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>Neither Satisfied nor Dissatisfied</td>
                                <td v-if="data.n_totals" class="text-center"  v-for="total in data.n_totals[index+1]">
                                    <span>
                                        {{total}}
                                    </span>
                                </td>
                                <td v-if="data.trp_totals" class="text-center" >
                                    <span>
                                        {{data.trp_totals[index+1].n_total_raw_points}}
                                    </span>
                                </td>
                                <td v-if="data.p1_total_scores" class="text-center" >
                                    <span>
                                        {{data.p1_total_scores[index+1].x_n_total}}
                                    </span>
                                </td>
                                <td v-if="data.lsr_totals" class="text-center" >
                                    <span>
                                        {{data.lsr_totals[index+1].n_lsr_total}}
                                    </span>
                                </td>
                            </tr>
                            <tr>
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
                            <tr>
                                <td class="text-center">1</td>
                                 <td>Very Dissatisfied</td>
                                <td v-if="data.vd_totals" class="text-center"  v-for="total in data.vd_totals[index+1]">
                                    <span>
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.trp_totals" class="text-center" >
                                    <span>
                                        {{ data.trp_totals[index+1].vd_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.p1_total_scores" class="text-center" >
                                    <span>
                                        {{ data.p1_total_scores[index+1].x_vd_total }}
                                    </span>
                                </td>
                                <td v-if="data.lsr_totals" class="text-center" >
                                    <span>
                                        {{ data.lsr_totals[index+1].vd_lsr_total }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center" colspan="2"></td>
                                <td v-if="data.grand_totals" class="text-center bg-gray-300"  v-for="total in data.grand_totals[index+1]">
                                    <span v-if="total > 0">
                                        {{total}}
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
                        <td colspan="3" class="text-right">Total No. of Very Satisfied (VS) Responses:</td>
                        <td class="text-center">
                            <span>
                                {{ data.first_month_vs_grand_total }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
                                {{ data.second_month_vs_grand_total  }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
                                {{ data.third_month_vs_grand_total   }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
                                {{ data.vs_grand_total_raw_points   }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
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
                            <span>
                                {{ data.first_month_ndvd_grand_total   }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
                                {{ data.second_month_ndvd_grand_total   }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
                                {{ data.third_month_ndvd_grand_total   }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
                                {{ data.ndvd_grand_total_raw_points   }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span>
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
                        <td colspan="3" class="text-right">Total No. of All Responses::</td>
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
                            <span>
                                {{ data.total_respondents }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="8" class="text-right">Total No. of Respondents/Customers who rated VS or S:</td>
                        <td class="text-center">
                            <span>
                                {{ data.total_vss_respondents }}
                            </span>
                        </td>
                    </tr>
                      <tr>
                        <td colspan="8" class="text-right">Percentage No. of Respondents/Customers who rated VS or S:</td>
                        <td class="text-center">
                            <span>
                                {{ data.percentage_vss_respondents }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="8" class="text-right"> Likert Scale Rating (Average):</td>
                        <td class="text-center">
                            <span>
                                {{ data.lsr_average }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="quarter-col">
                <div style="font-size: 12px;margin-bottom:5px; font-weight: bold">PART II: IMPORTANCE OF THESE ATTRIBUTES TO THE CUSTOMERS</div>
                 <table style="font-size: 10px;width:100%; border: 1px solid #333; border-collapse: collapse;  padding: 2px">
                    <tr class="text-left font-bold text-center bg-blue-200">
                        <th  colspan="3">Importance Service Quality Attributes</th>
                        <th>OCT</th>
                        <th>NOV</th>
                        <th>DEC</th>
                        <th >Total Raw Points</th>
                        <th  colspan="2">Total Score</th>
                    </tr>

                    <template v-for="(dimension, index) in data.dimensions" :key="dimension.id" class="border border-solid hover:bg-gray-100 focus-within:bg-gray-100">
                            <tr>
                               <td style="text-align: left; padding: 3px" rowspan="6">
                                 [{{ index + 1 }}] {{ dimension.name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">5</td>
                                <td>Very Important</td>
                                <td v-if="data.vi_totals" class="text-center"  v-for="total in data.vi_totals[index+1]">
                                    <span v-if="total > 0 ">
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.i_trp_totals" class="text-center" >
                                    <span v-if="total > 0 ">
                                        {{ data.i_trp_totals[index+1].vi_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.i_total_scores" class="text-center" >
                                    <span v-if="total > 0 ">
                                        {{ data.i_total_scores[index+1].x_vi_total }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">4</td>
                                 <td>Important</td>
                                <td v-if="data.i_totals" class="text-center"  v-for="total in data.i_totals[index+1]">
                                    <span>
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.i_trp_totals" class="text-center" >
                                    <span>
                                        {{ data.i_trp_totals[index+1].i_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.i_total_scores" class="text-center" >
                                    <span>
                                        {{ data.i_total_scores[index+1].x_i_total }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>Moderately Important</td>
                                <td v-if="data.mi_totals" class="text-center"  v-for="total in data.mi_totals[index+1]">
                                    <span v-if="total > 0 ">
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.i_trp_totals" class="text-center" >
                                    <span v-if="data.i_trp_totals[index+1].mi_total_raw_points > 0 ">
                                        {{ data.i_trp_totals[index+1].mi_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.i_total_scores" class="text-center" >
                                    <span v-if="data.i_total_scores[index+1].x_mi_total > 0 ">
                                        {{ data.i_total_scores[index+1].x_mi_total }}
                                    </span>
                                </td>

                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                 <td>Slightly Important</td>
                                <td v-if="data.si_totals" class="text-center"  v-for="total in data.si_totals[index+1]">
                                    <span>
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.i_trp_totals" class="text-center" >
                                    <span>
                                        {{ data.i_trp_totals[index+1].si_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.i_total_scores" class="text-center" >
                                    <span>
                                        {{ data.i_total_scores[index+1].x_si_total }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center">1</td>
                                 <td>Not all Important</td>
                                <td v-if="data.nai_totals" class="text-center"  v-for="total in data.nai_totals[index+1]">
                                    <span v-if="total > 0 ">
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.i_trp_totals" class="text-center" >
                                    <span v-if="data.i_trp_totals[index+1].nai_total_raw_points > 0 ">
                                        {{ data.i_trp_totals[index+1].nai_total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.i_total_scores" class="text-center" >
                                    <span v-if="data.i_total_scores[index+1].x_nai_total > 0 ">
                                        {{ data.i_total_scores[index+1].x_nai_total }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td colspan="3"></td>
                                 <td v-if="data.grand_totals" class="text-center bg-gray-300"  v-for="total in data.grand_totals[index+1]">
                                    <span>
                                        {{ total }}
                                    </span>
                                </td>
                                <td v-if="data.i_trp_totals" class="text-center bg-gray-200" >
                                    <span>
                                        {{ data.i_trp_totals[index+1].total_raw_points }}
                                    </span>
                                </td>
                                <td v-if="data.i_total_scores" class="text-center bg-gray-200" >
                                    <span>
                                        {{ data.i_total_scores[index+1].x_i_total_score }}
                                    </span>
                                </td>
                            </tr>
                    </template>

                    <!-- totals -->
                     <tr class="text-center bg-blue-200">
                        <td colspan="3"></td>
                        <th>OCT</th>
                        <th>NOV</th>
                        <th>DEC</th>
                        <td colspan="2">AVERAGE(%)</td>

                    </tr>

                    <tr>
                        <td colspan="3" class="text-right">% of Promoters:</td>
                         <td class="text-center" >
                            <span v-if="data.first_month_percentage_promoter > 0 ">
                                {{ data.first_month_percentage_promoter }}
                            </span>
                        </td>
                        <td class="text-center " >
                            <span v-if="data.second_month_percentage_promoters > 0 ">
                                {{ data.second_month_percentage_promoters }}
                            </span>
                        </td>
                        <td class="text-center" >
                            <span v-if="data.third_month_percentage_promoters > 0 ">
                                {{ data.third_month_percentage_promoters }}
                            </span>
                        </td>
                        <td colspan="2"  class="text-center">
                            <span v-if="data.average_percentage_promoters > 0 ">
                                {{ data.average_percentage_promoters }}
                            </span>
                        </td>

                    </tr>
                    <tr>
                        <td colspan="3" class="text-right">% of Detractors:</td>
                        <td class="text-center" >
                            <span>
                                {{ data.first_month_percentage_detractors }}
                            </span>
                        </td>
                        <td class="text-center " >
                            <span>
                                {{ data.second_month_percentage_detractors }}
                            </span>
                        </td>
                        <td class="text-center" >
                            <span>
                                {{ data.third_month_percentage_detractors }}
                            </span>
                        </td>
                        <td colspan="2"  class="text-center">
                            <span>
                                {{ data.average_percentage_detractors }}
                            </span>
                        </td>

                    </tr>
                    <tr>
                        <td colspan="3" class="text-right">Net Promoter Score:</td>
                        <td class="text-center" >
                            <span v-if="data.first_month_net_promoter_score > 0 ">
                                {{ data.first_month_net_promoter_score }}
                            </span>
                        </td>
                        <td class="text-center " >
                            <span v-if="data.second_month_percentage_detractors > 0 ">
                                {{ data.second_month_percentage_detractors }}
                            </span>
                        </td>
                        <td class="text-center" >
                            <span v-if="data.third_month_net_promoter_score > 0 ">
                                {{ data.third_month_net_promoter_score }}
                            </span>
                        </td>
                        <td  colspan="2"  class="text-center">
                            <span v-if="data.ave_net_promoter_score > 0 ">
                                {{ data.ave_net_promoter_score }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right">Customer Satisfaction Index (CSI):</td>
                        <td class="text-center">
                            <span>
                                {{ data.first_month_csi }}
                            </span>
                        </td>
                        <td class="text-center" >
                            <span>
                                {{ data.second_month_csi }}
                            </span>
                        </td>
                        <td class="text-center" >
                            <span>
                                {{ data.third_month_csi }}
                            </span>
                        </td>
                        <td class="text-center" colspan="2" v-if="data.csi">
                            <span>
                                {{ data.csi }}
                            </span>
                        </td>
                    </tr>
                   <tr class="bg-blue-200">
                        <td colspan="3" class="text-right" style="font-weight:bold">Customer Satisfaction (CSAT) Score Rating:</td>
                        <td colspan="5" class="text-center" style="font-weight:bold">
                            <span v-if="data.customer_satisfaction_rating > 0 ">
                                {{ data.customer_satisfaction_rating }}%
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="quarter-col quarter-col-side">
                <div style="font-size: 12px;margin-bottom:5px; font-weight: bold">SATISFACTION RATING:</div>
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

                <div style="font-size: 11px; margin-top: 12px; font-weight: bold">COMMENTS/COMPLAINTS:</div>
                <div style="font-size: 10px; text-align: left">
                    <span v-if="data.comments && data.comments.length > 0">
                        <div v-for="(comment, index) in data.comments" :key="index">[{{ index + 1 }}] {{ comment.text }}</div>
                    </span>
                    <span v-else>None.</span>
                </div>

                <div style="font-size: 11px; margin-top: 12px; font-weight: bold">ASSESSMENT:</div>
                <div style="text-align: justify; font-size: 10px">
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
    .print {
        display: none;
    }
    @media print {
        .print-id {
            display: block;
        }

    }
    .quarter-grid {
        display: grid;
        grid-template-columns: 2fr 2fr 1fr;
        gap: 10px;
        align-items: start;
    }
    .quarter-col table th,
    .quarter-col table td {
        padding: 2px 3px;
    }
    .quarter-col-side {
        font-size: 10px;
    }
    .pie-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin: 4px auto 8px auto;
        border: 1px solid #333;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .pie-legend-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }
    .pie-legend-table td {
        border: 1px solid #333;
        padding: 2px 4px;
    }
    .legend-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 4px;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
</style>
