this.wc=this.wc||{},this.wc.onboardingTaxNotice=function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}return n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=461)}({14:function(e,t){e.exports=window.wc.wcSettings},2:function(e,t){e.exports=window.wp.i18n},461:function(e,t,n){"use strict";n.r(t);var o=n(2),r=n(7),i=n(52),c=n.n(i),a=n(14);const u=e=>{if(e&&!e.disabled){return new Promise(e=>{window.requestAnimationFrame(e)}).then(()=>u(e))}return Promise.resolve(!0)},s=()=>{const e=document.querySelector(".woocommerce-save-button");e.classList.contains("has-tax")||u(e).then(()=>{document.querySelector(".wc_tax_rates .tips")&&(e.classList.add("has-tax"),Object(r.dispatch)("core/notices").createSuccessNotice(Object(o.__)("You've added your first tax rate!",'woocommerce'),{id:"WOOCOMMERCE_ONBOARDING_TAX_NOTICE",actions:[{url:Object(a.getAdminLink)("admin.php?page=wc-admin"),label:Object(o.__)("Continue setup.",'woocommerce')}]}))})};c()(()=>{const e=document.querySelector(".woocommerce-save-button");window.htmlSettingsTaxLocalizeScript&&window.htmlSettingsTaxLocalizeScript.rates&&!window.htmlSettingsTaxLocalizeScript.rates.length&&e&&e.addEventListener("click",s)})},52:function(e,t){e.exports=window.wp.domReady},7:function(e,t){e.exports=window.wp.data}});