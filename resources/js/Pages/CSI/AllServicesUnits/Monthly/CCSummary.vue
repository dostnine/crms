<script setup>
const props = defineProps({
  data: {
    type: Object,
  },
});

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
</script>

<template>
  <v-card class="mb-3" v-if="hasAnyCCData()">
    <v-card-title class="bg-gray-500 text-white">
      PART I: CITIZEN'S CHARTER(CC)
    </v-card-title>
    <table style="width:100%; border: 1px solid #333; border-collapse: collapse; padding: 5px" class="text-center">
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
</template>
