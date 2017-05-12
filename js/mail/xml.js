// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod){if(typeof exports=="object"&&typeof module=="object")
mod(require("../../lib/codemirror"));else if(typeof define=="function"&&define.amd)
define(["../../lib/codemirror"],mod);else mod(CodeMirror)})(function(CodeMirror){"use strict";var htmlConfig={autoSelfClosers:{'area':!0,'base':!0,'br':!0,'col':!0,'command':!0,'embed':!0,'frame':!0,'hr':!0,'img':!0,'input':!0,'keygen':!0,'link':!0,'meta':!0,'param':!0,'source':!0,'track':!0,'wbr':!0,'menuitem':!0},implicitlyClosed:{'dd':!0,'li':!0,'optgroup':!0,'option':!0,'p':!0,'rp':!0,'rt':!0,'tbody':!0,'td':!0,'tfoot':!0,'th':!0,'tr':!0},contextGrabbers:{'dd':{'dd':!0,'dt':!0},'dt':{'dd':!0,'dt':!0},'li':{'li':!0},'option':{'option':!0,'optgroup':!0},'optgroup':{'optgroup':!0},'p':{'address':!0,'article':!0,'aside':!0,'blockquote':!0,'dir':!0,'div':!0,'dl':!0,'fieldset':!0,'footer':!0,'form':!0,'h1':!0,'h2':!0,'h3':!0,'h4':!0,'h5':!0,'h6':!0,'header':!0,'hgroup':!0,'hr':!0,'menu':!0,'nav':!0,'ol':!0,'p':!0,'pre':!0,'section':!0,'table':!0,'ul':!0},'rp':{'rp':!0,'rt':!0},'rt':{'rp':!0,'rt':!0},'tbody':{'tbody':!0,'tfoot':!0},'td':{'td':!0,'th':!0},'tfoot':{'tbody':!0},'th':{'td':!0,'th':!0},'thead':{'tbody':!0,'tfoot':!0},'tr':{'tr':!0}},doNotIndent:{"pre":!0},allowUnquoted:!0,allowMissing:!0,caseFold:!0}
var xmlConfig={autoSelfClosers:{},implicitlyClosed:{},contextGrabbers:{},doNotIndent:{},allowUnquoted:!1,allowMissing:!1,caseFold:!1}
CodeMirror.defineMode("xml",function(editorConf,config_){var indentUnit=editorConf.indentUnit
var config={}
var defaults=config_.htmlMode?htmlConfig:xmlConfig
for(var prop in defaults)config[prop]=defaults[prop]
for(var prop in config_)config[prop]=config_[prop]
var type,setStyle;function inText(stream,state){function chain(parser){state.tokenize=parser;return parser(stream,state)}
var ch=stream.next();if(ch=="<"){if(stream.eat("!")){if(stream.eat("[")){if(stream.match("CDATA["))return chain(inBlock("atom","]]>"));else return null}else if(stream.match("--")){return chain(inBlock("comment","-->"))}else if(stream.match("DOCTYPE",!0,!0)){stream.eatWhile(/[\w\._\-]/);return chain(doctype(1))}else{return null}}else if(stream.eat("?")){stream.eatWhile(/[\w\._\-]/);state.tokenize=inBlock("meta","?>");return "meta"}else{type=stream.eat("/")?"closeTag":"openTag";state.tokenize=inTag;return "tag bracket"}}else if(ch=="&"){var ok;if(stream.eat("#")){if(stream.eat("x")){ok=stream.eatWhile(/[a-fA-F\d]/)&&stream.eat(";")}else{ok=stream.eatWhile(/[\d]/)&&stream.eat(";")}}else{ok=stream.eatWhile(/[\w\.\-:]/)&&stream.eat(";")}
return ok?"atom":"error"}else{stream.eatWhile(/[^&<]/);return null}}
inText.isInText=!0;function inTag(stream,state){var ch=stream.next();if(ch==">"||(ch=="/"&&stream.eat(">"))){state.tokenize=inText;type=ch==">"?"endTag":"selfcloseTag";return "tag bracket"}else if(ch=="="){type="equals";return null}else if(ch=="<"){state.tokenize=inText;state.state=baseState;state.tagName=state.tagStart=null;var next=state.tokenize(stream,state);return next?next+" tag error":"tag error"}else if(/[\'\"]/.test(ch)){state.tokenize=inAttribute(ch);state.stringStartCol=stream.column();return state.tokenize(stream,state)}else{stream.match(/^[^\s\u00a0=<>\"\']*[^\s\u00a0=<>\"\'\/]/);return "word"}}
function inAttribute(quote){var closure=function(stream,state){while(!stream.eol()){if(stream.next()==quote){state.tokenize=inTag;break}}
return "string"};closure.isInAttribute=!0;return closure}
function inBlock(style,terminator){return function(stream,state){while(!stream.eol()){if(stream.match(terminator)){state.tokenize=inText;break}
stream.next()}
return style}}
function doctype(depth){return function(stream,state){var ch;while((ch=stream.next())!=null){if(ch=="<"){state.tokenize=doctype(depth+1);return state.tokenize(stream,state)}else if(ch==">"){if(depth==1){state.tokenize=inText;break}else{state.tokenize=doctype(depth-1);return state.tokenize(stream,state)}}}
return "meta"}}
function Context(state,tagName,startOfLine){this.prev=state.context;this.tagName=tagName;this.indent=state.indented;this.startOfLine=startOfLine;if(config.doNotIndent.hasOwnProperty(tagName)||(state.context&&state.context.noIndent))
this.noIndent=!0}
function popContext(state){if(state.context)state.context=state.context.prev}
function maybePopContext(state,nextTagName){var parentTagName;while(!0){if(!state.context){return}
parentTagName=state.context.tagName;if(!config.contextGrabbers.hasOwnProperty(parentTagName)||!config.contextGrabbers[parentTagName].hasOwnProperty(nextTagName)){return}
popContext(state)}}
function baseState(type,stream,state){if(type=="openTag"){state.tagStart=stream.column();return tagNameState}else if(type=="closeTag"){return closeTagNameState}else{return baseState}}
function tagNameState(type,stream,state){if(type=="word"){state.tagName=stream.current();setStyle="tag";return attrState}else{setStyle="error";return tagNameState}}
function closeTagNameState(type,stream,state){if(type=="word"){var tagName=stream.current();if(state.context&&state.context.tagName!=tagName&&config.implicitlyClosed.hasOwnProperty(state.context.tagName))
popContext(state);if((state.context&&state.context.tagName==tagName)||config.matchClosing===!1){setStyle="tag";return closeState}else{setStyle="tag error";return closeStateErr}}else{setStyle="error";return closeStateErr}}
function closeState(type,_stream,state){if(type!="endTag"){setStyle="error";return closeState}
popContext(state);return baseState}
function closeStateErr(type,stream,state){setStyle="error";return closeState(type,stream,state)}
function attrState(type,_stream,state){if(type=="word"){setStyle="attribute";return attrEqState}else if(type=="endTag"||type=="selfcloseTag"){var tagName=state.tagName,tagStart=state.tagStart;state.tagName=state.tagStart=null;if(type=="selfcloseTag"||config.autoSelfClosers.hasOwnProperty(tagName)){maybePopContext(state,tagName)}else{maybePopContext(state,tagName);state.context=new Context(state,tagName,tagStart==state.indented)}
return baseState}
setStyle="error";return attrState}
function attrEqState(type,stream,state){if(type=="equals")return attrValueState;if(!config.allowMissing)setStyle="error";return attrState(type,stream,state)}
function attrValueState(type,stream,state){if(type=="string")return attrContinuedState;if(type=="word"&&config.allowUnquoted){setStyle="string";return attrState}
setStyle="error";return attrState(type,stream,state)}
function attrContinuedState(type,stream,state){if(type=="string")return attrContinuedState;return attrState(type,stream,state)}
return{startState:function(baseIndent){var state={tokenize:inText,state:baseState,indented:baseIndent||0,tagName:null,tagStart:null,context:null}
if(baseIndent!=null)state.baseIndent=baseIndent
return state},token:function(stream,state){if(!state.tagName&&stream.sol())
state.indented=stream.indentation();if(stream.eatSpace())return null;type=null;var style=state.tokenize(stream,state);if((style||type)&&style!="comment"){setStyle=null;state.state=state.state(type||style,stream,state);if(setStyle)
style=setStyle=="error"?style+" error":setStyle}
return style},indent:function(state,textAfter,fullLine){var context=state.context;if(state.tokenize.isInAttribute){if(state.tagStart==state.indented)
return state.stringStartCol+1;else return state.indented+indentUnit}
if(context&&context.noIndent)return CodeMirror.Pass;if(state.tokenize!=inTag&&state.tokenize!=inText)
return fullLine?fullLine.match(/^(\s*)/)[0].length:0;if(state.tagName){if(config.multilineTagIndentPastTag!==!1)
return state.tagStart+state.tagName.length+2;else return state.tagStart+indentUnit*(config.multilineTagIndentFactor||1)}
if(config.alignCDATA&&/<!\[CDATA\[/.test(textAfter))return 0;var tagAfter=textAfter&&/^<(\/)?([\w_:\.-]*)/.exec(textAfter);if(tagAfter&&tagAfter[1]){while(context){if(context.tagName==tagAfter[2]){context=context.prev;break}else if(config.implicitlyClosed.hasOwnProperty(context.tagName)){context=context.prev}else{break}}}else if(tagAfter){while(context){var grabbers=config.contextGrabbers[context.tagName];if(grabbers&&grabbers.hasOwnProperty(tagAfter[2]))
context=context.prev;else break}}
while(context&&context.prev&&!context.startOfLine)
context=context.prev;if(context)return context.indent+indentUnit;else return state.baseIndent||0},electricInput:/<\/[\s\w:]+>$/,blockCommentStart:"<!--",blockCommentEnd:"-->",configuration:config.htmlMode?"html":"xml",helperType:config.htmlMode?"html":"xml",skipAttribute:function(state){if(state.state==attrValueState)
state.state=attrState}}});CodeMirror.defineMIME("text/xml","xml");CodeMirror.defineMIME("application/xml","xml");if(!CodeMirror.mimeModes.hasOwnProperty("text/html"))
CodeMirror.defineMIME("text/html",{name:"xml",htmlMode:!0})})