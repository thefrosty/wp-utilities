#!/usr/bin/env bash

set -e

echo 'Checking PHPCS'
source "$(dirname "$0")/functions.sh"

args="-s --colors --extensions=php --tab-width=4 --standard=phpcs-ruleset.xml --runtime-set testVersion ${TEST_VERSION}-"
phpFiles=""
phpFilesCount=0
for f in ${commitFiles}; do
  if [[ ! -e ${f} ]]; then
    continue
  fi
  if [[ ${f} =~ \.(php|ctp)$ ]]; then
    phpFilesCount=$((phpFilesCount + 1))
    phpFiles="$phpFiles $f"
  fi
done
if [[ ${phpFilesCount} == 0 ]]; then
  echo "No PHP files updated, nothing to check."
  exit 0
fi

echo "Checking files: $phpFiles"

if [[ ${phpFilesCount} -gt 2 ]] && { [[ ${GITHUB_ACTIONS+x} ]] || [[ ${CIRCLECI+x} ]]; }; then
  args="$args --report=summary"
fi

./vendor/bin/phpcs ${args} ${phpFiles}
