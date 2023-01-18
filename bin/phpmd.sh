#!/usr/bin/env bash

set -e

source "$(dirname "$0")/functions.sh"
echo 'Checking PHPMD'

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

phpFiles=$(echo "${phpFiles:1}" | xargs)
echo "Checking files: $phpFiles"

# shellcheck disable=SC2086
for file in ${phpFiles}; do
  source_bin_file phpmd ${file} "${args}"
done
