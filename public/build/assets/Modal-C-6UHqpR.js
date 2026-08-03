import{s as b}from"./vue-multiselect.css_vue_type_style_index_0_src_true_lang-wdOqwMCl.js";import k from"./ByUnitMonthly-BY8lSxpC.js";import{P as x}from"./index-DajnbktX.js";import{J as B,d as f,i as V,q as C,o as d,e as y,a as e,b as _,u as g,g as i,n as S,f as h,c as O,F as N,p as P,m as j}from"./app-CWKsFHPy.js";import{_ as I}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./dost-logo-B4Huuyod.js";const n=t=>(P("data-v-fee2ae5c"),t=t(),j(),t),R={class:"modal-dialog modal-lg",role:"document"},M={class:"modal-content"},A={class:"modal-header bg-primary text-white"},F=n(()=>e("h5",{class:"modal-title"},[e("i",{class:"ri-user-line me-2"}),i(" Select Assignatoree ")],-1)),T={class:"modal-body"},U={class:"row mb-3"},$={class:"col-12"},q=n(()=>e("label",{class:"form-label"},"Prepared By: ",-1)),D={class:"row"},z={class:"col-12"},E=n(()=>e("label",{class:"form-label"},"Noted By:",-1)),J={class:"modal-footer"},G=n(()=>e("i",{class:"ri-close-line me-1"},null,-1)),H=n(()=>e("i",{class:"ri-printer-line me-1"},null,-1)),K={key:0,class:"modal-backdrop fade show"},L=B({__name:"Modal",props:{form:{type:Object,default:null},assignatorees:{type:Object,default:null},user:{type:Object,default:null},users:{type:Object,default:null},value:{type:Boolean,default:!1},data:{type:Object,default:null},generated:{type:Boolean}},emits:["input"],setup(t,{emit:v}){const c=v,p=t,r=f(!1);V(()=>p.value,s=>{r.value=s});const l=C({prepared_by:p.user,noted_by:{}}),m=s=>{c("input",s)},u=f(!1),w=async()=>{u.value=!0,await(await new x).print(document.querySelector(".print-id"),[`
          @import url('https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;800&family=Roboto:wght@100;300;400;500;700;900&display=swap');
          * {
              font-family: 'Time New Roman'
          }
          .new-page {
              page-break-before: always;
          }
          .th-color{
              background-color: #8fd1e8;
          }
          .text-center{
            text-align: center;
          }
          .text-right{
            text-align:end
          }
          table {
            border-collapse: collapse;
            width: 100%; /* Optional: Set a width for the table */
          }

          tr, th, td {
            border: 1px solid rgb(145, 139, 139); /* Optional: Add a border for better visibility */
            padding: 3px; /* Optional: Add padding for better spacing */
          }
          .page-break {
            page-break-before: always; /* or page-break-after: always; */
          }

        `]),setTimeout(()=>{c("input",!1)},100)};return(s,a)=>(d(),y(N,null,[e("div",{class:S(["modal fade",{show:r.value,"d-block":r.value}]),tabindex:"-1",role:"dialog"},[e("div",R,[e("div",M,[e("div",A,[F,e("button",{type:"button",class:"btn-close btn-close-white",onClick:a[0]||(a[0]=o=>m(!1)),"aria-label":"Close"})]),e("div",T,[e("div",U,[e("div",$,[q,_(g(b),{modelValue:l.prepared_by,"onUpdate:modelValue":a[1]||(a[1]=o=>l.prepared_by=o),options:t.users,multiple:!1,placeholder:"Select Prepared By",label:"name","track-by":"id","allow-empty":!1,class:"form-control p-0 border-0",style:{"min-width":"200px"}},null,8,["modelValue","options"])])]),e("div",D,[e("div",z,[E,_(g(b),{modelValue:l.noted_by,"onUpdate:modelValue":a[2]||(a[2]=o=>l.noted_by=o),options:t.assignatorees,multiple:!1,placeholder:"Select Noted By",label:"name","track-by":"name","allow-empty":!1},null,8,["modelValue","options"])])])]),e("div",J,[e("button",{type:"button",class:"btn btn-secondary",onClick:a[3]||(a[3]=o=>m(!1))},[G,i(" Cancel ")]),e("button",{type:"button",class:"btn btn-success",onClick:a[4]||(a[4]=o=>w())},[H,i(" Print Preview ")])])])])],2),r.value?(d(),y("div",K)):h("",!0),(t.form.csi_type=="By Month"||t.form.csi_type=="By Date")&&u.value&&t.data?(d(),O(k,{key:1,form:t.form,data:t.data,form_assignatorees:l},null,8,["form","data","form_assignatorees"])):h("",!0)],64))}}),te=I(L,[["__scopeId","data-v-fee2ae5c"]]);export{te as default};
