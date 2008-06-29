#!/bin/bash

# create phpDocumentor docs from Nephthys source code

phpdoc -o HTML:frames:earthli \
       -d . \
       -t docs \
       -i 'thumbs/,resources/,templates_c/,nephthys_cfg.php' \
       -ti 'Nephthys source-code documentation' \
       -dn 'nephthys' \
       -s \
       -q
