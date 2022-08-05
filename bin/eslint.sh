#!/usr/bin/env bash

set -e

echo 'Checking ESLint'
source "$(dirname "$0")/functions.sh"

jsFiles=""
jsFilesCount=0
for f in ${commitFiles}; do
	if [[ ! -e ${f} ]]; then
		continue
	fi
	if [[ ${f} =~ \.(js|jsx)$ ]]; then
		jsFilesCount=$((jsFilesCount + 1))
		jsFiles="$jsFiles $f"
	fi
done
if [[ ${jsFilesCount} == 0 ]]; then
	echo "No JS files updated, nothing to check."
	exit 0
fi

echo "Checking files: $jsFiles"

npx standard "${jsFiles}"
