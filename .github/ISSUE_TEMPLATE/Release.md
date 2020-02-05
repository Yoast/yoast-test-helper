---
name: Release
about: Create a new release
---

## Release version [VERSION]

* [ ] Update the changelog in `readme.txt` with the relevant entries.
* [ ] Merge `develop` into `master`.
* [ ] Set the version using `grunt set-version --new-version=[VERSION]` (e.g., `grunt set-version --new-version=1.1.0`) & `grunt update-version`.
* [ ] Commit & push the changes: `git commit -m "Updating version to [VERSION]" && git push`.
* [ ] Make the tag, e.g. `git tag -a 1.1.0 -m "1.1.0"`.
* [ ] Push the tag: `git push --tags`
* [ ] Run `grunt artifact`
* [ ] Rename the zip to `yoast-test-helper.zip`.
* [ ] [Create a GitHub release](https://github.com/Yoast/yoast-test-helper/releases), paste the changelog to the notes and attach the zip.
* [ ] Merge `master` back to `develop`.
