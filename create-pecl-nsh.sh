#!/bin/bash

# Copyright (c) 2005-2015 Ross Smith II (http://smithii.com). MIT Licensed.

export UA="Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.111 Safari/537.36"
export REFERER=http://www.textpad.com/add-ons/index.html

wget --mirror --no-parent --random-wait --referer="$REFERER" --user-agent="$UA" --wait 5 http://www.textpad.com/add-ons/index.html

mv -f textpad_installer.nsh textpad_installer.nsh.old

php textpad_installer.php >textpad_installer.nsh
