import{s as b}from"./vue-multiselect.css_vue_type_style_index_0_src_true_lang-3mFSuNqp.js";import k from"./ByUnitMonthly-CcwZhOND.js";import{P as x}from"./index-DajnbktX.js";import{I as B,d as f,i as V,q as C,o as d,e as y,a as e,b as _,u as g,g as i,n as S,f as h,c as O,F as N,p as I,m as P}from"./app-B5xFWbyn.js";import{_ as j}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./dost-logo-B4Huuyod.js";const n=t=>(I("data-v-46568918"),t=t(),P(),t),R={class:"modal-dialog modal-lg",role:"document"},M={class:"modal-content"},A={class:"modal-header bg-primary text-white"},F=n(()=>e("h5",{class:"modal-title"},[e("i",{class:"ri-user-line me-2"}),i(" Select Assignatoree ")],-1)),T={class:"modal-body"},U={class:"row mb-3"},$={class:"col-12"},q=n(()=>e("label",{class:"form-label"},"Prepared By: ",-1)),z={class:"row"},D={class:"col-12"},E=n(()=>e("label",{class:"form-label"},"Noted By:",-1)),G={class:"modal-footer"},H=n(()=>e("i",{class:"ri-close-line me-1"},null,-1)),J=n(()=>e("i",{class:"ri-printer-line me-1"},null,-1)),K={key:0,class:"modal-backdrop fade show"},L=B({__name:"Modal",props:{form:{type:Object,default:null},assignatorees:{type:Object,default:null},user:{type:Object,default:null},users:{type:Object,default:null},value:{type:Boolean,default:!1},data:{type:Object,default:null},generated:{type:Boolean}},emits:["input"],setup(t,{emit:v}){const c=v,p=t,r=f(!1);V(()=>p.value,s=>{r.value=s});const l=C({prepared_by:p.user,noted_by:{}}),m=s=>{c("input",s)},u=f(!1),w=async()=>{u.value=!0,await(await new x).print(document.querySelector(".print-id"),[`
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

        `]),setTimeout(()=>{c("input",!1)},100)};return(s,o)=>(d(),y(N,null,[e("div",{class:S(["modal fade",{show:r.value,"d-block":r.value}]),tabindex:"-1",role:"dialog"},[e("div",R,[e("div",M,[e("div",A,[F,e("button",{type:"button",class:"btn-close btn-close-white",onClick:o[0]||(o[0]=a=>m(!1)),"aria-label":"Close"})]),e("div",T,[e("div",U,[e("div",$,[q,_(g(b),{modelValue:l.prepared_by,"onUpdate:modelValue":o[1]||(o[1]=a=>l.prepared_by=a),options:t.users,multiple:!1,placeholder:"Select Prepared By",label:"name","track-by":"id","allow-empty":!1,class:"form-control p-0 border-0",style:{"min-width":"200px"}},null,8,["modelValue","options"])])]),e("div",z,[e("div",D,[E,_(g(b),{modelValue:l.noted_by,"onUpdate:modelValue":o[2]||(o[2]=a=>l.noted_by=a),options:t.assignatorees,multiple:!1,placeholder:"Select Noted By",label:"name","track-by":"name","allow-empty":!1},null,8,["modelValue","options"])])])]),e("div",G,[e("button",{type:"button",class:"btn btn-secondary",onClick:o[3]||(o[3]=a=>m(!1))},[H,i(" Cancel ")]),e("button",{type:"button",class:"btn btn-success",onClick:o[4]||(o[4]=a=>w())},[J,i(" Print Preview ")])])])])],2),r.value?(d(),y("div",K)):h("",!0),t.form.csi_type=="By Month"&&u.value&&t.data?(d(),O(k,{key:1,form:t.form,data:t.data,form_assignatorees:l},null,8,["form","data","form_assignatorees"])):h("",!0)],64))}}),te=j(L,[["__scopeId","data-v-46568918"]]);export{te as default};
