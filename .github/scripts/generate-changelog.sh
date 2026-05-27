#!/bin/bash
# Generates a changelog section in readme.txt from merged PRs since the latest tag on the current branch.
#
# The new section is inserted directly below the `== Changelog ==` header. If a section for the
# requested version already exists, it is replaced (its existing release date is preserved when no
# RELEASE_DATE env var is provided).
#
# Required environment variables:
#   GH_TOKEN   - GitHub token for API access.
#   VERSION    - Version number (e.g., 1.19).
#
# Optional environment variables:
#   PREVIOUS_TAG  - Tag to compare from (defaults to latest non-RC release tag on the branch).
#   RELEASE_DATE  - Release date in YYYY-MM-DD format (defaults to existing value in readme.txt, then calculated).
#   README_FILE   - Path to readme.txt (defaults to readme.txt).

set -euo pipefail

VERSION="${VERSION:?VERSION is required}"
README_FILE="${README_FILE:-readme.txt}"

if [ ! -f "$README_FILE" ]; then
	echo "::error::$README_FILE not found."
	exit 1
fi

# If a section for the requested version already exists in readme.txt, capture its release date so we
# can preserve it (unless RELEASE_DATE was explicitly set).
EXISTING_RELEASE_DATE=""
EXISTING_SECTION=$(awk -v v="= ${VERSION} =" '
	$0 == v { flag = 1; next }
	flag && /^= [0-9]/ { flag = 0 }
	flag { print }
' "$README_FILE")

if [ -n "$EXISTING_SECTION" ]; then
	EXISTING_RELEASE_DATE=$(echo "$EXISTING_SECTION" | grep -oP '(?<=Release date: ).*' | head -1 || true)
fi

# Resolve the release date.
if [ -n "${RELEASE_DATE:-}" ]; then
	: # Use provided value.
elif [ -n "$EXISTING_RELEASE_DATE" ]; then
	RELEASE_DATE="$EXISTING_RELEASE_DATE"
else
	# Same rules as duplicate-post's grunt task:
	#   patch (x.y.z, z>0): today.
	#   minor/major: next Tuesday ~2 weeks out.
	#   beta: ~3 weeks out.
	BASE_VERSION=$(echo "$VERSION" | cut -d'-' -f1)
	PATCH=$(echo "$BASE_VERSION" | awk -F. '{ print ($3 != "" ? $3 : 0) }')
	SUFFIX=$(echo "$VERSION" | grep -oP '(?<=-).*' || true)

	if [ "$PATCH" -gt 0 ]; then
		RELEASE_DATE=$(date +%Y-%m-%d)
	else
		DAYS_TO_ADD=14
		if echo "$SUFFIX" | grep -qi "beta"; then
			DAYS_TO_ADD=21
		fi
		DAY_OF_WEEK=$(date +%w)
		OFFSET=$(( 2 + DAYS_TO_ADD - DAY_OF_WEEK ))
		RELEASE_DATE=$(date -d "+${OFFSET} days" +%Y-%m-%d)
	fi
fi

# Resolve the previous tag.
if [ -z "${PREVIOUS_TAG:-}" ]; then
	PREVIOUS_TAG=$(git tag --sort=-creatordate --merged HEAD | grep -E '^[0-9]+\.[0-9]+(\.[0-9]+)?$' | head -1 || echo "")
fi

if [ -z "$PREVIOUS_TAG" ]; then
	echo "::error::No previous tag found on this branch."
	exit 1
fi

echo "Generating changelog for version $VERSION since tag $PREVIOUS_TAG (release date: $RELEASE_DATE)"

PR_NUMBERS=$(git log --grep='Merge pull request' "$PREVIOUS_TAG..HEAD" --oneline | grep -oP '#\K[0-9]+' || true)

if [ -z "$PR_NUMBERS" ]; then
	echo "::error::No merged PRs found since $PREVIOUS_TAG; refusing to write an empty changelog section. If this is intentional, edit readme.txt manually."
	exit 1
fi

ENHANCEMENTS=""
BUGFIXES=""
OTHER=""

for PR_NUM in $PR_NUMBERS; do
	echo "Processing PR #$PR_NUM..."

	PR_JSON=$(gh pr view "$PR_NUM" --json labels,body 2>/dev/null || echo '{"labels":[],"body":""}')

	LABEL=$(echo "$PR_JSON" | jq -r '[.labels[].name] | map(select(startswith("changelog:"))) | first // empty' | sed 's/^changelog: //')

	if [ -z "$LABEL" ] || [ "$LABEL" = "non-user-facing" ] || [ "$LABEL" = "reverted" ]; then
		echo "  Skipping (label: ${LABEL:-none})"
		continue
	fi

	# Strip CRs (GitHub returns PR bodies with \r\n line endings; the trailing \r leaks into
	# captured bullets and breaks the markdown rendering of any suffix we append).
	BODY=$(echo "$PR_JSON" | jq -r '.body // ""' | tr -d '\r')

	# Strip HTML comments first so the PR template's instructional `* changelog: …` bullets
	# (which live inside <!-- ... -->) are not picked up as changelog entries.
	# Anchor the range on the template intro line `This PR can be summarized in the following
	# changelog entry:` rather than the `## …` heading. The intro phrasing is identical across
	# the older `## Summary` template and the newer `## Changelog Entry` one, and it lives
	# below any HTML-comment block, so we get correct ranges for both without false positives
	# from bullets that happen to quote the heading text.
	ENTRIES=$(echo "$BODY" | awk 'BEGIN{c=0} /<!--/{c=1} !c{print} /-->/{c=0}' \
		| sed -n '/[Ss]ummarized in the following [Cc]hangelog [Ee]ntry/,/^##/p' | grep '^\*' | grep -v '^\* *$' || true)

	if [ -z "$ENTRIES" ]; then
		echo "  No changelog entry found"
		continue
	fi

	# Each entry on its own line, drop empties.
	FILTERED=$(echo "$ENTRIES" | sed '/^\* *$/d')

	case "$LABEL" in
		enhancement) ENHANCEMENTS+="$FILTERED"$'\n' ;;
		bugfix)      BUGFIXES+="$FILTERED"$'\n' ;;
		other)       OTHER+="$FILTERED"$'\n' ;;
		*)           echo "  Unknown label: $LABEL, treating as other"; OTHER+="$FILTERED"$'\n' ;;
	esac
done

# Build the new section in readme.txt format.
NEW_SECTION="= ${VERSION} =

Release date: ${RELEASE_DATE}
"

if [ -n "$ENHANCEMENTS" ]; then
	NEW_SECTION+="
Enhancements:
$(echo -n "$ENHANCEMENTS" | sed '/^$/d')
"
fi

if [ -n "$BUGFIXES" ]; then
	NEW_SECTION+="
Bugfixes:
$(echo -n "$BUGFIXES" | sed '/^$/d')
"
fi

if [ -n "$OTHER" ]; then
	NEW_SECTION+="
Other:
$(echo -n "$OTHER" | sed '/^$/d')
"
fi

# Splice the new section into readme.txt. If a section for VERSION exists, replace it; otherwise
# insert immediately after `== Changelog ==` (skipping any blank line that follows the header).
TMP_FILE=$(mktemp)
SECTION_FILE=$(mktemp)
printf '%s\n' "$NEW_SECTION" > "$SECTION_FILE"

awk -v version="$VERSION" -v section_file="$SECTION_FILE" '
	BEGIN {
		while ( (getline line < section_file) > 0 ) {
			new_section = new_section line "\n"
		}
		close(section_file)
		inserted = 0
		in_old = 0
	}
	{
		if ( !inserted && /^== Changelog ==/ ) {
			print
			# Print the new section straight after the header. If the next line is blank, swallow it
			# so we do not end up with a double blank line.
			if ( (getline next_line) > 0 ) {
				if ( next_line != "" ) {
					printf "\n%s", new_section
					print next_line
				} else {
					printf "\n%s", new_section
				}
			} else {
				printf "\n%s", new_section
			}
			inserted = 1
			next
		}

		# If a section for the same version exists later in the file, swallow it (already replaced above).
		if ( $0 == "= " version " =" ) {
			in_old = 1
			next
		}
		if ( in_old ) {
			if ( /^= [0-9]/ ) {
				in_old = 0
				print
			}
			next
		}

		print
	}
' "$README_FILE" > "$TMP_FILE"

mv "$TMP_FILE" "$README_FILE"
rm -f "$SECTION_FILE"

echo "Updated $README_FILE with section for $VERSION"
