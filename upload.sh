#!/usr/bin/env bash

PROD_REMOTE="fbhi.se:/www/webvol28/ww/lh9azq37w77rgig/fbhi.se/public_html/wp-content/themes/"
DEV_REMOTE="fbhi.devcx.com:/home/devfbhi/public_html/wp-content/themes/"
LOCAL="/home/dblom/wsl-projects/fbhi-website/salient-child"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
RESET='\033[0m'

TARGET="${1:-}"
if [[ -z "$TARGET" ]]; then
  echo -e "${BOLD}Select deployment target:${RESET}"
  echo -e "  ${CYAN}[p]${RESET} Production  (fbhi.se)"
  echo -e "  ${CYAN}[d]${RESET} Dev         (fbhi.devcx.com)"
  read -rp "Choice [p/d]: " TARGET
fi

case "$TARGET" in
  p|prod|production)
    REMOTE="$PROD_REMOTE"
    LABEL="${RED}${BOLD}PRODUCTION${RESET} (fbhi.se)"
    ;;
  d|dev|development)
    REMOTE="$DEV_REMOTE"
    LABEL="${CYAN}${BOLD}DEV${RESET} (fbhi.devcx.com)"
    ;;
  *)
    echo -e "${RED}Unknown target: '$TARGET'. Use 'p' (prod) or 'd' (dev).${RESET}"
    exit 1
    ;;
esac

echo ""
echo -e "${BOLD}Target:${RESET} $LABEL"
echo -e "${BOLD}Comparing local with remote... (dry-run)${RESET}"
echo ""

HAS_CHANGES=false

OUTPUT=$(rsync -avzn --delete --itemize-changes "$LOCAL" "$REMOTE" 2>&1)
RSYNC_EXIT=$?

if [ $RSYNC_EXIT -ne 0 ]; then
  echo -e "${RED}${BOLD}Dry-run failed (rsync exit code $RSYNC_EXIT):${RESET}"
  echo "$OUTPUT"
  exit 1
fi

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

echo -e "${BOLD}Target:${RESET} $LABEL"
read -rp "Proceed with deploy? [y/N] " answer
if [[ "$answer" =~ ^[Yy]$ ]]; then
  echo ""
  rsync -avz --progress --delete "$LOCAL" "$REMOTE"
else
  echo "Deploy cancelled."
fi
