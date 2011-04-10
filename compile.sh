#! /bin/sh
cat \
 ./getscrollbarwidth/jquery.getscrollbarwidth.js \
 ./splatter/src/jquery.splatter.js \
 | sed -e's-/\*\!-/*-' > jquery.extra.js

yui-compressor jquery.extra.js -o jquery.extra.min.js
