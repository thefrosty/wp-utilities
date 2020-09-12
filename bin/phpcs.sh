#!/usr/bin/env bash

set -e

# Based off: https://gist.github.com/Hounddog/3891872
# Go to root of the repository
echo 'Checking PHPCS'

if [[ $(git rev-parse --verify HEAD) ]]; then
  against='HEAD'
elif [[ $(git rev-parse --verify develop) ]]; then
  against='develop'
elif [[ $(git rev-parse --verify master) ]]; then
  against='master'
else
  echo "git can't verify HEAD, develop or master."
  exit 1
fi

commitFiles=$(git diff --name-only "$(git merge-base develop ${against})")

args="-s --colors --extensions=php --tab-width=4 --standard=phpcs-ruleset.xml --runtime-set testVersion 7.3-"
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

if [[ ${phpFilesCount} -gt 2 ]] && { [[ ${TRAVIS+x} ]] || [[ ${CIRCLECI+x} ]]; }; then
  args="$args --report=summary"
fi

./vendor/bin/phpcs ${args} ${phpFiles}
