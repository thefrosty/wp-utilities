#!/usr/bin/env bash

set -e

# Default values of arguments
# https://stackoverflow.com/a/44750379/558561 -- get the default git branch name
DEFAULT_BRANCH=$(git symbolic-ref refs/remotes/origin/HEAD | sed 's@^refs/remotes/origin/@@') || echo "develop"
TEST_VERSION="7.4"
OTHER_ARGUMENTS=()

# Loop through arguments and process them
# @ref https://pretzelhands.com/posts/command-line-flags/
for arg in "$@"
do
    case $arg in
        --default-branch)
        DEFAULT_BRANCH="$2"
        shift 2 # Remove name and value
        ;;
        --test-version)
        TEST_VERSION="$2"
        shift 2 # Remove name and value
        ;;
        *)
        OTHER_ARGUMENTS+=("$1")
        shift # Remove generic argument from processing
        ;;
    esac
done

if [[ $(git rev-parse --verify HEAD) ]]; then
  against='HEAD'
elif [[ $(git rev-parse --verify develop) ]]; then
  against='develop'
elif [[ $(git rev-parse --verify main) ]]; then
  against='main'
elif [[ $(git rev-parse --verify master) ]]; then
  against='master'
else
  echo "git can't verify HEAD, develop, master, or main."
  exit 1
fi

commitFiles=$(git diff --name-only "$(git merge-base ${DEFAULT_BRANCH} ${against})")
