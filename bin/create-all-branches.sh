#!/usr/bin/env bash

set -e

# https://stackoverflow.com/a/44036486/558561
function create_all_branches() {
  # Keep track of where Travis put us.
  # We are on a detached head, and we need to be able to go back to it.
  local build_head
  build_head=$(git rev-parse HEAD)

  # Fetch all the remote branches. Travis clones with `--depth`, which
  # implies `--single-branch`, so we need to overwrite remote.origin.fetch to
  # do that.
  git config --replace-all remote.origin.fetch +refs/heads/*:refs/remotes/origin/*
  git fetch --prune
  # optionally, we can also fetch the tags, pass `true` to the function call.
  if [[ $# -eq 1 ]]; then
    git fetch --tags
  fi

  # create the tacking branches
  for branch in $(git branch -r | grep -v HEAD); do
    git checkout -qf "${branch#origin/}"
  done

  # finally, go back to where we were at the beginning
  git checkout "${build_head}"
}

if [[ -n "$CI" ]]; then
  create_all_branches "$@"
fi;
