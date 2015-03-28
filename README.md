# textpad_installer [![Flattr this][flatter_png]][flatter]

Downloadsand install TextPad, and many of the
freeware add-ons available at http://www.textpad.com/add-ons/.

textpad_installer looks in the directory where textpad_installer.exe is for
any files it needs before attempting to download them. If it does download a
file, it will attempt to save a copy of the file in a directory below the
directory where textpad_installer.exe was found in.

The add-ons will be installed in the
`C:\Documents and Settings\%USERNAME%\Application Data\TextPad` directory.
Any add-ons with the same name in this directory will be overwritten without
warning.

Please note, if you select 'All', some add-ons have the same filenames as
other add-ons, so the one listed later, will overwrite the one listed earlier.

If you use the `/S` option, the installer will re-install TextPad, even if it's
already installed.

## Usage

````
textpad_installer [options]

Options:
/S         Install the application silently with the default options selected
/D=path    Install into the directory 'path' (default is
           %ProgramFiles%\textpad_installer)
/INSTYPE n Where n is a number between 0 and 3:
           0: TextPad 32-bit Only (Default)
           1: TextPad 64-bit Only
           2: All Add-Ons Only (TextPad should already be installed)
           3: TextPad 32-bit + All Add-Ons
           4: TextPad 64-bit + All Add-Ons
           5: None
````

## Verify a Release

To verify a release, download the .zip, .sha256, and .asc files for the release 
(replacing textpad_installer-7.4.0.1-win32.zip with the release you are verifying):

````
$ wget https://github.com/rasa/textpad_installer/releases/download/v7.4.0.1/textpad_installer-7.4.0.1-win32.zip{,.sha256,.asc}
````

Next, check that sha256sum reports "OK":
````
$ sha256sum -c textpad_installer-7.4.0.1-win32.zip.sha256
textpad_installer-7.4.0.1-win32.zip: OK
````

Lastly, check that GPG reports "Good signature":

````
$ gpg --keyserver hkps.pool.sks-keyservers.net --recv-key 0x105a5225b6ab4b22
$ gpg --verify textpad_installer-7.4.0.1-win32.zip.asc textpad_installer-7.4.0.1-win32.zip
gpg:                using RSA key 0xFF914F74B4BB6EF3
gpg: Good signature from "Ross Smith II <ross@smithii.com>" [ultimate]
...
````

## Contributing

To contribute to this project, please see [CONTRIBUTING.md](CONTRIBUTING.md).

## Bugs

To view existing bugs, or report a new bug, please see [issues](../../issues).

## Changelog

To view the version history for this project, please see [CHANGELOG.md](CHANGELOG.md).

## License

This project is [MIT licensed](LICENSE).

## Contact

This project was created and is maintained by [Ross Smith II][] [![endorse][endorse_png]][endorse]

Feedback, suggestions, and enhancements are welcome.

[Ross Smith II]: mailto:ross@smithii.com "ross@smithii.com"
[flatter]: https://flattr.com/submit/auto?user_id=rasa&url=https%3A%2F%2Fgithub.com%2Frasa%2Ftextpad_installer
[flatter_png]: http://button.flattr.com/flattr-badge-large.png "Flattr this"
[endorse]: https://coderwall.com/rasa
[endorse_png]: https://api.coderwall.com/rasa/endorsecount.png "endorse"

