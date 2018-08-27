<?php

# Copyright (c) 2005-2018 Ross Smith II (http://smithii.com). MIT Licensed.

/*

Depends:
php_tidy extension enabled
7z.exe in path
exetype.exe in path

todo if $ext <> zip then use
downloadGUIEXE
or downloadCLIEXE

use curl to get mod date and length headers, then download if different?
*/

$base = 'http://www.textpad.com';
$subdir = '/add-ons';
$root = '.';
$dir = $root . '/www.textpad.com';
$dest_root = $root . '/nsis';
$scandir = $dir . $subdir;
#$scandir = '.';

$files = array();

@mkdir($dest_root);

$get = array(
    'zip'   => '',
);

$skip = array(
);

function dump_nodes($file, $node) {
    global $files;
    global $base;
    global $subdir;
    global $get;
    global $dest_dir;
    global $skip;

    if (!$node->hasChildren()) {
        # printf("no children for %s", $file);
        return;
    }
    
    foreach ($node->child as $child) {
        if (isset($child->id) && $child->id == TIDY_TAG_A) {
            $url = @$child->attribute['href'];
            if (!$url)
                continue;
            if (strstr($url, ':') === false) {
                if (substr($url, 0, 1) <> '/')
                    $url = $base . $subdir . '/' . $url;
                else
                    $url = $base . $url;
#printf("url=%s\n", $url);
            }
#echo "child=";
#print_r($child);
            $a = parse_url($url);
            if (!$a)
                continue;
            if (@$a['scheme'] != 'http')
                continue;
            if (@$a['query'])
                continue;
            if (@$a['fragment'])
                continue;
            $b = pathinfo(@$a['path']);
            if (!$b)
                continue;
            $e = strtolower(@$b['extension']);

            if (!$e)
                continue;
            if (!isset($get[$e]))
                continue;
            $found = 0;
            foreach ($skip as $regex) {
                if (preg_match('/' . $regex . '/i', $b['basename'])) {
                    $found = 1;
                    break;
                }
            }
            if ($found > 0)
                continue;

            $value = '';
            if ($child->hasChildren()) {
                foreach ($child->child as $grandchild) {
                    $value = $grandchild->value;
                }
            }

            $desc = '';
            if ($child->getParent()) {
                $parent = $child->getParent();
                $grandparent = $parent->getParent();
                $n = 0;
                foreach ($grandparent->child as $grandchild) {
                    if ($n++ < 1) {
                        continue;
                    }
                    foreach ($grandchild->child as $greatgrandchild) {
                        $desc = $greatgrandchild->value;
                        break;
                    }
                }
            }

            $value = preg_replace('/\/\\:\*\?\<\>\|/', '-', $value);
            $value = preg_replace('/\s+/', ' ', $value);
            $value = preg_replace('/"/', "'", $value);
            $value = trim($value);

            $desc = preg_replace('/\/\\:\*\?\<\>\|/', '-', $desc);
            $desc = preg_replace('/\s+/', ' ', $desc);
            $desc = preg_replace('/"/', "'", $desc);
            $desc = trim($desc);

            $key = strtolower(basename($url));
            $files[$key] = array(
                'url'       => $url,
                'referer'   => $file,
                'value'     => $value,
                'desc'      => $desc,
            );
        }
        dump_nodes($file, $child);
    }
}

$a = scandir($scandir);

foreach($a as $file) {
    if (!preg_match('/\.html?/i', $file))
        continue;

    $tidy = tidy_parse_file($scandir . '/' . $file);

    dump_nodes($scandir . $file, $tidy->root());
}

$len = strlen($base) + 1;

ksort($files);

$zips = array(
    'cliplibs' => array(),
    'dictionaries' => array(),
    'macros' => array(),
    'syntax' => array(),
    'utilities' => array(),
);

printf("!ifdef INCLUDE_UTILITIES\n\n");

foreach($files as $key => $hash) {
    $url = $hash['url'];
    $referer = $hash['referer'];
    $value = $hash['value'];
    $desc = $hash['desc'];

    $file = substr($url, $len);

    $zip = $dir . '/' . $file;
    if (!file_exists($zip)) {
        fprintf(STDERR, "File not found: %s\n", $zip);
        continue;
    }
    $size = filesize($zip);
    $pathinfo = pathinfo($zip);
    if (!$pathinfo)
        continue;

    $ext = $pathinfo['extension'];
    $basename = basename($pathinfo['basename'], '.' . $ext);
    $dest_dir = $dest_root . '/' . $basename;
    if (!is_dir($dest_dir)) {
        if (!mkdir($dest_dir))
            die(sprintf("Cannot create directory '%s': %s", $dest_dir, @$phperror_msg));
    }

    if (strtolower($ext) <> 'zip') {
        $dst = $dest_dir . '/' . $pathinfo['basename'];
        if (!@copy($zip, $dst)) {
            die(sprintf("Cannot copy '%s' to '%s': %s", $zip, $dst, @$phperror_msg));
        }
    } else {
        $cmd = sprintf('7z.exe x -y -o"%s" "%s"', $dest_dir, $zip);
        exec($cmd, $output, $rv);
        if ($rv <> 0) {
            fprintf(STDERR, "Error %d executing '%s':\n", $rv, $cmd);
            fprintf(STDERR, join("\n", $output));
            continue;
        }
    }
    $exes = glob($dest_dir . '/*.[Ee][Xx][Ee]');
    $scrs = glob($dest_dir . '/*.[Ss][Cc][Rr]');
    $exes = array_merge($exes, $scrs);
    if (count($exes) == 0) {
        $size = '';
        $name = $basename;
        $exebase = '';
        $macro = 'TextPadDownloadAddOn';
        if (preg_match('/\bcliplibs\b/i', $url)) {
            $exebase = 'cliplibs';
        }
        if (preg_match('/\bdictionaries\b/i', $url)) {
            $exebase = 'dictionaries';
        }
        if (preg_match('/\bmacros\b/i', $url)) {
            $exebase = 'macros';
        }
        if (preg_match('/\bsyntax\b/i', $url)) {
            $exebase = 'syntax';
        }
        if (preg_match('/\butilities\b/i', $url)) {
            $exebase = 'utilities';
            $macro = 'TextPadDownloadUtility';
        }
        if ($exebase == '') {
            fprintf(STDERR, "%d exes found in %s (%s)\n", count($exes), $dest_dir, $url);
            continue;
        }

        if ($desc) {
          $my_desc = sprintf("%s - %s (%s)", $value, $desc, $name);
        } else {
          $my_desc = $name;
        }

        $zips[$exebase][$name] = sprintf('!insertmacro %s "3 4 5" "%s"    "%s"    "%s"    #"%s"   # "%s" %s' . "\n", $macro, $my_desc, $url, $exebase, $size, "", $referer);
    }
    $macros = Array();
    $md5s = Array();
    $hash = Array();
    foreach($exes as $exe) {
        $md5 = md5_file($zip);
        if (isset($md5s[$md5])) {
            fprintf(STDERR, "Duplicate md5: %s\n", $exe);
            continue;
        }
        $md5s[$md5] = $exe;
        $exebase = basename($exe);

        $cmd = 'exetype.exe -q ' . $exe;
        exec($cmd, $output, $exetype);

        $cmd = sprintf('sigcheck.exe "%s"', $exe);
        exec($cmd, $output, $rv);
        if ($rv <> 0) {
            fprintf(STDERR, "Error %d running '%s'\n", $rv, $cmd);
            fprintf(STDERR, join("\n", $output));
        }

        $_desc = '';
        $prod = '';

        foreach ($output as $o) {
            if (preg_match('/Description:\s*(.*)$/i', $o, $matches)) {
                $_desc = trim($matches[1]);
            }
            if (preg_match('/Product:\s*(.*)$/i', $o, $matches)) {
                $prod = trim($matches[1]);
            }
        }
        $name = $_desc;
        if ($_desc == '' || $_desc == 'n/a')
            $name = $prod;
        if ($name == '' || $name == 'n/a')
            $name = $basename;
        if (stristr($name, $basename) === false)
            $name = $basename . ': ' . $name;

        if ($desc) {
          $name = sprintf("%s - %s", $value, $desc);
        }

        if (substr($referer, 0, strlen($dir)) == $dir) {
            $referer = $base . substr($referer, strlen($dir), 255);
        }
        switch (strtolower($ext)) {
            case 'exe':
                $hash[$exetype] = sprintf('!insertmacro DownloadEXE "3 4 5" "%s"  "%s"    ""  "%s"    "%s"  ""    # %s' . "\n", $name, $url, $size, $md5, $referer);
                break;
            case 'zip':
                switch ($exetype) {
                    case 2:
                        if (isset($hash[$exetype])) {
                            fprintf(STDERR, "Extra GUI %s found in %s\n", $exe, $dest_dir);
                            continue;
                        }
                        $hash[$exetype] = sprintf('!insertmacro DownloadGUI "3 4 5" "%s"  "%s"    "%s"    "%s"    ""  ""  # "%s" %s' . "\n", $name, $url, $exebase, $size, $md5, $referer);
                        break;
                    case 3:
                        if (isset($hash[$exetype])) {
                            fprintf(STDERR, "Extra CLI %s found in %s\n", $exe, $dest_dir);
                            continue;
                        }
                        $hash[$exetype] = sprintf('!insertmacro DownloadCLI "3 4 5" "%s"  "%s"    "%s"    "%s"    ""  # "%s" %s' . "\n", $name, $url, $exebase, $size, $md5, $referer);
                        break;
                    default:
                        fprintf(STDERR, "exetype=%s for %s\n", $exetype, $exe);
                        $hash[1] = sprintf('#!insertmacro DownloadCLI "3 4 5" "%s"    "%s"    "%s"    "%s"  "" # "%s" %s' . "\n", $name, $url, $exebase, $size, $md5, $referer);
                        break;
                }
                break;
        }
    }
    if (isset($hash[3])) {
        print($hash[3]);
        continue;
    }
    if (isset($hash[2])) {
        print($hash[2]);
        continue;
    }
    if (isset($hash[1])) {
        print($hash[1]);
        continue;
    }
}

printf("!endif # INCLUDE_UTILITIES\n\n");

$desc_map = array(
    'cliplibs'  => 'Clip Libraries',
    'dictionaries'  =>  'Dictionaries',
    'macros'    =>  'Macros',
    'syntax'    =>  'Syntax Definitions',
    'utilities' =>  'Utilities',
);

foreach ($zips as $k => $v) {
    printf("\nSectionGroup '%s'\n\n", $desc_map[$k]);
    foreach ($v as $kk => $vv) {
        print $vv;
    }
    printf("\nSectionGroupEnd # '%s'\n\n", $desc_map[$k]);
}

# EOF
