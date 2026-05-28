import{q as I,D as R,d as b,G as F,o as l,c as w,w as E,a as t,t as n,k as p,x as m,e as c,F as g,h as y,f,g as d,O as k,E as j,p as D,m as V}from"./app-DInVRJ7t.js";import{A as z}from"./AppLayout-CPeBYPeY.js";import{P as G}from"./index-DajnbktX.js";import{S}from"./sweetalert2.all-DAXLSJHq.js";import L from"./Content-ByOw6GWM.js";import H from"./AltContent-9TXjdoKE.js";import"./vue-multiselect.css_vue_type_style_index_0_src_true_lang-P172vcfc.js";import{A as $}from"./aos-DsODtPyj.js";import{_ as J}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./dost-logo-B4Huuyod.js";const s=h=>(D("data-v-3ecd02c5"),h=h(),V(),h),K=s(()=>t("div",{class:"page-heading"},[t("h2",{class:"page-heading-title"},"Customer Satisfaction Index - All Services Units"),t("p",{class:"page-heading-subtitle mb-0"},"Generate monthly, quarterly, or yearly consolidated reports with profile filters.")],-1)),W={class:"container-fluid py-4 csi-all-units-page"},X={class:"row justify-content-center"},Z={class:"col-12 col-xl-11"},tt={class:"summary-hero mb-4","data-aos":"fade-up"},et={class:"summary-hero-content"},st=s(()=>t("div",null,[t("p",{class:"summary-kicker mb-1"},"Customer Experience Analytics"),t("h3",{class:"summary-title mb-1"},"All Services Units Dashboard"),t("p",{class:"summary-text mb-0"}," Build a consolidated CSI snapshot by period, then print a formatted report. ")],-1)),ot={class:"summary-stats"},at={class:"stat-pill"},rt=s(()=>t("span",{class:"stat-label"},"CSAT",-1)),nt={class:"stat-value"},it={class:"stat-pill"},lt=s(()=>t("span",{class:"stat-label"},"CSI",-1)),ct={class:"stat-value"},dt={class:"stat-pill"},pt=s(()=>t("span",{class:"stat-label"},"NPS",-1)),mt={class:"stat-value"},_t={class:"stat-pill"},ut=s(()=>t("span",{class:"stat-label"},"Respondents",-1)),ht={class:"stat-value"},bt={class:"card filter-card shadow border-0 mb-4","data-aos":"fade-up"},gt=s(()=>t("div",{class:"card-header filter-card-header text-white"},[t("h4",{class:"card-title mb-0 d-flex align-items-center"},[t("i",{class:"ri-filter-3-line me-2"}),d(" Generate Report ")])],-1)),yt={class:"card-body"},ft={class:"row g-3 align-items-end"},vt={class:"col-md-4"},xt=s(()=>t("label",{class:"form-label fw-semibold"},"Report Type",-1)),wt=s(()=>t("option",{value:""},"Select Report Type",-1)),kt=s(()=>t("option",{value:"By Month"},"By Month",-1)),St=s(()=>t("option",{value:"By Quarter"},"By Quarter",-1)),At=s(()=>t("option",{value:"By Year/Annual"},"By Year/Annual",-1)),Rt=[wt,kt,St,At],Et={key:0,class:"col-md-4"},Bt=s(()=>t("label",{class:"form-label fw-semibold"},"Month",-1)),Ct=["value"],Nt={key:1,class:"col-md-4"},Ut=s(()=>t("label",{class:"form-label fw-semibold"},"Quarter",-1)),Mt=s(()=>t("option",{value:""},"Select Quarter",-1)),Tt=s(()=>t("option",{value:"FIRST QUARTER"},"First Quarter",-1)),qt=s(()=>t("option",{value:"SECOND QUARTER"},"Second Quarter",-1)),Yt=s(()=>t("option",{value:"THIRD QUARTER"},"Third Quarter",-1)),Ot=s(()=>t("option",{value:"FOURTH QUARTER"},"Fourth Quarter",-1)),Pt=[Mt,Tt,qt,Yt,Ot],Qt={key:2,class:"col-md-4"},It=s(()=>t("label",{class:"form-label fw-semibold"},"Year",-1)),Ft=["value"],jt={class:"col-md-4"},Dt=s(()=>t("label",{class:"form-label fw-semibold"},"Client Type",-1)),Vt=s(()=>t("option",{value:null},"All",-1)),zt=["value"],Gt={class:"col-md-4"},Lt=s(()=>t("label",{class:"form-label fw-semibold"},"Sex",-1)),Ht=s(()=>t("option",{value:null},"All",-1)),$t=["value"],Jt={class:"col-md-4"},Kt=s(()=>t("label",{class:"form-label fw-semibold"},"Age Group",-1)),Wt=s(()=>t("option",{value:null},"All",-1)),Xt=["value"],Zt=s(()=>t("i",{class:"ri-file-chart-line me-2"},null,-1)),te={class:"active-filters mt-3"},ee={class:"filter-chip"},se=s(()=>t("strong",null,"Type:",-1)),oe={key:0,class:"filter-chip"},ae=s(()=>t("strong",null,"Month:",-1)),re={key:1,class:"filter-chip"},ne=s(()=>t("strong",null,"Quarter:",-1)),ie={class:"filter-chip"},le=s(()=>t("strong",null,"Year:",-1)),ce={class:"filter-chip"},de=s(()=>t("strong",null,"Client:",-1)),pe={class:"filter-chip"},me=s(()=>t("strong",null,"Sex:",-1)),_e={class:"filter-chip"},ue=s(()=>t("strong",null,"Age:",-1)),he={key:0,class:"card mt-4 shadow border-0 report-preview-card","data-aos":"fade-in"},be={class:"card-header preview-header d-flex justify-content-between align-items-center"},ge=s(()=>t("h5",{class:"card-title mb-1 text-white"},[t("i",{class:"ri-file-chart-line me-2"}),d(" Report Preview ")],-1)),ye={class:"mb-0 preview-period text-white-50"},fe={class:"d-flex align-items-center gap-3"},ve=s(()=>t("option",{value:"standard"},"Current Format",-1)),xe=s(()=>t("option",{value:"alternative"},"Alternative Format",-1)),we=[ve,xe],ke=s(()=>t("i",{class:"ri-printer-line me-2"},null,-1)),Se={class:"card-body print-id"},Ae={key:1,class:"empty-state-card text-center mt-4","data-aos":"fade-in"},Re=s(()=>t("div",{class:"empty-state-icon mb-2"},[t("i",{class:"ri-file-chart-line"})],-1)),Ee=s(()=>t("h5",{class:"mb-1"},"No Report Preview Yet",-1)),Be=s(()=>t("p",{class:"mb-0"},[d("Choose your filters above, then click "),t("strong",null,"Generate Report"),d(".")],-1)),Ce=[Re,Ee,Be],Ne={__name:"Index",props:{services_units:Object,cc_data:Object,all_units_data:Object,region:Object,csi_total:Number,nps_total:Number,lsr_total:Number,total_respondents:Number,total_vss_respondents:Number,percentage_vss_respondents:Number,total_comments:Number,total_complaints:Number,comments:Object,respondent_profile:Object,request:Object},setup(h){$.init();const a=h,e=I({date_from:null,date_to:null,csi_type:null,selected_month:null,selected_year:null,selected_quarter:null,client_type:null,sex:null,age_group:null,comments_complaints:null,analysis:null,service:[]}),B=R(()=>{const i=new Date().getFullYear();return Array.from({length:9},(o,u)=>(i-u).toString())}),A=["JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY","AUGUST","SEPTEMBER","OCTOBER","NOVEMBER","DECEMBER"],C=["Internal","External"],N=["Male","Female","Prefer not to say"],U=["19 or lower","20-34","35-49","50-64","60+","Prefer not to say"],M=b(T());function T(){return new Date().getFullYear().toString()}const q=b(Y());function Y(){return A[new Date().getMonth()]}const _=b(!1),v=b("standard");F(()=>{const i=a.request||{};e.csi_type=i.csi_type||null,e.selected_month=i.selected_month||q.value,e.selected_year=i.selected_year||M.value,e.selected_quarter=i.selected_quarter||null,e.client_type=i.client_type||null,e.sex=i.sex||null,e.age_group=i.age_group||null,_.value=!!i.csi_type});const O=R(()=>e.csi_type==="By Quarter"&&e.selected_quarter?`${e.selected_quarter} ${e.selected_year||""}`.trim():e.csi_type==="By Year/Annual"?e.selected_year||"":`${e.selected_month||""} ${e.selected_year||""}`.trim()),P=async()=>{if(!e.csi_type){S.fire({title:"Error",icon:"error",text:"Please select a report type first!"}),_.value=!1;return}_.value=!0,e.csi_type=="By Month"?(e.selected_quarter="",k.get("/csi/generate/all-units/monthly",e,{preserveState:!0,preserveScroll:!0})):e.csi_type=="By Quarter"?(e.selected_month="",e.selected_quarter?k.get("/csi/generate/all-units/monthly",e,{preserveState:!0,preserveScroll:!0}):(_.value=!1,S.fire({title:"Error",icon:"error",text:"Please select a quarter first!"}))):e.csi_type=="By Year/Annual"&&(e.selected_quarter="",e.selected_year?k.get("/csi/generate/all-units/monthly",e,{preserveState:!0,preserveScroll:!0}):(_.value=!1,S.fire({title:"Error",icon:"error",text:"Please select year first!"})))},x=b(!1),Q=async()=>{x.value=!0,await j();const i=document.activeElement;i&&typeof i.blur=="function"&&i.blur(),document.querySelectorAll("select").forEach(u=>{typeof u.blur=="function"&&u.blur()}),await new Promise(u=>setTimeout(u,150)),v.value==="alternative"&&document.body.classList.add("printing-alt"),(await new G).print(document.querySelector(".print-id"),[` 
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
            word-break: break-word;
            white-space: normal;
          }
          thead th {
            background: #1f3b6e !important;
            color: #ffffff !important;
            font-weight: 700;
            white-space: normal;
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
          .print-only {
            display: none !important;
          }
          .print-header,
          .print-title,
          .print-subtitle,
          .alt-header,
          .alt-title,
          .alt-subtitle {
            width: 100% !important;
            text-align: center !important;
            display: none !important;
          }
          /* Show alt header only when printing alternative format */
          body.printing-alt .alt-header,
          body.printing-alt .alt-title,
          body.printing-alt .alt-subtitle {
            display: block !important;
          }
          body.printing-alt .print-only {
            display: block !important;
          }
          /* Show standard print header when printing standard format */
          body:not(.printing-alt) .print-only,
          body:not(.printing-alt) .print-header,
          body:not(.printing-alt) .print-title,
          body:not(.printing-alt) .print-subtitle {
            display: block !important;
          }
          .pie-chart-collapsible {
            display: grid !important;
          }
          .pie-toggle-btn,
          .pie-collapsed-note,
          .comment-controls,
          .comment-filter-select,
          .print-hidden {
            display: none !important;
          }
          .pie-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            page-break-inside: avoid;
          }
          .comments-section-card {
            display: block !important;
            clear: both !important;
            break-before: page !important;
            page-break-before: always !important;
          }
          .comment-print-section {
            display: block !important;
            break-inside: auto !important;
            page-break-inside: auto !important;
          }
          .complaint-print-section {
            break-before: page !important;
            page-break-before: always !important;
          }
          .alt-comments-section {
            display: block !important;
            clear: both !important;
            break-before: page !important;
            page-break-before: always !important;
          }
          .alt-comment-print-section {
            display: block !important;
            break-inside: auto !important;
            page-break-inside: auto !important;
          }
          .alt-complaint-print-section {
            break-before: page !important;
            page-break-before: always !important;
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
            word-break: break-word !important;
            white-space: normal !important;
          }
          .service-category-summary thead th {
            background: #1f3b6e !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            white-space: normal !important;
            word-break: break-word !important;
          }
          .service-overview-table th,
          .service-overview-table td {
            font-size: 9.5px !important;
            line-height: 1.2 !important;
            padding: 4px !important;
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
          /* Ensure pie charts print with colors */
          .pie-circle {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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
        `]),document.body.classList.remove("printing-alt"),x.value=!1};return(i,r)=>(l(),w(z,{title:"Customer Satisfaction Index"},{header:E(()=>[K]),default:E(()=>[t("div",W,[t("div",X,[t("div",Z,[t("div",tt,[t("div",et,[st,t("div",ot,[t("div",at,[rt,t("span",nt,n(Number(a.percentage_vss_respondents||0).toFixed(2))+"%",1)]),t("div",it,[lt,t("span",ct,n(Number(a.csi_total||0).toFixed(2))+"%",1)]),t("div",dt,[pt,t("span",mt,n(Number(a.nps_total||0).toFixed(2))+"%",1)]),t("div",_t,[ut,t("span",ht,n(a.total_respondents??0),1)])])])]),t("div",bt,[gt,t("div",yt,[t("div",ft,[t("div",vt,[xt,p(t("select",{"onUpdate:modelValue":r[0]||(r[0]=o=>e.csi_type=o),class:"form-select"},Rt,512),[[m,e.csi_type]])]),e.csi_type=="By Month"?(l(),c("div",Et,[Bt,p(t("select",{"onUpdate:modelValue":r[1]||(r[1]=o=>e.selected_month=o),class:"form-select"},[(l(),c(g,null,y(A,o=>t("option",{key:o,value:o},n(o),9,Ct)),64))],512),[[m,e.selected_month]])])):f("",!0),e.csi_type=="By Quarter"?(l(),c("div",Nt,[Ut,p(t("select",{"onUpdate:modelValue":r[2]||(r[2]=o=>e.selected_quarter=o),class:"form-select"},Pt,512),[[m,e.selected_quarter]])])):f("",!0),e.csi_type?(l(),c("div",Qt,[It,p(t("select",{"onUpdate:modelValue":r[3]||(r[3]=o=>e.selected_year=o),class:"form-select"},[(l(!0),c(g,null,y(B.value,o=>(l(),c("option",{key:o,value:o},n(o),9,Ft))),128))],512),[[m,e.selected_year]])])):f("",!0),t("div",jt,[Dt,p(t("select",{"onUpdate:modelValue":r[4]||(r[4]=o=>e.client_type=o),class:"form-select"},[Vt,(l(),c(g,null,y(C,o=>t("option",{key:o,value:o},n(o),9,zt)),64))],512),[[m,e.client_type]])]),t("div",Gt,[Lt,p(t("select",{"onUpdate:modelValue":r[5]||(r[5]=o=>e.sex=o),class:"form-select"},[Ht,(l(),c(g,null,y(N,o=>t("option",{key:o,value:o},n(o),9,$t)),64))],512),[[m,e.sex]])]),t("div",Jt,[Kt,p(t("select",{"onUpdate:modelValue":r[6]||(r[6]=o=>e.age_group=o),class:"form-select"},[Wt,(l(),c(g,null,y(U,o=>t("option",{key:o,value:o},n(o),9,Xt)),64))],512),[[m,e.age_group]])]),t("div",{class:"col-md-4 d-flex align-items-end"},[t("button",{onClick:P,class:"btn btn-primary w-100 generate-btn"},[Zt,d(" Generate Report ")])])]),t("div",te,[t("span",ee,[se,d(" "+n(e.csi_type||"Not selected"),1)]),e.csi_type==="By Month"?(l(),c("span",oe,[ae,d(" "+n(e.selected_month||"-"),1)])):f("",!0),e.csi_type==="By Quarter"?(l(),c("span",re,[ne,d(" "+n(e.selected_quarter||"-"),1)])):f("",!0),t("span",ie,[le,d(" "+n(e.selected_year||"-"),1)]),t("span",ce,[de,d(" "+n(e.client_type||"All"),1)]),t("span",pe,[me,d(" "+n(e.sex||"All"),1)]),t("span",_e,[ue,d(" "+n(e.age_group||"All"),1)])])])]),_.value==!0&&e.csi_type?(l(),c("div",he,[t("div",be,[t("div",null,[ge,t("p",ye,n(O.value),1)]),t("div",fe,[p(t("select",{"onUpdate:modelValue":r[7]||(r[7]=o=>v.value=o),class:"form-select form-select-sm preview-format-select"},we,512),[[m,v.value]]),t("button",{onClick:Q,class:"btn btn-light preview-print-btn"},[ke,d(" Print Report ")])])]),t("div",Se,[v.value==="standard"?(l(),w(L,{key:0,form:e,data:{services_units:a.services_units,all_units_data:a.all_units_data,cc_data:a.cc_data,total_respondents:a.total_respondents,total_vss_respondents:a.total_vss_respondents,percentage_vss_respondents:a.percentage_vss_respondents,respondent_profile:a.respondent_profile,total_comments:a.total_comments,total_complaints:a.total_complaints,comments:a.comments,isPrinting:x.value,csi_total:a.csi_total,nps_total:a.nps_total,lsr_total:a.lsr_total,region:a.region}},null,8,["form","data"])):(l(),w(H,{key:1,form:e,data:{services_units:a.services_units,all_units_data:a.all_units_data,cc_data:a.cc_data,total_respondents:a.total_respondents,total_vss_respondents:a.total_vss_respondents,percentage_vss_respondents:a.percentage_vss_respondents,respondent_profile:a.respondent_profile,total_comments:a.total_comments,total_complaints:a.total_complaints,comments:a.comments,isPrinting:x.value,csi_total:a.csi_total,nps_total:a.nps_total,lsr_total:a.lsr_total,region:a.region}},null,8,["form","data"]))])])):(l(),c("div",Ae,Ce))])])])]),_:1}))}},je=J(Ne,[["__scopeId","data-v-3ecd02c5"]]);export{je as default};
