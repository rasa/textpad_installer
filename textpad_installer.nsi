# Copyright (c) 2005-2015 Ross Smith II (http://smithii.com). MIT Licensed.

!define TEXTPAD_VER 7.4.0
!define TEXTPAD_EXE "TextPad 7.msi"
!define TEXTPAD32_URL http://www.textpad.com/download/v74/win32/txpeng740-32.zip
!define TEXTPAD64_URL http://www.textpad.com/download/v74/x64/txpeng740-64.zip

!define PRODUCT_NAME "textpad_installer"
#!define PRODUCT_VERSION "1.0"
!define PRODUCT_DESC "TextPad™ Installer ${PRODUCT_VERSION} for TextPad ${TEXTPAD_VER}"
!define PRODUCT_TRADEMARKS "TextPad™ is a trademark of Helios Software Solutions (http://textpad.com)"

!addincludedir "../nsislib"
!addincludedir "nsislib"

!include "config.nsh"

!undef PRODUCT_EXE
!undef PRODUCT_FILE
!define NO_STARTMENU_ICONS
!define NO_DESKTOP_ICONS

#!undef COPYDIR # "$EXEDIR"
!define NOEXTRACTPATH
!define UNZIP_DIR "$APPDATA\Helios\TextPad\6"

InstType "TextPad 32-bit ${TEXTPAD_VER} Only" #1
InstType "TextPad 64-bit ${TEXTPAD_VER} Only" #2
InstType "All Add-Ons Only" #3
InstType "TextPad 32-bit ${TEXTPAD_VER} + All Add-Ons" #4
InstType "TextPad 64-bit ${TEXTPAD_VER} + All Add-Ons" #5
InstType "None" #6

!include "common.nsh"

Section ""
	CreateDirectory "${UNZIP_DIR}"
SectionEnd

Section "TextPad 32-bit ${TEXTPAD_VER} - English"
	SectionIn 1 4

	!insertmacro Download "${TEXTPAD32_URL}" "" ''
	StrCpy $1 '$INSTDIR\${TEXTPAD_EXE}'
	StrCpy $2 ""
	IfSilent +1 not_silent
		StrCpy $2 '/quiet /qb-! /norestart ALLUSERS=2 INSTALLLEVEL=1000'
	not_silent:
	DetailPrint "Executing $SYSDIR\msiexec.exe /i $2 '$1'"
	ExecWait '"$SYSDIR\msiexec.exe" /i "$1" $2' $0
	DetailPrint '$SYSDIR\msiexec.exe /i "$1" $2 returned $0'
	# test return value $0?
SectionEnd

Section "TextPad 64-bit ${TEXTPAD_VER} - English"
	SectionIn 2 5

	!insertmacro Download "${TEXTPAD64_URL}" "" ''
	StrCpy $1 '$INSTDIR\${TEXTPAD_EXE}'
	StrCpy $2 ""
	IfSilent +1 not_silent
		StrCpy $2 '/quiet /qb-! /norestart ALLUSERS=2 INSTALLLEVEL=1000'
	not_silent:
	DetailPrint "Executing $SYSDIR\msiexec.exe /i $2 '$1'"
	ExecWait '"$SYSDIR\msiexec.exe" /i "$1" $2' $0
	DetailPrint '$SYSDIR\msiexec.exe /i "$1" $2 returned $0'
	# test return value $0?
SectionEnd

!macro TextPadDownload url dir
	${GetURLFileName} '${url}' $1

	DetailPrint "Looking for $EXEDIR\${dir}\$1"

	FindFirst $0 $3 "$EXEDIR\${dir}\$1"
#	DetailPrint "Found '$3'"
	FindClose $0

	StrCpy $2 "$EXEDIR\${dir}\$1"
	StrCmp "$3" "" download md5

	download:

#	DetailPrint "Not found, will download"

	Call ConnectInternet

	StrCpy $2 "$TEMP\$1"
	DetailPrint "Downloading ${url} to $2"

	restart:

	IntOp $download_retries ${DOWNLOAD_RETRIES} + 0

	retry:
		Delete $2
		NSISdl::download ${url} $2
		Pop $0
		StrCmp $0 "success" success

		SetDetailsView show
		DetailPrint "Error: $0, retrying $download_retries more time(s)"
		IntOp $download_retries $download_retries - 1
		IntCmp $download_retries 0 download_error download_error
		Sleep ${RETRY_WAIT}
	Goto retry

	download_error:
		SetDetailsView show
		DetailPrint "Failed to download ${url}: $0"

		MessageBox MB_RETRYCANCEL "Failed to download ${url}: $0?" /SD IDCANCEL IDRETRY restart
		Abort
	success:

	md5:

!ifdef CHECK_MD5
	DetailPrint "Checking MD5 for $2"

	md5dll::GetMD5File "$2"
	Pop $0
	StrCmp "$0" "${md5}" md5ok
		DetailPrint "Failed MD5 check for $2: expected ${md5}, found $0"
		Abort

	md5ok:
!endif # CHECK_MD5

	StrCmp "$3" "" copy dont_copy
	copy:
	CreateDirectory "$EXEDIR\${dir}"
	DetailPrint "Copying $2 to $EXEDIR\${dir}"
	CopyFiles /SILENT "$2" "$EXEDIR\${dir}"
	dont_copy:

	DetailPrint "Deleting $TEMP\$1"
  	Delete "$TEMP\$1"
!macroend

!macro TextPadDownloadZip url dir
	${GetURLFileName} '${url}' $1

	DetailPrint "Looking for $EXEDIR\${dir}\$1"

	FindFirst $0 $3 "$EXEDIR\${dir}\$1"
#	DetailPrint "Found '$3'"
	FindClose $0

	StrCpy $2 "$EXEDIR\${dir}\$1"
	StrCmp "$3" "" download md5

	download:

#	DetailPrint "Not found, will download"

	Call ConnectInternet

	StrCpy $2 "$TEMP\$1"
	DetailPrint "Downloading ${url} to $2"

	restart:

	IntOp $download_retries ${DOWNLOAD_RETRIES} + 0

	retry:
		Delete $2
		NSISdl::download ${url} $2
		Pop $0
		StrCmp $0 "success" success

		SetDetailsView show
		DetailPrint "Error: $0, retrying $download_retries more time(s)"
		IntOp $download_retries $download_retries - 1
		IntCmp $download_retries 0 download_error download_error
		Sleep ${RETRY_WAIT}
	Goto retry

	download_error:
		SetDetailsView show
		DetailPrint "Failed to download ${url}: $0"

		MessageBox MB_RETRYCANCEL "Failed to download ${url}: $0?" /SD IDCANCEL IDRETRY restart
		Abort
	success:

	md5:

!ifdef CHECK_MD5
	DetailPrint "Checking MD5 for $2"

	md5dll::GetMD5File "$2"
	Pop $0
	StrCmp "$0" "${md5}" md5ok
		DetailPrint "Failed MD5 check for $2: expected ${md5}, found $0"
		Abort

	md5ok:
!endif # CHECK_MD5

	DetailPrint "Unzipping $2 to ${UNZIP_DIR}"

!ifdef NOEXTRACTPATH
	nsUnzip::Extract /j $2 "/d=${UNZIP_DIR}" /END
	#nsisunz::UnzipToLog /noextractpath $2 "${UNZIP_DIR}"
!else
	nsUnzip::Extract $2 "/d=${UNZIP_DIR}" /END
	#nsisunz::UnzipToLog $2 "${UNZIP_DIR}"
!endif # NOEXTRACTPATH

	Pop $0
	StrCmp $0 "success" ok
		DetailPrint "Failed to unzip $2: $0"
		Abort
	ok:

	StrCmp "$3" "" copy dont_copy
	copy:
	CreateDirectory "$EXEDIR\${dir}"
	DetailPrint "Copying $2 to $EXEDIR\${dir}"
	CopyFiles /SILENT "$2" "$EXEDIR\${dir}"
	dont_copy:

	DetailPrint "Deleting $TEMP\$1"
  	Delete "$TEMP\$1"
!macroend

!macro TextPadDownloadAddOn sectionin title url dir
	Section "${title}"
		SectionIn ${sectionin}

		!insertmacro TextPadDownloadZip "${url}" "${dir}"
	SectionEnd
!macroend

!macro TextPadDownloadAddOnNoUnZip sectionin title url dir
	Section "${title}"
		SectionIn ${sectionin}

		!insertmacro TextPadDownload "${url}" "${dir}"
	SectionEnd
!macroend

!macro TextPadDownloadUtility sectionin title url dir
	Section "${title}"
		SectionIn ${sectionin}

		!insertmacro TextPadDownload "${url}" "${dir}"
	SectionEnd
!macroend

!include "textpad_installer.nsh"
