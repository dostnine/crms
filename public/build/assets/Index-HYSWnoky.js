import{q as F,D as A,d as h,G as P,o as n,c as b,w as S,a as t,t as l,k as p,x as _,e as c,F as g,h as f,f as y,g as d,O as x,p as D,m as V}from"./app-B5xFWbyn.js";import{A as j}from"./AppLayout-o3Aee0GO.js";import{P as z}from"./index-DajnbktX.js";import{S as w}from"./sweetalert2.all-DB9r3rJe.js";import G from"./Content-Ovk8iF4Q.js";import L from"./AltContent-BvQdjTvd.js";import"./vue-multiselect.css_vue_type_style_index_0_src_true_lang-3mFSuNqp.js";import{A as H}from"./aos-CEYC6DfY.js";import{_ as $}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./dost-logo-B4Huuyod.js";const s=u=>(D("data-v-6328a470"),u=u(),V(),u),J=s(()=>t("div",{class:"page-heading"},[t("h2",{class:"page-heading-title"},"Customer Satisfaction Index - All Services Units"),t("p",{class:"page-heading-subtitle mb-0"},"Generate monthly, quarterly, or yearly consolidated reports with profile filters.")],-1)),K={class:"container-fluid py-4 csi-all-units-page"},W={class:"row justify-content-center"},X={class:"col-12 col-xl-11"},Z={class:"summary-hero mb-4","data-aos":"fade-up"},tt={class:"summary-hero-content"},et=s(()=>t("div",null,[t("p",{class:"summary-kicker mb-1"},"Customer Experience Analytics"),t("h3",{class:"summary-title mb-1"},"All Services Units Dashboard"),t("p",{class:"summary-text mb-0"}," Build a consolidated CSI snapshot by period, then print a formatted report. ")],-1)),st={class:"summary-stats"},ot={class:"stat-pill"},at=s(()=>t("span",{class:"stat-label"},"CSAT",-1)),rt={class:"stat-value"},lt={class:"stat-pill"},nt=s(()=>t("span",{class:"stat-label"},"CSI",-1)),it={class:"stat-value"},ct={class:"stat-pill"},dt=s(()=>t("span",{class:"stat-label"},"NPS",-1)),pt={class:"stat-value"},_t={class:"stat-pill"},mt=s(()=>t("span",{class:"stat-label"},"Respondents",-1)),ut={class:"stat-value"},ht={class:"card filter-card shadow border-0 mb-4","data-aos":"fade-up"},gt=s(()=>t("div",{class:"card-header filter-card-header text-white"},[t("h4",{class:"card-title mb-0 d-flex align-items-center"},[t("i",{class:"ri-filter-3-line me-2"}),d(" Generate Report ")])],-1)),ft={class:"card-body"},yt={class:"row g-3 align-items-end"},vt={class:"col-md-4"},bt=s(()=>t("label",{class:"form-label fw-semibold"},"Report Type",-1)),xt=s(()=>t("option",{value:""},"Select Report Type",-1)),wt=s(()=>t("option",{value:"By Month"},"By Month",-1)),kt=s(()=>t("option",{value:"By Quarter"},"By Quarter",-1)),At=s(()=>t("option",{value:"By Year/Annual"},"By Year/Annual",-1)),St=[xt,wt,kt,At],Rt={key:0,class:"col-md-4"},Bt=s(()=>t("label",{class:"form-label fw-semibold"},"Month",-1)),Ct=["value"],Et={key:1,class:"col-md-4"},Nt=s(()=>t("label",{class:"form-label fw-semibold"},"Quarter",-1)),Ut=s(()=>t("option",{value:""},"Select Quarter",-1)),Mt=s(()=>t("option",{value:"FIRST QUARTER"},"First Quarter",-1)),qt=s(()=>t("option",{value:"SECOND QUARTER"},"Second Quarter",-1)),Tt=s(()=>t("option",{value:"THIRD QUARTER"},"Third Quarter",-1)),Yt=s(()=>t("option",{value:"FOURTH QUARTER"},"Fourth Quarter",-1)),Ot=[Ut,Mt,qt,Tt,Yt],Qt={key:2,class:"col-md-4"},It=s(()=>t("label",{class:"form-label fw-semibold"},"Year",-1)),Ft=["value"],Pt={class:"col-md-4"},Dt=s(()=>t("label",{class:"form-label fw-semibold"},"Client Type",-1)),Vt=s(()=>t("option",{value:null},"All",-1)),jt=["value"],zt={class:"col-md-4"},Gt=s(()=>t("label",{class:"form-label fw-semibold"},"Sex",-1)),Lt=s(()=>t("option",{value:null},"All",-1)),Ht=["value"],$t={class:"col-md-4"},Jt=s(()=>t("label",{class:"form-label fw-semibold"},"Age Group",-1)),Kt=s(()=>t("option",{value:null},"All",-1)),Wt=["value"],Xt=s(()=>t("i",{class:"ri-file-chart-line me-2"},null,-1)),Zt={class:"active-filters mt-3"},te={class:"filter-chip"},ee=s(()=>t("strong",null,"Type:",-1)),se={key:0,class:"filter-chip"},oe=s(()=>t("strong",null,"Month:",-1)),ae={key:1,class:"filter-chip"},re=s(()=>t("strong",null,"Quarter:",-1)),le={class:"filter-chip"},ne=s(()=>t("strong",null,"Year:",-1)),ie={class:"filter-chip"},ce=s(()=>t("strong",null,"Client:",-1)),de={class:"filter-chip"},pe=s(()=>t("strong",null,"Sex:",-1)),_e={class:"filter-chip"},me=s(()=>t("strong",null,"Age:",-1)),ue={key:0,class:"card mt-4 shadow border-0 report-preview-card","data-aos":"fade-in"},he={class:"card-header preview-header d-flex justify-content-between align-items-center"},ge=s(()=>t("h5",{class:"card-title mb-1 text-white"},[t("i",{class:"ri-file-chart-line me-2"}),d(" Report Preview ")],-1)),fe={class:"mb-0 preview-period text-white-50"},ye={class:"d-flex align-items-center gap-3"},ve=s(()=>t("option",{value:"standard"},"Current Format",-1)),be=s(()=>t("option",{value:"alternative"},"Alternative Format",-1)),xe=[ve,be],we=s(()=>t("i",{class:"ri-printer-line me-2"},null,-1)),ke={class:"card-body print-id"},Ae={key:1,class:"empty-state-card text-center mt-4","data-aos":"fade-in"},Se=s(()=>t("div",{class:"empty-state-icon mb-2"},[t("i",{class:"ri-file-chart-line"})],-1)),Re=s(()=>t("h5",{class:"mb-1"},"No Report Preview Yet",-1)),Be=s(()=>t("p",{class:"mb-0"},[d("Choose your filters above, then click "),t("strong",null,"Generate Report"),d(".")],-1)),Ce=[Se,Re,Be],Ee={__name:"Index",props:{services_units:Object,cc_data:Object,all_units_data:Object,region:Object,csi_total:Number,nps_total:Number,lsr_total:Number,total_respondents:Number,total_vss_respondents:Number,percentage_vss_respondents:Number,total_comments:Number,total_complaints:Number,comments:Object,respondent_profile:Object,request:Object},setup(u){H.init();const a=u,e=F({date_from:null,date_to:null,csi_type:null,selected_month:null,selected_year:null,selected_quarter:null,client_type:null,sex:null,age_group:null,comments_complaints:null,analysis:null,service:[]}),R=A(()=>{const i=new Date().getFullYear();return Array.from({length:9},(o,I)=>(i-I).toString())}),k=["JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY","AUGUST","SEPTEMBER","OCTOBER","NOVEMBER","DECEMBER"],B=["Internal","External"],C=["Male","Female","Prefer not to say"],E=["19 or lower","20-34","35-49","50-64","60+","Prefer not to say"],N=h(U());function U(){return new Date().getFullYear().toString()}const M=h(q());function q(){return k[new Date().getMonth()]}const m=h(!1),v=h("standard");P(()=>{const i=a.request||{};e.csi_type=i.csi_type||null,e.selected_month=i.selected_month||M.value,e.selected_year=i.selected_year||N.value,e.selected_quarter=i.selected_quarter||null,e.client_type=i.client_type||null,e.sex=i.sex||null,e.age_group=i.age_group||null,m.value=!!i.csi_type});const T=A(()=>e.csi_type==="By Quarter"&&e.selected_quarter?`${e.selected_quarter} ${e.selected_year||""}`.trim():e.csi_type==="By Year/Annual"?e.selected_year||"":`${e.selected_month||""} ${e.selected_year||""}`.trim()),Y=async()=>{if(!e.csi_type){w.fire({title:"Error",icon:"error",text:"Please select a report type first!"}),m.value=!1;return}m.value=!0,e.csi_type=="By Month"?(e.selected_quarter="",x.get("/csi/generate/all-units/monthly",e,{preserveState:!0,preserveScroll:!0})):e.csi_type=="By Quarter"?(e.selected_month="",e.selected_quarter?x.get("/csi/generate/all-units/monthly",e,{preserveState:!0,preserveScroll:!0}):(m.value=!1,w.fire({title:"Error",icon:"error",text:"Please select a quarter first!"}))):e.csi_type=="By Year/Annual"&&(e.selected_quarter="",e.selected_year?x.get("/csi/generate/all-units/monthly",e,{preserveState:!0,preserveScroll:!0}):(m.value=!1,w.fire({title:"Error",icon:"error",text:"Please select year first!"})))},O=h(!1),Q=async()=>{O.value=!0,(await new z).print(document.querySelector(".print-id"),[` 
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
            display: block !important;
          }
          .alt-header,
          .alt-title,
          .alt-subtitle {
            text-align: center !important;
            width: 100%;
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
        `])};return(i,r)=>(n(),b(j,{title:"Customer Satisfaction Index"},{header:S(()=>[J]),default:S(()=>[t("div",K,[t("div",W,[t("div",X,[t("div",Z,[t("div",tt,[et,t("div",st,[t("div",ot,[at,t("span",rt,l(Number(a.percentage_vss_respondents||0).toFixed(2))+"%",1)]),t("div",lt,[nt,t("span",it,l(Number(a.csi_total||0).toFixed(2))+"%",1)]),t("div",ct,[dt,t("span",pt,l(Number(a.nps_total||0).toFixed(2))+"%",1)]),t("div",_t,[mt,t("span",ut,l(a.total_respondents??0),1)])])])]),t("div",ht,[gt,t("div",ft,[t("div",yt,[t("div",vt,[bt,p(t("select",{"onUpdate:modelValue":r[0]||(r[0]=o=>e.csi_type=o),class:"form-select"},St,512),[[_,e.csi_type]])]),e.csi_type=="By Month"?(n(),c("div",Rt,[Bt,p(t("select",{"onUpdate:modelValue":r[1]||(r[1]=o=>e.selected_month=o),class:"form-select"},[(n(),c(g,null,f(k,o=>t("option",{key:o,value:o},l(o),9,Ct)),64))],512),[[_,e.selected_month]])])):y("",!0),e.csi_type=="By Quarter"?(n(),c("div",Et,[Nt,p(t("select",{"onUpdate:modelValue":r[2]||(r[2]=o=>e.selected_quarter=o),class:"form-select"},Ot,512),[[_,e.selected_quarter]])])):y("",!0),e.csi_type?(n(),c("div",Qt,[It,p(t("select",{"onUpdate:modelValue":r[3]||(r[3]=o=>e.selected_year=o),class:"form-select"},[(n(!0),c(g,null,f(R.value,o=>(n(),c("option",{key:o,value:o},l(o),9,Ft))),128))],512),[[_,e.selected_year]])])):y("",!0),t("div",Pt,[Dt,p(t("select",{"onUpdate:modelValue":r[4]||(r[4]=o=>e.client_type=o),class:"form-select"},[Vt,(n(),c(g,null,f(B,o=>t("option",{key:o,value:o},l(o),9,jt)),64))],512),[[_,e.client_type]])]),t("div",zt,[Gt,p(t("select",{"onUpdate:modelValue":r[5]||(r[5]=o=>e.sex=o),class:"form-select"},[Lt,(n(),c(g,null,f(C,o=>t("option",{key:o,value:o},l(o),9,Ht)),64))],512),[[_,e.sex]])]),t("div",$t,[Jt,p(t("select",{"onUpdate:modelValue":r[6]||(r[6]=o=>e.age_group=o),class:"form-select"},[Kt,(n(),c(g,null,f(E,o=>t("option",{key:o,value:o},l(o),9,Wt)),64))],512),[[_,e.age_group]])]),t("div",{class:"col-md-4 d-flex align-items-end"},[t("button",{onClick:Y,class:"btn btn-primary w-100 generate-btn"},[Xt,d(" Generate Report ")])])]),t("div",Zt,[t("span",te,[ee,d(" "+l(e.csi_type||"Not selected"),1)]),e.csi_type==="By Month"?(n(),c("span",se,[oe,d(" "+l(e.selected_month||"-"),1)])):y("",!0),e.csi_type==="By Quarter"?(n(),c("span",ae,[re,d(" "+l(e.selected_quarter||"-"),1)])):y("",!0),t("span",le,[ne,d(" "+l(e.selected_year||"-"),1)]),t("span",ie,[ce,d(" "+l(e.client_type||"All"),1)]),t("span",de,[pe,d(" "+l(e.sex||"All"),1)]),t("span",_e,[me,d(" "+l(e.age_group||"All"),1)])])])]),m.value==!0&&e.csi_type?(n(),c("div",ue,[t("div",he,[t("div",null,[ge,t("p",fe,l(T.value),1)]),t("div",ye,[p(t("select",{"onUpdate:modelValue":r[7]||(r[7]=o=>v.value=o),class:"form-select form-select-sm preview-format-select"},xe,512),[[_,v.value]]),t("button",{onClick:Q,class:"btn btn-light preview-print-btn"},[we,d(" Print Report ")])])]),t("div",ke,[v.value==="standard"?(n(),b(G,{key:0,form:e,data:{services_units:a.services_units,all_units_data:a.all_units_data,cc_data:a.cc_data,total_respondents:a.total_respondents,total_vss_respondents:a.total_vss_respondents,percentage_vss_respondents:a.percentage_vss_respondents,respondent_profile:a.respondent_profile,total_comments:a.total_comments,total_complaints:a.total_complaints,comments:a.comments,csi_total:a.csi_total,nps_total:a.nps_total,lsr_total:a.lsr_total,region:a.region}},null,8,["form","data"])):(n(),b(L,{key:1,form:e,data:{services_units:a.services_units,all_units_data:a.all_units_data,cc_data:a.cc_data,total_respondents:a.total_respondents,total_vss_respondents:a.total_vss_respondents,percentage_vss_respondents:a.percentage_vss_respondents,respondent_profile:a.respondent_profile,total_comments:a.total_comments,total_complaints:a.total_complaints,comments:a.comments,csi_total:a.csi_total,nps_total:a.nps_total,lsr_total:a.lsr_total,region:a.region}},null,8,["form","data"]))])])):(n(),c("div",Ae,Ce))])])])]),_:1}))}},Pe=$(Ee,[["__scopeId","data-v-6328a470"]]);export{Pe as default};
