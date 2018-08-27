#!/usr/bin/env bash

# Copyright (c) 2005-2018 Ross Smith II (http://smithii.com). MIT Licensed.

URL=http://www.textpad.com/add-ons/index.html
REFERER=http://www.textpad.com/add-ons/index.html
UA="Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36"

WGET_OPTIONS="--mirror --no-parent --random-wait"
# WGET_OPTIONS="${WGET_OPTIONS} --wait 5"

wget ${WGET_OPTIONS} --referer="${REFERER}" --user-agent="${UA}" ${URL}

# mv -i textpad_installer.nsh textpad_installer.nsh.old

# php textpad_installer.php >textpad_installer.nsh
