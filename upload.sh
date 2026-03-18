#!/usr/bin/env bash

REMOTE="fbhi.se:/www/webvol53/ww/lh9azq37w77rgig/fbhi.se/public_html/wp-content/themes/"
LOCAL="/home/dblom/wsl-projects/fbhi-website/salient-child"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BOLD='\033[1m'
RESET='\033[0m'

echo -e "${BOLD}Comparing local with remote... (dry-run)${RESET}"
echo ""

HAS_CHANGES=false

OUTPUT=$(rsync -avzn --delete --itemize-changes "$LOCAL" "$REMOTE" 2>&1)

while IFS= read -r line; do
  [[ -z "$line" ]] && continue

  if [[ "$line" == *deleting* ]]; then
    file="${line#*deleting   }"
    echo -e "  ${RED}WILL DELETE      ${file}${RESET}  (exists on remote but not locally)"
    HAS_CHANGES=true
    continue
  fi

  flags="${line:0:11}"
  file="${line:12}"

  [[ "${flags:1:1}" != "f" ]] && continue

  if [[ "${flags:2:1}" == "+" ]]; then
    echo -e "  ${GREEN}WILL ADD         ${file}${RESET}"
    HAS_CHANGES=true
    continue
  fi

  if [[ "${flags:0:1}" == "<" ]]; then
    echo -e "  ${YELLOW}WILL OVERWRITE   ${file}${RESET}  (local differs from remote)"
    HAS_CHANGES=true
    continue
  fi
done <<< "$OUTPUT"

echo ""

if [ "$HAS_CHANGES" = false ]; then
  echo -e "${GREEN}${BOLD}Nothing to deploy. Local and remote are in sync.${RESET}"
  exit 0
fi

read -rp "Proceed with deploy? [y/N] " answer
if [[ "$answer" =~ ^[Yy]$ ]]; then
  echo ""
  rsync -avz --progress --delete "$LOCAL" "$REMOTE"
else
  echo "Deploy cancelled."
fi
