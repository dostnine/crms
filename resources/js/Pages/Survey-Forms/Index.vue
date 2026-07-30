<script setup>
import { ref, reactive, onMounted, onBeforeUnmount, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import SignaturePad from "signature_pad";
import AOS from "aos";
import "aos/dist/aos.css";
import Swal from "sweetalert2";
import ScienceBackground from "@/Components/ScienceBackground.vue";
/* Native selects used - Multiselect removed */

const props = defineProps({
  cc_questions: Object,
  dimensions: Object,
  unit: Object,
  sub_unit: Object,
  unit_psto: Object,
  sub_unit_psto: Object,
  status: String,
  errors: Object,
  captcha_img: String,
  date_display: String,
});

const cc1_options = [
  { label: "1. I know what a CC is and I saw this office's CC.", value: "1" },
  { label: "2. I know what a CC is but I did NOT see this office's CC. ", value: "2" },
  { label: "3. I learned the CC when I saw this office's CC.", value: "3" },
  {
    label:
      "4. I do not know what a CC is  and I did not see one in this office.(Answer 'N/A' on CC2 and CC3)",
    value: "4",
  },
];
const cc2_options = [
  { label: "1. Easy to see", value: "1" },
  { label: "2. Somewhat easy to see", value: "2" },
  { label: "3. Difficult to see", value: "3" },
  { label: "4. not Visible at all", value: "4" },
  { label: "5. N/A", value: "5" },
];
const cc3_options = [
  { label: "1. Helped Very Much", value: "1" },
  { label: "2. Somewhat helped", value: "2" },
  { label: "3. Did not help", value: "3" },
  { label: "4. N/A", value: "4" },
];
const options = [
  {
    label: "Strongly Agree",
    value: "5",
    icon: "ri-emotion-laugh-line",
    color: "#FFEB3B",
  },
  { label: "Agree", value: "4", icon: "ri-emotion-happy-line", color: "#FFC107" },
  { label: "Neither", value: "3", icon: "ri-emotion-normal-line", color: "#263238" },
  { label: "Disagree", value: "2", icon: "ri-emotion-sad-line", color: "#F44336" },
  {
    label: "Very Disagree",
    value: "1",
    icon: "ri-emotion-unhappy-line",
    color: "#6200EA",
  },
  { label: "N/A", value: "6", icon: "ri-close-circle-line", color: "red" },
];
const attribute_numbers = [
  { label: "5", value: "5" },
  { label: "4", value: "4" },
  { label: "3", value: "3" },
  { label: "2", value: "2" },
  { label: "1", value: "1" },
];
const recommendation_numbers = [
  { label: "10", value: "10" },
  { label: "9", value: "9" },
  { label: "8", value: "8" },
  { label: "7", value: "7" },
  { label: "6", value: "6" },
  { label: "5", value: "5" },
  { label: "4", value: "4" },
  { label: "3", value: "3" },
  { label: "2", value: "2" },
  { label: "1", value: "1" },
];

const getCurrentDate = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-based
  const day = String(today.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};
const form = reactive({
  region_id: null,
  service_id: null,
  unit_id: null,
  sub_unit_id: null,
  psto_id: null,
  date: getCurrentDate(),
  client_type: "",
  sub_unit_type: null,
  email: null,
  name: null,
  sex: "",
  age_group: "",
  pwd: 0,
  pregnant: 0,
  senior_citizen: 0,
  cc1: null,
  cc2: null,
  cc3: null,
  recommend_rate_score: null,
  comment: null,
  is_complaint: false,
  indication: null,
  // signature: null,
  dimension_form: {
    id: [],
    rate_score: [],
    importance_rate_score: [],
  },
  cc_form: {
    id: [],
    answer: [],
  },
  captcha: null,
  current_url: null,

  complaint_scanner: {
    value: [],
  },
});

const formSubmitted = ref(false);

// Light / dark (immersive) theme, remembered across the session.
const theme = ref("dark");
// SweetAlert renders outside the page, so mark <html> and let global CSS theme it.
const applyThemeClass = () => {
  const el = document.documentElement;
  el.classList.toggle("csf-theme-dark", theme.value === "dark");
  el.classList.toggle("csf-theme-light", theme.value === "light");
};
const toggleTheme = () => {
  theme.value = theme.value === "dark" ? "light" : "dark";
  applyThemeClass();
  try {
    localStorage.setItem("csf-theme", theme.value);
  } catch (e) {
    /* ignore */
  }
};

const getCC = (index, cc_id, answer) => {
  form.cc_form.id[index] = cc_id;
  form.cc_form.answer[index] = answer;
};

const getDimension = (index, dimension_id) => {
  form.dimension_form.id[index] = dimension_id;
};

// const signaturePad = ref(null);
// const canvas = document.querySelector('.signature-pad canvas');

onMounted(() => {
  AOS.init();

  try {
    const saved = localStorage.getItem("csf-theme");
    if (saved === "light" || saved === "dark") theme.value = saved;
  } catch (e) {
    /* ignore */
  }
  applyThemeClass();

  // signaturePad.value = new SignaturePad(signaturePad.value);
  // const canvas = signaturePad.value;
  // canvas.width = 400;
  // canvas.height = 200;

  const currentURL = window.location.href;
  // Extract query parameters from the URL
  const searchParams = new URLSearchParams(currentURL.split("?")[1]);

  // Get region_id, service_id, and unit_id values
  form.region_id = searchParams.get("region_id");
  form.service_id = searchParams.get("service_id");
  form.unit_id = searchParams.get("unit_id");
  form.sub_unit_id = searchParams.get("sub_unit_id");
  form.psto_id = searchParams.get("psto_id");
  form.sub_unit_type = searchParams.get("sub_unit_type");
  form.current_url = currentURL;

  Swal.fire({
    title: "Disclaimer",
    icon: "warning",
    text:
      "The DOST is committed to protect and respect your personal data privacy. All information collected will only be used for documentation purposes and will not be published in any platform.",
  });
});

onBeforeUnmount(() => {
  document.documentElement.classList.remove("csf-theme-dark", "csf-theme-light");
});

const validateForm = () => {
  const missing = [];
  if (!form.client_type) missing.push("Client Type");
  if (!form.sex) missing.push("Sex");
  if (!form.age_group) missing.push("Age Group");

  const ccCount = props.cc_questions ? Object.keys(props.cc_questions).length : 0;
  for (let i = 0; i < ccCount; i++) {
    if (!form.cc_form.answer[i]) missing.push("Citizen's Charter question " + (i + 1));
  }

  const dims = props.dimensions ? Object.values(props.dimensions) : [];
  dims.forEach((_dim, i) => {
    const rate = form.dimension_form.rate_score[i];
    if (!rate) missing.push("Service rating #" + (i + 1));
    else if (rate != 6 && !form.dimension_form.importance_rate_score[i])
      missing.push("Importance rating #" + (i + 1));
  });

  if (!form.recommend_rate_score) missing.push("Recommendation score");
  if (form.is_complaint && !form.email) missing.push("Email");
  if (form.is_complaint && !form.comment) missing.push("Comment");
  return missing;
};

const saveCSF = async () => {
  formSubmitted.value = true;

  // Block submission until every required field is answered.
  const missing = validateForm();
  if (missing.length) {
    Swal.fire({
      title: "Incomplete Form",
      icon: "warning",
      text: "Please answer all required fields before submitting.",
    });
    requestAnimationFrame(() => {
      const el = document.querySelector(".csf-page .text-danger");
      if (el) el.scrollIntoView({ behavior: "smooth", block: "center" });
    });
    return;
  }

  // const canvas = document.querySelector('.signature-pad');
  // const ctx = canvas.getContext('2d');

  // const imageDataUrl = canvas.toDataURL();

  // Include the data URL in your form data
  // form.signature = imageDataUrl;

  let captcha_code = Math.random();
  // Function to generate a new CAPTCHA image
  const generateCaptcha = () => {
    const captchaImageUrl = "/captcha/flat?rand=" + captcha_code; // Construct the URL with captcha_code
    return (
      '<img src="' +
      captchaImageUrl +
      '" alt="CAPTCHA" style="display: block; margin: 0 auto; ">'
    );
  };

  try {
    Swal.fire({
      title: generateCaptcha(),
      html:
        '<div style="font-weight: bold; font-size:25px">Enter Captcha Code</div> ' +
        '<input id="captcha-input" class="swal2-input text-center">' +
        '<p id="invalid-captcha-text" style="color: red; font-size: 14px; margin-top: 5px; display: none;">Invalid CAPTCHA code</p>',
      inputAttributes: {
        autocapitalize: "off",
      },
      showCancelButton: true,
      confirmButtonText: "Submit",
      showLoaderOnConfirm: true,
      preConfirm: () => {
        const enteredCaptcha = document.getElementById("captcha-input").value;
        form.captcha = enteredCaptcha;
        return true;
      },
    }).then((result) => {
      if (result.isConfirmed) {
        router.post("/csf_submission", form);
      }
    });
  } catch (error) {
    Swal.fire({
      title: "Failed",
      icon: "error",
      text: "Something went wrong please check",
    });
  }
};

const updateIsComplaint = (index, rate_score) => {
  if (
    form.dimension_form.rate_score[index] > 0 &&
    form.dimension_form.rate_score[index] < 4
  ) {
    form.complaint_scanner.value[index] = true;
  } else {
    form.complaint_scanner.value[index] = false;
  }

  if (form.dimension_form.rate_score[index] == 6) {
    form.dimension_form.importance_rate_score[index] = 5;
  }

  form.is_complaint = false;
  form.complaint_scanner.value.forEach((value) => {
    if (value === true) {
      form.is_complaint = true;
      return; // If we found any true value, we can exit the loop
    }
  });
};

// const clearSignature = () => {
//     new SignaturePad(signaturePad.value);
// };

// watch(
//     () => props.errors.captcha,
//     (value) => {
//         if(value){
//             Swal.fire({
//                 title: "Error Captcha",
//                 text: "Wrong captcha code!" ,
//                 icon: "error",
//             })
//         }
//     }

// );

watch(
  () => props.errors,
  (value) => {
    if (value) {
      Swal.fire({
        title: "Failed",
        icon: "error",
        text:
          "Something went wrong. Please check the fields and make sure the captcha is correctly entered. If you continue to get errors, please contact the administrator.",
      });
    }
  }
);
</script>

<template>
  <Head title="CSF Form" />

  <nav class="csf-nav" :class="{ light: theme === 'light' }">
    <a href="/" class="csf-nav-brand">
      <img src="/images/dost-logo.png" class="csf-nav-logo" alt="DOST Logo" />
      <span class="csf-nav-title">Department of Science and Technology</span>
    </a>
    <button
      type="button"
      class="csf-theme-toggle"
      :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
      @click="toggleTheme"
    >
      <i :class="theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line'"></i>
    </button>
  </nav>
  <div class="csf-page" :class="{ light: theme === 'light' }">
    <ScienceBackground v-if="theme === 'dark'" />
    <div v-else class="csf-light-aurora"></div>

    <!-- Form Content -->
    <div class="position-relative z-index-10 w-100 py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <form class="needs-validation" @submit.prevent="saveCSF" novalidate>
            <div class="csf-sheet">
              <div class="card mb-3 text-center shadow-sm csf-hero-card">
                <div class="card-body p-4">
                  <div class="mb-4">
                    <img
                      data-aos="zoom-in"
                      data-aos-duration="500"
                      data-aos-delay="500"
                      class="img-fluid mx-auto d-block"
                      style="width: 200px; height: 200px"
                      src="/images/dost-logo.png"
                      alt="DOST Logo"
                    />
                  </div>
                  <h2
                    class="fw-bold fs-3"
                    data-aos="fade-down"
                    data-aos-duration="500"
                    data-aos-delay="500"
                  >
                    CUSTOMER SATISFACTION FEEDBACK
                  </h2>
                </div>
              </div>

              <div
                data-aos="zoom-out-up"
                data-aos-duration="1000"
                data-aos-delay="500"
                class="card mb-4 shadow-sm"
              >
                <div class="card-body p-4">
                  <h4 class="card-title fw-bold fs-4 mb-3">
                    <span>{{ unit.data[0].unit_name }}</span
                    ><br />
                    <span v-if="sub_unit.data.length > 0">{{
                      sub_unit.data[0].sub_unit_name
                    }}</span>
                    <span v-if="unit_psto.data.length > 0">{{
                      unit_psto.data[0].psto["psto_name"]
                    }}</span>
                    <span v-if="sub_unit_psto.data.length > 0" class="ms-2">{{
                      sub_unit_psto.data[0].psto["psto_name"]
                    }}</span>
                    <span v-if="form.sub_unit_type" class="ms-2">{{
                      form.sub_unit_type
                    }}</span>
                  </h4>
                  <p class="card-text text-muted mb-4">
                    This questionnaire aims to solicit your honest assessment of our
                    services. Please take a minute in filling out this form and help us
                    serve you better.
                  </p>
                  <div>
                    <div v-if="date_display[0].is_displayed == 1" class="mb-3">
                      <label for="date" class="form-label">Date</label>
                      <input
                        id="date"
                        v-model="form.date"
                        type="date"
                        class="form-control"
                      />
                    </div>

                    <div v-if="form.is_complaint == true" class="mb-3">
                      <label for="email" class="form-label"
                        >Email <span class="text-danger">*</span></label
                      >
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-control"
                        placeholder="email@gmail.com"
                        required
                      />
                      <div class="text-danger small mt-1" v-if="formSubmitted && !form.email">
                        This field is required
                      </div>
                    </div>

                    <div v-else class="mb-3">
                      <label for="email" class="form-label">Email (Optional)</label>
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-control"
                        placeholder="email@gmail.com"
                      />
                    </div>

                    <div class="mb-3">
                      <label for="name" class="form-label">Name (Optional)</label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="form-control"
                        placeholder="Enter your full name"
                      />
                    </div>

                    <div class="row mb-4">
                      <div class="col-12 col-md-4 mb-3">
                        <label for="client_type" class="form-label"
                          >Client Type <span class="text-danger">*</span></label
                        >
<select id="client_type" v-model="form.client_type" class="form-select" required>
                          <option value="" disabled>Select Client Type</option>
                          <option>General Public</option>
                          <option>Internal Employees</option>
                          <option>Business/Organization</option>
                          <option>Government Employees</option>
                        </select>


                        <div
                          class="text-danger small mt-1"
                          v-if="formSubmitted && !form.client_type"
                        >
                          This field is required
                        </div>
                      </div>

                      <div class="col-12 col-md-4 mb-3">
                        <label for="sex" class="form-label"
                          >Sex <span class="text-danger">*</span></label
                        >
<select id="sex" v-model="form.sex" class="form-select" required>
                          <option value="" disabled>Select Sex</option>
                          <option>Male</option>
                          <option>Female</option>
                          <option>Prefer not to say</option>
                        </select>


                        <div class="text-danger small mt-1" v-if="formSubmitted && !form.sex">
                          This field is required
                        </div>
                      </div>

                      <div class="col-12 col-md-4 mb-3">
                        <label for="age_group" class="form-label"
                          >Age Group <span class="text-danger">*</span></label
                        >
<select id="age_group" v-model="form.age_group" class="form-select" required>
                          <option value="" disabled>Select Age Group</option>
                          <option>19 or lower</option>
                          <option>20-34</option>
                          <option>35-49</option>
                          <option>50-64</option>
                          <option>65+</option>
                          <option>Prefer not to say</option>
                        </select>



                        <div
                          class="text-danger small mt-1"
                          v-if="formSubmitted && !form.age_group"
                        >
                          This field is required
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div
                data-aos="zoom-out-up"
                data-aos-duration="1000"
                data-aos-delay="500"
                class="card mb-4 shadow-sm"
              >
                <div class="card-body">
                  <h5 class="card-title text-muted mb-4">
                    Select your answer to the Citizen's Charter(CC) questions. The
                    Citizen's Charter is an official document that reflects the services
                    of a government agency/office including its requirements, fees and
                    processing times among others.
                  </h5>
                  <div v-for="(cc_question, i) in cc_questions" :key="i" class="mb-4">
                    <h6 class="fw-bold mb-3">
                      {{ cc_question.title }}. {{ cc_question.question }}
                    </h6>

                    <div v-if="i == 0" class="ms-3">
                      <div
                        v-for="(option, index) in cc1_options"
                        :key="index"
                        class="form-check mb-2"
                      >
                        <input
                          class="form-check-input"
                          type="radio"
                          :name="'cc1'"
                          :id="'cc1_' + index"
                          :value="option.value"
                          v-model="form.cc1"
                          @change="getCC(i, cc_question.id, option.value)"
                        />
                        <label class="form-check-label" :for="'cc1_' + index">
                          {{ option.label }}
                        </label>
                      </div>
                    </div>

                    <div v-if="i == 1" class="ms-3">
                      <div
                        v-for="(option, index) in cc2_options"
                        :key="index"
                        class="form-check mb-2"
                      >
                        <input
                          class="form-check-input"
                          type="radio"
                          :name="'cc2'"
                          :id="'cc2_' + index"
                          :value="option.value"
                          v-model="form.cc2"
                          @change="getCC(i, cc_question.id, option.value)"
                        />
                        <label class="form-check-label" :for="'cc2_' + index">
                          {{ option.label }}
                        </label>
                      </div>
                    </div>

                    <div v-if="i == 2" class="ms-3">
                      <div
                        v-for="(option, index) in cc3_options"
                        :key="index"
                        class="form-check mb-2"
                      >
                        <input
                          class="form-check-input"
                          type="radio"
                          :name="'cc3'"
                          :id="'cc3_' + index"
                          :value="option.value"
                          v-model="form.cc3"
                          @change="getCC(i, cc_question.id, option.value)"
                        />
                        <label class="form-check-label" :for="'cc3_' + index">
                          {{ option.label }}
                        </label>
                      </div>
                    </div>

                    <div
                      class="text-danger ms-3 mt-2"
                      v-if="formSubmitted && !form.cc_form.answer[i]"
                    >
                      This selection is required
                    </div>
                  </div>
                </div>
              </div>

              <div
                data-aos="fade-left"
                data-aos-duration="1000"
                data-aos-delay="500"
                class="card mb-4 shadow-sm"
              >
                <div class="card-header csf-rate-header text-white">
                  <h4 class="card-title mb-0">HOW WOULD YOU RATE OUR SERVICES?</h4>
                </div>
                <div class="card-body">
                  <div
                    data-aos="fade-left"
                    data-aos-duration="1000"
                    data-aos-delay="500"
                    class="card mb-3 border"
                    v-for="(dimension, index) in dimensions"
                    :key="dimension.id"
                  >
                    <div class="card-body text-center">
                      <h6 class="card-title text-start bg-light p-3 mb-4 rounded">
                        {{ dimension.id }}. {{ dimension.description }} ({{
                          dimension.name
                        }})
                      </h6>

                      <input type="hidden" :value="getDimension(index, dimension.id)" />
                      <div class="mb-4">
                        <div
                          class="btn-group d-flex flex-wrap justify-content-center gap-2"
                          role="group"
                        >
                          <input
                            type="hidden"
                            v-model="form.dimension_form.rate_score[index]"
                          />
                          <button
                            v-for="option in options"
                            :key="option.value"
                            type="button"
                            class="btn btn-outline-secondary rounded-pill"
                            :class="{
                              active:
                                form.dimension_form.rate_score[index] === option.value,
                            }"
                            @click="
                              form.dimension_form.rate_score[index] = option.value;
                              updateIsComplaint(
                                index,
                                form.dimension_form.rate_score[index]
                              );
                            "
                          >
                            <i
                              :class="option.icon"
                              class="fs-4 d-block mb-1"
                              :style="{ color: option.color }"
                            ></i>
                            {{ option.label }}
                          </button>
                        </div>
                        <div
                          class="text-danger mt-2"
                          v-if="formSubmitted && !form.dimension_form.rate_score[index]"
                        >
                          This selection is required
                        </div>
                      </div>

                      <div
                        v-if="
                          form.dimension_form.rate_score[index] &&
                          form.dimension_form.rate_score[index] != 6
                        "
                        class="mt-4"
                      >
                        <p class="mb-3 fw-semibold">How important is this attribute?</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                          <div class="btn-group" role="group">
                            <input
                              type="hidden"
                              v-model="form.dimension_form.importance_rate_score[index]"
                            />
                            <button
                              v-for="option in attribute_numbers"
                              :key="option.value"
                              type="button"
                              class="btn btn-outline-secondary rounded-circle mx-1"
                              :class="{
                                active:
                                  form.dimension_form.importance_rate_score[index] ===
                                  option.value,
                              }"
                              @click="
                                form.dimension_form.importance_rate_score[index] =
                                  option.value
                              "
                            >
                              {{ option.label }}
                            </button>
                          </div>
                        </div>
                        <div
                          class="text-danger mt-2"
                          v-if="
                            formSubmitted &&
                            !form.dimension_form.importance_rate_score[index]
                          "
                        >
                          This selection is required
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                data-aos="zoom-out-up"
                data-aos-duration="1000"
                data-aos-delay="500"
                class="card mb-4 shadow-sm"
              >
                <div class="card-body">
                  <h5 class="card-title fw-bold">
                    Considering your complete experience with our agency, how likely would
                    you recommend our services to others?
                    <span class="text-danger">*</span>
                  </h5>

                  <div class="d-flex justify-content-center gap-3 flex-wrap mb-3">
                    <div class="btn-group" role="group">
                      <input type="hidden" v-model="form.recommend_rate_score" />
                      <button
                        v-for="option in recommendation_numbers"
                        :key="option.value"
                        type="button"
                        class="btn btn-outline-secondary rounded-circle mx-1"
                        :class="{ active: form.recommend_rate_score === option.value }"
                        @click="form.recommend_rate_score = option.value"
                      >
                        {{ option.label }}
                      </button>
                    </div>
                  </div>

                  <div
                    class="text-danger"
                    v-if="formSubmitted && !form.recommend_rate_score"
                  >
                    This selection is required
                  </div>
                </div>
              </div>

              <div
                data-aos="zoom-out-up"
                data-aos-duration="1000"
                data-aos-delay="500"
                class="card mb-4 shadow-sm"
              >
                <div class="card-body">
                  <h5 class="card-title fw-bold">
                    Please write your comment/suggestions below.
                    <span v-if="form.is_complaint == true" class="text-danger">*</span>
                    <span v-else class="text-info">(Optional)</span>
                  </h5>

                  <div class="mb-3">
                  <textarea
                      v-if="form.is_complaint == true"
                      v-model="form.comment"
                      class="form-control"
                      rows="10"
                      style="min-height: 150px; resize: vertical;"
                      placeholder="Enter your detailed comments/suggestions here"
                      required
                    ></textarea>
                    <textarea
                      v-else
                      v-model="form.comment"
                      class="form-control"
                      rows="10"
                      style="min-height: 150px; resize: vertical;"
                      placeholder="Enter your comments/suggestions here (supports long text)"
                    ></textarea>
                  </div>

                  <div
                    class="text-danger"
                    v-if="formSubmitted && form.is_complaint == true && !form.comment"
                  >
                    This selection is required because you rate low to our services with
                    the options above.<br />
                    Please input the reason/s why you have rated low.
                  </div>
                </div>
              </div>

              <!-- <v-card 
                                data-aos="zoom-out-up" 
                                data-aos-duration="1000" 
                                data-aos-delay="500" 
                                class="mb-5 mx-auto"
                            >
                                <div class="p-3 mt-0 font-bold text-lg">Please indicate other important attribute/s which you think is important to your needs. (
                                    <span class="text-blue-400">Optional</span>
                                    )</div>
                                    <v-container fluid>
                                        <v-textarea
                                            v-model="form.indication"
                                            placeholder="Input here"
                                        ></v-textarea>
                                        
                                    </v-container>
                            </v-card> -->

              <!-- <v-card 
                                data-aos="zoom-out-up" 
                                data-aos-duration="1000" 
                                data-aos-delay="500" 
                                class="mb-5 mx-auto"
                                >
                                <div class="p-3 mt-0 font-bold text-lg" >Please write your signature on the box. (
                                    <span class="text-blue-400">Optional</span>
                                    )</div>
                                    <v-container class="text-center">
                                        <v-row>
                                            <v-col >
                                            <div>
                                                <canvas class="signature-pad mb-3 mx-auto" ref="signaturePad">
                                                </canvas>
                                                </div>
                                                <v-btn @click="clearSignature" class="">Clear</v-btn>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                            </v-card> -->

              <div
                data-aos="zoom-out-up"
                data-aos-duration="1000"
                data-aos-delay="500"
                class="card mb-4 shadow-sm"
              >
                <div class="card-body">
                  <div class="row mt-4 mb-4 text-center">
                    <div class="col-6 text-end">
                      <a href="/" class="btn csf-back">Back</a>
                    </div>
                    <div class="col-6 text-start">
                      <button
                        type="submit"
                        class="btn csf-submit"
                        :disabled="
                          form.processing || (form.is_complaint && !form.comment)
                        "
                      >
                        <i class="ri-send-plane-line me-2"></i>Submit
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.csf-page {
  position: relative;
  min-height: 100vh;
  padding: 84px 0 40px;
  background: radial-gradient(120% 120% at 50% 0%, #0d1533 0%, #070b1e 55%, #05060f 100%);
  font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.csf-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1020;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 22px;
  background: rgba(7, 11, 30, 0.72);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(125, 211, 252, 0.12);
}
.csf-nav.light {
  background: rgba(255, 255, 255, 0.82);
  border-bottom: 1px solid #e2e8f0;
}
.csf-nav.light .csf-nav-title { color: #10214a; }

.csf-theme-toggle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 1px solid rgba(125, 211, 252, 0.35);
  background: rgba(255, 255, 255, 0.06);
  color: #7dd3fc;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  cursor: pointer;
  transition: all 0.2s ease;
}
.csf-theme-toggle:hover { background: rgba(56, 189, 248, 0.15); color: #bae6fd; }
.csf-nav.light .csf-theme-toggle { border-color: #cbd7ea; background: #fff; color: #f59e0b; }
.csf-nav.light .csf-theme-toggle:hover { background: #f1f6fc; }

.csf-light-aurora {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(60% 60% at 18% 15%, rgba(56, 189, 248, 0.2), transparent 60%),
    radial-gradient(55% 55% at 82% 20%, rgba(99, 102, 241, 0.16), transparent 60%),
    radial-gradient(70% 70% at 50% 95%, rgba(34, 211, 238, 0.12), transparent 60%);
}
.csf-nav-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }
.csf-nav-logo { height: 38px; width: 38px; object-fit: contain; }
.csf-nav-title { color: #fff; font-weight: 700; font-size: 1rem; }

/* The form sits directly on the scene — no opaque sheet */
.csf-sheet {
  position: relative;
  z-index: 10;
  background: transparent;
  padding: 4px;
}

/* Cards become dark glass */
.csf-page .card {
  background: rgba(255, 255, 255, 0.05) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  backdrop-filter: blur(12px);
  color: #e7ecff;
  border-radius: 16px !important;
}
.csf-page .card-body { color: #e7ecff; }
.csf-page .card-title,
.csf-page h2,
.csf-page h4,
.csf-page h5,
.csf-page h6,
.csf-page label,
.csf-page p,
.csf-page .form-check-label { color: #e7ecff; }
.csf-page .text-muted { color: #a9b4d9 !important; }

/* Inner "bg-light" chips (dimension titles) */
.csf-page .bg-light {
  background: rgba(56, 189, 248, 0.1) !important;
  color: #eaf1ff !important;
}

/* Inputs — use background-color (never the `background` shorthand) so the
   select's chevron background-image / no-repeat is never reset and tiled. */
.csf-page .form-control,
.csf-page .form-select,
.csf-page textarea {
  background-color: rgba(9, 13, 30, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #eaf0ff;
}
.csf-page .form-control::placeholder,
.csf-page textarea::placeholder { color: #7d88b0; }
.csf-page .form-control:focus,
.csf-page .form-select:focus,
.csf-page textarea:focus {
  background-color: rgba(9, 13, 30, 0.85);
  border-color: #38bdf8;
  box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.2);
  color: #eaf0ff;
}
.csf-page .form-select option { background: #0d1533; color: #e7ecff; }
.csf-page .form-check-input {
  background-color: rgba(9, 13, 30, 0.6);
  border-color: rgba(255, 255, 255, 0.3);
}
.csf-page .form-check-input:checked {
  background-color: #38bdf8;
  border-color: #38bdf8;
}

/* Outline choice buttons */
.csf-page .btn-outline-secondary {
  color: #c6d0ee;
  border-color: rgba(255, 255, 255, 0.22);
  background: rgba(255, 255, 255, 0.03);
}
.csf-page .btn-outline-secondary:hover {
  background: rgba(56, 189, 248, 0.12);
  border-color: rgba(125, 211, 252, 0.6);
  color: #fff;
}
.csf-page .btn-outline-secondary.active {
  background: linear-gradient(135deg, #38bdf8, #6366f1);
  border-color: transparent;
  color: #06122b;
  box-shadow: 0 6px 16px rgba(56, 189, 248, 0.35);
}

/* ---- Field polish (both themes) ---- */
.csf-page label {
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 6px;
  letter-spacing: 0.2px;
}
.csf-page .form-control,
.csf-page .form-select,
.csf-page textarea {
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 0.98rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}
/* A background shorthand elsewhere wipes Bootstrap's chevron, so redraw it here.
   Force appearance:none so the browser's own native caret doesn't show alongside
   this custom one (which read as a double / stacked arrow). */
.csf-page .form-select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-color: rgba(9, 13, 30, 0.6);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%2338bdf8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  background-size: 15px;
  padding-right: 40px;
  cursor: pointer;
}
.csf-page .form-check {
  padding: 4px 0 4px 1.9em;
}
.csf-page .form-check-input {
  width: 1.15em;
  height: 1.15em;
  margin-top: 0.15em;
  cursor: pointer;
}

/* ---- Light theme ---- */
.csf-page.light {
  background: radial-gradient(120% 120% at 50% 0%, #eef4ff 0%, #e5edf8 55%, #dde7f6 100%);
}
.csf-page.light .card {
  background: rgba(255, 255, 255, 0.78) !important;
  border: 1px solid rgba(21, 59, 112, 0.12) !important;
  color: #12243a;
  box-shadow: 0 10px 30px rgba(21, 59, 112, 0.08);
}
.csf-page.light .card-body,
.csf-page.light .card-title,
.csf-page.light h2,
.csf-page.light h4,
.csf-page.light h5,
.csf-page.light h6,
.csf-page.light label,
.csf-page.light p,
.csf-page.light .form-check-label { color: #12243a; }
.csf-page.light .text-muted { color: #5b7088 !important; }
.csf-page.light .bg-light {
  background: rgba(56, 189, 248, 0.12) !important;
  color: #12243a !important;
}
.csf-page.light .form-control,
.csf-page.light .form-select,
.csf-page.light textarea {
  background-color: #ffffff;
  border: 1px solid #cbd7ea;
  color: #12243a;
}
.csf-page.light .form-control::placeholder,
.csf-page.light textarea::placeholder { color: #94a3b8; }
.csf-page.light .form-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%232266a8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
}
.csf-page.light .form-select option { background: #fff; color: #12243a; }
.csf-page.light .form-control:focus,
.csf-page.light .form-select:focus,
.csf-page.light textarea:focus {
  background-color: #fff;
  border-color: #38bdf8;
  box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.2);
  color: #12243a;
}
.csf-page.light .form-check-input { background-color: #fff; border-color: #cbd7ea; }
.csf-page.light .form-check-input:checked { background-color: #38bdf8; border-color: #38bdf8; }
.csf-page.light .btn-outline-secondary {
  color: #3f6c9e;
  border-color: #cbd7ea;
  background: #fff;
}
.csf-page.light .btn-outline-secondary:hover { background: #eaf2ff; color: #10214a; }
.csf-page.light .btn-outline-secondary.active {
  background: linear-gradient(135deg, #38bdf8, #6366f1);
  color: #06122b;
  border-color: transparent;
}
.csf-page.light .csf-back { background: #fff; }

/* Keep banners on-brand and readable in light mode */
.csf-page.light .csf-hero-card {
  background: linear-gradient(120deg, #10214a 0%, #123a6b 55%, #0d2f54 100%) !important;
  border: none !important;
}
.csf-page.light .csf-hero-card,
.csf-page.light .csf-hero-card h2,
.csf-page.light .csf-hero-card .card-body { color: #fff; }
.csf-page.light .csf-rate-header .card-title { color: #fff; }

/* Branded header banner */
.csf-hero-card {
  border: none !important;
  border-radius: 18px !important;
  overflow: hidden;
  background: linear-gradient(120deg, #10214a 0%, #123a6b 55%, #0d2f54 100%) !important;
}
.csf-hero-card .card-body { color: #fff; }
.csf-hero-card h2 { color: #fff; letter-spacing: 0.5px; }

.csf-rate-header {
  background: linear-gradient(90deg, #10214a, #123a6b) !important;
  border-bottom: none !important;
}

/* Buttons */
.csf-submit {
  background: linear-gradient(135deg, #67e8f9 0%, #38bdf8 45%, #818cf8 100%);
  border: none;
  color: #06122b;
  font-weight: 700;
  border-radius: 999px;
  padding: 10px 28px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.csf-submit:hover:not(:disabled) {
  color: #06122b;
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(56, 189, 248, 0.45);
}
.csf-submit:disabled { opacity: 0.55; }
.csf-back {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #334155;
  border-radius: 999px;
  padding: 10px 28px;
  font-weight: 600;
}
.csf-back:hover { background: #f1f5f9; color: #0f172a; }

@media (max-width: 640px) {
  .csf-nav-title { display: none; }
  .csf-sheet { padding: 12px; border-radius: 16px; }
  /* Tighten the top gap: less page padding + trim the Form Content py-5 */
  .csf-page { padding-top: 64px; }
  .csf-page .py-5 {
    padding-top: 0.75rem !important;
    padding-bottom: 1.25rem !important;
  }
}

/* ---- SweetAlert modals themed to the form's mode ----
   (SweetAlert mounts on <body>, so these are gated by a class on <html>) */
html.csf-theme-dark .swal2-popup {
  background: #0e1730;
  color: #e7ecff;
  border: 1px solid rgba(125, 211, 252, 0.18);
  box-shadow: 0 30px 80px rgba(3, 6, 20, 0.6);
}
html.csf-theme-dark .swal2-title,
html.csf-theme-dark .swal2-html-container { color: #e7ecff; }
html.csf-theme-dark .swal2-input {
  background: rgba(9, 13, 30, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: #eaf0ff;
}
html.csf-theme-dark .swal2-input::placeholder { color: #7d88b0; }
html.csf-theme-dark .swal2-validation-message {
  background: rgba(9, 13, 30, 0.6);
  color: #fca5a5;
}

/* Brand buttons in both modes */
html.csf-theme-dark .swal2-confirm,
html.csf-theme-light .swal2-confirm {
  background: linear-gradient(135deg, #38bdf8, #6366f1) !important;
  color: #06122b !important;
  font-weight: 700;
}
html.csf-theme-dark .swal2-cancel,
html.csf-theme-light .swal2-cancel {
  background: transparent !important;
  border: 1px solid #94a3b8 !important;
  color: #64748b !important;
}
html.csf-theme-light .swal2-popup {
  background: #ffffff;
  color: #12243a;
  box-shadow: 0 24px 60px rgba(13, 47, 84, 0.18);
}
html.csf-theme-light .swal2-title { color: #10214a; }
html.csf-theme-light .swal2-html-container { color: #38506b; }
</style>
