(()=>{var Dn=Object.defineProperty;var Pt=Object.getOwnPropertySymbols;var Mn=Object.prototype.hasOwnProperty,Pn=Object.prototype.propertyIsEnumerable;var Bt=Math.pow,Ut=(s,B,T)=>B in s?Dn(s,B,{enumerable:!0,configurable:!0,writable:!0,value:T}):s[B]=T,pt=(s,B)=>{for(var T in B||(B={}))Mn.call(B,T)&&Ut(s,T,B[T]);if(Pt)for(var T of Pt(B))Pn.call(B,T)&&Ut(s,T,B[T]);return s};(()=>{var s=Object.create,B=Object.defineProperty,T=Object.getOwnPropertyDescriptor,g=Object.getOwnPropertyNames,d=Object.getPrototypeOf,C=Object.prototype.hasOwnProperty,o=(f,D)=>()=>(D||f((D={exports:{}}).exports,D),D.exports),k=(f,D,Q,ee)=>{if(D&&typeof D=="object"||typeof D=="function")for(let ne of g(D))!C.call(f,ne)&&ne!==Q&&B(f,ne,{get:()=>D[ne],enumerable:!(ee=T(D,ne))||ee.enumerable});return f},W=(f,D,Q)=>(Q=f!=null?s(d(f)):{},k(D||!f||!f.__esModule?B(Q,"default",{value:f,enumerable:!0}):Q,f)),Y=o((f,D)=>{function Q(t){return t instanceof Map?t.clear=t.delete=t.set=function(){throw new Error("map is read-only")}:t instanceof Set&&(t.add=t.clear=t.delete=function(){throw new Error("set is read-only")}),Object.freeze(t),Object.getOwnPropertyNames(t).forEach(L=>{let U=t[L],q=typeof U;(q==="object"||q==="function")&&!Object.isFrozen(U)&&Q(U)}),t}var ee=class{constructor(t){t.data===void 0&&(t.data={}),this.data=t.data,this.isMatchIgnored=!1}ignoreMatch(){this.isMatchIgnored=!0}};function ne(t){return t.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#x27;")}function se(t,...L){let U=Object.create(null);for(let q in t)U[q]=t[q];return L.forEach(function(q){for(let ce in q)U[ce]=q[ce]}),U}var le="</span>",ie=t=>!!t.scope,Re=(t,{prefix:L})=>{if(t.startsWith("language:"))return t.replace("language:","language-");if(t.includes(".")){let U=t.split(".");return[`${L}${U.shift()}`,...U.map((q,ce)=>`${q}${"_".repeat(ce+1)}`)].join(" ")}return`${L}${t}`},Ee=class{constructor(t,L){this.buffer="",this.classPrefix=L.classPrefix,t.walk(this)}addText(t){this.buffer+=ne(t)}openNode(t){if(!ie(t))return;let L=Re(t.scope,{prefix:this.classPrefix});this.span(L)}closeNode(t){ie(t)&&(this.buffer+=le)}value(){return this.buffer}span(t){this.buffer+=`<span class="${t}">`}},Ie=(t={})=>{let L={children:[]};return Object.assign(L,t),L},Me=class Ht{constructor(){this.rootNode=Ie(),this.stack=[this.rootNode]}get top(){return this.stack[this.stack.length-1]}get root(){return this.rootNode}add(L){this.top.children.push(L)}openNode(L){let U=Ie({scope:L});this.add(U),this.stack.push(U)}closeNode(){if(this.stack.length>1)return this.stack.pop()}closeAllNodes(){for(;this.closeNode(););}toJSON(){return JSON.stringify(this.rootNode,null,4)}walk(L){return this.constructor._walk(L,this.rootNode)}static _walk(L,U){return typeof U=="string"?L.addText(U):U.children&&(L.openNode(U),U.children.forEach(q=>this._walk(L,q)),L.closeNode(U)),L}static _collapse(L){typeof L!="string"&&L.children&&(L.children.every(U=>typeof U=="string")?L.children=[L.children.join("")]:L.children.forEach(U=>{Ht._collapse(U)}))}},Ne=class extends Me{constructor(t){super(),this.options=t}addText(t){t!==""&&this.add(t)}startScope(t){this.openNode(t)}endScope(){this.closeNode()}__addSublanguage(t,L){let U=t.root;L&&(U.scope=`language:${L}`),this.add(U)}toHTML(){return new Ee(this,this.options).value()}finalize(){return this.closeAllNodes(),!0}};function ge(t){return t?typeof t=="string"?t:t.source:null}function Ce(t){return be("(?=",t,")")}function ke(t){return be("(?:",t,")*")}function Pe(t){return be("(?:",t,")?")}function be(...t){return t.map(L=>ge(L)).join("")}function De(t){let L=t[t.length-1];return typeof L=="object"&&L.constructor===Object?(t.splice(t.length-1,1),L):{}}function ve(...t){return"("+(De(t).capture?"":"?:")+t.map(L=>ge(L)).join("|")+")"}function we(t){return new RegExp(t.toString()+"|").exec("").length-1}function pe(t,L){let U=t&&t.exec(L);return U&&U.index===0}var xe=/\[(?:[^\\\]]|\\.)*\]|\(\??|\\([1-9][0-9]*)|\\./;function Se(t,{joinWith:L}){let U=0;return t.map(q=>{U+=1;let ce=U,de=ge(q),F="";for(;de.length>0;){let H=xe.exec(de);if(!H){F+=de;break}F+=de.substring(0,H.index),de=de.substring(H.index+H[0].length),H[0][0]==="\\"&&H[1]?F+="\\"+String(Number(H[1])+ce):(F+=H[0],H[0]==="("&&U++)}return F}).map(q=>`(${q})`).join(L)}var Fe=/\b\B/,_e="[a-zA-Z]\\w*",je="[a-zA-Z_]\\w*",Xe="\\b\\d+(\\.\\d+)?",Ke="(-?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)",ye="\\b(0b[01]+)",Le="!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~",Ue=(t={})=>{let L=/^#![ ]*\//;return t.binary&&(t.begin=be(L,/.*\b/,t.binary,/\b.*/)),se({scope:"meta",begin:L,end:/$/,relevance:0,"on:begin":(U,q)=>{U.index!==0&&q.ignoreMatch()}},t)},Qe={begin:"\\\\[\\s\\S]",relevance:0},Ae={scope:"string",begin:"'",end:"'",illegal:"\\n",contains:[Qe]},Ve={scope:"string",begin:'"',end:'"',illegal:"\\n",contains:[Qe]},Ze={begin:/\b(a|an|the|are|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such|will|you|your|they|like|more)\b/},$e=function(t,L,U={}){let q=se({scope:"comment",begin:t,end:L,contains:[]},U);q.contains.push({scope:"doctag",begin:"[ ]*(?=(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):)",end:/(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):/,excludeBegin:!0,relevance:0});let ce=ve("I","a","is","so","us","to","at","if","in","it","on",/[A-Za-z]+['](d|ve|re|ll|t|s|n)/,/[A-Za-z]+[-][a-z]+/,/[A-Za-z][a-z]{2,}/);return q.contains.push({begin:be(/[ ]+/,"(",ce,/[.]?[:]?([.][ ]|[ ])/,"){3}")}),q},nt=$e("//","$"),it=$e("/\\*","\\*/"),Wt=$e("#","$"),Gt={scope:"number",begin:Xe,relevance:0},Ft={scope:"number",begin:Ke,relevance:0},jt={scope:"number",begin:ye,relevance:0},Vt={scope:"regexp",begin:/\/(?=[^/\n]*\/)/,end:/\/[gimuy]*/,contains:[Qe,{begin:/\[/,end:/\]/,relevance:0,contains:[Qe]}]},$t={scope:"title",begin:_e,relevance:0},zt={scope:"title",begin:je,relevance:0},Yt={begin:"\\.\\s*"+je,relevance:0},Xt=function(t){return Object.assign(t,{"on:begin":(L,U)=>{U.data._beginMatch=L[1]},"on:end":(L,U)=>{U.data._beginMatch!==L[1]&&U.ignoreMatch()}})},at=Object.freeze({__proto__:null,APOS_STRING_MODE:Ae,BACKSLASH_ESCAPE:Qe,BINARY_NUMBER_MODE:jt,BINARY_NUMBER_RE:ye,COMMENT:$e,C_BLOCK_COMMENT_MODE:it,C_LINE_COMMENT_MODE:nt,C_NUMBER_MODE:Ft,C_NUMBER_RE:Ke,END_SAME_AS_BEGIN:Xt,HASH_COMMENT_MODE:Wt,IDENT_RE:_e,MATCH_NOTHING_RE:Fe,METHOD_GUARD:Yt,NUMBER_MODE:Gt,NUMBER_RE:Xe,PHRASAL_WORDS_MODE:Ze,QUOTE_STRING_MODE:Ve,REGEXP_MODE:Vt,RE_STARTERS_RE:Le,SHEBANG:Ue,TITLE_MODE:$t,UNDERSCORE_IDENT_RE:je,UNDERSCORE_TITLE_MODE:zt});function Kt(t,L){t.input[t.index-1]==="."&&L.ignoreMatch()}function Qt(t,L){t.className!==void 0&&(t.scope=t.className,delete t.className)}function Zt(t,L){L&&t.beginKeywords&&(t.begin="\\b("+t.beginKeywords.split(" ").join("|")+")(?!\\.)(?=\\b|\\s)",t.__beforeBegin=Kt,t.keywords=t.keywords||t.beginKeywords,delete t.beginKeywords,t.relevance===void 0&&(t.relevance=0))}function qt(t,L){Array.isArray(t.illegal)&&(t.illegal=ve(...t.illegal))}function Jt(t,L){if(t.match){if(t.begin||t.end)throw new Error("begin & end are not supported with match");t.begin=t.match,delete t.match}}function en(t,L){t.relevance===void 0&&(t.relevance=1)}var tn=(t,L)=>{if(!t.beforeMatch)return;if(t.starts)throw new Error("beforeMatch cannot be used with starts");let U=Object.assign({},t);Object.keys(t).forEach(q=>{delete t[q]}),t.keywords=U.keywords,t.begin=be(U.beforeMatch,Ce(U.begin)),t.starts={relevance:0,contains:[Object.assign(U,{endsParent:!0})]},t.relevance=0,delete U.beforeMatch},nn=["of","and","for","in","not","or","if","then","parent","list","value"],sn="keyword";function Rt(t,L,U=sn){let q=Object.create(null);return typeof t=="string"?ce(U,t.split(" ")):Array.isArray(t)?ce(U,t):Object.keys(t).forEach(function(de){Object.assign(q,Rt(t[de],L,de))}),q;function ce(de,F){L&&(F=F.map(H=>H.toLowerCase())),F.forEach(function(H){let X=H.split("|");q[X[0]]=[de,an(X[0],X[1])]})}}function an(t,L){return L?Number(L):rn(t)?0:1}function rn(t){return nn.includes(t.toLowerCase())}var Nt={},qe=t=>{console.error(t)},Lt=(t,...L)=>{console.log(`WARN: ${t}`,...L)},et=(t,L)=>{Nt[`${t}/${L}`]||(console.log(`Deprecated as of ${t}. ${L}`),Nt[`${t}/${L}`]=!0)},rt=new Error;function _t(t,L,{key:U}){let q=0,ce=t[U],de={},F={};for(let H=1;H<=L.length;H++)F[H+q]=ce[H],de[H+q]=!0,q+=we(L[H-1]);t[U]=F,t[U]._emit=de,t[U]._multi=!0}function on(t){if(Array.isArray(t.begin)){if(t.skip||t.excludeBegin||t.returnBegin)throw qe("skip, excludeBegin, returnBegin not compatible with beginScope: {}"),rt;if(typeof t.beginScope!="object"||t.beginScope===null)throw qe("beginScope must be object"),rt;_t(t,t.begin,{key:"beginScope"}),t.begin=Se(t.begin,{joinWith:""})}}function ln(t){if(Array.isArray(t.end)){if(t.skip||t.excludeEnd||t.returnEnd)throw qe("skip, excludeEnd, returnEnd not compatible with endScope: {}"),rt;if(typeof t.endScope!="object"||t.endScope===null)throw qe("endScope must be object"),rt;_t(t,t.end,{key:"endScope"}),t.end=Se(t.end,{joinWith:""})}}function cn(t){t.scope&&typeof t.scope=="object"&&t.scope!==null&&(t.beginScope=t.scope,delete t.scope)}function dn(t){cn(t),typeof t.beginScope=="string"&&(t.beginScope={_wrap:t.beginScope}),typeof t.endScope=="string"&&(t.endScope={_wrap:t.endScope}),on(t),ln(t)}function un(t){function L(F,H){return new RegExp(ge(F),"m"+(t.case_insensitive?"i":"")+(t.unicodeRegex?"u":"")+(H?"g":""))}class U{constructor(){this.matchIndexes={},this.regexes=[],this.matchAt=1,this.position=0}addRule(H,X){X.position=this.position++,this.matchIndexes[this.matchAt]=X,this.regexes.push([X,H]),this.matchAt+=we(H)+1}compile(){this.regexes.length===0&&(this.exec=()=>null);let H=this.regexes.map(X=>X[1]);this.matcherRe=L(Se(H,{joinWith:"|"}),!0),this.lastIndex=0}exec(H){this.matcherRe.lastIndex=this.lastIndex;let X=this.matcherRe.exec(H);if(!X)return null;let me=X.findIndex((st,Et)=>Et>0&&st!==void 0),ue=this.matchIndexes[me];return X.splice(0,me),Object.assign(X,ue)}}class q{constructor(){this.rules=[],this.multiRegexes=[],this.count=0,this.lastIndex=0,this.regexIndex=0}getMatcher(H){if(this.multiRegexes[H])return this.multiRegexes[H];let X=new U;return this.rules.slice(H).forEach(([me,ue])=>X.addRule(me,ue)),X.compile(),this.multiRegexes[H]=X,X}resumingScanAtSamePosition(){return this.regexIndex!==0}considerAll(){this.regexIndex=0}addRule(H,X){this.rules.push([H,X]),X.type==="begin"&&this.count++}exec(H){let X=this.getMatcher(this.regexIndex);X.lastIndex=this.lastIndex;let me=X.exec(H);if(this.resumingScanAtSamePosition()&&!(me&&me.index===this.lastIndex)){let ue=this.getMatcher(0);ue.lastIndex=this.lastIndex+1,me=ue.exec(H)}return me&&(this.regexIndex+=me.position+1,this.regexIndex===this.count&&this.considerAll()),me}}function ce(F){let H=new q;return F.contains.forEach(X=>H.addRule(X.begin,{rule:X,type:"begin"})),F.terminatorEnd&&H.addRule(F.terminatorEnd,{type:"end"}),F.illegal&&H.addRule(F.illegal,{type:"illegal"}),H}function de(F,H){let X=F;if(F.isCompiled)return X;[Qt,Jt,dn,tn].forEach(ue=>ue(F,H)),t.compilerExtensions.forEach(ue=>ue(F,H)),F.__beforeBegin=null,[Zt,qt,en].forEach(ue=>ue(F,H)),F.isCompiled=!0;let me=null;return typeof F.keywords=="object"&&F.keywords.$pattern&&(F.keywords=Object.assign({},F.keywords),me=F.keywords.$pattern,delete F.keywords.$pattern),me=me||/\w+/,F.keywords&&(F.keywords=Rt(F.keywords,t.case_insensitive)),X.keywordPatternRe=L(me,!0),H&&(F.begin||(F.begin=/\B|\b/),X.beginRe=L(X.begin),!F.end&&!F.endsWithParent&&(F.end=/\B|\b/),F.end&&(X.endRe=L(X.end)),X.terminatorEnd=ge(X.end)||"",F.endsWithParent&&H.terminatorEnd&&(X.terminatorEnd+=(F.end?"|":"")+H.terminatorEnd)),F.illegal&&(X.illegalRe=L(F.illegal)),F.contains||(F.contains=[]),F.contains=[].concat(...F.contains.map(function(ue){return pn(ue==="self"?F:ue)})),F.contains.forEach(function(ue){de(ue,X)}),F.starts&&de(F.starts,H),X.matcher=ce(X),X}if(t.compilerExtensions||(t.compilerExtensions=[]),t.contains&&t.contains.includes("self"))throw new Error("ERR: contains `self` is not supported at the top-level of a language.  See documentation.");return t.classNameAliases=se(t.classNameAliases||{}),de(t)}function At(t){return t?t.endsWithParent||At(t.starts):!1}function pn(t){return t.variants&&!t.cachedVariants&&(t.cachedVariants=t.variants.map(function(L){return se(t,{variants:null},L)})),t.cachedVariants?t.cachedVariants:At(t)?se(t,{starts:t.starts?se(t.starts):null}):Object.isFrozen(t)?se(t):t}var hn="11.11.1",En=class extends Error{constructor(t,L){super(t),this.name="HTMLInjectionError",this.html=L}},ht=ne,St=se,yt=Symbol("nomatch"),gn=7,Ot=function(t){let L=Object.create(null),U=Object.create(null),q=[],ce=!0,de="Could not find the language '{}', did you forget to load/include a language module?",F={disableAutodetect:!0,name:"Plain text",contains:[]},H={ignoreUnescapedHTML:!1,throwUnescapedHTML:!1,noHighlightRe:/^(no-?highlight)$/i,languageDetectRe:/\blang(?:uage)?-([\w-]+)\b/i,classPrefix:"hljs-",cssSelector:"pre code",languages:null,__emitter:Ne};function X(I){return H.noHighlightRe.test(I)}function me(I){let $=I.className+" ";$+=I.parentNode?I.parentNode.className:"";let te=H.languageDetectRe.exec($);if(te){let re=ze(te[1]);return re||(Lt(de.replace("{}",te[1])),Lt("Falling back to no-highlight mode for this block.",I)),re?te[1]:"no-highlight"}return $.split(/\s+/).find(re=>X(re)||ze(re))}function ue(I,$,te){let re="",he="";typeof $=="object"?(re=I,te=$.ignoreIllegals,he=$.language):(et("10.7.0","highlight(lang, code, ...args) has been deprecated."),et("10.7.0",`Please use highlight(code, options) instead.
https://github.com/highlightjs/highlight.js/issues/2277`),he=I,re=$),te===void 0&&(te=!0);let Be={code:re,language:he};lt("before:highlight",Be);let Ye=Be.result?Be.result:st(Be.language,Be.code,te);return Ye.code=Be.code,lt("after:highlight",Ye),Ye}function st(I,$,te,re){let he=Object.create(null);function Be(P,j){return P.keywords[j]}function Ye(){if(!K.keywords){fe.addText(oe);return}let P=0;K.keywordPatternRe.lastIndex=0;let j=K.keywordPatternRe.exec(oe),J="";for(;j;){J+=oe.substring(P,j.index);let ae=We.case_insensitive?j[0].toLowerCase():j[0],Te=Be(K,ae);if(Te){let[Ge,xn]=Te;if(fe.addText(J),J="",he[ae]=(he[ae]||0)+1,he[ae]<=gn&&(ut+=xn),Ge.startsWith("_"))J+=j[0];else{let kn=We.classNameAliases[Ge]||Ge;He(j[0],kn)}}else J+=j[0];P=K.keywordPatternRe.lastIndex,j=K.keywordPatternRe.exec(oe)}J+=oe.substring(P),fe.addText(J)}function ct(){if(oe==="")return;let P=null;if(typeof K.subLanguage=="string"){if(!L[K.subLanguage]){fe.addText(oe);return}P=st(K.subLanguage,oe,!0,Mt[K.subLanguage]),Mt[K.subLanguage]=P._top}else P=gt(oe,K.subLanguage.length?K.subLanguage:null);K.relevance>0&&(ut+=P.relevance),fe.__addSublanguage(P._emitter,P.language)}function Oe(){K.subLanguage!=null?ct():Ye(),oe=""}function He(P,j){P!==""&&(fe.startScope(j),fe.addText(P),fe.endScope())}function wt(P,j){let J=1,ae=j.length-1;for(;J<=ae;){if(!P._emit[J]){J++;continue}let Te=We.classNameAliases[P[J]]||P[J],Ge=j[J];Te?He(Ge,Te):(oe=Ge,Ye(),oe=""),J++}}function xt(P,j){return P.scope&&typeof P.scope=="string"&&fe.openNode(We.classNameAliases[P.scope]||P.scope),P.beginScope&&(P.beginScope._wrap?(He(oe,We.classNameAliases[P.beginScope._wrap]||P.beginScope._wrap),oe=""):P.beginScope._multi&&(wt(P.beginScope,j),oe="")),K=Object.create(P,{parent:{value:K}}),K}function kt(P,j,J){let ae=pe(P.endRe,J);if(ae){if(P["on:end"]){let Te=new ee(P);P["on:end"](j,Te),Te.isMatchIgnored&&(ae=!1)}if(ae){for(;P.endsParent&&P.parent;)P=P.parent;return P}}if(P.endsWithParent)return kt(P.parent,j,J)}function On(P){return K.matcher.regexIndex===0?(oe+=P[0],1):(Tt=!0,0)}function In(P){let j=P[0],J=P.rule,ae=new ee(J),Te=[J.__beforeBegin,J["on:begin"]];for(let Ge of Te)if(Ge&&(Ge(P,ae),ae.isMatchIgnored))return On(j);return J.skip?oe+=j:(J.excludeBegin&&(oe+=j),Oe(),!J.returnBegin&&!J.excludeBegin&&(oe=j)),xt(J,P),J.returnBegin?0:j.length}function Cn(P){let j=P[0],J=$.substring(P.index),ae=kt(K,P,J);if(!ae)return yt;let Te=K;K.endScope&&K.endScope._wrap?(Oe(),He(j,K.endScope._wrap)):K.endScope&&K.endScope._multi?(Oe(),wt(K.endScope,P)):Te.skip?oe+=j:(Te.returnEnd||Te.excludeEnd||(oe+=j),Oe(),Te.excludeEnd&&(oe=j));do K.scope&&fe.closeNode(),!K.skip&&!K.subLanguage&&(ut+=K.relevance),K=K.parent;while(K!==ae.parent);return ae.starts&&xt(ae.starts,P),Te.returnEnd?0:j.length}function vn(){let P=[];for(let j=K;j!==We;j=j.parent)j.scope&&P.unshift(j.scope);P.forEach(j=>fe.openNode(j))}let dt={};function Dt(P,j){let J=j&&j[0];if(oe+=P,J==null)return Oe(),0;if(dt.type==="begin"&&j.type==="end"&&dt.index===j.index&&J===""){if(oe+=$.slice(j.index,j.index+1),!ce){let ae=new Error(`0 width match regex (${I})`);throw ae.languageName=I,ae.badRule=dt.rule,ae}return 1}if(dt=j,j.type==="begin")return In(j);if(j.type==="illegal"&&!te){let ae=new Error('Illegal lexeme "'+J+'" for mode "'+(K.scope||"<unnamed>")+'"');throw ae.mode=K,ae}else if(j.type==="end"){let ae=Cn(j);if(ae!==yt)return ae}if(j.type==="illegal"&&J==="")return oe+=`
`,1;if(bt>1e5&&bt>j.index*3)throw new Error("potential infinite loop, way more iterations than matches");return oe+=J,J.length}let We=ze(I);if(!We)throw qe(de.replace("{}",I)),new Error('Unknown language: "'+I+'"');let wn=un(We),ft="",K=re||wn,Mt={},fe=new H.__emitter(H);vn();let oe="",ut=0,Je=0,bt=0,Tt=!1;try{if(We.__emitTokens)We.__emitTokens($,fe);else{for(K.matcher.considerAll();;){bt++,Tt?Tt=!1:K.matcher.considerAll(),K.matcher.lastIndex=Je;let P=K.matcher.exec($);if(!P)break;let j=$.substring(Je,P.index),J=Dt(j,P);Je=P.index+J}Dt($.substring(Je))}return fe.finalize(),ft=fe.toHTML(),{language:I,value:ft,relevance:ut,illegal:!1,_emitter:fe,_top:K}}catch(P){if(P.message&&P.message.includes("Illegal"))return{language:I,value:ht($),illegal:!0,relevance:0,_illegalBy:{message:P.message,index:Je,context:$.slice(Je-100,Je+100),mode:P.mode,resultSoFar:ft},_emitter:fe};if(ce)return{language:I,value:ht($),illegal:!1,relevance:0,errorRaised:P,_emitter:fe,_top:K};throw P}}function Et(I){let $={value:ht(I),illegal:!1,relevance:0,_top:F,_emitter:new H.__emitter(H)};return $._emitter.addText(I),$}function gt(I,$){$=$||H.languages||Object.keys(L);let te=Et(I),re=$.filter(ze).filter(vt).map(Oe=>st(Oe,I,!1));re.unshift(te);let he=re.sort((Oe,He)=>{if(Oe.relevance!==He.relevance)return He.relevance-Oe.relevance;if(Oe.language&&He.language){if(ze(Oe.language).supersetOf===He.language)return 1;if(ze(He.language).supersetOf===Oe.language)return-1}return 0}),[Be,Ye]=he,ct=Be;return ct.secondBest=Ye,ct}function mn(I,$,te){let re=$&&U[$]||te;I.classList.add("hljs"),I.classList.add(`language-${re}`)}function mt(I){let $=null,te=me(I);if(X(te))return;if(lt("before:highlightElement",{el:I,language:te}),I.dataset.highlighted){console.log("Element previously highlighted. To highlight again, first unset `dataset.highlighted`.",I);return}if(I.children.length>0&&(H.ignoreUnescapedHTML||(console.warn("One of your code blocks includes unescaped HTML. This is a potentially serious security risk."),console.warn("https://github.com/highlightjs/highlight.js/wiki/security"),console.warn("The element with unescaped HTML:"),console.warn(I)),H.throwUnescapedHTML))throw new En("One of your code blocks includes unescaped HTML.",I.innerHTML);$=I;let re=$.textContent,he=te?ue(re,{language:te,ignoreIllegals:!0}):gt(re);I.innerHTML=he.value,I.dataset.highlighted="yes",mn(I,te,he.language),I.result={language:he.language,re:he.relevance,relevance:he.relevance},he.secondBest&&(I.secondBest={language:he.secondBest.language,relevance:he.secondBest.relevance}),lt("after:highlightElement",{el:I,result:he,text:re})}function fn(I){H=St(H,I)}let bn=()=>{ot(),et("10.6.0","initHighlighting() deprecated.  Use highlightAll() now.")};function Tn(){ot(),et("10.6.0","initHighlightingOnLoad() deprecated.  Use highlightAll() now.")}let It=!1;function ot(){function I(){ot()}if(document.readyState==="loading"){It||window.addEventListener("DOMContentLoaded",I,!1),It=!0;return}document.querySelectorAll(H.cssSelector).forEach(mt)}function Rn(I,$){let te=null;try{te=$(t)}catch(re){if(qe("Language definition for '{}' could not be registered.".replace("{}",I)),ce)qe(re);else throw re;te=F}te.name||(te.name=I),L[I]=te,te.rawDefinition=$.bind(null,t),te.aliases&&Ct(te.aliases,{languageName:I})}function Nn(I){delete L[I];for(let $ of Object.keys(U))U[$]===I&&delete U[$]}function Ln(){return Object.keys(L)}function ze(I){return I=(I||"").toLowerCase(),L[I]||L[U[I]]}function Ct(I,{languageName:$}){typeof I=="string"&&(I=[I]),I.forEach(te=>{U[te.toLowerCase()]=$})}function vt(I){let $=ze(I);return $&&!$.disableAutodetect}function _n(I){I["before:highlightBlock"]&&!I["before:highlightElement"]&&(I["before:highlightElement"]=$=>{I["before:highlightBlock"](Object.assign({block:$.el},$))}),I["after:highlightBlock"]&&!I["after:highlightElement"]&&(I["after:highlightElement"]=$=>{I["after:highlightBlock"](Object.assign({block:$.el},$))})}function An(I){_n(I),q.push(I)}function Sn(I){let $=q.indexOf(I);$!==-1&&q.splice($,1)}function lt(I,$){let te=I;q.forEach(function(re){re[te]&&re[te]($)})}function yn(I){return et("10.7.0","highlightBlock will be removed entirely in v12.0"),et("10.7.0","Please use highlightElement now."),mt(I)}Object.assign(t,{highlight:ue,highlightAuto:gt,highlightAll:ot,highlightElement:mt,highlightBlock:yn,configure:fn,initHighlighting:bn,initHighlightingOnLoad:Tn,registerLanguage:Rn,unregisterLanguage:Nn,listLanguages:Ln,getLanguage:ze,registerAliases:Ct,autoDetection:vt,inherit:St,addPlugin:An,removePlugin:Sn}),t.debugMode=function(){ce=!1},t.safeMode=function(){ce=!0},t.versionString=hn,t.regex={concat:be,lookahead:Ce,either:ve,optional:Pe,anyNumberOfTimes:ke};for(let I in at)typeof at[I]=="object"&&Q(at[I]);return Object.assign(t,at),t},tt=Ot({});tt.newInstance=()=>Ot({}),D.exports=tt,tt.HighlightJS=tt,tt.default=tt}),w=W(Y(),1),e=w.default;function n(f){let D=f.regex,Q=/(?![A-Za-z0-9])(?![$])/,ee=D.concat(/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/,Q),ne=D.concat(/(\\?[A-Z][a-z0-9_\x7f-\xff]+|\\?[A-Z]+(?=[A-Z][a-z0-9_\x7f-\xff])){1,}/,Q),se=D.concat(/[A-Z]+/,Q),le={scope:"variable",match:"\\$+"+ee},ie={scope:"meta",variants:[{begin:/<\?php/,relevance:10},{begin:/<\?=/},{begin:/<\?/,relevance:.1},{begin:/\?>/}]},Re={scope:"subst",variants:[{begin:/\$\w+/},{begin:/\{\$/,end:/\}/}]},Ee=f.inherit(f.APOS_STRING_MODE,{illegal:null}),Ie=f.inherit(f.QUOTE_STRING_MODE,{illegal:null,contains:f.QUOTE_STRING_MODE.contains.concat(Re)}),Me={begin:/<<<[ \t]*(?:(\w+)|"(\w+)")\n/,end:/[ \t]*(\w+)\b/,contains:f.QUOTE_STRING_MODE.contains.concat(Re),"on:begin":(ye,Le)=>{Le.data._beginMatch=ye[1]||ye[2]},"on:end":(ye,Le)=>{Le.data._beginMatch!==ye[1]&&Le.ignoreMatch()}},Ne=f.END_SAME_AS_BEGIN({begin:/<<<[ \t]*'(\w+)'\n/,end:/[ \t]*(\w+)\b/}),ge=`[ 	
]`,Ce={scope:"string",variants:[Ie,Ee,Me,Ne]},ke={scope:"number",variants:[{begin:"\\b0[bB][01]+(?:_[01]+)*\\b"},{begin:"\\b0[oO][0-7]+(?:_[0-7]+)*\\b"},{begin:"\\b0[xX][\\da-fA-F]+(?:_[\\da-fA-F]+)*\\b"},{begin:"(?:\\b\\d+(?:_\\d+)*(\\.(?:\\d+(?:_\\d+)*))?|\\B\\.\\d+)(?:[eE][+-]?\\d+)?"}],relevance:0},Pe=["false","null","true"],be=["__CLASS__","__DIR__","__FILE__","__FUNCTION__","__COMPILER_HALT_OFFSET__","__LINE__","__METHOD__","__NAMESPACE__","__TRAIT__","die","echo","exit","include","include_once","print","require","require_once","array","abstract","and","as","binary","bool","boolean","break","callable","case","catch","class","clone","const","continue","declare","default","do","double","else","elseif","empty","enddeclare","endfor","endforeach","endif","endswitch","endwhile","enum","eval","extends","final","finally","float","for","foreach","from","global","goto","if","implements","instanceof","insteadof","int","integer","interface","isset","iterable","list","match|0","mixed","new","never","object","or","private","protected","public","readonly","real","return","string","switch","throw","trait","try","unset","use","var","void","while","xor","yield"],De=["Error|0","AppendIterator","ArgumentCountError","ArithmeticError","ArrayIterator","ArrayObject","AssertionError","BadFunctionCallException","BadMethodCallException","CachingIterator","CallbackFilterIterator","CompileError","Countable","DirectoryIterator","DivisionByZeroError","DomainException","EmptyIterator","ErrorException","Exception","FilesystemIterator","FilterIterator","GlobIterator","InfiniteIterator","InvalidArgumentException","IteratorIterator","LengthException","LimitIterator","LogicException","MultipleIterator","NoRewindIterator","OutOfBoundsException","OutOfRangeException","OuterIterator","OverflowException","ParentIterator","ParseError","RangeException","RecursiveArrayIterator","RecursiveCachingIterator","RecursiveCallbackFilterIterator","RecursiveDirectoryIterator","RecursiveFilterIterator","RecursiveIterator","RecursiveIteratorIterator","RecursiveRegexIterator","RecursiveTreeIterator","RegexIterator","RuntimeException","SeekableIterator","SplDoublyLinkedList","SplFileInfo","SplFileObject","SplFixedArray","SplHeap","SplMaxHeap","SplMinHeap","SplObjectStorage","SplObserver","SplPriorityQueue","SplQueue","SplStack","SplSubject","SplTempFileObject","TypeError","UnderflowException","UnexpectedValueException","UnhandledMatchError","ArrayAccess","BackedEnum","Closure","Fiber","Generator","Iterator","IteratorAggregate","Serializable","Stringable","Throwable","Traversable","UnitEnum","WeakReference","WeakMap","Directory","__PHP_Incomplete_Class","parent","php_user_filter","self","static","stdClass"],ve={keyword:be,literal:(ye=>{let Le=[];return ye.forEach(Ue=>{Le.push(Ue),Ue.toLowerCase()===Ue?Le.push(Ue.toUpperCase()):Le.push(Ue.toLowerCase())}),Le})(Pe),built_in:De},we=ye=>ye.map(Le=>Le.replace(/\|\d+$/,"")),pe={variants:[{match:[/new/,D.concat(ge,"+"),D.concat("(?!",we(De).join("\\b|"),"\\b)"),ne],scope:{1:"keyword",4:"title.class"}}]},xe=D.concat(ee,"\\b(?!\\()"),Se={variants:[{match:[D.concat(/::/,D.lookahead(/(?!class\b)/)),xe],scope:{2:"variable.constant"}},{match:[/::/,/class/],scope:{2:"variable.language"}},{match:[ne,D.concat(/::/,D.lookahead(/(?!class\b)/)),xe],scope:{1:"title.class",3:"variable.constant"}},{match:[ne,D.concat("::",D.lookahead(/(?!class\b)/))],scope:{1:"title.class"}},{match:[ne,/::/,/class/],scope:{1:"title.class",3:"variable.language"}}]},Fe={scope:"attr",match:D.concat(ee,D.lookahead(":"),D.lookahead(/(?!::)/))},_e={relevance:0,begin:/\(/,end:/\)/,keywords:ve,contains:[Fe,le,Se,f.C_BLOCK_COMMENT_MODE,Ce,ke,pe]},je={relevance:0,match:[/\b/,D.concat("(?!fn\\b|function\\b|",we(be).join("\\b|"),"|",we(De).join("\\b|"),"\\b)"),ee,D.concat(ge,"*"),D.lookahead(/(?=\()/)],scope:{3:"title.function.invoke"},contains:[_e]};_e.contains.push(je);let Xe=[Fe,Se,f.C_BLOCK_COMMENT_MODE,Ce,ke,pe],Ke={begin:D.concat(/#\[\s*\\?/,D.either(ne,se)),beginScope:"meta",end:/]/,endScope:"meta",keywords:{literal:Pe,keyword:["new","array"]},contains:[{begin:/\[/,end:/]/,keywords:{literal:Pe,keyword:["new","array"]},contains:["self",...Xe]},...Xe,{scope:"meta",variants:[{match:ne},{match:se}]}]};return{case_insensitive:!1,keywords:ve,contains:[Ke,f.HASH_COMMENT_MODE,f.COMMENT("//","$"),f.COMMENT("/\\*","\\*/",{contains:[{scope:"doctag",match:"@[A-Za-z]+"}]}),{match:/__halt_compiler\(\);/,keywords:"__halt_compiler",starts:{scope:"comment",end:f.MATCH_NOTHING_RE,contains:[{match:/\?>/,scope:"meta",endsParent:!0}]}},ie,{scope:"variable.language",match:/\$this\b/},le,je,Se,{match:[/const/,/\s/,ee],scope:{1:"keyword",3:"variable.constant"}},pe,{scope:"function",relevance:0,beginKeywords:"fn function",end:/[;{]/,excludeEnd:!0,illegal:"[$%\\[]",contains:[{beginKeywords:"use"},f.UNDERSCORE_TITLE_MODE,{begin:"=>",endsParent:!0},{scope:"params",begin:"\\(",end:"\\)",excludeBegin:!0,excludeEnd:!0,keywords:ve,contains:["self",Ke,le,Se,f.C_BLOCK_COMMENT_MODE,Ce,ke]}]},{scope:"class",variants:[{beginKeywords:"enum",illegal:/[($"]/},{beginKeywords:"class interface trait",illegal:/[:($"]/}],relevance:0,end:/\{/,excludeEnd:!0,contains:[{beginKeywords:"extends implements"},f.UNDERSCORE_TITLE_MODE]},{beginKeywords:"namespace",relevance:0,end:";",illegal:/[.']/,contains:[f.inherit(f.UNDERSCORE_TITLE_MODE,{scope:"title.class"})]},{beginKeywords:"use",relevance:0,end:";",contains:[{match:/\b(as|const|function)\b/,scope:"keyword"},f.UNDERSCORE_TITLE_MODE]},Ce,ke]}}function p(f){return{name:"PHP template",subLanguage:"xml",contains:[{begin:/<\?(php|=)?/,end:/\?>/,subLanguage:"php",contains:[{begin:"/\\*",end:"\\*/",skip:!0},{begin:'b"',end:'"',skip:!0},{begin:"b'",end:"'",skip:!0},f.inherit(f.APOS_STRING_MODE,{illegal:null,className:null,contains:null,skip:!0}),f.inherit(f.QUOTE_STRING_MODE,{illegal:null,className:null,contains:null,skip:!0})]}]}}var y="[A-Za-z$_][0-9A-Za-z$_]*",M=["as","in","of","if","for","while","finally","var","new","function","do","return","void","else","break","catch","instanceof","with","throw","case","default","try","switch","continue","typeof","delete","let","yield","const","class","debugger","async","await","static","import","from","export","extends","using"],V=["true","false","null","undefined","NaN","Infinity"],z=["Object","Function","Boolean","Symbol","Math","Date","Number","BigInt","String","RegExp","Array","Float32Array","Float64Array","Int8Array","Uint8Array","Uint8ClampedArray","Int16Array","Int32Array","Uint16Array","Uint32Array","BigInt64Array","BigUint64Array","Set","Map","WeakSet","WeakMap","ArrayBuffer","SharedArrayBuffer","Atomics","DataView","JSON","Promise","Generator","GeneratorFunction","AsyncFunction","Reflect","Proxy","Intl","WebAssembly"],G=["Error","EvalError","InternalError","RangeError","ReferenceError","SyntaxError","TypeError","URIError"],R=["setInterval","setTimeout","clearInterval","clearTimeout","require","exports","eval","isFinite","isNaN","parseFloat","parseInt","decodeURI","decodeURIComponent","encodeURI","encodeURIComponent","escape","unescape"],_=["arguments","this","super","console","window","document","localStorage","sessionStorage","module","global"],N=[].concat(R,z,G);function E(f){let D=f.regex,Q=(Ae,{after:Ve})=>{let Ze="</"+Ae[0].slice(1);return Ae.input.indexOf(Ze,Ve)!==-1},ee=y,ne={begin:"<>",end:"</>"},se=/<[A-Za-z0-9\\._:-]+\s*\/>/,le={begin:/<[A-Za-z0-9\\._:-]+/,end:/\/[A-Za-z0-9\\._:-]+>|\/>/,isTrulyOpeningTag:(Ae,Ve)=>{let Ze=Ae[0].length+Ae.index,$e=Ae.input[Ze];if($e==="<"||$e===","){Ve.ignoreMatch();return}$e===">"&&(Q(Ae,{after:Ze})||Ve.ignoreMatch());let nt,it=Ae.input.substring(Ze);if(nt=it.match(/^\s*=/)){Ve.ignoreMatch();return}if((nt=it.match(/^\s+extends\s+/))&&nt.index===0){Ve.ignoreMatch();return}}},ie={$pattern:y,keyword:M,literal:V,built_in:N,"variable.language":_},Re="[0-9](_?[0-9])*",Ee=`\\.(${Re})`,Ie="0|[1-9](_?[0-9])*|0[0-7]*[89][0-9]*",Me={className:"number",variants:[{begin:`(\\b(${Ie})((${Ee})|\\.)?|(${Ee}))[eE][+-]?(${Re})\\b`},{begin:`\\b(${Ie})\\b((${Ee})\\b|\\.)?|(${Ee})\\b`},{begin:"\\b(0|[1-9](_?[0-9])*)n\\b"},{begin:"\\b0[xX][0-9a-fA-F](_?[0-9a-fA-F])*n?\\b"},{begin:"\\b0[bB][0-1](_?[0-1])*n?\\b"},{begin:"\\b0[oO][0-7](_?[0-7])*n?\\b"},{begin:"\\b0[0-7]+n?\\b"}],relevance:0},Ne={className:"subst",begin:"\\$\\{",end:"\\}",keywords:ie,contains:[]},ge={begin:".?html`",end:"",starts:{end:"`",returnEnd:!1,contains:[f.BACKSLASH_ESCAPE,Ne],subLanguage:"xml"}},Ce={begin:".?css`",end:"",starts:{end:"`",returnEnd:!1,contains:[f.BACKSLASH_ESCAPE,Ne],subLanguage:"css"}},ke={begin:".?gql`",end:"",starts:{end:"`",returnEnd:!1,contains:[f.BACKSLASH_ESCAPE,Ne],subLanguage:"graphql"}},Pe={className:"string",begin:"`",end:"`",contains:[f.BACKSLASH_ESCAPE,Ne]},be={className:"comment",variants:[f.COMMENT(/\/\*\*(?!\/)/,"\\*/",{relevance:0,contains:[{begin:"(?=@[A-Za-z]+)",relevance:0,contains:[{className:"doctag",begin:"@[A-Za-z]+"},{className:"type",begin:"\\{",end:"\\}",excludeEnd:!0,excludeBegin:!0,relevance:0},{className:"variable",begin:ee+"(?=\\s*(-)|$)",endsParent:!0,relevance:0},{begin:/(?=[^\n])\s/,relevance:0}]}]}),f.C_BLOCK_COMMENT_MODE,f.C_LINE_COMMENT_MODE]},De=[f.APOS_STRING_MODE,f.QUOTE_STRING_MODE,ge,Ce,ke,Pe,{match:/\$\d+/},Me];Ne.contains=De.concat({begin:/\{/,end:/\}/,keywords:ie,contains:["self"].concat(De)});let ve=[].concat(be,Ne.contains),we=ve.concat([{begin:/(\s*)\(/,end:/\)/,keywords:ie,contains:["self"].concat(ve)}]),pe={className:"params",begin:/(\s*)\(/,end:/\)/,excludeBegin:!0,excludeEnd:!0,keywords:ie,contains:we},xe={variants:[{match:[/class/,/\s+/,ee,/\s+/,/extends/,/\s+/,D.concat(ee,"(",D.concat(/\./,ee),")*")],scope:{1:"keyword",3:"title.class",5:"keyword",7:"title.class.inherited"}},{match:[/class/,/\s+/,ee],scope:{1:"keyword",3:"title.class"}}]},Se={relevance:0,match:D.either(/\bJSON/,/\b[A-Z][a-z]+([A-Z][a-z]*|\d)*/,/\b[A-Z]{2,}([A-Z][a-z]+|\d)+([A-Z][a-z]*)*/,/\b[A-Z]{2,}[a-z]+([A-Z][a-z]+|\d)*([A-Z][a-z]*)*/),className:"title.class",keywords:{_:[...z,...G]}},Fe={label:"use_strict",className:"meta",relevance:10,begin:/^\s*['"]use (strict|asm)['"]/},_e={variants:[{match:[/function/,/\s+/,ee,/(?=\s*\()/]},{match:[/function/,/\s*(?=\()/]}],className:{1:"keyword",3:"title.function"},label:"func.def",contains:[pe],illegal:/%/},je={relevance:0,match:/\b[A-Z][A-Z_0-9]+\b/,className:"variable.constant"};function Xe(Ae){return D.concat("(?!",Ae.join("|"),")")}let Ke={match:D.concat(/\b/,Xe([...R,"super","import"].map(Ae=>`${Ae}\\s*\\(`)),ee,D.lookahead(/\s*\(/)),className:"title.function",relevance:0},ye={begin:D.concat(/\./,D.lookahead(D.concat(ee,/(?![0-9A-Za-z$_(])/))),end:ee,excludeBegin:!0,keywords:"prototype",className:"property",relevance:0},Le={match:[/get|set/,/\s+/,ee,/(?=\()/],className:{1:"keyword",3:"title.function"},contains:[{begin:/\(\)/},pe]},Ue="(\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)|"+f.UNDERSCORE_IDENT_RE+")\\s*=>",Qe={match:[/const|var|let/,/\s+/,ee,/\s*/,/=\s*/,/(async\s*)?/,D.lookahead(Ue)],keywords:"async",className:{1:"keyword",3:"title.function"},contains:[pe]};return{name:"JavaScript",aliases:["js","jsx","mjs","cjs"],keywords:ie,exports:{PARAMS_CONTAINS:we,CLASS_REFERENCE:Se},illegal:/#(?![$_A-z])/,contains:[f.SHEBANG({label:"shebang",binary:"node",relevance:5}),Fe,f.APOS_STRING_MODE,f.QUOTE_STRING_MODE,ge,Ce,ke,Pe,be,{match:/\$\d+/},Me,Se,{scope:"attr",match:ee+D.lookahead(":"),relevance:0},Qe,{begin:"("+f.RE_STARTERS_RE+"|\\b(case|return|throw)\\b)\\s*",keywords:"return throw case",relevance:0,contains:[be,f.REGEXP_MODE,{className:"function",begin:Ue,returnBegin:!0,end:"\\s*=>",contains:[{className:"params",variants:[{begin:f.UNDERSCORE_IDENT_RE,relevance:0},{className:null,begin:/\(\s*\)/,skip:!0},{begin:/(\s*)\(/,end:/\)/,excludeBegin:!0,excludeEnd:!0,keywords:ie,contains:we}]}]},{begin:/,/,relevance:0},{match:/\s+/,relevance:0},{variants:[{begin:ne.begin,end:ne.end},{match:se},{begin:le.begin,"on:begin":le.isTrulyOpeningTag,end:le.end}],subLanguage:"xml",contains:[{begin:le.begin,end:le.end,skip:!0,contains:["self"]}]}]},_e,{beginKeywords:"while if switch catch for"},{begin:"\\b(?!function)"+f.UNDERSCORE_IDENT_RE+"\\([^()]*(\\([^()]*(\\([^()]*\\)[^()]*)*\\)[^()]*)*\\)\\s*\\{",returnBegin:!0,label:"func.def",contains:[pe,f.inherit(f.TITLE_MODE,{begin:ee,className:"title.function"})]},{match:/\.\.\./,relevance:0},ye,{match:"\\$"+ee,relevance:0},{match:[/\bconstructor(?=\s*\()/],className:{1:"title.function"},contains:[pe]},Ke,je,xe,Le,{match:/\$[(.]/}]}}function S(f){let D=f.regex,Q=f.COMMENT("--","$"),ee={scope:"string",variants:[{begin:/'/,end:/'/,contains:[{match:/''/}]}]},ne={begin:/"/,end:/"/,contains:[{match:/""/}]},se=["true","false","unknown"],le=["double precision","large object","with timezone","without timezone"],ie=["bigint","binary","blob","boolean","char","character","clob","date","dec","decfloat","decimal","float","int","integer","interval","nchar","nclob","national","numeric","real","row","smallint","time","timestamp","varchar","varying","varbinary"],Re=["add","asc","collation","desc","final","first","last","view"],Ee=["abs","acos","all","allocate","alter","and","any","are","array","array_agg","array_max_cardinality","as","asensitive","asin","asymmetric","at","atan","atomic","authorization","avg","begin","begin_frame","begin_partition","between","bigint","binary","blob","boolean","both","by","call","called","cardinality","cascaded","case","cast","ceil","ceiling","char","char_length","character","character_length","check","classifier","clob","close","coalesce","collate","collect","column","commit","condition","connect","constraint","contains","convert","copy","corr","corresponding","cos","cosh","count","covar_pop","covar_samp","create","cross","cube","cume_dist","current","current_catalog","current_date","current_default_transform_group","current_path","current_role","current_row","current_schema","current_time","current_timestamp","current_path","current_role","current_transform_group_for_type","current_user","cursor","cycle","date","day","deallocate","dec","decimal","decfloat","declare","default","define","delete","dense_rank","deref","describe","deterministic","disconnect","distinct","double","drop","dynamic","each","element","else","empty","end","end_frame","end_partition","end-exec","equals","escape","every","except","exec","execute","exists","exp","external","extract","false","fetch","filter","first_value","float","floor","for","foreign","frame_row","free","from","full","function","fusion","get","global","grant","group","grouping","groups","having","hold","hour","identity","in","indicator","initial","inner","inout","insensitive","insert","int","integer","intersect","intersection","interval","into","is","join","json_array","json_arrayagg","json_exists","json_object","json_objectagg","json_query","json_table","json_table_primitive","json_value","lag","language","large","last_value","lateral","lead","leading","left","like","like_regex","listagg","ln","local","localtime","localtimestamp","log","log10","lower","match","match_number","match_recognize","matches","max","member","merge","method","min","minute","mod","modifies","module","month","multiset","national","natural","nchar","nclob","new","no","none","normalize","not","nth_value","ntile","null","nullif","numeric","octet_length","occurrences_regex","of","offset","old","omit","on","one","only","open","or","order","out","outer","over","overlaps","overlay","parameter","partition","pattern","per","percent","percent_rank","percentile_cont","percentile_disc","period","portion","position","position_regex","power","precedes","precision","prepare","primary","procedure","ptf","range","rank","reads","real","recursive","ref","references","referencing","regr_avgx","regr_avgy","regr_count","regr_intercept","regr_r2","regr_slope","regr_sxx","regr_sxy","regr_syy","release","result","return","returns","revoke","right","rollback","rollup","row","row_number","rows","running","savepoint","scope","scroll","search","second","seek","select","sensitive","session_user","set","show","similar","sin","sinh","skip","smallint","some","specific","specifictype","sql","sqlexception","sqlstate","sqlwarning","sqrt","start","static","stddev_pop","stddev_samp","submultiset","subset","substring","substring_regex","succeeds","sum","symmetric","system","system_time","system_user","table","tablesample","tan","tanh","then","time","timestamp","timezone_hour","timezone_minute","to","trailing","translate","translate_regex","translation","treat","trigger","trim","trim_array","true","truncate","uescape","union","unique","unknown","unnest","update","upper","user","using","value","values","value_of","var_pop","var_samp","varbinary","varchar","varying","versioning","when","whenever","where","width_bucket","window","with","within","without","year"],Ie=["abs","acos","array_agg","asin","atan","avg","cast","ceil","ceiling","coalesce","corr","cos","cosh","count","covar_pop","covar_samp","cume_dist","dense_rank","deref","element","exp","extract","first_value","floor","json_array","json_arrayagg","json_exists","json_object","json_objectagg","json_query","json_table","json_table_primitive","json_value","lag","last_value","lead","listagg","ln","log","log10","lower","max","min","mod","nth_value","ntile","nullif","percent_rank","percentile_cont","percentile_disc","position","position_regex","power","rank","regr_avgx","regr_avgy","regr_count","regr_intercept","regr_r2","regr_slope","regr_sxx","regr_sxy","regr_syy","row_number","sin","sinh","sqrt","stddev_pop","stddev_samp","substring","substring_regex","sum","tan","tanh","translate","translate_regex","treat","trim","trim_array","unnest","upper","value_of","var_pop","var_samp","width_bucket"],Me=["current_catalog","current_date","current_default_transform_group","current_path","current_role","current_schema","current_transform_group_for_type","current_user","session_user","system_time","system_user","current_time","localtime","current_timestamp","localtimestamp"],Ne=["create table","insert into","primary key","foreign key","not null","alter table","add constraint","grouping sets","on overflow","character set","respect nulls","ignore nulls","nulls first","nulls last","depth first","breadth first"],ge=Ie,Ce=[...Ee,...Re].filter(pe=>!Ie.includes(pe)),ke={scope:"variable",match:/@[a-z0-9][a-z0-9_]*/},Pe={scope:"operator",match:/[-+*/=%^~]|&&?|\|\|?|!=?|<(?:=>?|<|>)?|>[>=]?/,relevance:0},be={match:D.concat(/\b/,D.either(...ge),/\s*\(/),relevance:0,keywords:{built_in:ge}};function De(pe){return D.concat(/\b/,D.either(...pe.map(xe=>xe.replace(/\s+/,"\\s+"))),/\b/)}let ve={scope:"keyword",match:De(Ne),relevance:0};function we(pe,{exceptions:xe,when:Se}={}){let Fe=Se;return xe=xe||[],pe.map(_e=>_e.match(/\|\d+$/)||xe.includes(_e)?_e:Fe(_e)?`${_e}|0`:_e)}return{name:"SQL",case_insensitive:!0,illegal:/[{}]|<\//,keywords:{$pattern:/\b[\w\.]+/,keyword:we(Ce,{when:pe=>pe.length<3}),literal:se,type:ie,built_in:Me},contains:[{scope:"type",match:De(le)},ve,be,ke,ee,ne,f.C_NUMBER_MODE,f.C_BLOCK_COMMENT_MODE,Q,Pe]}}function r(f){return{name:"Shell Session",aliases:["console","shellsession"],contains:[{className:"meta.prompt",begin:/^\s{0,3}[/~\w\d[\]()@-]*[>%$#][ ]?/,starts:{end:/[^\\](?=\s*$)/,subLanguage:"bash"}}]}}var u=f=>({IMPORTANT:{scope:"meta",begin:"!important"},BLOCK_COMMENT:f.C_BLOCK_COMMENT_MODE,HEXCOLOR:{scope:"number",begin:/#(([0-9a-fA-F]{3,4})|(([0-9a-fA-F]{2}){3,4}))\b/},FUNCTION_DISPATCH:{className:"built_in",begin:/[\w-]+(?=\()/},ATTRIBUTE_SELECTOR_MODE:{scope:"selector-attr",begin:/\[/,end:/\]/,illegal:"$",contains:[f.APOS_STRING_MODE,f.QUOTE_STRING_MODE]},CSS_NUMBER_MODE:{scope:"number",begin:f.NUMBER_RE+"(%|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|pt|pc|px|deg|grad|rad|turn|s|ms|Hz|kHz|dpi|dpcm|dppx)?",relevance:0},CSS_VARIABLE:{className:"attr",begin:/--[A-Za-z_][A-Za-z0-9_-]*/}}),l=["a","abbr","address","article","aside","audio","b","blockquote","body","button","canvas","caption","cite","code","dd","del","details","dfn","div","dl","dt","em","fieldset","figcaption","figure","footer","form","h1","h2","h3","h4","h5","h6","header","hgroup","html","i","iframe","img","input","ins","kbd","label","legend","li","main","mark","menu","nav","object","ol","optgroup","option","p","picture","q","quote","samp","section","select","source","span","strong","summary","sup","table","tbody","td","textarea","tfoot","th","thead","time","tr","ul","var","video"],h=["defs","g","marker","mask","pattern","svg","switch","symbol","feBlend","feColorMatrix","feComponentTransfer","feComposite","feConvolveMatrix","feDiffuseLighting","feDisplacementMap","feFlood","feGaussianBlur","feImage","feMerge","feMorphology","feOffset","feSpecularLighting","feTile","feTurbulence","linearGradient","radialGradient","stop","circle","ellipse","image","line","path","polygon","polyline","rect","text","use","textPath","tspan","foreignObject","clipPath"],O=[...l,...h],b=["any-hover","any-pointer","aspect-ratio","color","color-gamut","color-index","device-aspect-ratio","device-height","device-width","display-mode","forced-colors","grid","height","hover","inverted-colors","monochrome","orientation","overflow-block","overflow-inline","pointer","prefers-color-scheme","prefers-contrast","prefers-reduced-motion","prefers-reduced-transparency","resolution","scan","scripting","update","width","min-width","max-width","min-height","max-height"].sort().reverse(),i=["active","any-link","blank","checked","current","default","defined","dir","disabled","drop","empty","enabled","first","first-child","first-of-type","fullscreen","future","focus","focus-visible","focus-within","has","host","host-context","hover","indeterminate","in-range","invalid","is","lang","last-child","last-of-type","left","link","local-link","not","nth-child","nth-col","nth-last-child","nth-last-col","nth-last-of-type","nth-of-type","only-child","only-of-type","optional","out-of-range","past","placeholder-shown","read-only","read-write","required","right","root","scope","target","target-within","user-invalid","valid","visited","where"].sort().reverse(),v=["after","backdrop","before","cue","cue-region","first-letter","first-line","grammar-error","marker","part","placeholder","selection","slotted","spelling-error"].sort().reverse(),x=["accent-color","align-content","align-items","align-self","alignment-baseline","all","anchor-name","animation","animation-composition","animation-delay","animation-direction","animation-duration","animation-fill-mode","animation-iteration-count","animation-name","animation-play-state","animation-range","animation-range-end","animation-range-start","animation-timeline","animation-timing-function","appearance","aspect-ratio","backdrop-filter","backface-visibility","background","background-attachment","background-blend-mode","background-clip","background-color","background-image","background-origin","background-position","background-position-x","background-position-y","background-repeat","background-size","baseline-shift","block-size","border","border-block","border-block-color","border-block-end","border-block-end-color","border-block-end-style","border-block-end-width","border-block-start","border-block-start-color","border-block-start-style","border-block-start-width","border-block-style","border-block-width","border-bottom","border-bottom-color","border-bottom-left-radius","border-bottom-right-radius","border-bottom-style","border-bottom-width","border-collapse","border-color","border-end-end-radius","border-end-start-radius","border-image","border-image-outset","border-image-repeat","border-image-slice","border-image-source","border-image-width","border-inline","border-inline-color","border-inline-end","border-inline-end-color","border-inline-end-style","border-inline-end-width","border-inline-start","border-inline-start-color","border-inline-start-style","border-inline-start-width","border-inline-style","border-inline-width","border-left","border-left-color","border-left-style","border-left-width","border-radius","border-right","border-right-color","border-right-style","border-right-width","border-spacing","border-start-end-radius","border-start-start-radius","border-style","border-top","border-top-color","border-top-left-radius","border-top-right-radius","border-top-style","border-top-width","border-width","bottom","box-align","box-decoration-break","box-direction","box-flex","box-flex-group","box-lines","box-ordinal-group","box-orient","box-pack","box-shadow","box-sizing","break-after","break-before","break-inside","caption-side","caret-color","clear","clip","clip-path","clip-rule","color","color-interpolation","color-interpolation-filters","color-profile","color-rendering","color-scheme","column-count","column-fill","column-gap","column-rule","column-rule-color","column-rule-style","column-rule-width","column-span","column-width","columns","contain","contain-intrinsic-block-size","contain-intrinsic-height","contain-intrinsic-inline-size","contain-intrinsic-size","contain-intrinsic-width","container","container-name","container-type","content","content-visibility","counter-increment","counter-reset","counter-set","cue","cue-after","cue-before","cursor","cx","cy","direction","display","dominant-baseline","empty-cells","enable-background","field-sizing","fill","fill-opacity","fill-rule","filter","flex","flex-basis","flex-direction","flex-flow","flex-grow","flex-shrink","flex-wrap","float","flood-color","flood-opacity","flow","font","font-display","font-family","font-feature-settings","font-kerning","font-language-override","font-optical-sizing","font-palette","font-size","font-size-adjust","font-smooth","font-smoothing","font-stretch","font-style","font-synthesis","font-synthesis-position","font-synthesis-small-caps","font-synthesis-style","font-synthesis-weight","font-variant","font-variant-alternates","font-variant-caps","font-variant-east-asian","font-variant-emoji","font-variant-ligatures","font-variant-numeric","font-variant-position","font-variation-settings","font-weight","forced-color-adjust","gap","glyph-orientation-horizontal","glyph-orientation-vertical","grid","grid-area","grid-auto-columns","grid-auto-flow","grid-auto-rows","grid-column","grid-column-end","grid-column-start","grid-gap","grid-row","grid-row-end","grid-row-start","grid-template","grid-template-areas","grid-template-columns","grid-template-rows","hanging-punctuation","height","hyphenate-character","hyphenate-limit-chars","hyphens","icon","image-orientation","image-rendering","image-resolution","ime-mode","initial-letter","initial-letter-align","inline-size","inset","inset-area","inset-block","inset-block-end","inset-block-start","inset-inline","inset-inline-end","inset-inline-start","isolation","justify-content","justify-items","justify-self","kerning","left","letter-spacing","lighting-color","line-break","line-height","line-height-step","list-style","list-style-image","list-style-position","list-style-type","margin","margin-block","margin-block-end","margin-block-start","margin-bottom","margin-inline","margin-inline-end","margin-inline-start","margin-left","margin-right","margin-top","margin-trim","marker","marker-end","marker-mid","marker-start","marks","mask","mask-border","mask-border-mode","mask-border-outset","mask-border-repeat","mask-border-slice","mask-border-source","mask-border-width","mask-clip","mask-composite","mask-image","mask-mode","mask-origin","mask-position","mask-repeat","mask-size","mask-type","masonry-auto-flow","math-depth","math-shift","math-style","max-block-size","max-height","max-inline-size","max-width","min-block-size","min-height","min-inline-size","min-width","mix-blend-mode","nav-down","nav-index","nav-left","nav-right","nav-up","none","normal","object-fit","object-position","offset","offset-anchor","offset-distance","offset-path","offset-position","offset-rotate","opacity","order","orphans","outline","outline-color","outline-offset","outline-style","outline-width","overflow","overflow-anchor","overflow-block","overflow-clip-margin","overflow-inline","overflow-wrap","overflow-x","overflow-y","overlay","overscroll-behavior","overscroll-behavior-block","overscroll-behavior-inline","overscroll-behavior-x","overscroll-behavior-y","padding","padding-block","padding-block-end","padding-block-start","padding-bottom","padding-inline","padding-inline-end","padding-inline-start","padding-left","padding-right","padding-top","page","page-break-after","page-break-before","page-break-inside","paint-order","pause","pause-after","pause-before","perspective","perspective-origin","place-content","place-items","place-self","pointer-events","position","position-anchor","position-visibility","print-color-adjust","quotes","r","resize","rest","rest-after","rest-before","right","rotate","row-gap","ruby-align","ruby-position","scale","scroll-behavior","scroll-margin","scroll-margin-block","scroll-margin-block-end","scroll-margin-block-start","scroll-margin-bottom","scroll-margin-inline","scroll-margin-inline-end","scroll-margin-inline-start","scroll-margin-left","scroll-margin-right","scroll-margin-top","scroll-padding","scroll-padding-block","scroll-padding-block-end","scroll-padding-block-start","scroll-padding-bottom","scroll-padding-inline","scroll-padding-inline-end","scroll-padding-inline-start","scroll-padding-left","scroll-padding-right","scroll-padding-top","scroll-snap-align","scroll-snap-stop","scroll-snap-type","scroll-timeline","scroll-timeline-axis","scroll-timeline-name","scrollbar-color","scrollbar-gutter","scrollbar-width","shape-image-threshold","shape-margin","shape-outside","shape-rendering","speak","speak-as","src","stop-color","stop-opacity","stroke","stroke-dasharray","stroke-dashoffset","stroke-linecap","stroke-linejoin","stroke-miterlimit","stroke-opacity","stroke-width","tab-size","table-layout","text-align","text-align-all","text-align-last","text-anchor","text-combine-upright","text-decoration","text-decoration-color","text-decoration-line","text-decoration-skip","text-decoration-skip-ink","text-decoration-style","text-decoration-thickness","text-emphasis","text-emphasis-color","text-emphasis-position","text-emphasis-style","text-indent","text-justify","text-orientation","text-overflow","text-rendering","text-shadow","text-size-adjust","text-transform","text-underline-offset","text-underline-position","text-wrap","text-wrap-mode","text-wrap-style","timeline-scope","top","touch-action","transform","transform-box","transform-origin","transform-style","transition","transition-behavior","transition-delay","transition-duration","transition-property","transition-timing-function","translate","unicode-bidi","user-modify","user-select","vector-effect","vertical-align","view-timeline","view-timeline-axis","view-timeline-inset","view-timeline-name","view-transition-name","visibility","voice-balance","voice-duration","voice-family","voice-pitch","voice-range","voice-rate","voice-stress","voice-volume","white-space","white-space-collapse","widows","width","will-change","word-break","word-spacing","word-wrap","writing-mode","x","y","z-index","zoom"].sort().reverse();function m(f){let D=f.regex,Q=u(f),ee={begin:/-(webkit|moz|ms|o)-(?=[a-z])/},ne="and or not only",se=/@-?\w[\w]*(-\w+)*/,le="[a-zA-Z-][a-zA-Z0-9_-]*",ie=[f.APOS_STRING_MODE,f.QUOTE_STRING_MODE];return{name:"CSS",case_insensitive:!0,illegal:/[=|'\$]/,keywords:{keyframePosition:"from to"},classNameAliases:{keyframePosition:"selector-tag"},contains:[Q.BLOCK_COMMENT,ee,Q.CSS_NUMBER_MODE,{className:"selector-id",begin:/#[A-Za-z0-9_-]+/,relevance:0},{className:"selector-class",begin:"\\."+le,relevance:0},Q.ATTRIBUTE_SELECTOR_MODE,{className:"selector-pseudo",variants:[{begin:":("+i.join("|")+")"},{begin:":(:)?("+v.join("|")+")"}]},Q.CSS_VARIABLE,{className:"attribute",begin:"\\b("+x.join("|")+")\\b"},{begin:/:/,end:/[;}{]/,contains:[Q.BLOCK_COMMENT,Q.HEXCOLOR,Q.IMPORTANT,Q.CSS_NUMBER_MODE,...ie,{begin:/(url|data-uri)\(/,end:/\)/,relevance:0,keywords:{built_in:"url data-uri"},contains:[...ie,{className:"string",begin:/[^)]/,endsWithParent:!0,excludeEnd:!0}]},Q.FUNCTION_DISPATCH]},{begin:D.lookahead(/@/),end:"[{;]",relevance:0,illegal:/:/,contains:[{className:"keyword",begin:se},{begin:/\s/,endsWithParent:!0,excludeEnd:!0,relevance:0,keywords:{$pattern:/[a-z-]+/,keyword:ne,attribute:b.join(" ")},contains:[{begin:/[a-z-]+(?=:)/,className:"attribute"},...ie,Q.CSS_NUMBER_MODE]}]},{className:"selector-tag",begin:"\\b("+O.join("|")+")\\b"}]}}function a(f){return{name:"Plain text",aliases:["text","txt"],disableAutodetect:!0}}function c(f){let D=f.regex,Q=D.concat(/[\p{L}_]/u,D.optional(/[\p{L}0-9_.-]*:/u),/[\p{L}0-9_.-]*/u),ee=/[\p{L}0-9._:-]+/u,ne={className:"symbol",begin:/&[a-z]+;|&#[0-9]+;|&#x[a-f0-9]+;/},se={begin:/\s/,contains:[{className:"keyword",begin:/#?[a-z_][a-z1-9_-]+/,illegal:/\n/}]},le=f.inherit(se,{begin:/\(/,end:/\)/}),ie=f.inherit(f.APOS_STRING_MODE,{className:"string"}),Re=f.inherit(f.QUOTE_STRING_MODE,{className:"string"}),Ee={endsWithParent:!0,illegal:/</,relevance:0,contains:[{className:"attr",begin:ee,relevance:0},{begin:/=\s*/,relevance:0,contains:[{className:"string",endsParent:!0,variants:[{begin:/"/,end:/"/,contains:[ne]},{begin:/'/,end:/'/,contains:[ne]},{begin:/[^\s"'=<>`]+/}]}]}]};return{name:"HTML, XML",aliases:["html","xhtml","rss","atom","xjb","xsd","xsl","plist","wsf","svg"],case_insensitive:!0,unicodeRegex:!0,contains:[{className:"meta",begin:/<![a-z]/,end:/>/,relevance:10,contains:[se,Re,ie,le,{begin:/\[/,end:/\]/,contains:[{className:"meta",begin:/<![a-z]/,end:/>/,contains:[se,le,Re,ie]}]}]},f.COMMENT(/<!--/,/-->/,{relevance:10}),{begin:/<!\[CDATA\[/,end:/\]\]>/,relevance:10},ne,{className:"meta",end:/\?>/,variants:[{begin:/<\?xml/,relevance:10,contains:[Re]},{begin:/<\?[a-z][a-z0-9]+/}]},{className:"tag",begin:/<style(?=\s|>)/,end:/>/,keywords:{name:"style"},contains:[Ee],starts:{end:/<\/style>/,returnEnd:!0,subLanguage:["css","xml"]}},{className:"tag",begin:/<script(?=\s|>)/,end:/>/,keywords:{name:"script"},contains:[Ee],starts:{end:/<\/script>/,returnEnd:!0,subLanguage:["javascript","handlebars","xml"]}},{className:"tag",begin:/<>|<\/>/},{className:"tag",begin:D.concat(/</,D.lookahead(D.concat(Q,D.either(/\/>/,/>/,/\s/)))),end:/\/?>/,contains:[{className:"name",begin:Q,relevance:0,starts:Ee}]},{className:"tag",begin:D.concat(/<\//,D.lookahead(D.concat(Q,/>/))),contains:[{className:"name",begin:Q,relevance:0},{begin:/>/,relevance:0,endsParent:!0}]}]}}function A(f){let D="true false yes no null",Q="[\\w#;/?:@&=+$,.~*'()[\\]]+",ee={className:"attr",variants:[{begin:/[\w*@][\w*@ :()\./-]*:(?=[ \t]|$)/},{begin:/"[\w*@][\w*@ :()\./-]*":(?=[ \t]|$)/},{begin:/'[\w*@][\w*@ :()\./-]*':(?=[ \t]|$)/}]},ne={className:"template-variable",variants:[{begin:/\{\{/,end:/\}\}/},{begin:/%\{/,end:/\}/}]},se={className:"string",relevance:0,begin:/'/,end:/'/,contains:[{match:/''/,scope:"char.escape",relevance:0}]},le={className:"string",relevance:0,variants:[{begin:/"/,end:/"/},{begin:/\S+/}],contains:[f.BACKSLASH_ESCAPE,ne]},ie=f.inherit(le,{variants:[{begin:/'/,end:/'/,contains:[{begin:/''/,relevance:0}]},{begin:/"/,end:/"/},{begin:/[^\s,{}[\]]+/}]}),Re={className:"number",begin:"\\b[0-9]{4}(-[0-9][0-9]){0,2}([Tt \\t][0-9][0-9]?(:[0-9][0-9]){2})?(\\.[0-9]*)?([ \\t])*(Z|[-+][0-9][0-9]?(:[0-9][0-9])?)?\\b"},Ee={end:",",endsWithParent:!0,excludeEnd:!0,keywords:D,relevance:0},Ie={begin:/\{/,end:/\}/,contains:[Ee],illegal:"\\n",relevance:0},Me={begin:"\\[",end:"\\]",contains:[Ee],illegal:"\\n",relevance:0},Ne=[ee,{className:"meta",begin:"^---\\s*$",relevance:10},{className:"string",begin:"[\\|>]([1-9]?[+-])?[ ]*\\n( +)[^ ][^\\n]*\\n(\\2[^\\n]+\\n?)*"},{begin:"<%[%=-]?",end:"[%-]?%>",subLanguage:"ruby",excludeBegin:!0,excludeEnd:!0,relevance:0},{className:"type",begin:"!\\w+!"+Q},{className:"type",begin:"!<"+Q+">"},{className:"type",begin:"!"+Q},{className:"type",begin:"!!"+Q},{className:"meta",begin:"&"+f.UNDERSCORE_IDENT_RE+"$"},{className:"meta",begin:"\\*"+f.UNDERSCORE_IDENT_RE+"$"},{className:"bullet",begin:"-(?=[ ]|$)",relevance:0},f.HASH_COMMENT_MODE,{beginKeywords:D,keywords:{literal:D}},Re,{className:"number",begin:f.C_NUMBER_RE+"\\b",relevance:0},Ie,Me,se,le],ge=[...Ne];return ge.pop(),ge.push(ie),Ee.contains=ge,{name:"YAML",case_insensitive:!0,aliases:["yml"],contains:Ne}}e.registerLanguage("php",n),e.registerLanguage("php-template",p),e.registerLanguage("javascript",E),e.registerLanguage("sql",S),e.registerLanguage("shell",r),e.registerLanguage("css",m),e.registerLanguage("plaintext",a),e.registerLanguage("xml",c),e.registerLanguage("yaml",A);var Z=e.getLanguage("sql");Z.keywords.keyword=Array.from(new Set([...Z.keywords.keyword,"if","ifnull","limit","aes_decrypt","aes_encrypt","ascii","bin","bit_and","bit_count","bit_length","bit_or","bit_xor","coercibility","concat","group_concat","concat_ws","connection_id","conv","curdate","curtime","database","date_add","date_format","date_sub","dayname","dayofmonth","dayofweek","dayofyear","elt","export_set","field","find_in_set","format","from_base64","from_days","from_unixtime","get_lock","greatest","hex","ifnull","inet_aton","inet_ntoa","instr","isnull","last_insert_id","least","lpad","ltrim","make_set","md5","monthname","now","oct","ord","password","quote","release_lock","repeat","replace","reverse","rpad","rtrim","sec_to_time","sha1","sha2","sleep","soundex","space","strcmp","str_to_date","substr","sysdate","time_format","time_to_sec","to_base64","to_days","unix_timestamp","updatexml","version","week","weekday","yearweek","length","substring_index","json_unquote","json_extract","json_contains"])),Z.keywords.type=Array.from(new Set([...Z.keywords.type,"longtext"])),e.configure({classPrefix:"phpdebugbar-hljs-"}),globalThis.phpdebugbar_hljs=e.default})();(()=>{var s=Object.create,B=Object.defineProperty,T=Object.getOwnPropertyDescriptor,g=Object.getOwnPropertyNames,d=Object.getPrototypeOf,C=Object.prototype.hasOwnProperty,o=(r,u)=>()=>(u||r((u={exports:{}}).exports,u),u.exports),k=(r,u,l,h)=>{if(u&&typeof u=="object"||typeof u=="function")for(let O of g(u))!C.call(r,O)&&O!==l&&B(r,O,{get:()=>u[O],enumerable:!(h=T(u,O))||h.enumerable});return r},W=(r,u,l)=>(l=r!=null?s(d(r)):{},k(u||!r||!r.__esModule?B(l,"default",{value:r,enumerable:!0}):l,r)),Y=o(r=>{"use strict";r.__esModule=!0;var u=/[\\^$.*+?()[\]{}|]/g,l=RegExp(u.source);function h(O){return O&&l.test(O)?O.replace(u,"\\$&"):O||""}r.default=h}),w=o(r=>{"use strict";r.__esModule=!0,r.TokenTypes=void 0;var u;(function(l){l.WHITESPACE="whitespace",l.WORD="word",l.STRING="string",l.RESERVED="reserved",l.RESERVED_TOP_LEVEL="reserved-top-level",l.RESERVED_TOP_LEVEL_NO_INDENT="reserved-top-level-no-indent",l.RESERVED_NEWLINE="reserved-newline",l.OPERATOR="operator",l.NO_SPACE_OPERATOR="no-space-operator",l.OPEN_PAREN="open-paren",l.CLOSE_PAREN="close-paren",l.LINE_COMMENT="line-comment",l.BLOCK_COMMENT="block-comment",l.NUMBER="number",l.PLACEHOLDER="placeholder",l.SERVERVARIABLE="servervariable"})(u=r.TokenTypes||(r.TokenTypes={}))}),e=o(r=>{"use strict";var u=r&&r.__importDefault||function(b){return b&&b.__esModule?b:{default:b}};r.__esModule=!0;var l=u(Y()),h=w(),O=(function(){function b(i){this.WHITESPACE_REGEX=/^(\s+)/u,this.NUMBER_REGEX=/^((-\s*)?[0-9]+(\.[0-9]+)?|0x[0-9a-fA-F]+|0b[01]+|([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}))\b/u,this.AMBIGUOS_OPERATOR_REGEX=/^(\?\||\?&)/u,this.OPERATOR_REGEX=/^(!=|<>|>>|<<|==|<=|>=|!<|!>|\|\|\/|\|\/|\|\||~~\*|~~|!~~\*|!~~|~\*|!~\*|!~|:=|=>|&&|@>|<@|#-|@@|@|.)/u,this.NO_SPACE_OPERATOR_REGEX=/^(::|->>|->|#>>|#>)/u,this.BLOCK_COMMENT_REGEX=/^(\/\*[^]*?(?:\*\/|$))/u,this.LINE_COMMENT_REGEX=this.createLineCommentRegex(i.lineCommentTypes),this.RESERVED_TOP_LEVEL_REGEX=this.createReservedWordRegex(i.reservedTopLevelWords),this.RESERVED_TOP_LEVEL_NO_INDENT_REGEX=this.createReservedWordRegex(i.reservedTopLevelWordsNoIndent),this.RESERVED_NEWLINE_REGEX=this.createReservedWordRegex(i.reservedNewlineWords),this.RESERVED_PLAIN_REGEX=this.createReservedWordRegex(i.reservedWords),this.WORD_REGEX=this.createWordRegex(i.specialWordChars),this.STRING_REGEX=this.createStringRegex(i.stringTypes),this.OPEN_PAREN_REGEX=this.createParenRegex(i.openParens),this.CLOSE_PAREN_REGEX=this.createParenRegex(i.closeParens),this.INDEXED_PLACEHOLDER_REGEX=this.createPlaceholderRegex(i.indexedPlaceholderTypes,"[0-9]*"),this.IDENT_NAMED_PLACEHOLDER_REGEX=this.createPlaceholderRegex(i.namedPlaceholderTypes,"[a-zA-Z0-9._$]+"),this.STRING_NAMED_PLACEHOLDER_REGEX=this.createPlaceholderRegex(i.namedPlaceholderTypes,this.createStringPattern(i.stringTypes))}return b.prototype.createLineCommentRegex=function(i){var v="((?<!#)>|(?:[^>]))";return new RegExp("^((?:".concat(i.map(function(x){return(0,l.default)(x)}).join("|"),")").concat(v,`.*?(?:\r
|\r|
|$))`),"u")},b.prototype.createReservedWordRegex=function(i){var v=i.join("|").replace(/ /gu,"\\s+");return new RegExp("^(".concat(v,")\\b"),"iu")},b.prototype.createWordRegex=function(i){return new RegExp("^([\\p{Alphabetic}\\p{Mark}\\p{Decimal_Number}\\p{Connector_Punctuation}\\p{Join_Control}".concat(i.join(""),"]+)"),"u")},b.prototype.createStringRegex=function(i){return new RegExp("^("+this.createStringPattern(i)+")","u")},b.prototype.createStringPattern=function(i){var v={"``":"((`[^`]*($|`))+)","[]":"((\\[[^\\]]*($|\\]))(\\][^\\]]*($|\\]))*)",'""':'(("[^"\\\\]*(?:\\\\.[^"\\\\]*)*("|$))+)',"''":"(('[^'\\\\]*(?:\\\\.[^'\\\\]*)*('|$))+)","N''":"((N'[^N'\\\\]*(?:\\\\.[^N'\\\\]*)*('|$))+)","E''":"(((E|e)'[^'\\\\]*(?:\\\\.[^'\\\\]*)*('|$))+)"};return i.map(function(x){return v[x]}).join("|")},b.prototype.createParenRegex=function(i){var v=this;return new RegExp("^("+i.map(function(x){return v.escapeParen(x)}).join("|")+")","iu")},b.prototype.escapeParen=function(i){return i.length===1?(0,l.default)(i):"\\b"+i+"\\b"},b.prototype.createPlaceholderRegex=function(i,v){if(!i||i.length===0)return null;var x=i.map(l.default).join("|");return new RegExp("^((?:".concat(x,")(?:").concat(v,"))"),"u")},b.prototype.tokenize=function(i){if(!i)return[];for(var v=[],x;i.length;)x=this.getNextToken(i,x),i=i.substring(x.value.length),v.push(x);return v},b.prototype.getNextToken=function(i,v){return this.getWhitespaceToken(i)||this.getCommentToken(i)||this.getStringToken(i)||this.getOpenParenToken(i)||this.getCloseParenToken(i)||this.getAmbiguosOperatorToken(i)||this.getNoSpaceOperatorToken(i)||this.getServerVariableToken(i)||this.getPlaceholderToken(i)||this.getNumberToken(i)||this.getReservedWordToken(i,v)||this.getWordToken(i)||this.getOperatorToken(i)},b.prototype.getWhitespaceToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.WHITESPACE,regex:this.WHITESPACE_REGEX})},b.prototype.getCommentToken=function(i){return this.getLineCommentToken(i)||this.getBlockCommentToken(i)},b.prototype.getLineCommentToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.LINE_COMMENT,regex:this.LINE_COMMENT_REGEX})},b.prototype.getBlockCommentToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.BLOCK_COMMENT,regex:this.BLOCK_COMMENT_REGEX})},b.prototype.getStringToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.STRING,regex:this.STRING_REGEX})},b.prototype.getOpenParenToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.OPEN_PAREN,regex:this.OPEN_PAREN_REGEX})},b.prototype.getCloseParenToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.CLOSE_PAREN,regex:this.CLOSE_PAREN_REGEX})},b.prototype.getPlaceholderToken=function(i){return this.getIdentNamedPlaceholderToken(i)||this.getStringNamedPlaceholderToken(i)||this.getIndexedPlaceholderToken(i)},b.prototype.getServerVariableToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.SERVERVARIABLE,regex:/(^@@\w+)/iu})},b.prototype.getIdentNamedPlaceholderToken=function(i){return this.getPlaceholderTokenWithKey({input:i,regex:this.IDENT_NAMED_PLACEHOLDER_REGEX,parseKey:function(v){return v.slice(1)}})},b.prototype.getStringNamedPlaceholderToken=function(i){var v=this;return this.getPlaceholderTokenWithKey({input:i,regex:this.STRING_NAMED_PLACEHOLDER_REGEX,parseKey:function(x){return v.getEscapedPlaceholderKey({key:x.slice(2,-1),quoteChar:x.slice(-1)})}})},b.prototype.getIndexedPlaceholderToken=function(i){return this.getPlaceholderTokenWithKey({input:i,regex:this.INDEXED_PLACEHOLDER_REGEX,parseKey:function(v){return v.slice(1)}})},b.prototype.getPlaceholderTokenWithKey=function(i){var v=i.input,x=i.regex,m=i.parseKey,a=this.getTokenOnFirstMatch({input:v,regex:x,type:h.TokenTypes.PLACEHOLDER});return a&&(a.key=m(a.value)),a},b.prototype.getEscapedPlaceholderKey=function(i){var v=i.key,x=i.quoteChar;return v.replace(new RegExp((0,l.default)("\\"+x),"gu"),x)},b.prototype.getNumberToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.NUMBER,regex:this.NUMBER_REGEX})},b.prototype.getOperatorToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.OPERATOR,regex:this.OPERATOR_REGEX})},b.prototype.getAmbiguosOperatorToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.OPERATOR,regex:this.AMBIGUOS_OPERATOR_REGEX})},b.prototype.getNoSpaceOperatorToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.NO_SPACE_OPERATOR,regex:this.NO_SPACE_OPERATOR_REGEX})},b.prototype.getReservedWordToken=function(i,v){if(!(v&&v.value&&v.value==="."))return this.getToplevelReservedToken(i)||this.getNewlineReservedToken(i)||this.getTopLevelReservedTokenNoIndent(i)||this.getPlainReservedToken(i)},b.prototype.getToplevelReservedToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.RESERVED_TOP_LEVEL,regex:this.RESERVED_TOP_LEVEL_REGEX})},b.prototype.getNewlineReservedToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.RESERVED_NEWLINE,regex:this.RESERVED_NEWLINE_REGEX})},b.prototype.getPlainReservedToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.RESERVED,regex:this.RESERVED_PLAIN_REGEX})},b.prototype.getTopLevelReservedTokenNoIndent=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.RESERVED_TOP_LEVEL_NO_INDENT,regex:this.RESERVED_TOP_LEVEL_NO_INDENT_REGEX})},b.prototype.getWordToken=function(i){return this.getTokenOnFirstMatch({input:i,type:h.TokenTypes.WORD,regex:this.WORD_REGEX})},b.prototype.getTokenOnFirstMatch=function(i){var v=i.input,x=i.type,m=i.regex,a=v.match(m);if(a)return{type:x,value:a[1]}},b})();r.default=O}),n=o(r=>{"use strict";r.__esModule=!0;var u=function(l){return l===void 0&&(l=[]),l[l.length-1]};r.default=u}),p=o(r=>{"use strict";var u=r&&r.__importDefault||function(i){return i&&i.__esModule?i:{default:i}};r.__esModule=!0;var l=u(n()),h="top-level",O="block-level",b=(function(){function i(v){this.indent=v,this.indentTypes=[],this.indent=v||"  "}return i.prototype.getIndent=function(){return new Array(this.indentTypes.length).fill(this.indent).join("")},i.prototype.increaseTopLevel=function(){this.indentTypes.push(h)},i.prototype.increaseBlockLevel=function(){this.indentTypes.push(O)},i.prototype.decreaseTopLevel=function(){(0,l.default)(this.indentTypes)===h&&this.indentTypes.pop()},i.prototype.decreaseBlockLevel=function(){for(;this.indentTypes.length>0;){var v=this.indentTypes.pop();if(v!==h)break}},i.prototype.resetIndentation=function(){this.indentTypes=[]},i})();r.default=b}),y=o(r=>{"use strict";r.__esModule=!0;var u=w(),l=50,h=(function(){function O(){this.level=0}return O.prototype.beginIfPossible=function(b,i){this.level===0&&this.isInlineBlock(b,i)?this.level=1:this.level>0?this.level++:this.level=0},O.prototype.end=function(){this.level--},O.prototype.isActive=function(){return this.level>0},O.prototype.isInlineBlock=function(b,i){for(var v=0,x=0,m=i;m<b.length;m++){var a=b[m];if(v+=a.value.length,v>l)return!1;if(a.type===u.TokenTypes.OPEN_PAREN)x++;else if(a.type===u.TokenTypes.CLOSE_PAREN&&(x--,x===0))return!0;if(this.isForbiddenToken(a))return!1}return!1},O.prototype.isForbiddenToken=function(b){var i=b.type,v=b.value;return i===u.TokenTypes.RESERVED_TOP_LEVEL||i===u.TokenTypes.RESERVED_NEWLINE||i===u.TokenTypes.LINE_COMMENT||i===u.TokenTypes.BLOCK_COMMENT||v===";"},O})();r.default=h}),M=o(r=>{"use strict";r.__esModule=!0;var u=(function(){function l(h){this.params=h,this.index=0,this.params=h}return l.prototype.get=function(h){var O=h.key,b=h.value;return this.params?O?this.params[O]:this.params[this.index++]:b},l})();r.default=u}),V=o(r=>{"use strict";var u=r&&r.__importDefault||function(m){return m&&m.__esModule?m:{default:m}};r.__esModule=!0;var l=w(),h=u(p()),O=u(y()),b=u(M()),i=[" ","	"],v=function(m){for(var a=m.length-1;a>=0&&i.includes(m[a]);)a--;return m.substring(0,a+1)},x=(function(){function m(a,c,A){this.cfg=a,this.tokenizer=c,this.tokenOverride=A,this.tokens=[],this.previousReservedWord={type:null,value:null},this.previousNonWhiteSpace={type:null,value:null},this.index=0,this.indentation=new h.default(this.cfg.indent),this.inlineBlock=new O.default,this.params=new b.default(this.cfg.params)}return m.prototype.format=function(a){this.tokens=this.tokenizer.tokenize(a);var c=this.getFormattedQueryFromTokens();return c.trim()},m.prototype.getFormattedQueryFromTokens=function(){var a=this,c="";return this.tokens.forEach(function(A,Z){a.index=Z,a.tokenOverride&&(A=a.tokenOverride(A,a.previousReservedWord)||A),A.type===l.TokenTypes.WHITESPACE?c=a.formatWhitespace(A,c):A.type===l.TokenTypes.LINE_COMMENT?c=a.formatLineComment(A,c):A.type===l.TokenTypes.BLOCK_COMMENT?c=a.formatBlockComment(A,c):A.type===l.TokenTypes.RESERVED_TOP_LEVEL||A.type===l.TokenTypes.RESERVED_TOP_LEVEL_NO_INDENT||A.type===l.TokenTypes.RESERVED_NEWLINE||A.type===l.TokenTypes.RESERVED?c=a.formatReserved(A,c):A.type===l.TokenTypes.OPEN_PAREN?c=a.formatOpeningParentheses(A,c):A.type===l.TokenTypes.CLOSE_PAREN?c=a.formatClosingParentheses(A,c):A.type===l.TokenTypes.NO_SPACE_OPERATOR?c=a.formatWithoutSpaces(A,c):A.type===l.TokenTypes.PLACEHOLDER||A.type===l.TokenTypes.SERVERVARIABLE?c=a.formatPlaceholder(A,c):A.value===","?c=a.formatComma(A,c):A.value===":"?c=a.formatWithSpaceAfter(A,c):A.value==="."?c=a.formatWithoutSpaces(A,c):A.value===";"?c=a.formatQuerySeparator(A,c):c=a.formatWithSpaces(A,c),A.type!==l.TokenTypes.WHITESPACE&&(a.previousNonWhiteSpace=A)}),c},m.prototype.formatWhitespace=function(a,c){return this.cfg.linesBetweenQueries==="preserve"&&/((\r\n|\n)(\r\n|\n)+)/u.test(a.value)&&this.previousToken().value===";"?c.replace(/(\n|\r\n)$/u,"")+a.value:c},m.prototype.formatReserved=function(a,c){return a.type===l.TokenTypes.RESERVED_NEWLINE&&this.previousReservedWord&&this.previousReservedWord.value&&a.value.toUpperCase()==="AND"&&this.previousReservedWord.value.toUpperCase()==="BETWEEN"&&(a.type=l.TokenTypes.RESERVED),a.type===l.TokenTypes.RESERVED_TOP_LEVEL?c=this.formatTopLevelReservedWord(a,c):a.type===l.TokenTypes.RESERVED_TOP_LEVEL_NO_INDENT?c=this.formatTopLevelReservedWordNoIndent(a,c):a.type===l.TokenTypes.RESERVED_NEWLINE?c=this.formatNewlineReservedWord(a,c):c=this.formatWithSpaces(a,c),this.previousReservedWord=a,c},m.prototype.formatLineComment=function(a,c){return this.addNewline(c+a.value)},m.prototype.formatBlockComment=function(a,c){return this.addNewline(this.addNewline(c)+this.indentComment(a.value))},m.prototype.indentComment=function(a){return a.replace(/\n[ \t]*/gu,`
`+this.indentation.getIndent()+" ")},m.prototype.formatTopLevelReservedWordNoIndent=function(a,c){return this.indentation.decreaseTopLevel(),c=this.addNewline(c)+this.equalizeWhitespace(this.formatReservedWord(a.value)),this.addNewline(c)},m.prototype.formatTopLevelReservedWord=function(a,c){var A=this.previousNonWhiteSpace.value!==","&&!["GRANT"].includes("".concat(this.previousNonWhiteSpace.value).toUpperCase());return A&&(this.indentation.decreaseTopLevel(),c=this.addNewline(c)),c=c+this.equalizeWhitespace(this.formatReservedWord(a.value))+" ",A&&this.indentation.increaseTopLevel(),c},m.prototype.formatNewlineReservedWord=function(a,c){return this.addNewline(c)+this.equalizeWhitespace(this.formatReservedWord(a.value))+" "},m.prototype.equalizeWhitespace=function(a){return a.replace(/\s+/gu," ")},m.prototype.formatOpeningParentheses=function(a,c){a.value=this.formatCase(a.value);var A=this.previousToken().type;return A!==l.TokenTypes.WHITESPACE&&A!==l.TokenTypes.OPEN_PAREN&&A!==l.TokenTypes.LINE_COMMENT&&(c=v(c)),c+=a.value,this.inlineBlock.beginIfPossible(this.tokens,this.index),this.inlineBlock.isActive()||(this.indentation.increaseBlockLevel(),c=this.addNewline(c)),c},m.prototype.formatClosingParentheses=function(a,c){return a.value=this.formatCase(a.value),this.inlineBlock.isActive()?(this.inlineBlock.end(),this.formatWithSpaceAfter(a,c)):(this.indentation.decreaseBlockLevel(),this.formatWithSpaces(a,this.addNewline(c)))},m.prototype.formatPlaceholder=function(a,c){return c+this.params.get(a)+" "},m.prototype.formatComma=function(a,c){return c=v(c)+a.value+" ",this.inlineBlock.isActive()||/^LIMIT$/iu.test(this.previousReservedWord.value)?c:this.addNewline(c)},m.prototype.formatWithSpaceAfter=function(a,c){return v(c)+a.value+" "},m.prototype.formatWithoutSpaces=function(a,c){return v(c)+a.value},m.prototype.formatWithSpaces=function(a,c){var A=a.type===l.TokenTypes.RESERVED?this.formatReservedWord(a.value):a.value;return c+A+" "},m.prototype.formatReservedWord=function(a){return this.formatCase(a)},m.prototype.formatQuerySeparator=function(a,c){this.indentation.resetIndentation();var A=`
`;return this.cfg.linesBetweenQueries!=="preserve"&&(A=`
`.repeat(this.cfg.linesBetweenQueries||1)),v(c)+a.value+A},m.prototype.addNewline=function(a){return a=v(a),a.endsWith(`
`)||(a+=`
`),a+this.indentation.getIndent()},m.prototype.previousToken=function(){return this.tokens[this.index-1]||{type:null,value:null}},m.prototype.formatCase=function(a){return this.cfg.reservedWordCase==="upper"?a.toUpperCase():this.cfg.reservedWordCase==="lower"?a.toLowerCase():a},m})();r.default=x}),z=o(r=>{"use strict";var u=r&&r.__importDefault||function(b){return b&&b.__esModule?b:{default:b}};r.__esModule=!0;var l=u(e()),h=u(V()),O=(function(){function b(i){this.cfg=i}return b.prototype.format=function(i){return new h.default(this.cfg,this.tokenizer(),this.tokenOverride).format(i)},b.prototype.tokenize=function(i){return this.tokenizer().tokenize(i)},b.prototype.tokenizer=function(){return new l.default(this.getTokenizerConfig())},b})();r.default=O}),G=o(r=>{"use strict";var u=r&&r.__extends||(function(){var m=function(a,c){return m=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(A,Z){A.__proto__=Z}||function(A,Z){for(var f in Z)Object.prototype.hasOwnProperty.call(Z,f)&&(A[f]=Z[f])},m(a,c)};return function(a,c){if(typeof c!="function"&&c!==null)throw new TypeError("Class extends value "+String(c)+" is not a constructor or null");m(a,c);function A(){this.constructor=a}a.prototype=c===null?Object.create(c):(A.prototype=c.prototype,new A)}})(),l=r&&r.__importDefault||function(m){return m&&m.__esModule?m:{default:m}};r.__esModule=!0;var h=l(z()),O=(function(m){u(a,m);function a(){return m!==null&&m.apply(this,arguments)||this}return a.prototype.getTokenizerConfig=function(){return{reservedWords:b,reservedTopLevelWords:i,reservedNewlineWords:x,reservedTopLevelWordsNoIndent:v,stringTypes:['""',"''","``","[]"],openParens:["("],closeParens:[")"],indexedPlaceholderTypes:["?"],namedPlaceholderTypes:[":"],lineCommentTypes:["--"],specialWordChars:["#","@"]}},a})(h.default);r.default=O;var b=["ABS","ACTIVATE","ALIAS","ALL","ALLOCATE","ALLOW","ALTER","ANY","ARE","ARRAY","AS","ASC","ASENSITIVE","ASSOCIATE","ASUTIME","ASYMMETRIC","AT","ATOMIC","ATTRIBUTES","AUDIT","AUTHORIZATION","AUX","AUXILIARY","AVG","BEFORE","BEGIN","BETWEEN","BIGINT","BINARY","BLOB","BOOLEAN","BOTH","BUFFERPOOL","BY","CACHE","CALL","CALLED","CAPTURE","CARDINALITY","CASCADED","CASE","CAST","CCSID","CEIL","CEILING","CHAR","CHARACTER","CHARACTER_LENGTH","CHAR_LENGTH","CHECK","CLOB","CLONE","CLOSE","CLUSTER","COALESCE","COLLATE","COLLECT","COLLECTION","COLLID","COLUMN","COMMENT","COMMIT","CONCAT","CONDITION","CONNECT","CONNECTION","CONSTRAINT","CONTAINS","CONTINUE","CONVERT","CORR","CORRESPONDING","COUNT","COUNT_BIG","COVAR_POP","COVAR_SAMP","CREATE","CROSS","CUBE","CUME_DIST","CURRENT","CURRENT_DATE","CURRENT_DEFAULT_TRANSFORM_GROUP","CURRENT_LC_CTYPE","CURRENT_PATH","CURRENT_ROLE","CURRENT_SCHEMA","CURRENT_SERVER","CURRENT_TIME","CURRENT_TIMESTAMP","CURRENT_TIMEZONE","CURRENT_TRANSFORM_GROUP_FOR_TYPE","CURRENT_USER","CURSOR","CYCLE","DATA","DATABASE","DATAPARTITIONNAME","DATAPARTITIONNUM","DATE","DAY","DAYS","DB2GENERAL","DB2GENRL","DB2SQL","DBINFO","DBPARTITIONNAME","DBPARTITIONNUM","DEALLOCATE","DEC","DECIMAL","DECLARE","DEFAULT","DEFAULTS","DEFINITION","DELETE","DENSERANK","DENSE_RANK","DEREF","DESCRIBE","DESCRIPTOR","DETERMINISTIC","DIAGNOSTICS","DISABLE","DISALLOW","DISCONNECT","DISTINCT","DO","DOCUMENT","DOUBLE","DROP","DSSIZE","DYNAMIC","EACH","EDITPROC","ELEMENT","ELSE","ELSEIF","ENABLE","ENCODING","ENCRYPTION","END","END-EXEC","ENDING","ERASE","ESCAPE","EVERY","EXCEPTION","EXCLUDING","EXCLUSIVE","EXEC","EXECUTE","EXISTS","EXIT","EXP","EXPLAIN","EXTENDED","EXTERNAL","EXTRACT","FALSE","FENCED","FETCH","FIELDPROC","FILE","FILTER","FINAL","FIRST","FLOAT","FLOOR","FOR","FOREIGN","FREE","FULL","FUNCTION","FUSION","GENERAL","GENERATED","GET","GLOBAL","GOTO","GRANT","GRAPHIC","GROUP","GROUPING","HANDLER","HASH","HASHED_VALUE","HINT","HOLD","HOUR","HOURS","IDENTITY","IF","IMMEDIATE","IN","INCLUDING","INCLUSIVE","INCREMENT","INDEX","INDICATOR","INDICATORS","INF","INFINITY","INHERIT","INNER","INOUT","INSENSITIVE","INSERT","INT","INTEGER","INTEGRITY","INTERSECTION","INTERVAL","INTO","IS","ISOBID","ISOLATION","ITERATE","JAR","JAVA","KEEP","KEY","LABEL","LANGUAGE","LARGE","LATERAL","LC_CTYPE","LEADING","LEAVE","LEFT","LIKE","LINKTYPE","LN","LOCAL","LOCALDATE","LOCALE","LOCALTIME","LOCALTIMESTAMP","LOCATOR","LOCATORS","LOCK","LOCKMAX","LOCKSIZE","LONG","LOOP","LOWER","MAINTAINED","MATCH","MATERIALIZED","MAX","MAXVALUE","MEMBER","MERGE","METHOD","MICROSECOND","MICROSECONDS","MIN","MINUTE","MINUTES","MINVALUE","MOD","MODE","MODIFIES","MODULE","MONTH","MONTHS","MULTISET","NAN","NATIONAL","NATURAL","NCHAR","NCLOB","NEW","NEW_TABLE","NEXTVAL","NO","NOCACHE","NOCYCLE","NODENAME","NODENUMBER","NOMAXVALUE","NOMINVALUE","NONE","NOORDER","NORMALIZE","NORMALIZED","NOT","NULL","NULLIF","NULLS","NUMERIC","NUMPARTS","OBID","OCTET_LENGTH","OF","OFFSET","OLD","OLD_TABLE","ON","ONLY","OPEN","OPTIMIZATION","OPTIMIZE","OPTION","ORDER","OUT","OUTER","OVER","OVERLAPS","OVERLAY","OVERRIDING","PACKAGE","PADDED","PAGESIZE","PARAMETER","PART","PARTITION","PARTITIONED","PARTITIONING","PARTITIONS","PASSWORD","PATH","PERCENTILE_CONT","PERCENTILE_DISC","PERCENT_RANK","PIECESIZE","PLAN","POSITION","POWER","PRECISION","PREPARE","PREVVAL","PRIMARY","PRIQTY","PRIVILEGES","PROCEDURE","PROGRAM","PSID","PUBLIC","QUERY","QUERYNO","RANGE","RANK","READ","READS","REAL","RECOVERY","RECURSIVE","REF","REFERENCES","REFERENCING","REFRESH","REGR_AVGX","REGR_AVGY","REGR_COUNT","REGR_INTERCEPT","REGR_R2","REGR_SLOPE","REGR_SXX","REGR_SXY","REGR_SYY","RELEASE","RENAME","REPEAT","RESET","RESIGNAL","RESTART","RESTRICT","RESULT","RESULT_SET_LOCATOR","RETURN","RETURNS","REVOKE","RIGHT","ROLE","ROLLBACK","ROLLUP","ROUND_CEILING","ROUND_DOWN","ROUND_FLOOR","ROUND_HALF_DOWN","ROUND_HALF_EVEN","ROUND_HALF_UP","ROUND_UP","ROUTINE","ROW","ROWNUMBER","ROWS","ROWSET","ROW_NUMBER","RRN","RUN","SAVEPOINT","SCHEMA","SCOPE","SCRATCHPAD","SCROLL","SEARCH","SECOND","SECONDS","SECQTY","SECURITY","SENSITIVE","SEQUENCE","SESSION","SESSION_USER","SIGNAL","SIMILAR","SIMPLE","SMALLINT","SNAN","SOME","SOURCE","SPECIFIC","SPECIFICTYPE","SQL","SQLEXCEPTION","SQLID","SQLSTATE","SQLWARNING","SQRT","STACKED","STANDARD","START","STARTING","STATEMENT","STATIC","STATMENT","STAY","STDDEV_POP","STDDEV_SAMP","STOGROUP","STORES","STYLE","SUBMULTISET","SUBSTRING","SUM","SUMMARY","SYMMETRIC","SYNONYM","SYSFUN","SYSIBM","SYSPROC","SYSTEM","SYSTEM_USER","TABLE","TABLESAMPLE","TABLESPACE","THEN","TIME","TIMESTAMP","TIMEZONE_HOUR","TIMEZONE_MINUTE","TO","TRAILING","TRANSACTION","TRANSLATE","TRANSLATION","TREAT","TRIGGER","TRIM","TRUE","TRUNCATE","TYPE","UESCAPE","UNDO","UNIQUE","UNKNOWN","UNNEST","UNTIL","UPPER","USAGE","USER","USING","VALIDPROC","VALUE","VARCHAR","VARIABLE","VARIANT","VARYING","VAR_POP","VAR_SAMP","VCAT","VERSION","VIEW","VOLATILE","VOLUMES","WHEN","WHENEVER","WHILE","WIDTH_BUCKET","WINDOW","WITH","WITHIN","WITHOUT","WLM","WRITE","XMLELEMENT","XMLEXISTS","XMLNAMESPACES","YEAR","YEARS"],i=["ADD","AFTER","ALTER COLUMN","ALTER TABLE","DELETE FROM","EXCEPT","FETCH FIRST","FROM","GROUP BY","GO","HAVING","INSERT INTO","INTERSECT","LIMIT","ORDER BY","SELECT","SET CURRENT SCHEMA","SET SCHEMA","SET","UPDATE","VALUES","WHERE"],v=["INTERSECT","INTERSECT ALL","MINUS","UNION","UNION ALL"],x=["AND","CROSS JOIN","INNER JOIN","JOIN","LEFT JOIN","LEFT OUTER JOIN","OR","OUTER JOIN","RIGHT JOIN","RIGHT OUTER JOIN"]}),R=o(r=>{"use strict";var u=r&&r.__extends||(function(){var m=function(a,c){return m=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(A,Z){A.__proto__=Z}||function(A,Z){for(var f in Z)Object.prototype.hasOwnProperty.call(Z,f)&&(A[f]=Z[f])},m(a,c)};return function(a,c){if(typeof c!="function"&&c!==null)throw new TypeError("Class extends value "+String(c)+" is not a constructor or null");m(a,c);function A(){this.constructor=a}a.prototype=c===null?Object.create(c):(A.prototype=c.prototype,new A)}})(),l=r&&r.__importDefault||function(m){return m&&m.__esModule?m:{default:m}};r.__esModule=!0;var h=l(z()),O=(function(m){u(a,m);function a(){return m!==null&&m.apply(this,arguments)||this}return a.prototype.getTokenizerConfig=function(){return{reservedWords:b,reservedTopLevelWords:i,reservedNewlineWords:x,reservedTopLevelWordsNoIndent:v,stringTypes:['""',"''","``"],openParens:["(","[","{"],closeParens:[")","]","}"],namedPlaceholderTypes:["$"],lineCommentTypes:["#","--"],specialWordChars:[]}},a})(h.default);r.default=O;var b=["ALL","ALTER","ANALYZE","AND","ANY","ARRAY","AS","ASC","BEGIN","BETWEEN","BINARY","BOOLEAN","BREAK","BUCKET","BUILD","BY","CALL","CASE","CAST","CLUSTER","COLLATE","COLLECTION","COMMIT","CONNECT","CONTINUE","CORRELATE","COVER","CREATE","DATABASE","DATASET","DATASTORE","DECLARE","DECREMENT","DELETE","DERIVED","DESC","DESCRIBE","DISTINCT","DO","DROP","EACH","ELEMENT","ELSE","END","EVERY","EXCEPT","EXCLUDE","EXECUTE","EXISTS","EXPLAIN","FALSE","FETCH","FIRST","FLATTEN","FOR","FORCE","FROM","FUNCTION","GRANT","GROUP","GSI","HAVING","IF","IGNORE","ILIKE","IN","INCLUDE","INCREMENT","INDEX","INFER","INLINE","INNER","INSERT","INTERSECT","INTO","IS","JOIN","KEY","KEYS","KEYSPACE","KNOWN","LAST","LEFT","LET","LETTING","LIKE","LIMIT","LSM","MAP","MAPPING","MATCHED","MATERIALIZED","MERGE","MISSING","NAMESPACE","NEST","NOT","NULL","NUMBER","OBJECT","OFFSET","ON","OPTION","OR","ORDER","OUTER","OVER","PARSE","PARTITION","PASSWORD","PATH","POOL","PREPARE","PRIMARY","PRIVATE","PRIVILEGE","PROCEDURE","PUBLIC","RAW","REALM","REDUCE","RENAME","RETURN","RETURNING","REVOKE","RIGHT","ROLE","ROLLBACK","SATISFIES","SCHEMA","SELECT","SELF","SEMI","SET","SHOW","SOME","START","STATISTICS","STRING","SYSTEM","THEN","TO","TRANSACTION","TRIGGER","TRUE","TRUNCATE","UNDER","UNION","UNIQUE","UNKNOWN","UNNEST","UNSET","UPDATE","UPSERT","USE","USER","USING","VALIDATE","VALUE","VALUED","VALUES","VIA","VIEW","WHEN","WHERE","WHILE","WITH","WITHIN","WORK","XOR"],i=["DELETE FROM","EXCEPT ALL","EXCEPT","EXPLAIN DELETE FROM","EXPLAIN UPDATE","EXPLAIN UPSERT","FROM","GROUP BY","HAVING","INFER","INSERT INTO","LET","LIMIT","MERGE","NEST","ORDER BY","PREPARE","SELECT","SET CURRENT SCHEMA","SET SCHEMA","SET","UNNEST","UPDATE","UPSERT","USE KEYS","VALUES","WHERE"],v=["INTERSECT","INTERSECT ALL","MINUS","UNION","UNION ALL"],x=["AND","INNER JOIN","JOIN","LEFT JOIN","LEFT OUTER JOIN","OR","OUTER JOIN","RIGHT JOIN","RIGHT OUTER JOIN","XOR"]}),_=o(r=>{"use strict";var u=r&&r.__extends||(function(){var a=function(c,A){return a=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(Z,f){Z.__proto__=f}||function(Z,f){for(var D in f)Object.prototype.hasOwnProperty.call(f,D)&&(Z[D]=f[D])},a(c,A)};return function(c,A){if(typeof A!="function"&&A!==null)throw new TypeError("Class extends value "+String(A)+" is not a constructor or null");a(c,A);function Z(){this.constructor=c}c.prototype=A===null?Object.create(A):(Z.prototype=A.prototype,new Z)}})(),l=r&&r.__importDefault||function(a){return a&&a.__esModule?a:{default:a}};r.__esModule=!0;var h=l(z()),O=w(),b=(function(a){u(c,a);function c(){var A=a!==null&&a.apply(this,arguments)||this;return A.tokenOverride=function(Z,f){if(Z.type===O.TokenTypes.RESERVED_TOP_LEVEL&&f.value&&Z.value.toUpperCase()==="SET"&&f.value.toUpperCase()==="BY")return Z.type=O.TokenTypes.RESERVED,Z},A}return c.prototype.getTokenizerConfig=function(){return{reservedWords:i,reservedTopLevelWords:v,reservedNewlineWords:m,reservedTopLevelWordsNoIndent:x,stringTypes:['""',"N''","''","``"],openParens:["(","CASE"],closeParens:[")","END"],indexedPlaceholderTypes:["?"],namedPlaceholderTypes:[":"],lineCommentTypes:["--"],specialWordChars:["_","$","#",".","@"]}},c})(h.default);r.default=b;var i=["A","ACCESSIBLE","AGENT","AGGREGATE","ALL","ALTER","ANY","ARRAY","AS","ASC","AT","ATTRIBUTE","AUTHID","AVG","BETWEEN","BFILE_BASE","BINARY_INTEGER","BINARY","BLOB_BASE","BLOCK","BODY","BOOLEAN","BOTH","BOUND","BREADTH","BULK","BY","BYTE","C","CALL","CALLING","CASCADE","CASE","CHAR_BASE","CHAR","CHARACTER","CHARSET","CHARSETFORM","CHARSETID","CHECK","CLOB_BASE","CLONE","CLOSE","CLUSTER","CLUSTERS","COALESCE","COLAUTH","COLLECT","COLUMNS","COMMENT","COMMIT","COMMITTED","COMPILED","COMPRESS","CONNECT","CONSTANT","CONSTRUCTOR","CONTEXT","CONTINUE","CONVERT","COUNT","CRASH","CREATE","CREDENTIAL","CURRENT","CURRVAL","CURSOR","CUSTOMDATUM","DANGLING","DATA","DATE_BASE","DATE","DAY","DECIMAL","DEFAULT","DEFINE","DELETE","DEPTH","DESC","DETERMINISTIC","DIRECTORY","DISTINCT","DO","DOUBLE","DROP","DURATION","ELEMENT","ELSIF","EMPTY","END","ESCAPE","EXCEPTIONS","EXCLUSIVE","EXECUTE","EXISTS","EXIT","EXTENDS","EXTERNAL","EXTRACT","FALSE","FETCH","FINAL","FIRST","FIXED","FLOAT","FOR","FORALL","FORCE","FROM","FUNCTION","GENERAL","GOTO","GRANT","GROUP","HASH","HEAP","HIDDEN","HOUR","IDENTIFIED","IF","IMMEDIATE","IN","INCLUDING","INDEX","INDEXES","INDICATOR","INDICES","INFINITE","INSTANTIABLE","INT","INTEGER","INTERFACE","INTERVAL","INTO","INVALIDATE","IS","ISOLATION","JAVA","LANGUAGE","LARGE","LEADING","LENGTH","LEVEL","LIBRARY","LIKE","LIKE2","LIKE4","LIKEC","LIMITED","LOCAL","LOCK","LONG","MAP","MAX","MAXLEN","MEMBER","MERGE","MIN","MINUTE","MLSLABEL","MOD","MODE","MONTH","MULTISET","NAME","NAN","NATIONAL","NATIVE","NATURAL","NATURALN","NCHAR","NEW","NEXTVAL","NOCOMPRESS","NOCOPY","NOT","NOWAIT","NULL","NULLIF","NUMBER_BASE","NUMBER","OBJECT","OCICOLL","OCIDATE","OCIDATETIME","OCIDURATION","OCIINTERVAL","OCILOBLOCATOR","OCINUMBER","OCIRAW","OCIREF","OCIREFCURSOR","OCIROWID","OCISTRING","OCITYPE","OF","OLD","ON","ONLY","OPAQUE","OPEN","OPERATOR","OPTION","ORACLE","ORADATA","ORDER","ORGANIZATION","ORLANY","ORLVARY","OTHERS","OUT","OVERLAPS","OVERRIDING","PACKAGE","PARALLEL_ENABLE","PARAMETER","PARAMETERS","PARENT","PARTITION","PASCAL","PCTFREE","PIPE","PIPELINED","PLS_INTEGER","PLUGGABLE","POSITIVE","POSITIVEN","PRAGMA","PRECISION","PRIOR","PRIVATE","PROCEDURE","PUBLIC","RAISE","RANGE","RAW","READ","REAL","RECORD","REF","REFERENCE","RELEASE","RELIES_ON","REM","REMAINDER","RENAME","RESOURCE","RESULT_CACHE","RESULT","RETURN","RETURNING","REVERSE","REVOKE","ROLLBACK","ROW","ROWID","ROWNUM","ROWTYPE","SAMPLE","SAVE","SAVEPOINT","SB1","SB2","SB4","SEARCH","SECOND","SEGMENT","SELF","SEPARATE","SEQUENCE","SERIALIZABLE","SHARE","SHORT","SIZE_T","SIZE","SMALLINT","SOME","SPACE","SPARSE","SQL","SQLCODE","SQLDATA","SQLERRM","SQLNAME","SQLSTATE","STANDARD","START","STATIC","STDDEV","STORED","STRING","STRUCT","STYLE","SUBMULTISET","SUBPARTITION","SUBSTITUTABLE","SUBTYPE","SUCCESSFUL","SUM","SYNONYM","SYSDATE","TABAUTH","TABLE","TDO","THE","THEN","TIME","TIMESTAMP","TIMEZONE_ABBR","TIMEZONE_HOUR","TIMEZONE_MINUTE","TIMEZONE_REGION","TO","TRAILING","TRANSACTION","TRANSACTIONAL","TRIGGER","TRUE","TRUSTED","TYPE","UB1","UB2","UB4","UID","UNDER","UNIQUE","UNPLUG","UNSIGNED","UNTRUSTED","USE","USER","USING","VALIDATE","VALIST","VALUE","VARCHAR","VARCHAR2","VARIABLE","VARIANCE","VARRAY","VARYING","VIEW","VIEWS","VOID","WHENEVER","WHILE","WITH","WORK","WRAPPED","WRITE","YEAR","ZONE"],v=["ADD","ALTER COLUMN","ALTER TABLE","BEGIN","CONNECT BY","DECLARE","DELETE FROM","DELETE","END","EXCEPT","EXCEPTION","FETCH FIRST","FROM","GROUP BY","HAVING","INSERT INTO","INSERT","LIMIT","LOOP","MODIFY","ORDER BY","SELECT","SET CURRENT SCHEMA","SET SCHEMA","SET","START WITH","UPDATE","VALUES","WHERE"],x=["INTERSECT","INTERSECT ALL","MINUS","UNION","UNION ALL"],m=["AND","CROSS APPLY","CROSS JOIN","ELSE","END","INNER JOIN","JOIN","LEFT JOIN","LEFT OUTER JOIN","OR","OUTER APPLY","OUTER JOIN","RIGHT JOIN","RIGHT OUTER JOIN","WHEN","XOR"]}),N=o(r=>{"use strict";var u=r&&r.__extends||(function(){var m=function(a,c){return m=Object.setPrototypeOf||{__proto__:[]}instanceof Array&&function(A,Z){A.__proto__=Z}||function(A,Z){for(var f in Z)Object.prototype.hasOwnProperty.call(Z,f)&&(A[f]=Z[f])},m(a,c)};return function(a,c){if(typeof c!="function"&&c!==null)throw new TypeError("Class extends value "+String(c)+" is not a constructor or null");m(a,c);function A(){this.constructor=a}a.prototype=c===null?Object.create(c):(A.prototype=c.prototype,new A)}})(),l=r&&r.__importDefault||function(m){return m&&m.__esModule?m:{default:m}};r.__esModule=!0;var h=l(z()),O=(function(m){u(a,m);function a(){return m!==null&&m.apply(this,arguments)||this}return a.prototype.getTokenizerConfig=function(){return{reservedWords:b,reservedTopLevelWords:i,reservedNewlineWords:x,reservedTopLevelWordsNoIndent:v,stringTypes:['""',"N''","''","``","[]","E''"],openParens:["(","CASE"],closeParens:[")","END"],indexedPlaceholderTypes:["?"],namedPlaceholderTypes:["@",":","%","$"],lineCommentTypes:["#","--"],specialWordChars:[]}},a})(h.default);r.default=O;var b=["ACCESSIBLE","ACTION","AGAINST","AGGREGATE","ALGORITHM","ALL","ALTER","ANALYSE","ANALYZE","AS","ASC","AUTOCOMMIT","AUTO_INCREMENT","BACKUP","BEGIN","BETWEEN","BINLOG","BOTH","CASCADE","CASE","CHANGE","CHANGED","CHARACTER SET","CHARSET","CHECK","CHECKSUM","COLLATE","COLLATION","COLUMN","COLUMNS","COMMENT","COMMIT","COMMITTED","COMPRESSED","CONCURRENT","CONSTRAINT","CONTAINS","CONVERT","COUNT","CREATE","CROSS","CURRENT_TIMESTAMP","DATABASE","DATABASES","DAY_HOUR","DAY_MINUTE","DAY_SECOND","DAY","DEFAULT","DEFINER","DELAYED","DELETE","DESC","DESCRIBE","DETERMINISTIC","DISTINCT","DISTINCTROW","DIV","DO","DROP","DUMPFILE","DUPLICATE","DYNAMIC","ELSE","ENCLOSED","END","ENGINE","ENGINES","ENGINE_TYPE","ESCAPE","ESCAPED","EVENTS","EXEC","EXECUTE","EXISTS","EXPLAIN","EXTENDED","FAST","FETCH","FIELDS","FILE","FIRST","FIXED","FLUSH","FOR","FORCE","FOREIGN","FULL","FULLTEXT","FUNCTION","GLOBAL","GRANTS","GROUP_CONCAT","HEAP","HIGH_PRIORITY","HOSTS","HOUR","HOUR_MINUTE","HOUR_SECOND","IDENTIFIED","IF","IFNULL","IGNORE","IN","INDEX","INDEXES","INFILE","INSERT","INSERT_ID","INSERT_METHOD","INTERVAL","INTO","INVOKER","IS","ISOLATION","KEY","KEYS","KILL","LAST_INSERT_ID","LEADING","LEVEL","LIKE","LINEAR","LINES","LOAD","LOCAL","LOCK","LOCKS","LOGS","LOW_PRIORITY","MARIA","MASTER","MASTER_CONNECT_RETRY","MASTER_HOST","MASTER_LOG_FILE","MATCH","MAX_CONNECTIONS_PER_HOUR","MAX_QUERIES_PER_HOUR","MAX_ROWS","MAX_UPDATES_PER_HOUR","MAX_USER_CONNECTIONS","MEDIUM","MERGE","MINUTE","MINUTE_SECOND","MIN_ROWS","MODE","MONTH","MRG_MYISAM","MYISAM","NAMES","NATURAL","NOT","NOW()","NULL","OFFSET","ON DELETE","ON UPDATE","ON","ONLY","OPEN","OPTIMIZE","OPTION","OPTIONALLY","OUTFILE","PACK_KEYS","PAGE","PARTIAL","PARTITION","PARTITIONS","PASSWORD","PRIMARY","PRIVILEGES","PROCEDURE","PROCESS","PROCESSLIST","PURGE","QUICK","RAID0","RAID_CHUNKS","RAID_CHUNKSIZE","RAID_TYPE","RANGE","READ","READ_ONLY","READ_WRITE","REFERENCES","REGEXP","RELOAD","RENAME","REPAIR","REPEATABLE","REPLACE","REPLICATION","RESET","RESTORE","RESTRICT","RETURN","RETURNS","REVOKE","RLIKE","ROLLBACK","ROW","ROWS","ROW_FORMAT","SECOND","SECURITY","SEPARATOR","SERIALIZABLE","SESSION","SHARE","SHOW","SHUTDOWN","SLAVE","SONAME","SOUNDS","SQL","SQL_AUTO_IS_NULL","SQL_BIG_RESULT","SQL_BIG_SELECTS","SQL_BIG_TABLES","SQL_BUFFER_RESULT","SQL_CACHE","SQL_CALC_FOUND_ROWS","SQL_LOG_BIN","SQL_LOG_OFF","SQL_LOG_UPDATE","SQL_LOW_PRIORITY_UPDATES","SQL_MAX_JOIN_SIZE","SQL_NO_CACHE","SQL_QUOTE_SHOW_CREATE","SQL_SAFE_UPDATES","SQL_SELECT_LIMIT","SQL_SLAVE_SKIP_COUNTER","SQL_SMALL_RESULT","SQL_WARNINGS","START","STARTING","STATUS","STOP","STORAGE","STRAIGHT_JOIN","STRING","STRIPED","SUPER","TABLE","TABLES","TEMPORARY","TERMINATED","THEN","TO","TRAILING","TRANSACTIONAL","TRIGGER","TRUE","TRUNCATE","TYPE","TYPES","UNCOMMITTED","UNIQUE","UNLOCK","UNSIGNED","USAGE","USE","USING","VARIABLES","VIEW","WHEN","WITH","WORK","WRITE","YEAR_MONTH"],i=["ADD","AFTER","ALTER COLUMN","ALTER TABLE","CREATE OR REPLACE","DECLARE","DELETE FROM","EXCEPT","FETCH FIRST","FROM","GO","GRANT","GROUP BY","HAVING","INSERT INTO","INSERT","LIMIT","MODIFY","ORDER BY","RETURNING","SELECT","SET CURRENT SCHEMA","SET SCHEMA","SET","UPDATE","VALUES","WHERE"],v=["INTERSECT ALL","INTERSECT","MINUS","UNION ALL","UNION"],x=["AND","CROSS APPLY","CROSS JOIN","ELSE","INNER JOIN","FULL JOIN","FULL OUTER JOIN","LEFT JOIN","LEFT OUTER JOIN","NATURAL JOIN","OR","OUTER APPLY","OUTER JOIN","RENAME","RIGHT JOIN","RIGHT OUTER JOIN","JOIN","WHEN","XOR"]}),E=o(r=>{"use strict";var u=r&&r.__importDefault||function(x){return x&&x.__esModule?x:{default:x}};r.__esModule=!0,r.tokenize=r.format=void 0;var l=u(G()),h=u(R()),O=u(_()),b=u(N()),i=function(x,m){switch(m===void 0&&(m={}),m.language){case"db2":return new l.default(m).format(x);case"n1ql":return new h.default(m).format(x);case"pl/sql":return new O.default(m).format(x);default:return new b.default(m).format(x)}};r.format=i;var v=function(x,m){return m===void 0&&(m={}),new b.default(m).tokenize(x)};r.tokenize=v,r.default={format:r.format,tokenize:r.tokenize}}),S=W(E(),1);globalThis.phpdebugbar_sqlformatter=S.default.default})();window.PhpDebugBar=window.PhpDebugBar||{};(function(){const s=window.PhpDebugBar;s.utils=s.utils||{};const B=s.utils.getDictValue=function(w,e,n){if(w==null)return n;const p=String(e).split(".");let y=w;for(const M of p)if(y==null||(y=y[M],y===void 0))return n;return y};s.utils.csscls=function(w,e){const n=String(w).trim();return n.includes(" ")?n.split(/\s+/).filter(Boolean).map(p=>s.utils.csscls(p,e)).join(" "):n.startsWith(".")?`.${e}${n.slice(1)}`:e+n},s.utils.makecsscls=function(w){return e=>s.utils.csscls(e,w)};const T=s.utils.makecsscls("phpdebugbar-");s.utils.sfDump=function(w){typeof window.Sfdump=="function"&&w.querySelectorAll("pre.sf-dump[id]").forEach(e=>{window.Sfdump(e.id,{maxDepth:0})})},s.utils.schedule=function(w){return window.requestIdleCallback?window.requestIdleCallback(w,{timeout:1e3}):setTimeout(w,0)};class g{get tagName(){return"div"}constructor(e={}){this._attributes=pt({},this.defaults),this._boundAttributes={},this.el=document.createElement(this.tagName),this.className&&this.el.classList.add(...this.className.split(" ")),this.initialize(e),this.render()}initialize(e){this.set(e)}render(){}set(e,n){const p=typeof e=="string"?{[e]:n}:e,y=[];for(const M in p)if(n=p[M],this._attributes[M]=n,this._boundAttributes[M])for(const V of this._boundAttributes[M])y.includes(V)||(V.call(this,n),y.push(V))}has(e){return this._attributes[e]!==void 0&&this._attributes[e]!==null}get(e){return this._attributes[e]}bindAttr(e,n){if(Array.isArray(e)){for(const p of e)this.bindAttr(p,n);return}if(this._boundAttributes[e]||(this._boundAttributes[e]=[]),n instanceof HTMLElement){const p=n;n=y=>p.textContent=y||""}this._boundAttributes[e].push(n),this.has(e)&&n.call(this,this._attributes[e])}static extend(e){const n=this;class p extends n{}for(const y in e){const M=Object.getOwnPropertyDescriptor(e,y);M&&Object.defineProperty(p.prototype,y,M)}return Object.assign(p,n),p.__super__=n.prototype,p}}g.prototype.defaults={},s.Widget=g;class d extends g{get className(){return T("panel")}render(){this.active=!1,this.tab=document.createElement("a"),this.tab.classList.add(T("tab")),this.icon=document.createElement("i"),this.tab.append(this.icon),this.bindAttr("icon",function(n){n?this.icon.className=`phpdebugbar-icon phpdebugbar-icon-${n}`:this.icon.className=""});const e=document.createElement("span");e.classList.add(T("text")),this.tab.append(e),this.bindAttr("title",e),this.badge=document.createElement("span"),this.badge.classList.add(T("badge")),this.tab.append(this.badge),this.bindAttr("badge",function(n){n!==null?(this.badge.textContent=n,this.badge.classList.add(T("visible"))):this.badge.classList.remove(T("visible"))}),this.bindAttr("widget",function(n){this.el.innerHTML="",this.el.append(n.el)}),this.widgetRendered=!1,this.bindAttr("data",function(n){this.has("widget")&&(this.tab.setAttribute("data-empty",Object.keys(n).length===0||n.count===0),!this.widgetRendered&&this.active&&n!=null?this.renderWidgetData():this.widgetRendered=!1)})}renderWidgetData(){const e=this.get("data"),n=this.get("widget");e==null||!n||(n.set("data",e),s.utils.schedule(()=>{s.utils.sfDump(n.el)}),this.widgetRendered=!0)}show(){const e=T("active");this.tab.classList.add(e),this.tab.hidden=!1,this.el.classList.add(e),this.el.hidden=!1,this.active=!0,this.widgetRendered||this.renderWidgetData()}hide(){const e=T("active");this.tab.classList.remove(e),this.el.classList.remove(e),this.el.hidden=!0,this.active=!1}}class C extends g{get tagName(){return"span"}get className(){return T("indicator")}render(){this.icon=document.createElement("i"),this.el.append(this.icon),this.bindAttr("icon",function(n){n?this.icon.className=`phpdebugbar-icon phpdebugbar-icon-${n}`:this.icon.className=""}),this.bindAttr("link",function(n){n?(this.el.addEventListener("click",()=>{this.get("debugbar").showTab(n)}),this.el.style.cursor="pointer"):this.el.style.cursor=""});const e=document.createElement("span");e.classList.add(T("text")),this.el.append(e),this.bindAttr(["title","data"],e),this.tooltip=document.createElement("span"),this.tooltip.classList.add(T("tooltip"),T("disabled")),this.el.append(this.tooltip),this.bindAttr("tooltip",function(n){if(n)if(Array.isArray(n)||typeof n=="object"){const p=document.createElement("dl");for(const[y,M]of Object.entries(n)){const V=document.createElement("dt");V.textContent=y,p.append(V);const z=document.createElement("dd");z.textContent=M,p.append(z)}this.tooltip.innerHTML="",this.tooltip.append(p),this.tooltip.classList.remove(T("disabled"))}else this.tooltip.textContent=n,this.tooltip.classList.remove(T("disabled"));else this.tooltip.classList.add(T("disabled"))})}}class o extends g{get tagName(){return"form"}get className(){return T("settings")}initialize(e){this.set(e);const n=this.get("debugbar");this.settings=JSON.parse(localStorage.getItem("phpdebugbar-settings"))||{};for(const p in n.options)p in this.settings&&(n.options[p]=this.settings[p]),p==="theme"?n.setTheme(n.options[p]):n.el.setAttribute(`data-${p}`,n.options[p])}clearSettings(){const e=this.get("debugbar");if(localStorage.removeItem("phpdebugbar-settings"),localStorage.removeItem("phpdebugbar-ajaxhandler-autoshow"),this.settings={},e.options=pt({},e.defaultOptions),e.ajaxHandler){const n=e.ajaxHandler.defaultAutoShow;e.ajaxHandler.setAutoShow(n),this.set("autoshow",n),e.controls.__datasets&&e.controls.__datasets.get("widget").set("autoshow",this.autoshow.checked)}this.initialize(e.options)}storeSetting(e,n){this.settings[e]=n;const p=this.get("debugbar");p.options[e]=n,e!=="theme"&&p.el.setAttribute(`data-${e}`,n),localStorage.setItem("phpdebugbar-settings",JSON.stringify(this.settings))}render(){this.el.innerHTML="";const e=this.get("debugbar"),n=this,p={},y=document.createElement("select");y.innerHTML='<option value="auto">Auto (System preference)</option><option value="light">Light</option><option value="dark">Dark</option>',y.value=e.options.theme,y.addEventListener("change",function(){n.storeSetting("theme",this.value),e.setTheme(this.value)}),p.Theme=y;const M=document.createElement("select");M.innerHTML='<option value="bottomLeft">Bottom Left</option><option value="bottomRight">Bottom Right</option><option value="topLeft">Top Left</option><option value="topRight">Top Right</option>',M.value=e.options.openBtnPosition,M.addEventListener("change",function(){n.storeSetting("openBtnPosition",this.value),this.value==="topLeft"||this.value==="topRight"?n.storeSetting("toolbarPosition","top"):n.storeSetting("toolbarPosition","bottom"),n.get("debugbar").recomputeBottomOffset()}),p["Toolbar Position"]=M,this.hideEmptyTabs=document.createElement("input"),this.hideEmptyTabs.type="checkbox",this.hideEmptyTabs.checked=e.options.hideEmptyTabs,this.hideEmptyTabs.addEventListener("click",function(){n.storeSetting("hideEmptyTabs",this.checked),n.get("debugbar").respCSSSize=0,n.get("debugbar").resize()});const V=document.createElement("label");V.append(this.hideEmptyTabs,"Hide empty tabs until they have data"),p["Hide Empty Tabs"]=V,this.autoshow=document.createElement("input"),this.autoshow.type="checkbox",this.autoshow.checked=e.ajaxHandler&&e.ajaxHandler.autoShow,this.autoshow.addEventListener("click",function(){e.ajaxHandler&&e.ajaxHandler.setAutoShow(this.checked),e.controls.__datasets&&e.controls.__datasets.get("widget").set("autoshow",this.checked),e.datasetSwitcherWidget&&e.datasetSwitcherWidget.set("autoshow",this.checked)}),this.bindAttr("autoshow",function(){this.autoshow.checked=this.get("autoshow");const R=this.autoshow.closest(`.${T("form-row")}`);R&&(R.style.display="")});const z=document.createElement("label");z.append(this.autoshow,"Automatically show new incoming Ajax requests"),p.Autoshow=z;const G=document.createElement("button");G.textContent="Reset settings",G.addEventListener("click",R=>{R.preventDefault(),n.clearSettings(),n.render()}),p["Reset to defaults"]=G;for(const[R,_]of Object.entries(p)){const N=document.createElement("div");N.classList.add(T("form-row"));const E=document.createElement("div");E.classList.add(T("form-label")),E.textContent=R,N.append(E);const S=document.createElement("div");S.classList.add(T("form-input")),_ instanceof HTMLElement?S.append(_):S.innerHTML=_,N.append(S),n.el.append(N)}e.ajaxHandler||(this.autoshow.closest(`.${T("form-row")}`).style.display="none")}}class k{constructor(e){this.debugbar=e}format(e,n,p,y){if(p=p?` ${p}`:"",y=y||Object.keys(this.debugbar.datasets).length,n.__meta===void 0)return`#${y}${p}`;const M=n.__meta.uri.split("/");let V=M.pop();V||(V=`${M.pop()||""}/`),M.length&&!Number.isNaN(V)&&(V=`${M.pop()}/${V}`);const z=150;return V.length>z&&(V=`${V.substr(0,z)}...`),`#${y} ${V}${p} (${n.__meta.datetime.split(" ")[1]})`}}s.DatasetTitleFormater=k;class W extends g{get className(){return"phpdebugbar"}initialize(e={}){this.options=Object.assign({bodyBottomInset:!0,theme:"auto",toolbarPosition:"bottom",openBtnPosition:"bottomLeft",hideEmptyTabs:!1,spaNavigationEvents:[]},e),this.defaultOptions=pt({},this.options),this.controls={},this.dataMap={},this.datasets={},this.firstTabName=null,this.activePanelName=null,this.activeDatasetId=null,this.pendingDataSetId=null,this.datesetTitleFormater=new k(this);const n=window.getComputedStyle(document.body);this.bodyPaddingBottomHeight=Number.parseInt(n.paddingBottom),this.bodyPaddingTopHeight=Number.parseInt(n.paddingTop);try{this.isIframe=window.self!==window.top&&window.top.PhpDebugBar&&window.top.PhpDebugBar}catch(p){this.isIframe=!1}this.registerResizeHandler(),this.registerMediaListener(),this.registerNavigationListener(),this.settingsControl=new s.DebugBar.Tab({icon:"adjustments-horizontal",title:"Settings",widget:new o({debugbar:this})})}registerResizeHandler(){if(this.resize.bind===void 0||this.isIframe)return;const e=this.resize.bind(this);this.respCSSSize=0,window.addEventListener("resize",e),setTimeout(e,20)}registerMediaListener(){window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change",n=>{this.options.theme==="auto"&&this.setTheme("auto")})}registerNavigationListener(){const e=this.options.spaNavigationEvents;if(!(!e||!e.length))for(const n of e)document.addEventListener(n,()=>{this.recalculateBodyPadding()})}recalculateBodyPadding(){if(!this.options.bodyBottomInset)return;document.body.style.paddingTop="",document.body.style.paddingBottom="";const e=window.getComputedStyle(document.body);this.bodyPaddingTopHeight=Number.parseFloat(e.paddingTop),this.bodyPaddingBottomHeight=Number.parseFloat(e.paddingBottom),this.recomputeBottomOffset()}setTheme(e){this.options.theme=e,e==="auto"&&(e=window.matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light"),this.el.setAttribute("data-theme",e),this.openHandler&&this.openHandler.el.setAttribute("data-theme",e),this.datasetSwitcherWidget&&this.datasetSwitcherWidget.panel&&this.datasetSwitcherWidget.panel.setAttribute("data-theme",e)}resize(){if(this.isIframe)return;let e=this.respCSSSize;if(this.respCSSSize===0){const V=Array.from(this.header.children).filter(z=>z.offsetParent!==null);for(const z of V){const G=window.getComputedStyle(z);e+=z.offsetWidth+Number.parseFloat(G.marginLeft)+Number.parseFloat(G.marginRight)}}const n=this.header.offsetWidth,p=T("mini-design"),y=this.header.classList.contains(p);n<=e&&!y?(this.respCSSSize=e,this.header.classList.add(p)):e<n&&y&&(this.respCSSSize=0,this.header.classList.remove(p));const M=this.body.clientHeight||Number.parseInt(localStorage.getItem("phpdebugbar-height"),10)||300;this.setHeight(M)}render(){this.isIframe&&(this.el.hidden=!0);const e=this;document.body.append(this.el),this.dragCapture=document.createElement("div"),this.dragCapture.classList.add(T("drag-capture")),this.el.append(this.dragCapture),this.resizeHandle=document.createElement("div"),this.resizeHandle.classList.add(T("resize-handle")),this.resizeHandle.classList.add(T("resize-handle-top")),this.el.append(this.resizeHandle),this.header=document.createElement("div"),this.header.classList.add(T("header")),this.el.append(this.header),this.headerBtn=document.createElement("a"),this.headerBtn.classList.add(T("restore-btn")),this.header.append(this.headerBtn),this.headerBtn.addEventListener("click",()=>{e.close()}),this.headerLeft=document.createElement("div"),this.headerLeft.classList.add(T("header-left")),this.header.append(this.headerLeft),this.headerRight=document.createElement("div"),this.headerRight.classList.add(T("header-right")),this.header.append(this.headerRight),this.body=document.createElement("div"),this.body.classList.add(T("body")),this.el.append(this.body),this.recomputeBottomOffset(),this.resizeHandleBottom=document.createElement("div"),this.resizeHandleBottom.classList.add(T("resize-handle")),this.resizeHandleBottom.classList.add(T("resize-handle-bottom")),this.el.append(this.resizeHandleBottom);let n,p;const y=z=>{const G=p+(n-z.pageY);e.setHeight(G)},M=z=>{const G=p-(n-z.pageY);e.setHeight(G)},V=()=>{document.removeEventListener("mousemove",y),document.removeEventListener("mousemove",M),document.removeEventListener("mouseup",V),e.dragCapture.style.display="none"};this.resizeHandle.addEventListener("mousedown",z=>{p=e.body.offsetHeight,n=z.pageY,document.addEventListener("mousemove",y),document.addEventListener("mouseup",V),e.dragCapture.style.display="",z.preventDefault()}),this.resizeHandleBottom.addEventListener("mousedown",z=>{p=e.body.offsetHeight,n=z.pageY,document.addEventListener("mousemove",M),document.addEventListener("mouseup",V),e.dragCapture.style.display="",z.preventDefault()}),this.closebtn=document.createElement("a"),this.closebtn.classList.add(T("close-btn")),this.headerRight.append(this.closebtn),this.closebtn.addEventListener("click",()=>{e.close()}),this.minimizebtn=document.createElement("a"),this.minimizebtn.classList.add(T("minimize-btn")),this.minimizebtn.hidden=!this.isMinimized(),this.headerRight.append(this.minimizebtn),this.minimizebtn.addEventListener("click",()=>{e.minimize()}),this.maximizebtn=document.createElement("a"),this.maximizebtn.classList.add(T("maximize-btn")),this.maximizebtn.hidden=this.isMinimized(),this.headerRight.append(this.maximizebtn),this.maximizebtn.addEventListener("click",()=>{e.restore()}),this.restorebtn=document.createElement("a"),this.restorebtn.classList.add(T("restore-btn")),this.restorebtn.hidden=!0,this.el.append(this.restorebtn),this.restorebtn.addEventListener("click",()=>{e.restore()}),this.openbtn=document.createElement("a"),this.openbtn.classList.add(T("open-btn")),this.openbtn.hidden=!0,this.headerRight.append(this.openbtn),this.openbtn.addEventListener("click",()=>{e.openHandler.show((z,G)=>{e.addDataSet(G,z,"(opened)")})}),this.datasetsSelectSpan=document.createElement("span"),this.datasetsSelectSpan.classList.add(T("datasets-switcher")),this.datasetsSelectSpan.setAttribute("name","datasets-switcher"),this.datasetsSelect=document.createElement("select"),this.datasetsSelect.hidden=!0,this.datasetsSelectSpan.append(this.datasetsSelect),this.headerRight.append(this.datasetsSelectSpan),this.datasetsSelect.addEventListener("change",function(){e.showDataSet(this.value)}),this.controls.__settings=this.settingsControl,this.settingsControl.tab.classList.add(T("tab-settings")),this.settingsControl.tab.setAttribute("data-collector","__settings"),this.settingsControl.el.setAttribute("data-collector","__settings"),this.settingsControl.el.hidden=!0,this.maximizebtn.after(this.settingsControl.tab),this.settingsControl.tab.hidden=!1,this.settingsControl.tab.addEventListener("click",()=>{!this.isMinimized()&&this.activePanelName==="__settings"?this.minimize():(this.showTab("__settings"),this.settingsControl.get("widget").render())}),this.body.append(this.settingsControl.el)}setHeight(e){const p=window.innerHeight-this.header.offsetHeight-10;e=Math.min(e,p),e=Math.max(e,40),this.body.style.height=`${e}px`,localStorage.setItem("phpdebugbar-height",e),this.recomputeBottomOffset()}restoreState(){if(this.isIframe)return;const e=localStorage.getItem("phpdebugbar-height");this.setHeight(Number.parseInt(e)||this.body.offsetHeight);const n=localStorage.getItem("phpdebugbar-open");if(n&&n==="0")this.close();else{const p=localStorage.getItem("phpdebugbar-visible");if(p&&p==="1"){const y=localStorage.getItem("phpdebugbar-tab");this.isTab(y)?this.showTab(y):this.showTab()}else this.minimize()}}createTab(e,n,p){const y=new d({title:p||e.replace(/[_-]/g," ").charAt(0).toUpperCase()+e.slice(1),widget:n});return this.addTab(e,y)}addTab(e,n){if(this.isControl(e))throw new Error(`${e} already exists`);const p=this;return this.headerLeft.append(n.tab),n.tab.addEventListener("click",()=>{!p.isMinimized()&&p.activePanelName===e?p.minimize():(p.restore(),p.showTab(e))}),n.tab.setAttribute("data-empty",!0),n.tab.setAttribute("data-collector",e),n.el.setAttribute("data-collector",e),this.body.append(n.el),this.controls[e]=n,this.firstTabName===null&&(this.firstTabName=e),n}createIndicator(e,n,p,y){const M=new C({icon:n,tooltip:p});return this.addIndicator(e,M,y)}addIndicator(e,n,p){if(this.isControl(e))throw new Error(`${e} already exists`);return n.set("debugbar",this),p==="left"?this.headerLeft.prepend(n.el):this.headerRight.append(n.el),this.controls[e]=n,n}getControl(e){if(this.isControl(e))return this.controls[e]}isControl(e){return this.controls[e]!==void 0}isTab(e){return this.isControl(e)&&this.controls[e]instanceof d}isIndicator(e){return this.isControl(e)&&this.controls[e]instanceof C}reset(){this.minimize();for(const[e,n]of Object.entries(this.controls))this.isTab(e)&&n.tab.remove(),n.el.remove();this.controls={}}showTab(e){if(e||(this.activePanelName?e=this.activePanelName:e=this.firstTabName),!this.isTab(e))throw new Error(`Unknown tab '${e}'`);this.body.hidden=!1,this.recomputeBottomOffset();for(const[n,p]of Object.entries(this.controls))p instanceof d&&(n===e?p.show():p.hide());this.activePanelName=e,this.el.classList.remove(T("minimized")),localStorage.setItem("phpdebugbar-visible","1"),localStorage.setItem("phpdebugbar-tab",e),this.maximize()}minimize(){const e=T("active"),n=this.header.querySelectorAll(`:scope > div > .${e}`);for(const p of n)p.classList.remove(e);this.body.hidden=!0,this.minimizebtn.hidden=!0,this.maximizebtn.hidden=!1,this.recomputeBottomOffset(),localStorage.setItem("phpdebugbar-visible","0"),this.el.classList.add(T("minimized")),this.resize()}maximize(){this.header.hidden=!1,this.restorebtn.hidden=!0,this.body.hidden=!1,this.minimizebtn.hidden=!1,this.maximizebtn.hidden=!0,this.recomputeBottomOffset(),localStorage.setItem("phpdebugbar-visible","1"),localStorage.setItem("phpdebugbar-open","1"),this.el.classList.remove(T("minimized")),this.el.classList.remove(T("closed")),this.resize()}isMinimized(){return this.el.classList.contains(T("minimized"))}close(){this.header.hidden=!0,this.body.hidden=!0,this.restorebtn.hidden=!1,localStorage.setItem("phpdebugbar-open","0"),this.el.classList.add(T("closed")),this.recomputeBottomOffset()}isClosed(){return this.el.classList.contains(T("closed"))}restore(){const e=localStorage.getItem("phpdebugbar-tab");this.pendingDataSetId&&(this.dataChangeHandler(this.datasets[this.pendingDataSetId]),this.pendingDataSetId=null),this.isTab(e)?this.showTab(e):this.showTab()}recomputeBottomOffset(){if(this.options.bodyBottomInset){if(this.isClosed()){document.body.style.paddingBottom=this.bodyPaddingBottomHeight?`${this.bodyPaddingBottomHeight}px`:"",document.body.style.paddingTop=this.bodyPaddingTopHeight?`${this.bodyPaddingTopHeight}px`:"";return}if(this.options.toolbarPosition==="top"){const e=this.el.offsetHeight+(this.bodyPaddingTopHeight||0);document.body.style.paddingTop=`${e}px`,document.body.style.paddingBottom=this.bodyPaddingBottomHeight?`${this.bodyPaddingBottomHeight}px`:""}else{const e=this.el.offsetHeight+(this.bodyPaddingBottomHeight||0);document.body.style.paddingBottom=`${e}px`,document.body.style.paddingTop=this.bodyPaddingTopHeight?`${this.bodyPaddingTopHeight}px`:""}}}setDataMap(e){this.dataMap=e}addDataMap(e){Object.assign(this.dataMap,e)}setData(e){return this.datasets={},this.addDataSet(e)}addDataSet(e,n,p,y){if(!e||!e.__meta)return;if(this.isIframe&&window.top.PhpDebugBar&&window.top.PhpDebugBar.instance){window.top.PhpDebugBar.instance.addDataSet(e,n,`(iframe)${p||""}`,y);return}const M=Object.keys(this.datasets).length+1;n=n||M,e.__meta.nb=M,e.__meta.suffix=p,this.datasets[n]=e;const V=this.datesetTitleFormater.format(n,this.datasets[n],p,M);if(this.datasetSwitcherWidget)this.datasetSwitcherWidget.set("data",this.datasets);else{const z=document.createElement("option");z.value=n,z.textContent=V,this.datasetsSelect.append(z),this.datasetsSelect.hidden=!1}return(y===void 0||y)&&this.showDataSet(n),this.resize(),n}loadDataSet(e,n,p,y){if(!this.openHandler)throw new Error("loadDataSet() needs an open handler");const M=this;this.openHandler.load(e,V=>{M.addDataSet(V,e,n,y),M.resize(),p&&p(V)})}getDataSet(e){return this.datasets[e]}showDataSet(e){this.activeDatasetId=e,this.isClosed()?this.pendingDataSetId=e:(this.dataChangeHandler(this.datasets[e]),this.pendingDataSetId=null),this.datasetSwitcherWidget?this.datasetSwitcherWidget.set("activeId",e):this.datasetsSelect.value=e}dataChangeHandler(e){for(const[n,p]of Object.entries(this.dataMap)){const y=B(e,p[0],p[1]);if(n.includes(":")){const M=n.split(":");this.getControl(M[0]).set(M[1],y)}else this.getControl(n).set("data",y)}this.isMinimized()||this.showTab(),this.resize()}setOpenHandler(e){this.openHandler=e,this.openHandler.el.setAttribute("data-theme",this.el.getAttribute("data-theme")),this.openbtn.hidden=e==null}getOpenHandler(){return this.openHandler}enableAjaxHandlerTab(){this.datasetsSelectSpan&&(this.datasetsSelectSpan.hidden=!0),this.datasetSwitcherWidget=new s.Widgets.DatasetWidget({debugbar:this}),this.openbtn.after(this.datasetSwitcherWidget.el)}}s.DebugBar=W,W.Tab=d,W.Indicator=C;class Y{constructor(e,n,p){this.debugbar=e,this.headerName=n||"phpdebugbar",this.autoShow=p===void 0?!0:p,this.defaultAutoShow=this.autoShow,localStorage.getItem("phpdebugbar-ajaxhandler-autoshow")!==null&&(this.autoShow=localStorage.getItem("phpdebugbar-ajaxhandler-autoshow")==="1"),e.controls.__settings&&e.controls.__settings.get("widget").set("autoshow",this.autoShow)}handle(e){const n=this.getHeader(e,`${this.headerName}-stack`);return n&&JSON.parse(n).forEach(y=>{this.debugbar.loadDataSet(y," (stacked)",null,!1)}),!!(this.loadFromId(e)||this.loadFromData(e))}getHeader(e,n){return e instanceof Response?e.headers.get(n):e instanceof XMLHttpRequest?e.getResponseHeader(n):null}setAutoShow(e){this.autoShow=e,localStorage.setItem("phpdebugbar-ajaxhandler-autoshow",e?"1":"0")}loadFromId(e){const n=this.extractIdFromHeaders(e);return n&&this.debugbar.openHandler?(this.debugbar.loadDataSet(n,"(ajax)",void 0,this.autoShow),!0):!1}extractIdFromHeaders(e){return this.getHeader(e,`${this.headerName}-id`)}loadFromData(e){const n=this.extractDataFromHeaders(e);if(!n)return!1;const p=this.parseHeaders(n);if(p.error)throw new Error(`Error loading debugbar data: ${p.error}`);return p.data&&this.debugbar.addDataSet(p.data,p.id,"(ajax)",this.autoShow),!0}extractDataFromHeaders(e){let n=this.getHeader(e,this.headerName);if(n){for(let p=1;;p++){const y=this.getHeader(e,`${this.headerName}-${p}`);if(!y)break;n+=y}return decodeURIComponent(n)}}parseHeaders(e){return JSON.parse(e)}bindToFetch(){const e=this,n=window.fetch.__debugbar_original||window.fetch,p=n.bind(window);function y(...M){var z;const V=p(...M);return(z=V==null?void 0:V.then)==null||z.call(V,G=>e.handle(G)).catch(()=>{}),V}y.__debugbar_wrapped=!0,y.__debugbar_original=n,window.fetch=y}bindToXHR(){const e=this,n=XMLHttpRequest.prototype,p=(n.open||{}).__debugbar_original||n.open;if(typeof p!="function")return;function y(M,V,z=!0,G=null,R=null){return this.__debugbar_listener_attached||(this.__debugbar_listener_attached=!0,this.addEventListener("readystatechange",()=>{this.readyState===4&&e.handle(this)})),p.call(this,M,V,z,G,R)}y.__debugbar_wrapped=!0,y.__debugbar_original=p,n.open=y}}s.AjaxHandler=Y})();(function(){PhpDebugBar.Widgets={};const s=PhpDebugBar.utils.makecsscls("phpdebugbar-widgets-"),B=PhpDebugBar.Widgets.htmlize=function(G){return G.replace(/\n/g,"<br>").replace(/\s/g,"&nbsp;")};let T;const g=PhpDebugBar.Widgets.renderValue=function(G,R){return G&&typeof G=="object"&&"_sd"in G?(T||(T=new PhpDebugBar.Widgets.VarDumpRenderer),T.render(G)):typeof G!="string"?R?B(JSON.stringify(G,void 0,2)):JSON.stringify(G):G};PhpDebugBar.Widgets.renderValueInto=function(G,R,_){const N=g(R,_);N instanceof Node?G.append(N):G.insertAdjacentHTML("beforeend",N)};const d=PhpDebugBar.Widgets.editorLink=function(G){const R=document.createElement("span"),_=G.line?`#${G.line}`:"";if(R.classList.add(s("filename")),R.textContent=G.filename+_,G.path&&R.setAttribute("title",G.path+_),G.url){const N=document.createElement("a");N.classList.add(s("editor-link")),N.setAttribute(N.ajax?"title":"href",G.url),N.addEventListener("click",E=>{E.stopPropagation(),G.ajax&&(fetch(stmt.xdebug_link.url),E.preventDefault())}),R.append(N)}return R},C=PhpDebugBar.Widgets.highlight=function(G,R){if(typeof phpdebugbar_hljs=="undefined")return B(G);const _=phpdebugbar_hljs;return R&&_.getLanguage(R)?_.highlight(G,{language:R}).value:_.highlightAuto(G).value},o=PhpDebugBar.Widgets.createCodeBlock=function(G,R,_,N){const E=document.createElement("pre");E.classList.add(s("code-block"));const S=document.createElement("code");if(S.innerHTML=C(`${G}
`,R),E.append(S),!Number.isNaN(Number.parseFloat(_))){const r=G.split(`
`).length,u=document.createElement("ul");E.prepend(u);const l=Array.from(E.children);for(const h of l)h.classList.add(s("numbered-code"));for(let h=_;h<_+r;h++){const O=document.createElement("li");if(O.textContent=h,u.append(O),N===h){O.classList.add(s("highlighted-line"));const b=document.createElement("span");b.innerHTML="&nbsp;",O.append(b)}}}return E},{getDictValue:k}=PhpDebugBar.utils;class W extends PhpDebugBar.Widget{get tagName(){return"ul"}get className(){return s("list")}initialize(R){R.itemRenderer||(R.itemRenderer=this.itemRenderer),this.set(R)}render(){this.bindAttr(["itemRenderer","data"],function(){if(this.el.innerHTML="",!this.has("data"))return;const R=this.get("data");for(let _=0;_<R.length;_++){const N=document.createElement("li");N.classList.add(s("list-item")),this.el.append(N),this.get("itemRenderer")(N,R[_])}})}itemRenderer(R,_){R.innerHTML=g(_)}}PhpDebugBar.Widgets.ListWidget=W;class Y extends W{get tagName(){return"dl"}get className(){return s("kvlist")}render(){this.bindAttr(["itemRenderer","data"],function(){if(this.el.innerHTML="",!!this.has("data"))for(const[R,_]of Object.entries(this.get("data"))){const N=document.createElement("dt");N.classList.add(s("key")),this.el.append(N);const E=document.createElement("dd");E.classList.add(s("value")),this.el.append(E),this.get("itemRenderer")(N,E,R,_)}})}itemRenderer(R,_,N,E){R.textContent=N,_.innerHTML=B(E)}}PhpDebugBar.Widgets.KVListWidget=Y;class w extends Y{get className(){return s("kvlist varlist")}itemRenderer(R,_,N,E){const S=document.createElement("span");S.setAttribute("title",N),S.textContent=N,R.append(S);let r=E&&E.value||E;r&&r.length>100&&(r=`${r.substr(0,100)}...`);let u=null;_.textContent=r,_.addEventListener("click",()=>{if(window.getSelection().type==="Range")return"";_.classList.contains(s("pretty"))?(_.textContent=r,_.classList.remove(s("pretty"))):(u=u||o(E),_.classList.add(s("pretty")),_.innerHTML="",_.append(u))})}}PhpDebugBar.Widgets.VariableListWidget=w;class e extends Y{get className(){return s("kvlist htmlvarlist")}itemRenderer(R,_,N,E){const S=document.createElement("i");S.innerHTML=N!=null?N:"";const r=document.createElement("span");r.setAttribute("title",S.textContent),r.innerHTML=N!=null?N:"",R.append(r),_.innerHTML=E&&E.value||E,E!=null&&E.xdebug_link&&_.append(d(E.xdebug_link))}}PhpDebugBar.Widgets.HtmlVariableListWidget=e;class n extends PhpDebugBar.Widget{get tagName(){return"div"}get className(){return s("tablevarlist")}render(){this.bindAttr("data",function(R){var h,O,b;if(this.el.innerHTML="",!this.has("data"))return;this.table=document.createElement("table"),this.table.classList.add(s("tablevar")),this.el.append(this.table);const _=document.createElement("tr");_.classList.add(s("header"));const N=document.createElement("td");_.append(N),this.table.append(_);let E=R.key_map||{value:"Value"};Array.isArray(E)&&(E=Object.fromEntries(E.map(i=>[i,null])));for(const[i,v]of Object.entries(E)){const x=document.createElement("td");if(x.textContent=v!=null?v:i,_.append(x),R.badges&&R.badges[i]){const m=document.createElement("span");m.textContent=R.badges[i],m.classList.add(s("badge")),x.append(m)}}const S=this;if(!R.data)return;let r=!1;for(const[i,v]of Object.entries(R.data)){const x=document.createElement("tr");x.classList.add(s("item")),S.table.append(x);const m=document.createElement("td");if(m.classList.add(s("key")),m.textContent=i,x.append(m),typeof v!="object"||v===null){const a=document.createElement("td");a.classList.add(s("value")),a.textContent=v!=null?v:"",x.append(a);continue}for(const a of Object.keys(E)){const c=document.createElement("td");c.classList.add(s("value")),c.textContent=(h=v[a])!=null?h:"",x.append(c)}if(v.xdebug_link){const a=document.createElement("td");a.classList.add(s("editor")),a.append(d(v.xdebug_link)),x.append(a),r||(r=!0,_.append(document.createElement("td")))}}if(!R.summary)return;const u=document.createElement("tr");u.classList.add(s("summary")),S.table.append(u);const l=document.createElement("td");if(l.classList.add(s("key")),u.append(l),typeof R.summary!="object"||R.summary===null){const i=document.createElement("td");i.classList.add(s("value")),i.textContent=(O=R.summary)!=null?O:"",u.append(i)}else for(const i of Object.keys(E)){const v=document.createElement("td");v.classList.add(s("value")),v.textContent=(b=R.summary[i])!=null?b:"",u.append(v)}r&&u.append(document.createElement("td"))})}}PhpDebugBar.Widgets.TableVariableListWidget=n;class p extends PhpDebugBar.Widget{get tagName(){return"iframe"}get className(){return s("iframe")}render(){this.el.setAttribute("seamless","seamless"),this.el.setAttribute("border","0"),this.el.setAttribute("width","100%"),this.el.setAttribute("height","100%"),this.bindAttr("data",function(R){this.el.setAttribute("src",R)})}}PhpDebugBar.Widgets.IFrameWidget=p;class y extends PhpDebugBar.Widget{get className(){return s("messages")}render(){const R=this;this.list=new W({itemRenderer(N,E){let S;if(E.message_json)S=document.createElement("span"),S.classList.add(s("value")),PhpDebugBar.Widgets.renderValueInto(S,E.message_json),N.append(S);else if(E.message_html)S=document.createElement("span"),S.classList.add(s("value")),S.innerHTML=E.message_html,N.append(S);else{const r=E.message;if(S=document.createElement("span"),S.classList.add(s("value")),S.textContent=r,S.classList.add(s("truncated")),N.append(S),!E.is_string||S.scrollWidth>S.clientWidth){let u=E.message;E.is_string||(u=null),N.style.cursor="pointer",N.addEventListener("click",()=>{if(window.getSelection().type==="Range")return"";S.classList.contains(s("pretty"))?(S.textContent=r,S.classList.remove(s("pretty")),S.classList.add(s("truncated"))):(u=u||o(E.message),S.classList.add(s("pretty")),S.classList.remove(s("truncated")),S.innerHTML="",S.append(u))})}}if(E.collector){const r=document.createElement("span");r.classList.add(s("collector")),r.textContent=E.collector,N.prepend(r)}if(E.label){S.classList.add(s(E.label));const r=document.createElement("span");r.classList.add(s("label")),r.textContent=E.label,N.prepend(r)}if(E.context&&Object.keys(E.context).length>0){const r=document.createElement("span");r.setAttribute("title","Context"),r.classList.add(s("context-count")),r.textContent=Object.keys(E.context).length,N.prepend(r);const u=document.createElement("table");u.classList.add(s("params")),u.hidden=!0,u.innerHTML='<tr><th colspan="2">Context</th></tr>';const l=E.context_json||{};for(const h in E.context)if(typeof E.context[h]!="function"){const O=document.createElement("tr"),b=document.createElement("td");b.classList.add(s("name")),b.textContent=h,O.append(b);const i=document.createElement("td");i.classList.add(s("value")),l[h]?PhpDebugBar.Widgets.renderValueInto(i,l[h]):i.innerHTML=E.context[h],O.append(i),u.append(O)}N.append(u),N.style.cursor="pointer",N.addEventListener("click",h=>{window.getSelection().type==="Range"||h.target.closest(".sf-dump")||(u.hidden=!u.hidden)})}E.xdebug_link&&N.prepend(d(E.xdebug_link))}}),this.el.append(this.list.el),this.toolbar=document.createElement("div"),this.toolbar.classList.add(s("toolbar")),this.toolbar.innerHTML='<i class="phpdebugbar-icon phpdebugbar-icon-search"></i>',this.el.append(this.toolbar);const _=document.createElement("input");_.type="text",_.name="search",_.setAttribute("aria-label","Search"),_.placeholder="Search",_.addEventListener("change",function(){R.set("search",this.value)}),this.toolbar.append(_),this.bindAttr("data",function(N){this.set({excludelabel:[],excludecollector:[],search:""});const E=this.toolbar.querySelectorAll(`.${s("filter")}`);for(const O of E)O.remove();const S=[],r=[],u=this,l=function(O,b){const i=document.createElement("a");i.classList.add(s("filter")),i.classList.add(s(O)),i.textContent=b,i.setAttribute("rel",b),i.addEventListener("click",function(){u.onFilterClick(this,O)}),u.toolbar.append(i)};if(N.forEach(O=>{S.includes(O.label||"none")||S.push(O.label||"none"),r.includes(O.collector||"none")||r.push(O.collector||"none")}),S.length>1&&S.forEach(O=>l("label",O)),r.length===1)return;const h=document.createElement("a");h.classList.add(s("filter")),h.style.visibility="hidden",u.toolbar.append(h),r.forEach(O=>l("collector",O))}),this.bindAttr(["excludelabel","excludecollector","search"],function(){const N=this.get("excludelabel")||[],E=this.get("excludecollector")||[],S=this.get("search");let r=!1;const u=[];S&&S===S.toLowerCase()&&(r=!0),this.get("data").forEach(l=>{const h=r?l.message.toLowerCase():l.message;!N.includes(l.label||void 0)&&!E.includes(l.collector||void 0)&&(!S||h.includes(S))&&u.push(l)}),this.list.set("data",u)})}onFilterClick(R,_){R.classList.toggle(s("excluded"));const N=[],E=`.${s("filter")}.${s("excluded")}.${s(_)}`,S=this.toolbar.querySelectorAll(E);for(const r of S)N.push(r.rel==="none"||!r.rel?void 0:r.rel);this.set(`exclude${_}`,N)}}PhpDebugBar.Widgets.MessagesWidget=y;class M extends PhpDebugBar.Widget{get tagName(){return"ul"}get className(){return s("timeline")}render(){this.bindAttr("data",function(R){const _=function(E){return E<.001?`${(E*1e6).toFixed()}\u03BCs`:E<.1?`${(E*1e3).toFixed(2)}ms`:E<1?`${(E*1e3).toFixed()}ms`:`${E.toFixed(2)}s`},N=function(S){if(S===0||S===null)return"0B";const r=S<0?"-":"",u=Math.abs(S),l=Math.log(u)/Math.log(1024),h=["B","KB","MB","GB","TB"];return r+Math.round(Bt(1024,l-Math.floor(l))*100)/100+h[Math.floor(l)]};if(this.el.innerHTML="",R.measures){let E={};for(let u=0;u<R.measures.length;u++){const l=R.measures[u],h=l.group||l.label,O=l.values||[{relative_start:l.relative_start,duration:l.duration}];E[h]||(E[h]={count:0,duration:0,memory:0}),E[h].count+=O.length,E[h].duration+=l.duration,E[h].memory+=l.memory||0;const b=document.createElement("div");b.classList.add(s("measure"));const i=document.createElement("li");for(let x=0;x<O.length;x++){const m=(O[x].relative_start*100/R.duration).toFixed(2),a=Math.min((O[x].duration*100/R.duration).toFixed(2),100-m),c=document.createElement("span");c.classList.add(s("value")),c.style.left=`${m}%`,c.style.width=`${a}%`,b.append(c)}const v=document.createElement("span");if(v.classList.add(s("label")),v.textContent=(O.length>1?O.length+"x ":"")+l.label.replace(/\s+/g," ")+(l.duration?` (${l.duration_str}${l.memory?`/${l.memory_str}`:""})`:""),b.append(v),l.collector){const x=document.createElement("span");x.classList.add(s("collector")),x.textContent=l.collector,b.append(x)}if(i.append(b),this.el.append(i),l.params&&Object.keys(l.params).length>0){const x=document.createElement("table");x.classList.add(s("params")),x.hidden=!0,x.innerHTML='<tr><th colspan="2">Params</th></tr>';for(const m in l.params)if(typeof l.params[m]!="function"){const a=document.createElement("tr"),c=document.createElement("td");c.className=s("name"),c.textContent=m,a.append(c);const A=document.createElement("td");A.className=s("value"),PhpDebugBar.Widgets.renderValueInto(A,l.params[m]),a.append(A),x.append(a)}i.append(x),i.style.cursor="pointer",i.addEventListener("click",function(m){if(window.getSelection().type==="Range"||m.target.closest(".sf-dump"))return"";const a=this.querySelector("table");a.hidden=!a.hidden})}}E=Object.entries(E).map(([u,l])=>({label:u,data:l})).sort((u,l)=>l.data.duration-u.data.duration);const S=document.createElement("table");S.classList.add(s("params"));for(const u of E){const l=Math.min((u.data.duration*100/R.duration).toFixed(2),100),h=document.createElement("i");h.textContent=u.label.replace(/\s+/g," ");const O=h.innerHTML,b=document.createElement("tr");b.innerHTML=`<td class="${s("name")}">${u.data.count} x ${O} (${l}%)</td><td class="${s("value")}"><div class="${s("measure")}"><span class="${s("value")}"></span><span class="${s("label")}">${_(u.data.duration)}${u.data.memory?`/${N(u.data.memory)}`:""}</span></div></td>`,S.append(b);const i=b.querySelector(`span.${s("value")}`);i.style.width=`${l}%`}const r=document.createElement("li");r.append(S),this.el.append(r)}})}}PhpDebugBar.Widgets.TimelineWidget=M;class V extends PhpDebugBar.Widget{get className(){return s("exceptions")}render(){this.list=new W({itemRenderer(R,_){const N=document.createElement("span");if(N.classList.add(s("message")),N.textContent=_.message,_.count>1){const E=document.createElement("span");E.classList.add(s("badge")),E.textContent=`${_.count}x`,N.prepend(E)}if(R.append(N),_.file&&R.append(d(_.xdebug_link||{filename:_.file,line:_.line})),_.type){const E=document.createElement("span");E.classList.add(s("type")),E.textContent=_.type,R.append(E)}if(_.surrounding_lines){const E=_.line-3<=0?1:_.line-3,S=o(_.surrounding_lines.join(""),"php",E,_.line);S.classList.add(s("file")),S.hidden=!0,R.append(S),R.addEventListener("click",r=>{window.getSelection().type==="Range"||r.target.closest(".sf-dump")||(S.hidden=!S.hidden)})}if(_.stack_trace_html){const E=document.createElement("span");E.classList.add(s("filename")),E.innerHTML=_.stack_trace_html;const S=E.querySelectorAll('samp[data-depth="1"]');for(const r of S){r.classList.remove("sf-dump-expanded"),r.classList.add("sf-dump-compact");const u=r.parentElement.querySelector(":scope > .sf-dump-note");u&&(u.innerHTML=`${u.innerHTML.replace(/^array:/,'<span class="sf-dump-key">Stack Trace:</span> ')} files`)}R.append(E)}else _.stack_trace&&_.stack_trace.split(`
`).forEach(E=>{const S=document.createElement("div"),r=document.createElement("span");r.classList.add(s("filename")),r.textContent=E,S.append(r),R.append(S)})}}),this.el.append(this.list.el),this.bindAttr("data",function(R){if(this.list.set("data",R),R.length===1){const _=this.list.el.children[0];if(_){const N=_.querySelector(`.${s("file")}`);N&&(N.hidden=!1)}}})}}PhpDebugBar.Widgets.ExceptionsWidget=V;class z extends PhpDebugBar.Widget{get className(){return s("datasets-switcher-widget")}initialize(R){this.set(R);const _=this,N=this.get("debugbar");this.badge=document.createElement("div"),this.badge.classList.add(s("datasets-badge")),this.badge.hidden=!0,this.badgeCount=document.createElement("span"),this.badgeCount.classList.add(s("datasets-badge-count")),this.badge.append(this.badgeCount),this.badgeUrl=document.createElement("span"),this.badgeUrl.classList.add(s("datasets-badge-url")),this.badge.append(this.badgeUrl),this.panel=document.createElement("div"),this.panel.classList.add(s("datasets-panel")),this.panel.hidden=!0,N.el&&this.panel.setAttribute("data-theme",N.el.getAttribute("data-theme"));const E=document.createElement("div");E.classList.add(s("datasets-panel-toolbar"));const S=document.createElement("label");S.classList.add(s("datasets-autoshow")),this.autoshowCheckbox=document.createElement("input"),this.autoshowCheckbox.type="checkbox";const r=localStorage.getItem("phpdebugbar-ajaxhandler-autoshow");this.autoshowCheckbox.checked=r!==null?r==="1":N.ajaxHandler?N.ajaxHandler.autoShow:!0,this.autoshowCheckbox.addEventListener("change",function(){N.ajaxHandler&&N.ajaxHandler.setAutoShow(this.checked),N.controls.__settings&&N.controls.__settings.get("widget").set("autoshow",this.checked),N.controls.__datasets&&N.controls.__datasets.get("widget").set("autoshow",this.checked)}),S.append(this.autoshowCheckbox),S.append(document.createTextNode(" Autoshow")),E.append(S),this.refreshBtn=document.createElement("a"),this.refreshBtn.tabIndex=0,this.refreshBtn.classList.add(s("datasets-refresh-btn")),this.refreshBtn.innerHTML='<i class="phpdebugbar-icon phpdebugbar-icon-refresh"></i>',this.refreshBtn.title="Auto-scan for new datasets",this.isScanning=!1,this.refreshBtn.addEventListener("click",h=>{h.stopPropagation(),this.isScanning?(this.isScanning=!1,this.refreshBtn.classList.remove(s("active")),this.refreshBtn.title="Auto-scan for new datasets"):(this.isScanning=!0,this.refreshBtn.classList.add(s("active")),this.refreshBtn.title="Stop auto-scanning",this.scanForNewDatasets())}),E.append(this.refreshBtn);const u=document.createElement("a");u.tabIndex=0,u.classList.add(s("datasets-clear-btn")),u.textContent="Clear",u.addEventListener("click",()=>{const h=N.activeDatasetId,O=N.datasets[h];N.datasets={},O&&N.addDataSet(O,h,O.__meta.suffix,!0),this.panel.hidden=!0}),E.append(u),this.searchInput=document.createElement("input"),this.searchInput.type="search",this.searchInput.placeholder="Search",this.searchInput.classList.add(s("datasets-search")),this.searchInput.addEventListener("input",()=>{_.applySearchFilter()}),E.append(this.searchInput),this.panel.append(E),this.list=document.createElement("div"),this.list.classList.add(s("datasets-list")),this.panel.append(this.list),this.el.append(this.badge),document.body.append(this.panel);const l=()=>{const h=this.badge.getBoundingClientRect(),O=h.top,b=window.innerHeight-h.bottom,i=b>O;this.panel.style.position="fixed",this.panel.style.right=`${window.innerWidth-h.right}px`,this.panel.style.left="auto",i?(this.panel.style.top=`${h.bottom}px`,this.panel.style.bottom="auto",this.panel.style.maxHeight=`${b}px`):(this.panel.style.bottom=`${window.innerHeight-h.top}px`,this.panel.style.top="auto",this.panel.style.maxHeight=`${O}px`),this.refreshBtn.hidden=!N.openHandler};this.badge.addEventListener("click",h=>{h.target!==this.panel&&!this.panel.contains(h.target)&&(this.panel.hidden&&l(),this.panel.hidden=!this.panel.hidden)}),document.addEventListener("click",h=>{!this.badge.contains(h.target)&&!this.panel.contains(h.target)&&(this.panel.hidden=!0)})}render(){this.bindAttr("data",function(){this.updateBadge()}),this.bindAttr("activeId",function(){this.updateBadge()}),this.bindAttr("autoshow",function(){this.autoshowCheckbox&&(this.autoshowCheckbox.checked=this.get("autoshow"))})}updateBadge(){const R=this.get("debugbar"),_=this.get("data")||R.datasets,N=this.get("activeId")||R.activeDatasetId;if(!_){this.badge.hidden=!0;return}const E=Object.keys(_).length;if(E>=1){this.badge.hidden=!1;const S=_[N];if(S&&S.__meta){const u=S.__meta.uri||"",l=S.__meta.method||"GET";this.badgeUrl.textContent=`${l} ${u}`}E>1?(this.badgeCount.textContent=E,this.badgeCount.hidden=!1):this.badgeCount.hidden=!0,this.list.innerHTML="";const r=Object.keys(_).sort((u,l)=>{var b,i;const h=((b=_[u].__meta)==null?void 0:b.utime)||0;return(((i=_[l].__meta)==null?void 0:i.utime)||0)-h});for(const u of r){const l=_[u],h=document.createElement("div");h.classList.add(s("datasets-list-item"));const O=l.__meta.uri||"",b=l.__meta.method||"GET";h.setAttribute("data-url",O),h.setAttribute("data-method",b),u===N&&h.classList.add(s("active"));const i=document.createElement("span");i.classList.add(s("datasets-item-nb")),i.textContent=`#${l.__meta.nb}`,h.append(i);const v=document.createElement("span");v.classList.add(s("datasets-item-time")),v.textContent=l.__meta.datetime?l.__meta.datetime.split(" ")[1]:"",h.append(v);const x=document.createElement("div");x.classList.add(s("datasets-item-request"));const m=document.createElement("span");m.classList.add(s("datasets-item-method")),m.textContent=b,x.append(m);const a=document.createElement("span");if(a.classList.add(s("datasets-item-url")),a.textContent=O,x.append(a),l.__meta.suffix){const A=document.createElement("span");A.classList.add(s("datasets-item-suffix")),A.textContent=` ${l.__meta.suffix}`,x.append(A)}h.append(x);const c=document.createElement("div");c.classList.add(s("datasets-item-badges"));for(const[A,Z]of Object.entries(R.dataMap)){const f=k(l,Z[0],Z[1]);if(A.includes(":")){const D=A.split(":"),Q=D[0];if(D[1]==="badge"&&f>0){const ne=R.getControl(Q);if(ne){const se=document.createElement("span");if(se.classList.add(s("datasets-item-badge")),se.setAttribute("title",ne.get("title")),se.dataset.tab=Q,ne.icon){const ie=ne.icon.cloneNode(!0);ie.style.width="12px",ie.style.height="12px",se.append(ie)}const le=document.createElement("span");le.textContent=f,se.append(le),c.append(se),se.addEventListener("click",ie=>{ie.stopPropagation(),R.showDataSet(u),R.showTab(Q),this.panel.hidden=!0})}}}}h.append(c),h.addEventListener("click",()=>{R.showDataSet(u),this.panel.hidden=!0}),this.list.append(h)}this.applySearchFilter()}else this.badge.hidden=!0}applySearchFilter(){const R=this.searchInput.value.toLowerCase().trim(),_=this.list.querySelectorAll(`.${s("datasets-list-item")}`);for(const N of _)if(R==="")N.hidden=!1;else{const E=N.getAttribute("data-url").toLowerCase(),r=`${N.getAttribute("data-method").toLowerCase()} ${E}`,l=R.split(/\s+/).filter(h=>h.length>0).every(h=>r.includes(h));N.hidden=!l}}scanForNewDatasets(){const R=this.get("debugbar");if(!this.isScanning||!R.openHandler)return;const _=R.datasets,N=Object.values(_).reduce((S,r)=>{var u;return Math.max(S,((u=r.__meta)==null?void 0:u.utime)||0)},0),E=()=>{this.isScanning&&setTimeout(()=>this.scanForNewDatasets(),1e3)};R.openHandler.find({utime:N},0,(S,r)=>{if(r){console.error("scanForNewDatasets: find() failed",r),this.isScanning=!1,this.refreshBtn.classList.remove(s("active")),this.refreshBtn.title="Error scanning";return}try{const u=S.filter(h=>h.utime>N&&!_[h.id]);u.reverse();const l=(h=0)=>{if(h>=u.length){E();return}const{id:O}=u[h],b=h===u.length-1;R.loadDataSet(O,"(scan)",()=>l(h+1),this.autoshowCheckbox.checked&&b)};u.length?l():E()}catch(u){console.error("scanForNewDatasets: unexpected error",u),this.refreshBtn.classList.remove(s("active")),this.refreshBtn.title="Error scanning",this.isScanning=!1}})}}PhpDebugBar.Widgets.DatasetWidget=z})();(function(){const s=function(B){return PhpDebugBar.utils.csscls(B,"phpdebugbar-openhandler-")};PhpDebugBar.OpenHandler=PhpDebugBar.Widget.extend({className:"phpdebugbar-openhandler",defaults:{items_per_page:20},render(){const B=this;document.body.append(this.el),this.el.style.display="none",this.closebtn=document.createElement("a"),this.closebtn.classList.add(s("closebtn")),this.closebtn.innerHTML='<i class="phpdebugbar-icon phpdebugbar-icon-x"></i>',this.brand=document.createElement("span"),this.brand.classList.add(s("brand")),this.brand.innerHTML='<i class="phpdebugbar-icon phpdebugbar-icon-brand"></i>',this.table=document.createElement("tbody");const T=document.createElement("div");T.classList.add(s("header")),T.textContent="PHP DebugBar | Open",T.prepend(this.brand),T.append(this.closebtn),this.el.append(T);const g=document.createElement("table");g.innerHTML='<thead><tr><th width="155">Date</th><th width="75">Method</th><th>URL</th><th width="125">IP</th><th width="100">Filter data</th></tr></thead>',g.append(this.table),this.el.append(g),this.actions=document.createElement("div"),this.actions.classList.add(s("actions")),this.el.append(this.actions),this.closebtn.addEventListener("click",()=>{B.hide()}),this.loadmorebtn=document.createElement("a"),this.loadmorebtn.textContent="Load more",this.actions.append(this.loadmorebtn),this.loadmorebtn.addEventListener("click",()=>{B.find(B.last_find_request,B.last_find_request.offset+B.get("items_per_page"),B.handleFind.bind(B))}),this.showonlycurrentbtn=document.createElement("a"),this.showonlycurrentbtn.textContent="Show only current URL",this.actions.append(this.showonlycurrentbtn),this.showonlycurrentbtn.addEventListener("click",()=>{B.uriInput.value=window.location.pathname,B.searchBtn.click()}),this.refreshbtn=document.createElement("a"),this.refreshbtn.textContent="Refresh",this.actions.append(this.refreshbtn),this.refreshbtn.addEventListener("click",()=>{B.refresh()}),this.clearbtn=document.createElement("a"),this.clearbtn.textContent="Clear storage",this.actions.append(this.clearbtn),this.clearbtn.addEventListener("click",()=>{B.clear(()=>{B.hide()})}),this.addSearch(),this.overlay=document.createElement("div"),this.overlay.classList.add(s("overlay")),this.overlay.style.display="none",document.body.append(this.overlay),this.overlay.addEventListener("click",()=>{B.hide()})},refresh(){this.table.innerHTML="",this.loadmorebtn.style.display="",this.find({},0,this.handleFind.bind(this))},addSearch(){const B=this,T=this.searchBtn=document.createElement("button");T.textContent="Search",T.type="submit",T.addEventListener("click",function(C){B.table.innerHTML="";const o={},k=new FormData(this.parentElement);for(const[W,Y]of k.entries())Y&&(o[W]=Y);B.find(o,0,B.handleFind.bind(B)),C.preventDefault()});const g=document.createElement("form");g.innerHTML='<br/><b>Filter results</b><br/><select name="method"><option selected value="">(Method)</option><option>GET</option><option>POST</option><option>PUT</option><option>DELETE</option></select>',this.uriInput=document.createElement("input"),this.uriInput.type="text",this.uriInput.name="uri",this.uriInput.placeholder="URI, eg '/user/*'",g.append(this.uriInput),this.ipInput=document.createElement("input"),this.ipInput.type="text",this.ipInput.name="ip",this.ipInput.placeholder="IP",g.append(this.ipInput);const d=document.createElement("button");d.textContent="Reset",d.type="button",d.addEventListener("click",()=>{g.reset(),T.click()}),g.append(T),g.append(d),this.actions.append(g)},handleFind(B){const T=this;for(const g of B){const d=document.createElement("a");d.textContent="Load dataset",d.addEventListener("click",M=>{T.hide(),T.load(g.id,V=>{T.callback(g.id,V)}),M.preventDefault()});const C=document.createElement("a");C.textContent=g.method,C.addEventListener("click",M=>{T.table.innerHTML="",T.find({method:g.method},0,T.handleFind.bind(T)),M.preventDefault()});const o=document.createElement("a");o.textContent=g.uri,o.addEventListener("click",M=>{T.hide(),T.load(g.id,V=>{T.callback(g.id,V)}),M.preventDefault()});const k=document.createElement("a");k.textContent=g.ip,k.addEventListener("click",M=>{T.ipInput.value=g.ip,T.searchBtn.click(),M.preventDefault()});const W=document.createElement("a");W.textContent="Show URL",W.addEventListener("click",M=>{T.uriInput.value=g.uri,T.searchBtn.click(),M.preventDefault()});const Y=document.createElement("tr"),w=document.createElement("td");w.textContent=g.datetime,Y.append(w);const e=document.createElement("td");e.textContent=g.method,Y.append(e);const n=document.createElement("td");n.append(o),Y.append(n);const p=document.createElement("td");p.append(k),Y.append(p);const y=document.createElement("td");y.append(W),Y.append(y),T.table.append(Y)}B.length<this.get("items_per_page")&&(this.loadmorebtn.style.display="none")},show(B){this.callback=B,this.el.style.display="block",this.overlay.style.display="block",this.refresh()},hide(){this.el.style.display="none",this.overlay.style.display="none"},find(B,T,g){const d=Object.assign({op:"find"},B,{max:this.get("items_per_page"),offset:T||0});this.last_find_request=d,this.ajax(d,g)},load(B,T){this.ajax({op:"get",id:B},T)},clear(B){this.ajax({op:"clear"},B)},ajax(B,T){let g=this.get("url");B&&(g=g+(g.includes("?")?"&":"?")+new URLSearchParams(B)),fetch(g,{method:"GET",headers:{Accept:"application/json"}}).then(d=>d.json()).then(T).catch(d=>{T(null,d)})}})})();(function(){const s=PhpDebugBar.utils.makecsscls("phpdebugbar-widgets-");class B extends PhpDebugBar.Widget{get className(){return s("mails")}render(){this.list=new PhpDebugBar.Widgets.ListWidget({itemRenderer(g,d){const C=document.createElement("span");C.classList.add(s("subject")),C.textContent=d.subject,g.append(C);const o=document.createElement("span");if(o.classList.add(s("to")),o.textContent=d.to,g.append(o),d.body||d.html){const k=document.createElement("span");k.classList.add(s("filename")),k.textContent="";const W=document.createElement("a");W.setAttribute("title","Mail Preview"),W.textContent="View Mail",W.classList.add(s("editor-link")),W.addEventListener("click",()=>{const w=window.open("about:blank","Mail Preview","width=650,height=440,scrollbars=yes").document;let e="";if(d.headers){const M=document.createElement("pre");M.style.border="1px solid #ddd",M.style.padding="5px";const V=document.createElement("code");V.textContent=d.headers,M.append(V),e=M.outerHTML}const n=document.createElement("pre");n.style.border="1px solid #ddd",n.style.padding="5px",n.textContent=d.body;let p=n.outerHTML,y="";if(d.html){const M=document.createElement("details"),V=document.createElement("summary");V.textContent="Text version",M.append(V),M.append(n),p=M.outerHTML;const z=document.createElement("iframe");z.setAttribute("width","100%"),z.setAttribute("height","400px"),z.setAttribute("sandbox",""),z.setAttribute("referrerpolicy","no-referrer"),z.setAttribute("srcdoc",d.html),y=z.outerHTML}w.open(),w.write(e+p+y),w.close()}),k.append(W),g.append(k)}if(d.headers){const k=document.createElement("pre");k.classList.add(s("headers"));const W=document.createElement("code");W.textContent=d.headers,k.append(W),k.hidden=!0,g.append(k),g.addEventListener("click",()=>{k.hidden=!k.hidden})}}}),this.el.append(this.list.el),this.bindAttr("data",function(g){this.list.set("data",g)})}}PhpDebugBar.Widgets.MailsWidget=B})();(function(){const s=PhpDebugBar.utils.makecsscls("phpdebugbar-widgets-");class B extends PhpDebugBar.Widget{get className(){return s("sqlqueries")}onFilterClick(g){g.classList.toggle(s("excluded"));const d=g.getAttribute("rel"),C=this.list.el.querySelectorAll(`li[connection="${d}"]`);for(const o of C)o.hidden=!o.hidden}onCopyToClipboard(g){const d=g.parentElement.querySelector("code"),C=function(){try{document.execCommand("copy")&&(g.classList.add(s("copy-clipboard-check")),setTimeout(()=>{g.classList.remove(s("copy-clipboard-check"))},2e3))}catch(k){console.log("Oops, unable to copy")}};(function(k){if(document.selection){const W=document.body.createTextRange();W.moveToElementText(k),W.select()}else if(window.getSelection){const W=document.createRange();W.selectNodeContents(k),window.getSelection().removeAllRanges(),window.getSelection().addRange(W)}C(),window.getSelection().removeAllRanges()})(d)}renderList(g,d,C){const o=document.createElement("thead"),k=document.createElement("tr"),W=document.createElement("th");W.colSpan=2,W.classList.add(s("name")),W.innerHTML=d,k.append(W),o.append(k),g.append(o);const Y=document.createElement("tbody");for(const w in C){const e=typeof C[w]=="function"?`${C[w].name} {}`:C[w],n=document.createElement("tr");if(typeof e=="object"&&e!==null){const p=document.createElement("td");p.classList.add("phpdebugbar-text-muted"),p.textContent=e.index||w,n.append(p);const y=document.createElement("td");if(e.namespace&&y.append(`${e.namespace}::`),y.append(e.name||e.file),e.line){const M=document.createElement("span");M.classList.add("phpdebugbar-text-muted"),M.textContent=`:${e.line}`,y.append(M)}}else{const p=document.createElement("td");p.classList.add("phpdebugbar-text-muted"),p.textContent=w,n.append(p);const y=document.createElement("td");y.classList.add("phpdebugbar-text-muted"),y.textContent=e,n.append(y)}Y.append(n)}g.append(Y)}itemRenderer(g,d){if(d.type=d.type||"query",d.slow&&g.classList.add(s("sql-slow")),d.width_percent){const o=document.createElement("div");o.classList.add(s("bg-measure"));const k=document.createElement("div");k.classList.add(s("value")),k.style.left=`${d.start_percent}%`,k.style.width=`${Math.max(d.width_percent,.01)}%`,o.append(k),g.append(o)}if(d.duration_str){const o=document.createElement("span");o.setAttribute("title","Duration"),o.classList.add(s("duration")),o.textContent=d.duration_str,g.append(o)}if(d.memory_str){const o=document.createElement("span");o.setAttribute("title","Memory usage"),o.classList.add(s("memory")),o.textContent=d.memory_str,g.append(o)}if(typeof d.row_count!="undefined"){const o=document.createElement("span");o.setAttribute("title","Row count"),o.classList.add(s("row-count")),o.textContent=d.row_count,g.append(o)}if(typeof d.stmt_id!="undefined"&&d.stmt_id){const o=document.createElement("span");o.setAttribute("title","Prepared statement ID"),o.classList.add(s("stmt-id")),o.textContent=d.stmt_id,g.append(o)}if(d.connection){const o=document.createElement("span");if(o.setAttribute("title","Connection"),o.classList.add(s("database")),o.textContent=d.connection,g.append(o),g.setAttribute("connection",d.connection),!this.filters.includes(d.connection)){this.filters.push(d.connection);const k=document.createElement("a");k.classList.add(s("filter")),k.textContent=d.connection,k.setAttribute("rel",d.connection),k.addEventListener("click",()=>{this.onFilterClick(k)}),this.toolbar.append(k),this.filters.length>1&&(this.toolbar.hidden=!1)}}if(d.type==="query"){const o=document.createElement("span");o.setAttribute("title","Copy to clipboard"),o.classList.add(s("copy-clipboard")),o.style.cursor="pointer",o.innerHTML="&#8203;",o.addEventListener("click",k=>{this.onCopyToClipboard(o),k.stopPropagation()}),g.append(o)}if(d.xdebug_link?g.prepend(PhpDebugBar.Widgets.editorLink(d.xdebug_link)):typeof d.filename!="undefined"&&d.filename&&g.prepend(PhpDebugBar.Widgets.editorLink(d)),d.type!=="query"){const o=document.createElement("strong");o.classList.add(s("sql"),s(d.type)),o.textContent=d.sql,g.append(o)}else{const o=document.createElement("code");o.classList.add(s("sql")),o.innerHTML=PhpDebugBar.Widgets.highlight(d.sql,"sql"),g.append(o)}if(typeof d.is_success!="undefined"&&!d.is_success){g.classList.add(s("error"));const o=document.createElement("span");o.classList.add(s("error")),o.textContent=`[${d.error_code}] ${d.error_message}`,g.append(o)}if(d.type!=="query")return;const C=document.createElement("table");if(C.classList.add(s("params")),C.hidden=!0,d.params&&Object.keys(d.params).length>0&&this.renderList(C,"Params",d.params),d.backtrace&&Object.keys(d.backtrace).length>0){const o=[];for(const k of d.backtrace.values()){let W=k.name||k.file;k.line&&(W=`${W}:${k.line}`),o.push(W)}this.renderList(C,"Backtrace",o)}C.querySelectorAll("tr").length||(C.style.display="none"),g.append(C),g.style.cursor="pointer",g.addEventListener("click",o=>{if(window.getSelection().type==="Range"||o.target.closest(".sf-dump"))return"";C.hidden=!C.hidden;const k=g.querySelector("code");if(k&&typeof phpdebugbar_sqlformatter!="undefined"){let W=d.sql;C.hidden||(W=phpdebugbar_sqlformatter.format(d.sql)),k.innerHTML=PhpDebugBar.Widgets.highlight(W,"sql")}})}render(){this.status=document.createElement("div"),this.status.classList.add(s("status")),this.el.append(this.status),this.toolbar=document.createElement("div"),this.toolbar.classList.add(s("toolbar")),this.el.append(this.toolbar),this.filters=[];let g="none",d=null;this.list=new PhpDebugBar.Widgets.ListWidget({itemRenderer:(C,o)=>this.itemRenderer(C,o)}),this.el.append(this.list.el),this.bindAttr("data",function(C){if(C.length<=0||!C.statements)return!1;this.filters=[],this.toolbar.hidden=!0;const o=this.toolbar.querySelectorAll(`.${s("filter")}`);for(const w of o)w.remove();this.list.set("data",C.statements),this.status.innerHTML="";const k={};let W=0;for(let w=0;w<C.statements.length;w++){if(C.statements[w].type&&C.statements[w].type!=="query")continue;let e=C.statements[w].sql;C.statements[w].params&&Object.keys(C.statements[w].params).length>0&&(e+=JSON.stringify(C.statements[w].params)),C.statements[w].connection&&(e+=`@${C.statements[w].connection}`),k[e]=k[e]||{keys:[]},k[e].keys.push(w)}for(const w in k)if(k[w].keys.length>1){W+=k[w].keys.length;for(let e=0;e<k[w].keys.length;e++){const n=this.list.el.querySelectorAll(`.${s("list-item")}`);n[k[w].keys[e]]&&n[k[w].keys[e]].classList.add(s("sql-duplicate"))}}const Y=document.createElement("span");if(Y.textContent=`${C.nb_statements} statements were executed`,C.nb_excluded_statements&&(Y.textContent+=`, ${C.nb_excluded_statements} have been excluded`),this.status.append(Y),C.nb_failed_statements&&Y.append(`, ${C.nb_failed_statements} of which failed`),W){Y.append(`, ${W} of which were duplicates`),Y.append(`, ${C.nb_statements-W} unique. `);const w="Show only duplicated",e=document.createElement("a");e.classList.add(s("duplicates")),e.textContent=w,e.addEventListener("click",()=>{e.classList.toggle("shown-duplicated"),e.textContent=e.classList.contains("shown-duplicated")?"Show All":w;const n=`.${this.className} .${s("list-item")}:not(.${s("sql-duplicate")})`,p=document.querySelectorAll(n);for(const y of p)y.hidden=!y.hidden}),Y.append(e)}if(C.accumulated_duration_str){const w=document.createElement("span");w.setAttribute("title","Accumulated duration"),w.classList.add(s("duration")),w.textContent=C.accumulated_duration_str;const e=document.createElement("span");e.classList.add(s("sort-icon")),e.style.cursor="pointer",e.style.marginLeft="5px",e.textContent="Sort \u21C5",e.setAttribute("title","Sort by duration"),e.addEventListener("click",()=>{g==="none"?(g="desc",e.textContent="\u2193",d=[...C.statements],C.statements.sort((n,p)=>(p.duration||0)-(n.duration||0))):g==="desc"?(g="asc",e.textContent="\u2191",C.statements.sort((n,p)=>(n.duration||0)-(p.duration||0))):(g="none",e.textContent="\u21C5",d&&(C.statements=d,d=null)),this.list.set("data",C.statements)}),w.append(e),this.status.append(w)}if(C.memory_usage_str){const w=document.createElement("span");w.setAttribute("title","Memory usage"),w.classList.add(s("memory")),w.textContent=C.memory_usage_str,this.status.append(w)}})}}PhpDebugBar.Widgets.SQLQueriesWidget=B})();(function(){const s=PhpDebugBar.utils.makecsscls("phpdebugbar-widgets-");class B extends PhpDebugBar.Widget{get className(){return s("templates")}render(){this.status=document.createElement("div"),this.status.classList.add(s("status")),this.el.append(this.status),this.list=new PhpDebugBar.Widgets.ListWidget({itemRenderer(g,d){const C=document.createElement("span");if(C.classList.add(s("name")),d.html?C.innerHTML=d.html:C.textContent=d.name,g.append(C),d.xdebug_link&&g.append(PhpDebugBar.Widgets.editorLink(d.xdebug_link)),d.render_time_str){const o=document.createElement("span");o.setAttribute("title","Render time"),o.classList.add(s("render-time")),o.textContent=d.render_time_str,g.append(o)}if(d.memory_str){const o=document.createElement("span");o.setAttribute("title","Memory usage"),o.classList.add(s("memory")),o.textContent=d.memory_str,g.append(o)}if(typeof d.param_count!="undefined"){const o=document.createElement("span");o.setAttribute("title","Parameter count"),o.classList.add(s("param-count")),o.textContent=d.param_count,g.append(o)}if(typeof d.type!="undefined"&&d.type){const o=document.createElement("span");o.setAttribute("title","Type"),o.classList.add(s("type")),o.textContent=d.type,g.append(o)}if(typeof d.editorLink!="undefined"&&d.editorLink){const o=document.createElement("a");o.setAttribute("href",d.editorLink),o.classList.add(s("editor-link")),o.textContent="file",o.addEventListener("click",k=>{k.stopPropagation()}),g.append(o)}if(d.params&&Object.keys(d.params).length>0){const o=document.createElement("table");o.classList.add(s("params"));const k=document.createElement("thead");k.innerHTML='<tr><th colspan="2">Params</th></tr>';const W=document.createElement("tbody");o.append(k,W);for(const Y in d.params)if(typeof d.params[Y]!="function"){const w=document.createElement("tr"),e=document.createElement("td");e.className=s("name"),e.textContent=Y,w.append(e);const n=document.createElement("td");n.className=s("value"),PhpDebugBar.Widgets.renderValueInto(n,d.params[Y]),w.append(n),W.append(w)}o.hidden=!0,g.append(o),g.style.cursor="pointer",g.addEventListener("click",Y=>{window.getSelection().type==="Range"||Y.target.closest(".sf-dump")||(o.hidden=!o.hidden)})}}}),this.el.append(this.list.el),this.callgraph=document.createElement("div"),this.callgraph.classList.add(s("callgraph")),this.el.append(this.callgraph),this.bindAttr("data",function(g){this.list.set("data",g.templates),this.status.innerHTML="",this.callgraph.innerHTML="";const d=g.sentence||"templates were rendered",C=document.createElement("span");if(C.textContent=`${g.nb_templates} ${d}`,this.status.append(C),g.accumulated_render_time_str){const o=document.createElement("span");o.setAttribute("title","Accumulated render time"),o.classList.add(s("render-time")),o.textContent=g.accumulated_render_time_str,this.status.append(o)}if(g.memory_usage_str){const o=document.createElement("span");o.setAttribute("title","Memory usage"),o.classList.add(s("memory")),o.textContent=g.memory_usage_str,this.status.append(o)}if(g.nb_blocks>0){const o=document.createElement("div");o.textContent=`${g.nb_blocks} blocks were rendered`,this.status.append(o)}if(g.nb_macros>0){const o=document.createElement("div");o.textContent=`${g.nb_macros} macros were rendered`,this.status.append(o)}typeof g.callgraph!="undefined"&&(this.callgraph.innerHTML=g.callgraph)})}}PhpDebugBar.Widgets.TemplatesWidget=B})();(function(){const s=PhpDebugBar.utils.makecsscls("phpdebugbar-widgets-");class B extends PhpDebugBar.Widget{get className(){return s("httpclient")}render(){this.list=new PhpDebugBar.Widgets.ListWidget({itemRenderer(g,d){const C=document.createElement("div");C.classList.add(s("request-summary")),C.style.display="flex",C.style.gap="10px",C.style.alignItems="center";const o=document.createElement("span");o.classList.add(s("method")),o.textContent=d.method,o.style.fontWeight="bold",o.style.minWidth="60px",C.append(o);const k=document.createElement("span");k.classList.add(s("url")),k.textContent=d.url,k.style.flex="1",k.style.overflow="hidden",k.style.textOverflow="ellipsis",k.style.whiteSpace="nowrap",C.append(k);const W=document.createElement("span");if(W.classList.add(s("status")),W.textContent=d.status,W.style.minWidth="40px",W.style.textAlign="center",typeof d.status=="number"&&(d.status>=200&&d.status<300?W.style.color="#4caf50":d.status>=300&&d.status<400?W.style.color="#ff9800":d.status>=400&&(W.style.color="#f44336")),C.append(W),d.duration!==null&&typeof d.duration!="undefined"){const Y=document.createElement("span");Y.classList.add(s("duration")),Y.textContent=d.duration,Y.style.minWidth="60px",Y.style.textAlign="right",C.append(Y)}if(g.append(C),d.details&&Object.keys(d.details).length>0){const Y=document.createElement("table");Y.classList.add(s("params"));const w=document.createElement("thead");w.innerHTML='<tr><th colspan="2">Details</th></tr>';const e=document.createElement("tbody");Y.append(w,e);for(const n in d.details)if(typeof d.details[n]!="function"){const p=document.createElement("tr"),y=document.createElement("td");y.className=s("name"),y.textContent=n,p.append(y);const M=document.createElement("td");M.className=s("value"),PhpDebugBar.Widgets.renderValueInto(M,d.details[n]),p.append(M),e.append(p)}Y.hidden=!0,g.append(Y),g.style.cursor="pointer",g.addEventListener("click",n=>{window.getSelection().type==="Range"||n.target.closest(".sf-dump")||(Y.hidden=!Y.hidden)})}}}),this.el.append(this.list.el),this.bindAttr("data",function(g){this.list.set("data",g)})}}PhpDebugBar.Widgets.HttpWidget=B})();})();

(function () {
    const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    const lazyStore = new Map();
    let lazySeq = 0;

    /**
     * Renders a JSON variable dump as HTML with lazy-rendered collapsed nodes.
     *
     * Generates HTML using Sfdump CSS classes for styling compatibility. Collapsed
     * children are deferred until the user clicks to expand them. A document-level
     * click handler manages toggle/expand for these id-less <pre> elements, while
     * Sfdump continues to handle server-rendered HTML dumps (with IDs) unchanged.
     *
     * Usage:
     *   const renderer = new PhpDebugBar.Widgets.VarDumpRenderer({ expandedDepth: 1 });
     *   const el = renderer.render(jsonData);
     *   container.appendChild(el);
     */
    class VarDumpRenderer {
        constructor(options) {
            this.expandedDepth = (options && options.expandedDepth !== undefined) ? options.expandedDepth : 1;
        }

        render(data) {
            if (data && typeof data === 'object' && '_sd' in data) {
                const pre = document.createElement('pre');
                pre.className = 'sf-dump';
                pre.setAttribute('data-indent-pad', '  ');

                const savedDepth = this.expandedDepth;
                if (typeof data._sd === 'number') {
                    this.expandedDepth = data._sd;
                }
                pre.innerHTML = this.nodeToHtml(data, 0, '') + '\n';
                this.expandedDepth = savedDepth;

                return pre;
            }

            return data;
        }

        esc(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        nodeToHtml(node, depth, indent) {
            if (!node || typeof node !== 'object') {
                return '<span class=sf-dump-const>null</span>';
            }

            switch (node.t) {
                case 's':
                    return this.scalarToHtml(node);
                case 'r':
                    return this.stringToHtml(node);
                case 'h':
                    return this.hashToHtml(node, depth, indent);
                default:
                    return this.esc(JSON.stringify(node));
            }
        }

        scalarToHtml(node) {
            const s = node.s;
            if (s === 'b') {
                return '<span class=sf-dump-const>' + (node.v === true ? 'true' : 'false') + '</span>';
            }
            if (s === 'n') {
                return '<span class=sf-dump-const>null</span>';
            }
            if (s === 'i' || s === 'd') {
                return '<span class=sf-dump-num>' + this.esc(String(node.v)) + '</span>';
            }
            if (s === 'l') {
                return node.v ? '<span class=sf-dump-note>' + this.esc(node.v) + '</span>' : '';
            }
            return this.esc(String(node.v));
        }

        stringToHtml(node) {
            const totalLen = node.len || (node.v.length + (node.cut || 0));
            let html = '"<span class=sf-dump-str title="' + totalLen + ' characters">' + this.esc(node.v) + '</span>';
            if (node.cut > 0) {
                html += '…';
            }
            html += '"';
            return html;
        }

        hashToHtml(node, depth, indent) {
            const children = node.c || [];
            const hasChildren = children.length > 0;
            const ht = node.ht;
            const isObject = (ht === 4);
            const isResource = (ht === 5);
            const isArray = (ht === 1 || ht === 2);
            const closingChar = isArray ? ']' : '}';
            const childIndent = indent + '  ';
            const expanded = depth < this.expandedDepth;

            let html = '';

            // Header
            let ref = '';
            if (isObject) {
                if (node.cls) {
                    html += '<span class=sf-dump-note>' + this.esc(String(node.cls)) + '</span> ';
                }
                html += '{';
                if (node.ref && node.ref.s) {
                    ref = '<span class=sf-dump-ref>#' + this.esc(String(node.ref.s)) + '</span> ';
                }
            } else if (isResource) {
                html += '<span class=sf-dump-note>' + this.esc(String(node.cls || 'resource')) + '</span>';
                html += ' {';
            } else {
                // Array
                if (node.cls) {
                    html += '<span class=sf-dump-note>array:' + this.esc(String(node.cls)) + '</span> [';
                } else {
                    html += '[';
                }
            }

            // Empty hash
            if (!hasChildren && !node.cut) {
                if (ref) html += ref;
                html += closingChar;
                return html;
            }

            // Cut-only (no expandable children)
            if (!hasChildren && node.cut > 0) {
                if (ref) html += ref;
                html += ' …' + node.cut + closingChar;
                return html;
            }

            // Toggle anchor (includes ref if present)
            html += '<a class=sf-dump-toggle>' + ref + '<span>' + (expanded ? '▼' : '▶') + '</span></a>';

            if (expanded) {
                // Render children eagerly
                html += '<samp data-depth=' + (depth + 1) + ' class=sf-dump-expanded>';
                html += this.childrenToHtml(children, node.cut, depth, childIndent, indent, ht);
                html += '</samp>';
            } else {
                // Lazy placeholder — store data, emit empty samp
                const id = ++lazySeq;
                lazyStore.set(id, {
                    children: children,
                    cut: node.cut,
                    depth: depth,
                    childIndent: childIndent,
                    indent: indent,
                    ht: ht,
                    renderer: this,
                    expandedDepth: this.expandedDepth
                });
                html += '<samp data-depth=' + (depth + 1) + ' class=sf-dump-compact data-lazy=' + id + '></samp>';
            }

            html += closingChar;
            return html;
        }

        childrenToHtml(children, cut, depth, childIndent, indent, ht) {
            let html = '';
            for (let i = 0; i < children.length; i++) {
                const entry = children[i];
                html += '\n' + childIndent;

                // Infer missing kt from parent hash type
                let kt = entry.kt;
                if (kt === undefined) {
                    if (entry.k !== undefined || ht === 2) {
                        if (ht === 2) kt = 'i';               // HASH_INDEXED
                        else if (ht === 5) kt = 'meta';        // HASH_RESOURCE
                        else if (ht === 4) kt = 'pub';         // HASH_OBJECT default
                        else kt = (typeof entry.k === 'number') ? 'i' : 'k';  // HASH_ASSOC
                    }
                }

                // Infer missing k from loop index (HASH_INDEXED)
                const k = (entry.k !== undefined) ? entry.k : i;

                if (kt !== undefined) {
                    html += this.keyToHtml(kt, k, entry);
                }
                if (entry.ref) {
                    html += '<span class=sf-dump-ref>&amp;' + this.esc(String(entry.ref)) + '</span> ';
                }
                html += this.nodeToHtml(entry.n, depth + 1, childIndent);
            }
            if (cut > 0) {
                html += '\n' + childIndent + '…' + cut;
            }
            html += '\n' + indent;
            return html;
        }

        keyToHtml(kt, key, entry) {
            const k = this.esc(String(key));

            switch (kt) {
                case 'i':
                    return '<span class=sf-dump-index>' + k + '</span> => ';
                case 'k':
                    return '"<span class=sf-dump-key>' + k + '</span>" => ';
                case 'pub':
                    if (entry.dyn) {
                        return '+"<span class=sf-dump-public title="Runtime added dynamic property">' + k + '</span>": ';
                    }
                    return '+<span class=sf-dump-public title="Public property">' + k + '</span>: ';
                case 'pro':
                    return '#<span class=sf-dump-protected title="Protected property">' + k + '</span>: ';
                case 'pri': {
                    let title = 'Private property';
                    if (entry.kc) {
                        title += ' declared in ' + this.esc(entry.kc);
                    }
                    return '-<span class=sf-dump-private title="' + title + '">' + k + '</span>: ';
                }
                case 'meta':
                    return '<span class=sf-dump-meta>' + k + '</span>: ';
                default:
                    return k + ': ';
            }
        }
    }
    PhpDebugBar.Widgets.VarDumpRenderer = VarDumpRenderer;

    function expandLazy(samp) {
        const id = parseInt(samp.dataset.lazy, 10);
        delete samp.dataset.lazy;

        const data = lazyStore.get(id);
        if (!data) return;
        lazyStore.delete(id);

        const renderer = data.renderer;
        const savedDepth = renderer.expandedDepth;
        renderer.expandedDepth = data.expandedDepth;

        samp.innerHTML = renderer.childrenToHtml(data.children, data.cut, data.depth, data.childIndent, data.indent, data.ht);

        renderer.expandedDepth = savedDepth;
    }

    document.addEventListener('click', function (e) {
        const toggle = e.target.closest('a.sf-dump-toggle');
        if (!toggle) return;

        const pre = toggle.closest('pre.sf-dump');
        if (!pre || pre.id) return; // has id → belongs to Sfdump, skip

        const samp = toggle.nextElementSibling;
        if (!samp || samp.tagName !== 'SAMP') return;

        e.preventDefault();
        const isCompact = samp.classList.contains('sf-dump-compact');

        // Lazy expand if needed
        if (isCompact && samp.dataset.lazy) expandLazy(samp);

        // Ctrl/Meta+click → recursive
        if (e.ctrlKey || e.metaKey) {
            if (isCompact) {
                // Expand all lazy descendants first
                let pending;
                while ((pending = samp.querySelectorAll('[data-lazy]')).length) {
                    pending.forEach(expandLazy);
                }
                // Then expand all compact children
                samp.querySelectorAll('samp.sf-dump-compact').forEach(function (s) {
                    s.classList.replace('sf-dump-compact', 'sf-dump-expanded');
                    const arrow = s.previousElementSibling && s.previousElementSibling.lastElementChild;
                    if (arrow) arrow.textContent = '▼';
                });
            } else {
                // Collapse all expanded children
                samp.querySelectorAll('samp.sf-dump-expanded').forEach(function (s) {
                    s.classList.replace('sf-dump-expanded', 'sf-dump-compact');
                    const arrow = s.previousElementSibling && s.previousElementSibling.lastElementChild;
                    if (arrow) arrow.textContent = '▶';
                });
            }
        }

        // Toggle current
        samp.classList.toggle('sf-dump-compact', !isCompact);
        samp.classList.toggle('sf-dump-expanded', isCompact);
        toggle.lastElementChild.textContent = isCompact ? '▼' : '▶';
    });

    // ------------------------------------------------------------------

    /**
     * An extension of KVListWidget where values are rendered using VarDumpRenderer.
     * Drop-in replacement for HtmlVariableListWidget when using JsonDataFormatter.
     *
     * Options:
     *  - data
     */

    class JsonVariableListWidget extends PhpDebugBar.Widgets.KVListWidget {
        get className() {
            return csscls('kvlist jsonvarlist');
        }

        itemRenderer(dt, dd, key, value) {
            const span = document.createElement('span');
            span.setAttribute('title', key);
            span.textContent = key;
            dt.appendChild(span);

            const rawValue = (value && value.value !== undefined) ? value.value : value;
            PhpDebugBar.Widgets.renderValueInto(dd, rawValue);

            if (value && value.xdebug_link) {
                dd.appendChild(PhpDebugBar.Widgets.editorLink(value.xdebug_link));
            }
        }
    }
    PhpDebugBar.Widgets.JsonVariableListWidget = JsonVariableListWidget;
})();

/* global phpdebugbar_sqlformatter */
(function () {
    const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for the displaying sql queries
     *
     * Options:
     *  - data
     */
    class SQLQueriesWidget extends PhpDebugBar.Widget {
        get className() {
            return csscls('sqlqueries');
        }

        onFilterClick(el) {
            el.classList.toggle(csscls('excluded'));
            const connection = el.getAttribute('rel');
            const items = this.list.el.querySelectorAll(`li[connection="${connection}"]`);
            for (const item of items) {
                item.hidden = !item.hidden;
            }
        }

        onCopyToClipboard(el) {
            const code = el.parentElement.querySelector('code');
            const copy = function () {
                try {
                    if (document.execCommand('copy')) {
                        el.classList.add(csscls('copy-clipboard-check'));
                        setTimeout(() => {
                            el.classList.remove(csscls('copy-clipboard-check'));
                        }, 2000);
                    }
                } catch (err) {
                    console.log('Oops, unable to copy');
                }
            };
            const select = function (node) {
                if (document.selection) {
                    const range = document.body.createTextRange();
                    range.moveToElementText(node);
                    range.select();
                } else if (window.getSelection) {
                    const range = document.createRange();
                    range.selectNodeContents(node);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                }
                copy();
                window.getSelection().removeAllRanges();
            };
            select(code);
        }

        renderList(table, caption, data) {
            const thead = document.createElement('thead');

            const tr = document.createElement('tr');
            const nameTh = document.createElement('th');
            nameTh.colSpan = 2;
            nameTh.classList.add(csscls('name'));
            nameTh.innerHTML = caption;
            tr.append(nameTh);
            thead.append(tr);

            table.append(thead);

            const tbody = document.createElement('tbody');

            for (const key in data) {
                const value = typeof data[key] === 'function' ? `${data[key].name} {}` : data[key];
                const tr = document.createElement('tr');

                if (typeof value === 'object' && value !== null) {
                    const keyTd = document.createElement('td');
                    keyTd.classList.add('phpdebugbar-text-muted');
                    keyTd.textContent = value.index || key;
                    tr.append(keyTd);

                    const valueTd = document.createElement('td');
                    if (value.namespace) {
                        valueTd.append(`${value.namespace}::`);
                    }

                    valueTd.append(value.name || value.file);

                    if (value.line) {
                        const lineSpan = document.createElement('span');
                        lineSpan.classList.add('phpdebugbar-text-muted');
                        lineSpan.textContent = `:${value.line}`;
                        valueTd.append(lineSpan);
                    }
                } else {
                    const keyTd = document.createElement('td');
                    keyTd.classList.add('phpdebugbar-text-muted');
                    keyTd.textContent = key;
                    tr.append(keyTd);

                    const valueTd = document.createElement('td');
                    valueTd.classList.add('phpdebugbar-text-muted');
                    valueTd.textContent = value;
                    tr.append(valueTd);
                }

                tbody.append(tr);
            }

            table.append(tbody);
        }

        itemRenderer(li, stmt) {
            stmt.type = stmt.type || 'query';
            if (stmt.slow) {
                li.classList.add(csscls('sql-slow'));
            }
            if (stmt.width_percent) {
                const bgMeasure = document.createElement('div');
                bgMeasure.classList.add(csscls('bg-measure'));

                const valueDiv = document.createElement('div');
                valueDiv.classList.add(csscls('value'));
                valueDiv.style.left = `${stmt.start_percent}%`;
                valueDiv.style.width = `${Math.max(stmt.width_percent, 0.01)}%`;
                bgMeasure.append(valueDiv);
                li.append(bgMeasure);
            }
            if (stmt.duration_str) {
                const duration = document.createElement('span');
                duration.setAttribute('title', 'Duration');
                duration.classList.add(csscls('duration'));
                duration.textContent = stmt.duration_str;
                li.append(duration);
            }
            if (stmt.memory_str) {
                const memory = document.createElement('span');
                memory.setAttribute('title', 'Memory usage');
                memory.classList.add(csscls('memory'));
                memory.textContent = stmt.memory_str;
                li.append(memory);
            }
            if (typeof (stmt.row_count) !== 'undefined') {
                const rowCount = document.createElement('span');
                rowCount.setAttribute('title', 'Row count');
                rowCount.classList.add(csscls('row-count'));
                rowCount.textContent = stmt.row_count;
                li.append(rowCount);
            }
            if (typeof (stmt.stmt_id) !== 'undefined' && stmt.stmt_id) {
                const stmtId = document.createElement('span');
                stmtId.setAttribute('title', 'Prepared statement ID');
                stmtId.classList.add(csscls('stmt-id'));
                stmtId.textContent = stmt.stmt_id;
                li.append(stmtId);
            }
            if (stmt.connection) {
                const database = document.createElement('span');
                database.setAttribute('title', 'Connection');
                database.classList.add(csscls('database'));
                database.textContent = stmt.connection;
                li.append(database);
                li.setAttribute('connection', stmt.connection);

                if (!this.filters.includes(stmt.connection)) {
                    this.filters.push(stmt.connection);

                    const filterLink = document.createElement('a');
                    filterLink.classList.add(csscls('filter'));
                    filterLink.textContent = stmt.connection;
                    filterLink.setAttribute('rel', stmt.connection);
                    filterLink.addEventListener('click', () => {
                        this.onFilterClick(filterLink);
                    });
                    this.toolbar.append(filterLink);

                    if (this.filters.length > 1) {
                        this.toolbar.hidden = false;
                    }
                }
            }
            if (stmt.type === 'query') {
                const copyBtn = document.createElement('span');
                copyBtn.setAttribute('title', 'Copy to clipboard');
                copyBtn.classList.add(csscls('copy-clipboard'));
                copyBtn.style.cursor = 'pointer';
                copyBtn.innerHTML = '&#8203;';
                copyBtn.addEventListener('click', (event) => {
                    this.onCopyToClipboard(copyBtn);
                    event.stopPropagation();
                });
                li.append(copyBtn);
            }
            if (stmt.xdebug_link) {
                li.prepend(PhpDebugBar.Widgets.editorLink(stmt.xdebug_link));
            } else if (typeof (stmt.filename) !== 'undefined' && stmt.filename) {
                li.prepend(PhpDebugBar.Widgets.editorLink(stmt));
            }
            if (stmt.type !== 'query') {
                const strong = document.createElement('strong');
                strong.classList.add(csscls('sql'), csscls(stmt.type));
                strong.textContent = stmt.sql;
                li.append(strong);
            } else {
                const code = document.createElement('code');
                code.classList.add(csscls('sql'));
                code.innerHTML = PhpDebugBar.Widgets.highlight(stmt.sql, 'sql');
                li.append(code);
            }
            if (typeof (stmt.is_success) !== 'undefined' && !stmt.is_success) {
                li.classList.add(csscls('error'));
                const errorSpan = document.createElement('span');
                errorSpan.classList.add(csscls('error'));
                errorSpan.textContent = `[${stmt.error_code}] ${stmt.error_message}`;
                li.append(errorSpan);
            }

            if (stmt.type !== 'query') {
                return;
            }

            const table = document.createElement('table');
            table.classList.add(csscls('params'));
            table.hidden = true;

            if (stmt.params && Object.keys(stmt.params).length > 0) {
                this.renderList(table, 'Params', stmt.params);
            }
            if (stmt.backtrace && Object.keys(stmt.backtrace).length > 0) {
                const values = [];
                for (const trace of stmt.backtrace.values()) {
                    let text = trace.name || trace.file;
                    if (trace.line) {
                        text = `${text}:${trace.line}`;
                    }
                    values.push(text);
                }
                this.renderList(table, 'Backtrace', values);
            }
            if (!table.querySelectorAll('tr').length) {
                table.style.display = 'none';
            }
            li.append(table);
            li.style.cursor = 'pointer';
            li.addEventListener('click', (event) => {
                if (window.getSelection().type === 'Range' || event.target.closest('.sf-dump')) {
                    return '';
                }
                table.hidden = !table.hidden;
                const code = li.querySelector('code');
                if (code && typeof phpdebugbar_sqlformatter !== 'undefined') {
                    let sql = stmt.sql;
                    if (!table.hidden) {
                        sql = phpdebugbar_sqlformatter.format(stmt.sql);
                    }
                    code.innerHTML = PhpDebugBar.Widgets.highlight(sql, 'sql');
                }
            });
        }

        render() {
            this.status = document.createElement('div');
            this.status.classList.add(csscls('status'));
            this.el.append(this.status);

            this.toolbar = document.createElement('div');
            this.toolbar.classList.add(csscls('toolbar'));
            this.el.append(this.toolbar);

            this.filters = [];
            let sortState = 'none'; // 'none', 'asc', 'desc'
            let originalData = null;

            this.list = new PhpDebugBar.Widgets.ListWidget({
                itemRenderer: (li, stmt) => this.itemRenderer(li, stmt)
            });
            this.el.append(this.list.el);

            this.bindAttr('data', function (data) {
                // the PDO collector maybe is empty
                if (data.length <= 0 || !data.statements) {
                    return false;
                }
                this.filters = [];
                this.toolbar.hidden = true;
                const toolbarFilters = this.toolbar.querySelectorAll(`.${csscls('filter')}`);
                for (const filter of toolbarFilters) {
                    filter.remove();
                }
                this.list.set('data', data.statements);
                this.status.innerHTML = '';

                // Search for duplicate statements.
                const sql = {};
                let duplicate = 0;
                for (let i = 0; i < data.statements.length; i++) {
                    if (data.statements[i].type && data.statements[i].type !== 'query') {
                        continue;
                    }
                    let stmt = data.statements[i].sql;
                    if (data.statements[i].params && Object.keys(data.statements[i].params).length > 0) {
                        stmt += JSON.stringify(data.statements[i].params);
                    }
                    if (data.statements[i].connection) {
                        stmt += `@${data.statements[i].connection}`;
                    }
                    sql[stmt] = sql[stmt] || { keys: [] };
                    sql[stmt].keys.push(i);
                }
                // Add classes to all duplicate SQL statements.
                for (const stmt in sql) {
                    if (sql[stmt].keys.length > 1) {
                        duplicate += sql[stmt].keys.length;
                        for (let i = 0; i < sql[stmt].keys.length; i++) {
                            const listItems = this.list.el.querySelectorAll(`.${csscls('list-item')}`);
                            if (listItems[sql[stmt].keys[i]]) {
                                listItems[sql[stmt].keys[i]].classList.add(csscls('sql-duplicate'));
                            }
                        }
                    }
                }

                const t = document.createElement('span');
                t.textContent = `${data.nb_statements} statements were executed`;
                if (data.nb_excluded_statements) {
                    t.textContent += `, ${data.nb_excluded_statements} have been excluded`;
                }
                this.status.append(t);

                if (data.nb_failed_statements) {
                    t.append(`, ${data.nb_failed_statements} of which failed`);
                }
                if (duplicate) {
                    t.append(`, ${duplicate} of which were duplicates`);
                    t.append(`, ${data.nb_statements - duplicate} unique. `);

                    // add toggler for displaying only duplicated queries
                    const duplicatedText = 'Show only duplicated';
                    const toggleLink = document.createElement('a');
                    toggleLink.classList.add(csscls('duplicates'));
                    toggleLink.textContent = duplicatedText;
                    toggleLink.addEventListener('click', () => {
                        toggleLink.classList.toggle('shown-duplicated');
                        toggleLink.textContent = toggleLink.classList.contains('shown-duplicated') ? 'Show All' : duplicatedText;

                        const selector = `.${this.className} .${csscls('list-item')}:not(.${csscls('sql-duplicate')})`;
                        const items = document.querySelectorAll(selector);
                        for (const item of items) {
                            item.hidden = !item.hidden;
                        }
                    });
                    t.append(toggleLink);
                }
                if (data.accumulated_duration_str) {
                    const duration = document.createElement('span');
                    duration.setAttribute('title', 'Accumulated duration');
                    duration.classList.add(csscls('duration'));
                    duration.textContent = data.accumulated_duration_str;

                    const sortIcon = document.createElement('span');
                    sortIcon.classList.add(csscls('sort-icon'));
                    sortIcon.style.cursor = 'pointer';
                    sortIcon.style.marginLeft = '5px';
                    sortIcon.textContent = 'Sort ⇅';
                    sortIcon.setAttribute('title', 'Sort by duration');

                    sortIcon.addEventListener('click', () => {
                        if (sortState === 'none') {
                            sortState = 'desc';
                            sortIcon.textContent = '↓';
                            originalData = [...data.statements];
                            data.statements.sort((a, b) => (b.duration || 0) - (a.duration || 0));
                        } else if (sortState === 'desc') {
                            sortState = 'asc';
                            sortIcon.textContent = '↑';
                            data.statements.sort((a, b) => (a.duration || 0) - (b.duration || 0));
                        } else {
                            sortState = 'none';
                            sortIcon.textContent = '⇅';
                            if (originalData) {
                                data.statements = originalData;
                                originalData = null;
                            }
                        }
                        this.list.set('data', data.statements);
                    });

                    duration.append(sortIcon);
                    this.status.append(duration);
                }
                if (data.memory_usage_str) {
                    const memory = document.createElement('span');
                    memory.setAttribute('title', 'Memory usage');
                    memory.classList.add(csscls('memory'));
                    memory.textContent = data.memory_usage_str;
                    this.status.append(memory);
                }
            });
        }
    }
    PhpDebugBar.Widgets.SQLQueriesWidget = SQLQueriesWidget;
})();

(function () {
    const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for displaying sql queries with Laravel-specific features.
     * Extends the base SQLQueriesWidget to add EXPLAIN functionality.
     *
     * Options:
     *  - data
     */
    class LaravelQueriesWidget extends PhpDebugBar.Widgets.SQLQueriesWidget {

        buildTable(rows, opts = {}) {
            const headings = [];
            for (const key in rows[0]) {
                const th = document.createElement('th');
                th.textContent = key;
                headings.push(th);
            }

            const values = [];
            for (const row of rows) {
                const tr = document.createElement('tr');
                for (const key in row) {
                    const td = document.createElement('td');
                    const text = row[key] == null ? '' : String(row[key]);
                    td.textContent = text;
                    if (!opts.expanded) {
                        td.title = text;
                        td.addEventListener('click', (e) => {
                            e.stopPropagation();
                            td.classList.toggle(csscls('cell-expanded'));
                            if (td.classList.contains(csscls('cell-expanded'))) {
                                const selection = window.getSelection();
                                const range = document.createRange();
                                range.selectNodeContents(td);
                                selection.removeAllRanges();
                                selection.addRange(range);
                            }
                        });
                    }
                    tr.append(td);
                }
                values.push(tr);
            }

            const table = document.createElement('table');
            table.classList.add(csscls('explain'));
            if (opts.expanded) {
                table.classList.add(csscls('explain-full'));
            }
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            const headerRow = document.createElement('tr');
            headerRow.append(...headings);
            thead.append(headerRow);
            tbody.append(...values);
            table.append(thead, tbody);
            return table;
        }

        buildPgsqlTable(rows, opts = {}) {
            const values = [];
            for (const row of rows) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.textContent = row;
                tr.append(td);
                values.push(tr);
            }

            const table = document.createElement('table');
            table.classList.add(csscls('explain'));
            if (opts.expanded) {
                table.classList.add(csscls('explain-full'));
            }
            const tbody = document.createElement('tbody');
            tbody.append(...values);
            table.append(tbody);
            return table;
        }

        actionButton(label, onClick) {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.classList.add(csscls('explain-btn'));
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                onClick(e);
            });
            return btn;
        }

        fetchQuery(statement, mode, format) {
            const body = {
                connection: statement.explain.connection,
                query: statement.explain.query,
                bindings: statement.params,
                hash: statement.explain.hash,
                mode: mode,
            };
            if (format) {
                body.format = format;
            }
            return fetch(statement.explain.url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body),
            }).then((response) =>
                response.json().then((json) => {
                    if (!response.ok)
                        throw new Error(json.message || 'Request failed');
                    return json;
                })
            );
        }

        renderResult(container, statement, data, btnBar) {
            container.innerHTML = '';

            const result = data.result;
            if (Array.isArray(result) && result.length > 0 && typeof result[0] === 'object') {
                const wrapper = document.createElement('div');
                wrapper.classList.add(csscls('explain-scroll'));
                wrapper.append(this.buildTable(result));
                container.append(wrapper);
                btnBar.append(this.actionButton('Expand', () => {
                    this.showPopup(statement.explain.query, this.buildTable(result, { expanded: true }));
                }));
            } else {
                const empty = document.createElement('em');
                empty.textContent = 'No results';
                container.append(empty);
            }

            container.prepend(btnBar);
        }

        renderDump(container, statement, data, btnBar) {
            container.innerHTML = '';
            PhpDebugBar.Widgets.renderValueInto(container, data.result);
            PhpDebugBar.utils.sfDump(container);
            container.prepend(btnBar);
        }

        renderExplain(container, statement, data, driver, btnBar) {
            container.innerHTML = '';

            const rows = data;
            const buildFn = driver === 'pgsql' ? 'buildPgsqlTable' : 'buildTable';

            const wrapper = document.createElement('div');
            wrapper.classList.add(csscls('explain-scroll'));
            wrapper.append(this[buildFn](rows));
            container.append(wrapper);

            btnBar.append(this.actionButton('Expand', () => {
                this.showPopup(statement.explain.query, this[buildFn](rows, { expanded: true }));
            }));

            container.prepend(btnBar);
        }

        showPopup(query, contentEl) {
            const overlay = document.createElement('div');
            overlay.classList.add(csscls('explain-overlay'));

            const popup = document.createElement('div');
            popup.classList.add(csscls('explain-popup'));

            const header = document.createElement('div');
            header.classList.add(csscls('explain-popup-header'));

            const title = document.createElement('span');
            title.innerHTML = PhpDebugBar.Widgets.highlight(query.length > 120 ? query.substring(0, 120) + '...' : query, 'sql');
            title.classList.add(csscls('explain-popup-title'));

            const closeBtn = document.createElement('button');
            closeBtn.textContent = '\u2715';
            closeBtn.classList.add(csscls('explain-popup-close'));
            closeBtn.addEventListener('click', () => overlay.remove());

            header.append(title, closeBtn);

            const body = document.createElement('div');
            body.classList.add(csscls('explain-popup-body'));
            body.append(contentEl);

            popup.append(header, body);
            overlay.append(popup);

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.remove();
            });

            document.addEventListener('keydown', function onEsc(e) {
                if (e.key === 'Escape') {
                    overlay.remove();
                    document.removeEventListener('keydown', onEsc);
                }
            });

            document.querySelector('div.phpdebugbar').append(overlay);
        }

        itemRenderer(li, stmt, filters) {
            // Call parent's item renderer first
            super.itemRenderer(li, stmt, filters);

            // Add explain button if available
            if (stmt.explain) {
                let table = li.querySelector(`.${csscls('params')}`);
                table.style.display = '';
                if (stmt.explain.modes.includes('result')) {
                    this.renderDetailSection(table, 'Result', stmt, 'result');
                }
                if (stmt.explain.modes.includes('explain')) {
                    this.renderDetailSection(table, 'Performance', stmt, 'explain');
                }
            }
        }

        renderDetailSection(table, caption, statement, mode) {
            const thead = document.createElement('thead');
            const tr = document.createElement('tr');
            const th = document.createElement('th');
            th.colSpan = 2;
            th.classList.add(csscls('name'));
            th.textContent = caption;
            tr.append(th);
            thead.append(tr);
            table.append(thead);

            const tbody = document.createElement('tbody');
            const bodyTr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 2;

            const driver = statement.explain.driver;

            if (mode === 'result') {
                const makeBtnBar = () => {
                    const bar = document.createElement('div');
                    bar.classList.add(csscls('explain-btnbar'));
                    bar.append(
                        this.actionButton('Run SELECT', () => {
                            const btnBar = makeBtnBar();
                            this.fetchQuery(statement, 'result').then((json) => {
                                this.renderResult(td, statement, json.data, btnBar);
                            }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
                        }),
                        this.actionButton('Run SELECT (dump)', () => {
                            const btnBar = makeBtnBar();
                            this.fetchQuery(statement, 'result', 'dump').then((json) => {
                                this.renderDump(td, statement, json.data, btnBar);
                            }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
                        })
                    );
                    return bar;
                };
                td.append(makeBtnBar());
            } else {
                const run = () => {
                    this.fetchQuery(statement, 'explain').then((json) => {
                        const btnBar = document.createElement('div');
                        btnBar.classList.add(csscls('explain-btnbar'));
                        btnBar.append(this.actionButton('Re-run EXPLAIN', run));

                        if (json.visual) {
                            btnBar.append(this.buildVisualExplainButton(statement, json.visual.confirm));
                        }

                        this.renderExplain(td, statement, json.data, driver, btnBar);
                    }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
                };

                td.append(this.actionButton('Run EXPLAIN', run));
            }

            bodyTr.append(td);
            tbody.append(bodyTr);
            table.append(tbody);
        }

        buildVisualExplainButton(statement, confirmMessage) {
            const linkContainer = document.createElement('span');
            linkContainer.classList.add(csscls('visual-link'));

            const btn = this.actionButton('Visual Explain', () => {
                if (!confirm(confirmMessage)) // eslint-disable-line no-alert
                    return;
                this.fetchQuery(statement, 'visual').then((json) => {
                    linkContainer.innerHTML = '';
                    const link = document.createElement('a');
                    link.href = json.data;
                    link.textContent = json.data;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    linkContainer.append(link);
                    window.open(json.data, '_blank', 'noopener');
                }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
            });

            const wrapper = document.createDocumentFragment();
            wrapper.append(btn, linkContainer);
            return wrapper;
        }
    }

    PhpDebugBar.Widgets.LaravelQueriesWidget = LaravelQueriesWidget;
})();

(function () {
    const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for the displaying cache events
     *
     * Options:
     *  - data
     */
    class LaravelCacheWidget extends PhpDebugBar.Widgets.TimelineWidget {
        get tagName() {
            return 'ul';
        }

        get className() {
            return csscls('timeline cache');
        }

        onForgetClick(e, el) {
            e.stopPropagation();

            fetch(el.getAttribute('data-url'), {
                method: 'DELETE'
            }).then((response) => {
                if (response.ok) {
                    el.style.transition = 'opacity 200ms';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 200);
                }
            }).catch((err) => {
                console.error('Failed to forget cache key:', err);
            });
        }

        render() {
            super.render();

            this.bindAttr('data', function (data) {
                if (data.measures) {
                    const lines = this.el.querySelectorAll(`.${csscls('measure')}`);

                    for (let i = 0; i < data.measures.length; i++) {
                        const measure = data.measures[i];
                        const m = lines[i];

                        if (measure.params && Object.keys(measure.params).length > 0) {
                            if (measure.params.delete) {
                                const nextElement = m.nextElementSibling;
                                if (nextElement) {
                                    const deleteRow = Array.from(nextElement.querySelectorAll('td.phpdebugbar-widgets-name'))
                                        .find(td => td.textContent.includes('delete'));
                                    if (deleteRow) {
                                        deleteRow.closest('tr')?.remove();
                                    }
                                }
                            }
                            if (measure.delete_url && measure.params.key) {
                                const forgetLink = document.createElement('a');
                                forgetLink.className = csscls('forget');
                                forgetLink.textContent = 'forget';
                                forgetLink.setAttribute('data-url', measure.delete_url);
                                forgetLink.addEventListener('click', (e) => {
                                    this.onForgetClick(e, forgetLink);
                                }, { once: true });
                                m.appendChild(forgetLink);
                            }
                        }
                    }
                }
            });
        }
    }

    PhpDebugBar.Widgets.LaravelCacheWidget = LaravelCacheWidget;
})();

