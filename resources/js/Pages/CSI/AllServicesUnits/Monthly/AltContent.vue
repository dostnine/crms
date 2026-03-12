<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: {
    type: Object,
  },
  form: {
    type: Object,
  },
});

const reportSummaryLabel = computed(() => {
  if (props.form?.csi_type === 'By Quarter') {
    return 'FOR THE ' + (props.form?.selected_quarter || '');
  }
  if (props.form?.csi_type === 'By Year/Annual') {
    return 'FOR THE YEAR ' + (props.form?.selected_year || '');
  }
  return 'FOR THE MONTH OF ' + (props.form?.selected_month || '') + ' ' + (props.form?.selected_year || '');
});

const assessmentPeriodText = computed(() => {
  if (props.form?.csi_type === 'By Quarter') {
    return `${props.form?.selected_quarter || ''} ${props.form?.selected_year || ''}`.trim();
  }
  if (props.form?.csi_type === 'By Year/Annual') {
    return props.form?.selected_year || '';
  }
  return `${props.form?.selected_month || ''} ${props.form?.selected_year || ''}`.trim();
});

const regionTitle = computed(() => {
  const region = props.data?.region || {};
  const regionLabel = region.short_name || region.code || region.name;
  if (!regionLabel) return 'DOST';
  return `DOST ${regionLabel}`;
});

// Function to check if there's any CC data
const hasAnyCCData = () => {
  if (!props.data?.cc_data) return false;
  const cc1 = props.data.cc_data.cc1_data;
  const cc2 = props.data.cc_data.cc2_data;
  const cc3 = props.data.cc_data.cc3_data;

  return (
    (cc1?.cc1_ans1 > 0) || (cc1?.cc1_ans2 > 0) || (cc1?.cc1_ans3 > 0) || (cc1?.cc1_ans4 > 0) ||
    (cc2?.cc2_ans1 > 0) || (cc2?.cc2_ans2 > 0) || (cc2?.cc2_ans3 > 0) || (cc2?.cc2_ans4 > 0) || (cc2?.cc2_ans5 > 0) ||
    (cc3?.cc3_ans1 > 0) || (cc3?.cc3_ans2 > 0) || (cc3?.cc3_ans3 > 0) || (cc3?.cc3_ans4 > 0)
  );
};

const getPstosWithData = (serviceId, unitId, subUnitId) => {
  if (!props.data?.all_units_data?.units_data?.[serviceId]?.[unitId]?.sub_units_data?.[subUnitId]?.pstos_data) {
    return [];
  }
  const pstosData = props.data.all_units_data.units_data[serviceId][unitId].sub_units_data[subUnitId].pstos_data;
  return Object.entries(pstosData)
    .filter(([pstoId, psto]) => psto.total_respo > 0)
    .map(([pstoId, psto]) => ({ id: pstoId, ...psto }));
};

const getUnitPstosWithData = (serviceId, unitId) => {
  if (!props.data?.all_units_data?.units_data?.[serviceId]?.[unitId]?.unit_pstos_data) {
    return [];
  }
  const unitPstosData = props.data.all_units_data.units_data[serviceId][unitId].unit_pstos_data;
  return Object.entries(unitPstosData)
    .filter(([pstoId, psto]) => psto.total_respo > 0)
    .map(([pstoId, psto]) => ({ id: pstoId, ...psto }));
};

const shouldShowServiceUnit = (serviceId, unit) => {
  const totalRespo = props.data?.all_units_data?.units_data?.[serviceId]?.[unit.id]?.total_respo || 0;
  if (unit.unit_name === 'Research and Development Support' && totalRespo <= 0) {
    return false;
  }
  return totalRespo > 0 || (unit.sub_units && unit.sub_units.length > 0);
};

const getRowVssCount = (row) => {
  return Number(row?.strongly_agree_count || 0) + Number(row?.agree_count || 0);
};

const getRowPercentage = (row) => {
  const totalRespo = Number(row?.total_respo || 0);
  const naCount = Number(row?.na_count || 0);
  const denom = Math.max(totalRespo - naCount, 0);
  const vssCount = getRowVssCount(row);
  return denom > 0 ? (vssCount / denom) * 100 : 0;
};

const getServiceTotals = (serviceId) => {
  const unitsData = props.data?.all_units_data?.units_data?.[serviceId] || {};
  const totals = {
    total_respo: 0,
    strongly_agree_count: 0,
    agree_count: 0,
    na_count: 0,
  };

  Object.values(unitsData).forEach((unit) => {
    if (!unit) return;
    totals.total_respo += Number(unit.total_respo || 0);
    totals.strongly_agree_count += Number(unit.strongly_agree_count || 0);
    totals.agree_count += Number(unit.agree_count || 0);
    totals.na_count += Number(unit.na_count || 0);
  });

  return totals;
};

const formatPercentOrDash = (value) => {
  const num = Number(value);
  if (!Number.isFinite(num) || num === 0) return '-';
  return `${num.toFixed(2)}%`;
};

const formatNumberOrDash = (value) => {
  const num = Number(value);
  if (!Number.isFinite(num) || num === 0) return '-';
  return num.toFixed(2);
};
</script>

<template>
  <div class="alt-report">
    <div class="alt-header print-only">
      <h4 class="alt-title">{{ regionTitle }} Customer Satisfaction feedback</h4>
      <h5 class="alt-subtitle">{{ reportSummaryLabel }}</h5>
    </div>

    <!-- PART I: Citizen's Charter -->
    <v-card class="mb-3" v-if="hasAnyCCData()">
      <v-card-title class="bg-gray-500 text-white">
        PART I: CITIZEN'S CHARTER(CC)
      </v-card-title>
      <table style="width:100%; border: 1px solid #333; border-collapse: collapse; padding: 5px" class="text-center cc-table">
        <tr>
          <th></th>
          <th></th>
          <th>Number of Respondents</th>
          <th>Percentage</th>
        </tr>
        <tr class="bg-blue-200">
          <th>CC1</th>
          <th colspan="3" class="text-left">Which of the following best describes your awareness of a CC?</th>
        </tr>
        <tr>
          <td>1</td>
          <td class="text-left">I know what a CC is and I saw this office's CC</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans1 || 0 }}</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans1_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>2</td>
          <td class="text-left">I know what a CC is but I did NOT see this office's CC</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans2 || 0 }}</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans2_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>3</td>
          <td class="text-left">I learned the CC when I saw this office's CC</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans3 || 0 }}</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans3_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>4</td>
          <td class="text-left">I do not know what a CC is and I did not see one in this office. (Answer 'N/A' on CC2 and CC3)</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans4 || 0 }}</td>
          <td>{{ props.data.cc_data.cc1_data?.cc1_ans4_pct || 0 }}%</td>
        </tr>
        <tr class="bg-blue-200">
          <td></td>
          <td class="text-left"><strong>Total</strong></td>
          <td><strong>{{ props.data.cc_data.cc1_data?.cc1_total || 0 }}</strong></td>
          <td><strong>{{ (props.data.cc_data.cc1_data?.cc1_total || 0) > 0 ? '100%' : '0%' }}</strong></td>
        </tr>
        <tr class="bg-blue-200" >
          <th >CC2</th>
          <th colspan="3" class="text-left">If aware of CC (answered 1-3 in CC1), would say that the CC of this was...?</th>
        </tr>
        <tr>
          <td>1</td>
          <td class="text-left">Easy to see</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans1 || 0 }}</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans1_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>2</td>
          <td class="text-left">Somewhat easy to see</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans2 || 0 }}</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans2_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>3</td>
          <td class="text-left">Difficult to see</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans3 || 0 }}</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans3_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>4</td>
          <td class="text-left">Not visible at all</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans4 || 0 }}</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans4_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>5</td>
          <td class="text-left">N/A</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans5 || 0 }}</td>
          <td>{{ props.data.cc_data.cc2_data?.cc2_ans5_pct || 0 }}%</td>
        </tr>
        <tr class="bg-blue-200">
          <td></td>
          <td class="text-left"><strong>Total</strong></td>
          <td><strong>{{ props.data.cc_data.cc2_data?.cc2_total || 0 }}</strong></td>
          <td><strong>{{ (props.data.cc_data.cc2_data?.cc2_total || 0) > 0 ? '100%' : '0%' }}</strong></td>
        </tr>
        <tr class="bg-blue-200">
          <th >CC3</th>
          <th colspan="3" class="text-left">If aware of CC (answered 1-3 in CC1), how much did the CC help you in your transaction?</th>
        </tr>
        <tr>
          <td>1</td>
          <td class="text-left">Helped Very Much</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans1 || 0 }}</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans1_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>2</td>
          <td class="text-left">Somewhat helped</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans2 || 0 }}</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans2_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>3</td>
          <td class="text-left">Did not help</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans3 || 0 }}</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans3_pct || 0 }}%</td>
        </tr>
        <tr>
          <td>4</td>
          <td class="text-left">N/A</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans4 || 0 }}</td>
          <td>{{ props.data.cc_data.cc3_data?.cc3_ans4_pct || 0 }}%</td>
        </tr>
        <tr class="bg-blue-200">
          <td></td>
          <td class="text-left"><strong>Total</strong></td>
          <td><strong>{{ props.data.cc_data.cc3_data?.cc3_total || 0 }}</strong></td>
          <td><strong>{{ (props.data.cc_data.cc3_data?.cc3_total || 0) > 0 ? '100%' : '0%' }}</strong></td>
        </tr>
      </table>
    </v-card>

    <!-- PART II: Alternative Service Units Overview -->
    <div class="alt-section">
      <h5 class="alt-section-title">PART II: SERVICE UNITS OVERVIEW - {{ assessmentPeriodText }}</h5>
      <table class="alt-table">
        <thead>
          <tr>
            <th>Service Unit</th>
            <th>Total No. of Respondents</th>
            <th>Total No. of Respondents who rated VS and S</th>
            <th>Percentage of Respondents who rated VS and S</th>
            <th>Customer Satisfaction Index (CSI)</th>
            <th>Net Promoter Score</th>
            <th>Likert Scale Rating (Attribute Average)</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(service, index) in props.data?.services_units?.data || []" :key="index">
            <tr class="alt-service-row">
              <td colspan="7"><strong>{{ service.services_name }}</strong></td>
            </tr>
            <template v-for="(unit, unitIndex) in service.units || []" :key="unitIndex">
              <template v-if="shouldShowServiceUnit(service.id, unit)">
                <tr v-if="props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.total_respo > 0">
                  <td class="pl-5">{{ unit.unit_name }}</td>
                  <td class="text-center">{{ props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.total_respo || '-' }}</td>
                  <td class="text-center">
                    {{ getRowVssCount(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]) || '-' }}
                  </td>
                  <td class="text-center">
                    {{ getRowPercentage(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]).toFixed(2) }}%
                  </td>
                  <td class="text-center">
                    {{ getRowPercentage(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]).toFixed(2) }}%
                  </td>
                  <td class="text-center">
                    {{ formatPercentOrDash(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.nps) }}
                  </td>
                  <td class="text-center">
                    {{ formatNumberOrDash(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.lsr) }}
                  </td>
                </tr>

                <template v-if="getUnitPstosWithData(service.id, unit.id).length > 0">
                  <tr v-for="(unitPsto, unitPstoIndex) in getUnitPstosWithData(service.id, unit.id)" :key="'unitpsto-alt-' + unitPstoIndex" class="alt-sub-row">
                    <td class="pl-10">{{ unitPsto.psto_name || 'PSTO' }}</td>
                    <td class="text-center">{{ unitPsto.total_respo || '-' }}</td>
                    <td class="text-center">{{ getRowVssCount(unitPsto) || '-' }}</td>
                    <td class="text-center">{{ getRowPercentage(unitPsto).toFixed(2) }}%</td>
                    <td class="text-center">{{ getRowPercentage(unitPsto).toFixed(2) }}%</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                  </tr>
                </template>

                <template v-if="unit.sub_units && unit.sub_units.length > 0">
                  <template v-for="(subUnit, subUnitIndex) in unit.sub_units" :key="'sub-alt-' + subUnitIndex">
                    <template v-if="props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]?.total_respo > 0">
                      <tr class="alt-sub-row">
                        <td class="pl-10">{{ subUnit.sub_unit_name }}</td>
                        <td class="text-center">{{ props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]?.total_respo || '-' }}</td>
                        <td class="text-center">{{ getRowVssCount(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]) || '-' }}</td>
                        <td class="text-center">{{ getRowPercentage(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]).toFixed(2) }}%</td>
                        <td class="text-center">{{ getRowPercentage(props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]).toFixed(2) }}%</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                      </tr>
                    </template>

                    <template v-if="props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]?.sub_unit_types?.length">
                      <template v-for="(subUnitType, subUnitTypeIndex) in props.data.all_units_data?.units_data?.[service.id]?.[unit.id]?.sub_units_data?.[subUnit.id]?.sub_unit_types" :key="'subtype-alt-' + subUnitTypeIndex">
                        <tr v-if="subUnitType.total_respo > 0" class="alt-sub-row">
                          <td class="pl-14">{{ subUnitType.type_name }}</td>
                          <td class="text-center">{{ subUnitType.total_respo || '-' }}</td>
                          <td class="text-center">{{ getRowVssCount(subUnitType) || '-' }}</td>
                          <td class="text-center">{{ getRowPercentage(subUnitType).toFixed(2) }}%</td>
                          <td class="text-center">{{ getRowPercentage(subUnitType).toFixed(2) }}%</td>
                          <td class="text-center">-</td>
                          <td class="text-center">-</td>
                        </tr>
                      </template>
                    </template>

                    <template v-if="getPstosWithData(service.id, unit.id, subUnit.id).length > 0">
                      <tr v-for="(subUnitPsto, subUnitPstoIndex) in getPstosWithData(service.id, unit.id, subUnit.id)" :key="'subpsto-alt-' + subUnitPstoIndex" class="alt-sub-row">
                        <td class="pl-14">{{ subUnitPsto.psto_name || 'PSTO' }} (PSTO)</td>
                        <td class="text-center">{{ subUnitPsto.total_respo || '-' }}</td>
                        <td class="text-center">{{ getRowVssCount(subUnitPsto) || '-' }}</td>
                        <td class="text-center">{{ getRowPercentage(subUnitPsto).toFixed(2) }}%</td>
                        <td class="text-center">{{ getRowPercentage(subUnitPsto).toFixed(2) }}%</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                      </tr>
                    </template>
                  </template>
                </template>
              </template>
            </template>

            <tr class="alt-total-row">
              <td class="pl-5">
                <strong>{{ ['OFFICE OF THE REGIONAL DIRECTOR', 'FINANCE AND ADMINISTRATIVE SUPPORT SERVICES', 'TECHNICAL OPERATION SERVICES'].includes(service.services_name) ? '' : service.services_name + ' TOTAL' }}</strong>
              </td>
              <td class="text-center"><strong>{{ getServiceTotals(service.id).total_respo || '-' }}</strong></td>
              <td class="text-center"><strong>{{ getRowVssCount(getServiceTotals(service.id)) || '-' }}</strong></td>
              <td class="text-center"><strong>{{ formatPercentOrDash(getRowPercentage(getServiceTotals(service.id))) }}</strong></td>
              <td class="text-center"><strong>{{ formatPercentOrDash(getRowPercentage(getServiceTotals(service.id))) }}</strong></td>
              <td class="text-center"><strong>{{ formatPercentOrDash(props.data.all_units_data?.service_totals?.[service.id]?.nps) }}</strong></td>
              <td class="text-center"><strong>{{ formatNumberOrDash(props.data.all_units_data?.service_totals?.[service.id]?.lsr) }}</strong></td>
            </tr>
          </template>

          <tr class="alt-grand-total">
            <td><strong>TOTAL</strong></td>
            <td class="text-center"><strong>{{ props.data?.total_respondents || 0 }}</strong></td>
            <td class="text-center"><strong>{{ props.data?.total_vss_respondents || 0 }}</strong></td>
            <td class="text-center"><strong>{{ formatPercentOrDash(props.data?.percentage_vss_respondents) }}</strong></td>
            <td class="text-center"><strong>{{ formatPercentOrDash(props.data?.csi_total) }}</strong></td>
            <td class="text-center"><strong>{{ formatPercentOrDash(props.data?.nps_total) }}</strong></td>
            <td class="text-center"><strong>{{ formatNumberOrDash(props.data?.lsr_total) }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Service Category Totals Summary -->
    <div v-if="props.data?.all_units_data?.service_totals && (props.data?.total_respondents || 0) > 0" class="alt-section">
      <h5 class="alt-section-title">SERVICE CATEGORY TOTALS SUMMARY</h5>
      <table class="alt-summary-table">
        <thead>
          <tr>
            <th></th>
            <th>Office of the Regional Director</th>
            <th>Finance and Administrative Services</th>
            <th>Technical Operation Services</th>
            <th>Overall Average</th>
          </tr>
        </thead>
        <tbody>
          <tr class="alt-summary-row">
            <td class="label-cell">Total No. of Customers/Respondents</td>
            <td class="text-center">{{ props.data.all_units_data.service_totals[1]?.total_respo || 0 }}</td>
            <td class="text-center">{{ props.data.all_units_data.service_totals[2]?.total_respo || 0 }}</td>
            <td class="text-center">{{ props.data.all_units_data.service_totals[3]?.total_respo || 0 }}</td>
            <td class="text-center">{{ props.data.total_respondents || 0 }}</td>
          </tr>
          <tr class="alt-summary-row">
            <td class="label-cell">Total No. of Respondents who rated VS/S</td>
            <td class="text-center">{{ props.data.all_units_data.service_totals[1]?.strongly_agree_agree_count || 0 }}</td>
            <td class="text-center">{{ props.data.all_units_data.service_totals[2]?.strongly_agree_agree_count || 0 }}</td>
            <td class="text-center">{{ props.data.all_units_data.service_totals[3]?.strongly_agree_agree_count || 0 }}</td>
            <td class="text-center">{{ props.data.all_units_data?.grand_strongly_agree_agree_count || 0 }}</td>
          </tr>
          <tr class="alt-summary-row">
            <td class="label-cell">Percentage of Respondents who rated VS/S</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data.service_totals[1]?.pct_strongly_agree_agree) }}</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data.service_totals[2]?.pct_strongly_agree_agree) }}</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data.service_totals[3]?.pct_strongly_agree_agree) }}</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data?.grand_pct_strongly_agree_agree) }}</td>
          </tr>
          <tr class="alt-summary-row">
            <td class="label-cell">Customer Satisfaction Index (CSI)</td>
            <td class="text-center">-</td>
            <td class="text-center">-</td>
            <td class="text-center">-</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.csi_total) }}</td>
          </tr>
          <tr class="alt-summary-row">
            <td class="label-cell">Net Promoter Score</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data?.service_totals?.[1]?.nps) }}</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data?.service_totals?.[2]?.nps) }}</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.all_units_data?.service_totals?.[3]?.nps) }}</td>
            <td class="text-center">{{ formatPercentOrDash(props.data.nps_total) }}</td>
          </tr>
          <tr class="alt-summary-row">
            <td class="label-cell">Customer Satisfaction Score (CSAT) Rating</td>
            <td class="text-center">-</td>
            <td class="text-center">-</td>
            <td class="text-center">-</td>
            <td class="text-center">{{ props.data.all_units_data?.grand_pct_strongly_agree_agree || 0 }}%</td>
          </tr>
          <tr class="alt-summary-row">
            <td class="label-cell">Likert Scale Rating (Attribute Average)</td>
            <td class="text-center">{{ formatNumberOrDash(props.data.all_units_data?.service_totals?.[1]?.lsr) }}</td>
            <td class="text-center">{{ formatNumberOrDash(props.data.all_units_data?.service_totals?.[2]?.lsr) }}</td>
            <td class="text-center">{{ formatNumberOrDash(props.data.all_units_data?.service_totals?.[3]?.lsr) }}</td>
            <td class="text-center">{{ formatNumberOrDash(props.data.lsr_total) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Comments and Complaints -->
    <div class="alt-section" v-if="(props.data?.total_respondents || 0) > 0 && (props.data?.total_comments || props.data?.total_complaints || (props.data?.comments && props.data.comments.length > 0))">
      <div class="alt-assessment-title">COMMENTS AND COMPLAINTS:</div>
      <div class="alt-comments-summary">
        <div class="alt-comments-item">
          <span class="label">Comments:</span>
          <span class="value">{{ props.data.total_comments || 0 }}</span>
        </div>
        <div class="alt-comments-item">
          <span class="label">Complaints:</span>
          <span class="value">{{ props.data.total_complaints || 0 }}</span>
        </div>
      </div>
      <div v-if="props.data.comments && props.data.comments.length > 0" class="alt-comments-list">
        <div v-for="(comment, index) in props.data.comments" :key="'alt-comment-' + index" class="alt-comment-row">
          <strong>[{{ index + 1 }}]</strong> {{ comment }}
        </div>
      </div>
    </div>

    <!-- Assessment Summary -->
    <div class="alt-section alt-assessment">
      <div class="alt-assessment-title">ASSESSMENT:</div>
      <p>
        The Department of Science and Technology IX for <strong>{{ assessmentPeriodText }}</strong> had a total of
        <strong>{{ props.data.total_respondents || 0 }}</strong> respondents who filled out and rated the Customer Satisfaction Feedback.
        <strong>{{ props.data.total_vss_respondents || 0 }}</strong> (or <strong>{{ props.data.percentage_vss_respondents || 0 }}%</strong>)
        of the respondents rated the CSF as either very satisfied (VS) or satisfied (S), which resulted in an overall average
        Customer Satisfaction Index (CSI) of <strong>{{ props.data.csi_total || 0 }}%</strong> and a Net Promoter Score of
        <strong>{{ props.data.nps_total || 0 }}%</strong>.
      </p>
      <p>
        The survey instruments align with the 8 Service Quality Dimensions (SQD) and are scored using a 5-point Likert scale.
        The simple average of the questions is used to get the overall score. The interpretation of the results is as follows:
        1.00-1.49 = very unsatisfied; 1.50-2.49 = unsatisfied; 2.50-3.49 = neither unsatisfied nor satisfied; 3.50-4.49 = satisfied;
        4.50-5.00 = very satisfied. The overall Likert Scale Rating for this period is
        <strong>{{ props.data.lsr_total || 0 }}</strong>, which translates to
        <strong>{{ Number(props.data.lsr_total || 0) >= 4.5 ? 'very satisfied' : (Number(props.data.lsr_total || 0) >= 3.5 ? 'satisfied' : 'needs review') }}</strong>.
      </p>
      <p>
        The Department of Science and Technology IX Customer Satisfaction Survey resulted in an Overall Customer Satisfaction Score Rating of
        <strong>{{ props.data.all_units_data?.grand_pct_strongly_agree_agree || 0 }}%</strong>, which reflects progress toward the target
        of at least 95% of customers being satisfied with DOST-IX services.
      </p>
    </div>
  </div>
</template>

<style scoped>
.alt-report {
  font-family: Arial, sans-serif;
  color: #111827;
}
.alt-header {
  text-align: center;
  margin-bottom: 10px;
}
.print-only {
  display: none;
}
.alt-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
}
.alt-subtitle {
  margin: 4px 0 0 0;
  font-size: 12px;
  font-weight: 600;
}
.alt-title,
.alt-subtitle {
  text-align: center;
}

@media print {
  .alt-header,
  .alt-title,
  .alt-subtitle {
    text-align: center !important;
    width: 100%;
  }
}
.alt-section-title {
  margin: 8px 0;
  font-size: 12px;
  font-weight: 700;
}
.alt-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}
.alt-table th,
.alt-table td {
  border: 1px solid #333;
  padding: 4px;
  font-size: 10px;
  word-break: break-word;
}
.alt-table thead th {
  background: #e3f2fd;
  font-weight: 700;
  text-align: center;
}
.alt-service-row td {
  background: #e3f2fd;
  font-weight: 700;
}
.alt-sub-row td {
  background: #f8fafc;
}
.alt-total-row td {
  background: #fef9e7;
  font-weight: 700;
}
.alt-grand-total td {
  background: #dbeafe;
  font-weight: 700;
}
.pl-5 {
  padding-left: 8px;
}
.pl-10 {
  padding-left: 16px;
}
.pl-14 {
  padding-left: 22px;
}
.cc-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}
.cc-table tr,
.cc-table th,
.cc-table td {
  border: 1px solid #dddddd;
  padding: 8px;
}
.bg-blue-200 {
  background-color: #e3f2fd;
}
.alt-summary-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  margin-top: 6px;
}
.alt-summary-table th,
.alt-summary-table td {
  border: 1px solid #333;
  padding: 4px;
  font-size: 10px;
}
.alt-summary-table thead th {
  background: #bcd7f2;
  font-weight: 700;
  text-align: center;
}
.alt-summary-table .label-cell {
  font-weight: 700;
  text-align: left;
  background: #fff2b3;
}
.alt-summary-row td {
  background: #fff2b3;
}
.alt-assessment-title {
  font-weight: 700;
  font-size: 11px;
  margin-bottom: 4px;
}
.alt-comments-summary {
  display: flex;
  gap: 16px;
  margin-bottom: 6px;
}
.alt-comments-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 10.5px;
}
.alt-comments-item .label {
  font-weight: 700;
}
.alt-comments-item .value {
  padding: 2px 6px;
  border: 1px solid #333;
  background: #fff2b3;
  font-weight: 700;
}
.alt-comments-list {
  margin-top: 6px;
}
.alt-comment-row {
  font-size: 10.5px;
  padding: 4px 6px;
  border: 1px solid #e2e8f0;
  margin-bottom: 4px;
  background: #f8fafc;
}
.alt-assessment p {
  margin: 6px 0;
  font-size: 10.5px;
  line-height: 1.35;
}
</style>
