// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod){if(typeof exports=="object"&&typeof module=="object")
mod(require("../../lib/codemirror"),require("../xml/xml"),require("../meta"));else if(typeof define=="function"&&define.amd)
define(["../../lib/codemirror","../xml/xml","../meta"],mod);else mod(CodeMirror)})(function(CodeMirror){"use strict";CodeMirror.defineMode("markdown",function(cmCfg,modeCfg){var htmlMode=CodeMirror.getMode(cmCfg,"text/html");var htmlModeMissing=htmlMode.name=="null"
function getMode(name){if(CodeMirror.findModeByName){var found=CodeMirror.findModeByName(name);if(found)name=found.mime||found.mimes[0]}
var mode=CodeMirror.getMode(cmCfg,name);return mode.name=="null"?null:mode}
if(modeCfg.highlightFormatting===undefined)
modeCfg.highlightFormatting=!1;if(modeCfg.maxBlockquoteDepth===undefined)
modeCfg.maxBlockquoteDepth=0;if(modeCfg.underscoresBreakWords===undefined)
modeCfg.underscoresBreakWords=!0;if(modeCfg.taskLists===undefined)modeCfg.taskLists=!1;if(modeCfg.strikethrough===undefined)
modeCfg.strikethrough=!1;if(modeCfg.tokenTypeOverrides===undefined)
modeCfg.tokenTypeOverrides={};var tokenTypes={header:"header",code:"comment",quote:"quote",list1:"variable-2",list2:"variable-3",list3:"keyword",hr:"hr",image:"image",imageAltText:"image-alt-text",imageMarker:"image-marker",formatting:"formatting",linkInline:"link",linkEmail:"link",linkText:"link",linkHref:"string",em:"em",strong:"strong",strikethrough:"strikethrough"};for(var tokenType in tokenTypes){if(tokenTypes.hasOwnProperty(tokenType)&&modeCfg.tokenTypeOverrides[tokenType]){tokenTypes[tokenType]=modeCfg.tokenTypeOverrides[tokenType]}}
var hrRE=/^([*\-_])(?:\s*\1){2,}\s*$/,listRE=/^(?:[*\-+]|^[0-9]+([.)]))\s+/,taskListRE=/^\[(x|)\](?=\s)/,atxHeaderRE=modeCfg.allowAtxHeaderWithoutSpace?/^(#+)/ : /^(#+)(?: |$)/,setextHeaderRE=/^ *(?:\={1,}|-{1,})\s*$/,textRE=/^[^#!\[\]*_\\<>` "'(~]+/,fencedCodeRE=new RegExp("^("+(modeCfg.fencedCodeBlocks===!0?"~~~+|```+":modeCfg.fencedCodeBlocks)+")[ \\t]*([\\w+#\-]*)");function switchInline(stream,state,f){state.f=state.inline=f;return f(stream,state)}
function switchBlock(stream,state,f){state.f=state.block=f;return f(stream,state)}
function lineIsEmpty(line){return!line||!/\S/.test(line.string)}
function blankLine(state){state.linkTitle=!1;state.em=!1;state.strong=!1;state.strikethrough=!1;state.quote=0;state.indentedCode=!1;if(htmlModeMissing&&state.f==htmlBlock){state.f=inlineNormal;state.block=blockNormal}
state.trailingSpace=0;state.trailingSpaceNewLine=!1;state.prevLine=state.thisLine
state.thisLine=null
return null}
function blockNormal(stream,state){var sol=stream.sol();var prevLineIsList=state.list!==!1,prevLineIsIndentedCode=state.indentedCode;state.indentedCode=!1;if(prevLineIsList){if(state.indentationDiff>=0){if(state.indentationDiff<4){state.indentation-=state.indentationDiff}
state.list=null}else if(state.indentation>0){state.list=null}else{state.list=!1}}
var match=null;if(state.indentationDiff>=4){stream.skipToEnd();if(prevLineIsIndentedCode||lineIsEmpty(state.prevLine)){state.indentation-=4;state.indentedCode=!0;return tokenTypes.code}else{return null}}else if(stream.eatSpace()){return null}else if((match=stream.match(atxHeaderRE))&&match[1].length<=6){state.header=match[1].length;if(modeCfg.highlightFormatting)state.formatting="header";state.f=state.inline;return getType(state)}else if(!lineIsEmpty(state.prevLine)&&!state.quote&&!prevLineIsList&&!prevLineIsIndentedCode&&(match=stream.match(setextHeaderRE))){state.header=match[0].charAt(0)=='='?1:2;if(modeCfg.highlightFormatting)state.formatting="header";state.f=state.inline;return getType(state)}else if(stream.eat('>')){state.quote=sol?1:state.quote+1;if(modeCfg.highlightFormatting)state.formatting="quote";stream.eatSpace();return getType(state)}else if(stream.peek()==='['){return switchInline(stream,state,footnoteLink)}else if(stream.match(hrRE,!0)){state.hr=!0;return tokenTypes.hr}else if(match=stream.match(listRE)){var listType=match[1]?"ol":"ul";state.indentation=stream.column()+stream.current().length;state.list=!0;while(state.listStack&&stream.column()<state.listStack[state.listStack.length-1]){state.listStack.pop()}
state.listStack.push(state.indentation);if(modeCfg.taskLists&&stream.match(taskListRE,!1)){state.taskList=!0}
state.f=state.inline;if(modeCfg.highlightFormatting)state.formatting=["list","list-"+listType];return getType(state)}else if(modeCfg.fencedCodeBlocks&&(match=stream.match(fencedCodeRE,!0))){state.fencedChars=match[1]
state.localMode=getMode(match[2]);if(state.localMode)state.localState=CodeMirror.startState(state.localMode);state.f=state.block=local;if(modeCfg.highlightFormatting)state.formatting="code-block";state.code=-1
return getType(state)}
return switchInline(stream,state,state.inline)}
function htmlBlock(stream,state){var style=htmlMode.token(stream,state.htmlState);if(!htmlModeMissing){var inner=CodeMirror.innerMode(htmlMode,state.htmlState)
if((inner.mode.name=="xml"&&inner.state.tagStart===null&&(!inner.state.context&&inner.state.tokenize.isInText))||(state.md_inside&&stream.current().indexOf(">")>-1)){state.f=inlineNormal;state.block=blockNormal;state.htmlState=null}}
return style}
function local(stream,state){if(state.fencedChars&&stream.match(state.fencedChars,!1)){state.localMode=state.localState=null;state.f=state.block=leavingLocal;return null}else if(state.localMode){return state.localMode.token(stream,state.localState)}else{stream.skipToEnd();return tokenTypes.code}}
function leavingLocal(stream,state){stream.match(state.fencedChars);state.block=blockNormal;state.f=inlineNormal;state.fencedChars=null;if(modeCfg.highlightFormatting)state.formatting="code-block";state.code=1
var returnType=getType(state);state.code=0
return returnType}
function getType(state){var styles=[];if(state.formatting){styles.push(tokenTypes.formatting);if(typeof state.formatting==="string")state.formatting=[state.formatting];for(var i=0;i<state.formatting.length;i++){styles.push(tokenTypes.formatting+"-"+state.formatting[i]);if(state.formatting[i]==="header"){styles.push(tokenTypes.formatting+"-"+state.formatting[i]+"-"+state.header)}
if(state.formatting[i]==="quote"){if(!modeCfg.maxBlockquoteDepth||modeCfg.maxBlockquoteDepth>=state.quote){styles.push(tokenTypes.formatting+"-"+state.formatting[i]+"-"+state.quote)}else{styles.push("error")}}}}
if(state.taskOpen){styles.push("meta");return styles.length?styles.join(' '):null}
if(state.taskClosed){styles.push("property");return styles.length?styles.join(' '):null}
if(state.linkHref){styles.push(tokenTypes.linkHref,"url")}else{if(state.strong){styles.push(tokenTypes.strong)}
if(state.em){styles.push(tokenTypes.em)}
if(state.strikethrough){styles.push(tokenTypes.strikethrough)}
if(state.linkText){styles.push(tokenTypes.linkText)}
if(state.code){styles.push(tokenTypes.code)}
if(state.image){styles.push(tokenTypes.image)}
if(state.imageAltText){styles.push(tokenTypes.imageAltText,"link")}
if(state.imageMarker){styles.push(tokenTypes.imageMarker)}}
if(state.header){styles.push(tokenTypes.header,tokenTypes.header+"-"+state.header)}
if(state.quote){styles.push(tokenTypes.quote);if(!modeCfg.maxBlockquoteDepth||modeCfg.maxBlockquoteDepth>=state.quote){styles.push(tokenTypes.quote+"-"+state.quote)}else{styles.push(tokenTypes.quote+"-"+modeCfg.maxBlockquoteDepth)}}
if(state.list!==!1){var listMod=(state.listStack.length-1)%3;if(!listMod){styles.push(tokenTypes.list1)}else if(listMod===1){styles.push(tokenTypes.list2)}else{styles.push(tokenTypes.list3)}}
if(state.trailingSpaceNewLine){styles.push("trailing-space-new-line")}else if(state.trailingSpace){styles.push("trailing-space-"+(state.trailingSpace%2?"a":"b"))}
return styles.length?styles.join(' '):null}
function handleText(stream,state){if(stream.match(textRE,!0)){return getType(state)}
return undefined}
function inlineNormal(stream,state){var style=state.text(stream,state);if(typeof style!=='undefined')
return style;if(state.list){state.list=null;return getType(state)}
if(state.taskList){var taskOpen=stream.match(taskListRE,!0)[1]!=="x";if(taskOpen)state.taskOpen=!0;else state.taskClosed=!0;if(modeCfg.highlightFormatting)state.formatting="task";state.taskList=!1;return getType(state)}
state.taskOpen=!1;state.taskClosed=!1;if(state.header&&stream.match(/^#+$/,!0)){if(modeCfg.highlightFormatting)state.formatting="header";return getType(state)}
var sol=stream.sol();var ch=stream.next();if(state.linkTitle){state.linkTitle=!1;var matchCh=ch;if(ch==='('){matchCh=')'}
matchCh=(matchCh+'').replace(/([.?*+^$[\]\\(){}|-])/g,"\\$1");var regex='^\\s*(?:[^'+matchCh+'\\\\]+|\\\\\\\\|\\\\.)'+matchCh;if(stream.match(new RegExp(regex),!0)){return tokenTypes.linkHref}}
if(ch==='`'){var previousFormatting=state.formatting;if(modeCfg.highlightFormatting)state.formatting="code";stream.eatWhile('`');var count=stream.current().length
if(state.code==0){state.code=count
return getType(state)}else if(count==state.code){var t=getType(state)
state.code=0
return t}else{state.formatting=previousFormatting
return getType(state)}}else if(state.code){return getType(state)}
if(ch==='\\'){stream.next();if(modeCfg.highlightFormatting){var type=getType(state);var formattingEscape=tokenTypes.formatting+"-escape";return type?type+" "+formattingEscape:formattingEscape}}
if(ch==='!'&&stream.match(/\[[^\]]*\] ?(?:\(|\[)/,!1)){state.imageMarker=!0;state.image=!0;if(modeCfg.highlightFormatting)state.formatting="image";return getType(state)}
if(ch==='['&&state.imageMarker&&stream.match(/[^\]]*\](\(.*?\)| ?\[.*?\])/,!1)){state.imageMarker=!1;state.imageAltText=!0
if(modeCfg.highlightFormatting)state.formatting="image";return getType(state)}
if(ch===']'&&state.imageAltText){if(modeCfg.highlightFormatting)state.formatting="image";var type=getType(state);state.imageAltText=!1;state.image=!1;state.inline=state.f=linkHref;return type}
if(ch==='['&&stream.match(/[^\]]*\](\(.*\)| ?\[.*?\])/,!1)&&!state.image){state.linkText=!0;if(modeCfg.highlightFormatting)state.formatting="link";return getType(state)}
if(ch===']'&&state.linkText&&stream.match(/\(.*?\)| ?\[.*?\]/,!1)){if(modeCfg.highlightFormatting)state.formatting="link";var type=getType(state);state.linkText=!1;state.inline=state.f=linkHref;return type}
if(ch==='<'&&stream.match(/^(https?|ftps?):\/\/(?:[^\\>]|\\.)+>/,!1)){state.f=state.inline=linkInline;if(modeCfg.highlightFormatting)state.formatting="link";var type=getType(state);if(type){type+=" "}else{type=""}
return type+tokenTypes.linkInline}
if(ch==='<'&&stream.match(/^[^> \\]+@(?:[^\\>]|\\.)+>/,!1)){state.f=state.inline=linkInline;if(modeCfg.highlightFormatting)state.formatting="link";var type=getType(state);if(type){type+=" "}else{type=""}
return type+tokenTypes.linkEmail}
if(ch==='<'&&stream.match(/^(!--|\w)/,!1)){var end=stream.string.indexOf(">",stream.pos);if(end!=-1){var atts=stream.string.substring(stream.start,end);if(/markdown\s*=\s*('|"){0,1}1('|"){0,1}/.test(atts))state.md_inside=!0}
stream.backUp(1);state.htmlState=CodeMirror.startState(htmlMode);return switchBlock(stream,state,htmlBlock)}
if(ch==='<'&&stream.match(/^\/\w*?>/)){state.md_inside=!1;return "tag"}
var ignoreUnderscore=!1;if(!modeCfg.underscoresBreakWords){if(ch==='_'&&stream.peek()!=='_'&&stream.match(/(\w)/,!1)){var prevPos=stream.pos-2;if(prevPos>=0){var prevCh=stream.string.charAt(prevPos);if(prevCh!=='_'&&prevCh.match(/(\w)/,!1)){ignoreUnderscore=!0}}}}
if(ch==='*'||(ch==='_'&&!ignoreUnderscore)){if(sol&&stream.peek()===' '){}else if(state.strong===ch&&stream.eat(ch)){if(modeCfg.highlightFormatting)state.formatting="strong";var t=getType(state);state.strong=!1;return t}else if(!state.strong&&stream.eat(ch)){state.strong=ch;if(modeCfg.highlightFormatting)state.formatting="strong";return getType(state)}else if(state.em===ch){if(modeCfg.highlightFormatting)state.formatting="em";var t=getType(state);state.em=!1;return t}else if(!state.em){state.em=ch;if(modeCfg.highlightFormatting)state.formatting="em";return getType(state)}}else if(ch===' '){if(stream.eat('*')||stream.eat('_')){if(stream.peek()===' '){return getType(state)}else{stream.backUp(1)}}}
if(modeCfg.strikethrough){if(ch==='~'&&stream.eatWhile(ch)){if(state.strikethrough){if(modeCfg.highlightFormatting)state.formatting="strikethrough";var t=getType(state);state.strikethrough=!1;return t}else if(stream.match(/^[^\s]/,!1)){state.strikethrough=!0;if(modeCfg.highlightFormatting)state.formatting="strikethrough";return getType(state)}}else if(ch===' '){if(stream.match(/^~~/,!0)){if(stream.peek()===' '){return getType(state)}else{stream.backUp(2)}}}}
if(ch===' '){if(stream.match(/ +$/,!1)){state.trailingSpace++}else if(state.trailingSpace){state.trailingSpaceNewLine=!0}}
return getType(state)}
function linkInline(stream,state){var ch=stream.next();if(ch===">"){state.f=state.inline=inlineNormal;if(modeCfg.highlightFormatting)state.formatting="link";var type=getType(state);if(type){type+=" "}else{type=""}
return type+tokenTypes.linkInline}
stream.match(/^[^>]+/,!0);return tokenTypes.linkInline}
function linkHref(stream,state){if(stream.eatSpace()){return null}
var ch=stream.next();if(ch==='('||ch==='['){state.f=state.inline=getLinkHrefInside(ch==="("?")":"]",0);if(modeCfg.highlightFormatting)state.formatting="link-string";state.linkHref=!0;return getType(state)}
return 'error'}
var linkRE={")":/^(?:[^\\\(\)]|\\.|\((?:[^\\\(\)]|\\.)*\))*?(?=\))/,"]":/^(?:[^\\\[\]]|\\.|\[(?:[^\\\[\\]]|\\.)*\])*?(?=\])/}
function getLinkHrefInside(endChar){return function(stream,state){var ch=stream.next();if(ch===endChar){state.f=state.inline=inlineNormal;if(modeCfg.highlightFormatting)state.formatting="link-string";var returnState=getType(state);state.linkHref=!1;return returnState}
stream.match(linkRE[endChar])
state.linkHref=!0;return getType(state)}}
function footnoteLink(stream,state){if(stream.match(/^([^\]\\]|\\.)*\]:/,!1)){state.f=footnoteLinkInside;stream.next();if(modeCfg.highlightFormatting)state.formatting="link";state.linkText=!0;return getType(state)}
return switchInline(stream,state,inlineNormal)}
function footnoteLinkInside(stream,state){if(stream.match(/^\]:/,!0)){state.f=state.inline=footnoteUrl;if(modeCfg.highlightFormatting)state.formatting="link";var returnType=getType(state);state.linkText=!1;return returnType}
stream.match(/^([^\]\\]|\\.)+/,!0);return tokenTypes.linkText}
function footnoteUrl(stream,state){if(stream.eatSpace()){return null}
stream.match(/^[^\s]+/,!0);if(stream.peek()===undefined){state.linkTitle=!0}else{stream.match(/^(?:\s+(?:"(?:[^"\\]|\\\\|\\.)+"|'(?:[^'\\]|\\\\|\\.)+'|\((?:[^)\\]|\\\\|\\.)+\)))?/,!0)}
state.f=state.inline=inlineNormal;return tokenTypes.linkHref+" url"}
var mode={startState:function(){return{f:blockNormal,prevLine:null,thisLine:null,block:blockNormal,htmlState:null,indentation:0,inline:inlineNormal,text:handleText,formatting:!1,linkText:!1,linkHref:!1,linkTitle:!1,code:0,em:!1,strong:!1,header:0,hr:!1,taskList:!1,list:!1,listStack:[],quote:0,trailingSpace:0,trailingSpaceNewLine:!1,strikethrough:!1,fencedChars:null}},copyState:function(s){return{f:s.f,prevLine:s.prevLine,thisLine:s.thisLine,block:s.block,htmlState:s.htmlState&&CodeMirror.copyState(htmlMode,s.htmlState),indentation:s.indentation,localMode:s.localMode,localState:s.localMode?CodeMirror.copyState(s.localMode,s.localState):null,inline:s.inline,text:s.text,formatting:!1,linkTitle:s.linkTitle,code:s.code,em:s.em,strong:s.strong,strikethrough:s.strikethrough,header:s.header,hr:s.hr,taskList:s.taskList,list:s.list,listStack:s.listStack.slice(0),quote:s.quote,indentedCode:s.indentedCode,trailingSpace:s.trailingSpace,trailingSpaceNewLine:s.trailingSpaceNewLine,md_inside:s.md_inside,fencedChars:s.fencedChars}},token:function(stream,state){state.formatting=!1;if(stream!=state.thisLine){var forceBlankLine=state.header||state.hr;state.header=0;state.hr=!1;if(stream.match(/^\s*$/,!0)||forceBlankLine){blankLine(state);if(!forceBlankLine)return null
state.prevLine=null}
state.prevLine=state.thisLine
state.thisLine=stream
state.taskList=!1;state.trailingSpace=0;state.trailingSpaceNewLine=!1;state.f=state.block;var indentation=stream.match(/^\s*/,!0)[0].replace(/\t/g,'    ').length;state.indentationDiff=Math.min(indentation-state.indentation,4);state.indentation=state.indentation+state.indentationDiff;if(indentation>0)return null}
return state.f(stream,state)},innerMode:function(state){if(state.block==htmlBlock)return{state:state.htmlState,mode:htmlMode};if(state.localState)return{state:state.localState,mode:state.localMode};return{state:state,mode:mode}},blankLine:blankLine,getType:getType,closeBrackets:"()[]{}''\"\"``",fold:"markdown"};return mode},"xml");CodeMirror.defineMIME("text/x-markdown","markdown")})