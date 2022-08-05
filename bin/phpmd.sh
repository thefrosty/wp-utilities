#!/usr/bin/env bash

set -e

echo 'Checking PHPMD'
source "$(dirname "$0")/functions.sh"

args="text phpmd-ruleset.xml --exclude tests,vendor --suffixes php"
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

for file in ${phpFiles}; do
  ./vendor/bin/phpmd ${file} ${args}
done
