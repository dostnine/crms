import{q as I,D as w,d as f,G as O,o as n,c as P,w as k,a as e,t as a,k as p,x as _,e as c,F as h,h as g,f as y,g as d,b as D,O as b,p as F,m as V}from"./app-Zcv0E_xx.js";import{A as j}from"./AppLayout-D5BQVqL8.js";import{P as z}from"./index-DajnbktX.js";import{S as v}from"./sweetalert2.all-BNIOc-jY.js";import G from"./Content-B8ZFzwK_.js";import"./vue-multiselect.css_vue_type_style_index_0_src_true_lang-B0Tpqlkh.js";import{A as L}from"./aos-Cy3DjARk.js";import{_ as H}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./dost-logo-B4Huuyod.js";const s=m=>(F("data-v-8eea2ddc"),m=m(),V(),m),$=s(()=>e("div",{class:"page-heading"},[e("h2",{class:"page-heading-title"},"Customer Satisfaction Index - All Services Units"),e("p",{class:"page-heading-subtitle mb-0"},"Generate monthly, quarterly, or yearly consolidated reports with profile filters.")],-1)),J={class:"container-fluid py-4 csi-all-units-page"},K={class:"row justify-content-center"},W={class:"col-12 col-xl-11"},X={class:"summary-hero mb-4","data-aos":"fade-up"},Z={class:"summary-hero-content"},ee=s(()=>e("div",null,[e("p",{class:"summary-kicker mb-1"},"Customer Experience Analytics"),e("h3",{class:"summary-title mb-1"},"All Services Units Dashboard"),e("p",{class:"summary-text mb-0"}," Build a consolidated CSI snapshot by period, then print a formatted report. ")],-1)),te={class:"summary-stats"},se={class:"stat-pill"},oe=s(()=>e("span",{class:"stat-label"},"CSI",-1)),re={class:"stat-value"},ae={class:"stat-pill"},le=s(()=>e("span",{class:"stat-label"},"NPS",-1)),ne={class:"stat-value"},ie={class:"stat-pill"},ce=s(()=>e("span",{class:"stat-label"},"Respondents",-1)),de={class:"stat-value"},pe={class:"card filter-card shadow border-0 mb-4","data-aos":"fade-up"},_e=s(()=>e("div",{class:"card-header filter-card-header text-white"},[e("h4",{class:"card-title mb-0 d-flex align-items-center"},[e("i",{class:"ri-filter-3-line me-2"}),d(" Generate Report ")])],-1)),ue={class:"card-body"},me={class:"row g-3 align-items-end"},he={class:"col-md-4"},ge=s(()=>e("label",{class:"form-label fw-semibold"},"Report Type",-1)),ye=s(()=>e("option",{value:""},"Select Report Type",-1)),fe=s(()=>e("option",{value:"By Month"},"By Month",-1)),be=s(()=>e("option",{value:"By Quarter"},"By Quarter",-1)),ve=s(()=>e("option",{value:"By Year/Annual"},"By Year/Annual",-1)),xe=[ye,fe,be,ve],we={key:0,class:"col-md-4"},ke=s(()=>e("label",{class:"form-label fw-semibold"},"Month",-1)),Se=["value"],Ae={key:1,class:"col-md-4"},Re=s(()=>e("label",{class:"form-label fw-semibold"},"Quarter",-1)),Be=s(()=>e("option",{value:""},"Select Quarter",-1)),Ee=s(()=>e("option",{value:"FIRST QUARTER"},"First Quarter",-1)),Ce=s(()=>e("option",{value:"SECOND QUARTER"},"Second Quarter",-1)),Ue=s(()=>e("option",{value:"THIRD QUARTER"},"Third Quarter",-1)),Me=s(()=>e("option",{value:"FOURTH QUARTER"},"Fourth Quarter",-1)),Ne=[Be,Ee,Ce,Ue,Me],qe={key:2,class:"col-md-4"},Ye=s(()=>e("label",{class:"form-label fw-semibold"},"Year",-1)),Qe=["value"],Te={class:"col-md-4"},Ie=s(()=>e("label",{class:"form-label fw-semibold"},"Client Type",-1)),Oe=s(()=>e("option",{value:null},"All",-1)),Pe=["value"],De={class:"col-md-4"},Fe=s(()=>e("label",{class:"form-label fw-semibold"},"Sex",-1)),Ve=s(()=>e("option",{value:null},"All",-1)),je=["value"],ze={class:"col-md-4"},Ge=s(()=>e("label",{class:"form-label fw-semibold"},"Age Group",-1)),Le=s(()=>e("option",{value:null},"All",-1)),He=["value"],$e=s(()=>e("i",{class:"ri-file-chart-line me-2"},null,-1)),Je={class:"active-filters mt-3"},Ke={class:"filter-chip"},We=s(()=>e("strong",null,"Type:",-1)),Xe={key:0,class:"filter-chip"},Ze=s(()=>e("strong",null,"Month:",-1)),et={key:1,class:"filter-chip"},tt=s(()=>e("strong",null,"Quarter:",-1)),st={class:"filter-chip"},ot=s(()=>e("strong",null,"Year:",-1)),rt={class:"filter-chip"},at=s(()=>e("strong",null,"Client:",-1)),lt={class:"filter-chip"},nt=s(()=>e("strong",null,"Sex:",-1)),it={class:"filter-chip"},ct=s(()=>e("strong",null,"Age:",-1)),dt={key:0,class:"card mt-4 shadow border-0 report-preview-card","data-aos":"fade-in"},pt={class:"card-header preview-header d-flex justify-content-between align-items-center"},_t=s(()=>e("h5",{class:"card-title mb-1 text-white"},[e("i",{class:"ri-file-chart-line me-2"}),d(" Report Preview ")],-1)),ut={class:"mb-0 preview-period text-white-50"},mt=s(()=>e("i",{class:"ri-printer-line me-2"},null,-1)),ht={class:"card-body print-id"},gt={key:1,class:"empty-state-card text-center mt-4","data-aos":"fade-in"},yt=s(()=>e("div",{class:"empty-state-icon mb-2"},[e("i",{class:"ri-file-chart-line"})],-1)),ft=s(()=>e("h5",{class:"mb-1"},"No Report Preview Yet",-1)),bt=s(()=>e("p",{class:"mb-0"},[d("Choose your filters above, then click "),e("strong",null,"Generate Report"),d(".")],-1)),vt=[yt,ft,bt],xt={__name:"Index",props:{services_units:Object,cc_data:Object,all_units_data:Object,csi_total:Number,nps_total:Number,lsr_total:Number,total_respondents:Number,total_vss_respondents:Number,percentage_vss_respondents:Number,respondent_profile:Object,request:Object},setup(m){L.init();const l=m,t=I({date_from:null,date_to:null,csi_type:null,selected_month:null,selected_year:null,selected_quarter:null,client_type:null,sex:null,age_group:null,comments_complaints:null,analysis:null,service:[]}),S=w(()=>{const i=new Date().getFullYear();return Array.from({length:9},(o,T)=>(i-T).toString())}),x=["JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY","AUGUST","SEPTEMBER","OCTOBER","NOVEMBER","DECEMBER"],A=["Internal","External"],R=["Male","Female","Prefer not to say"],B=["19 or lower","20-34","35-49","50-64","60+","Prefer not to say"],E=f(C());function C(){return new Date().getFullYear().toString()}const U=f(M());function M(){return x[new Date().getMonth()]}const u=f(!1);O(()=>{const i=l.request||{};t.csi_type=i.csi_type||null,t.selected_month=i.selected_month||U.value,t.selected_year=i.selected_year||E.value,t.selected_quarter=i.selected_quarter||null,t.client_type=i.client_type||null,t.sex=i.sex||null,t.age_group=i.age_group||null,u.value=!!i.csi_type});const N=w(()=>t.csi_type==="By Quarter"&&t.selected_quarter?`${t.selected_quarter} ${t.selected_year||""}`.trim():t.csi_type==="By Year/Annual"?t.selected_year||"":`${t.selected_month||""} ${t.selected_year||""}`.trim()),q=async()=>{if(!t.csi_type){v.fire({title:"Error",icon:"error",text:"Please select a report type first!"}),u.value=!1;return}u.value=!0,t.csi_type=="By Month"?(t.selected_quarter="",b.get("/csi/generate/all-units/monthly",t,{preserveState:!0,preserveScroll:!0})):t.csi_type=="By Quarter"?(t.selected_month="",t.selected_quarter?b.get("/csi/generate/all-units/monthly",t,{preserveState:!0,preserveScroll:!0}):(u.value=!1,v.fire({title:"Error",icon:"error",text:"Please select a quarter first!"}))):t.csi_type=="By Year/Annual"&&(t.selected_quarter="",t.selected_year?b.get("/csi/generate/all-units/monthly",t,{preserveState:!0,preserveScroll:!0}):(u.value=!1,v.fire({title:"Error",icon:"error",text:"Please select year first!"})))},Y=f(!1),Q=async()=>{Y.value=!0,(await new z).print(document.querySelector(".print-id"),[` 
          @page {
            size: A4 portrait;
            margin: 10mm;
          }
          * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          body {
            margin: 0;
            color: #111827;
            font-size: 11px;
            line-height: 1.3;
          }
          h4, h5 {
            margin: 0 0 8px 0;
            color: #1f2937;
          }
          .m-5 {
            margin: 0 !important;
          }
          .mb-3 {
            margin-bottom: 12px !important;
          }
          .mb-4 {
            margin-bottom: 14px !important;
          }
          .mt-4 {
            margin-top: 14px !important;
          }
          .text-center {
            text-align: center !important;
          }
          .text-right {
            text-align: right !important;
          }
          .text-left {
            text-align: left !important;
          }
          .pl-5, .pl-10, .pl-14 {
            padding-left: 8px !important;
          }

          table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
          }
          th, td {
            border: 1px solid #9ca3af;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
          }
          thead th {
            background: #1f3b6e !important;
            color: #ffffff !important;
            font-weight: 700;
          }
          .bg-blue-200 {
            background-color: #e3f2fd !important;
          }
          .bg-yellow-50 {
            background-color: #fef9e7 !important;
          }
          .bg-green-50 {
            background-color: #e8f5e9 !important;
          }
          .total-row {
            font-weight: 700;
            background-color: #eef2ff !important;
          }
          .assessment {
            margin-top: 10px !important;
          }
          .assessment p {
            margin: 0 0 6px 0;
          }

          .pie-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
          }
          .pie-chart-collapsible {
            display: grid !important;
          }
          .pie-toggle-btn,
          .pie-collapsed-note {
            display: none !important;
          }
          .pie-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            page-break-inside: avoid;
          }
          .service-category-summary {
            page-break-inside: avoid;
          }
          .service-category-summary table {
            width: 100% !important;
            table-layout: fixed !important;
            border: 1px solid #64748b !important;
          }
          .service-category-summary th,
          .service-category-summary td {
            border: 1px solid #94a3b8 !important;
            padding: 4px 3px !important;
            font-size: 9px !important;
            line-height: 1.2 !important;
            text-align: center !important;
            vertical-align: middle !important;
            word-break: keep-all !important;
            white-space: normal !important;
          }
          .service-category-summary thead th {
            background: #1f3b6e !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            white-space: nowrap !important;
          }
          .service-category-summary tr > th:nth-child(1),
          .service-category-summary tr > td:nth-child(1) {
            width: 25% !important;
            text-align: left !important;
            font-weight: 700 !important;
          }
          .service-category-summary tr > th:nth-child(2),
          .service-category-summary tr > td:nth-child(2),
          .service-category-summary tr > th:nth-child(3),
          .service-category-summary tr > td:nth-child(3),
          .service-category-summary tr > th:nth-child(4),
          .service-category-summary tr > td:nth-child(4),
          .service-category-summary tr > th:nth-child(5),
          .service-category-summary tr > td:nth-child(5) {
            width: 18.75% !important;
          }
          .pie-title {
            font-size: 12px;
            font-weight: 700;
            text-align: center;
          }
          .pie-subtitle {
            font-size: 10px;
            text-align: center;
            color: #334155;
            margin-bottom: 6px;
          }
          .pie-circle {
            width: 120px !important;
            height: 120px !important;
            border-radius: 50%;
            margin: 0 auto 8px auto;
            border: 1px solid #94a3b8;
          }
          .pie-total {
            text-align: center;
            font-size: 10px;
            margin-bottom: 6px;
          }
          .pie-legend-table th,
          .pie-legend-table td {
            font-size: 9px !important;
            padding: 3px !important;
          }
          .legend-label {
            display: flex;
            align-items: center;
            gap: 4px;
          }
          .legend-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
          }

          .new-page,
          .page-break {
            page-break-before: always;
          }
        `])};return(i,r)=>(n(),P(j,{title:"Customer Satisfaction Index"},{header:k(()=>[$]),default:k(()=>[e("div",J,[e("div",K,[e("div",W,[e("div",X,[e("div",Z,[ee,e("div",te,[e("div",se,[oe,e("span",re,a(Number(l.csi_total||0).toFixed(2))+"%",1)]),e("div",ae,[le,e("span",ne,a(Number(l.nps_total||0).toFixed(2))+"%",1)]),e("div",ie,[ce,e("span",de,a(l.total_respondents??0),1)])])])]),e("div",pe,[_e,e("div",ue,[e("div",me,[e("div",he,[ge,p(e("select",{"onUpdate:modelValue":r[0]||(r[0]=o=>t.csi_type=o),class:"form-select"},xe,512),[[_,t.csi_type]])]),t.csi_type=="By Month"?(n(),c("div",we,[ke,p(e("select",{"onUpdate:modelValue":r[1]||(r[1]=o=>t.selected_month=o),class:"form-select"},[(n(),c(h,null,g(x,o=>e("option",{key:o,value:o},a(o),9,Se)),64))],512),[[_,t.selected_month]])])):y("",!0),t.csi_type=="By Quarter"?(n(),c("div",Ae,[Re,p(e("select",{"onUpdate:modelValue":r[2]||(r[2]=o=>t.selected_quarter=o),class:"form-select"},Ne,512),[[_,t.selected_quarter]])])):y("",!0),t.csi_type?(n(),c("div",qe,[Ye,p(e("select",{"onUpdate:modelValue":r[3]||(r[3]=o=>t.selected_year=o),class:"form-select"},[(n(!0),c(h,null,g(S.value,o=>(n(),c("option",{key:o,value:o},a(o),9,Qe))),128))],512),[[_,t.selected_year]])])):y("",!0),e("div",Te,[Ie,p(e("select",{"onUpdate:modelValue":r[4]||(r[4]=o=>t.client_type=o),class:"form-select"},[Oe,(n(),c(h,null,g(A,o=>e("option",{key:o,value:o},a(o),9,Pe)),64))],512),[[_,t.client_type]])]),e("div",De,[Fe,p(e("select",{"onUpdate:modelValue":r[5]||(r[5]=o=>t.sex=o),class:"form-select"},[Ve,(n(),c(h,null,g(R,o=>e("option",{key:o,value:o},a(o),9,je)),64))],512),[[_,t.sex]])]),e("div",ze,[Ge,p(e("select",{"onUpdate:modelValue":r[6]||(r[6]=o=>t.age_group=o),class:"form-select"},[Le,(n(),c(h,null,g(B,o=>e("option",{key:o,value:o},a(o),9,He)),64))],512),[[_,t.age_group]])]),e("div",{class:"col-md-4 d-flex align-items-end"},[e("button",{onClick:q,class:"btn btn-primary w-100 generate-btn"},[$e,d(" Generate Report ")])])]),e("div",Je,[e("span",Ke,[We,d(" "+a(t.csi_type||"Not selected"),1)]),t.csi_type==="By Month"?(n(),c("span",Xe,[Ze,d(" "+a(t.selected_month||"-"),1)])):y("",!0),t.csi_type==="By Quarter"?(n(),c("span",et,[tt,d(" "+a(t.selected_quarter||"-"),1)])):y("",!0),e("span",st,[ot,d(" "+a(t.selected_year||"-"),1)]),e("span",rt,[at,d(" "+a(t.client_type||"All"),1)]),e("span",lt,[nt,d(" "+a(t.sex||"All"),1)]),e("span",it,[ct,d(" "+a(t.age_group||"All"),1)])])])]),u.value==!0&&t.csi_type?(n(),c("div",dt,[e("div",pt,[e("div",null,[_t,e("p",ut,a(N.value),1)]),e("button",{onClick:Q,class:"btn btn-light preview-print-btn"},[mt,d(" Print Report ")])]),e("div",ht,[D(G,{form:t,data:{services_units:l.services_units,all_units_data:l.all_units_data,cc_data:l.cc_data,total_respondents:l.total_respondents,total_vss_respondents:l.total_vss_respondents,percentage_vss_respondents:l.percentage_vss_respondents,respondent_profile:l.respondent_profile,csi_total:l.csi_total,nps_total:l.nps_total,lsr_total:l.lsr_total}},null,8,["form","data"])])])):(n(),c("div",gt,vt))])])])]),_:1}))}},Mt=H(xt,[["__scopeId","data-v-8eea2ddc"]]);export{Mt as default};
