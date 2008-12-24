<?php
header('Content-Type: text/css');
?>
/*
reset style:
http://meyerweb.com/eric/thoughts/2007/05/01/reset-reloaded/
*/
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td
/* also add hr */, hr {
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;
	font-weight: inherit;
	font-style: inherit;
	font-size: 100%;
	font-family: inherit;
	vertical-align: baseline;
}
/* remember to define focus styles! */
:focus {
	outline: 0;
}
body {
	line-height: 1;
	color: black;
	background: white;
}
/*
comment this out. we want to have normal style...
ol, ul {
	list-style: none;
}
*/
/* tables still need 'cellspacing="0"' in the markup */
table {
	border-collapse: separate;
	border-spacing: 0;
}
caption, th, td {
	text-align: left;
	font-weight: normal;
}
blockquote:before, blockquote:after,
q:before, q:after {
	content: "";
}
blockquote, q {
	quotes: "" "";
}

/* own page style */

body
{
	background: #F8FAFC;
	font-size: 14px;
	line-height: 1.25;
	font-family: serif;
}
body:target
{
	background-image: url(15pxgrid.png);
}
em
{
	font-style: italic;
}
strong
{
	font-weight: bold;
}
a
{
	color: #2F960A;
	text-decoration: none;
}
a[href]:hover
{
	text-decoration: underline;
}
ul, ol
{
	margin-top: 1.25em;
}
ul
{
	list-style-type: none;
	/*list-style-image: url(bullet.png);*/
}
ul li::before
{
	display: block;
	position: relative;
	margin-left: -1.15em;
	margin-bottom: -1em;
	width: 1.15em;
	height: 1em;
	text-align: left;
	content: "✮";
	color: #2F960A;
	text-shadow: 0 0 0.2em #84B7E7;
}
li
{
	line-height: 1.25;
}
blockquote
{
	margin-top: 1.25em;
	font-style: italic;
	quotes: "\201C" "\201D";
	/*text-indent: -1em;*/
}
blockquote p:first-child::before
{
	display: block;
	position: relative;
	margin-left: -1em;
	margin-bottom: -1em;
	width: 1em;
	height: 1em;
	text-align: right;
	content: open-quote;
}
blockquote p:last-child::after
{
	margin-left: -0.125em;
	content: close-quote;
}
pre
{
	font-family: monospace;
	font-size: 0.625em;
	line-height: 2em;
	margin-top: 2em;
	white-space: pre-wrap;
}
q
{
	quotes: "\201C" "\201D";
}
q::before
{
	content: open-quote;
}
q::after
{
	content: close-quote;
}
p
{
	margin-top: 1.25em;
}
hr + p
{
	margin-top: 0;
}
p + p
{
	margin-top: 0;
	text-indent: 1em;
}
p.highlight
{
	margin-top: 1.25em;
	text-indent: 0em;
	border-left: 0.25em solid #2F960A;
	background: #C3E599;
	margin-left: -0.35em;
	padding-left: 0.1em;
}
hr::before
{
	display: block;
	content: "↭";
	color: #84B7E7;
	text-shadow: 0 0 0.2em #2F960A;
}
hr
{
	display: block;
	height: 1.25em;
	width: 1em;
	margin: auto;
	/*background: url(separator.png) no-repeat;*/
}
h1
{
	font-size: 1.5em;
	line-height: 1.66667em;
	color: #2F960A;
	font-weight: bold;
}
h2
{
	font-size: 1.25em;
	line-height: 2em;
	color: #84B7E7;
	font-weight: bold;
}
h3
{
	font-size: 1.12500em;
	line-height: 2.22222em;
	color: #9AC7F1;
	font-weight: bold;
}
h1 + p, h2 + p, h3 + p,
h1 + ul, h2 + ul, h3 + ul,
h1 + ol, h2 + ol, h3 + ol
{
	margin-top: 0;
}
/*p, blockquote
{
	text-align: justify;
}*/

/* column styles etc... */

.area
{
	width: 48em;
	margin: auto;
}
.col2
{
	display: inline-block;
	vertical-align: top;
	width: 23.3em;  /* opera doesn't like 23.5em here ?!? */
	/* seems like opera inserts a space character between the two boxes :( */
	margin-right: 1em;
}
.col2 + .col2
{
	margin-right: 0;
}
.right
{
	text-align: right;
}

/* post style */

.post
{
	position: relative;
	width: 33em;
	padding-left: 7.5em;
	padding-right: 7.5em;
	margin: 1.25em auto;
}
.postheader
{
	background: #E1EEFB;
	margin: 0 -7.5em 0 -7.5em;
	/*-moz-border-radius-topleft: 0.3125em;
	-moz-border-radius-topright: 0.3125em;
	-moz-box-shadow: 0 0 0.625em #E1EEFB;*/
	padding: 0 0.3125em 0 0.3125em;
}
.postheader h1
{
	font-size: 1.25em;
	line-height: 2em;
	vertical-align: -10%;
	font-weight: bold;
	text-shadow: 0.0625em 0.0625em 0 #F8FAFC;
}
.postheader h1, .postheader h1 a
{
	color: #99C1EA;
}

.postinfo
{
	font-size: 0.6250em;
	line-height: 2em;
}

.userinfo, .dateinfo
{
	font-weight: bold;
}

.dateinfo::before, .tagsinfo::before, .commentinfo::before
{
	font-weight: normal;
	color: black;
	content: "→ ";
}

.dateinfo
{
	color: #2F960A;
}

/* Rating */

.rating
{
	font-size: 2.5em;
	position: absolute;
	top: 0;
	right: 0;
	width: 5.6em;
	height: 0.25em;
}
.rating a
{
	display: block;
	float: left;
	color: transparent;
	width: 1.1em;
	line-height: 0.25em;
	height: 0.25em;
}
.rating a::before
{
	font-weight: bold;
	content: "—";
	text-shadow: 0 0 0.15em, 0 0 0.4em;
}
.rating a:first-child::before /* first */
{
	color: hsl(0, 25%, 50%);
}
.rating a:first-child + a::before, .rating:hover a:first-child:hover + a::before
{
	color: hsl(30, 25%, 50%);
}
.rating a:first-child + a + a::before,
	.rating:hover a:first-child:hover + a + a::before,
	.rating:hover a:first-child + a:hover + a::before
{
	color: hsl(60, 25%, 50%);
}
.rating a:first-child + a + a + a::before,
	.rating:hover a:first-child:hover + a + a + a::before,
	.rating:hover a:first-child + a:hover + a + a::before,
	.rating:hover a:first-child + a + a:hover + a::before
{
	color: hsl(90, 25%, 50%);
}
.rating a::before, /* last */
	.rating:hover a:first-child:hover + a + a + a + a::before,
	.rating:hover a:first-child + a:hover + a + a + a::before,
	.rating:hover a:first-child + a + a:hover + a + a::before,
	.rating:hover a:first-child + a + a + a:hover + a::before
{
	color: hsl(120, 25%, 50%);
}

.rating:hover a:first-child::before, /* first */
	.score1 a:first-child::before,
	.score2 a:first-child::before,
	.score3 a:first-child::before,
	.score4 a:first-child::before,
	.score5 a:first-child::before
{
	color: hsl(0, 100%, 50%);
}
.rating:hover a:first-child + a::before,
	.score2 a:first-child + a::before,
	.score3 a:first-child + a::before,
	.score4 a:first-child + a::before,
	.score5 a:first-child + a::before
{
	color: hsl(30, 100%, 50%);
}
.rating:hover a:first-child + a + a::before,
	.score3 a:first-child + a + a::before,
	.score4 a:first-child + a + a::before,
	.score5 a:first-child + a + a::before
{
	color: hsl(60, 100%, 50%);
}
.rating:hover a:first-child + a + a + a::before,
	.score4 a:first-child + a + a + a::before,
	.score5 a:first-child + a + a + a::before
{
	color: hsl(90, 100%, 50%);
}
.rating:hover a::before, /* last */
	.score5 a::before
{
	color: hsl(120, 100%, 50%);
}


.ratingcaption
{
	display: none;
}
.rating:hover .ratingcaption
{
	position: absolute;
	bottom: 0.75em;
	height: 1.25em;
	width: 14em;
	display: block;
	background: #E1EEFB;
	text-align: center;
	font-size: 0.4em;
	line-height: 1.25em;
}

/* Footer */

#footer
{
	font-size: 0.75em;
	line-height: 1.66667em;
	width: 64em;
	margin: 3.25em auto 1.66667em auto;
	border-top: 0.08333em solid #AAAAAA;
	text-align: center;
	color: #AAAAAA;
}
#footer a
{
	color: #888888;
}

/* Header */

#header
{
	width: 48em;
	height: 2.5em;
	padding-top: 7.5em;
	margin: auto;
	background: url(header.png);
}

/* Header menu */

#header > a::before
{
	content: '';
	background: url(headertab.png) top left no-repeat;
	display: block;
	position: relative;
	width: 0.31250em;
	height: 1.25em;
	margin-bottom: -1.25em;
	margin-left: -0.31250em;
}
#header > a
{
	float: left;
	display: block;
	height: 1.25em;
	margin-left: 1em;
	padding-right: 0.31250em;
	background: url(headertab.png) top right no-repeat;
	font-weight: bold;
	color: #2F960A;
}
/*#header > li:hover, #header li.selected
{
	background: url(images/headtabs.png) 0 -23px no-repeat;
}
#header > li:hover > a, #header li.selected > a
{
	padding: 4px 5px 3px 2px;
	background: url(images/headtabs.png) 100% -23px no-repeat;
}*/

/* OpenID login */

.openid
{
	font-size: 1.12500em;
	line-height: 2.22222em;
}
.openid input
{
	font-size: 1.12500em;
}
.openid input[type="text"]
{
	width: 15em;
	margin-right: 0.5em;
	/*font-weight: bold;*/
	color: #2F960A;
	background: url(../../dwing/images/openid-icon.png) white no-repeat;
	padding-left: 28px;
}

/* new post styles */

/* Problem: content-box and native widget sizing... */
#title, #tags, #text
{
	border: 0;
	margin: 0;
	padding: 0;
	background: transparent;
	outline: 0.0625em solid #99C1EA;
}
#title
{
	font-size: 1em;
	width: 26.4em;
	color: inherit;
	font-family: inherit;
	font-weight: inherit;
}
#tags
{
	color: #2F960A;
	font-size: 1em;
	width: 48em;
}
#tags_preview
{
	color: #2F960A;
	font-size: 1em;
}
#text
{
	margin: 0;
	border: 0;
	padding: 0;
	line-height: 1.25em;
	margin-top: 1.25em;
	font-size: 1em;
	height: 10em;
	width: 33em;
}
.postcontrols
{
	position: relative;
	width: 10em;
	height: 5em;
	margin-top: -5em;
	margin-bottom: -2.5em;
	margin-left: 33em;
	text-align: center;
	line-height: 1.87500em;
}
.postcontrols input, .postcontrols button
{
	font-size: 1em;
	font-weight: bold;
}
