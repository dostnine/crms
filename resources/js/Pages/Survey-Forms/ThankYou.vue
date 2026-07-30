<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import ScienceBackground from "@/Components/ScienceBackground.vue";

const props = defineProps({
  message: String,
  status: String,
  current_url: String,
});

const theme = ref("dark");
onMounted(() => {
  try {
    const saved = localStorage.getItem("csf-theme");
    if (saved === "light" || saved === "dark") theme.value = saved;
  } catch (e) {
    /* ignore */
  }
});
</script>

<template>
  <Head title="CSF Submission" />

  <div class="ty" :class="{ light: theme === 'light' }">
    <ScienceBackground v-if="theme === 'dark'" />
    <div v-else class="ty-aurora"></div>

    <nav class="ty-nav" :class="{ light: theme === 'light' }">
      <a href="/" class="ty-brand">
        <img src="/images/dost-logo.png" class="ty-logo" alt="DOST Logo" />
        <span class="ty-title">Department of Science and Technology</span>
      </a>
    </nav>

    <main class="ty-main">
      <div class="ty-card">
        <template v-if="status == 'success'">
          <div class="ty-icon success"><i class="ri-checkbox-circle-fill"></i></div>
          <h1 class="ty-heading">{{ message || "Successfully Submitted" }}</h1>
          <p class="ty-text">
            Thank you for your feedback — it helps DOST serve you better.<br />
            Have a great day!
          </p>
        </template>
        <template v-else>
          <div class="ty-icon error"><i class="ri-error-warning-fill"></i></div>
          <h1 class="ty-heading">{{ message || "Something went wrong" }}</h1>
          <p class="ty-text">
            Your feedback could not be submitted. Please try again.
          </p>
        </template>

        <div class="ty-actions">
          <Link :href="props.current_url" class="btn-primary">
            <i class="ri-check-line me-1"></i> Okay
          </Link>
          <a href="/" class="btn-ghost"><i class="ri-home-4-line me-1"></i> Home</a>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.ty {
  position: relative;
  min-height: 100vh;
  color: #e7ecff;
  background: radial-gradient(120% 120% at 50% 0%, #0d1533 0%, #070b1e 55%, #05060f 100%);
  overflow-x: hidden;
  font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
}
.ty.light {
  color: #12243a;
  background: radial-gradient(120% 120% at 50% 0%, #eef4ff 0%, #e5edf8 55%, #dde7f6 100%);
}
.ty-aurora {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(60% 60% at 18% 15%, rgba(56, 189, 248, 0.2), transparent 60%),
    radial-gradient(55% 55% at 82% 20%, rgba(99, 102, 241, 0.16), transparent 60%),
    radial-gradient(70% 70% at 50% 95%, rgba(34, 211, 238, 0.12), transparent 60%);
}

.ty-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  padding: 14px 24px;
  background: rgba(7, 11, 30, 0.72);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(125, 211, 252, 0.12);
}
.ty-nav.light {
  background: rgba(255, 255, 255, 0.82);
  border-bottom: 1px solid #e2e8f0;
}
.ty-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }
.ty-logo { height: 40px; width: 40px; object-fit: contain; }
.ty-title { font-weight: 700; font-size: 1rem; color: #fff; }
.ty-nav.light .ty-title { color: #10214a; }

.ty-main {
  position: relative;
  z-index: 10;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 100px 20px 48px;
}

.ty-card {
  width: 100%;
  max-width: 520px;
  text-align: center;
  padding: 48px 40px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(18px);
  box-shadow: 0 30px 80px rgba(3, 6, 20, 0.55);
  animation: rise 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.ty.light .ty-card {
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(21, 59, 112, 0.12);
  box-shadow: 0 24px 60px rgba(13, 47, 84, 0.15);
}

.ty-icon {
  width: 92px;
  height: 92px;
  margin: 0 auto 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
}
.ty-icon.success {
  color: #34d399;
  background: rgba(52, 211, 153, 0.14);
  box-shadow: 0 0 0 6px rgba(52, 211, 153, 0.08);
  animation: pop 0.5s cubic-bezier(0.22, 1, 0.36, 1) 0.15s both;
}
.ty-icon.error {
  color: #f87171;
  background: rgba(248, 113, 113, 0.14);
  box-shadow: 0 0 0 6px rgba(248, 113, 113, 0.08);
}

.ty-heading {
  font-size: clamp(1.5rem, 4vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.4px;
  margin: 0 0 12px;
}
.ty-text { font-size: 1.02rem; line-height: 1.6; margin: 0 0 30px; }
.ty.light .ty-text { color: #5b7088; }
.ty:not(.light) .ty-text { color: #b7c2e8; }

.ty-actions {
  display: flex;
  gap: 14px;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-primary,
.btn-ghost {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.98rem;
  padding: 12px 28px;
  text-decoration: none;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}
.btn-primary {
  color: #06122b;
  background: linear-gradient(135deg, #67e8f9 0%, #38bdf8 45%, #818cf8 100%);
  box-shadow: 0 10px 30px rgba(56, 189, 248, 0.35);
  border: none;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 44px rgba(56, 189, 248, 0.5); }
.btn-ghost {
  color: #e7ecff;
  border: 1px solid rgba(231, 236, 255, 0.3);
  background: rgba(255, 255, 255, 0.05);
}
.ty.light .btn-ghost { color: #334155; border-color: #cbd7ea; background: #fff; }
.btn-ghost:hover { transform: translateY(-2px); }

@keyframes rise {
  from { opacity: 0; transform: translateY(26px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes pop {
  from { opacity: 0; transform: scale(0.6); }
  to { opacity: 1; transform: scale(1); }
}

@media (max-width: 560px) {
  .ty-title { display: none; }
  .ty-card { padding: 36px 24px; }
}
</style>
