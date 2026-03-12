<script setup>
import { ref, reactive, onMounted, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import SignaturePad from "signature_pad";
import AOS from "aos";
import "aos/dist/aos.css";
import Swal from "sweetalert2";
import Multiselect from "@/Components/Multiselect.vue";

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
  client_type: null,
  sub_unit_type: null,
  email: null,
  name: null,
  sex: null,
  age_group: null,
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

const saveCSF = async () => {
  formSubmitted.value = true;

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

  <nav
    data-aos="fade-down"
    data-aos-duration="500"
    data-aos-delay="500"
    class="navbar navbar-expand-lg navbar-light bg-white border-bottom position-fixed w-100"
    style="z-index: 1020"
  >
    <div class="container-fluid">
      <a href="/" class="navbar-brand d-flex align-items-center">
        <img
          src="../../../../public/images/dost-logo.jpg"
          class="me-3"
          style="height: 2rem"
          alt="DOST Logo"
        />
        <span class="fw-semibold fs-5 text-dark"
          >Department of Science and Technology</span
        >
      </a>
    </div>
  </nav>
  <div
    class="min-vh-100 position-relative"
    data-aos="fade-up"
    data-aos-duration="2000"
    data-aos-delay="500"
    style="background: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #312e81 100%)"
  >
    <!-- Animated Background Elements -->
    <div class="position-absolute w-100 h-100">
      <div
        class="position-absolute bg-primary rounded-circle opacity-25 animate-pulse"
        style="top: 10%; left: 10%; width: 8rem; height: 8rem"
      ></div>
      <div
        class="position-absolute bg-info rounded-circle opacity-25 animate-pulse"
        style="bottom: 10%; right: 10%; width: 6rem; height: 6rem; animation-delay: 1s"
      ></div>
      <div
        class="position-absolute bg-warning rounded-circle opacity-25 animate-pulse"
        style="
          top: 50%;
          left: 50%;
          width: 4rem;
          height: 4rem;
          animation-delay: 2s;
          transform: translate(-50%, -50%);
        "
      ></div>
    </div>

    <!-- Tech Animation Elements -->
    <div class="position-absolute w-100 h-100 overflow-hidden">
      <!-- Moving Circuit Lines -->
      <div class="position-absolute w-100 h-100">
        <svg
          class="w-100 h-100 opacity-25"
          viewBox="0 0 1000 1000"
          xmlns="http://www.w3.org/2000/svg"
        >
          <defs>
            <linearGradient id="circuit-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color: #0d6efd; stop-opacity: 1" />
              <stop offset="100%" style="stop-color: #6610f2; stop-opacity: 1" />
            </linearGradient>
          </defs>
          <!-- Horizontal Lines -->
          <line
            x1="0"
            y1="200"
            x2="1000"
            y2="200"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="8s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="0"
            y1="400"
            x2="1000"
            y2="400"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="10s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="0"
            y1="600"
            x2="1000"
            y2="600"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="12s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="0"
            y1="800"
            x2="1000"
            y2="800"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="9s"
              repeatCount="indefinite"
            />
          </line>
          <!-- Vertical Lines -->
          <line
            x1="200"
            y1="0"
            x2="200"
            y2="1000"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="11s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="400"
            y1="0"
            x2="400"
            y2="1000"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="13s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="600"
            y1="0"
            x2="600"
            y2="1000"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="7s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="800"
            y1="0"
            x2="800"
            y2="1000"
            stroke="url(#circuit-gradient)"
            stroke-width="2"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1000;1000,0"
              dur="15s"
              repeatCount="indefinite"
            />
          </line>
          <!-- Diagonal Lines -->
          <line
            x1="0"
            y1="0"
            x2="1000"
            y2="1000"
            stroke="url(#circuit-gradient)"
            stroke-width="1"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1414;1414,0"
              dur="20s"
              repeatCount="indefinite"
            />
          </line>
          <line
            x1="1000"
            y1="0"
            x2="0"
            y2="1000"
            stroke="url(#circuit-gradient)"
            stroke-width="1"
            class="animate-pulse"
          >
            <animate
              attributeName="stroke-dasharray"
              values="0,1414;1414,0"
              dur="18s"
              repeatCount="indefinite"
            />
          </line>
        </svg>
      </div>

      <!-- Floating Geometric Shapes -->
      <div
        class="position-absolute border border-info rotate-45 animate-bounce"
        style="top: 25%; left: 25%; width: 2rem; height: 2rem; animation-duration: 6s"
      ></div>
      <div
        class="position-absolute border border-primary rounded-circle animate-spin"
        style="
          top: 75%;
          right: 25%;
          width: 1.5rem;
          height: 1.5rem;
          animation-duration: 8s;
          animation-delay: 2s;
        "
      ></div>
      <div
        class="position-absolute bg-warning opacity-50 animate-ping"
        style="
          top: 50%;
          left: 50%;
          width: 1rem;
          height: 1rem;
          animation-duration: 4s;
          animation-delay: 1s;
          transform: translate(-50%, -50%);
        "
      ></div>
      <div
        class="position-absolute border border-light opacity-50 rotate-12 animate-pulse"
        style="
          bottom: 25%;
          right: 33%;
          width: 2.5rem;
          height: 2.5rem;
          animation-duration: 5s;
          animation-delay: 3s;
        "
      ></div>
      <div
        class="position-absolute bg-light opacity-75 rounded-circle animate-bounce"
        style="
          top: 17%;
          right: 17%;
          width: 0.75rem;
          height: 0.75rem;
          animation-duration: 7s;
          animation-delay: 1.5s;
        "
      ></div>
    </div>

    <!-- Form Content -->
    <div class="position-relative z-index-10 w-100 py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <form class="needs-validation" @submit.prevent="saveCSF" novalidate>
            <div class="py-5 bg-light">
              <div class="card mb-3 text-center shadow-sm">
                <div class="card-body p-4">
                  <div class="mb-4">
                    <img
                      data-aos="zoom-in"
                      data-aos-duration="500"
                      data-aos-delay="500"
                      class="img-fluid mx-auto d-block"
                      style="width: 200px; height: 200px"
                      src="../../../../public/images/dost-logo.jpg"
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
                      <div class="invalid-feedback" v-if="formSubmitted && !form.email">
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
                        <Multiselect
                          id="client_type"
                          v-model="form.client_type"
                          placeholder="Please select your client type"
                          :options="[
                            'General Public',
                            'Internal Employees',
                            'Business/Organization',
                            'Government Employees',
                          ]"
                          required
                        />
                        <div
                          class="invalid-feedback"
                          v-if="formSubmitted && !form.client_type"
                        >
                          This field is required
                        </div>
                      </div>

                      <div class="col-12 col-md-4 mb-3">
                        <label for="sex" class="form-label"
                          >Sex <span class="text-danger">*</span></label
                        >
                        <Multiselect
                          id="sex"
                          v-model="form.sex"
                          placeholder="Please select your sex"
                          :options="['Male', 'Female', 'Prefer not to say']"
                          required
                        />
                        <div class="invalid-feedback" v-if="formSubmitted && !form.sex">
                          This field is required
                        </div>
                      </div>

                      <div class="col-12 col-md-4 mb-3">
                        <label for="age_group" class="form-label"
                          >Age Group <span class="text-danger">*</span></label
                        >
                        <Multiselect
                          id="age_group"
                          v-model="form.age_group"
                          placeholder="Please select your age group"
                          :options="[
                            '19 or lower',
                            '20-34',
                            '35-49',
                            '50-64',
                            '65+',
                            'Prefer not to say',
                          ]"
                          label="value"
                          required
                        />

                        <div
                          class="invalid-feedback"
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
                <div class="card-header bg-primary text-white">
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
                      rows="4"
                      placeholder="Input here!"
                      required
                    ></textarea>
                    <textarea
                      v-else
                      v-model="form.comment"
                      class="form-control"
                      rows="4"
                      placeholder="Input here"
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
                      <a href="/" class="btn btn-secondary">Back</a>
                    </div>
                    <div class="col-6 text-start">
                      <button
                        type="submit"
                        class="btn btn-success"
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
canvas {
  border: 1px solid #000;
}
</style>
